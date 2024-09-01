<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Uma lista dos tipos de exceção que não são reportados.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        ValidationException::class,
        NotFoundHttpException::class,
        ModelNotFoundException::class,
    ];

    /**
     * Uma lista dos inputs que nunca são armazenados na sessão para exceções de validação.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registra os callbacks de tratamento de exceções para a aplicação.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Log::error('Exception occurred: ' . $e->getMessage(), [
                'exception' => $e,
                'url' => request()->url(),
                'input' => request()->except($this->dontFlash),
            ]);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Resource not found',
                ], Response::HTTP_NOT_FOUND);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Resource not found',
                ], Response::HTTP_NOT_FOUND);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'errors' => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                ], Response::HTTP_UNAUTHORIZED);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Server Error',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }

    /**
     * Converte uma exceção de autenticação em uma resposta.
     *
     * @param \Illuminate\Http\Request $request
     * @param AuthenticationException $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Converte uma exceção de validação em uma resposta.
     *
     * @param \Illuminate\Http\Request $request
     * @param ValidationException $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }
}
