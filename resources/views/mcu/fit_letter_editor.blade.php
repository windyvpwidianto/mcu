<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Draft Surat Keterangan FIT - {{ $employee->name ?? 'MCU' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS (CDN for standalone editor view) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['"Times New Roman"', 'Times', 'serif']
                    }
                }
            }
        }
    </script>

    <!-- Vite Assets (Includes local app.css, app.js, Toastify, FontAwesome) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Toastify CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <style>
        /* A4 Paper Simulation */
        .page-canvas {
            background-color: #525659;
            min-height: calc(100vh - 120px);
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .a4-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            border-radius: 2px;
            box-sizing: border-box;
            outline: none;
            position: relative;
            font-family: "Times New Roman", Times, serif;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Editing Outline Indicator */
        .a4-sheet:focus-within {
            box-shadow: 0 10px 35px rgba(37, 99, 235, 0.25), 0 0 0 2px rgba(59, 130, 246, 0.4);
        }

        /* Locked Header & Footer (Protected from editing) */
        .letter-header-locked {
            user-select: none;
            cursor: not-allowed !important;
            margin-bottom: 15px;
            border-bottom: none;
            padding-bottom: 0;
            position: relative;
        }

        .letter-header-locked table {
            width: 100%;
        }

        .letter-header-locked td {
            vertical-align: middle;
        }

        .letter-body-editable {
            flex: 1;
            outline: none;
            cursor: text;
        }

        .letter-footer-locked {
            user-select: none;
            cursor: not-allowed !important;
            margin-top: 25px;
            position: relative;
        }

        .letter-footer-locked .footer-note {
            margin-bottom: 6px;
            font-size: 10px;
            color: #475569;
        }

        .letter-footer-locked .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #ccc;
        }

        .letter-footer-locked .footer-table td {
            border: 1px solid #ccc;
            padding: 3px 5px;
        }

        /* Styling inside the letter */
        .a4-sheet table {
            border-collapse: collapse;
        }

        .a4-sheet .header-table {
            width: 100%;
            border-bottom: 1.5px solid #000;
            margin-bottom: 18px;
            padding-bottom: 8px;
        }

        .a4-sheet .header-table td {
            vertical-align: middle;
        }

        .a4-sheet .header-table .logo-left {
            width: 22%;
            text-align: center;
            vertical-align: middle;
        }

        .a4-sheet .header-table .header-text {
            width: 56%;
            text-align: center;
            vertical-align: middle;
        }

        .a4-sheet .header-table .logo-right {
            width: 22%;
            text-align: center;
            vertical-align: middle;
        }

        .a4-sheet .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .a4-sheet .doc-no {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 4px 12px;
            display: inline-block;
            cursor: not-allowed !important;
            user-select: none;
        }

        .a4-sheet .doc-no::after {
            content: " 🔒 [READ ONLY]";
            font-size: 10px;
            color: #94a3b8;
            font-family: sans-serif;
            font-weight: 600;
        }

        .a4-sheet .paragraph {
            text-align: justify;
            margin-bottom: 16px;
        }

        .a4-sheet .info-table {
            width: 95%;
            margin: 0 auto 16px auto;
        }

        .a4-sheet .info-table td {
            padding: 4px;
            vertical-align: top;
        }

        /* Styling Kotak Pilihan / Checkbox Kategori & Hasil */
        .a4-sheet .checkbox-row {
            margin-bottom: 6px;
        }
        .a4-sheet .checkbox-container {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.15s ease;
            user-select: none;
        }
        .a4-sheet .checkbox-container:hover {
            background-color: #f1f5f9;
        }
        .a4-sheet .result-section {
            margin-bottom: 18px;
        }
        .a4-sheet .result-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            margin-left: 20px;
            cursor: pointer;
            padding: 3px 8px;
            border-radius: 4px;
            transition: all 0.15s ease;
            user-select: none;
        }
        .a4-sheet .result-item:hover {
            background-color: #f1f5f9;
        }
        .a4-sheet .box-white,
        .a4-sheet .box-dark,
        .a4-sheet .box-light {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 14px !important;
            height: 14px !important;
            min-width: 14px !important;
            min-height: 14px !important;
            border: 1.5px solid #0f172a !important;
            background-color: #ffffff !important;
            color: #0f172a !important;
            text-align: center !important;
            font-size: 11px !important;
            line-height: 14px !important;
            font-weight: 900 !important;
            margin-right: 8px !important;
            vertical-align: middle !important;
            border-radius: 2.5px !important;
            cursor: pointer !important;
            user-select: none !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
            transition: all 0.15s ease;
        }
        .a4-sheet .checkbox-container:hover .box-white,
        .a4-sheet .checkbox-container:hover .box-dark,
        .a4-sheet .checkbox-container:hover .box-light,
        .a4-sheet .result-item:hover .box-white {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important;
            transform: scale(1.08);
        }

        /* Checkbox Strikethrough (Coret) & Category Table Styling */
        .a4-sheet .category-table {
            width: 100%;
            border-collapse: collapse;
        }
        .a4-sheet .category-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .a4-sheet .is-strikethrough {
            text-decoration: line-through !important;
            color: #64748b !important;
            opacity: 0.75;
            transition: all 0.2s ease;
        }
        .a4-sheet .is-strikethrough .opt-label,
        .a4-sheet .is-strikethrough .en-label,
        .a4-sheet .is-strikethrough span:not(.box-white) {
            text-decoration: line-through !important;
        }
        .a4-sheet .is-strikethrough .box-white {
            border-color: #94a3b8 !important;
            background-color: #f8fafc !important;
            opacity: 0.6;
            box-shadow: none !important;
        }
        .a4-sheet .is-strikethrough:hover {
            opacity: 1;
            background-color: #eff6ff !important;
        }
        @media print {
            .a4-sheet .is-strikethrough {
                text-decoration: line-through !important;
                color: #555555 !important;
                opacity: 1 !important;
            }
        }

        .a4-sheet .notes-section {
            margin-top: 18px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: not-allowed !important;
            position: relative;
        }

        .a4-sheet .notes-section .notes-lines {
            cursor: not-allowed !important;
            user-select: text;
        }

        /* Print normalization for read-only fields */
        @media print {
            .a4-sheet .doc-no,
            .a4-sheet .notes-section {
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
            }
            .a4-sheet .doc-no::after {
                display: none !important;
            }
        }

        .a4-sheet .signature-section {
            margin-top: 30px;
        }

        .a4-sheet .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #ccc;
            margin-top: 25px;
        }

        .a4-sheet .footer-table td {
            border: 1px solid #ccc;
            padding: 3px 5px;
        }

        .clickable-box {
            display: inline-block;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .clickable-box:hover {
            transform: scale(1.15);
            background-color: #eff6ff;
        }

        /* Toolbar styles */
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 32px;
            min-width: 32px;
            padding: 0 8px;
            border-radius: 4px;
            font-size: 13px;
            color: #4b5563;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            transition: all 0.15s ease;
        }
        .toolbar-btn:hover {
            background-color: #f3f4f6;
            color: #111827;
            border-color: #d1d5db;
        }
        .toolbar-btn:active {
            background-color: #e5e7eb;
        }
        .toolbar-divider {
            width: 1px;
            height: 24px;
            background-color: #e5e7eb;
            margin: 0 4px;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 flex flex-col min-h-screen relative">

    <!-- Dedicated Toast / Banner Notification (Reliable, standalone) -->
    <div id="saveNotificationBanner" style="display: none;" class="fixed top-5 right-6 z-[9999] px-5 py-3.5 rounded-xl shadow-2xl text-white font-semibold flex items-center gap-3 transition-all duration-300">
        <i id="saveBannerIcon" class="fas fa-check-circle text-xl"></i>
        <div id="saveBannerText" class="text-sm"></div>
        <button type="button" onclick="document.getElementById('saveNotificationBanner').style.display='none'" class="ml-4 text-white/80 hover:text-white">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    <!-- TOP HEADER BAR -->
    <header class="bg-slate-800 border-b border-slate-700 px-6 py-3 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
            
            <!-- Left Info & Navigation -->
            <div class="flex items-center gap-4">
                <a href="{{ route('mcu.list') }}" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold flex items-center gap-2 transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke MCU
                </a>
                <div class="h-6 w-px bg-slate-700"></div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm font-bold text-white tracking-wide">
                            Editor Surat Keterangan FIT
                        </h1>
                        <span id="badgeLetterStatus" class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider {{ ($result->letter_status ?? 'draft') === 'final' ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-slate-900' }}">
                            <i class="fas {{ ($result->letter_status ?? 'draft') === 'final' ? 'fa-check-circle' : 'fa-pen-to-square' }} mr-1"></i>
                            {{ ($result->letter_status ?? 'draft') === 'final' ? 'FINAL' : 'DRAFT' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">
                        {{ $employee->name ?? 'Karyawan' }} (ID: {{ $employee->employee_id ?? ($employee->nik ?? '-') }}) &bull; {{ $employee->department_name ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2.5">
                <!-- Reset Template Button -->
                <button type="button" onclick="confirmResetTemplate()" class="px-3 py-1.5 rounded-lg bg-rose-950/70 hover:bg-rose-900 text-rose-300 border border-rose-800 text-xs font-semibold flex items-center gap-1.5 transition">
                    <i class="fas fa-rotate-left"></i> Reset Standar
                </button>

                <!-- Preview / Print Button -->
                <a href="{{ route('mcu.fit-letter', $result->id) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center gap-1.5 shadow transition">
                    <i class="fas fa-print"></i> Cetak / PDF
                </a>

                <!-- Simpan Surat Button (Menyimpan langsung menjadi Final) -->
                <button type="button" onclick="saveLetter()" id="btnSave" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-2 shadow-lg transition">
                    <i class="fas fa-floppy-disk"></i> Simpan Surat
                </button>
            </div>
        </div>
    </header>

    <!-- FORMATTING TOOLBAR BAR -->
    <div class="bg-white border-b border-gray-200 px-6 py-2 sticky top-[57px] z-40 shadow-sm">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            
            <div class="flex flex-wrap items-center gap-1">
                <!-- Text formatting -->
                <button type="button" class="toolbar-btn" onclick="formatDoc('bold')" title="Tebal (Ctrl+B)">
                    <i class="fas fa-bold"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('italic')" title="Miring (Ctrl+I)">
                    <i class="fas fa-italic"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('underline')" title="Garis Bawah (Ctrl+U)">
                    <i class="fas fa-underline"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('strikeThrough')" title="Coret Teks">
                    <i class="fas fa-strikethrough"></i>
                </button>

                <div class="toolbar-divider"></div>

                <!-- Paragraph / Heading -->
                <button type="button" class="toolbar-btn" onclick="formatBlock('P')" title="Paragraf Normal">
                    <span class="font-serif font-bold text-xs">P</span>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatBlock('H2')" title="Judul Sub (H2)">
                    <span class="font-serif font-bold text-xs">H2</span>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatBlock('H3')" title="Judul Sub (H3)">
                    <span class="font-serif font-bold text-xs">H3</span>
                </button>

                <div class="toolbar-divider"></div>

                <!-- Alignment -->
                <button type="button" class="toolbar-btn" onclick="formatDoc('justifyLeft')" title="Rata Kiri">
                    <i class="fas fa-align-left"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('justifyCenter')" title="Rata Tengah">
                    <i class="fas fa-align-center"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('justifyRight')" title="Rata Kanan">
                    <i class="fas fa-align-right"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('justifyFull')" title="Rata Kiri-Kanan">
                    <i class="fas fa-align-justify"></i>
                </button>

                <div class="toolbar-divider"></div>

                <!-- Lists -->
                <button type="button" class="toolbar-btn" onclick="formatDoc('insertUnorderedList')" title="Daftar Poin (Bullets)">
                    <i class="fas fa-list-ul"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('insertOrderedList')" title="Daftar Angka">
                    <i class="fas fa-list-ol"></i>
                </button>

                <div class="toolbar-divider"></div>

                <!-- Undo / Redo -->
                <button type="button" class="toolbar-btn" onclick="formatDoc('undo')" title="Batalkan (Ctrl+Z)">
                    <i class="fas fa-undo"></i>
                </button>
                <button type="button" class="toolbar-btn" onclick="formatDoc('redo')" title="Ulangi (Ctrl+Y)">
                    <i class="fas fa-redo"></i>
                </button>
            </div>

            <!-- Save indicator -->
            <div class="text-xs text-gray-500 flex items-center gap-2">
                <span id="saveStatusIndicator" class="flex items-center gap-1.5 text-emerald-600 font-medium">
                    <i class="fas fa-check-circle text-xs"></i> Siap diedit
                </span>
                <span class="text-gray-300">|</span>
                <span class="text-[11px] text-gray-400">Tekan <kbd class="px-1.5 py-0.5 bg-gray-100 border border-gray-300 rounded font-mono text-[10px]">Ctrl+S</kbd> untuk simpan</span>
            </div>
        </div>
    </div>

    <!-- MAIN DOCUMENT CANVAS WORKSPACE -->
    <main class="page-canvas flex-1 overflow-y-auto">
        <div class="a4-sheet">
            <!-- HEADER (KOP SURAT) - RESMI & TERKUNCI -->
            <div class="letter-header-locked" contenteditable="false" title="Header Resmi Dokumen (Terkunci)">
                <table class="header-table">
                    <tr>
                        <td class="logo-left" style="width: 22%; text-align: center; vertical-align: middle;">
                            @if(!empty($logoMsm))
                                <img src="{{ $logoMsm }}" height="46" style="height: 46px; max-height: 46px; width: auto; object-fit: contain; vertical-align: middle; display: inline-block;" alt="Logo PT MSM">
                            @else
                                <img src="{{ asset('images/logo-msm.png') }}" height="46" style="height: 46px; max-height: 46px; width: auto; object-fit: contain; vertical-align: middle; display: inline-block;" alt="Logo PT MSM">
                            @endif
                        </td>
                        <td class="header-text" style="text-align: center; width: 56%; vertical-align: middle;">
                            <h1 style="font-size: 14px; font-weight: bold; margin: 0; padding: 0; letter-spacing: 0.5px; color: #0f172a;">TOKA TINDUNG PROJECT</h1>
                            <p style="font-size: 11px; font-weight: bold; margin: 2px 0 1px 0; color: #1e293b;">Fitness for Work Certificate</p>
                            <p class="doc-num" style="font-size: 9.5px; color: #64748b; margin: 0; font-family: 'Times New Roman', Times, serif;">TT-OHS-FRO-033A</p>
                        </td>
                        <td class="logo-right" style="text-align: center; width: 22%; vertical-align: middle;">
                            @if(!empty($logoArchi))
                                <img src="{{ $logoArchi }}" height="46" style="height: 46px; max-height: 46px; width: auto; object-fit: contain; vertical-align: middle; display: inline-block;" alt="Logo Archi">
                            @else
                                <img src="{{ asset('images/logo-archi.png') }}" height="46" style="height: 46px; max-height: 46px; width: auto; object-fit: contain; vertical-align: middle; display: inline-block;" alt="Logo Archi">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <!-- EDITABLE LETTER BODY ONLY -->
            <div id="letterPaper" class="letter-body-editable" contenteditable="true" spellcheck="false">
                {!! $bodyContent !!}
            </div>

            <!-- FOOTER - RESMI & TERKUNCI -->
            <div class="letter-footer-locked" contenteditable="false" title="Footer Kontrol Dokumen (Terkunci)">
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
            </div>
        </div>
    </main>

    <!-- FOOTER STATUS BAR -->
    <footer class="bg-slate-800 border-t border-slate-700 px-6 py-2 text-xs text-slate-400 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span><i class="fas fa-user-pen mr-1"></i> Terakhir diubah oleh: <strong id="lastUpdatedBy" class="text-slate-300">{{ $result->letterUpdatedBy->name ?? 'Belum ada' }}</strong></span>
            <span><i class="fas fa-clock mr-1"></i> Waktu: <strong id="lastUpdatedAt" class="text-slate-300">{{ $result->letter_updated_at ? \Carbon\Carbon::parse($result->letter_updated_at)->format('d M Y H:i') : '-' }}</strong></span>
        </div>
        <div class="text-slate-400 flex items-center gap-1">
            <i class="fas fa-shield-halved text-emerald-400"></i> Mode Editor Dokumen Resmi PT MSM / PT TTN
        </div>
    </footer>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const resultId = {{ $result->id }};
        let currentStatus = '{{ $result->letter_status ?? "draft" }}';
        let isDirty = false;

        // Rich text formatting execution
        function formatDoc(cmd, val = null) {
            document.execCommand(cmd, false, val);
            document.getElementById('letterPaper').focus();
            markDirty();
        }

        function formatBlock(tag) {
            document.execCommand('formatBlock', false, '<' + tag + '>');
            document.getElementById('letterPaper').focus();
            markDirty();
        }

        // Indicator when text changed (kembali ke status draft saat sedang diedit)
        function markDirty() {
            isDirty = true;
            const ind = document.getElementById('saveStatusIndicator');
            if (ind) {
                ind.innerHTML = '<i class="fas fa-circle-dot text-amber-500 text-xs"></i> Mode Draf (Belum Disimpan)';
                ind.className = 'flex items-center gap-1.5 text-amber-600 font-medium';
            }

            const badge = document.getElementById('badgeLetterStatus');
            if (badge) {
                badge.className = 'px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-amber-500 text-slate-900';
                badge.innerHTML = '<i class="fas fa-pen-to-square mr-1"></i> DRAFT';
            }
        }

        document.getElementById('letterPaper').addEventListener('input', () => {
            markDirty();
        });

        // Reliable Notification Helper (Independent Banner + Toastify Fallback)
        function notify(message, isSuccess = true) {
            const banner = document.getElementById('saveNotificationBanner');
            const icon = document.getElementById('saveBannerIcon');
            const text = document.getElementById('saveBannerText');

            if (banner) {
                banner.className = `fixed top-5 right-6 z-[9999] px-5 py-3.5 rounded-xl shadow-2xl text-white font-semibold flex items-center gap-3 transition-all duration-300 ${isSuccess ? 'bg-emerald-600 border border-emerald-400' : 'bg-rose-600 border border-rose-400'}`;
                if (icon) icon.className = `fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'} text-xl`;
                if (text) text.innerText = message;
                banner.style.display = 'flex';

                clearTimeout(window._bannerTimer);
                window._bannerTimer = setTimeout(() => {
                    banner.style.display = 'none';
                }, 4500);
            }

            if (window.Toastify) {
                try {
                    Toastify({
                        text: message,
                        duration: 4000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: isSuccess ? "linear-gradient(to right, #059669, #10b981)" : "linear-gradient(to right, #dc2626, #ef4444)",
                            borderRadius: "8px",
                            boxShadow: "0 4px 12px rgba(0,0,0,0.15)"
                        }
                    }).showToast();
                } catch(e) {}
            }
        }

        // Fungsi untuk mengunci elemen Nomor Surat & Catatan Tambahan (Read-Only)
        function enforceReadOnly() {
            const paper = document.getElementById('letterPaper');
            if (!paper) return;

            // 1. Nomor Surat
            paper.querySelectorAll('.doc-no').forEach(el => {
                el.setAttribute('contenteditable', 'false');
                el.setAttribute('title', 'Nomor surat bersifat Read-Only (Otomatis dari sistem)');
            });

            // 2. Catatan Tambahan (Additional Notes)
            paper.querySelectorAll('.notes-section').forEach(el => {
                el.setAttribute('contenteditable', 'false');
                el.setAttribute('title', 'Catatan dokter bersifat Read-Only');
                el.querySelectorAll('.notes-lines, p, span, div').forEach(child => {
                    child.setAttribute('contenteditable', 'false');
                });
            });

            // 3. Header Kop & Footer Tabel Kontrol
            paper.querySelectorAll('header, footer, .header-table, .footer-table').forEach(el => {
                el.setAttribute('contenteditable', 'false');
            });

            // 4. Standarisasi dan proteksi kotak centang (Checkbox)
            paper.querySelectorAll('.box-dark, .box-light, .box-white').forEach(box => {
                box.classList.remove('box-dark', 'box-light');
                box.classList.add('box-white');
                box.setAttribute('contenteditable', 'false');
                box.setAttribute('title', 'Klik untuk memilih/mencentang');
            });

            paper.querySelectorAll('.checkbox-container, .result-item').forEach(el => {
                el.setAttribute('title', 'Klik untuk memilih/mencentang');
            });

            // 5. Normalisasi tata letak Kategori jika masih format lama (Sejajar 2 kolom x 2 baris)
            const catRow = Array.from(paper.querySelectorAll('tr')).find(tr => tr.innerText.includes('Kategori') || tr.innerText.includes('Category'));
            const catCell = catRow ? catRow.querySelector('td.val-col') || catRow.querySelector('td:last-child') : null;
            if (catCell) {
                const trList = catCell.querySelectorAll('table.category-table tr');
                if (!catCell.querySelector('.category-table') || trList.length < 2) {
                    const txt = catCell.innerText.toLowerCase();
                    const isLengkapChecked = catCell.querySelector('[data-cat="lengkap"] .box-white, [data-cat="lengkap"] .box-dark')?.innerText.trim() === '✓';
                    
                    let isHighRisk = false;
                    let isLowRisk = false;
                    catCell.querySelectorAll('.checkbox-container').forEach(c => {
                        const cTxt = c.innerText.toLowerCase();
                        const b = c.querySelector('.box-white, .box-dark, .box-light');
                        if (b && b.innerText.trim() === '✓') {
                            if (cTxt.includes('tinggi') || cTxt.includes('high')) isHighRisk = true;
                            if (cTxt.includes('rendah') || cTxt.includes('low')) isLowRisk = true;
                        }
                    });

                    const isSiteChecked = !isLengkapChecked && (isHighRisk || isLowRisk || txt.includes('site exam') || txt.includes('pemeriksaan di site'));
                    const chosenType = isSiteChecked ? 'site' : 'lengkap';
                    const chosenRisk = isHighRisk ? 'high_risk' : 'low_risk';

                    catCell.innerHTML = `
                        <table class="category-table" style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                    <span class="checkbox-container ${chosenType === 'site' ? 'is-strikethrough' : ''}" data-cat="lengkap" style="${chosenType === 'site' ? 'text-decoration: line-through;' : ''}">
                                        <span class="box-white" contenteditable="false">${chosenType === 'lengkap' ? '✓' : ''}</span>
                                        <span class="opt-label" style="${chosenType === 'site' ? 'text-decoration: line-through;' : ''}">Lengkap/<span class="en-label" style="${chosenType === 'site' ? 'text-decoration: line-through;' : ''}">Full</span></span>
                                    </span>
                                </td>
                                <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                    <span class="checkbox-container ${chosenType === 'lengkap' ? 'is-strikethrough' : ''}" data-cat="site" style="${chosenType === 'lengkap' ? 'text-decoration: line-through;' : ''}">
                                        <span class="box-white" contenteditable="false">${chosenType === 'site' ? '✓' : ''}</span>
                                        <span class="opt-label" style="${chosenType === 'lengkap' ? 'text-decoration: line-through;' : ''}">Pemeriksaan di site/<span class="en-label" style="${chosenType === 'lengkap' ? 'text-decoration: line-through;' : ''}">Site Exam</span></span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                    <span class="checkbox-container ${(chosenType === 'lengkap' || chosenRisk !== 'high_risk') ? 'is-strikethrough' : ''}" data-cat="high_risk" style="${(chosenType === 'lengkap' || chosenRisk !== 'high_risk') ? 'text-decoration: line-through;' : ''}">
                                        <span class="box-white" contenteditable="false">${(chosenType === 'site' && chosenRisk === 'high_risk') ? '✓' : ''}</span>
                                        <span class="opt-label" style="${(chosenType === 'lengkap' || chosenRisk !== 'high_risk') ? 'text-decoration: line-through;' : ''}">Resiko Tinggi/<span class="en-label" style="${(chosenType === 'lengkap' || chosenRisk !== 'high_risk') ? 'text-decoration: line-through;' : ''}">High Risk*</span></span>
                                    </span>
                                </td>
                                <td style="width: 50%; vertical-align: top; padding: 2px 0;">
                                    <span class="checkbox-container ${(chosenType === 'lengkap' || chosenRisk !== 'low_risk') ? 'is-strikethrough' : ''}" data-cat="low_risk" style="${(chosenType === 'lengkap' || chosenRisk !== 'low_risk') ? 'text-decoration: line-through;' : ''}">
                                        <span class="box-white" contenteditable="false">${(chosenType === 'site' && chosenRisk === 'low_risk') ? '✓' : ''}</span>
                                        <span class="opt-label" style="${(chosenType === 'lengkap' || chosenRisk !== 'low_risk') ? 'text-decoration: line-through;' : ''}">Resiko Rendah/<span class="en-label" style="${(chosenType === 'lengkap' || chosenRisk !== 'low_risk') ? 'text-decoration: line-through;' : ''}">Low Risk</span></span>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    `;
                }
            }
        }

        // Jalankan penguncian secara otomatis saat awal muat
        document.addEventListener('DOMContentLoaded', () => {
            enforceReadOnly();
        });
        enforceReadOnly();

        // Mencegah pengetikan atau penghapusan pada bagian Read-Only
        document.getElementById('letterPaper').addEventListener('keydown', function(e) {
            const sel = window.getSelection();
            if (!sel || !sel.anchorNode) return;

            let target = sel.anchorNode;
            if (target.nodeType === 3) target = target.parentElement;

            if (target && (target.closest('.doc-no') || target.closest('.notes-section') || target.closest('header') || target.closest('footer') || target.closest('.letter-header-locked') || target.closest('.letter-footer-locked'))) {
                // Izinkan tombol navigasi dan copy
                if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown', 'Tab'].includes(e.key)) {
                    return;
                }
                if ((e.ctrlKey || e.metaKey) && ['c', 'a'].includes(e.key.toLowerCase())) {
                    return;
                }

                e.preventDefault();
                notify("Bagian ini bersifat Read-Only dan tidak dapat diedit.", false);
            }
        });

        // Helper: Ubah status centang dan coret pada item Kategori
        function setCategoryItemState(item, isChecked, isStrikethrough) {
            if (!item) return;
            const box = item.querySelector('.box-white, .box-dark, .box-light');
            if (box) {
                box.innerText = isChecked ? '✓' : '';
                box.classList.remove('box-dark', 'box-light');
                box.classList.add('box-white');
            }
            if (isStrikethrough) {
                item.classList.add('is-strikethrough');
                item.style.textDecoration = 'line-through';
                item.style.color = '#64748b';
                item.querySelectorAll('.opt-label, .en-label, span:not(.box-white)').forEach(span => {
                    span.style.textDecoration = 'line-through';
                });
            } else {
                item.classList.remove('is-strikethrough');
                item.style.textDecoration = 'none';
                item.style.color = '';
                item.querySelectorAll('.opt-label, .en-label, span:not(.box-white)').forEach(span => {
                    span.style.textDecoration = 'none';
                });
            }
        }

        // Helper: Cari elemen Kategori dalam paper
        function findCategoryElements(paper) {
            let itemLengkap = paper.querySelector('[data-cat="lengkap"]');
            let itemSite = paper.querySelector('[data-cat="site"]');
            let itemHighRisk = paper.querySelector('[data-cat="high_risk"]');
            let itemLowRisk = paper.querySelector('[data-cat="low_risk"]');

            if (!itemLengkap || !itemSite || !itemHighRisk || !itemLowRisk) {
                paper.querySelectorAll('.checkbox-container').forEach(c => {
                    const txt = c.innerText.toLowerCase();
                    if (!itemLengkap && (txt.includes('lengkap') || txt.includes('full'))) {
                        itemLengkap = c;
                        c.setAttribute('data-cat', 'lengkap');
                    } else if (!itemSite && (txt.includes('site') || txt.includes('pemeriksaan di site'))) {
                        itemSite = c;
                        c.setAttribute('data-cat', 'site');
                    } else if (!itemHighRisk && (txt.includes('tinggi') || txt.includes('high'))) {
                        itemHighRisk = c;
                        c.setAttribute('data-cat', 'high_risk');
                    } else if (!itemLowRisk && (txt.includes('rendah') || txt.includes('low'))) {
                        itemLowRisk = c;
                        c.setAttribute('data-cat', 'low_risk');
                    }
                });
            }

            return { itemLengkap, itemSite, itemHighRisk, itemLowRisk };
        }

        // Handler Khusus Kategori: Logika Coret & Centang Otomatis
        function handleCategoryClick(catType, paper) {
            const { itemLengkap, itemSite, itemHighRisk, itemLowRisk } = findCategoryElements(paper);

            if (catType === 'lengkap') {
                // Ketika memilih Lengkap/Full:
                // Lengkap dicentang (tidak dicoret)
                setCategoryItemState(itemLengkap, true, false);
                // Pemeriksaan di Site dicoret dan tidak dicentang
                setCategoryItemState(itemSite, false, true);
                // Resiko Tinggi dan Resiko Rendah dicoret dan tidak dicentang
                setCategoryItemState(itemHighRisk, false, true);
                setCategoryItemState(itemLowRisk, false, true);
            } else if (catType === 'site') {
                // Ketika memilih Pemeriksaan di site:
                // Coret Lengkap/Full (tidak dicentang)
                setCategoryItemState(itemLengkap, false, true);
                // Centang Pemeriksaan di site (tidak dicoret)
                setCategoryItemState(itemSite, true, false);
                
                // Harus memilih Resiko Tinggi atau Rendah:
                // Jika Resiko Tinggi sudah aktif sebelumnya, pertahankan Resiko Tinggi & coret Resiko Rendah;
                // Jika tidak, default centang Resiko Rendah dan coret Resiko Tinggi
                const isHighRisk = itemHighRisk && itemHighRisk.querySelector('.box-white')?.innerText.trim() === '✓';
                if (isHighRisk) {
                    setCategoryItemState(itemHighRisk, true, false);
                    setCategoryItemState(itemLowRisk, false, true);
                } else {
                    setCategoryItemState(itemLowRisk, true, false);
                    setCategoryItemState(itemHighRisk, false, true);
                }
            } else if (catType === 'high_risk') {
                // Ketika memilih Resiko Tinggi:
                // Otomatis aktifkan Pemeriksaan di Site & coret Lengkap
                setCategoryItemState(itemLengkap, false, true);
                setCategoryItemState(itemSite, true, false);
                // Centang Resiko Tinggi (tidak dicoret)
                setCategoryItemState(itemHighRisk, true, false);
                // Coret Resiko Rendah (tidak dicentang)
                setCategoryItemState(itemLowRisk, false, true);
            } else if (catType === 'low_risk') {
                // Ketika memilih Resiko Rendah:
                // Otomatis aktifkan Pemeriksaan di Site & coret Lengkap
                setCategoryItemState(itemLengkap, false, true);
                setCategoryItemState(itemSite, true, false);
                // Centang Resiko Rendah (tidak dicoret)
                setCategoryItemState(itemLowRisk, true, false);
                // Coret Resiko Tinggi (tidak dicentang)
                setCategoryItemState(itemHighRisk, false, true);
            }

            markDirty();
        }

        // Clickable Checkbox Handler: Klik kotak atau teks pilihan untuk memilih / mencentang dengan rapih
        document.getElementById('letterPaper').addEventListener('click', function(e) {
            const target = e.target;
            if (target.closest('.doc-no') || target.closest('.notes-section') || target.closest('header') || target.closest('footer') || target.closest('.letter-header-locked') || target.closest('.letter-footer-locked')) {
                return;
            }

            const paper = document.getElementById('letterPaper');

            // 1. Cek apakah yang diklik adalah bagian Kategori (Lengkap, Site Exam, Resiko Tinggi, Resiko Rendah)
            let catContainer = target.closest('[data-cat]');
            if (!catContainer) {
                const testC = target.closest('.checkbox-container');
                if (testC) {
                    const txt = testC.innerText.toLowerCase();
                    if (txt.includes('lengkap') || txt.includes('full')) catContainer = testC, testC.setAttribute('data-cat', 'lengkap');
                    else if (txt.includes('site')) catContainer = testC, testC.setAttribute('data-cat', 'site');
                    else if (txt.includes('tinggi') || txt.includes('high')) catContainer = testC, testC.setAttribute('data-cat', 'high_risk');
                    else if (txt.includes('rendah') || txt.includes('low')) catContainer = testC, testC.setAttribute('data-cat', 'low_risk');
                }
            }

            if (catContainer) {
                const catType = catContainer.getAttribute('data-cat');
                if (catType) {
                    e.stopPropagation();
                    handleCategoryClick(catType, paper);
                    return;
                }
            }

            // 2. Temukan kotak centang atau pembungkus pilihan umum (misal Hasil Pemeriksaan)
            const container = target.closest('.checkbox-container') || target.closest('.result-item');
            const box = (target.classList.contains('box-white') || target.classList.contains('box-dark') || target.classList.contains('box-light'))
                ? target
                : (container ? container.querySelector('.box-white, .box-dark, .box-light') : null);

            if (!box) return;

            e.stopPropagation();
            const isCurrentlyChecked = box.innerText.trim() === '✓';

            // 3. Logika Hasil Pemeriksaan (Fit, Temporary Unfit, Unfit, Fit with Notes)
            const resultSection = box.closest('.result-section');
            if (resultSection) {
                if (!isCurrentlyChecked) {
                    resultSection.querySelectorAll('.box-white, .box-dark, .box-light').forEach(b => {
                        b.innerText = '';
                    });
                    box.innerText = '✓';
                } else {
                    box.innerText = '';
                }
                markDirty();
                return;
            }

            // 4. Toggle standar untuk kotak lainnya
            box.innerText = isCurrentlyChecked ? '' : '✓';
            markDirty();
        });

        // Simpan Surat: Otomatis tersimpan dan terfinalisasi
        async function saveLetter() {
            const saveBtn = document.getElementById('btnSave');
            const paper = document.getElementById('letterPaper');
            const saveStatusIndicator = document.getElementById('saveStatusIndicator');

            enforceReadOnly();
            const contentHtml = paper.innerHTML;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

            try {
                const response = await fetch("{{ route('mcu.fit-letter.save', $result->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: contentHtml,
                        status: 'final'
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Status server: ' + response.status);
                }

                const data = await response.json();

                if (data.success) {
                    isDirty = false;
                    if (saveStatusIndicator) {
                        saveStatusIndicator.innerHTML = '<i class="fas fa-check-circle text-xs"></i> Tersimpan sebagai Final (' + (data.updated_at || 'baru saja') + ')';
                        saveStatusIndicator.className = 'flex items-center gap-1.5 text-emerald-600 font-medium';
                    }

                    const badge = document.getElementById('badgeLetterStatus');
                    if (badge) {
                        badge.className = 'px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-emerald-600 text-white';
                        badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> FINAL';
                    }

                    if (data.updated_by) {
                        document.getElementById('lastUpdatedBy').innerText = data.updated_by;
                    }
                    if (data.updated_at) {
                        document.getElementById('lastUpdatedAt').innerText = data.updated_at;
                    }

                    saveBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Tersimpan!';
                    notify("Surat keterangan FIT berhasil disimpan sebagai FINAL!", true);

                    setTimeout(() => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Surat';
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Gagal menyimpan surat.');
                }
            } catch (err) {
                console.error(err);
                if (saveStatusIndicator) {
                    saveStatusIndicator.innerHTML = '<i class="fas fa-exclamation-triangle text-xs text-rose-500"></i> Gagal menyimpan';
                    saveStatusIndicator.className = 'flex items-center gap-1.5 text-rose-600 font-medium';
                }

                notify("Terjadi kesalahan: " + err.message, false);

                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Surat';
            }
        }

        // Reset Template to Default Confirmation
        async function confirmResetTemplate() {
            if (!confirm("Apakah Anda yakin ingin mengembalikan isi surat ke template standar?\n\nSemua perubahan teks yang telah diedit secara manual akan digantikan dengan format standar sistem.")) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch("{{ route('mcu.fit-letter.reset', $result->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success && data.content) {
                    document.getElementById('letterPaper').innerHTML = data.content;
                    enforceReadOnly();
                    markDirty();

                    const ind = document.getElementById('saveStatusIndicator');
                    if (ind) {
                        ind.innerHTML = '<i class="fas fa-check-circle text-xs"></i> Berhasil di-reset ke template standar';
                        ind.className = 'flex items-center gap-1.5 text-emerald-600 font-medium';
                    }

                    notify("Surat berhasil di-reset ke template resmi standar!", true);
                } else {
                    throw new Error(data.message || 'Gagal mereset template');
                }
            } catch (err) {
                console.error(err);
                notify("Gagal mereset template: " + err.message, false);
            }
        }

        // Keyboard Shortcut: Ctrl + S / Cmd + S to save
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveLetter();
            }
        });

        // Warn before leaving if unsaved changes exist
        window.addEventListener('beforeunload', function(e) {
            if (isDirty) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki perubahan draf surat yang belum disimpan. Tetap keluar?';
            }
        });
    </script>
</body>
</html>
