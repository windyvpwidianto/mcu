<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Rujukan Medis - {{ $employee->name ?? 'Karyawan' }}</title>
    <style>
        @page {
            margin: 110px 45px 145px 45px; /* top, right, bottom, left */
            size: A4 portrait;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        /* Fixed Header */
        header {
            position: fixed;
            top: -85px;
            left: 0px;
            right: 0px;
            height: 70px;
        }
        
        /* Fixed Footer */
        footer {
            position: fixed;
            bottom: -135px;
            left: -45px;
            right: -45px;
            height: 125px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-left {
            width: 20%;
            text-align: left;
        }
        .logo-right {
            width: 20%;
            text-align: right;
        }
        .header-text {
            width: 60%;
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-text p.doc-title {
            margin: 3px 0 2px 0;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p.doc-num {
            margin: 0;
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
        }

        .title-doc {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .date-location {
            text-align: right;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .recipient-section {
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .greeting {
            margin-bottom: 12px;
        }

        .info-table {
            width: 100%;
            margin: 10px 0 20px 20px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 4px;
            vertical-align: top;
            font-size: 13px;
        }
        .info-table td.label-col {
            width: 130px;
            font-weight: normal;
        }
        .info-table td.colon-col {
            width: 15px;
        }
        .info-table td.val-col {
            font-weight: 600;
        }

        .observation-intro {
            margin-bottom: 10px;
            text-align: justify;
        }

        .observation-box {
            margin-left: 20px;
            margin-bottom: 25px;
        }
        .observation-table {
            width: 100%;
            border-collapse: collapse;
        }
        .observation-table td {
            padding: 4px;
            vertical-align: top;
            font-size: 13px;
        }
        .observation-table td.label-obs {
            width: 110px;
            font-weight: bold;
        }
        .observation-table td.colon-obs {
            width: 15px;
        }
        .observation-table td.val-obs {
            text-align: justify;
        }

        .closing-text {
            margin-bottom: 30px;
            text-align: justify;
        }

        .signature-section {
            width: 100%;
            margin-top: 20px;
        }
        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-title {
            margin-bottom: 65px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-role {
            font-size: 12px;
        }

        /* Footer Table (Standar Dokumen Mutu Archi / PT MSM) */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        .footer-table td {
            border: 1px solid #94a3b8;
            padding: 2.5px 6px;
            vertical-align: middle;
        }
        .footer-table td.doc-controlled {
            font-style: italic;
            color: #334155;
        }
    </style>
</head>
<body>

    <!-- FIXED HEADER -->
    <header>
        <table class="header-table">
            <tr>
                <td class="logo-left">
                    @if(!empty($logoMsm))
                        <img src="{{ $logoMsm }}" height="44" style="height: 44px; max-height: 44px; width: auto;" alt="Logo PT MSM">
                    @else
                        <img src="{{ public_path('images/logo-msm.png') }}" height="44" style="height: 44px; max-height: 44px; width: auto;" alt="Logo PT MSM">
                    @endif
                </td>
                <td class="header-text">
                    <h1>TOKA TINDUNG PROJECT</h1>
                    <p class="doc-title">FORMULIR RUJUKAN MEDIS</p>
                    <p class="doc-num">TT-OHS-FRO-028D</p>
                </td>
                <td class="logo-right">
                    @if(!empty($logoArchi))
                        <img src="{{ $logoArchi }}" height="44" style="height: 44px; max-height: 44px; width: auto;" alt="Logo Archi">
                    @else
                        <img src="{{ public_path('images/logo-archi.png') }}" height="44" style="height: 44px; max-height: 44px; width: auto;" alt="Logo Archi">
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <!-- FIXED FOOTER -->
    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 25%; font-weight: bold;">Nama Dokumen/Document Name</td>
                <td colspan="3">Formulir Rujukan Medis</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Ditetapkan Oleh/Determined By</td>
                <td style="width: 32%;">Kepala Teknik Tambang</td>
                <td style="width: 26%; font-weight: bold;">Tanggal Terbit/Date of Issue</td>
                <td style="width: 17%;">20 Juli 2023</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">No Dokumen/ Document No</td>
                <td>TT-OHS-FRO-028D</td>
                <td style="font-weight: bold;">Tanggal Tinjau Ulang /Review Date</td>
                <td>20 Juli 2028</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">No Revisi</td>
                <td>01</td>
                <td class="doc-controlled">Dokumen terkendali dan valid hanya ada di sharepoint Archi Indonesia</td>
                <td style="text-align: right; font-weight: bold;">Halaman 1 dari 1</td>
            </tr>
        </table>
    </footer>

    <!-- MAIN BODY KONTEN SURAT RUJUKAN -->
    <main>
        <div class="title-doc">
            SURAT RUJUKAN
        </div>

        <div class="date-location">
            Winuri, {{ $formattedDate }}
        </div>

        <div class="recipient-section">
            Kepada YTH :<br>
            <strong>{{ $result->specialist_type ?? 'Dokter Spesialis / Faskes Rujukan' }}</strong><br>
            Di -<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tempat
        </div>

        <div class="greeting">
            Dengan Hormat,<br>
            Bersama surat ini, sesuai dengan hasil pemeriksaan yang sudah di lakukan menerangkan bahwa :
        </div>

        <table class="info-table">
            <tr>
                <td class="label-col">Nama</td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Umur</td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $age }}</td>
            </tr>
            <tr>
                <td class="label-col">Jenis Kelamin</td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $gender }}</td>
            </tr>
            <tr>
                <td class="label-col">No ID</td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->employee_id ?? ($employee->nik ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label-col">Alamat / Dept</td>
                <td class="colon-col">:</td>
                <td class="val-col">{{ $employee->department_name ?? ($employee->department->name ?? 'Site Toka Tindung') }}</td>
            </tr>
        </table>

        <div class="observation-intro">
            Memerlukan pemeriksaan dan penanganan lebih lanjut untuk proses penyembuhan, sesuai dengan hasil observasi yang telah dilakukan di Klinik PT.MSM/TTN :
        </div>

        <div class="observation-box">
            <table class="observation-table">
                <tr>
                    <td class="label-obs">Observasi</td>
                    <td class="colon-obs">:</td>
                    <td class="val-obs">
                        @if(!empty($result->doctor_notes))
                            {!! $result->doctor_notes !!}
                        @else
                            Hasil evaluasi Medical Check Up berkala menunjukkan temuan klinis yang memerlukan konsultasi dan pemeriksaan penunjang lanjutan.
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label-obs">Diagnosa</td>
                    <td class="colon-obs">:</td>
                    <td class="val-obs">
                        <strong>{{ $diagnosaText }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="label-obs">Tindakan</td>
                    <td class="colon-obs">:</td>
                    <td class="val-obs">
                        Rujukan pemeriksaan spesialis ke <strong>{{ $result->specialist_type ?? 'Dokter Spesialis' }}</strong> untuk evaluasi klinis dan penatalaksanaan lanjutan
                        @if($followUpDateText)
                            (Target konsultasi sebelum: <strong>{{ $followUpDateText }}</strong>)
                        @endif.
                    </td>
                </tr>
            </table>
        </div>

        <div class="closing-text">
            Demikian surat ini di buat untuk di gunakan sebagaimana mestinya, dan atas perhatian di ucapkan terima kasih.
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-title">
                    Tanda Tangan Dokter,
                </div>
                <div class="signature-name">
                    dr. {{ $result->reviewedBy->name ?? '_________________________' }}
                </div>
                <div class="signature-role">
                    Dokter Klinik PT.MSM/TTN
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </main>

</body>
</html>
