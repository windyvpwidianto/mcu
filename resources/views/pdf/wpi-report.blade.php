<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            size: a4 portrait;
            margin: 130px 1.5cm 130px 1.5cm;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
        }

        footer {
            position: fixed;
            bottom: -115px;
            left: 0;
            right: 0;
            height: 120px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        .main-table,
        .footer-table,
        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td,
        .footer-table td {
            border: 1px solid #dcdcdc;
            padding: 4px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .footer-table td {
            font-size: 9px !important;
        }

        .main-table td,
        .main-table th {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .header-table td {
            vertical-align: middle;
        }

        .main-table tr {
            page-break-inside: avoid;
        }

        .en {
            color: #1e40af;
            font-style: italic;
        }

        .footer-table .en {
            font-size: 7px;
        }

        .red-note {
            color: #ff0000;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }

        .bg-label {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        /* PERUBAHAN DISINI: Mengatur tinggi gambar agar seragam */
        img.photo {
            display: inline-block;
            /* Agar gambar berjajar jika lebih dari satu */
            position: static;
            /* PENTING: Menghapus efek tumpang tindih */
            height: 120px;
            width: auto;
            max-width: 100%;
            /* Agar gambar tidak meluap keluar kolom */
            margin: 5px 5px 5px 0;
            border: 1px solid #000;
            vertical-align: top;
        }

        .photo-container {
            display: block;
            /* Memastikan kontainer mengambil lebar penuh */
            clear: both;
            /* Membersihkan float jika ada */
            margin-top: 10px;
            /* Memberi jarak aman dengan teks deskripsi */
            text-align: left;
        }

        .page-break {
            page-break-before: always;
        }

        .risk-table-page td {
            font-size: 8pt;
            line-height: 1.3;
        }

        .bg-extrim {
            background-color: #ff0000 !important;
            color: white;
            font-weight: bold;
        }

        .bg-tinggi {
            background-color: #ffff00 !important;
            color: black;
            font-weight: bold;
        }

        .bg-menengah {
            background-color: #0070c0 !important;
            color: white;
            font-weight: bold;
        }

        .bg-rendah {
            background-color: #92d050 !important;
            color: black;
            font-weight: bold;
        }

        /* Border Utility Classes */
        .border-t-thick {
            border-top: 2px solid #999999 !important;
        }

        .border-b-thick {
            border-bottom: 2px solid #999999 !important;
        }

        .border-l-thick {
            border-left: 2px solid #999999 !important;
        }

        .border-r-thick {
            border-right: 2px solid #999999 !important;
        }

        .border-l-none {
            border-left: none !important;
        }

        .border-r-none {
            border-right: none !important;
        }
    </style>
</head>

<body>
    <header>
        <table class="header-table">
            <tr>
                <td width="15%" class="center border-b-thick border-l-none">
                    <img src="{{ public_path('images/logo-msm.png') }}" width="65">
                </td>
                <td width="70%" class="center border-b-thick">
                    <strong style="font-size: 14pt;">TOKA TINDUNG PROJECT</strong><br>
                    <strong style="font-size: 11pt;">Formulir Laporan WPI KPLH</strong><br>
                    <span style="font-size: 9pt;">TT-MGT-FRS-024A</span>
                </td>
                <td width="15%" class="center border-b-thick border-r-none">
                    <img src="{{ public_path('images/logo-archi.png') }}" width="65">
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table class="footer-table">
            <tr>
                <td class="border-l-none" width="20%">Nama Dokumen/<span class="en">Document Name</span></td>
                <td class="border-r-none " width="51%">Formulir Laporan WPI KPLH</td>
                <td class=" border-r-none border-l-none" width="8%"></td>
                <td width="29%" class="border-l-none border-r-none "></td>
            </tr>
            <tr>
                <td class="border-l-none">Ditetapkan Oleh/<span class="en">Determined By</span></td>
                <td colspan="2">Kepala Teknik Tambang/<span class="en">Mining Technical Head</span></td>
                <td class="border-r-none">Tanggal Terbit / <span class="en">Date of Issue</span>: 15-03-2023</td>
            </tr>
            <tr>
                <td class="border-l-none">No Dokumen/<span class="en">No Document</span></td>
                <td colspan="2">TT-MGT-FRS-024A</td>
                <td class="border-r-none">Tanggal Peninjauan / <span class="en">Review Date</span>: 15-03-2026</td>
            </tr>
            <tr>
                <td class="border-l-none">No Revisi : 00</td>
                <td colspan="2" class="red-note ">
                    Dokumen terkendali dan valid hanya ada di SharePoint Archi Indonesia
                </td>
                <td class="right border-r-none" style="font-weight: bold;">&nbsp;</td>
            </tr>
        </table>
    </footer>

    <main>
         <div style="margin-bottom: 10px;">
            <strong>Reference :</strong> {{ $no_referensi }}
        </div>
        <table class="main-table" style="margin-bottom: 15px;">
            <tr>
                <td width="18%" class="bg-label">Tanggal / <span class="en" style="color:black">Date</span></td>
                <td width="32%">{{ date('d F Y', strtotime($report->report_date)) }}</td>
                <td width="25%" class="bg-label center">Nama Petugas Inspeksi / <br><span class="en"
                        style="color:black">Inspector Name</span></td>
                <td width="10%" class="bg-label center">ID</td>
                <td width="15%" class="bg-label center">Dept/Cont</td>
            </tr>

            @php $maxRows = 6; @endphp
            @for ($i = 0; $i < $maxRows; $i++)
                <tr>
                    @if ($i == 0)
                        <td class="bg-label">Jam / <span class="en" style="color:black">Time</span></td>
                        <td>{{ $report->report_time }}</td>
                    @elseif ($i == 1)
                        <td class="bg-label">Lokasi / <span class="en" style="color:black">Location</span></td>
                        <td>{{ $report->location ?? '-' }}</td>
                    @elseif ($i == 2)
                        <td class="bg-label">Site Name</td>
                        <td>Tokatindung</td>
                    @elseif ($i == 3)
                        <td class="bg-label">Area</td>
                        <td>{{ $report->area }}</td>
                    @elseif ($i == 4)
                        <td class="bg-label">Company</td>
                        <td>PT. MSM/PT. TTN</td>
                    @elseif ($i == 5)
                        <td class="bg-label">{{ $deptLabel ?? 'Department' }}</td>
                        <td>{{ $report->department }}</td>
                    @endif

                    <td>{{ isset($report->inspectors[$i]) ? $i + 1 . '. ' . $report->inspectors[$i]['name'] : '' }}</td>
                    <td class="center">{{ $report->inspectors[$i]['id_number'] ?? '' }}</td>
                    <td class="center">{{ $report->inspectors[$i]['dept_con'] ?? '' }}</td>
                </tr>
            @endfor

            <tr>
                <td colspan="2" style="vertical-align: top; padding: 5px;">
                    Direview oleh / <br> <i>Reviewing by:</i>
                    <div style="margin-top: 5px;">
                        <br>
                        <strong>{{ $report->reviewed_by ?? '................' }}</strong>
                    </div>
                </td>
                <td colspan="1" style="vertical-align: top;">
                    ID: {{ $report->review_id ?? '' }}
                </td>
                <td colspan="2" style="vertical-align: top;">
                    Tanggal review / Date: <br>
                    {{ $report->review_date ? date('d/m/Y', strtotime($report->review_date)) : '' }}
                </td>
            </tr>
        </table>

        <table class="main-table">
            <thead>
                <tr class="bg-label center">
                    <th width="5%">No</th>
                    <th width="6%">OHS Risk</th>
                    <th width="35%">Uraian Tindakan / Kondisi Tidak Aman<br><span class="en"
                            style="color:black; font-weight:normal">Unsafe Act / Unsafe Condition Description</span>
                    </th>
                    <th width="30%">Jenis Tindakan Pencegahan<br><span class="en"
                            style="color:black; font-weight:normal">Type of Preventive Action</span></th>
                    <th width="24%">Tindak Lanjut / <span class="en"
                            style="color:black; font-weight:normal">Follow Up</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report->findings as $index => $find)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="center" style="font-weight: bold;">{{ $find->ohs_risk }}</td>
                        <td>
                            <div style="margin-bottom: 8px;">
                                {{ $find->description }}
                            </div>
                            @if (!empty($find->photos))
                                <div class="photo-container">
                                    @foreach ($find->photos as $p)
                                        <img src="{{ public_path('storage/' . $p) }}" class="photo">
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="margin-bottom: 8px;">
                                {{ $find->prevention_action }}
                            </div>
                            @if (!empty($find->photos_prevention))
                                <div class="photo-container">
                                    @foreach ($find->photos_prevention as $pp)
                                        <img src="{{ public_path('storage/' . $pp) }}" class="photo">
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <strong>PIC:</strong>
                            @if (!empty($find->pic_responsible))
                                @php $picList = explode('|', $find->pic_responsible); @endphp
                                <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                    @foreach ($picList as $picName)
                                        <li>{{ trim($picName) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                            <div style="margin-top: 10px; padding-top: 5px; border-top: 1px dashed #000;">
                                <strong>Due:</strong>
                                {{ $find->due_date ? date('d-m-Y', strtotime($find->due_date)) : '-' }}<br>
                                <strong>Selesai:</strong>
                                {{ $find->completion_date ? date('d-m-Y', strtotime($find->completion_date)) : '-' }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="page-break"></div>

        <h3 class="center" style="text-decoration: underline;">Level Resiko / <span class="en">Risk Level</span>
        </h3>
        <table class="main-table risk-table-page">
            <thead>
                <tr class="bg-label center">
                    <th width="75%">Deskripsi / <span class="en"
                            style="color:black; font-weight:normal">Description</span></th>
                    <th width="25%">Kode OHS Risk / <br><span class="en"
                            style="color:black; font-weight:normal">Code of OHS Risk</span></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Prioritas tindakan <strong>"Extrim (E)"</strong>: Menangani resiko bahaya yang mengancam
                        keselamatan jiwa...</td>
                    <td class="center bg-extrim">E - Ekstrim<br><span class="en bg-extrim">E - Extreme</span></td>
                </tr>
                <tr>
                    <td>Prioritas tindakan <strong>"Tinggi (T)"</strong>: Menangani kondisi atau praktik kerja yang
                        membahayakan...</td>
                    <td class="center bg-tinggi">T - Tinggi<br><span class="en bg-tinggi">T - High</span></td>
                </tr>
                <tr>
                    <td>Prioritas tindakan <strong>"Menengah (M)"</strong>: Menangani pelanggaran peraturan K3...</td>
                    <td class="center bg-menengah">M - Menengah<br><span class="en bg-menengah">M - Moderate</span>
                    </td>
                </tr>
                <tr>
                    <td>Prioritas tindakan <strong>"Rendah (L)"</strong>: Menangani pelanggaran yang tidak signifikan...
                    </td>
                    <td class="center bg-rendah">L - Rendah<br><span class="en bg-rendah">L - Low</span></td>
                </tr>
            </tbody>
        </table>
    </main>
</body>

</html>
