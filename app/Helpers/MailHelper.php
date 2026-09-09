<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    /**
     * Kirim email notifikasi
     *
     * @param string $to Email tujuan
     * @param string $subject Judul email
     * @param string $view Blade view untuk email (resources/views/emails/*)
     * @param array $data Data yang dikirim ke view
     *
     * @return bool
     */
    public static function sendNotification($to, $subject, $view, $data = [])
    {
        try {
            Mail::send($view, $data, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            if (count(Mail::failures()) > 0) {
                Log::error('Mail gagal dikirim', Mail::failures());
                return false;
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error saat kirim email: ' . $e->getMessage());
            return false;
        }
    }
    // 1 user saja
    public static function sendToUserId($userId, $subject, $view, $data = [])
    {
        $user = User::find($userId);

        if (!$user || !$user->email) {
            Log::error("User dengan ID {$userId} tidak ditemukan atau tidak memiliki email.");
            return false;
        }

        return self::sendNotification(
            $user->email,
            $subject,
            $view,
            $data
        );
    }
    // banyak user
    public static function sendToUsers(array $userIds, $subject, $view, $data = [])
    {
        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $u) {
            if (!$u->email) {
                Log::warning("User ID {$u->id} tidak memiliki email, dilewati.");
                continue;
            }
            self::sendNotification($u->email, $subject, $view, $data);
        }
        return true;
    }
}
