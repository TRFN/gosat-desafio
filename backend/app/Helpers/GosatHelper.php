<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;

/**
 * Classe helper para integrar e manipular chamadas à API Gosat,
 * validação de CPF, tratamento de respostas HTTP e gerenciamento de erros.
 *
 * Essa classe encapsula a lógica para validação de CPF,
 * realização de chamadas HTTP via Guzzle com tratamento customizado,
 * além de padronizar as respostas JSON da API.
 */
class GosatHelper
{
    /**
     * Lista de CPFs inválidos permitidos para testes.
     * Esses CPFs não são válidos no mundo real, mas podem ser usados para testes internos.
     *
     * @var string[]
     */
    private static $allowedInvalidCpfs = [
        '11111111111',
        '22222222222',
        '12312312312',
    ];

    /**
     * Código HTTP para Not Found (404).
     */
    public const HTTP_NOT_FOUND = 404;

    /**
     * Código HTTP para Internal Server Error (500).
     */
    public const HTTP_SERVER_ERROR = 500;

    /**
     * Código HTTP para Unauthorized (401).
     */
    public const HTTP_UNAUTHORIZED = 401;

    /**
     * Código HTTP para Forbidden (403).
     */
    public const HTTP_FORBIDDEN = 403;

    /**
     * Código HTTP para Bad Request (400).
     */
    public const HTTP_BAD_REQUEST = 400;

    /**
     * Código HTTP para OK (200).
     */
    public const HTTP_OK = 200;

    /**
     * Configuração que permite ou não chamadas para rotas não registradas.
     *
     * @var bool
     */
    private const ALLOW_NOT_REGISTERED_REQUESTS = false;

    /**
     * Configuração que permite ou não a utilização dos CPFs inválidos da lista para testes.
     *
     * @var bool
     */
    private const ALLOW_INVALID_CPFS_BY_LIST = true;

    /**
     * Constantes para as consultas possíveis (identificadores internos).
     */
    public const CONSULTA_CPF = 10001;
    public const CONSULTA_OFERTA = 10002;

    /**
     * Verifica se um código HTTP informado é válido (entre 100 e 599).
     *
     * @param int $code Código HTTP para validação.
     * @return bool True se válido, false caso contrário.
     */
    public static function isValidCode(int $code): bool
    {
        return $code >= 100 && $code < 600;
    }

    /**
     * Valida um CPF (formato e dígitos verificadores).
     *
     * Aceita também CPFs da lista de inválidos permitidos para testes, se configurado.
     *
     * @param string $cpf CPF a ser validado.
     * @return string|bool Retorna o CPF limpo se válido, ou false se inválido.
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

    /**
     * Realiza uma chamada HTTP POST para uma API externa, com tratamento de erros e respostas padronizadas.
     *
     * Pode receber como primeiro parâmetro um identificador de consulta (int),
     * que será convertido para a URL de ambiente correspondente,
     * ou uma URL direta (string).
     *
     * @param string|int $url URL ou código identificador da API.
     * @param array $payload Dados a serem enviados na requisição POST (JSON).
     * @param callable|int $onSuccess Callback ou código HTTP para sucesso (default -1 = 200).
     * @param callable|int|string $onError Callback, código HTTP ou mensagem para erro (default -1 = 500).
     * @return JsonResponse Resposta JSON padronizada.
     */
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

    /**
     * Gera uma resposta JSON padrão para API, contendo sucesso, mensagem e código HTTP.
     *
     * @param mixed $message Mensagem ou dados da resposta.
     * @param int $status Código HTTP da resposta (default 200).
     * @return JsonResponse Objeto de resposta JSON.
     */
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
