<div>
    <div class="border bg-base-100 border-base-200" wire:ignore id="container_reportby" style="height: 355px"></div>

    <script type="module">
        const initHazardUserChart = () => {
            const dom_reportBy = document.getElementById('container_reportby');
            if (!dom_reportBy) return;

            // Pembersihan instance lama
            let existingChart = echarts.getInstanceByDom(dom_reportBy);
            if (existingChart) {
                existingChart.dispose();
            }

            // --- 1. LOKALISASI TEKS (Bilingual) ---
            const i18n = {
                title: @json(__('Top Kontributor (Jumlah Laporan)')),
                seriesName: @json(__('Jumlah Laporan')),
            };

            // Ambil data dari PHP
            const pelaporData = @json($pelapor);
            const pelapor = typeof pelaporData === 'string' ? JSON.parse(pelaporData) : pelaporData;

            const myChart_reportBy = echarts.init(dom_reportBy);

            const getThemeColor = (variable) => {
                const temp = document.createElement('div');
                temp.style.color = `var(${variable})`;
                document.body.appendChild(temp);
                const style = getComputedStyle(temp).color;
                document.body.removeChild(temp);
                return style;
            };

            const fetchColors = () => ({
                primary: getThemeColor('--color-primary'),
                content: getThemeColor('--color-base-content'),
                base100: getThemeColor('--color-base-100'),
                base300: getThemeColor('--color-base-300'),
            });

            let theme = fetchColors();

            function generateColor(index) {
                const seed = Math.sin(index + 1) * 10000;
                const hue = (seed - Math.floor(seed)) * 360;
                return `hsl(${hue}, 70%, 55%)`;
            }

            const option_reportBy = {
                backgroundColor: 'transparent',
                title: {
                    text: i18n.title, // MENGGUNAKAN i18n
                    left: 'center',
                    textStyle: {
                        color: theme.content,
                        fontFamily: 'Poppins, sans-serif',
                        fontSize: 14
                    },
                    subtext: pelapor.range,
                    subtextStyle: {
                        color: theme.content,
                        opacity: 0.7
                    }
                },
                grid: {
                    top: 60,
                    left: '3%',
                    right: '10%',
                    bottom: '5%',
                    containLabel: true
                },
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: theme.base100,
                    borderColor: theme.primary,
                    borderWidth: 1,
                    textStyle: {
                        color: theme.content
                    },
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                xAxis: {
                    type: 'value',
                    axisLabel: {
                        color: theme.content
                    },
                    splitLine: {
                        lineStyle: {
                            color: theme.base300,
                            type: 'dashed'
                        }
                    }
                },
                yAxis: {
                    type: 'category',
                    data: pelapor.label,
                    inverse: true,
                    axisLabel: {
                        color: theme.content,
                        fontSize: 10,
                        fontWeight: 'bold',
                        width: 120,
                        overflow: 'truncate'
                    }
                },
                series: [{
                    name: i18n.seriesName, // MENGGUNAKAN i18n
                    type: 'bar',
                    data: pelapor.counts,
                    barMaxWidth: 25,
                    itemStyle: {
                        color: params => generateColor(params.dataIndex),
                        borderRadius: [0, 6, 6, 0]
                    },
                    label: {
                        show: true,
                        position: 'right',
                        color: theme.content
                    }
                }]
            };

            myChart_reportBy.setOption(option_reportBy);

            // Livewire Event Listener
            Livewire.on('distribusiPelapor', event => {
                const rawPayload = Array.isArray(event) ? event[0] : event;
                const payload = typeof rawPayload === 'string' ? JSON.parse(rawPayload) : rawPayload;

                myChart_reportBy.setOption({
                    title: {
                        subtext: payload.range
                    },
                    yAxis: {
                        data: payload.label
                    },
                    series: [{
                        data: payload.counts
                    }]
                });
            });

            window.addEventListener('resize', () => myChart_reportBy.resize());
        };

        // Jalankan
        initHazardUserChart();

        document.addEventListener('livewire:navigated', () => {
            initHazardUserChart();
        });
    </script>
</div>