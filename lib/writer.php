<?php

/**
 * Seleciona o writer adequado.
 *
 * @param string $path
 * @return void
 */
function writer_select(string $path): void {
    try{

    $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch (strtolower($ext)) {
            case 'db':
                require_once 'writer/sqlite3.php';
                break;
            case 'csv':
                require_once 'writer/csv.php';
                break;
            default :
                throw new Exception("Extensão $ext sem suporte.");
        }
    } catch (Exception $ex) {
        throw $ex;
    }
}
