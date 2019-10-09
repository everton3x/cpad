<?php

namespace cPad\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commando de conversão
 *
 * @author everton
 */
class ConvertCommand extends Command {

    protected static $defaultName = 'convert';

    public function __construct() {
        parent::__construct();
    }

    protected function configure() {
        $this->setDescription('Converte os arquivos *.txt.')
                ->setHelp('Converte os arquivos *.txt para o formato de saída especificado.')
                ->addArgument('output', InputArgument::REQUIRED, 'Caminho para o arquivo de destino.')
                ->addArgument('input', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Caminhos para o diretório dos arquivos *.txt');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $repo = $input->getArgument('output');
        $ds = $input->getArgument('input');
        
        foreach ($ds as $item){
            $output->writeln("<info>Origem: $item</info>");
        }
        $output->writeln("<info>Destino: $repo</info>");
        
        try{
            parse($output, $repo, $ds);
        } catch (Exception $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");
            $output->writeln("<comment>{$ex->getTraceAsString()}</comment>");
        }
    }

}
