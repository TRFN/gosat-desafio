<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;

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

    /* Códigos de erro para respostas */
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_SERVER_ERROR = 500;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_OK = 200;

    /* Configurações */
    private const ALLOW_NOT_REGISTERED_REQUESTS = false; // Permitir consultas de ROTAS não registradas
    private const ALLOW_INVALID_CPFS_BY_LIST = true; // Permitir consultas de CPFs inválidos por lista

    /* Consultas Possiveis */
    public const CONSULTA_CPF = 10001;
    public const CONSULTA_OFERTA = 10002;

    /**
     * Verifica se o codigo é valido.
     */

    public static function isValidCode(int $code): bool
    {
        return $code >= 100 && $code < 600;
    }

    /**
     * Valida um CPF.
     *
     * @param string $cpf O CPF a ser validado.
     * @return bool Retorna true se o CPF for válido, false caso contrário.
     */
    public static function validateCpf(string $cpf): string|bool
    {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/\D/', '', $cpf);

        // Verifica se o CPF é um dos inválidos permitidos
        if (in_array($cpf, self::$allowedInvalidCpfs) && self::ALLOW_INVALID_CPFS_BY_LIST) {
            return $cpf;
        }

        // Verifica se o CPF tem 11 dígitos e não é uma sequência repetida
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

    public static function callApi(string|int $url, array $payload, callable|int $onSuccess = -1, callable|int|string $onError = -1)
    {
        $internalApiCall = false;
        // Se for código numérico, mapeia para a URL da API
        if (is_int($url)) {
            $apiMap = [
                self::CONSULTA_CPF => ['API_CONSULTA_CPF', 'API para consulta de CPF não configurada.'],
                self::CONSULTA_OFERTA => ['API_CONSULTA_OFERTA', 'API para consulta de oferta não configurada.'],
            ];

            if (!isset($apiMap[$url])) {
                return self::makeResponse("Chamada de API não encontrada.", self::HTTP_SERVER_ERROR);
            }

            [$envKey, $errorMsg] = $apiMap[$url];
            $url = env($envKey); // Usa env diretamente, sem helper

            if (!$url) {
                return self::makeResponse($errorMsg, self::HTTP_BAD_REQUEST);
            } else {
                $internalApiCall = true;
            }
        }

        // Se chegou aqui e não é string, retorna erro
        if (!is_string($url)) {
            return self::makeResponse("Formato de URL inválido para chamada de API.", self::HTTP_SERVER_ERROR);
        }

        // Se não é uma URL registrada e não for permitido uso arbitrário
        if (!$internalApiCall && !self::ALLOW_NOT_REGISTERED_REQUESTS) {
            return self::makeResponse("Chamadas de API não registradas não são permitidas.", self::HTTP_BAD_REQUEST);
        }

        // Garante que a URL não está vazia
        if (empty($url)) {
            return self::makeResponse("URL da API não pode ser vazia.", self::HTTP_BAD_REQUEST);
        }

        if (is_int($onSuccess)) {
            $onSuccess = function ($response) use ($onSuccess) {
                return self::makeResponse($response, self::isValidCode($onSuccess) ? $onSuccess : self::HTTP_OK);
            };
        }

        if (is_int($onError)) {
            $onError = function ($error) use ($onError) {
                return self::makeResponse([
                    "return" => $error->getMessage(),
                    "code" => $error->getCode(),
                ], self::isValidCode($onError) ? $onError : self::HTTP_SERVER_ERROR);
            };
        } elseif (is_string($onError)) {
            $onError = function ($error) use ($onError) {
                return self::makeResponse([
                    "return" => $error->getMessage(),
                    "code" => $error->getCode(),
                    "details" => $onError,
                ], self::HTTP_SERVER_ERROR);
            };
        }

        try {
            $client = new Client([
                'timeout' => 10,
            ]);

            $response = $client->post($url, ['json' => $payload]);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            return $onSuccess($data);
        } catch (RequestException $e) {
            return $onError($e);
        }
    }

    public static function makeResponse($message, $status = -1): JsonResponse
    {
        if ($status == -1) {
            $status = self::HTTP_OK; // Define o status como OK se não for especificado
        }

        if (!self::isValidCode($status)) {
            $status = self::HTTP_BAD_REQUEST; // Se o status não for válido, define como BAD_REQUEST
        }

        return response()->json([
            'success' => $status === self::HTTP_OK,
            'response' => $message,
            'code' => $status,
        ])->setStatusCode($status);
    }
}
