<?php

/**
 * Cria/limpa o diretório fornecido e retorna o caminho para o diretório.
 *
 * @param string $path
 * @return string
 */
function writer_create(string $path): string {
    try {
		$dirname = dirname($path).DIRECTORY_SEPARATOR.basename($path, '.csv').DIRECTORY_SEPARATOR;

        if (file_exists($dirname)) {
            foreach(($directory = new DirectoryIterator($dirname)) as $item){
				if($item->isFile()){
					unlink($item->getPathname());
				}
			}
        }else{
			$mkdir = mkdir($dirname, 0777, true);

			// var_dump($mkdir);exit();
		}

        return $dirname;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Cria o arquivo CSV.
 *
 * @param string Writer
 * @param string $table
 * @param SimpleXMLElement $spec
 * @return bool
 */
function writer_prepare(string $writer, string $table, SimpleXMLElement $spec): bool {
    try {
        //print_r($spec);exit();

        /* monta o cabeçalho */
		$header = [];
		foreach($spec->field as $col){
			$header[] = $col['id'];
		}

        /* cria o arquivo */
		$fh = fopen("$writer$table.csv", 'a');
		if(fputcsv($fh, $header, ';') === false){
			throw new Exception("Não foi possível preparar o arquivo pra $table.");
		}

		fclose($fh);

        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Escreve os dados no arquivo.
 *
 * @param string $writer
 * @param string $table
 * @param array $data
 * @return bool
 */
function writer_write(string $writer, string $table, array $data): bool {
    try {
		static $fwriter = null;

		if(is_null($fwriter)){
			$fwriter = fopen("$writer$table.csv", 'a');
		}

		/* percorre todos os campos transformando float em string */

		array_walk_recursive($data, function(&$item, $key){
			if(is_float($item)){
				$item = number_format($item, 2, ',', '.');
			}
		});

		if(fputcsv($fwriter, $data, ';') === false){
			throw new Exception("Não foi possível gravar os dados de $table.");
		}

        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Não faz nada.
 *
 * @param string $writer
 * @return bool
 */
function writer_commit(string $writer, string $table): bool {
    try {
		return true;
    } catch (Exception $ex) {
        throw $ex;
    }
    return true;
}

/**
 * Não faz nada
 *
 * @param string $writer
 * @return bool
 */
function writer_save(string $writer): bool {
    return true;
}

/**
 * Salva os meta-dados.
 *
 * @param string $writer
 * @param string $table
 * @param array $meta
 * @return bool
 */
function writer_save_meta(string $writer, string $table, array $meta): bool {
    try {
		//print_r($meta);exit();
		$file = $writer.'meta.csv';

		array_unshift($meta, $table);

		$header = [];
		if(!file_exists($file)){
			//array_unshift($meta, 'tabela', 'arquivo', 'cnpj', 'data_inicial', 'data_final', 'data_geracao', 'entidade', 'registros');
			$header = ['tabela', 'arquivo', 'cnpj', 'data_inicial', 'data_final', 'data_geracao', 'entidade', 'registros'];
		}

		$fh = fopen($file, 'a');

		if($header){
			if(fputcsv($fh, $header, ';') === false){
				throw new Exception("Não foi possível gravar o cabeçalho dos meta-dados.");
			}
		}

		if(fputcsv($fh, $meta, ';') === false){
			throw new Exception("Não foi possível gravar os meta-dados de $table");
		}

        fclose($fh);

        return true;

    } catch (Exception $ex) {
        throw $ex;
    }
}
