<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;

/**
 * Classe helper para integrar e manipular chamadas à API Gosat,
 * validação de CPF, tratamento de respostas HTTP e gerenciamento de erros.
 *
 * Funções principais:
 * - Validar CPF
 * - Encapsular chamadas HTTP via Guzzle
 * - Padronizar respostas JSON
 */
class GosatHelper
{
    /**
     * Lista de CPFs inválidos permitidos para testes.
     * Esses CPFs não são válidos no mundo real, mas podem ser utilizados em ambientes de homologação.
     *
     * @var string[]
     */
    private static $allowedInvalidCpfs = [
        '11111111111',
        '22222222222',
        '12312312312',
    ];

    // Códigos HTTP padrão
    public const HTTP_OK = 200;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_SERVER_ERROR = 500;

    // Identificadores internos para rotas da API
    public const CONSULTA_CPF = 10001;
    public const CONSULTA_OFERTA = 10002;

    /**
     * Permite ou não chamadas para URLs arbitrárias (não registradas).
     */
    private const ALLOW_NOT_REGISTERED_REQUESTS = false;

    /**
     * Permite uso de CPFs inválidos (mas listados) para testes.
     */
    private const ALLOW_INVALID_CPFS_BY_LIST = true;

    /**
     * Verifica se um código HTTP está dentro da faixa válida.
     *
     * @param int $code
     * @return bool
     */
    public static function isValidCode(int $code): bool
    {
        return $code >= 100 && $code < 600;
    }

    /**
     * Valida um CPF (remove caracteres não numéricos e verifica dígitos verificadores).
     * Aceita CPFs inválidos da lista, se configurado.
     *
     * @param string $cpf
     * @return string|bool
     */
    public static function validateCpf(string $cpf): string|bool
    {
        $cpf = preg_replace('/\D/', '', $cpf); // Remove tudo que não for número

        if (in_array($cpf, self::$allowedInvalidCpfs) && self::ALLOW_INVALID_CPFS_BY_LIST) {
            return $cpf;
        }

        // Verifica tamanho e se é repetido
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Valida dígitos verificadores
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

    /**
     * Realiza uma chamada POST para uma API externa, com tratamento completo de erro e sucesso.
     *
     * @param string|int $url - Pode ser uma URL ou um identificador interno (ex: CONSULTA_CPF)
     * @param array $payload - Dados enviados como JSON no corpo da requisição
     * @param callable|int $onSuccess - Código HTTP ou callback executado em sucesso
     * @param callable|int|string $onError - Código HTTP, callback ou mensagem para erro
     * @return JsonResponse
     */
    public static function callApi(string|int $url, array $payload, callable|int $onSuccess = -1, callable|int|string $onError = -1)
    {
        $internalApiCall = false;

        // Se for um código numérico, mapeia para variável de ambiente
        if (is_int($url)) {
            $apiMap = [
                self::CONSULTA_CPF => ['API_CONSULTA_CPF', 'API para consulta de CPF não configurada.'],
                self::CONSULTA_OFERTA => ['API_CONSULTA_OFERTA', 'API para consulta de oferta não configurada.'],
            ];

            if (!isset($apiMap[$url])) {
                return self::makeResponse("Chamada de API não encontrada.", self::HTTP_SERVER_ERROR);
            }

            [$envKey, $errorMsg] = $apiMap[$url];
            $url = env($envKey);

            if (!$url) {
                return self::makeResponse($errorMsg, self::HTTP_BAD_REQUEST);
            } else {
                $internalApiCall = true;
            }
        }

        // Se não é string (URL) válida
        if (!is_string($url)) {
            return self::makeResponse("Formato de URL inválido para chamada de API.", self::HTTP_SERVER_ERROR);
        }

        // Bloqueia chamadas arbitrárias se não permitido
        if (!$internalApiCall && !self::ALLOW_NOT_REGISTERED_REQUESTS) {
            return self::makeResponse("Chamadas de API não registradas não são permitidas.", self::HTTP_BAD_REQUEST);
        }

        if (empty($url)) {
            return self::makeResponse("URL da API não pode ser vazia.", self::HTTP_BAD_REQUEST);
        }

        // Normaliza $onSuccess
        if (is_int($onSuccess)) {
            $onSuccess = function ($response) use ($onSuccess) {
                return self::makeResponse($response, self::isValidCode($onSuccess) ? $onSuccess : self::HTTP_OK);
            };
        }

        // Normaliza $onError
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
                'timeout' => 10, // Tempo limite da requisição
            ]);

            // Envia POST com payload JSON
            $response = $client->post($url, ['json' => $payload]);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            return $onSuccess($data);
        } catch (RequestException $e) {
            return $onError($e);
        }
    }

    /**
     * Gera uma resposta padronizada da API no formato JSON.
     *
     * @param mixed $message - Dados ou mensagem de retorno
     * @param int $status - Código HTTP (default: 200)
     * @return JsonResponse
     */
    public static function makeResponse($message, $status = -1): JsonResponse
    {
        // Define status default como 200 (OK)
        if ($status == -1) {
            $status = self::HTTP_OK;
        }

        // Garante que o status seja válido
        if (!self::isValidCode($status)) {
            $status = self::HTTP_BAD_REQUEST;
        }

        return response()->json([
            'success' => $status === self::HTTP_OK,
            'response' => $message,
            'code' => $status,
        ])->setStatusCode($status);
    }
}
