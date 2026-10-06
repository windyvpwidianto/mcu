<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPhoneImport implements ToCollection, WithHeadingRow
{
    protected int $updatedCount = 0;
    protected int $notFoundCount = 0;
    protected int $skippedCount = 0;
    protected array $notFoundBadges = [];
    protected array $updatedRows = [];

    /**
     * Process collection of rows.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // 1. Preload semua user yang memiliki employee_id / nik ke dalam memory lookup
        // Ini menghindari ribuan query full table scan (N+1 query problem)
        $existingUsers = User::select('id', 'employee_id', 'nik', 'name', 'phone_number')
            ->where(function ($q) {
                $q->whereNotNull('employee_id')->where('employee_id', '!=', '');
            })
            ->orWhere(function ($q) {
                $q->whereNotNull('nik')->where('nik', '!=', '');
            })
            ->get();

        $userByBadge = [];
        $userByIntBadge = [];
        $userByNik = [];

        foreach ($existingUsers as $u) {
            if (!empty($u->employee_id)) {
                $b = trim((string)$u->employee_id);
                $userByBadge[$b] = $u;
                if (is_numeric($b)) {
                    $userByIntBadge[(string)intval($b)] = $u;
                }
            }
            if (!empty($u->nik)) {
                $n = trim((string)$u->nik);
                $userByNik[$n] = $u;
            }
        }

        $pendingUpdates = [];

        foreach ($rows as $index => $row) {
            $badge = $this->extractBadge($row);
            $phone = $this->extractPhone($row);

            if (empty($badge)) {
                $this->skippedCount++;
                continue;
            }

            if (empty($phone)) {
                $this->skippedCount++;
                continue;
            }

            $cleanPhone = $this->cleanPhoneNumber((string)$phone);
            $badgeStr = trim((string)$badge);

            // Cari di memory lookup O(1)
            $user = $userByBadge[$badgeStr] ?? null;

            if (!$user && is_numeric($badgeStr)) {
                $user = $userByIntBadge[(string)intval($badgeStr)] ?? null;
            }

            if (!$user) {
                $user = $userByNik[$badgeStr] ?? null;
            }

            if ($user) {
                $pendingUpdates[$user->id] = [
                    'phone' => $cleanPhone,
                    'name'  => $user->name,
                    'badge' => $badgeStr,
                ];
            } else {
                $this->notFoundCount++;
                $this->notFoundBadges[] = $badgeStr;
            }
        }

        // 2. Eksekusi update dalam 1 transaksi DB cepat
        if (!empty($pendingUpdates)) {
            DB::beginTransaction();
            try {
                foreach ($pendingUpdates as $userId => $info) {
                    DB::table('users')->where('id', $userId)->update([
                        'phone_number' => $info['phone'],
                        'updated_at'   => now(),
                    ]);

                    $this->updatedCount++;
                    $this->updatedRows[] = [
                        'badge' => $info['badge'],
                        'name'  => $info['name'],
                        'phone' => $info['phone'],
                    ];
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('UserPhoneImport DB Update Error: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Extract ID Badge from row with various possible column names.
     */
    protected function extractBadge($row): ?string
    {
        $keys = [
            'id_badge', 'badge_id', 'employee_id', 'badge', 'no_badge',
            'nomor_badge', 'id_karyawan', 'id', 'nik', 'badge_number',
            'id_badge_karyawan', 'nomor_id_badge', 'badge_no'
        ];

        foreach ($keys as $key) {
            if (isset($row[$key]) && !empty(trim((string)$row[$key]))) {
                return trim((string)$row[$key]);
            }
        }

        // Fuzzy match column names
        foreach ($row as $k => $val) {
            $kLower = strtolower(str_replace(['_', '-', ' ', '.'], '', (string)$k));
            if ((str_contains($kLower, 'badge') || str_contains($kLower, 'employeeid') || str_contains($kLower, 'idkaryawan')) && !empty(trim((string)$val))) {
                return trim((string)$val);
            }
        }

        // Fallback jika header tidak standar: gunakan kolom pertama
        $arr = array_values(is_array($row) ? $row : $row->toArray());
        if (count($arr) >= 2 && !empty(trim((string)$arr[0])) && !str_contains(strtolower((string)$arr[0]), 'badge')) {
            return trim((string)$arr[0]);
        }

        return null;
    }

    /**
     * Extract Phone Number from row with various possible column names.
     */
    protected function extractPhone($row): ?string
    {
        $keys = [
            'phone_number', 'phone', 'nomor_hp', 'no_hp', 'hp',
            'no_wa', 'nomor_wa', 'whatsapp', 'nomor_telepon', 'no_telepon', 'telepon',
            'no_telp', 'nomor_telp', 'mobile', 'handphone'
        ];

        foreach ($keys as $key) {
            if (isset($row[$key]) && !empty(trim((string)$row[$key]))) {
                return trim((string)$row[$key]);
            }
        }

        // Fuzzy match column names
        foreach ($row as $k => $val) {
            $kLower = strtolower(str_replace(['_', '-', ' ', '.'], '', (string)$k));
            if ((str_contains($kLower, 'phone') || str_contains($kLower, 'hp') || str_contains($kLower, 'wa') || str_contains($kLower, 'telp')) && !empty(trim((string)$val))) {
                return trim((string)$val);
            }
        }

        // Fallback jika header tidak standar: gunakan kolom kedua
        $arr = array_values(is_array($row) ? $row : $row->toArray());
        if (count($arr) >= 2 && !empty(trim((string)$arr[1])) && !str_contains(strtolower((string)$arr[1]), 'phone')) {
            return trim((string)$arr[1]);
        }

        return null;
    }

    /**
     * Clean and normalize phone number.
     */
    public function cleanPhoneNumber(string $phone): string
    {
        // Hilangkan spasi, strip, titik, kurung, karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 628, ubah jadi 08
        if (str_starts_with($phone, '628')) {
            $phone = '0' . substr($phone, 2);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '0' . $phone;
        }

        return $phone;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getNotFoundCount(): int
    {
        return $this->notFoundCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getNotFoundBadges(): array
    {
        return $this->notFoundBadges;
    }

    public function getUpdatedRows(): array
    {
        return $this->updatedRows;
    }
}
