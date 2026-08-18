<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-command environment health check. Run and paste the output:
 *
 * php artisan app:doctor
 */
class AppDoctor extends Command
{
 protected $signature = 'app:doctor';

 protected $description = 'Check the environment for common Task365 startup problems';

 public function handle(): int
 {
 $fail = 0;

 $this->info('=== Task365 environment doctor ===');
 $this->line('PHP: '.PHP_VERSION.' ('.PHP_OS_FAMILY.')');
 $this->line('Laravel: '.\Illuminate\Foundation\Application::VERSION);
 $this->line('Env: '.app()->environment());
 $this->line('Debug: '.(config('app.debug') ? 'ON' : 'OFF'));
 $this->line('');

 // 1. APP_KEY
 if (config('app.key')) {
 $this->info('[OK] APP_KEY is set');
 } else {
 $this->error('[FAIL] APP_KEY is missing - run: php artisan key:generate');
 $fail++;
 }

 // 2. Storage writability
 foreach ([storage_path('logs'), storage_path('framework/cache'), storage_path('framework/sessions'), storage_path('framework/views')] as $dir) {
 if (! is_dir($dir)) {
 @mkdir($dir, 0775, true);
 }
 if (is_writable($dir)) {
 $this->info('[OK] '.str_replace(base_path(), '', $dir).' writable');
 } else {
 $this->error('[FAIL] '.$dir.' not writable - chmod -R 775 storage');
 $fail++;
 }
 }

 // 3. Database connection + migrations
 try {
 DB::connection()->getPdo();
 $this->info('[OK] Database connected ('.config('database.default').': '.config('database.connections.'.config('database.default').'.database').')');
 } catch (\Throwable $e) {
 $this->error('[FAIL] Database connection: '.$e->getMessage());
 $fail++;
 }

 if ($fail === 0 || true) {
 try {
 $applied = DB::table('migrations')->pluck('migration')->values();
 $files = collect(glob(database_path('migrations/*.php')))->map(fn ($f) => basename($f, '.php'))->sort()->values();
 $missing = $files->diff($applied);
 if ($missing->isEmpty()) {
 $this->info('[OK] All migrations applied ('.count($files).' total)');
 } else {
 $this->error('[FAIL] Pending migrations (run: php artisan migrate):');
 $missing->each(fn ($m) => $this->error(' - '.$m));
 $fail++;
 }
 } catch (\Throwable $e) {
 $this->error('[FAIL] Could not read migrations table: '.$e->getMessage().' (run: php artisan migrate)');
 $fail++;
 }
 }

 // 4. Session driver + cookie configuration
 $this->line(' Session driver: '.config('session.driver'));
 $this->line(' Session cookie: '.config('session.cookie')
 .' | secure: '.(config('session.secure') ? 'YES' : 'no')
 .' | domain: '.(config('session.domain') ?: 'host-only'));
 if (config('session.driver') === 'database') {
 try {
 DB::table('sessions')->limit(1)->get();
 $this->info('[OK] sessions table reachable');
 } catch (\Throwable $e) {
 $this->error('[FAIL] sessions table: '.$e->getMessage().' (run: php artisan migrate)');
 $fail++;
 }
 }

 if (config('session.secure')) {
 $this->error('[FAIL] SESSION_SECURE_COOKIE is true - the session cookie is only sent over HTTPS, so http://127.0.0.1 logins never persist (you stay on the login page). Remove it or set SESSION_SECURE_COOKIE=false for local dev.');
 $fail++;
 }

 if (config('session.domain')) {
 $this->error('[FAIL] SESSION_DOMAIN='.config('session.domain').' - the cookie is scoped to that domain and will NOT be sent to 127.0.0.1. Remove SESSION_DOMAIN (or set it to 127.0.0.1) for local dev.');
 $fail++;
 }

 if (app()->environment('production') && ! config('app.debug')) {
 $this->warn('[WARN] APP_ENV=production with APP_DEBUG=false hides error details. For local development use APP_ENV=local and APP_DEBUG=true (then /dev/session and /dev/error diagnostics work).');
 }

 // 5. Queue driver
 $this->line(' Queue driver: '.config('queue.default'));

 // 6. Tenant seed check
 try {
 $tenants = DB::table('tenants')->count();
 $users = DB::table('users')->count();
 $this->line(' Tenants in DB: '.$tenants.' | Users: '.$users);
 if ($users === 0) {
 $this->error('[FAIL] No users exist - run: php artisan db:seed (demo: admin@demo.com / password123)');
 $fail++;
 }
 } catch (\Throwable $e) {
 $this->error('[FAIL] Could not read tenants/users: '.$e->getMessage().' (run: php artisan migrate --seed)');
 $fail++;
 }

 // 6b. Tenant context resolution (the classic "Target class [currentTenant]" bug)
 try {
 $tenant = app('currentTenant');
 $this->info("[OK] app('currentTenant') resolves (".($tenant?->slug ?? 'null').')');
 } catch (\Throwable $e) {
 $this->error("[FAIL] app('currentTenant') throws: ".$e->getMessage());
 $fail++;
 }

 // 7. Login event log (last 6) - shows exactly what the login flow did
 $log = storage_path('logs/laravel.log');
 if (is_file($log)) {
 $lines = array_filter(file($log), fn ($l) => preg_match('/Login (success|failed)|Super admin login|Post-login setup/', $l));
 $recent = array_slice(array_values($lines), -6);
 if ($recent) {
 $this->line('--- Last login events ---');
 foreach ($recent as $line) {
 $this->line(' '.substr(trim($line), 0, 240));
 }
 }
 }

 // 8. Recent log errors (last 5 ERROR/CRITICAL lines)
 $log = storage_path('logs/laravel.log');
 if (is_file($log)) {
 $lines = array_filter(file($log), fn ($l) => preg_match('/\.(ERROR|CRITICAL|EMERGENCY):/', $l));
 $recent = array_slice(array_values($lines), -5);
 if ($recent) {
 $this->warn('--- Last errors in laravel.log ---');
 foreach ($recent as $line) {
 $this->warn(' '.substr(trim($line), 0, 300));
 }
 } else {
 $this->info('[OK] No recent ERROR/CRITICAL in laravel.log');
 }
 }

 $this->line('');
 if ($fail > 0) {
 $this->error("Finished with {$fail} problem(s).");

 return self::FAILURE;
 }

 $this->info('All checks passed check-circle');

 return self::SUCCESS;
 }
}
