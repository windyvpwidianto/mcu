<div class="grid grid-cols-1 gap-2 my-2 lg:grid-cols-3">
    <div class="shadow rounded-xl lg:col-span-2 bg-base-100 p-2">
        <div wire:ignore id="hazardEnvJenisChart" style="height: 350px;" class="w-full"></div>
    </div>

    <div class="shadow rounded-xl bg-base-100 p-2">
        <div wire:ignore id="ktaTtaEnvPieChart" style="height: 350px;" class="w-full"></div>
    </div>

    <script type="module">
        // --- 1. LOKALISASI TEKS (Bilingual) ---
        const i18n = {
            barTitle: @json(__('Tren Env Hazard Report per Jenis Bahaya')),
            pieTitle: @json(__('Kategori Bahaya Env (KTA vs TTA)')),
            period: @json(__('Periode')),
            uc: @json(__('Kondisi Tidak Aman')), // Unsafe Condition
            ua: @json(__('Tindakan Tidak Aman')) // Unsafe Action
        };

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

        const barColors = ['#022C22', '#6EE7B7', '#065F46', '#B9FBC0', '#10B981', '#818C78', '#D1FAE5'];

        const barChart = echarts.init(document.getElementById('hazardEnvJenisChart'));
        const pieChart = echarts.init(document.getElementById('ktaTtaEnvPieChart'));

        const getBarOption = (data, currentTheme) => ({
            backgroundColor: 'transparent',
            color: barColors,
            title: {
                text: i18n.barTitle,
                left: 'center',
                textStyle: {
                    color: currentTheme.content,
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: 14
                },
                subtext: data.range ? `${i18n.period}: ${data.range}` : '',
                subtextStyle: {
                    color: currentTheme.content,
                    opacity: 0.7
                }
            },
            tooltip: {
                trigger: 'axis',
                backgroundColor: currentTheme.base100,
                borderColor: currentTheme.primary,
                borderWidth: 1,
                textStyle: {
                    color: currentTheme.content
                },
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                bottom: 0,
                textStyle: {
                    color: currentTheme.content
                },
                type: 'scroll'
            },
            grid: {
                top: 70,
                left: '3%',
                right: '4%',
                bottom: '15%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: data.labels,
                axisLabel: {
                    color: currentTheme.content,
                    fontSize: 10
                },
                axisLine: {
                    lineStyle: {
                        color: currentTheme.base300
                    }
                }
            },
            yAxis: {
                type: 'value',
                splitLine: {
                    lineStyle: {
                        color: currentTheme.base300,
                        type: 'dashed',
                        opacity: 0.5
                    }
                },
                axisLabel: {
                    color: currentTheme.content
                }
            },
            series: data.series.map(s => ({
                ...s,
                type: 'bar',
                barMaxWidth: 20,
                label: {
                    show: true,
                    position: 'top',
                    color: currentTheme.content,
                    fontSize: 10,
                    formatter: (p) => p.value > 0 ? p.value : ''
                }
            }))
        });

        const getPieOption = (data, currentTheme) => {
            // Pemetaan nama kategori agar mengikuti bahasa aktif
            const mappedData = data.series.map(item => {
                let name = item.name;
                if (name === 'KTA' || name === 'Unsafe Condition') name = i18n.uc;
                if (name === 'TTA' || name === 'Unsafe Action') name = i18n.ua;
                return {
                    ...item,
                    name: name
                };
            });

            return {
                backgroundColor: 'transparent',
                color: ['#059669', '#FBBF24'],
                title: {
                    text: i18n.pieTitle,
                    left: 'center',
                    textStyle: {
                        color: currentTheme.content,
                        fontFamily: 'Poppins, sans-serif',
                        fontSize: 14
                    }
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: <b>{c}</b> ({d}%)'
                },
                legend: {
                    bottom: 0,
                    textStyle: {
                        color: currentTheme.content
                    }
                },
                series: [{
                    type: 'pie',
                    radius: ['35%', '60%'],
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: currentTheme.base100,
                        borderWidth: 2
                    },
                    label: {
                        color: currentTheme.content,
                        formatter: '{b}\n{c} ({d}%)'
                    },
                    data: mappedData
                }]
            };
        };

        // Render Awal
        barChart.setOption(getBarOption(@json(json_decode($chartJenisBahaya, true)), theme));
        pieChart.setOption(getPieOption(@json(json_decode($chartKtaTta, true)), theme));

        // Update Events
        Livewire.on('updateEnvJenisBahayaChart', event => {
            barChart.setOption(getBarOption(JSON.parse(event), theme), true);
        });

        Livewire.on('updateEnvPieChart', event => {
            pieChart.setOption(getPieOption(JSON.parse(event), theme), true);
        });

        // Theme Observer
        const observer = new MutationObserver(() => {
            theme = fetchColors();
            barChart.setOption(getBarOption(JSON.parse(@this.chartJenisBahaya), theme));
            pieChart.setOption(getPieOption(JSON.parse(@this.chartKtaTta), theme));
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });

        window.addEventListener('resize', () => {
            barChart.resize();
            pieChart.resize();
        });
    </script>
</div>