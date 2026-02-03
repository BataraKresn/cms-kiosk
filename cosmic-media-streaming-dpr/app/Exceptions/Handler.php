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

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (str_contains(request()->path(), 'remote-control')) {
                \Log::error('remote-control exception: ' . get_class($e) . ' - ' . $e->getMessage(), [
                    'path' => request()->path(),
                    'method' => request()->method(),
                    'exception' => $e,
                ]);
            }
        });
    }
}
