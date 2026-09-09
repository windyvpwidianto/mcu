<div>
    <div class="min-h-screen p-6 bg-base-200">
        <h1 class="mb-6 text-2xl font-bold text-base-content">Dashboard Medical Check-Up</h1>

        <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-3">

            <div class="p-4 border shadow bg-base-100 rounded-xl border-base-300">
                <h3 class="mb-2 text-lg font-semibold text-center">Tingkat Kehadiran MCU</h3>
                <div id="chartKehadiran" wire:ignore style="width: 100%; height: 300px;"></div>
            </div>

            <div class="p-4 border shadow bg-base-100 rounded-xl border-base-300">
                <h3 class="mb-2 text-lg font-semibold text-center">Sebaran Status Kebugaran</h3>
                <div id="chartFitStatus" wire:ignore style="width: 100%; height: 300px;"></div>
            </div>

            <div class="p-4 border shadow bg-base-100 rounded-xl border-base-300">
                <h3 class="mb-2 text-lg font-semibold text-center">Status Review Dokter</h3>
                <div id="chartWorkflow" wire:ignore style="width: 100%; height: 300px;"></div>
            </div>

            <div class="p-4 mb-6 border shadow bg-base-100 rounded-xl border-base-300">
                <h3 class="mb-2 text-lg font-semibold text-center">Top 10 Penyakit / Temuan Medis Terbanyak</h3>
                <div id="chartPenyakit" wire:ignore style="width: 100%; height: 350px;"></div>
            </div>
        </div>
    </div>
    <script>
        function initMcuCharts() {
            // Cek apakah elemen ada di halaman ini untuk menghindari error
            if (!document.getElementById('chartKehadiran')) return;

            // 1. PENTING: Bersihkan instance lama agar tidak terjadi error "Already initialized"
            const chartIds = ['chartKehadiran', 'chartFitStatus', 'chartWorkflow', 'chartPenyakit'];
            chartIds.forEach(id => {
                const dom = document.getElementById(id);
                if (dom) {
                    const existingInstance = echarts.getInstanceByDom(dom);
                    if (existingInstance) existingInstance.dispose();
                }
            });

            // 2. Grafik Kehadiran (Donut Chart)
            var chartKehadiran = echarts.init(document.getElementById('chartKehadiran'));
            chartKehadiran.setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    bottom: '0%',
                    left: 'center'
                },
                color: ['#10b981', '#ef4444', '#f59e0b'],
                series: [{
                    name: 'Kehadiran',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 20,
                            fontWeight: 'bold'
                        }
                    },
                    data: [{
                            value: {{ $sudahMcu ?? 0 }},
                            name: 'Sudah MCU'
                        },
                        {
                            value: {{ $terlewatMcu ?? 0 }},
                            name: 'Terlewat Jadwal'
                        },
                        {
                            value: {{ $menungguJadwal ?? 0 }},
                            name: 'Menunggu Jadwal'
                        }
                    ]
                }]
            });

            // 3. Grafik Status Kebugaran (Pie Chart)
            var chartFitStatus = echarts.init(document.getElementById('chartFitStatus'));
            chartFitStatus.setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    bottom: '0%',
                    left: 'center',
                    itemWidth: 10,
                    itemHeight: 10,
                    textStyle: {
                        fontSize: 10
                    }
                },
                color: ['#3b82f6', '#eab308', '#f97316', '#ef4444'],
                series: [{
                    name: 'Status',
                    type: 'pie',
                    radius: '60%',
                    data: [{
                            value: {{ $fitStatus['fit_to_work'] ?? 0 }},
                            name: '✅ Fit To Work'
                        },
                        {
                            value: {{ $fitStatus['fit_with_notes'] ?? 0 }},
                            name: '⚠️ Fit With Notes'
                        },
                        {
                            value: {{ $fitStatus['temporary_unfit'] ?? 0 }},
                            name: '⏳ Temp. Unfit'
                        },
                        {
                            value: {{ $fitStatus['unfit'] ?? 0 }},
                            name: '❌ Unfit'
                        }
                    ],
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });

            // 4. Grafik Workflow Review (Bar Chart)
            var chartWorkflow = echarts.init(document.getElementById('chartWorkflow'));
            chartWorkflow.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '15%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: ['Pending Doctor', 'Reviewed'],
                    axisLabel: {
                        fontWeight: 'bold'
                    }
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    name: 'Jumlah Data',
                    type: 'bar',
                    barWidth: '50%',
                    itemStyle: {
                        color: function(params) {
                            var colorList = ['#eab308', '#10b981'];
                            return colorList[params.dataIndex];
                        },
                        borderRadius: [4, 4, 0, 0]
                    },
                    data: [
                        {{ $workflowStatus['pending_doctor'] ?? 0 }},
                        {{ $workflowStatus['reviewed'] ?? 0 }}
                    ]
                }]
            });

            // 4. TAMBAHAN: Grafik Top Penyakit (Horizontal Bar Chart)
            var chartPenyakitDom = document.getElementById('chartPenyakit');
            if (chartPenyakitDom) {
                var chartPenyakit = echarts.init(chartPenyakitDom);
                chartPenyakit.setOption({
                    // Palet warna modern-soft (Matte palette) yang nyaman di mata
                    color: [
                        '#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ec4899',
                        '#8b5cf6', '#3b82f6', '#14b8a6', '#84cc16', '#f97316'
                    ],
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        top: '5%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'value',
                        boundaryGap: [0, 0.01]
                    },
                    yAxis: {
                        type: 'category',
                        data: {!! json_encode($diseaseNames ?? []) !!},
                        axisLabel: {
                            fontWeight: '500',
                            interval: 0
                        }
                    },
                    series: [{
                        name: 'Jumlah Kasus',
                        type: 'bar',
                        colorBy: 'data', // <-- INI KUNCI UTAMANYA: Mengambil warna berbeda per bar dari array color
                        data: {!! json_encode($diseaseCounts ?? []) !!},
                        itemStyle: {
                            borderRadius: [0, 4, 4, 0] // Rounded di ujung kanan bar
                        },
                        label: {
                            show: true,
                            position: 'right',
                            fontWeight: 'bold'
                        }
                    }]
                });
            }
        }


        // Jalankan saat pertama kali halaman dimuat
        document.addEventListener('livewire:initialized', initMcuCharts);

        // Jalankan setiap kali berpindah halaman via wire:navigate
        document.addEventListener('livewire:navigated', initMcuCharts);

        // Resize handling yang lebih aman
        window.addEventListener('resize', function() {
            ['chartKehadiran', 'chartFitStatus', 'chartWorkflow', 'chartPenyakit'].forEach(id => {
                const chart = echarts.getInstanceByDom(document.getElementById(id));
                if (chart) chart.resize();
            });
        });
    </script>
</div>
