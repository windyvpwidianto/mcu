<?php

namespace App\Livewire\Mcu;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\Contractor;
use App\Models\McuNotificationLog;
use App\Services\FonnteService;
use Carbon\Carbon;

class McuUpcoming extends Component
{
    use WithPagination;

    public $search = '';
    public $filterPeriod = 'all'; // all, next_30, next_7, overdue
    public $filterDepartment = '';
    public $filterContractor = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterPeriod()
    {
        $this->resetPage();
    }

    public function sendExpiredNotification($userId, FonnteService $fonnteService)
    {
        $user = User::find($userId);
        if (!$user) {
            session()->flash('error', 'Karyawan tidak ditemukan.');
            return;
        }

        $phone = $user->phone_number ?? $user->whatsapp_number;
        $email = $user->email;

        if (!$phone && !$email) {
            session()->flash('error', "Nomor WhatsApp/telepon dan email untuk {$user->name} tidak ditemukan.");
            return;
        }

        $formattedDate = $user->next_mcu_date ? Carbon::parse($user->next_mcu_date)->translatedFormat('d F Y') : '-';

        $message = "Yth. Bapak/Ibu {$user->name},\n\n";
        $message .= "Kami informasikan bahwa masa berlaku Medical Check Up (MCU) Anda telah berakhir pada tanggal {$formattedDate}.\n\n";
        $message .= "Mohon untuk segera melakukan pendaftaran Medical Check Up (MCU) terbaru dengan menghubungi OHS Department.\n\n";
        $message .= "Terima kasih atas perhatian dan kerja samanya dalam menjaga kesehatan dan keselamatan kerja.\n\n";
        $message .= "OHS Department";

        $sentChannels = [];

        // Kirim WhatsApp
        if ($phone) {
            try {
                $response = $fonnteService->sendMessage($phone, $message);
                if (isset($response['status']) && $response['status']) {
                    McuNotificationLog::updateOrCreate([
                        'user_id' => $user->id,
                        'notification_stage' => 'MCU_EXPIRED_EMPLOYEE',
                        'scheduled_date' => $user->next_mcu_date ?? now()->toDateString(),
                        'channel' => 'whatsapp',
                    ], [
                        'status' => 'Sent',
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);
                    $sentChannels[] = "WhatsApp ({$phone})";
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error sending expired MCU WA to {$user->name}: " . $e->getMessage());
            }
        }

        // Kirim Email
        if ($email) {
            try {
                \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\McuExpiredEmployeeMail($user->name, $formattedDate));
                McuNotificationLog::updateOrCreate([
                    'user_id' => $user->id,
                    'notification_stage' => 'MCU_EXPIRED_EMPLOYEE',
                    'scheduled_date' => $user->next_mcu_date ?? now()->toDateString(),
                    'channel' => 'email',
                ], [
                    'status' => 'Sent',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);
                $sentChannels[] = "Email ({$email})";
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error sending expired MCU email to {$user->name}: " . $e->getMessage());
            }
        }

        if (!empty($sentChannels)) {
            session()->flash('success', "Notifikasi masa berlaku MCU berakhir berhasil dikirim ke {$user->name} via " . implode(' & ', $sentChannels) . ".");
        } else {
            session()->flash('error', "Gagal mengirim notifikasi ke {$user->name}. Periksa koneksi WhatsApp dan pengaturan Mail server.");
        }
    }

    public function render()
    {
        $today = Carbon::today('Asia/Jakarta');

        $query = User::whereNotNull('next_mcu_date')
            ->with(['departments', 'contractors'])
            ->when($this->search, function($q) {
                $q->where(function($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('employee_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterDepartment, function($q) {
                $q->whereHas('departments', function($q2) {
                    $q2->where('departments.id', $this->filterDepartment);
                });
            })
            ->when($this->filterContractor, function($q) {
                $q->whereHas('contractors', function($q2) {
                    $q2->where('contractors.id', $this->filterContractor);
                });
            });

        // Filter based on period
        if ($this->filterPeriod === 'next_30') {
            $query->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(30)->toDateString()]);
        } elseif ($this->filterPeriod === 'next_7') {
            $query->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(7)->toDateString()]);
        } elseif ($this->filterPeriod === 'overdue') {
            $query->where('next_mcu_date', '<', $today->toDateString());
        } elseif ($this->filterPeriod === 'today') {
            $query->where('next_mcu_date', '=', $today->toDateString());
        }

        $query->orderBy('next_mcu_date', 'asc');

        // Statistics
        $statAll = User::whereNotNull('next_mcu_date')->count();
        $statOverdue = User::whereNotNull('next_mcu_date')->where('next_mcu_date', '<', $today->toDateString())->count();
        $statNext30 = User::whereNotNull('next_mcu_date')->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(30)->toDateString()])->count();
        $statNext7 = User::whereNotNull('next_mcu_date')->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(7)->toDateString()])->count();

        return view('livewire.mcu.mcu-upcoming', [
            'users' => $query->paginate(15),
            'departments' => Department::orderBy('department_name')->get(),
            'contractors' => Contractor::orderBy('contractor_name')->get(),
            'stats' => [
                'all' => $statAll,
                'overdue' => $statOverdue,
                'next_30' => $statNext30,
                'next_7' => $statNext7,
            ]
        ]);
    }
}
