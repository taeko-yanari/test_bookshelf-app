<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $levels = [
        \Illuminate\Database\Eloquent\ModelNotFoundException::class => 'info',
        \Illuminate\Auth\Access\AuthorizationException::class => 'warning',
        \Illuminate\Auth\AuthenticationException::class => 'info',
        \Illuminate\Validation\ValidationException::class => 'info',
        \Illuminate\Session\TokenMismatchException::class => 'warning',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
        if ($request->is('api/*')) {
            return response()->json([
                'error' => '書籍が見つかりません。',
            ], 404);
        }
    });
    }
}
