<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            size: a4 landscape;
            margin: 110px 1cm 1.5cm 1cm;
        }

        header {
            position: fixed;
            top: -90px;
            left: 0;
            right: 0;
            height: 100px;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .main-table th {
            background-color: #ffff00;
            font-size: 7pt;
            line-height: 1.1;
            text-transform: uppercase;
        }

        /* --- Perbaikan Style Lampiran Foto --- */
        .photo-section {
            page-break-before: always;
            margin-top: 20px;
            width: 100%;
        }

        .photo-table {
            width: 100%;
            table-layout: fixed;
            /* Memaksa kolom bagi rata */
            border-collapse: separate;
            border-spacing: 5px;
            /* Jarak antar kotak */
        }

        .photo-card-td {
            vertical-align: top;
            border: 1px solid #000;
            background-color: #fff;
            padding: 0;
        }

        .photo-img-container {
            padding: 5px;
        }

        .photo-img {
            width: 100%;
            height: 150px;
            /* Tinggi proporsional untuk landscape */
            object-fit: cover;
            display: block;
        }

        .photo-info {
            background-color: #fcd5b4;
            border-top: 1px solid #000;
            padding: 5px;
            font-size: 7pt;
            min-height: 55px;
            line-height: 1.2;
        }

        /* --- Footer & Legend --- */
        .good {
            font-family: DejaVu Sans, sans-serif;
            color: green;
            font-weight: bold;
        }

        .nogood {
            font-family: DejaVu Sans, sans-serif;
            color: red;
            font-weight: bold;
        }

        .bg-gray {
            background-color: #f1f5f9;
        }

        .no-border {
            border: none !important;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
        }

        .legend-table td,
        .legend-table th {
            border: 1px solid black;
            padding: 3px;
        }
    </style>
</head>

<body>

    <header>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="border-bottom: 2px solid #999; width: 15%; text-align: center;">
                    <img src="{{ public_path('images/logo-msm.png') }}" width="60">
                </td>
                <td style="border-bottom: 2px solid #999; width: 70%; text-align: center;">
                    <strong style="font-size: 14pt;">TOKA TINDUNG PROJECT</strong><br>
                    <strong style="font-size: 11pt;">LAPORAN INSPEKSI {{ strtoupper($type) }}</strong>
                </td>
                <td style="border-bottom: 2px solid #999; width: 15%; text-align: center;">
                    <img src="{{ public_path('images/logo-archi.png') }}" width="60">
                </td>
            </tr>
        </table>
    </header>

    <main>
        <div style="margin-bottom: 10px;">
            <strong>Periode:</strong> {{ $month }} | <strong>Area:</strong> {{ $area ?? 'Tokatindung Site' }}
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th width="25px">NO</th>
                    @php
                    $name_loc = $type === 'Fire & Rescue Equipment' ? 'NAMA' : 'LOKASI';

                    @endphp
                    <th width="120px"> {{ $name_loc }}</th>
                    {{-- Loop Inputs --}}
                    @foreach ($structure['inputs'] as $header)
                    @php
                    // Cek jika data lama (string) atau data baru (array/object)
                    $labelText = is_array($header) ? ($header['label'] ?? 'N/A') : $header;
                    @endphp
                    <th>{{ $labelText }}</th>
                    @endforeach

                    {{-- Loop Checks --}}
                    @foreach ($structure['checks'] as $header)
                    @php
                    $labelText = is_array($header) ? ($header['name'] ?? 'N/A') : $header;
                    @endphp
                    <th>{{ $labelText }}</th>
                    @endforeach
                    <th width="60px">TANGGAL</th>
                    <th width="60px">DIPERIKSA OLEH</th>
                    <th width="120px">REMARKS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $item->equipmentMaster->specific_location }}</td>

                    @foreach ($structure['inputs'] as $input)
                    @php
                    // Normalisasi key untuk mencari di array conditions
                    $key = is_array($input) ? ($input['name'] ?? null) : $input;
                    @endphp
                    <td>{{ $item->conditions[$key] ?? '-' }}</td>
                    @endforeach

                    {{-- Loop Checks Body --}}
                    @foreach ($structure['checks'] as $check)
                    @php
                    $key = is_array($check) ? ($check['name'] ?? null) : $check;
                    $val = $item->conditions[$key] ?? null;

                    @endphp
                    <td>
                        @if ($val === true || $val === 'true' || $val === 1 || $val === '1')
                        <span class="good">✔</span>
                        @elseif($val === false || $val === 'false' || $val === 0 || $val === '0')
                        <span class="nogood">✘</span>
                        @else
                        {{ $val }}
                        @endif
                    </td>
                    @endforeach
                    <td> {{ \Carbon\Carbon::parse($item->inspectionSession->inspection_date)->format('d/m/Y') }} </td>
                    <td>
                        @php
                        $daftarNama = array_filter(explode('|', $item->inspected_by ?? ''), fn($n) => !empty(trim($n)));
                        @endphp
                        @foreach ($daftarNama as $namaOrang)
                        @php
                        $search = ['"', ','];
                        $cleanName = str_replace($search, '', $namaOrang);
                        $initials = collect(preg_split('/\s+/', trim($cleanName)))
                        ->filter()
                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                        ->implode('');
                        @endphp
                        {{ $initials }}@if (!$loop->last), @endif
                        @endforeach
                    </td>
                    <td style="text-align: left;">{{ $item->remarks }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="photo-section" style="font-family: Arial, sans-serif;">
            <div style="background-color: #eee; padding: 5px; text-align: center; border: 1px solid #000; margin-bottom: 10px;">
                <strong style="font-size: 10pt; letter-spacing: 1px;">LAMPIRAN DOKUMENTASI FOTO</strong>
            </div>

            @php
            $firstItem = $data->first();
            $areaPhotoPath = $firstItem && $firstItem->inspectionSession ? $firstItem->inspectionSession->area_photo_path : null;
            $documentationPhotos = $data->filter(fn($item) => $item->documentation_path && file_exists(storage_path('app/public/' . $item->documentation_path)));
            $areaPhotoExists = $areaPhotoPath && file_exists(storage_path('app/public/' . $areaPhotoPath));

            // Gabungkan semua item yang akan ditampilkan di grid
            $gridItems = collect();
            if($areaPhotoExists) {
            $gridItems->push(['type' => 'area', 'path' => $areaPhotoPath]);
            }
            foreach($documentationPhotos as $photo) {
            $gridItems->push(['type' => 'doc', 'data' => $photo]);
            }
            @endphp

            @if($gridItems->isEmpty())
            <div style="text-align: center; color: #999; padding: 40px; border: 1px dashed #ccc;">
                Tidak ada lampiran foto dokumentasi.
            </div>
            @else
            <table class="photo-table">
                @foreach ($gridItems->chunk(4) as $chunk)
                <tr>
                    @foreach ($chunk as $cell)
                    <td class="photo-card-td">
                        <div class="photo-img-container">
                            @php $path = ($cell['type'] == 'area') ? $cell['path'] : $cell['data']->documentation_path; @endphp
                            <img src="{{ storage_path('app/public/' . $path) }}" class="photo-img">
                        </div>
                        <div class="photo-info">
                            @if($cell['type'] == 'area')
                            <strong>Foto Inspeksi Area :</strong><br>
                            <span style="color: blue; text-decoration: underline;">
                                {{ $firstItem->inspectionSession->area_name ?? 'Environment' }}
                            </span>
                            @else
                            <strong>No:</strong> {{ $loop->parent->index * 4 + $loop->iteration - ($areaPhotoExists ? 0 : 0) }}<br>
                            <strong>Lokasi:</strong> {{ $cell['data']->equipmentMaster->specific_location ?? '-' }}<br>
                            <strong>Ket:</strong> {{ Str::limit($cell['data']->remarks ?? '-', 35) }}
                            @endif
                        </div>
                    </td>
                    @endforeach
                    {{-- Mengisi kolom kosong jika jumlah foto tidak kelipatan 4 --}}
                    @for ($i = 0; $i < (4 - $chunk->count()); $i++)
                        <td style="border: none;"></td>
                        @endfor
                </tr>
                @endforeach
            </table>
            @endif
        </div>

        {{-- Footer Section --}}
        <div style="margin-top: 30px; page-break-inside: avoid;">
            <table class="no-border" style="width: 100%;">
                <tr>
                    <td class="no-border" style="width: 15%; vertical-align: top;">
                        <table class="legend-table">
                            <tr>
                                <th class="bg-gray">Keterangan</th>
                            </tr>
                            <tr>
                                <td><span class="good">✔</span> Baik</td>
                            </tr>
                            <tr>
                                <td><span class="nogood">✘</span> Rusak / Tidak Baik</td>
                            </tr>
                        </table>
                    </td>
                    <td class="no-border" style="width: 5%;"></td>
                    <td class="no-border" style="width: 45%; vertical-align: top;">
                        <table class="legend-table">
                            <tr>
                                <td class="bg-gray" style="width: 100px; font-weight: bold;">Di Input Oleh</td>
                                <td>: {{ $submitted_by ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="bg-gray" style="font-weight: bold;">Nomor Inspeksi</td>
                                <td>: {{ $inspection_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="bg-gray" style="font-weight: bold;">Date</td>
                                <td>: {{ $tgl }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="no-border" style="width: 5%;"></td>
                    <td class="no-border" style="width: 30%; vertical-align: top;">
                        <table class="legend-table">
                            @php
                            $daftarNamaUnik = collect($data)->pluck('inspected_by')->flatMap(fn($item) => explode('|', $item))->map(fn($name) => trim($name))->unique()->filter();
                            @endphp
                            <tr>
                                <th colspan="2">Inisial Pemeriksa</th>
                            </tr>
                            @foreach ($daftarNamaUnik as $name)
                            <tr>
                                <td class="bg-gray" style="width: 40px; text-align: center; font-weight: bold;">
                                    @php
                                    $clean = str_replace(['"', ','], '', $name);
                                    $init = collect(preg_split('/\s+/', trim($clean)))->filter()->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                                    @endphp
                                    {{ $init }}
                                </td>
                                <td style="text-align: left;"> {{ trim(str_replace('"', '', $name)) }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>

</html>