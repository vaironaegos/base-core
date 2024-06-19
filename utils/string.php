<?php

declare(strict_types=1);

use Astrotech\Core\Base\Exception\RuntimeException;

if (!function_exists('stripAccents')) {
    function stripAccents(string $value): string
    {
        return strtr(
            $value,
            'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
            'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY'
        );
    }
}

if (!function_exists('dashesToCamelCase')) {
    function dashesToCamelCase(string $string, bool $capitalizeFirst = false): string
    {
        $string = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirst) {
            $string = lcfirst($string);
        }

        return $string;
    }
}

if (!function_exists('underscoreToCamelCase')) {
    function underscoreToCamelCase(string $string, bool $capitalizeFirst = false): string
    {
        $string = str_replace('_', '', ucwords($string, '_'));

        if (!$capitalizeFirst) {
            $string = lcfirst($string);
        }

        return $string;
    }
}

if (!function_exists('camelCaseToUnderscores')) {
    function camelCaseToUnderscores(string $string, bool $capitalizeFirst = false): string
    {
        $string = mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));

        if (!$capitalizeFirst) {
            $string = lcfirst($string);
        }

        return $string;
    }
}

if (!function_exists('getFileNameFromFullPath')) {
    function getFileNameFromFullPath(string $fullPath): string
    {
        $fileName = explode(DIRECTORY_SEPARATOR, $fullPath);
        return end($fileName);
    }
}

if (!function_exists('getStringFileExtension')) {
    function getStringFileExtension(string $fileName): string
    {
        $n = strrpos($fileName, ".");
        return ($n === false) ? "" : substr($fileName, $n + 1);
    }
}

if (!function_exists('imagePathToBase64')) {
    function imagePathToBase64(string $path): string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return "data:image/{$type};base64," . base64_encode($data);
    }
}

if (!function_exists('isUuidString')) {
    function isUuidString(mixed $term): bool
    {
        $pattern = '/^[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/';
        return (bool)preg_match($pattern, (string)$term) !== false;
    }
}

if (!function_exists('convertToBool')) {
    function convertToBool(int|string|bool $term): bool
    {
        if ($term === true || $term === false) {
            return $term;
        }

        $trueValues = ['1', 1, 'true', 't', 'T'];
        $falseValues = ['0', 0, 'false', 'f', 'F'];

        if (in_array($term, $trueValues)) {
            return true;
        }

        if (in_array($term, $falseValues)) {
            return false;
        }

        throw new RuntimeException("Invalid boolean value '{$term}'");
    }
}

if (!function_exists('onlyNumbers')) {
    function onlyNumbers(string $term): string
    {
        return preg_replace('/[^0-9]/', "", $term);
    }
}

if (!function_exists('maskDocument')) {
    function maskDocument($val, $mask): string
    {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }
}

if (!function_exists('convertDecimalToLongString')) {
    function convertDecimalToLongString($valor = 0)
    {
        $singular = ["centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão"];
        $plural = ["centavos", "reais", "mil", "milhões", "bilhões", "trilhões", "quatrilhões"];

        $c = ["", "cem", "duzentos", "trezentos", "quatrocentos", "quinhentos", "seiscentos", "setecentos",
            "oitocentos", "novecentos"];
        $d = ["", "dez", "vinte", "trinta", "quarenta", "cinquenta", "sessenta", "setenta", "oitenta", "noventa"];
        $d10 = ["dez", "onze", "doze", "treze", "quatorze", "quinze", "dezesseis", "dezesete", "dezoito", "dezenove"];
        $u = ["", "um", "dois", "três", "quatro", "cinco", "seis", "sete", "oito", "nove"];

        $z = 0;

        $valor = number_format($valor, 2, ".", ".");
        $inteiro = explode(".", $valor);
        for ($i = 0; $i < count($inteiro); $i++) {
            for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++) {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }

        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," 😉
        $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
        for ($i = 0; $i < count($inteiro); $i++) {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count($inteiro) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ($valor == "000") {
                $z++;
            } elseif ($z > 0) {
                $z--;
            }
            if (($t == 1) && ($z > 0) && ($inteiro[0] > 0)) {
                $r .= (($z > 1) ? " de " : "") . $plural[$t];
            }
            if ($r) {
                if (isset($rt)) {
                    $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim)
                            ? ", " : " e ") : " ") . $r;
                    continue;
                }

                $rt = ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ?
                        (($i < $fim) ? ", " : " e ") : " ") . $r;
            }
        }

        return ($rt ? $rt : "zero");
    }
}

if (!function_exists('env')) {
    function env(string $name, mixed $default = null): mixed
    {
        $value = getenv($name);

        if (!$value) {
            $value = $_ENV[$name] ?? false;
        }

        if (!$value) {
            $value = $_SERVER[$name] ?? false;
        }

        return $value !== false ? $value : $default;
    }
}

if (!function_exists('randomString')) {
    function randomString(int $length = 16): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
