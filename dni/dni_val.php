<?php

function validateSpanishID($str)
{
    $dniRegex = '/^(\d{8})([A-Z])$/';
    $cifRegex = '/^([ABCDEFGHJKLMNPQRSUVW])(\d{7})([0-9A-J])$/';
    $nieRegex = '/^[XYZ]\d{7,8}[A-Z]$/';

    if (preg_match($dniRegex, $str)) {
        $idType = 'dni';
    } elseif (preg_match($cifRegex, $str)) {
        $idType = 'cif';
    } elseif (preg_match($nieRegex, $str)) {
        $idType = 'nie';
    } else {
        $idType = null;
    }

    if ($idType === 'dni') {
        $valid = validDNI($str);
    } elseif ($idType === 'nie') {
        $valid = validNIE($str);
    } elseif ($idType === 'cif') {
        $valid = validCIF($str);
    } else {
        $valid = false;
    }

    return ['type' => $idType, 'valid' => $valid];
}

function validDNI($dni)
{
    $dniLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';
    // Extraer el número del DNI sin la letra
    $number = substr($dni, 0, -1);
    // Calcular la letra correspondiente al número del DNI
    $calculatedLetter = $dniLetters[intval($number) % 23];
    // Comparar la letra calculada con la letra del DNI proporcionado
    return $calculatedLetter === $dni[strlen($dni) - 1];
}

function validNIE($nie)
{
    $niePrefix = $nie[0];
    switch ($niePrefix) {
        case 'X':
            $niePrefix = 0;
            break;
        case 'Y':
            $niePrefix = 1;
            break;
        case 'Z':
            $niePrefix = 2;
            break;
    }
    return validDNI($niePrefix . substr($nie, 1));
}

function validCIF($cif)
{
    $control = 'JABCDEFGHI';
    $number = substr($cif, 1, 7);
    $controlChar = substr($cif, -1);

    if (preg_match('/^[ABCDEFGHJNPQRSUVW]$/', $cif[0])) {
        $sumA = 0;
        $sumB = 0;
        for ($i = 0; $i < 7; $i++) {
            $digit = intval($number[$i]);
            if ($i % 2 == 0) { // Posiciones pares de la cadena (pero impares en índice 0)
                $tmp = (2 * $digit);
                $tmp = $tmp > 9 ? array_sum(str_split((string) $tmp)) : $tmp;
                $sumA += $tmp;
            } else { // Posiciones impares de la cadena
                $sumB += $digit;
            }
        }
        $total = $sumA + $sumB;
        $digitControl = ($total % 10) == 0 ? 0 : 10 - ($total % 10);

        return $controlChar == $control[$digitControl] || $controlChar == strval($digitControl);
    }

    if ($cif[0] == 'K' || $cif[0] == 'L' || $cif[0] == 'M') {
        $dniLetters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $calculatedLetter = $dniLetters[intval($number) % 23];
        return $controlChar === $calculatedLetter;
    }

    return false;
}