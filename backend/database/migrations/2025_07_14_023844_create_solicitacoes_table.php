<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitacoesTable extends Migration
{
    public function up()
    {
        Schema::create('solicitacoes', function (Blueprint $table) {
            $table->id();
            $table->string('cpf');
            $table->string('instituicao');
            $table->string('modalidade');
            $table->string('codModalidade');
            $table->decimal('valor', 10, 2);
            $table->decimal('jurosMes', 5, 4);
            $table->integer('parcelas');
            $table->timestamps(); // created_at e updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('solicitacoes');
    }
}
