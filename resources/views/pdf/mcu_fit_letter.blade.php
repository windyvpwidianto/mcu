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
            bottom: -140px;
            left: -40px;
            right: -40px;
            height: 120px;
        }

        .header-table {
            width: 100%;
            border-bottom: 1.5px solid #000;
            margin-bottom: 15px;
            padding-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-left {
            width: 22%;
            text-align: center;
        }
        .logo-right {
            width: 22%;
            text-align: center;
        }
        .header-text {
            width: 56%;
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0;
            font-size: 12px;
            font-weight: bold;
        }
        .header-text p.doc-num {
            font-size: 10px;
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
        .box-white, .box-dark, .box-light {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 1.5px solid #000;
            background-color: #ffffff;
            color: #000000;
            text-align: center;
            font-size: 11px;
            line-height: 13px;
            font-weight: bold;
            margin-right: 6px;
            vertical-align: middle;
            border-radius: 2px;
        }
        .is-strikethrough {
            text-decoration: line-through;
            color: #64748b;
        }
        .is-strikethrough .opt-label,
        .is-strikethrough .en-label {
            text-decoration: line-through;
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
            margin-bottom: 6px;
            padding-left: 40px;
            padding-right: 40px;
            font-size: 10px;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            border-left: none;
            border-right: none;
        }
        .footer-table td {
            border: 1px solid #94a3b8;
            padding: 3px 6px;
        }
        .footer-table td:first-child {
            padding-left: 40px;
        }
        .footer-table td:last-child {
            padding-right: 40px;
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
                    <img src="{{ public_path('images/logo-msm.png') }}" height="46" style="height: 46px; max-height: 46px; width: auto;" alt="Logo Kiri">
                </td>
                <td class="header-text">
                    <h1>TOKA TINDUNG PROJECT</h1>
                    <p>Fitness for Work Certificate</p>
                    <p class="doc-num">TT-OHS-FRO-033A</p>
                </td>
                <td class="logo-right">
                    <img src="{{ public_path('images/logo-archi.png') }}" height="46" style="height: 46px; max-height: 46px; width: auto;" alt="Logo Kanan">
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
                    <table class="category-table" style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                <span class="checkbox-container" data-cat="lengkap">
                                    <span class="box-white">✓</span>
                                    <span class="opt-label">Lengkap/<span class="en-label">Full</span></span>
                                </span>
                            </td>
                            <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                <span class="checkbox-container is-strikethrough" data-cat="site" style="text-decoration: line-through;">
                                    <span class="box-white"></span>
                                    <span class="opt-label" style="text-decoration: line-through;">Pemeriksaan di site/<span class="en-label" style="text-decoration: line-through;">Site Exam</span></span>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                <span class="checkbox-container is-strikethrough" data-cat="high_risk" style="text-decoration: line-through;">
                                    <span class="box-white"></span>
                                    <span class="opt-label" style="text-decoration: line-through;">Resiko Tinggi/<span class="en-label" style="text-decoration: line-through;">High Risk*</span></span>
                                </span>
                            </td>
                            <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                <span class="checkbox-container is-strikethrough" data-cat="low_risk" style="text-decoration: line-through;">
                                    <span class="box-white"></span>
                                    <span class="opt-label" style="text-decoration: line-through;">Resiko Rendah/<span class="en-label" style="text-decoration: line-through;">Low Risk</span></span>
                                </span>
                            </td>
                        </tr>
                    </table>
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
