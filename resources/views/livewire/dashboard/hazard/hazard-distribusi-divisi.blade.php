<div>
    <div class="border bg-base-100 border-base-200" wire:ignore id="container-distribusi" style="height: 355px; width: 100%;"></div>

    <script type="module">
        const initHazardChart = () => {
            const dom_divis = document.getElementById('container-distribusi');
            if (!dom_divis) return;

            let myChart_divis = echarts.getInstanceByDom(dom_divis);
            if (myChart_divis) {
                myChart_divis.dispose();
            }

            myChart_divis = echarts.init(dom_divis);

            // Lokalisasi Teks dari Laravel
            const i18n = {
                title: @json(__('Distribusi Laporan per Divisi')),
                seriesName: @json(__('Jumlah'))
            };

            const rawData = @json($categories);
            const categories = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;

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

            const option_divis = {
                backgroundColor: 'transparent',
                title: {
                    text: i18n.title, // MENGGUNAKAN i18n
                    textStyle: {
                        color: theme.content,
                        fontFamily: 'Poppins, sans-serif',
                        fontSize: 14
                    },
                    subtext: categories.range,
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
                    textStyle: {
                        color: theme.content
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
                    data: categories.label,
                    inverse: true,
                    axisLabel: {
                        color: theme.content,
                        fontSize: 10,
                        width: 120,
                        overflow: 'truncate'
                    }
                },
                series: [{
                    name: i18n.seriesName, // MENGGUNAKAN i18n
                    type: 'bar',
                    data: categories.counts,
                    itemStyle: {
                        color: (params) => generateColor(params.dataIndex),
                        borderRadius: [0, 4, 4, 0]
                    },
                    label: {
                        show: true,
                        position: 'right',
                        color: theme.content
                    }
                }]
            };

            myChart_divis.setOption(option_divis);

            Livewire.on('distribusiDivisi', (event) => {
                const data = typeof event[0] === 'string' ? JSON.parse(event[0]) : event[0];
                myChart_divis.setOption({
                    title: {
                        subtext: data.range
                    },
                    yAxis: {
                        data: data.label
                    },
                    series: [{
                        data: data.counts
                    }]
                });
            });

            window.addEventListener('resize', () => myChart_divis.resize());
        };

        initHazardChart();
        document.addEventListener('livewire:navigated', initHazardChart);
    </script>
</div>