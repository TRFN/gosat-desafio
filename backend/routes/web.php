<?php

use App\Helpers\GosatHelper as GH;
use Illuminate\Http\Request;

/**
 * Rota raiz da API
 * Retorna uma mensagem simples com a versão da aplicação
 */
$router->get('/', function () {
	return GH::makeResponse('API Gosat with ' . app()->version());
});

/**
 * Consulta informações de um CPF informado
 *
 * @param string $cpf - CPF no caminho da URL
 * @return JSON com os dados consultados ou erro de validação
 */
$router->get('/consultarCpf/{cpf:.+}', function ($cpf) {
	$cpf = GH::validateCpf($cpf);

	if (!$cpf) {
		return GH::makeResponse('CPF inválido, verifique o número informado e tente novamente.', GH::HTTP_BAD_REQUEST);
	}

	// Chama a API de consulta de CPF
	return GH::callApi(GH::CONSULTA_CPF, ['cpf' => $cpf]);
});

/**
 * Consulta ofertas financeiras com base em CPF, instituição e modalidade
 *
 * Espera um JSON via POST com:
 * - cpf: string
 * - instituicao_id: int
 * - codModalidade: string|int (opcional)
 *
 * Valida os dados e retorna o resultado da API de ofertas
 */
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

	// Monta payload com segurança
	$payload = [
		'cpf' => $cpf,
		'instituicao_id' => (int)$instituicao_id,
		'codModalidade' => $codModalidade,
	];

	// Chama a API de consulta de ofertas
	return GH::callApi(GH::CONSULTA_OFERTA, $payload);
});

/**
 * Rota para registrar uma solicitação de empréstimo
 * Controlador: SolicitacaoController@registrar
 * Espera dados via POST
 */
$router->post('/solicitarEmprestimo', 'SolicitacaoController@registrar');

/**
 * Lista solicitações de empréstimo associadas a um CPF
 *
 * @param string $cpf - CPF no caminho da URL
 * Controlador: SolicitacaoController@porCpf
 */
$router->get('/solicitacoesPorCpf/{cpf}', 'SolicitacaoController@porCpf');

/**
 * Remove uma solicitação com base no ID
 *
 * @param int $id - ID da solicitação
 * Método: DELETE
 * Controlador: SolicitacaoController@destroy
 */
$router->delete('/solicitacoes/{id}', 'SolicitacaoController@destroy');
