<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Solicitacao;

class SolicitacaoController extends Controller
{
	public function registrar(Request $request)
	{
		$data = $request->validate([
			'cpf' => 'required|string|size:11',
			'instituicao' => 'required|string',
			'modalidade' => 'required|string',
			'codModalidade' => 'required|string',
			'valor' => 'required|numeric',
			'jurosMes' => 'required|numeric',
			'parcelas' => 'required|integer',
		]);

		$registro = Solicitacao::create($data);

		return response()->json([
			'success' => true,
			'registro' => $registro
		]);
	}

	public function listar()
	{
		$registros = Solicitacao::orderBy('created_at', 'desc')->get();

		return response()->json([
			'success' => true,
			'data' => $registros
		]);
	}
}
