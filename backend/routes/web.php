<?php

use App\Helpers\GosatHelper as GH;

$router->get('/', function () {
    return GH::makeResponse('API Gosat with ' . app()->version());
});

$router->get('/consultarCpf/{cpf:.+}', function ($cpf) {
    $cpf = GH::validateCpf($cpf);

    if (!$cpf) {
        return GH::makeResponse('CPF inválido, verifique o número informado e tente novamente.', GH::HTTP_BAD_REQUEST);
    }

    return GH::callApi(GH::CONSULTA_CPF, ['cpf' => $cpf]);
});
