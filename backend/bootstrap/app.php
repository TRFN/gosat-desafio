<?php

// Autoload do Composer: carrega automaticamente classes e dependências
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente a partir do arquivo .env
(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

// Define o timezone da aplicação com fallback para UTC
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Criação da Instância do Lumen
|--------------------------------------------------------------------------
| Cria a aplicação base, que funciona como um contêiner IoC e roteador.
| A instância do Lumen gerencia todos os componentes do app.
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

// Ativa suporte a Facades (como DB::, Config:: etc)
$app->withFacades();

// Ativa o Eloquent ORM para manipulação de banco de dados via modelos
$app->withEloquent();

// Carrega configurações de banco de dados a partir de config/database.php
$app->configure('database');

/*
|--------------------------------------------------------------------------
| Bindings do Contêiner de Injeção de Dependência
|--------------------------------------------------------------------------
| Aqui registramos implementações para interfaces do framework.
| Isso inclui o manipulador de exceções e o kernel de comandos (CLI).
*/

// Manipulador de exceções global
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Kernel da linha de comando (responsável por migrations, jobs, etc)
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->configure('app');

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

// Retorna a instância configurada da aplicação
return $app;
