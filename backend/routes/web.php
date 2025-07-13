<?php

use App\Helpers\GosatHelper as GH;
use Illuminate\Http\Request;

$router->get('/', function () {
	return GH::makeResponse('API Gosat with ' . app()->version());
});

$router->get('/docs', function () {
	// Futuramente sera o caminho para a documentação
});

$router->get('/consultarCpf/{cpf:.+}', function ($cpf) {
	$cpf = GH::validateCpf($cpf);

	if (!$cpf) {
		return GH::makeResponse('CPF inválido, verifique o número informado e tente novamente.', GH::HTTP_BAD_REQUEST);
	}

	return GH::callApi(GH::CONSULTA_CPF, ['cpf' => $cpf]);
});

$router->post('/consultarOfertas', function (Request $post) {
	$cpf = GH::validateCpf($post->input('cpf'));
	$instituicao_id = $post->input('instituicao_id');
	$codModalidade = $post->input('codModalidade');

	if (!$cpf) {
		return GH::makeResponse('CPF inválido.', GH::HTTP_BAD_REQUEST);
	}

	if (!is_numeric($instituicao_id) || $instituicao_id <= 0) {
		return GH::makeResponse('instituicao_id deve ser um número válido.', GH::HTTP_BAD_REQUEST);
	}

	if (!is_string($codModalidade) || trim($codModalidade) === '') {
		return GH::makeResponse('codModalidade deve ser uma string não vazia.', GH::HTTP_BAD_REQUEST);
	}

	// Monta payload com segurança
	$payload = [
		'cpf' => $cpf,
		'instituicao_id' => (int)$instituicao_id,
		'codModalidade' => trim($codModalidade),
	];

	return GH::callApi(GH::CONSULTA_OFERTA, $payload);
});
