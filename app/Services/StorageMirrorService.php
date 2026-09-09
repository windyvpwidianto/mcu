<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class StorageMirrorService
{
    /**
     * Mirror storage/app/public to public/storage automatically
     *
     * @return void
     */
    public static function mirror()
    {
        $source = storage_path('app/public');
        $destination = public_path('storage');

        if (! File::exists($source)) {
            return;
        }

        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        File::copyDirectory($source, $destination);
    }
}
