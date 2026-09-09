<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpiWorkflow extends Model
{
    protected $fillable = [
        'role',
        'from_status',
        'to_status',
        'from_inisial',
        'to_inisial',
        'validate_transition'
    ];

    /**
     * Memeriksa apakah transisi status diperbolehkan untuk role tertentu.
     */
    public static function isValidTransition($from, $to, $role): bool
    {
        return self::where('from_status', $from)
            ->where('to_status', $to)
            ->where('role', $role)
            ->exists();
    }

    /**
     * Mengambil daftar tombol aksi (transisi) yang tersedia berdasarkan status saat ini dan role user.
     */
    public static function getAvailableTransitions(string $fromStatus, string $role): array
    {
        return self::where('from_status', $fromStatus)
            ->where('role', $role)
            ->pluck('to_status', 'to_inisial') // 'to_inisial' sebagai label tombol, 'to_status' sebagai value
            ->unique()
            ->toArray();
    }

    /**
     * Mengambil daftar User ID Moderator yang berhak mendapatkan notifikasi berdasarkan data WPI.
     */
    public static function getModeratorsForStatus(string $status, WpiReport $wpi): array
    {
        // Secara default, notifikasi dikirim saat status pertama kali disubmit.
        // Sesuaikan 'Submitted' dengan case yang Anda gunakan di seeder.
        if (strtolower($status) !== 'submitted') {
            return [];
        }

        // Mencari moderator yang relevan di tabel ModeratorAssignment
        $moderatorIds = ModeratorAssignment::where('event_type_id', 5)
            ->where(function ($query) use ($wpi) {

                // Kriteria 1: Penugasan bersifat umum (global untuk tipe event ini)
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria 2: Penugasan spesifik berdasarkan Department laporan
                if ($wpi->department_id) {
                    $query->orWhere('department_id', $wpi->department_id);
                }

                // Kriteria 3: Penugasan spesifik berdasarkan Contractor laporan
                if ($wpi->contractor_id) {
                    $query->orWhere('contractor_id', $wpi->contractor_id);
                }
            })
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        return $moderatorIds;
    }
}
