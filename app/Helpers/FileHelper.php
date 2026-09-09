<?php

namespace App\Helpers;

use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

class FileHelper
{
    public static function compressAndStore($file, $folder, $width = 800, $quality = 75)
    {
        // 1. Tambahkan ini di awal untuk menaikkan limit memori khusus saat proses ini jalan
        ini_set('memory_limit', '512M');

        // Gunakan timestamp agar nama file unik dan hindari spasi
        $filename  = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $extension = strtolower($file->getClientOriginalExtension());

        $manager = new ImageManager(new Driver());

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
            // 2. Gunakan getRealPath() dengan pengecekan
            $image = $manager->read($file->getRealPath());

            // 3. Resize (scale) agar tidak terlalu besar dimensinya
            $image->scale(width: $width);

            // Ganti extension ke jpg untuk outputnya
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
            $path     = $folder . '/' . $filename;

            // 4. Logika Kompresi
            $encoded = $image->toJpeg($quality);

            // Jika ukuran masih > 1MB, turunkan kualitas secara bertahap
            // Tambahkan (string) untuk memastikan casting data biner
            while (strlen((string) $encoded) > 1024 * 1024 && $quality > 10) {
                $quality -= 10;
                $encoded = $image->toJpeg($quality);
            }

            Storage::disk('public')->put($path, (string) $encoded);
        } else {
            // Jika bukan gambar, simpan asli
            $path = $folder . '/' . $filename;
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
        }

        return $path;
    }

    public static function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
