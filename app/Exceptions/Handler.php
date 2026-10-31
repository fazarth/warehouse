<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Prefer readable Blade error pages (e.g. 403) over silent/default English shells.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
            && $e->getStatusCode() === 403
            && ! $request->expectsJson()
            && view()->exists('errors.403')
        ) {
            return response()->view('errors.403', [
                'exception' => $e,
            ], 403);
        }

        return parent::render($request, $e);
    }
}
