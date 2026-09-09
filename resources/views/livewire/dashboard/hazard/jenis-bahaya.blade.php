<div class="grid grid-cols-1 gap-2 my-2 lg:grid-cols-3">
    <div class="shadow rounded-xl lg:col-span-2 bg-base-100 p-2">
        <div wire:ignore id="hazardJenisChart" style="height: 350px;" class="w-full"></div>
    </div>

    <div class="shadow rounded-xl bg-base-100 p-2">
        <div wire:ignore id="ktaTtaPieChart" style="height: 350px;" class="w-full"></div>
    </div>

    <script type="module">
        // --- 1. UTILS & LOKALISASI (Bilingual) ---
        const i18n = {
            barTitle: @json(__('Tren OHS Hazard Report per Jenis Bahaya')),
            pieTitle: @json(__('Kategori Bahaya OHS (KTA vs TTA)')),
            categoryName: @json(__('Kategori'))
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
        const barColors = ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de', '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'];

        // --- 2. INISIALISASI CHART ---
        const barChart = echarts.init(document.getElementById('hazardJenisChart'));
        const pieChart = echarts.init(document.getElementById('ktaTtaPieChart'));

        // --- 3. KONFIGURASI BAR CHART ---
        const getBarOption = (data, currentTheme) => ({
            backgroundColor: 'transparent',
            color: barColors,
            title: {
                text: i18n.barTitle, // MENGGUNAKAN i18n
                left: 'center',
                textStyle: {
                    color: currentTheme.content,
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: 14
                },
                subtext: data.range,
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
                },
                formatter: function(params) {
                    let res = '<b>' + params[0].name + '</b>';
                    params.sort((a, b) => b.value - a.value);
                    params.forEach(item => {
                        if (item.value > 0) res += `<br/>${item.marker} ${item.seriesName}: <b>${item.value}</b>`;
                    });
                    return res;
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
                boundaryGap: true,
                axisLabel: {
                    color: currentTheme.content,
                    fontSize: 10
                },
                axisLine: {
                    lineStyle: {
                        color: currentTheme.base300
                    }
                },
                splitLine: {
                    show: true,
                    lineStyle: {
                        color: currentTheme.base300,
                        type: 'solid',
                        width: 1,
                        opacity: 0.5
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
                name: s.name,
                data: s.data,
                type: 'bar',
                barMaxWidth: 20,
                barGap: '15%',
                label: {
                    show: true,
                    position: 'top',
                    color: currentTheme.content,
                    fontSize: 10,
                    formatter: (p) => p.value > 0 ? p.value : ''
                },
                itemStyle: {
                    borderRadius: [3, 3, 0, 0]
                },
                emphasis: {
                    focus: 'series'
                }
            }))
        });

        // --- 4. KONFIGURASI PIE CHART ---
        const getPieOption = (data, currentTheme) => ({
            backgroundColor: 'transparent',
            color: ['#4F75FE', '#FAC858'],
            title: {
                text: i18n.pieTitle, // MENGGUNAKAN i18n
                left: 'center',
                textStyle: {
                    color: currentTheme.content,
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: 14
                }
            },
            tooltip: {
                trigger: 'item',
                backgroundColor: currentTheme.base100,
                borderColor: currentTheme.primary,
                borderWidth: 1,
                textStyle: {
                    color: currentTheme.content
                },
                formatter: '{b}: <b>{c}</b> ({d}%)'
            },
            legend: {
                bottom: 0,
                textStyle: {
                    color: currentTheme.content
                }
            },
            series: [{
                name: i18n.categoryName, // MENGGUNAKAN i18n
                type: 'pie',
                radius: ['35%', '60%'],
                center: ['50%', '50%'],
                avoidLabelOverlap: true,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: currentTheme.base100,
                    borderWidth: 2
                },
                label: {
                    show: true,
                    position: 'outer',
                    color: currentTheme.content,
                    fontSize: 11,
                    formatter: '{b}\n{c} ({d}%)'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: 13,
                        fontWeight: 'bold'
                    }
                },
                // Data mapping untuk memastikan label KTA/TTA bisa diterjemahkan jika perlu
                data: data.series
            }]
        });

        // --- 5. RENDER AWAL ---
        const rawBarData = @json(json_decode($chartJenisBahaya, true));
        const rawPieData = @json(json_decode($chartKtaTta, true));

        barChart.setOption(getBarOption(rawBarData, theme));
        pieChart.setOption(getPieOption(rawPieData, theme));

        // --- 6. LIVEWIRE UPDATE EVENTS ---
        Livewire.on('updateJenisBahayaChart', event => {
            barChart.setOption(getBarOption(JSON.parse(event), theme), true);
        });

        Livewire.on('updatePieChart', event => {
            pieChart.setOption(getPieOption(JSON.parse(event), theme), true);
        });

        // --- 7. OBSERVER TEMA ---
        const observer = new MutationObserver(() => {
            theme = fetchColors();
            barChart.setOption(getBarOption(rawBarData, theme));
            pieChart.setOption(getPieOption(rawPieData, theme));
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