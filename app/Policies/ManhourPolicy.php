<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Manhour;

class ManhourPolicy
{
    /**
     * Cek apakah user boleh melihat daftar manhours (index).
     */
    public function viewAny(User $user): bool
    {
        // Admin bisa lihat semua
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }

        // Contractor hanya jika punya relasi contractor
        return $user->contractors()->exists();
    }

    /**
     * Cek apakah user boleh melihat detail manhour tertentu.
     */
    public function view(User $user, Manhour $manhour): bool
    {
        // Admin bisa lihat semua
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }

        // Contractor hanya bisa lihat jika nama contractor pada manhour
        // ada di daftar contractor user tsb
        return $user->contractors()
            ->where('company', $manhour->contractor_name)
            ->exists();
    }

    /**
     * Buat manhour baru.
     */
    public function create(User $user): bool
    {
        // Admin boleh
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }

        // Contractor user boleh kalau punya relasi contractor
        return $user->contractors()->exists();
    }
    public function viewAdmin(User $user)
    {
        // Admin bisa lihat semua
        if ($user->roles()->where('role_id', 1)->exists()) {
            return true;
        }
    }
}
