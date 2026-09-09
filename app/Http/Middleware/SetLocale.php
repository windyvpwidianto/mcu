<?php

namespace App\Http\Middleware;

use App\Models\Translation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Tentukan locale dari session atau default
        $locale = session('locale', config('app.locale'));
        app()->setLocale($locale);

        // 2. Ambil data terjemahan dari Database (dengan Cache)
        $translations = cache()->remember("translations_json_{$locale}", 86400, function () use ($locale) {
            $column = ($locale === 'id') ? 'id_text' : 'en';
            return \App\Models\Translation::pluck($column, 'key')->toArray();
        });

        // 3. Suntikkan langsung ke Translator (Cara ini aman dari error 'Undefined array key 1')
        if (!empty($translations)) {
            app('translator')->setLoaded([
                '*' => [
                    '*' => [
                        $locale => $translations
                    ]
                ]
            ]);
        }

        return $next($request);
    }
}
