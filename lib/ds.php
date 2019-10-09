<?php

/**
 * Retorna uma lista com os arquivos *.txt
 * 
 * @param string $path
 * @return array
 * @throws Exception
 */
function ds_get_files(string $path): array {
    try {
        $list = [];
        $it = new DirectoryIterator($path);
        foreach ($it as $item) {
            if (!$it->isDot() && $item->isFile()) {
                if (strtolower($item->getExtension()) === 'txt') {
                    $list[] = $item->getPathname();
                }
            }
        }

        return $list;
    } catch (Exception $ex) {
        throw $ex;
    }
}

function ds_get_meta(string $path): array {
    try {
        $handle = fopen($path, 'r');
        
        /* processa o cabeçalho */
        $header = fgets($handle);
        $meta['cnpj'] = substr($header, 0, 14);
        $meta['data_inicial'] = mktime(0, 0, 0, substr($header, 16, 2), substr($header, 14, 2), substr($header, 18, 4));
        $meta['data_final'] = mktime(0, 0, 0, substr($header, 24, 2), substr($header, 22, 2), substr($header, 26, 4));
        $meta['data_geracao'] = mktime(0, 0, 0, substr($header, 32, 2), substr($header, 30, 2), substr($header, 34, 4));
        $meta['entidade'] = trim(substr($header, 38, 80));

        /* encontra o número de registros */
        $counter = 0;
        while (($buffer = fgets($handle)) !== false) {
            if (strtoupper(substr($buffer, 0, 11)) === 'FINALIZADOR') {
                $meta['registros'] = (int) substr($buffer, 11, 20);
                break;
            }
            $counter++;
        }
        
        if($counter !== $meta['registros']){
            throw new Exception("Número de linhas de dados ($counter) difere do número de registros do finalizador ({$meta['registros']}) para $path");
        }
        
        return $meta;
        
    } catch (Exception $ex) {
        throw $ex;
    }
}
