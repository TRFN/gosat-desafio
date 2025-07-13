<?php

// Autoload do Composer: carrega automaticamente classes e dependências
require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\ResponseFactory as RoutingResponseFactory;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Routing\RouteCollection;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory as ViewFactory;

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
$app->withFacades(true, [
    Response::class => 'Response',
    DB::class => 'DB',
]);


// ViewFactory fake para satisfazer dependência
$app->singleton(ViewFactory::class, function () {
    return new class implements ViewFactory {
        public function make($view, $data = [], $mergeData = []) {}
        public function exists($view)
        {
            return false;
        }
        public function file($path, $data = [], $mergeData = []) {}
        public function share($key, $value = null) {}
        public function composer($views, $callback) {}
        public function creator($views, $callback) {}
        public function addNamespace($namespace, $hints) {}
        public function replaceNamespace($namespace, $hints) {}
    };
});

// UrlGenerator com RouteCollection vazio (compatível com Laravel)
$app->singleton('url', function ($app) {
    return new UrlGenerator(new RouteCollection(), $app->make(Request::class));
});

// Redirector (necessário para ResponseFactory)
$app->singleton('redirect', function ($app) {
    return new Redirector($app->make('url'));
});

// ResponseFactory para habilitar response()->json()
$app->singleton(ResponseFactory::class, function ($app) {
    return new RoutingResponseFactory(
        $app->make(ViewFactory::class),
        $app->make('redirect')
    );
});


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
], function ($router) use ($app) {
    require __DIR__ . '/../routes/web.php';
});

// Retorna a instância configurada da aplicação
return $app;
