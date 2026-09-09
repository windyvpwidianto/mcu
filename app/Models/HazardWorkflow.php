<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HazardWorkflow extends Model
{
    protected $table = 'hazard_workflows';
    protected $fillable = ['from_status', 'to_status', 'role', 'from_inisial', 'to_inisial'];

    public static function isValidTransition($from, $to, $role): bool
    {
        return self::where('from_status', $from)
            ->where('to_status', $to)
            ->where('role', $role)
            ->exists();
    }

    public static function getAvailableTransitions(string $fromStatus, string $role): array
    {
        return self::where('from_status', $fromStatus)
            ->where('role', $role)
            ->pluck('to_status', 'to_inisial')
            ->unique()
            ->toArray();
    }
    public static function getModeratorsForStatus(string $status, Hazard $hazard): array
    {
        // Secara default, hanya kirim notifikasi saat laporan baru disubmit.
        // Anda dapat menambahkan logika status lain di sini jika diperlukan.
        if ($status !== 'submitted') {
            return [];
        }

        // Jika status adalah 'submitted', kita cari semua moderator yang relevan.

        $moderatorIds = ModeratorAssignment::where('event_type_id', $hazard->event_type_id)
            ->where(function ($query) use ($hazard) {

                // Kriteria 1: Penugasan bersifat umum (hanya berdasarkan event_type_id)
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria 2: Penugasan spesifik untuk Department laporan
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }

                // Kriteria 3: Penugasan spesifik untuk Contractor laporan
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        return $moderatorIds;
    }

    public static function getErmModerators(Hazard $hazard): array
    {
        // Kita mencari user berdasarkan Department atau Contractor dari laporan Hazard
        $moderatorIds = ErmAssignment::query()
            ->where(function ($query) use ($hazard) {

                // Kriteria 1: Penugasan bersifat global (tidak terikat dep/cont)
                // Opsional: Hapus bagian ini jika ERM harus selalu spesifik
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria 2: Cocok dengan Department laporan
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }

                // Kriteria 3: Cocok dengan Contractor laporan
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        return $moderatorIds;
    }
}
