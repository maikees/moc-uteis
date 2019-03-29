<?php

namespace MOCUtils\Helpers;

/**
 * Password
 */
class Password
{
    public function encrypt($password, $salt = null)
    {
        if (!$salt) {
            $salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
        }

        $password = hash('sha512', $password . $salt);

        return [
            'salt' => $salt,
            'password' => $password
        ];
    }

    public static function create()
    {
        return new self;
    }

    public static function generate($password, $salt = null)
    {
        if (!$salt) {
            $salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
        }

        $password = hash('sha512', $password . $salt);

        return [
            'salt' => $salt,
            'password' => $password
        ];
    }

    public static function generatePlain($tamanho = 8, $maiusculas = true, $numeros = true, $minusculas = false, $simbolos = false)
    {
        $lmin = 'abcdefghijklmnopqrstuvwxyz';
        $lmai = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $num = '1234567890';
        $simb = '!@#$%*/+-._';
        $retorno = '';
        $caracteres = '';

        if ($minusculas) $caracteres .= $lmin;
        if ($maiusculas) $caracteres .= $lmai;
        if ($numeros) $caracteres .= $num;
        if ($simbolos) $caracteres .= $simb;

        $len = strlen($caracteres);

        for ($n = 1; $n <= $tamanho; $n++) {
            $rand = mt_rand(1, $len);
            $retorno .= $caracteres[$rand - 1];
        }

        return $retorno;
    }
}
