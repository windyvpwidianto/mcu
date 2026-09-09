<div>
    <div wire:ignore id="chart-container" style="height: 355px" class="w-full border bg-base-100 border-base-200"></div>

    <script type="module">
        var dom_status = document.getElementById('chart-container');

        // --- 1. LOKALISASI TEKS (Bilingual) ---
        const i18n = {
            title: @json(__('Distribusi Status')),
            seriesName: @json(__('Status')),
            unit: @json(__('laporan'))
        };

        const chartData = JSON.parse(@json($statusChart));
        const labels = chartData.labels;
        const values = chartData.values;

        const seriesData = labels.map((label, i) => ({
            name: label,
            value: values[i]
        }));

        var myChart_status = echarts.init(dom_status, null, {
            renderer: 'canvas',
            useDirtyRect: false
        });

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
        });

        let theme = fetchColors();

        var option_status = {
            backgroundColor: 'transparent',
            title: {
                text: i18n.title, // MENGGUNAKAN i18n
                left: 'center',
                textStyle: {
                    color: theme.content,
                    fontFamily: 'Poppins, Arial, sans-serif',
                    fontSize: 16
                }
            },
            tooltip: {
                trigger: 'item',
                backgroundColor: theme.base100,
                borderColor: theme.primary,
                borderWidth: 1,
                textStyle: {
                    color: theme.content
                },
                // Formatter menggunakan unit dinamis (laporan/reports)
                formatter: `{b}: {c} ${i18n.unit} ({d}%)`
            },
            legend: {
                top: 'bottom',
                left: 'center',
                textStyle: {
                    fontSize: 10,
                    color: theme.content,
                    fontFamily: 'Poppins, Arial, sans-serif'
                }
            },
            series: [{
                name: i18n.seriesName, // MENGGUNAKAN i18n
                type: 'pie',
                radius: ['30%', '60%'],
                avoidLabelOverlap: true,
                data: seriesData,
                label: {
                    show: true,
                    position: 'outside',
                    formatter: '{b}: {c}',
                    color: theme.content
                },
                emphasis: {
                    focus: 'none',
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }]
        };

        if (option_status && typeof option_status === 'object') {
            myChart_status.setOption(option_status);

            const observer = new MutationObserver(() => {
                const newTheme = fetchColors();
                myChart_status.setOption({
                    title: {
                        textStyle: {
                            color: newTheme.content
                        }
                    },
                    tooltip: {
                        backgroundColor: newTheme.base100,
                        borderColor: newTheme.primary,
                        textStyle: {
                            color: newTheme.content
                        }
                    },
                    legend: {
                        textStyle: {
                            color: newTheme.content
                        }
                    },
                    series: [{
                        label: {
                            color: newTheme.content
                        }
                    }]
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });

            Livewire.on('distribusiStatus', event => {
                let payload_status = JSON.parse(event);
                const newSeriesData = payload_status.labels.map((label, i) => ({
                    name: label,
                    value: payload_status.values[i]
                }));

                myChart_status.setOption({
                    series: [{
                        data: newSeriesData
                    }]
                });
            });
        }

        window.addEventListener('resize', () => myChart_status.resize());
    </script>
</div>