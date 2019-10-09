<?php

/**
 * Cria o banco de dados e retorna o PDO.
 * 
 * @param string $path
 * @return PDO
 */
function writer_create(string $path): PDO {
    try {
        if (file_exists($path)) {
            if (unlink($path) === false) {
                throw new Exception("Falha ao excluir $path antido.");
            }
        }
        $pdo = new PDO("sqlite:$path");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Cria a tabela no banco de dados.
 * 
 * @param PDO Writer
 * @param string $table
 * @param SimpleXMLElement $spec
 * @return bool
 */
function writer_prepare(PDO $writer, string $table, SimpleXMLElement $spec): bool {
    try {
        /* monta a spec das colunas */
        $coldef = [];
        $cols = $spec->field;
        foreach ($cols as $col) {
            $coltype = writer_translate_col_types($col['type']);
            $coldef[] = "{$col['id']} $coltype";
        }
        $coldef = join(', ', $coldef);
        $sql = "CREATE TABLE IF NOT EXISTS $table ($coldef)";

        $writer->exec($sql);

        /* inicia a transação */
        $writer->beginTransaction();

        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Traduz o tipo de dados da coluna que consta na especificação num dos tipos suportados pelo SQLite
 * @param string $native
 * @return string
 */
function writer_translate_col_types(string $native): string {
    switch ($native) {
        case 'int':
            return 'INTEGER';
        case 'string':
            return 'TEXT';
        case 'text':
            return 'TEXT';
        case 'float':
//            return 'REAL';
//            return 'NUMERIC';
            return 'DECIMAL';
        case 'date':
            return 'TEXT';
        default :
            throw new Exception("Tipo de dados $native não suportado.");
    }
}

/**
 * Escreve os dados na tabela.
 * 
 * @param PDO $writer
 * @param string $table
 * @param array $data
 * @return bool
 */
function writer_write(PDO $writer, string $table, array $data): bool {
    try {
        $colslabel = array_map(function($value) {
            return ":$value";
        }, array_keys($data));
        $colnames = join(', ', array_keys($data));
        $colvalues = join(', ', array_values($colslabel));
        $data_prepared = [];
        foreach ($data as $k => $v) {
            $data_prepared[":$k"] = $v;
        }
        $sql = "INSERT INTO $table ($colnames) VALUES ($colvalues)";
        $stmt = $writer->prepare($sql);
        $stmt->execute($data_prepared);
        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}

/**
 * Finaliza a tabela.
 * 
 * @param PDO $writer
 * @return bool
 */
function writer_commit(PDO $writer, string $table): bool {
    try {
        return $writer->commit();
    } catch (Exception $ex) {
        $writer->rollBack();
        throw $ex;
    }
    return true;
}

/**
 * Finaliza o banco de dados.
 * 
 * @param PDO $writer
 * @return bool
 */
function writer_save(PDO $writer): bool {
    unset($writer);
    return true;
}

/**
 * Salva os meta-dados.
 * 
 * @param PDO $writer
 * @param string $table
 * @param array $meta
 * @return bool
 */
function writer_save_meta(PDO $writer, string $table, array $meta): bool {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS meta (tabela TEXT, arquivo TEXT, cnpj TEXT, data_inicial INTEGER, data_final INTEGER, data_geracao INTEGER, entidade TEXT, registros INTEGER)";
        $writer->exec($sql);

    $sql = "INSERT INTO meta (tabela, arquivo, cnpj, data_inicial, data_final, data_geracao, entidade, registros) VALUES ('$table', '{$meta['arquivo']}', '{$meta['cnpj']}', '{$meta['data_inicial']}', '{$meta['data_final']}', '{$meta['data_geracao']}', '{$meta['entidade']}', '{$meta['registros']}')";
        $writer->exec($sql);

        return true;
    } catch (Exception $ex) {
        throw $ex;
    }
}
