<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Gosat",
 *     description="Documentação da API para integração com serviços externos (CPF e Ofertas)."
 * )
 *
 * @OA\Server(
 *     url="http://localhost:7001",
 *     description="API Principal"
 * )
 * 
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
 * 
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
 * 
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

class SwaggerInfo {}
