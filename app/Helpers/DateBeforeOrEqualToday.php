<?php

namespace App\Helpers;

use Illuminate\Contracts\Validation\Rule;
use DateTime;
use Carbon\Carbon;

class DateBeforeOrEqualToday implements Rule
{
    public function passes($attribute, $value)
    {
        $value = preg_replace('/\s+/', ' ', trim($value));

        // parsing input
        $dateObj = DateTime::createFromFormat('d-m-Y : H:i', $value)
            ?: DateTime::createFromFormat('d-m-Y H:i', $value);

        if (!$dateObj) {
            return false; // format salah
        }

        $now = Carbon::now('Asia/Makassar');

        // --- Pisahkan tanggal & jam ---
        $inputDate = $dateObj->format('Y-m-d');
        $inputTime = $dateObj->format('H:i');
        $today     = $now->format('Y-m-d');
        $nowTime   = $now->format('H:i');

        // Validasi tanggal
        if ($inputDate > $today) {
            return false; // tanggal lebih dari hari ini
        }

        // Kalau tanggal = hari ini → validasi jam
        if ($inputDate === $today && $inputTime > $nowTime) {
            return false; // jam melebihi jam sekarang
        }

        return true;
    }

    public function message()
    {
        return 'Tanggal atau jam tidak boleh melebihi waktu saat ini.';
    }
}
