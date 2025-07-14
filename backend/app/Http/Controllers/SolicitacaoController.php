<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Solicitacao;
use App\Helpers\GosatHelper as GH;

/**
 * Controlador responsável por registrar, listar e remover solicitações de empréstimo.
 */
class SolicitacaoController extends Controller
{
	/**
	 * Registra uma nova solicitação de empréstimo no banco de dados.
	 *
	 * Espera os seguintes campos via POST:
	 * - cpf: string (11 dígitos, obrigatório)
	 * - instituicao: string (nome da instituição, obrigatório)
	 * - modalidade: string (nome da modalidade, obrigatório)
	 * - codModalidade: string (código da modalidade, obrigatório)
	 * - valor: float (valor solicitado, mínimo 1)
	 * - jurosMes: float (taxa de juros mensal, mínimo 0)
	 * - parcelas: int (quantidade de parcelas, mínimo 1)
	 *
	 * Valida os dados, cria o registro e retorna a resposta JSON.
	 */
	public function registrar(Request $request)
	{
		// Validação dos campos obrigatórios
		$validator = Validator::make($request->all(), [
			'cpf' => 'required|digits:11',
			'instituicao' => 'required|string',
			'modalidade' => 'required|string',
			'codModalidade' => 'required|string',
			'valor' => 'required|numeric|min:1',
			'jurosMes' => 'required|numeric|min:0',
			'parcelas' => 'required|integer|min:1',
		]);

		// Se falhar, retorna erros de validação
		if ($validator->fails()) {
			return GH::makeResponse($validator->errors(), GH::HTTP_BAD_REQUEST);
		}

		// Cria a solicitação com os campos permitidos
		$solicitacao = Solicitacao::create($request->only([
			'cpf',
			'instituicao',
			'modalidade',
			'codModalidade',
			'valor',
			'jurosMes',
			'parcelas'
		]));

		// Retorna a solicitação registrada
		return GH::makeResponse($solicitacao);
	}

	/**
	 * Retorna todas as solicitações associadas a um determinado CPF.
	 *
	 * @param string $cpf - CPF a ser consultado
	 * @return JSON com as solicitações (ordenadas por data de criação, mais recente primeiro)
	 */
	public function porCpf($cpf)
	{
		return GH::makeResponse(
			Solicitacao::where('cpf', $cpf)->orderBy('created_at', 'desc')->get()
		);
	}

	/**
	 * Remove uma solicitação com base no ID informado.
	 *
	 * @param int $id - ID da solicitação a ser removida
	 * @return JSON com mensagem de sucesso ou erro caso não exista
	 */
	public function destroy($id)
	{
		$solicitacao = Solicitacao::find($id);

		if (!$solicitacao) {
			return GH::makeResponse('Não encontrado', GH::HTTP_BAD_REQUEST);
		}

		$solicitacao->delete();
		return GH::makeResponse('Solicitação removida com sucesso');
	}
}
