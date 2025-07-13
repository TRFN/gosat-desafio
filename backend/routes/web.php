<?php

use App\Helpers\GosatHelper as GH;

$router->get('/', function () {
    return GH::makeResponse('API Gosat with ' . app()->version());
});


$router->get('/consultarCpf/{cpf:.+}', function ($cpf) {
    $cpf = GH::validateCpf($cpf);

    if (!$cpf) {
        return GH::makeResponse('CPF inválido, verifique o número informado e tente novamente.', 400);
    }

    // return GH::makeResponse('CPF válido: ' . $cpf, 200); // Debugging

    // Pega a URL de consulta no environment
    $apiUrl = GH::env(
        'API_CONSULTA_CPF', // Variável de ambiente
        'API para consulta de CPF não configurada. Verifique as configurações e tente novamente.' // Retorno de erro se não estiver definida
    );

    // Faz a requisição POST para a API externa com Guzzle
    $client = new \GuzzleHttp\Client();

    try {
        $response = $client->post($apiUrl, [
            'json' => [
                'cpf' => $cpf,
            ],
            // Tempo limite para a requisição
            'timeout' => 10,
        ]);

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        // Retorna a resposta JSON para o cliente que chamou sua rota
        return GH::makeResponse($data);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Em caso de erro na requisição externa
        return GH::makeResponse('Erro ao consultar API externa: ' . $e->getMessage(), 500);
    }
});
