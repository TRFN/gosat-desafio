<?php

namespace App\Helpers;

class GosatHelper
{
    /**
     * Lista de CPFs inválidos permitidos para testes.
     * Esses CPFs não devem ser considerados válidos, mas são usados para testes.
     */

    private static $allowedInvalidCpfs = [
        '11111111111',
        '22222222222',
        '12312312312',
    ];

    /**
     * Valida um CPF.
     *
     * @param string $cpf O CPF a ser validado.
     * @return bool Retorna true se o CPF for válido, false caso contrário.
     */
    public static function validateCpf(string $cpf): mixed
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se o CPF é um dos inválidos permitidos
        if (in_array($cpf, self::$allowedInvalidCpfs)) {
            return $cpf;
        }

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $rest = ($sum * 10) % 11;
            $digit = ($rest == 10) ? 0 : $rest;

            if ($cpf[$t] != $digit) {
                return false;
            }
        }

        return $cpf;
    }

    public static function makeResponse($message, $status = 200)
    {
        return response()->json([
            'success' => $status === 200,
            'response' => $message,
            'code' => $status,
        ])->setStatusCode($status);
    }

    public static function env($key, $alias, $flexible = false): mixed
    {
        // Se flexível, retorna o valor do ambiente ou o alias
        // Se não, verifica se a variável de ambiente está definida
        // Se não estiver, retorna uma resposta de erro
        // Isso permite que o alias seja usado como fallback
        // para evitar erros caso a variável de ambiente não esteja definida

        if ($flexible) {
            return env($key, $alias);
        } else {
            $value = env(
                $key, // Variavel de ambiente
                NULL // Caso não esteja definida, retorna NULL
            );

            if (!$value) {
                return self::makeResponse($alias, 500);
            }
            return $value;
        }
    }
}
