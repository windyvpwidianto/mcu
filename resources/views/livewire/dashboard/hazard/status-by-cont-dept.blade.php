<div>
    <div wire:ignore id="hazardStatusByContDept" style="height: 350px;" class="w-full"></div>

    <script type="module">
        // --- 1. LOKALISASI TEKS (Bilingual) ---
        const i18n = {
            title: @json(__('Status Laporan Responsible Departemen/Kontraktor')),
            yAxisName: @json(__('Jumlah Laporan')),
            statusOpen: @json(__('Open')),
            statusClosed: @json(__('Closed')),
            periodPrefix: @json(__('Periode: ')),
            defaultRange: @json(__('12 Bulan Terakhir'))
        };

        // --- UTILS TEMA DAISYUI ---
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
        let rawData = @json(json_decode($statusDeptCont, true));

        var dom = document.getElementById('hazardStatusByContDept');
        var myChart = echarts.init(dom);

        var option = {
            backgroundColor: 'transparent',
            title: {
                text: i18n.title, // MENGGUNAKAN i18n
                left: 'center',
                textStyle: {
                    color: theme.content,
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: 14
                },
                subtext: rawData.range,
                subtextStyle: {
                    color: theme.content,
                    opacity: 0.7,
                    fontSize: 12
                }
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
                },
                formatter: function(params) {
                    let res = '<b>' + params[0].name + '</b>';
                    let total = 0;
                    params.forEach(item => {
                        res += '<br/>' + item.marker + ' ' + item.seriesName + ': ' + item.value;
                        total += item.value;
                    });
                    res += '<br/><b>Total: ' + total + '</b>';
                    return res;
                }
            },
            legend: {
                data: [i18n.statusOpen, i18n.statusClosed], // MENGGUNAKAN i18n
                bottom: 5,
                textStyle: {
                    color: theme.content
                }
            },
            grid: {
                top: 80,
                left: '3%',
                right: '4%',
                bottom: '15%',
                containLabel: true
            },
            dataZoom: [{
                    type: 'inside',
                    start: 0,
                    end: 100
                },
                {
                    show: true,
                    type: 'slider',
                    top: 'bottom',
                    start: 0,
                    end: 100,
                    height: 20,
                    textStyle: {
                        color: theme.content
                    }
                }
            ],
            xAxis: {
                type: 'category',
                data: rawData.labels,
                axisLabel: {
                    interval: 0,
                    rotate: 35,
                    fontSize: 10,
                    color: theme.content,
                    formatter: function(value) {
                        return value.length > 12 ? value.substring(0, 12) + '...' : value;
                    }
                },
                axisLine: {
                    lineStyle: {
                        color: theme.base300
                    }
                }
            },
            yAxis: {
                type: 'value',
                name: i18n.yAxisName, // MENGGUNAKAN i18n
                nameTextStyle: {
                    color: theme.content
                },
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
            series: [{
                    name: i18n.statusOpen, // MENGGUNAKAN i18n
                    type: 'bar',
                    stack: 'total',
                    barMaxWidth: 40,
                    itemStyle: {
                        color: '#F87171'
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: rawData.open
                },
                {
                    name: i18n.statusClosed, // MENGGUNAKAN i18n
                    type: 'bar',
                    stack: 'total',
                    barMaxWidth: 40,
                    itemStyle: {
                        color: '#34D399',
                        borderRadius: [4, 4, 0, 0]
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: rawData.closed
                }
            ]
        };

        myChart.setOption(option);

        // --- OBSERVER PERUBAHAN TEMA ---
        const observer = new MutationObserver(() => {
            const newTheme = fetchColors();
            myChart.setOption({
                title: {
                    textStyle: {
                        color: newTheme.content
                    },
                    subtextStyle: {
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
                xAxis: {
                    axisLabel: {
                        color: newTheme.content
                    },
                    axisLine: {
                        lineStyle: {
                            color: newTheme.base300
                        }
                    }
                },
                yAxis: {
                    nameTextStyle: {
                        color: newTheme.content
                    },
                    axisLabel: {
                        color: newTheme.content
                    },
                    splitLine: {
                        lineStyle: {
                            color: newTheme.base300
                        }
                    }
                },
                dataZoom: [{}, {
                    textStyle: {
                        color: newTheme.content
                    }
                }]
            });
        });

        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });

        // --- LIVEWIRE EVENT ---
        Livewire.on('hazardStatus_DeptOrCont', event => {
            let payload = JSON.parse(event);
            myChart.setOption({
                title: {
                    subtext: payload.range ? i18n.periodPrefix + payload.range : i18n.defaultRange
                },
                xAxis: {
                    data: payload.labels
                },
                series: [{
                        data: payload.open
                    },
                    {
                        data: payload.closed
                    }
                ]
            });
        });

        window.addEventListener('resize', () => myChart.resize());
    </script>
</div>