<?php

namespace App\Policies;

use App\Models\Hazard;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;

class HazardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Hazard $hazard): bool
    {
        // 1. ✅ Admin (role_id = 1) selalu bisa mengakses
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }

        // 2. ✅ Penanggung jawab bisa melihat
        // Asumsi relasi penanggungJawab mengarah ke model User
        if ($hazard->penanggungJawab && $user->id === $hazard->penanggungJawab->id) {
            return true;
        }

        // 3. ✅ Pelapor bisa melihat
        // Asumsi relasi pelapor mengarah ke model User
        if ($hazard->pelapor && $user->id === $hazard->pelapor->id) {
            return true;
        }

        // 4. ✅ Assigned ERM bisa melihat (berdasarkan hazard_erm_assignments)
        if ($hazard->assignedErms()->wherePivot('erm_id', $user->id)->exists()) {
            return true;
        }
        $isAssignedEerm = $user->ermAssignments()
            ->where(function (Builder $query) use ($hazard) {

                // Kriteria A: Penugasan bersifat umum (tidak spesifik pada Department/Contractor)
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria B: Penugasan spesifik untuk Department
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }

                // Kriteria C: Penugasan spesifik untuk Contractor
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->exists();

        // 5. ✅ Moderator (Logika Diperbarui)
        // Moderator hanya bisa akses jika penugasannya cocok dengan Hazard (EventType + Department/Contractor)
        $isAssignedModerator = $user->moderatorAssignments()
            ->where('event_type_id', $hazard->event_type_id)
            ->where(function (Builder $query) use ($hazard) {

                // Kriteria A: Penugasan bersifat umum (tidak spesifik pada Department/Contractor)
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria B: Penugasan spesifik untuk Department
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }

                // Kriteria C: Penugasan spesifik untuk Contractor
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->exists();

        if ($isAssignedModerator) {
            return true;
        }
        if ($isAssignedEerm) {
            return true;
        }

        // 6. ❌ Jika tidak memenuhi semua kondisi di atas, akses ditolak
        return false;
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Hazard $hazard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Hazard $hazard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Hazard $hazard): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Hazard $hazard): bool
    {
        return false;
    }
}
