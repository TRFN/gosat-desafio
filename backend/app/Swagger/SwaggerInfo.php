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
 *             @OA\Property(property="codModalidade", type="any", example="123")
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
 *  * @OA\Post(
 *     path="/solicitarEmprestimo",
 *     summary="Registrar solicitação de empréstimo",
 *     description="Registra uma nova solicitação de empréstimo com base nos dados fornecidos.",
 *     tags={"Solicitações"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"cpf", "instituicao", "modalidade", "codModalidade", "valor", "jurosMes", "parcelas"},
 *             @OA\Property(property="cpf", type="string", example="12345678901"),
 *             @OA\Property(property="instituicao", type="string", example="Banco XPTO"),
 *             @OA\Property(property="modalidade", type="string", example="crédito pessoal"),
 *             @OA\Property(property="codModalidade", type="string", example="MOD001"),
 *             @OA\Property(property="valor", type="number", format="float", example=5000),
 *             @OA\Property(property="jurosMes", type="number", format="float", example=0.0495),
 *             @OA\Property(property="parcelas", type="integer", example=12)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Solicitação registrada com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="response", type="object", example={"id":1,"cpf":"12345678901","instituicao":"Banco XPTO","modalidade":"crédito pessoal","codModalidade":"MOD001","valor":5000,"jurosMes":0.0495,"parcelas":12}),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Parâmetros inválidos.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="response", type="object", example={"cpf": {"O campo cpf é obrigatório."}}),
 *             @OA\Property(property="code", type="integer", example=400)
 *         )
 *     )
 * )
 *
 * @OA\Get(
 *     path="/solicitacoesPorCpf/{cpf}",
 *     summary="Listar solicitações por CPF",
 *     description="Retorna todas as solicitações registradas associadas ao CPF informado.",
 *     tags={"Solicitações"},
 *     @OA\Parameter(
 *         name="cpf",
 *         in="path",
 *         required=true,
 *         description="CPF a ser consultado (somente números)",
 *         @OA\Schema(type="string", example="12345678901")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de solicitações retornada com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="response", type="array", @OA\Items(type="object")),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="CPF inválido ou erro ao buscar dados.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="response", type="string", example="Não foi possível recuperar as solicitações."),
 *             @OA\Property(property="code", type="integer", example=400)
 *         )
 *     )
 * )
 *
 * @OA\Delete(
 *     path="/solicitacoes/{id}",
 *     summary="Remover solicitação",
 *     description="Remove uma solicitação de empréstimo pelo ID.",
 *     tags={"Solicitações"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID da solicitação a ser removida",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Solicitação removida com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="response", type="string", example="Solicitação removida com sucesso"),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Solicitação não encontrada.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="response", type="string", example="Não encontrado"),
 *             @OA\Property(property="code", type="integer", example=400)
 *         )
 *     )
 * )
 */

class SwaggerInfo {}
