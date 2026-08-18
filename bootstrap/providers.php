<?php

/*
|--------------------------------------------------------------------------
| Laravel Framework Service Providers
|--------------------------------------------------------------------------
|
| The framework's core providers are listed here EXPLICITLY. Normally they
| are auto-registered via Composer package discovery
| (vendor/composer/installed.php -> extra.laravel.providers), but when that
| discovery data is missing or stale (e.g. after switching major framework
| versions without regenerating vendor metadata), services like the Gate
| contract (Illuminate\Contracts\Auth\Access\Gate), the auth guards and the
| session manager never get bound, producing:
|
|   Target [Illuminate\Contracts\Auth\Access\Gate] is not instantiable
|   while building [App\Providers\AppServiceProvider]
|
| Duplicates with package discovery are safe: the provider repository
| de-duplicates by class name and Application::register() returns the
| already-registered instance for repeat registrations.
|
*/

return [

    // Framework core providers...
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Cookie\CookieServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Pipeline\PipelineServiceProvider::class,
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,
    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,

    // Application Service Providers...
    App\Providers\AppServiceProvider::class,

];
