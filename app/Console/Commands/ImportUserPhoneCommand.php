<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\UserPhoneImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportUserPhoneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:import-phone {file : Path to Excel/CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import / Update nomor telepon (phone_number) karyawan berdasarkan ID Badge (employee_id)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return 1;
        }

        $this->info("Memulai import nomor HP dari: {$filePath}...");

        try {
            $import = new UserPhoneImport();
            Excel::import($import, $filePath);

            $this->newLine();
            $this->info("=== HASIL IMPORT NOMOR HP ===");
            $this->info("Berhasil diupdate : {$import->getUpdatedCount()} karyawan");
            $this->warn("Tidak ditemukan   : {$import->getNotFoundCount()} ID Badge");
            $this->comment("Dilewati (kosong) : {$import->getSkippedCount()} baris");

            if ($import->getNotFoundCount() > 0) {
                $this->newLine();
                $this->warn("Daftar ID Badge yang tidak ditemukan:");
                foreach (array_chunk($import->getNotFoundBadges(), 10) as $chunk) {
                    $this->line(" - " . implode(', ', $chunk));
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Gagal melakukan import: " . $e->getMessage());
            return 1;
        }
    }
}
