<!DOCTYPE html>
<html>
<head>
    <title>Rekap MCU Departemen</title>
</head>
<body>
    <p>Yth. Admin Departemen <strong>{{ $department->department_name }}</strong>,</p>
    
    <p>Berikut kami lampirkan file Excel yang berisi rekap jadwal Medical Check Up (MCU) untuk karyawan di departemen Anda pada periode <strong>{{ $monthName }}</strong>.</p>
    
    <p>Mohon untuk dapat meninjau lampiran tersebut dan memastikan karyawan yang bersangkutan telah mendapatkan informasi jadwalnya.</p>
    
    <p>Terima kasih atas kerja samanya.</p>
    <br>
    <p>Salam,<br>Tim Klinik / HSE TOSAR</p>
</body>
</html>
