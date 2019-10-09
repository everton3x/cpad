<?php

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Converte.
 * 
 * @param OutputInterface $console
 * @param array $ds Caminhos dos diretórios com os *.txt. Se houver mais que um, os dados serão agregados.
 * @param string $repo Caminho para o repositório que conterá o resultado da conversão.
 * @return void
 */
function parse(OutputInterface $console, string $repo, array $ds): void {
    $console->writeln(sprintf("<comment>Conversão iniciada em %s, às %s</comment>", date('d/m/Y'), date('H:i:s')));

    /* pega a lista de *.txt de todos os ds */
    try {
        $dsList = [];
        foreach ($ds as $item) {
            $dsList = array_merge($dsList, ds_get_files($item));
        }
    } catch (Exception $ex) {
        throw $ex;
    }

    /* prepara o writer adequado */
    try {
        writer_select($repo);
        $writer = writer_create($repo);
    } catch (Exception $ex) {
        throw $ex;
    }

    ProgressBar::setFormatDefinition('custom', '<info>%message%</info> [%bar%] %percent:3s%% [%current% / %max%]');
    
    /* pegando metadados */
    try {
        $index = 0;
        $totalRows = 0;
        $progressbarMeta = new ProgressBar($console->section());
        $progressbarMeta->setBarCharacter('|');
        $progressbarMeta->setProgressCharacter('|');
        $progressbarMeta->setEmptyBarCharacter(' ');
        $progressbarMeta->setBarWidth(80);
        $progressbarMeta->setFormat('custom');
        $progressbarMeta->setMessage(str_pad("Salvando meta-dados:", 20, ' ', STR_PAD_RIGHT));
        $progressbarMeta->start(count($dsList));
        foreach ($dsList as $txtPath) {
            $tmp = ds_get_meta($txtPath);

            $meta[$index]['arquivo'] = $txtPath;
            $meta[$index]['cnpj'] = $tmp['cnpj'];
            $meta[$index]['data_inicial'] = $tmp['data_inicial'];
            $meta[$index]['data_final'] = $tmp['data_final'];
            $meta[$index]['data_geracao'] = $tmp['data_geracao'];
            $meta[$index]['entidade'] = $tmp['entidade'];
            $meta[$index]['registros'] = $tmp['registros'];

            $totalRows += $meta[$index]['registros'];

            /* salva os meta dados */
            $txtName = basename(strtoupper($meta[$index]['arquivo']), '.TXT');
            writer_save_meta($writer, $txtName, $meta[$index]);
            $progressbarMeta->advance();
            $index++;
        }
    } catch (Exception $ex) {
        throw $ex;
    }

    /* loop de processamento dos arquivos */
    try {

        /* configura as barras de progresso */
        $totalProgress = 0;
        $sectionTotal = $console->section();
        $progressbarTotal = new ProgressBar($sectionTotal);
        $progressbarTotal->setBarCharacter('|');
        $progressbarTotal->setProgressCharacter('|');
        $progressbarTotal->setEmptyBarCharacter(' ');
        $progressbarTotal->setBarWidth(80);
        ProgressBar::setFormatDefinition('custom2', '<info>%message%</info> [<info>%bar%</info>] %percent:3s%% [%current% / %max%]');
        $progressbarTotal->setFormat('custom2');
        $progressbarTotal->setMessage(str_pad("Progresso Total", 20, ' ', STR_PAD_RIGHT));
        $progressbarTotal->setRedrawFrequency(1000);
        $progressbarTotal->start($totalRows);

        $sectionFile = $console->section();

        $noSpecFiles = ["<comment>Arquivos sem especificação:</comment>"];

        foreach ($meta as $txtMeta) {
            $txtName = basename(strtoupper($txtMeta['arquivo']), '.TXT');

            /* verifica se tem spec */
            if (is_null($spec = spec_get($txtName))) {
                $noSpecFiles[] = $txtName;
                continue;
            }

            $fileProgress = 0;
            $progressbarFile = new ProgressBar($sectionFile);
            $progressbarFile->setMessage(str_pad($txtName, 20, ' ', STR_PAD_RIGHT));
            $progressbarFile->setBarCharacter('|');
            $progressbarFile->setProgressCharacter('|');
            $progressbarFile->setEmptyBarCharacter(' ');
            $progressbarFile->setBarWidth(80);
            $progressbarFile->setFormat('custom');
            $progressbarFile->setRedrawFrequency((int) $txtMeta['registros'] * 0.0001);
            $progressbarFile->start($txtMeta['registros']);

            if (($handle = fopen("{$txtMeta['arquivo']}", 'r')) === false) {
                throw new Exception("Não foi possível abrir {$txtMeta['arquivo']}");
            }

            //avança a primeira linha
            $tmp = fgets($handle);

            /* prepara a tabela no writer */
            if (writer_prepare($writer, $txtName, $spec) === false) {
                throw new Exception("Falha ao preparar o writer para receber $txtName");
            }
            /* processa cada linha */
            while (($buffer = fgets($handle)) !== false) {
                if (strtoupper(substr($buffer, 0, 11)) === 'FINALIZADOR') {
                    break;
                }

                if (writer_write($writer, $txtName, parse_data($buffer, $spec)) === false) {
                    throw new Exception("Falha ao salvar dados da linha $fileProgress em $txtName:" . PHP_EOL . $buffer);
                }
                $totalProgress++;
                $fileProgress++;
                $progressbarFile->advance();
                $progressbarTotal->advance();
            }//loop linhas

            if (writer_commit($writer, $txtName) === false) {
                throw new Exception("Falha ao salvar $txtName");
            }

            $progressbarFile->finish();
        }//loop arquivos

        if (writer_save($writer) === false) {
            throw new Exception("Falha ao finalizar o writer");
        }

        $progressbarTotal->finish();

        $console->writeln($noSpecFiles);
    } catch (Exception $ex) {
        throw $ex;
    }

    $console->writeln(sprintf("<info>Conversão terminada em %s, às %s</>", date('d/m/Y'), date('H:i:s')));
}

/**
 * Converte cada linha com campos em largura fixa num array.
 * 
 * @param string $data
 * @param SimpleXMLElement $cols
 * @return array
 */
function parse_data(string $data, SimpleXMLElement $spec): array {
    try {

        $result = [];
        $cols = $spec->field;
        foreach ($cols as $field) {
            $result[(string) $field['id']] = substr($data, (int) $field['start'] - 1, (int) $field['size']);

            if ($field['transform'] != "") {
                $transform = (string) $field['transform'];
                $result[(string) $field['id']] = $transform($result[(string) $field['id']]);
            }
        }
        return $result;
    } catch (Exception $ex) {
        throw $ex;
    }
}
