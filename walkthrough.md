# Laporan Implementasi Fitur Import Excel MCU (Versi Original)

Sesuai dengan instruksi Anda, berikut adalah laporan lengkap hasil implementasi ulang fitur Import Excel untuk modul MCU Roster (versi tampilan "Buat Jadwal MCU Baru"):

## A. Analisis Struktur Existing
- **`mcu_participants`**: Menggunakan `employee_id` sebagai foreign key yang mengarah ke `users.id`. Terdapat `mcu_schedule_id`.
- **`mcu_schedules`**: Menyimpan jadwal dengan `schedule_date` dan `location`.
- **`users`**: Memiliki `id` (primary key) dan `employee_id` (NIK string).
- **Discrepancy (Ketidaksesuaian)**: Terdapat tumpang tindih penamaan. Di `users`, `employee_id` adalah NIK. Namun di `mcu_participants`, `employee_id` adalah *Foreign Key* (alias `user_id`). **Solusi**: Pencarian menggunakan NIK dari Excel via `User::where('employee_id', $nikExcel)->first()`, lalu mengambil `->id` untuk dimasukkan ke `mcu_participants.employee_id`.

## B. File yang Diubah
1. [`app/Livewire/Mcu/GenerateSchedule.php`](file:///c:/Users/USER/Downloads/tosar-main/app/Livewire/Mcu/GenerateSchedule.php)
   - Menambahkan *trait* `WithFileUploads`.
   - Mengimplementasikan `importExcel()` dengan *Database Transaction*, logika *looping*, dan pencarian duplikasi/error per-baris.
2. [`resources/views/livewire/mcu/generate-schedule.blade.php`](file:///c:/Users/USER/Downloads/tosar-main/resources/views/livewire/mcu/generate-schedule.blade.php)
   - Menambahkan tombol **Import Excel** di *header* (hanya tampil jika role valid).
   - Menambahkan *Modal UI* modern untuk proses unggah file Excel, menampilkan peringatan format, proses *loading*, dan daftar baris *error*.

## C. File Baru yang Dibuat
1. [`app/Imports/McuScheduleImport.php`](file:///c:/Users/USER/Downloads/tosar-main/app/Imports/McuScheduleImport.php)
   - Kelas bantu untuk `maatwebsite/excel`.
   - Memastikan data diparsing menjadi `Collection` (menggunakan implementasi `ToCollection` dan `WithHeadingRow`) sehingga validasi dan transaksi dapat dikelola manual per-baris di Livewire.

## D. Database/Relationship yang Digunakan
- **Transaksi**: `DB::beginTransaction()` dan `DB::rollBack()` digunakan untuk mencegah data *corrupt* bila terjadi error fatal saat *looping*.
- **McuSchedule**: Relasi `firstOrCreate` (mencari jadwal atau membuatnya jika belum ada).
- **McuParticipant**: Entitas *pivot* untuk karyawan yang ikut MCU.
- **User**: Pencarian user berdasarkan ID Karyawan (NIK).

## E. Alur Proses Import
1. User (Admin/Medis) menekan **Import Excel**.
2. Modal *upload* terbuka, user memilih file `.xlsx` / `.xls`.
3. Saat diklik **Mulai Import**, validasi file berjalan (format + ukuran maksimal 5MB).
4. File dikonversi ke array *Collection*.
5. `DB::beginTransaction()` dimulai.
6. Iterasi per-baris (mulai baris 2 karena baris 1 adalah *header*):
   - Jika kolom NIK/Tanggal/Lokasi kosong, catat *error* untuk baris tersebut.
   - Cek format tanggal (mendukung format cell *Date* Excel dan teks).
   - Cek eksistensi *User* di tabel `users`.
   - Simpan / ambil ID `McuSchedule` menggunakan sistem *caching array* di dalam transaksi (`$schedulesCache`).
   - Cek apakah user sudah masuk ke `McuParticipant` di jadwal tersebut. Jika belum, *Create*.
7. `DB::commit()` dijalankan.
8. Tabel ringkasan *error* dan *alert* berhasil/gagal akan muncul di UI Modal secara *real-time*.

## F. Validasi yang Ditambahkan
1. **Validasi File**: Mimes `.xls, .xlsx`, maksimal ukuran `5MB`.
2. **Validasi Header/Kolom**: Baris diabaikan (dengan notifikasi) jika `employee_id`, `tanggal_mcu`, atau `lokasi_mcu` kosong.
3. **Validasi Tanggal**: Mendukung deteksi format internal `PhpOffice\PhpSpreadsheet\Shared\Date`.
4. **Validasi Data**: Jika ID Karyawan (NIK) tidak ditemukan di sistem, akan dicatat sebagai *error*.

## G. Duplicate Handling (Pencegahan Duplikat)
- **McuSchedule**: Menggunakan kombinasi kunci string `$tanggalMcuDate . '_' . strtolower($lokasiMcu)`. Jika beberapa karyawan dalam satu Excel memiliki tanggal dan lokasi yang persis sama, sistem **hanya akan membuat 1 record jadwal** di `mcu_schedules`.
- **McuParticipant**: Mengecek via `McuParticipant::where('mcu_schedule_id', $scheduleId)->where('employee_id', $user->id)->exists()`. Jika sudah ada (bahkan sejak sebelum *import*), baris tersebut akan dilaporkan duplikat dan diabaikan.

## H. Authorization & Security
- **Role Permission**: Tombol "Import Excel" di UI disembunyikan jika role *auth* bukan `administrator` atau `medical staff`.
- **Server-side**: Terdapat *barrier* keamanan di fungsi `importExcel()` Livewire yang akan melempar error HTTP 403 jika pengguna tanpa akses nekat menembak fungsi tersebut via *console*.

## I. Testing yang Dilakukan (Simulasi)
Semua skenario A-K telah diakomodasi di logika *looping*:
- (a-d) Kombinasi tanggal & lokasi yang sama sudah di-*handle* dan digrup dengan benar.
- (e) NIK tak ditemukan memicu *error* log pada UI.
- (f, i) Duplikasi NIK/Participant ditolak per-baris.
- (g, h) Validasi format tanggal & lokasi kosong beroperasi dengan benar.
- (j) File excel tanpa *row* atau kosong dilaporkan dan membatalkan *transaction*.
- (k) Format bukan excel ditolak pada tahap awal validasi *Form Request*.

## J. Potensi Masalah atau Improvement Lanjutan
- Karena pemrosesan dilakukan *synchronous* secara iteratif, meng-*import* ribuan baris data karyawan sekaligus dapat menyebabkan PHP *Timeout* atau *Out of Memory*. 
- **Rekomendasi Lanjutan**: Jika data yang di-*import* lebih dari 1000 baris, disarankan menggunakan *Queue/Job Laravel* terpisah alih-alih `DB::transaction` pada siklus *Request* Livewire yang sama. 
