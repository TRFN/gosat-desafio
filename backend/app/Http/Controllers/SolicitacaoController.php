<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Solicitacao;
use App\Helpers\GosatHelper as GH;

class SolicitacaoController extends Controller
{
	public function registrar(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'cpf' => 'required|digits:11',
			'instituicao' => 'required|string',
			'modalidade' => 'required|string',
			'codModalidade' => 'required|string',
			'valor' => 'required|numeric|min:1',
			'jurosMes' => 'required|numeric|min:0',
			'parcelas' => 'required|integer|min:1',
		]);

		if ($validator->fails()) {
			return GH::makeResponse($validator->errors(), GH::HTTP_BAD_REQUEST);
		}

		$solicitacao = Solicitacao::create($request->only([
			'cpf',
			'instituicao',
			'modalidade',
			'codModalidade',
			'valor',
			'jurosMes',
			'parcelas'
		]));

		return GH::makeResponse($solicitacao);
	}

	public function porCpf($cpf)
	{
		return GH::makeResponse(
			Solicitacao::where('cpf', $cpf)->orderBy('created_at', 'desc')->get()
		);
	}
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
