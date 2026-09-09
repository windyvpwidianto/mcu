<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // 1. Normalisasi Data Kunci
        $employeeId = $row['employee_id'] ?? null;
        $email = $row['email'] ?? null;
        $username = $row['username'] ?? null;
        $name = $row['name'] ?? null;

        $normalizedEmployeeId = !empty($employeeId) ? trim((string)$employeeId) : null;
        $normalizedEmail = !empty($email) ? strtolower(trim($email)) : null;
        $normalizedUsername = !empty($username) ? strtolower(trim($username)) : null;
        $normalizedName = !empty($name) ? trim($name) : null;


        // 2. Lakukan PENCARIAN EKSPLISIT (Prioritas: ID > Email > Username > Nama)
        $user = null;

        // PRIORITAS 1: Cari berdasarkan Employee ID
        if (!empty($normalizedEmployeeId)) {
            $user = User::where('employee_id', $normalizedEmployeeId)->first();
        }

        // PRIORITAS 2: Cari berdasarkan Email
        if (is_null($user) && !empty($normalizedEmail)) {
            $user = User::where('email', $normalizedEmail)->first();
        }

        // PRIORITAS 3: Cari berdasarkan Username
        if (is_null($user) && !empty($normalizedUsername)) {
            $user = User::where('username', $normalizedUsername)->first();
        }

        // PRIORITAS 4: Cari berdasarkan Name
        if (is_null($user) && !empty($normalizedName)) {
            $user = User::where('name', $normalizedName)->first();
        }

        // 3. Jika user tidak ditemukan, buat instance baru
        if (is_null($user)) {
             $user = new User();
             $user->exists = false;
        }


        // 4. Siapkan Data Dasar yang Akan Disisipkan atau Diperbarui
        // SEMUA KOLOM MASUK DI SINI, TERMASUK USERNAME
        $dataToUpdate = [
            'name'                => $normalizedName,
            'email'               => $normalizedEmail,
            'gender'              => $this->mapGender($row['gender'] ?? null),
            'date_birth'          => $this->parseDate($row['date_birth'] ?? null),
            'department_name'     => $row['department_name'] ?? null,
            'employee_id'         => $normalizedEmployeeId,
            'date_commenced'      => $this->parseDate($row['date_commenced'] ?? null),
            'pilih_divisi'             => $row['pilih_divisi'] ?? null,
            'role_id'             => $row['role_id'] ?? null,
            'updated_at'          => now(),
            'username'            => $normalizedUsername,
        ];


        // 5. Logika Update atau Insert
        if ($user->exists) {
            // Data DITEMUKAN (OVERWRITE SEMUA DATA LAMA)

            // Password tetap kondisional (hanya jika kosong di DB)
            // Ganti 'password_kolom' dengan nama header yang benar di Excel Anda
            if (empty($user->password) && !empty($row['password_kolom'])) {
                 $user->password = $row['password_kolom'];
            }

            // Kolom lainnya (termasuk username) akan DITIMPA/DI-REPLACE
            // karena semua kolom ada di $dataToUpdate
            $user->fill($dataToUpdate);
            $user->save();
            return $user;

        } else {
            // Data BARU (INSERT)

            $dataToUpdate['created_at'] = now();

            // Tambahkan password untuk data baru (Plain Text)
            if (!empty($row['password_kolom'])) {
                $dataToUpdate['password'] = $row['password_kolom'];
            } else {
                 $dataToUpdate['password'] = 'default_password';
            }

            // Lakukan INSERT
            return User::create($dataToUpdate);
        }
    }

    // --- Metode Pembantu (parseDate, mapGender, rules) ---

    private function parseDate($value)
    {
        if (empty($value) || strtoupper($value) === 'NULL') {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function mapGender($value)
    {
        if (empty($value)) {
            return null;
        }
        $normalizedValue = strtolower(trim($value));
        if (in_array($normalizedValue, ['l', 'male', 'laki-laki', 'pria'])) {
            return 'L';
        }
        if (in_array($normalizedValue, ['p', 'female', 'perempuan', 'wanita'])) {
            return 'P';
        }
        return null;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable'],
            'email' => ['nullable', 'email'],
            // ... (lanjutkan aturan validasi lainnya)
        ];
    }
}
