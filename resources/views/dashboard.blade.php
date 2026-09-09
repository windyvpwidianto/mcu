<x-layouts.app :title="__('Dashboard')">
    <div class="p-4 space-y-2">

        {{-- Header --}}
        <h1 class="text-2xl font-bold">Hazard Report Dashboard</h1>
        <p class="text-gray-600">Ringkasan kondisi laporan hazard terkini</p>

        {{-- Statistik Ringkas --}}
        <div class="stats stats-vertical lg:stats-horizontal shadow w-full">
            <div class="stat ">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div class="stat-title">Total Laporan</div>
                <div class="stat-value text-primary">25.6K</div>
                <div class="stat-desc">21% more than last month</div>
            </div>

            <div class="stat ">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="stat-title">Sedang Diproses</div>
                <div class="stat-value text-secondary">2.6M</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
            <div class="stat ">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="stat-title">Overdue</div>
                <div class="stat-value text-secondary">2.6M</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
            <div class="stat ">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-8 w-8 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="stat-title">Overdue</div>
                <div class="stat-value text-secondary">2.6M</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
        </div>

        {{-- Grafik --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-4 rounded-xl shadow">
                <h3 class="font-semibold mb-2">Trend Laporan per Bulan</h3>
                <div id="trendChart" class="h-64"></div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow">
                <h3 class="font-semibold mb-2">Distribusi Berdasarkan Status</h3>
                <div id="statusChart" class="h-64"></div>
            </div>
        </div>

        {{-- Daftar Laporan Terbaru --}}
        <div class="bg-white p-4 rounded-xl shadow">
            <h3 class="font-semibold mb-4">Laporan Hazard Terbaru</h3>
            <table class="table table-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 border">ID</th>
                        <th class="px-3 py-2 border">Judul</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Pelapor</th>
                        <th class="px-3 py-2 border">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>5</td>
                </tbody>
            </table>
        </div>
    </div>
    {{-- Tambahkan ChartJS / ApexCharts --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartDom = document.getElementById('trendChart');
            const myChart = echarts.init(chartDom);

            const option = {
                title: {
                    text: 'Trend Laporan Hazard per Bulan'
                    , left: 'center'
                }
                , tooltip: {
                    trigger: 'axis'
                    , formatter: (params) => {
                        const data = params[0];
                        return `${data.axisValue} : ${data.data} laporan`;
                    }
                }
                , xAxis: {
                    type: 'category'
                    , data: @json($labels)
                }
                , yAxis: {
                    type: 'value'
                    , name: 'Jumlah Laporan'
                    , minInterval: 1
                }
                , series: [{
                    data: @json($values)
                    , type: 'line'
                    , smooth: true
                    , symbol: 'circle'
                    , symbolSize: 8
                    , lineStyle: {
                        color: '#2563eb'
                        , width: 3
                    }
                    , areaStyle: {
                        color: 'rgba(37, 99, 235, 0.2)'
                    }
                    , itemStyle: {
                        color: '#2563eb'
                    }
                }]
                , grid: {
                    left: '5%'
                    , right: '5%'
                    , bottom: '10%'
                    , containLabel: true
                }
            };

            myChart.setOption(option);
        });

    </script>
    @endpush
</x-layouts.app>
