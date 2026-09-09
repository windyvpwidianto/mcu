<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Hazard;
use App\Models\ActionHazard;
use App\Models\ModeratorAssignment;
use App\Models\RiskMatrixCell;
use App\Models\User;
use App\Helpers\FileHelper;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HazardService
{
    /**
     * Simpan laporan Hazard beserta semua Action-nya dalam satu transaksi database.
     *
     * @param  array  $data    Data dari form Livewire
     * @param  array  $actions Daftar action sementara dari form
     * @return Hazard           Model Hazard yang baru dibuat
     */
    public function store(array $data, array $actions): Hazard
    {
        return DB::transaction(function () use ($data, $actions) {

            // -- 1. Generate nomor referensi yang unik --
            $referenceNumber = $this->generateReferenceNumber();

            // -- 2. Format tanggal dari format Indonesia (d-m-Y H:i) ke Y-m-d H:i:s --
            $tanggalFull = Carbon::createFromFormat('d-m-Y H:i', $data['tanggal'])->format('Y-m-d H:i:s');
            $tanggalDate = Carbon::createFromFormat('d-m-Y H:i', $data['tanggal'])->format('Y-m-d');

            // -- 3. Simpan file upload dari temporary ke folder permanen --
            $docDeskripsiPath  = $this->finalizeUpload($data['doc_deskripsi_temp'],  'sebelum_perbaikan');
            $docCorrectivePath = $this->finalizeUpload($data['doc_corrective_temp'], 'sesudah_perbaikan');

            // -- 4. Kalkulasi Risk Level dari Risk Matrix --
            $riskLevel = null;
            if (!empty($data['consequence_id']) && !empty($data['likelihood_id'])) {
                $riskLevel = RiskMatrixCell::where('likelihood_id', $data['likelihood_id'])
                    ->where('risk_consequence_id', $data['consequence_id'])
                    ->value('severity');
            }

            // -- 5. Tentukan status: jika ada action maka 'submitted', jika tidak 'closed' --
            $status = !empty($actions) ? 'submitted' : 'closed';

            // -- 6. Tentukan nama pelapor untuk disimpan --
            $manualPelaporName = $data['pelapor_id']
                ? User::find($data['pelapor_id'])?->name
                : ($data['manualPelaporName'] ?? null);

            // -- 7. Simpan data Hazard utama --
            $hazard = Hazard::create([
                'no_referensi'                => $referenceNumber,
                'event_type_id'               => $data['tipe_bahaya'],
                'event_sub_type_id'           => $data['sub_tipe_bahaya'],
                'department_id'               => $data['department_id'] ?? null,
                'contractor_id'               => $data['contractor_id'] ?? null,
                'pelapor_id'                  => $data['pelapor_id'] ?? null,
                'penanggung_jawab_id'         => $data['penanggungJawab'],
                'location_id'                 => $data['location_id'],
                'location_specific'           => $data['location_specific'],
                'tanggal'                     => $tanggalFull,
                'description'                 => $data['description'],
                'doc_deskripsi'               => $docDeskripsiPath,
                'immediate_corrective_action' => $data['immediate_corrective_action'],
                'doc_corrective'              => $docCorrectivePath,
                'key_word'                    => $data['keyWord'],
                'kondisi_tidak_aman_id'       => $data['kondisi_tidak_aman'] ?? null,
                'tindakan_tidak_aman_id'      => $data['tindakan_tidak_aman'] ?? null,
                'consequence_id'              => $data['consequence_id'],
                'likelihood_id'               => $data['likelihood_id'],
                'risk_level'                  => $riskLevel,
                'status'                      => $status,
                'manualPelaporName'           => $manualPelaporName,
            ]);

            // -- 8. Simpan semua Action/Tindakan Lanjutan --
            foreach ($actions as $act) {
                $dueDate = !empty($act['due_date'])
                    ? Carbon::createFromFormat('d-m-Y', $act['due_date'])->format('Y-m-d')
                    : null;

                $actualCloseDate = !empty($act['actual_close_date'])
                    ? Carbon::createFromFormat('d-m-Y', $act['actual_close_date'])->format('Y-m-d')
                    : null;

                ActionHazard::create([
                    'hazard_id'         => $hazard->id,
                    'original_date'     => $tanggalDate,
                    'description'       => $act['description'],
                    'due_date'          => $dueDate,
                    'actual_close_date' => $actualCloseDate,
                    'responsible_id'    => $act['responsible_id'] ?? null,
                ]);
            }

            // -- 9. Kirim Notifikasi --
            $this->dispatchNotifications($hazard, $status);

            return $hazard;
        });
    }

    /**
     * Generate nomor referensi laporan dengan format LH-XXXXX.
     * Menggunakan DB lock untuk keamanan concurrent requests.
     */
    private function generateReferenceNumber(): string
    {
        $lastReport = Hazard::lockForUpdate()->latest('id')->first();
        $nextId     = $lastReport ? $lastReport->id + 1 : 1;

        return 'LH-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Pindahkan file dari temporary upload ke folder permanen.
     * Jika tidak ada file sementara, kembalikan null.
     *
     * @param  mixed   $tempFile  Path string (dari property Livewire) atau null
     * @param  string  $folder    Nama folder tujuan di disk 'public'
     * @return string|null         Path permanen file, atau null
     */
    private function finalizeUpload(mixed $tempFile, string $folder): ?string
    {
        if (!$tempFile) {
            return null;
        }

        // Jika file adalah instance TemporaryUploadedFile (dari Livewire)
        if ($tempFile instanceof TemporaryUploadedFile) {
            return FileHelper::compressAndStore($tempFile, $folder);
        }

        // Jika sudah berupa string path (sudah diproses sebelumnya)
        if (is_string($tempFile)) {
            return $tempFile;
        }

        return null;
    }

    /**
     * Kirim notifikasi email ke Penanggung Jawab dan semua Moderator yang relevan.
     * Email ke moderator dikirim secara batch (1 query) untuk efisiensi.
     */
    private function dispatchNotifications(Hazard $hazard, string $status): void
    {
        // -- Siapkan teks informasi yang akan muncul di email --
        $locationName = $hazard->department?->department_name
            ?? $hazard->contractor?->contractor_name
            ?? 'N/A';

        $reporterName = $hazard->pelapor_id
            ? ($hazard->pelapor?->name ?? 'User Terdaftar')
            : ($hazard->manualPelaporName ?? 'Anonim');

        $additionalInfo = implode("\n", [
            "Nomor Laporan: {$hazard->no_referensi}",
            "Nama Pelapor : {$reporterName}",
            "Lokasi Penugasan: {$locationName}",
            "Status: {$status}",
        ]);

        $actionUrl = route('hazard-detail', $hazard->id);

        // -- Notifikasi ke Penanggung Jawab Area --
        if ($hazard->penanggung_jawab_id) {
            $responsibilityName = $hazard->penanggungJawab?->name ?? '-';
            defer(fn () => MailHelper::sendToUserId(
                $hazard->penanggung_jawab_id,
                'Anda Menjadi PIC di laporan Hazard Ini',
                'emails.notification',
                [
                    'subject'        => 'Laporan Hazard Baru',
                    'title'          => 'Notifikasi Laporan Hazard',
                    'messageText'    => "Telah dibuat laporan hazard baru.\nSilakan lakukan pemeriksaan.",
                    'additionalInfo' => $additionalInfo . "\nPenanggung Jawab Area: {$responsibilityName}",
                    'actionUrl'      => $actionUrl,
                ]
            ));
        }

        // -- Notifikasi Batch ke semua Moderator yang relevan (1 query, bukan N+1) --
        $moderatorIds = ModeratorAssignment::where('event_type_id', $hazard->event_type_id)
            ->where(function ($query) use ($hazard) {
                // Moderator global (tidak spesifik dept/contractor)
                $query->where(function ($q) {
                    $q->whereNull('department_id')->whereNull('contractor_id');
                });
                // Moderator spesifik Department
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }
                // Moderator spesifik Contractor
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->distinct('user_id')
            ->pluck('user_id')
            ->toArray();

        if (!empty($moderatorIds)) {
            // Kirim 1 batch ke semua moderator sekaligus (efisien)
            defer(fn () => MailHelper::sendToUsers(
                $moderatorIds,
                'Notifikasi Laporan Hazard Baru',
                'emails.notification',
                [
                    'subject'        => 'Laporan Hazard Baru',
                    'title'          => 'Notifikasi Laporan Hazard',
                    'messageText'    => "Telah dibuat laporan hazard baru.\nSilakan lakukan pemeriksaan.",
                    'additionalInfo' => $additionalInfo,
                    'actionUrl'      => $actionUrl,
                ]
            ));
        }
    }
}
