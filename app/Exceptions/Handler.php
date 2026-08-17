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
        $this->reportable(function (BikriBookApiException $e) {
            logger()->error('BikriBook API error', [
                'message' => $e->getMessage(),
                'context' => $e->context(),
            ]);
        });

        $this->reportable(function (BikriBookAuthException $e) {
            logger()->error('BikriBook authentication error', ['message' => $e->getMessage()]);
        });

        $this->reportable(function (TenantNotFoundException $e) {
            logger()->warning('Tenant not found', ['message' => $e->getMessage()]);
        });

        $this->reportable(function (SubscriptionExpiredException $e) {
            logger()->warning('Subscription expired', ['message' => $e->getMessage()]);
        });
    }
}
