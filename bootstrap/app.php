<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
        $middleware->append(ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, \Illuminate\Http\Request $request) {
            // A verificação `is('api/*')` é uma segurança extra
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'error' => [
                        'message' => 'Os dados fornecidos são inválidos.',
                        'details' => $e->errors()
                    ]
                ], 422);
            }
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json(['error' => ['message' => 'Não autenticado.']], 401);
            }
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['error' => ['message' => 'Você não tem permissão para executar esta ação.']], 403);
            }
            if ($e instanceof  \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json(['error' => ['message' => 'O recurso solicitado não foi encontrado.']], 404);
            }

            if ($e instanceof \DomainException || $e instanceof \InvalidArgumentException) {
                return response()->json(['error' => ['message' => $e->getMessage()]], 422);
            }

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = $statusCode === 500 && !config('app.debug') ? 'Ocorreu um erro interno no servidor.' : $e->getMessage();

            return response()->json(['error' => ['message' => $message]], $statusCode);
        });

    })->create();
