<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitacao extends Model
{
	protected $table = 'solicitacoes';

	protected $fillable = [
		'cpf',
		'instituicao',
		'modalidade',
		'codModalidade',
		'valor',
		'jurosMes',
		'parcelas',
	];
}
