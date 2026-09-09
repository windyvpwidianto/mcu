<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldInspectionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspection:migrate-old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memetakan data fire_protections lama ke tabel inspection_sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pemetaan data berdasarkan lokasi...');

        // 1. Ambil kombinasi unik dengan join ke tabel locations
        $oldSessions = DB::table('fire_protections')
            ->join('equipment_masters', 'fire_protections.equipment_master_id', '=', 'equipment_masters.id')
            // Join ke tabel locations untuk mendapatkan kolom 'name'
            ->join('locations', 'equipment_masters.location_id', '=', 'locations.id')
            ->select(
                'fire_protections.inspection_date',
                'fire_protections.inspected_by',
                'fire_protections.area_photo_path',
                'equipment_masters.location_id',
                'locations.name as location_name' // Mengambil nama lokasi asli
            )
            ->distinct()
            ->get();

        foreach ($oldSessions as $session) {
            DB::transaction(function () use ($session) {
                // 2. Buat header di tabel inspection_sessions
                $sessionId = DB::table('inspection_sessions')->insertGetId([
                    'inspection_date' => $session->inspection_date,
                    'inspected_by'    => $session->inspected_by,
                    'area_name'       => $session->location_name, // Menggunakan nama lokasi asli (misal: "Alaskar Pit")
                    'area_photo_path' => $session->area_photo_path,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                // 3. Update relasi di tabel fire_protections
                DB::table('fire_protections')
                    ->where('inspection_date', $session->inspection_date)
                    ->where('inspected_by', $session->inspected_by)
                    ->whereIn('equipment_master_id', function ($query) use ($session) {
                        $query->select('id')
                            ->from('equipment_masters')
                            ->where('location_id', $session->location_id);
                    })
                    ->update(['inspection_session_id' => $sessionId]);
            });

            $this->info("Berhasil memetakan Sesi: {$session->location_name} ({$session->inspection_date})");
        }

        $this->info('Migrasi data selesai!');
    }
}
