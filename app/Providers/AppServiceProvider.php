<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public function register(): void
    {
        if (config('app.env') === 'production') {
            $this->app['request']->server->set('HTTPS', true);
        }
        $this->app->bind('path.public', function () {
            return realpath(base_path() . '/../public_html');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // === START: PERBAIKAN MIXED CONTENT UNTUK LIVEWIRE ===
        // Memaksa Laravel menggunakan skema HTTPS saat membuat URL
        // karena lingkungan cPanel/proxy sering tidak menyampaikan header HTTPS dengan benar.
        // APP_ENV diatur ke 'local' di .env Anda, jadi kita gunakan kondisi ini,
        // atau gunakan 'production' jika Anda sudah mengubahnya.
        if (config('app.env') === 'local' || config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        // === END: PERBAIKAN MIXED CONTENT ===

        if (file_exists(base_path('routes/breadcrumbs.php'))) {
            require_once base_path('routes/breadcrumbs.php');
        }
        App::setLocale(Session::get('locale', config('app.locale')));
        Blade::if('role', function ($roles) {
            $user = Auth::user();
            if (!$user) return false;

            $roles = is_array($roles) ? $roles : [$roles];
            // Matikan Clockwork secara global agar aman

            // ✅ Kasus: single role (role_id)
            if (method_exists($user, 'role') && $user->role) {
                if (in_array($user->role->name, $roles)) {
                    return true;
                }
            }

            // ✅ Kasus: multiple role (pivot)
            if (method_exists($user, 'roles') && $user->roles()->whereIn('name', $roles)->exists()) {
                return true;
            }

            return false;
        });
    }
}
