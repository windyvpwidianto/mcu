<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fitness for Work Certificate</title>
    <style>
        @page {
            margin: 100px 40px 140px 40px; /* top, right, bottom, left */
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
        }
        
        /* Fixed Header */
        header {
            position: fixed;
            top: -80px; /* Offset into the top margin */
            left: 0px;
            right: 0px;
            height: 60px;
        }
        
        /* Fixed Footer */
        footer {
            position: fixed;
            bottom: -120px; /* Offset into the bottom margin */
            left: 0px;
            right: 0px;
            height: 100px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #ccc;
            margin-bottom: 15px;
            padding-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-left {
            width: 25%;
            text-align: left;
        }
        .logo-right {
            width: 25%;
            text-align: right;
        }
        .header-text {
            width: 50%;
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .header-text p.doc-num {
            font-weight: normal;
        }
        
        /* Main Content styles */
        main {
            /* No margin needed as @page handles it */
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .title p {
            margin: 0;
            text-transform: uppercase;
        }
        .title p.en {
            font-style: italic;
        }
        .doc-no {
            text-align: center;
            font-weight: bold;
            margin-bottom: 25px;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 20px;
        }
        .paragraph p {
            margin: 0 0 5px 0;
        }
        .paragraph p.en {
            font-style: italic;
        }
        
        .info-table {
            width: 90%;
            margin: 0 auto 20px auto;
        }
        .info-table td {
            padding: 4px;
            vertical-align: top;
        }
        .info-table td.label-col {
            width: 35%;
        }
        .info-table td.colon-col {
            width: 5%;
        }
        .info-table td.val-col {
            width: 60%;
        }
        .en-label {
            font-style: italic;
            font-weight: bold;
        }
        
        .checkbox-row {
            margin-bottom: 5px;
        }
        .checkbox-container {
            display: inline-block;
            margin-right: 20px;
        }
        .box-dark {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #666;
            color: #fff;
            text-align: center;
            font-size: 10px;
            line-height: 10px;
            font-weight: bold;
            border: 2px solid #999;
            margin-right: 4px;
        }
        .box-light {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #ccc;
            border: 2px solid #999;
            margin-right: 4px;
        }
        .box-white {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            font-size: 12px;
            line-height: 12px;
            font-weight: bold;
            margin-right: 6px;
        }

        .result-section {
            margin-bottom: 20px;
        }
        .result-item {
            margin-bottom: 10px;
            margin-left: 20px;
        }
        
        .notes-section {
            margin-top: 20px;
        }
        .notes-lines {
            margin-top: 15px;
            line-height: 25px;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            width: 100%;
            height: 20px;
            margin-bottom: 15px;
        }
        
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-title {
            margin-bottom: 60px;
        }
        
        .footer-note {
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #ccc;
        }
        .footer-table td {
            border: 1px solid #ccc;
            padding: 3px 5px;
        }
        .red-text {
            color: red;
        }
    </style>
</head>
<body>

    <!-- HEADER akan selalu muncul di bagian atas setiap halaman -->
    <header>
        <table class="header-table">
            <tr>
                <td class="logo-left">
                    <img src="{{ public_path('images/logo-msm.png') }}" height="40" alt="Logo Kiri">
                </td>
                <td class="header-text">
                    <h1>TOKA TINDUNG PROJECT</h1>
                    <p>Fitness for Work Certificate</p>
                    <p class="doc-num">TT-OHS-FRO-033A</p>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/logo-archi.png') }}" height="40" alt="Logo Kanan">
                </td>
            </tr>
        </table>
    </header>

    <!-- FOOTER akan selalu muncul di bagian bawah setiap halaman -->
    <footer>
        <div class="footer-note">
            *Definisi resiko tinggi mengacu pada SOP TT-OHS-SO-60-001 Site Access & ID Badge poin 4.10
        </div>

        <table class="footer-table">
            <tr>
                <td width="20%">Nama Dokumen</td>
                <td width="30%">Fitness for Work Certificate</td>
                <td width="25%"></td>
                <td width="25%"></td>
            </tr>
            <tr>
                <td>Disetujui Oleh</td>
                <td>KTT / Teknik Tambang</td>
                <td>Tanggal Terbit</td>
                <td>18 Juli 2026</td>
            </tr>
            <tr>
                <td>No Dokumen</td>
                <td>TT-OHS-FRO-033A</td>
                <td>Tanggal Kaji Ulang</td>
                <td>18 Juli 2028</td>
            </tr>
            <tr>
                <td>No Revisi</td>
                <td>01</td>
                <td colspan="2" style="text-align: right; color: red;">Salinan Valid Dokumen Asli...</td>
            </tr>
        </table>
    </footer>

    <!-- MAIN konten yang akan dipaginasi secara otomatis jika melebihi 1 halaman -->
    <main>
        <div class="title">
            <p>KETERANGAN SEHAT UNTUK BEKERJA</p>
            <p class="en">FITNESS FOR WORK CERTIFICATE</p>
        </div>
        <div class="doc-no">
            @if(isset($fullCertNumber))
                No: {{ $fullCertNumber }}
            @elseif(isset($result->certificate_number))
                No: {{ $result->certificate_number }}
            @else
                No: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/20&nbsp;&nbsp;&nbsp;
            @endif
        </div>

        <div class="paragraph">
            <p>Berdasarkan hasil pemeriksaan kesehatan dan rencana pengelolaan kesehatan PT. MSM/TTN, maka saya menyatakan bahwa pribadi dibawah ini:</p>
            <p class="en">According to the Medical Check Up result and PT. MSM / PT. TTN Health Management Plan, I certified that the person below;</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="label-col">Nama/<span class="en-label">Name</span></td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">No. ID/<span class="en-label">ID Number</span></td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->employee_id ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Jabatan/<span class="en-label">Job Title</span></td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->position ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Department</td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->department_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Kategori/<span class="en-label">Category</span></td>
                <td class="colon-col">:</td>
                <td class="val-col">
                    <div class="checkbox-row">
                        <span class="checkbox-container"><span class="box-dark">✓</span> Lengkap/<span class="en-label">Full</span></span>
                        <span class="checkbox-container"><span class="box-light"></span> Pemeriksaan di site/<span class="en-label">Site Exam</span></span>
                    </div>
                    <div class="checkbox-row">
                        <span class="checkbox-container"><span class="box-dark">✓</span> Resiko Tinggi/<span class="en-label">High Risk*</span></span>
                        <span class="checkbox-container"><span class="box-light"></span> Resiko Rendah/<span class="en-label">Low Risk</span></span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="result-section">
            <p>Dengan hasil/with result;</p>
            
            <div class="result-item">
                <span class="box-white">{{ $result->status === 'fit_to_work' ? '✓' : '' }}</span>
                Sehat untuk bekerja/<span class="en-label">Fit for work</span>
            </div>
            <div class="result-item">
                <span class="box-white">{{ $result->status === 'temporary_unfit' ? '✓' : '' }}</span>
                Sementara Tidak Sehat Untuk Bekerja/<span class="en-label">Temporary Unfit for work</span>
            </div>
            <div class="result-item">
                <span class="box-white">{{ $result->status === 'unfit' ? '✓' : '' }}</span>
                Tidak Sehat Untuk Bekerja/<span class="en-label">Unfit for work</span>
            </div>
            <div class="result-item">
                <span class="box-white"></span>
                Jenis Pemeriksaan Tidak Lengkap/<span class="en-label">Incomplete Examination</span>
            </div>
        </div>

        <div class="notes-section">
            <p>Catatan Tambahan/<span class="en-label">Additional Notes:</span></p>
            <div class="notes-lines">
                @if(!empty($result->doctor_notes))
                    <p>{!! strip_tags($result->doctor_notes) !!}</p>
                @endif
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-title">
                Diperiksa oleh/<span class="en-label">Checked by</span>,
            </div>
            <div class="signature-name">
                dr. _________________________<br>
                (Dokter Site/<span class="en-label">Site Doctor</span>)
            </div>
        </div>
    </main>

</body>
</html>
