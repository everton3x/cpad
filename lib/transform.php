<?php

function valor_com_sinal(string $data): float {
    $sinal = (int) substr($data, -1, 1);
//    $inteiro = (int) substr($data, 0, strlen($data) - 3);
    $modulo = (int) substr($data, 0, strlen($data) - 2);
//    $decimal = (int) substr($data, strlen($data)-3, strlen($data)-1);
    return (float) ($sinal . round($modulo / 2, 2));
}

function valor_sem_sinal(string $data): float {
//    $inteiro = (int) substr($data, 0, strlen($data)-2);
//    $decimal = (int) substr($data, -2);
//    return (float) round("$inteiro.$decimal", 2);
    return round(((int) $data) / 100, 2);
}

function transpor_zeros_a_direita(string $data): string {
    $str = '';
    $control = true;
    for ($i = 0; $i < strlen($data); $i++) {
        if ($control) {
            if ($data[$i] !== '0') {
                $control = false;
                $str .= $data[$i];
            }
        } else {
            $str .= $data[$i];
        }
    }

    return str_pad($str, strlen($data), '0', STR_PAD_RIGHT);
}

function formata_conta_contabil(string $data): string {
    //0.0.0.0.0.00.00.00.00.00
    $data = transpor_zeros_a_direita($data);
    $n1 = substr($data, 0, 1);
    $n2 = substr($data, 1, 1);
    $n3 = substr($data, 2, 1);
    $n4 = substr($data, 3, 1);
    $n5 = substr($data, 4, 1);
    $n6 = substr($data, 5, 2);
    $n7 = substr($data, 7, 2);
    $n8 = substr($data, 9, 2);
    $n9 = substr($data, 11, 2);
    $n10 = substr($data, 13, 2);

    return "$n1.$n2.$n3.$n4.$n5.$n6.$n7.$n8.$n9.$n10";
}

function formata_nro(string $data): string {
    //0.0.0.0.00.0.0.00.00.00
    $data = transpor_zeros_a_direita($data);
    if ($data[0] == '9') {
        $n0 = substr($data, 0, 1);
        $n1 = substr($data, 1, 1);
        $n2 = substr($data, 2, 1);
        $n3 = substr($data, 3, 1);
        $n4 = substr($data, 4, 1);
        $n5 = substr($data, 5, 2);
        $n6 = substr($data, 7, 1);
        $n7 = substr($data, 8, 1);
        $n8 = substr($data, 9, 2);
        $n9 = substr($data, 11, 2);
        $n10 = substr($data, 13, 2);
        return "$n0.$n1.$n2.$n3.$n4.$n5.$n6.$n7.$n8.$n9.$n10";
    } else {
        $n1 = substr($data, 0, 1);
        $n2 = substr($data, 1, 1);
        $n3 = substr($data, 2, 1);
        $n4 = substr($data, 3, 1);
        $n5 = substr($data, 4, 2);
        $n6 = substr($data, 6, 1);
        $n7 = substr($data, 7, 1);
        $n8 = substr($data, 8, 2);
        $n9 = substr($data, 10, 2);
        $n10 = substr($data, 12, 2);
        return "$n1.$n2.$n3.$n4.$n5.$n6.$n7.$n8.$n9.$n10";
    }
}

function formata_ndo(string $data): string {
    //00 00 00 00 00 00 00
    $data = transpor_zeros_a_direita($data);
    $n1 = substr($data, 0, 2);
    $n2 = substr($data, 2, 2);
    $n3 = substr($data, 4, 2);
    $n4 = substr($data, 6, 2);
    $n5 = substr($data, 8, 2);
    $n6 = substr($data, 10, 2);
    $n7 = substr($data, 12, 2);

    return "$n1 $n2 $n3 $n4 $n5 $n6 $n7";
}

function formata_elemento(string $data): string {
    //00 00 00
    $data = transpor_zeros_a_direita($data);
    $n1 = substr($data, 0, 2);
    $n2 = substr($data, 2, 2);
    $n3 = substr($data, 4, 2);

    return "$n1 $n2 $n3";
}
