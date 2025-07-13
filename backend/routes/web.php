<?php

use App\Helpers\GosatHelper as GH;
use Illuminate\Http\Request;

/**
 * @OA\Get(
 *     path="/",
 *     summary="Status da API",
 *     description="Retorna uma mensagem simples com a versão da API.",
 *     tags={"Status"},
 *     @OA\Response(
 *         response=200,
 *         description="API funcionando corretamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="response", type="string", example="API Gosat with Lumen (10.0.4) (Laravel Components ^10.0)"),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     )
 * )
 */

$router->get('/', function () {
    return GH::makeResponse('API Gosat with ' . app()->version());
});

/**
 * @OA\Get(
 *     path="/consultarCpf/{cpf}",
 *     summary="Consulta CPF",
 *     description="Valida um CPF informado e realiza uma consulta via API externa.",
 *     tags={"Consultas"},
 *     @OA\Parameter(
 *         name="cpf",
 *         in="path",
 *         required=true,
 *         description="CPF a ser consultado (apenas números).",
 *         @OA\Schema(type="string", example="12345678909")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="CPF válido e consulta realizada com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="response", type="object"),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="CPF inválido ou erro na consulta.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="response", type="string", example="CPF inválido."),
 *             @OA\Property(property="code", type="integer", example=400)
 *         )
 *     )
 * )
 */

$router->get('/consultarCpf/{cpf:.+}', function ($cpf) {
    $cpf = GH::validateCpf($cpf);

    if (!$cpf) {
        return GH::makeResponse('CPF inválido, verifique o número informado e tente novamente.', GH::HTTP_BAD_REQUEST);
    }

    return GH::callApi(GH::CONSULTA_CPF, ['cpf' => $cpf]);
});

/**
 * @OA\Post(
 *     path="/consultarOfertas",
 *     summary="Consulta Ofertas",
 *     description="Consulta ofertas financeiras a partir de um CPF, instituição e modalidade.",
 *     tags={"Consultas"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cpf", "instituicao_id", "codModalidade"},
 *             @OA\Property(property="cpf", type="string", example="12345678909"),
 *             @OA\Property(property="instituicao_id", type="integer", example=12),
 *             @OA\Property(property="codModalidade", type="string", example="123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Consulta de ofertas realizada com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="response", type="object"),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Parâmetros inválidos ou faltando.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="response", type="string", example="instituicao_id deve ser um número válido."),
 *             @OA\Property(property="code", type="integer", example=400)
 *         )
 *     )
 * )
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
