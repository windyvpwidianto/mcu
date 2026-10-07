<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UserPhoneImport implements ToCollection, WithCalculatedFormulas
{
    protected int $updatedCount = 0;
    protected int $notFoundCount = 0;
    protected int $skippedCount = 0;
    protected array $notFoundBadges = [];
    protected array $updatedRows = [];

    /**
     * Import directly from file path reading ALL sheets and resolving formulas.
     *
     * @param string $filePath
     * @return self
     */
    public function importFromPath(string $filePath): self
    {
        $spreadsheet = IOFactory::load($filePath);

        // 1. Preload semua user yang memiliki employee_id / nik ke dalam memory lookup
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

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            // Ambil array baris dengan rumus terhitung ($calculateFormulas = true, $formatData = true)
            $rows = $sheet->toArray(null, true, true, false);
            if (empty($rows)) {
                continue;
            }

            $firstRow = $rows[0];
            $hasHeader = false;
            $badgeCol = null;
            $phoneCol = null;

            // Deteksi header kolom pada baris pertama
            foreach ($firstRow as $cIdx => $cVal) {
                $valLower = strtolower(str_replace(['_', '-', ' ', '.'], '', (string)$cVal));
                if (in_array($valLower, ['idbadge', 'badgeid', 'employeeid', 'badge', 'nobadge', 'nik', 'id', 'idkaryawan', 'persno', 'nrp'])) {
                    $badgeCol = $cIdx;
                    $hasHeader = true;
                }
                if (in_array($valLower, ['phonenumber', 'phone', 'nomorhp', 'nohp', 'hp', 'nowa', 'nomorwa', 'telepon', 'mobile', 'handphone'])) {
                    // Jika belum diset atau kolom sebelumnya kosong
                    if ($phoneCol === null) {
                        $phoneCol = $cIdx;
                    }
                    $hasHeader = true;
                }
            }

            $startRow = $hasHeader ? 1 : 0;

            // Jika tidak memiliki baris header (misal Sheet2 langsung data baris 1)
            if (!$hasHeader) {
                for ($rIdx = 0; $rIdx < min(5, count($rows)); $rIdx++) {
                    foreach ($rows[$rIdx] as $cIdx => $val) {
                        $valStr = trim((string)$val);
                        if (strlen($valStr) >= 5 && strlen($valStr) <= 8 && is_numeric($valStr)) {
                            if ($badgeCol === null) {
                                $badgeCol = $cIdx;
                            }
                        }
                        $clean = $this->cleanPhoneNumber($valStr);
                        if ($clean) {
                            if ($phoneCol === null) {
                                $phoneCol = $cIdx;
                            }
                        }
                    }
                }
            }

            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $badge = isset($row[$badgeCol]) ? trim((string)$row[$badgeCol]) : null;
                $phone = isset($row[$phoneCol]) ? $this->cleanPhoneNumber($row[$phoneCol]) : null;

                // Jika kolom utama tidak ada nomor valid, coba cari di kolom lain pada baris yang sama
                if (!$phone) {
                    foreach ($row as $cVal) {
                        $candidate = $this->cleanPhoneNumber($cVal);
                        if ($candidate) {
                            $phone = $candidate;
                            break;
                        }
                    }
                }

                if (empty($badge) && empty($phone)) {
                    $this->skippedCount++;
                    continue;
                }

                if (empty($badge) || empty($phone)) {
                    $this->skippedCount++;
                    continue;
                }

                $badgeStr = trim((string)$badge);
                $user = $userByBadge[$badgeStr] ?? null;

                if (!$user && is_numeric($badgeStr)) {
                    $user = $userByIntBadge[(string)intval($badgeStr)] ?? null;
                }

                if (!$user) {
                    $user = $userByNik[$badgeStr] ?? null;
                }

                if ($user) {
                    $pendingUpdates[$user->id] = [
                        'phone' => $phone,
                        'name'  => $user->name,
                        'badge' => $badgeStr,
                    ];
                } else {
                    $this->notFoundCount++;
                    $this->notFoundBadges[] = $badgeStr;
                }
            }
        }

        // Simpan update ke database
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

        return $this;
    }

    /**
     * Fallback method if called via Excel::import.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        // 1. Preload users
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

            if (empty($badge) || empty($phone)) {
                $this->skippedCount++;
                continue;
            }

            $cleanPhone = $this->cleanPhoneNumber((string)$phone);
            if (empty($cleanPhone)) {
                $this->skippedCount++;
                continue;
            }

            $badgeStr = trim((string)$badge);
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
            'id_badge_karyawan', 'nomor_id_badge', 'badge_no', 'pers.no.', 'pers_no'
        ];

        foreach ($keys as $key) {
            if (isset($row[$key]) && !empty(trim((string)$row[$key]))) {
                return trim((string)$row[$key]);
            }
        }

        foreach ($row as $k => $val) {
            $kLower = strtolower(str_replace(['_', '-', ' ', '.'], '', (string)$k));
            if ((str_contains($kLower, 'badge') || str_contains($kLower, 'employeeid') || str_contains($kLower, 'idkaryawan')) && !empty(trim((string)$val))) {
                return trim((string)$val);
            }
        }

        $arr = array_values(is_array($row) ? $row : $row->toArray());
        if (count($arr) >= 2 && !empty(trim((string)$arr[0]))) {
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
                $clean = $this->cleanPhoneNumber((string)$row[$key]);
                if ($clean) {
                    return $clean;
                }
            }
        }

        foreach ($row as $val) {
            $clean = $this->cleanPhoneNumber((string)$val);
            if ($clean) {
                return $clean;
            }
        }

        return null;
    }

    /**
     * Clean and normalize phone number into standard 08xxxxxxxxxx.
     */
    public function cleanPhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $str = trim((string)$phone);

        // Jangan proses jika formula Excel
        if (str_starts_with($str, '=')) {
            return null;
        }

        // Ambil hanya digit angka
        $digits = preg_replace('/[^0-9]/', '', $str);

        // Validasi panjang nomor HP Indonesia (9 s/d 15 digit)
        if (strlen($digits) < 9 || strlen($digits) > 15) {
            return null;
        }

        // Normalisasi awalan ke 08...
        if (str_starts_with($digits, '628')) {
            return '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            return '0' . $digits;
        } elseif (str_starts_with($digits, '08')) {
            return $digits;
        }

        return null;
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
