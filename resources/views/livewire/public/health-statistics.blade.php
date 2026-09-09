<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Dashboard Statistik Kesehatan dan Keselamatan Kerja (K3) - Laporan real-time Hazard, Incident, dan MCU perusahaan." />
    <title>Dashboard Statistik Kesehatan & K3</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root {
            --color-primary:   #6366f1;
            --color-secondary: #8b5cf6;
            --color-accent:    #06b6d4;
            --color-success:   #10b981;
            --color-warning:   #f59e0b;
            --color-danger:    #ef4444;
            --color-bg:        #0f0f1a;
            --color-surface:   #1a1a2e;
            --color-card:      rgba(255,255,255,0.04);
            --color-border:    rgba(255,255,255,0.08);
        }

        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        body {
            background: var(--color-bg);
            color: #e2e8f0;
            min-height: 100vh;
            margin: 0;
        }

        /* ── Animated gradient background ─────────────────────── */
        .bg-hero {
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(99,102,241,0.25) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 90% 80%, rgba(139,92,246,0.15) 0%, transparent 60%),
                linear-gradient(180deg, #0f0f1a 0%, #0d0d1f 100%);
        }

        /* ── Glass card ────────────────────────────────────────── */
        .glass-card {
            background: var(--color-card);
            border: 1px solid var(--color-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 1rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .glass-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.3);
        }

        /* ── KPI Card ──────────────────────────────────────────── */
        .kpi-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
            border: 1px solid var(--color-border);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.03));
            pointer-events: none;
        }
        .kpi-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99,102,241,0.4);
            box-shadow: 0 16px 40px -8px rgba(99,102,241,0.25);
        }

        /* ── KPI Colour Variants ─────────────────────────────── */
        .kpi-indigo  { --kpi-color: #6366f1; }
        .kpi-violet  { --kpi-color: #8b5cf6; }
        .kpi-cyan    { --kpi-color: #06b6d4; }
        .kpi-emerald { --kpi-color: #10b981; }

        .kpi-card .kpi-icon-wrap {
            background: rgba(var(--kpi-rgb, 99,102,241), 0.15);
            border: 1px solid rgba(var(--kpi-rgb, 99,102,241), 0.25);
            border-radius: 0.75rem;
            width: 3rem; height: 3rem;
            display: flex; align-items: center; justify-content: center;
        }
        .kpi-indigo  .kpi-icon-wrap { background: rgba(99,102,241,.15);  border-color: rgba(99,102,241,.25); }
        .kpi-violet  .kpi-icon-wrap { background: rgba(139,92,246,.15);  border-color: rgba(139,92,246,.25); }
        .kpi-cyan    .kpi-icon-wrap { background: rgba(6,182,212,.15);   border-color: rgba(6,182,212,.25); }
        .kpi-emerald .kpi-icon-wrap { background: rgba(16,185,129,.15);  border-color: rgba(16,185,129,.25); }

        .kpi-value {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, #fff 50%, rgba(255,255,255,0.7));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .kpi-indigo  .kpi-value { background: linear-gradient(135deg, #a5b4fc, #6366f1); -webkit-background-clip:text; background-clip:text; }
        .kpi-violet  .kpi-value { background: linear-gradient(135deg, #c4b5fd, #8b5cf6); -webkit-background-clip:text; background-clip:text; }
        .kpi-cyan    .kpi-value { background: linear-gradient(135deg, #67e8f9, #06b6d4); -webkit-background-clip:text; background-clip:text; }
        .kpi-emerald .kpi-value { background: linear-gradient(135deg, #6ee7b7, #10b981); -webkit-background-clip:text; background-clip:text; }

        /* ── Progress bar ──────────────────────────────────────── */
        .progress-bar {
            height: 6px;
            background: rgba(255,255,255,0.07);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 0.75rem;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 1s ease;
        }

        /* ── Section headings ─────────────────────────────────── */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.01em;
        }
        .section-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        /* ── Navbar ────────────────────────────────────────────── */
        .topnav {
            background: rgba(15,15,26,0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--color-border);
            position: sticky; top: 0; z-index: 100;
        }

        /* ── Select pill ───────────────────────────────────────── */
        .select-pill {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.5rem;
            color: #e2e8f0;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            cursor: pointer;
            appearance: none;
            outline: none;
            transition: border-color 0.2s;
        }
        .select-pill:focus { border-color: #6366f1; }

        /* ── Badge ─────────────────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.7rem; font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
        }
        .badge-success { background: rgba(16,185,129,.15); color: #6ee7b7; }
        .badge-danger  { background: rgba(239,68,68,.15);  color: #fca5a5; }
        .badge-warn    { background: rgba(245,158,11,.15); color: #fcd34d; }

        /* ── Scrollbar ─────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); border-radius: 999px; }

        /* ── Pulse dot ─────────────────────────────────────────── */
        @keyframes pulse-ring {
            0%   { transform: scale(.8); opacity: 1; }
            100% { transform: scale(2.2); opacity: 0; }
        }
        .pulse-dot {
            width: 8px; height: 8px;
            background: #10b981;
            border-radius: 50%;
            position: relative;
        }
        .pulse-dot::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: #10b981;
            animation: pulse-ring 1.8s ease-out infinite;
        }

        /* ── Chart container ───────────────────────────────────── */
        .chart-box { min-height: 280px; }

        /* ── Animate on mount ──────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity:0; transform: translateY(20px); }
            to   { opacity:1; transform: translateY(0); }
        }
        .fade-up   { animation: fadeUp 0.5s ease both; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
    </style>
</head>

<body class="bg-hero">

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- NAV                                                        --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <nav class="topnav px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            {{-- Logo / Brand --}}
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-700 text-white leading-tight" style="font-weight:700">HealthStats K3</p>
                <p class="text-xs text-slate-500">Safety & Health Analytics</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            {{-- Live indicator --}}
            <div class="hidden sm:flex items-center gap-2">
                <div class="pulse-dot"></div>
                <span class="text-xs text-slate-400">Live Data</span>
            </div>

            {{-- Login button (jika user belum login) --}}
            @guest
            <a href="{{ route('login') }}"
               class="text-xs font-medium px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-colors">
                Masuk
            </a>
            @else
            <a href="{{ route('dashboard') }}"
               class="text-xs font-medium px-4 py-2 rounded-lg bg-white/10 hover:bg-white/15 text-white transition-colors">
                Dashboard Internal →
            </a>
            @endguest
        </div>
    </nav>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- HERO SECTION                                               --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <header class="px-6 pt-12 pb-8 max-w-7xl mx-auto fade-up">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 rounded-full px-3 py-1 mb-4">
                    <svg class="w-3 h-3 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <span class="text-xs text-indigo-300 font-medium">Dashboard Resmi K3</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-800 text-white leading-tight" style="font-weight:800">
                    Statistik Kesehatan &<br class="hidden sm:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">
                        Keselamatan Kerja
                    </span>
                </h1>
                <p class="mt-2 text-slate-400 text-sm max-w-lg">
                    Monitoring real-time laporan Hazard, Incident, dan Medical Check-Up (MCU) seluruh perusahaan.
                </p>
            </div>

            {{-- Pilih Tahun --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Tahun:</span>
                <select wire:model.live="selectedYear" class="select-pill">
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- MAIN CONTENT                                               --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <main class="px-6 pb-16 max-w-7xl mx-auto space-y-6">

        {{-- ── KPI CARDS ───────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Hazard Total --}}
            <div class="kpi-card kpi-indigo fade-up delay-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="kpi-icon-wrap">
                        <svg class="w-5 h-5" style="color:#818cf8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    @if($kpiSummary['total_hazard'] > 0)
                        <span class="badge badge-warn">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            Aktif
                        </span>
                    @endif
                </div>
                <p class="kpi-value">{{ number_format($kpiSummary['total_hazard']) }}</p>
                <p class="text-xs text-slate-400 mt-1 font-medium">Total Laporan Hazard</p>
                <div class="progress-bar">
                    <div class="progress-bar-fill"
                         style="width:{{ min($kpiSummary['hazard_close_rate'], 100) }}%;
                                background: linear-gradient(90deg, #4f46e5, #818cf8)"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">{{ $kpiSummary['hazard_close_rate'] }}% selesai ditangani</p>
            </div>

            {{-- Incident Total --}}
            <div class="kpi-card kpi-violet fade-up delay-200">
                <div class="flex items-start justify-between mb-3">
                    <div class="kpi-icon-wrap">
                        <svg class="w-5 h-5" style="color:#a78bfa" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    @if($kpiSummary['total_incident'] > 0)
                        <span class="badge badge-danger">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                            Perlu Tindak
                        </span>
                    @endif
                </div>
                <p class="kpi-value">{{ number_format($kpiSummary['total_incident']) }}</p>
                <p class="text-xs text-slate-400 mt-1 font-medium">Total Laporan Incident</p>
                <div class="progress-bar">
                    <div class="progress-bar-fill"
                         style="width:{{ $kpiSummary['total_incident'] > 0 ? min($kpiSummary['total_incident'] * 5, 100) : 0 }}%;
                                background: linear-gradient(90deg, #7c3aed, #a78bfa)"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">Tahun {{ $selectedYear }}</p>
            </div>

            {{-- MCU Total --}}
            <div class="kpi-card kpi-cyan fade-up delay-300">
                <div class="flex items-start justify-between mb-3">
                    <div class="kpi-icon-wrap">
                        <svg class="w-5 h-5" style="color:#22d3ee" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <span class="badge badge-success">MCU</span>
                </div>
                <p class="kpi-value">{{ number_format($kpiSummary['total_mcu']) }}</p>
                <p class="text-xs text-slate-400 mt-1 font-medium">Total Peserta MCU</p>
                <div class="progress-bar">
                    <div class="progress-bar-fill"
                         style="width:{{ min($kpiSummary['mcu_fit_rate'], 100) }}%;
                                background: linear-gradient(90deg, #0891b2, #22d3ee)"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">{{ $kpiSummary['mcu_fit_rate'] }}% hasil Fit</p>
            </div>

            {{-- MCU Fit --}}
            <div class="kpi-card kpi-emerald fade-up delay-400">
                <div class="flex items-start justify-between mb-3">
                    <div class="kpi-icon-wrap">
                        <svg class="w-5 h-5" style="color:#34d399" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="badge badge-success">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Fit
                    </span>
                </div>
                <p class="kpi-value">{{ number_format($kpiSummary['fit_mcu']) }}</p>
                <p class="text-xs text-slate-400 mt-1 font-medium">Karyawan Fit MCU</p>
                <div class="progress-bar">
                    <div class="progress-bar-fill"
                         style="width:{{ min($kpiSummary['mcu_fit_rate'], 100) }}%;
                                background: linear-gradient(90deg, #059669, #34d399)"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">dari {{ $kpiSummary['total_mcu'] }} total peserta</p>
            </div>
        </div>

        {{-- ── ROW 2 : LINE CHART + DONUT CHART ────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 fade-up delay-300">

            {{-- Line Chart - Tren Bulanan (2/3 lebar) --}}
            <div class="glass-card p-5 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="section-title">Tren Hazard & Incident</p>
                        <p class="section-subtitle">Per bulan sepanjang tahun {{ $selectedYear }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-0.5 rounded bg-indigo-400 inline-block"></span>
                            <span class="text-slate-400">Hazard</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-0.5 rounded bg-violet-400 inline-block"></span>
                            <span class="text-slate-400">Incident</span>
                        </span>
                    </div>
                </div>
                <div id="chart-trend" class="chart-box" wire:ignore></div>
            </div>

            {{-- Donut Chart - Distribusi MCU (1/3 lebar) --}}
            <div class="glass-card p-5 flex flex-col">
                <div class="mb-4">
                    <p class="section-title">Distribusi Hasil MCU</p>
                    <p class="section-subtitle">Fit / Fit with Note / Unfit</p>
                </div>
                <div id="chart-mcu" class="chart-box flex-1" wire:ignore></div>
            </div>
        </div>

        {{-- ── ROW 3 : BAR CHART (DEPT) + RISK LEVEL ───────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 fade-up delay-400">

            {{-- Bar Chart - Top Department --}}
            <div class="glass-card p-5">
                <div class="mb-4">
                    <p class="section-title">Top 5 Departemen – Hazard Terbanyak</p>
                    <p class="section-subtitle">Berdasarkan jumlah laporan masuk tahun {{ $selectedYear }}</p>
                </div>
                <div id="chart-dept" class="chart-box" wire:ignore></div>
            </div>

            {{-- Bar Chart - Risk Distribution --}}
            <div class="glass-card p-5">
                <div class="mb-4">
                    <p class="section-title">Distribusi Tingkat Risiko Hazard</p>
                    <p class="section-subtitle">Low / Medium / High / Critical</p>
                </div>
                <div id="chart-risk" class="chart-box" wire:ignore></div>
            </div>
        </div>

        {{-- ── FOOTER ───────────────────────────────────────────── --}}
        <div class="text-center pt-8 fade-up delay-500">
            <p class="text-xs text-slate-600">
                Data diperbarui otomatis · Dashboard Statistik K3 &copy; {{ date('Y') }}
            </p>
        </div>

    </main>

    @livewireScripts

    {{-- ECharts --}}
    <script type="module">
        import * as echarts from 'https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.esm.min.js';

        // ── Palet Warna Global ─────────────────────────────────
        const COLORS = {
            indigo:  '#6366f1',
            violet:  '#8b5cf6',
            cyan:    '#06b6d4',
            emerald: '#10b981',
            amber:   '#f59e0b',
            red:     '#ef4444',
            slate:   '#475569',
        };

        const baseTextStyle = { color: '#64748b', fontFamily: 'Inter, sans-serif', fontSize: 11 };
        const baseAxis = {
            axisLine:  { lineStyle: { color: 'rgba(255,255,255,0.07)' } },
            splitLine: { lineStyle: { color: 'rgba(255,255,255,0.05)', type: 'dashed' } },
            axisLabel: { color: '#64748b', fontFamily: 'Inter, sans-serif', fontSize: 11 },
            axisTick:  { show: false },
        };

        // ─────────────────────────────────────────────────────────
        // 1. LINE CHART – Tren Hazard & Incident
        // ─────────────────────────────────────────────────────────
        const trendData  = @json($monthlyTrend);
        const chartTrend = echarts.init(document.getElementById('chart-trend'), null, { renderer: 'svg' });

        chartTrend.setOption({
            backgroundColor: 'transparent',
            textStyle: baseTextStyle,
            tooltip: {
                trigger: 'axis',
                backgroundColor: 'rgba(15,15,26,0.92)',
                borderColor: 'rgba(99,102,241,0.3)',
                borderWidth: 1,
                textStyle: { color: '#e2e8f0', fontSize: 12 },
                axisPointer: { type: 'cross', lineStyle: { color: 'rgba(255,255,255,0.15)' } },
            },
            grid: { left: '3%', right: '3%', bottom: '3%', top: '6%', containLabel: true },
            xAxis: { type: 'category', data: trendData.labels, ...baseAxis },
            yAxis: { type: 'value', minInterval: 1, ...baseAxis },
            series: [
                {
                    name: 'Hazard',
                    type: 'line',
                    data: trendData.hazard,
                    smooth: true,
                    symbol: 'circle', symbolSize: 6,
                    lineStyle: { color: COLORS.indigo, width: 2.5 },
                    itemStyle: { color: COLORS.indigo, borderWidth: 2, borderColor: '#fff' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(99,102,241,0.3)' },
                            { offset: 1, color: 'rgba(99,102,241,0.0)' },
                        ])
                    },
                },
                {
                    name: 'Incident',
                    type: 'line',
                    data: trendData.incident,
                    smooth: true,
                    symbol: 'circle', symbolSize: 6,
                    lineStyle: { color: COLORS.violet, width: 2.5 },
                    itemStyle: { color: COLORS.violet, borderWidth: 2, borderColor: '#fff' },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(139,92,246,0.25)' },
                            { offset: 1, color: 'rgba(139,92,246,0.0)' },
                        ])
                    },
                },
            ],
        });

        // ─────────────────────────────────────────────────────────
        // 2. DONUT CHART – Distribusi MCU
        // ─────────────────────────────────────────────────────────
        const mcuData  = @json($mcuDistribution);
        const chartMcu = echarts.init(document.getElementById('chart-mcu'), null, { renderer: 'svg' });

        const totalMcu = mcuData.fit + mcuData.unfit + mcuData.fit_with_note;

        chartMcu.setOption({
            backgroundColor: 'transparent',
            textStyle: baseTextStyle,
            tooltip: {
                trigger: 'item',
                backgroundColor: 'rgba(15,15,26,0.92)',
                borderColor: 'rgba(99,102,241,0.3)',
                textStyle: { color: '#e2e8f0', fontSize: 12 },
                formatter: (p) => `${p.name}<br/><b>${p.value}</b> orang (${p.percent.toFixed(1)}%)`,
            },
            legend: {
                bottom: '2%', orient: 'horizontal',
                textStyle: { color: '#94a3b8', fontSize: 11 },
                itemWidth: 10, itemHeight: 10,
            },
            series: [{
                type: 'pie',
                radius: ['50%', '78%'],
                center: ['50%', '44%'],
                avoidLabelOverlap: false,
                itemStyle: { borderRadius: 6, borderColor: 'transparent', borderWidth: 2 },
                label: {
                    show: true, position: 'center',
                    formatter: () => [`{val|${totalMcu}}`, `{lbl|Peserta}`].join('\n'),
                    rich: {
                        val: { fontSize: 28, fontWeight: 800, color: '#f1f5f9', lineHeight: 36 },
                        lbl: { fontSize: 11, color: '#64748b', lineHeight: 18 },
                    },
                },
                emphasis: { label: { show: true }, scale: true, scaleSize: 8 },
                data: [
                    { value: mcuData.fit,           name: 'Fit',           itemStyle: { color: COLORS.emerald } },
                    { value: mcuData.fit_with_note,  name: 'Fit with Note', itemStyle: { color: COLORS.amber } },
                    { value: mcuData.unfit,          name: 'Unfit',         itemStyle: { color: COLORS.red } },
                ],
            }],
        });

        // ─────────────────────────────────────────────────────────
        // 3. BAR CHART – Top Departemen
        // ─────────────────────────────────────────────────────────
        const deptData  = @json($topDepartments);
        const chartDept = echarts.init(document.getElementById('chart-dept'), null, { renderer: 'svg' });

        chartDept.setOption({
            backgroundColor: 'transparent',
            textStyle: baseTextStyle,
            tooltip: {
                trigger: 'axis', axisPointer: { type: 'shadow' },
                backgroundColor: 'rgba(15,15,26,0.92)',
                borderColor: 'rgba(99,102,241,0.3)',
                textStyle: { color: '#e2e8f0', fontSize: 12 },
            },
            grid: { left: '3%', right: '4%', bottom: '3%', top: '6%', containLabel: true },
            xAxis: { type: 'value', ...baseAxis },
            yAxis: {
                type: 'category',
                data: deptData.labels.map(l => l.length > 18 ? l.substring(0, 18) + '…' : l),
                ...baseAxis,
                axisLabel: { ...baseAxis.axisLabel, width: 120, overflow: 'truncate' },
            },
            series: [{
                type: 'bar',
                data: deptData.values,
                barMaxWidth: 28,
                itemStyle: {
                    borderRadius: [0, 6, 6, 0],
                    color: new echarts.graphic.LinearGradient(1, 0, 0, 0, [
                        { offset: 0, color: COLORS.indigo },
                        { offset: 1, color: 'rgba(99,102,241,0.35)' },
                    ]),
                },
                emphasis: { itemStyle: { color: COLORS.violet } },
                label: { show: true, position: 'insideRight', color: '#fff', fontSize: 11, fontWeight: 600 },
            }],
        });

        // ─────────────────────────────────────────────────────────
        // 4. BAR CHART – Risk Level Distribution
        // ─────────────────────────────────────────────────────────
        const riskData  = @json($riskDistribution);
        const chartRisk = echarts.init(document.getElementById('chart-risk'), null, { renderer: 'svg' });

        const riskColors = {
            Low:      '#10b981',
            Medium:   '#f59e0b',
            High:     '#ef4444',
            Critical: '#7f1d1d',
        };

        chartRisk.setOption({
            backgroundColor: 'transparent',
            textStyle: baseTextStyle,
            tooltip: {
                trigger: 'axis', axisPointer: { type: 'shadow' },
                backgroundColor: 'rgba(15,15,26,0.92)',
                borderColor: 'rgba(99,102,241,0.3)',
                textStyle: { color: '#e2e8f0', fontSize: 12 },
            },
            grid: { left: '3%', right: '4%', bottom: '3%', top: '6%', containLabel: true },
            xAxis: {
                type: 'category',
                data: ['Low', 'Medium', 'High', 'Critical'],
                ...baseAxis,
            },
            yAxis: { type: 'value', minInterval: 1, ...baseAxis },
            series: [{
                type: 'bar',
                data: [
                    { value: riskData.low,      itemStyle: { color: riskColors.Low,      borderRadius: [6, 6, 0, 0] } },
                    { value: riskData.medium,   itemStyle: { color: riskColors.Medium,   borderRadius: [6, 6, 0, 0] } },
                    { value: riskData.high,     itemStyle: { color: riskColors.High,     borderRadius: [6, 6, 0, 0] } },
                    { value: riskData.critical, itemStyle: { color: riskColors.Critical, borderRadius: [6, 6, 0, 0] } },
                ],
                barMaxWidth: 52,
                label: {
                    show: true, position: 'top',
                    color: '#94a3b8', fontSize: 12, fontWeight: 700,
                },
            }],
        });

        // ─────────────────────────────────────────────────────────
        // Responsive resize
        // ─────────────────────────────────────────────────────────
        window.addEventListener('resize', () => {
            chartTrend.resize();
            chartMcu.resize();
            chartDept.resize();
            chartRisk.resize();
        });

        // Re-render ketika Livewire update data (pilih tahun lain)
        document.addEventListener('livewire:navigated', () => {
            chartTrend.resize();
            chartMcu.resize();
            chartDept.resize();
            chartRisk.resize();
        });
    </script>
</body>
</html>
