<?php

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Cria o banco de dados e retorna o PDO.
 * 
 * @param string $path
 * @return Spreadsheet
 */
function writer_create(string $path): Spreadsheet {
    try {
        if (file_exists($path)) {
            if (unlink($path) === false) {
                throw new Exception("Falha ao excluir $path antido.");
            }
        }

//        global $xlsxSavePath;
//        $xlsxSavePath = $path;
//        $writer = new Spreadsheet();
//
//        $pool = new ApcuCachePool();
//        $cache = new SimpleCacheBridge($pool);
//        Settings::setCache($cache);

//        $client = new \Memcache();
//        $client->connect('localhost', 11211);
//        $pool = new \Cache\Adapter\Memcache\MemcacheCachePool($client);
//        $simpleCache = new \Cache\Bridge\SimpleCache\SimpleCacheBridge($pool);
//        \PhpOffice\PhpSpreadsheet\Settings::setCache($simpleCache);

        return $writer;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Cria a tabela no banco de dados.
 * 
 * @param Spreadsheet Writer
 * @param string $table
 * @param SimpleXMLElement $spec
 * @return bool
 */
function writer_prepare(Spreadsheet $writer, string $table, SimpleXMLElement $spec): bool {
    try {
//        $writer->disconnectWorksheets();
        $colsSpec = $spec->field;
        foreach ($colsSpec as $col) {
            $cols[] = $col['id'];
        }
        $ws = new Worksheet($writer, $table);
        $ws->fromArray($cols, null, 'A1');
        $writer->addSheet($ws);
        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Escreve os dados na tabela.
 * 
 * @param Spreadsheet $writer
 * @param string $table
 * @param array $data
 * @return bool
 */
function writer_write(Spreadsheet $writer, string $table, array $data): bool {
    try {
        static $actualTable = '';
        static $lineno = 2;
        if ($actualTable !== $table) {
            $actualTable = $table;
            $lineno = 2;
        }
        $ws = $writer->getSheetByName($table);
        $ws->fromArray($data, null, "A$lineno");
        $lineno++;
        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Finaliza a tabela.
 * 
 * @param Spreadsheet $writer
 * @return bool
 */
function writer_commit(Spreadsheet $writer, string $table): bool {
    return true;
}

/**
 * Finaliza o banco de dados.
 * 
 * @param Spreadsheet $writer
 * @return bool
 */
function writer_save(Spreadsheet $writer): bool {
    try {
        global $xlsxSavePath;
        $xlsx = new Xlsx($writer);
        $xlsx->save($xlsxSavePath);
        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Salva os meta-dados.
 * 
 * @param Spreadsheet $writer
 * @param string $table
 * @param array $meta
 * @return bool
 */
function writer_save_meta(Spreadsheet $writer, string $table, array $meta): bool {
    try {
        static $lineno = 2;
        if ($writer->getSheetByName('meta') === null) {
            $ws = new Worksheet($writer, 'meta');
            $writer->addSheet($ws);
        } else {
            $ws = $writer->getSheetByName('meta');
        }

        $ws->fromArray([
            'tabela', 'arquivo', 'cnpj', 'data_inicial', 'data_final', 'data_geracao', 'entidade', 'registros'
                ], null, 'A1');
        $ws->fromArray(array_merge([$table], $meta), null, "A$lineno");
        $lineno++;

        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}
