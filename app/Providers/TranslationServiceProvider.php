<?php

namespace App\Providers;

use App\Models\Translation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Ambil locale yang sedang aktif (setelah diatur oleh Middleware)
        $locale = App::getLocale();

        // 2. Ambil data dari Cache agar performa tetap maksimal
        // Cache akan disimpan selama 24 jam (86400 detik)
        $translations = Cache::remember("translations_json_{$locale}", 86400, function () use ($locale) {
            // Tentukan kolom mana yang diambil berdasarkan bahasa aktif
            $column = ($locale === 'id') ? 'id_text' : 'en';

            // Ambil pasangan 'key' => 'hasil terjemahan'
            return Translation::pluck($column, 'key')->toArray();
        });

        // 3. Suntikkan array tersebut ke dalam Translator Laravel
        // Tanda '*' berarti ini akan menggantikan JSON loader default
        if (!empty($translations)) {
            // Gunakan setLoaded untuk mengisi data JSON secara langsung
            // Ini menghindari error explode() di fungsi addLines
            app('translator')->setLoaded([
                '*' => [
                    '*' => [
                        $locale => $translations
                    ]
                ]
            ]);
        }
    }
}
