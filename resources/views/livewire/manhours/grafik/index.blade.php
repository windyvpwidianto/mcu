<div class="grid grid-cols-1 gap-2 mb-10 lg:grid-cols-2">
    <div wire:ignore id="grafik-manhours" style="height: 320px"></div>
    <div wire:ignore id="grafik-manpower" style="height: 320px"></div>

    <script type="module">
        /**
         * Fungsi utama untuk inisialisasi semua grafik.
         * Dibungkus agar bisa dipanggil ulang saat navigasi Livewire (SPA).
         */
        const initSafetyCharts = () => {
            // 1. Ambil data dari PHP
            const dataMH = @json($data);
            const dataMP = @json($manpowerData);
            const currentYear = @json($years);

            // 2. Helper untuk inisialisasi atau pembersihan instance ECharts
            const setupChart = (elementId) => {
                const dom = document.getElementById(elementId);
                if (!dom) return null;

                // Dispose instance lama jika ada (penting agar tidak error saat navigasi balik)
                let existingChart = echarts.getInstanceByDom(dom);
                if (existingChart) {
                    existingChart.dispose();
                }
                return echarts.init(dom);
            };

            const myChartMH = setupChart('grafik-manhours');
            const myChartMP = setupChart('grafik-manpower');

            // 3. Fungsi pembuat opsi (karena strukturnya mirip)
            const createOption = (titlePrefix, data, year) => {
                // Parse jika data berupa string JSON
                const parsedData = typeof data === 'string' ? JSON.parse(data) : data;

                return {
                    title: {
                        text: titlePrefix + ' Bulanan Tahun ' + (year || '')
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'line',
                            lineStyle: {
                                type: 'dashed'
                            }
                        }
                    },
                    legend: {
                        data: ['PT. MSM', 'PT. TTN', 'CONTRACTOR'],
                        selected: (function() {
                            let sel = {
                                'PT. MSM': true,
                                'PT. TTN': true,
                                'CONTRACTOR': true
                            };
                            if (parsedData.hidden_legends) {
                                parsedData.hidden_legends.forEach(name => {
                                    sel[name] = false;
                                });
                            }
                            return sel;
                        })()
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    toolbox: {
                        feature: {
                            saveAsImage: {}
                        }
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: parsedData.months
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                            name: 'PT. MSM',
                            type: 'line',
                            data: parsedData.msm,
                            emphasis: {
                                focus: 'series'
                            }
                        },
                        {
                            name: 'PT. TTN',
                            type: 'line',
                            data: parsedData.ttn,
                            emphasis: {
                                focus: 'series'
                            }
                        },
                        {
                            name: 'CONTRACTOR',
                            type: 'line',
                            data: parsedData.contractor,
                            emphasis: {
                                focus: 'series'
                            }
                        }
                    ]
                };
            };

            // 4. Set Opsi Awal
            if (myChartMH) myChartMH.setOption(createOption('Manhours', dataMH, currentYear));
            if (myChartMP) myChartMP.setOption(createOption('Manpower', dataMP, currentYear));

            // 5. Livewire Event Listeners (v3 menggunakan array untuk payload)
            Livewire.on('manhoursChart', (event) => {
                if (!myChartMH) return;
                const payload = typeof event[0] === 'string' ? JSON.parse(event[0]) : event[0];

                let selMH = {
                    'PT. MSM': true,
                    'PT. TTN': true,
                    'CONTRACTOR': true
                };
                if (payload.hidden_legends) {
                    payload.hidden_legends.forEach(name => {
                        selMH[name] = false;
                    });
                }

                myChartMH.setOption({
                    legend: {
                        selected: selMH
                    },
                    xAxis: {
                        data: payload.months
                    },
                    series: [{
                            data: payload.msm
                        },
                        {
                            data: payload.ttn
                        },
                        {
                            data: payload.contractor
                        }
                    ]
                });
            });

            Livewire.on('manpowerChart', (event) => {
                if (!myChartMP) return;
                const payload = typeof event[0] === 'string' ? JSON.parse(event[0]) : event[0];

                let selMP = {
                    'PT. MSM': true,
                    'PT. TTN': true,
                    'CONTRACTOR': true
                };
                if (payload.hidden_legends) {
                    payload.hidden_legends.forEach(name => {
                        selMP[name] = false;
                    });
                }

                myChartMP.setOption({
                    legend: {
                        selected: selMP
                    },
                    xAxis: {
                        data: payload.months
                    },
                    series: [{
                            data: payload.msm
                        },
                        {
                            data: payload.ttn
                        },
                        {
                            data: payload.contractor
                        }
                    ]
                });
            });

            // 6. Handle Resize
            const resizeCharts = () => {
                myChartMH?.resize();
                myChartMP?.resize();
            };
            window.addEventListener('resize', resizeCharts);
        };

        // Jalankan saat halaman pertama kali dimuat
        initSafetyCharts();

        // Jalankan ulang setiap kali navigasi Livewire selesai (wire:navigate)
        document.addEventListener('livewire:navigated', () => {
            initSafetyCharts();
        });
    </script>
</div>