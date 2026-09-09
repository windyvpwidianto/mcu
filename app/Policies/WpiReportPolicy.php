<?php

namespace App\Policies;

use App\Models\WpiReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;

class WpiReportPolicy
{
    /**
     * Izinkan semua user yang login untuk melihat list (filter dilakukan di query)
     */
    public function viewAny(User $user): bool
    {
        return auth()->check();
    }

    /**
     * Aturan detail siapa yang bisa melihat laporan spesifik
     */
    public function view(User $user, WpiReport $wpiReport): bool
    {
        // 1. ✅ Admin (role_id = 1) selalu punya akses penuh
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }

        // 2. ✅ Pembuat Laporan (Submitter) bisa melihat
        if ($wpiReport->created_by === $user->id) {
            return true;
        }

        // 3. ✅ Assigned ERMs bisa melihat (Relasi Many-to-Many lewat tabel pivot)
        if ($wpiReport->assignedErms()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // 4. ✅ Moderator (Berdasarkan Department/Contractor)
        // WPI biasanya memiliki EventType tersendiri (Asumsi ID untuk WPI tetap)
        $isAssignedModerator = $user->moderatorAssignments()
            ->where(function (Builder $query) use ($wpiReport) {
                // Kriteria A: Penugasan umum (Global Moderator)
                $query->whereNull('department_id')
                      ->whereNull('contractor_id');

                // Kriteria B: Penugasan spesifik untuk Department
                if ($wpiReport->department_id) {
                    $query->orWhere('department_id', $wpiReport->department_id);
                }

                // Kriteria C: Penugasan spesifik untuk Contractor
                if ($wpiReport->contractor_id) {
                    $query->orWhere('contractor_id', $wpiReport->contractor_id);
                }
            })
            ->exists();

        if ($isAssignedModerator) {
            return true;
        }

        return false;
    }

    /**
     * Siapa yang bisa membuat laporan baru
     */
    public function create(User $user): bool
    {
        // Semua user terdaftar biasanya diizinkan melapor
        return auth()->check();
    }

    /**
     * Siapa yang bisa mengedit laporan
     */
    public function update(User $user, WpiReport $wpiReport): bool
    {
        // Jangan izinkan edit jika status sudah final (Closed/Cancelled)
        if ($wpiReport->isClosed()) {
            return false;
        }

        // Admin selalu bisa
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }

        // Pembuat laporan bisa edit selama status masih 'Draft' atau 'Submitted' (tergantung alur Anda)
        if ($wpiReport->created_by === $user->id && in_array($wpiReport->status, ['Submitted', 'Draft'])) {
            return true;
        }

        // ERM yang ditugaskan bisa mengedit untuk mengisi tindak lanjut
        if ($wpiReport->assignedErms()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Siapa yang bisa menghapus laporan
     */
    public function delete(User $user, WpiReport $wpiReport): bool
    {
        // Biasanya hanya Admin atau Moderator tertentu
        return $user->roles()->where('role_id', 1)->exists();
    }
}
