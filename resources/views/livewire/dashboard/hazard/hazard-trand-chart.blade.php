<div>
    <div wire:ignore id="hazardTrend" style="height: 355px" class="w-full border bg-base-100 border-base-200"></div>

    <script type="module">
        const initHazardTrendChart = () => {
            const dom = document.getElementById('hazardTrend');

            if (!dom) return;

            let myChart = echarts.getInstanceByDom(dom);
            if (myChart) {
                myChart.dispose();
            }

            myChart = echarts.init(dom);

            // --- 1. LOKALISASI TEKS (Bilingual) ---
            const i18n = {
                title: @json(__('Jumlah Laporan Hazard per Bulan')),
                subtext: @json(__('Data laporan berdasarkan bulan berjalan')),
                legend: @json(__('Jumlah Laporan'))
            };

            const dataRaw = @json($data);
            const data = typeof dataRaw === 'string' ? JSON.parse(dataRaw) : dataRaw;

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

            let colors = fetchColors();

            const option = {
                backgroundColor: 'transparent',
                title: {
                    text: i18n.title, // MENGGUNAKAN i18n
                    left: 'center',
                    top: 5,
                    textStyle: {
                        fontFamily: 'Poppins, sans-serif',
                        fontSize: 14,
                        fontWeight: 'bold',
                        color: colors.content
                    },
                    subtext: i18n.subtext, // MENGGUNAKAN i18n
                    subtextStyle: {
                        fontSize: 10,
                        color: colors.content
                    }
                },
                grid: {
                    top: 90,
                    right: 30,
                    bottom: 50,
                    left: 50,
                    containLabel: true
                },
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: colors.base100,
                    borderColor: colors.primary,
                    borderWidth: 1,
                    textStyle: {
                        color: colors.content
                    },
                    axisPointer: {
                        type: 'line',
                        lineStyle: {
                            color: colors.base300,
                            width: 1,
                            type: 'dashed'
                        }
                    }
                },
                legend: {
                    data: [i18n.legend], // MENGGUNAKAN i18n
                    top: 50,
                    textStyle: {
                        color: colors.content
                    }
                },
                xAxis: {
                    type: 'category',
                    data: data.months,
                    axisLine: {
                        lineStyle: {
                            color: colors.base300
                        }
                    },
                    axisLabel: {
                        color: colors.content
                    }
                },
                yAxis: {
                    type: 'value',
                    axisLine: {
                        show: false
                    },
                    splitLine: {
                        lineStyle: {
                            type: 'dashed',
                            color: colors.base300
                        }
                    },
                    axisLabel: {
                        color: colors.content
                    }
                },
                series: [{
                    name: i18n.legend, // MENGGUNAKAN i18n agar sinkron dengan Legend
                    data: data.counts,
                    type: 'line',
                    smooth: 0.3,
                    lineStyle: {
                        width: 4,
                        color: colors.primary
                    },
                    symbol: 'circle',
                    symbolSize: 8,
                    itemStyle: {
                        color: colors.primary,
                        borderWidth: 2,
                        borderColor: colors.base100
                    },
                    emphasis: {
                        focus: 'series',
                        lineStyle: {
                            width: 5,
                            color: colors.primary
                        }
                    }
                }]
            };

            myChart.setOption(option);

            // 4. Observer untuk ganti tema (DaisyUI)
            const observer = new MutationObserver(() => {
                const newColors = fetchColors();
                myChart.setOption({
                    title: {
                        textStyle: {
                            color: newColors.content
                        },
                        subtextStyle: {
                            color: newColors.content
                        }
                    },
                    legend: {
                        textStyle: {
                            color: newColors.content
                        }
                    },
                    tooltip: {
                        backgroundColor: newColors.base100,
                        borderColor: newColors.primary,
                        textStyle: {
                            color: newColors.content
                        },
                        axisPointer: {
                            lineStyle: {
                                color: newColors.base300
                            }
                        }
                    },
                    xAxis: {
                        axisLabel: {
                            color: newColors.content
                        },
                        axisLine: {
                            lineStyle: {
                                color: newColors.base300
                            }
                        }
                    },
                    yAxis: {
                        axisLabel: {
                            color: newColors.content
                        },
                        splitLine: {
                            lineStyle: {
                                color: newColors.base300
                            }
                        }
                    },
                    series: [{
                        lineStyle: {
                            color: newColors.primary
                        },
                        itemStyle: {
                            color: newColors.primary,
                            borderColor: newColors.base100
                        },
                        emphasis: {
                            lineStyle: {
                                color: newColors.primary
                            }
                        }
                    }]
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme']
            });

            // 5. Handle Livewire Dispatch
            Livewire.on('trandChart', (event) => {
                const payload = typeof event[0] === 'string' ? JSON.parse(event[0]) : event[0];
                myChart.setOption({
                    xAxis: {
                        data: payload.months
                    },
                    series: [{
                        data: payload.counts
                    }]
                });
            });

            window.addEventListener('resize', () => myChart.resize());
        };

        initHazardTrendChart();

        document.addEventListener('livewire:navigated', () => {
            initHazardTrendChart();
        });
    </script>
</div>