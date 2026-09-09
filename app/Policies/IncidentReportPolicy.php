<?php

namespace App\Policies;

use App\Models\IncidentReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class IncidentReportPolicy
{
    // /**
    //  * Determine whether the user can view any models.
    //  */
    // public function viewAny(User $user): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can view the model.
    //  */
    // public function view(User $user, IncidentReport $incidentReport): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can create models.
    //  */
    // public function create(User $user): bool
    // {
    //     return false;
    // }

    /**
     * Logic Super Admin / Administrator HSE
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }
        return null; // Lanjut ke check method di bawah
    }
    /**
     * BAGIAN 1 & 2: Detil & Pihak Terlibat
     * Akses: Pelapor (Originator) & Tim Investigasi
     */
    public function updateInitialData(User $user, IncidentReport $incident): bool
    {
        // Hanya bisa diedit jika status BELUM Closed
        if ($incident->status === 'Closed') {
            return false;
        }

        return $user->id === $incident->pelapor_id ||
            $this->isInvestigator($user, $incident);
    }
    /**
     * BAGIAN 3 - 6: Analisis & Investigasi (PEEPO, Timeline, Checklist)
     * Akses: Khusus Tim Investigasi
     */
    public function conductInvestigation(User $user, IncidentReport $incident): bool
    {
        return $this->isInvestigator($user, $incident);
    }
    /**
     * BAGIAN 7: Tindakan Perbaikan
     * Akses: Tim Investigasi (Full) & Assignee (Update Tgl Selesai)
     */
    public function manageCorrectiveActions(User $user, IncidentReport $incident): bool
    {
        $isAssignee = $incident->correctiveActions()->where('pic_user_id', $user->id)->exists();

        return $this->isInvestigator($user, $incident) || $isAssignee;
    }

    /**
     * BAGIAN 8: Kunci Pembelajaran
     * Akses: Tim Investigasi
     */
    public function updateLessonsLearned(User $user, IncidentReport $incident): bool
    {
        return $this->isInvestigator($user, $incident);
    }

    /**
     * BAGIAN 9: Penerimaan & Komentar Peninjau
     * Akses: Manager HSE, Superintendent, atau Management
     */
    public function reviewReport(User $user, IncidentReport $incident): bool
    {
        return $user->hasAnyRole(['Manager HSE', 'Superintendent', 'General Manager']);
    }
    public function updateTeamInvestigation(User $user, IncidentReport $incident): bool
    {
        return $user->moderatorAssignments()
            ->where('event_type_id', $incident->event_type_id)
            ->where(function (Builder $query) use ($incident) {
                // Gunakan nested WHERE untuk memastikan logika OR tidak bocor ke event_type_id
                $query->where(function ($q) {
                    // Kriteria A: Penugasan bersifat Global (Super Moderator untuk Event ini)
                    $q->whereNull('department_id')
                        ->whereNull('contractor_id');
                });

                // Kriteria B: Spesifik ke Department insiden
                if ($incident->department_id) {
                    $query->orWhere('department_id', $incident->department_id);
                }

                // Kriteria C: Spesifik ke Contractor insiden
                if ($incident->contractor_id) {
                    $query->orWhere('contractor_id', $incident->contractor_id);
                }
            })
            ->exists();
    }

    /**
     * Helper: Cek apakah user bagian dari Tim Investigasi
     */
    private function isInvestigator(User $user, IncidentReport $incident): bool
    {
        return $incident->investigationTeams()->where('user_id', $user->id)->exists();
    }


    // /**
    //  * Determine whether the user can update the model.
    //  */
    // public function update(User $user, IncidentReport $incidentReport): bool
    // {
    //     // 1. Admin HSE selalu boleh
    //     if ($user->hasRole('Administrator')) return true;

    //     // 2. Jika laporan masih Open, Pembuat (Originator) bisa edit Part 1-2
    //     if ($incidentReport->status === 'Open' && $user->id === $incidentReport->pelapor_id) {
    //         return true;
    //     }

    //     // 3. Tim Investigasi yang terdaftar di Part 3
    //     // Cek apakah user ID ada dalam relasi investigationTeams
    //     if ($incidentReport->investigationTeams()->where('user_id', $user->id)->exists()) {
    //         return true;
    //     }
    //     return false;
    // }
    // public function close(User $user, IncidentReport $incident)
    // {
    //     $users = $user->hasAnyRole(['Manager HSE', 'Superintendent']);

    //     if ($users) {
    //         return true;
    //     }
    //     return false;
    // }

    // /**
    //  * Determine whether the user can delete the model.
    //  */
    // public function delete(User $user, IncidentReport $incidentReport): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can restore the model.
    //  */
    // public function restore(User $user, IncidentReport $incidentReport): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, IncidentReport $incidentReport): bool
    // {
    //     return false;
    // }
}
