<template>
    <div class="rate-history-chart w-full bg-white dark:bg-zinc-900 rounded-3xl p-1 sm:p-2">
        <ChartHeader :chart-info="chartInfo" :periods="periods" :active-period="activePeriod" :loading="loading"
            @period-change="setPeriod" />

        <div class="relative w-full mt-10 px-2 sm:px-0">
            <div class="relative w-full h-[350px] sm:h-[370px] lg:h-[370px]">
                <ChartContainer :loading="loading" :error="error" :has-data="chartPoints.length > 0"
                    :is-scrollable="isScrollable" :canvas-width="canvasWidth" :chart-points="chartPoints"
                    @retry="retry">
                    <canvas ref="chartCanvas" class="w-full h-full"></canvas>
                </ChartContainer>
            </div>
        </div>

        <div class="">
            <ChartStats v-if="!loading && chartPoints.length && chartStats" :stats="chartStats"
                :points-count="pointsCount" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick, getCurrentInstance } from 'vue'
import Chart from 'chart.js/auto'
import ChartHeader from './ChartHeader.vue'
import ChartContainer from './ChartContainer.vue'
import ChartStats from './ChartStats.vue'
import { useChartData } from '../../../Composables/useChart'
import { useChartTheme } from '../../../Composables/useChartTheme'
import { useChartResize } from '../../../Composables/useChartResize'

// ── Props ──────────────────────────────────────────────
const props = defineProps({
    data: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(item =>
                'buy_rate' in item &&
                'sell_rate' in item &&
                'created_at' in item
            )
        }
    },
    options: {
        type: Object,
        default: () => ({
            showStats: true,
            enableZoom: false,
            defaultPeriod: 'month'
        })
    },
    loading: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: null
    }
})

// ── Emits ──────────────────────────────────────────────
const emit = defineEmits(['period-change', 'retry'])

const { proxy } = getCurrentInstance();

// ── Constants ──────────────────────────────────────────
const periods = [
    { value: 'week', label: '7D', days: 7 },
    { value: 'month', label: '1M', days: 30 },
    { value: 'quarter', label: '3M', days: 90 },
    { value: 'year', label: '1Y', days: 365 },
    { value: 'all', label: 'All', days: null }
]

// ── State ──────────────────────────────────────────────
const chartCanvas = ref(null)
let chartInstance = null
const activePeriod = ref(props.options.defaultPeriod || 'month')

// ── Composables ────────────────────────────────────────
const { chartPoints, chartStats, pointsCount, chartInfo, filteredData } = useChartData(props, activePeriod, periods)
const { getChartOptions, handleThemeChange } = useChartTheme(chartPoints, activePeriod)
const { isScrollable, canvasWidth } = useChartResize(chartPoints, activePeriod)

// Helper function to get week number
const getWeekNumber = (date) => {
    const d = new Date(date)
    const dayNum = d.getUTCDay() || 7
    d.setUTCDate(d.getUTCDate() + 4 - dayNum)
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
}

// Group data by period for better performance
const groupDataByPeriod = () => {
    const points = chartPoints.value
    if (points.length === 0) return []

    // For 'all' period with more than 200 points, group by month
    if (activePeriod.value === 'all' && points.length > 200) {
        const grouped = {}
        points.forEach(point => {
            const date = new Date(point.created_at)
            const key = `${date.getFullYear()}-${date.getMonth()}`
            if (!grouped[key]) {
                grouped[key] = {
                    buy_rates: [],
                    sell_rates: [],
                    date: new Date(date.getFullYear(), date.getMonth(), 1)
                }
            }
            grouped[key].buy_rates.push(Number(point.buy_rate))
            grouped[key].sell_rates.push(Number(point.sell_rate))
        })
        return Object.values(grouped).map(group => ({
            buy_rate: group.buy_rates.reduce((a, b) => a + b, 0) / group.buy_rates.length,
            sell_rate: group.sell_rates.reduce((a, b) => a + b, 0) / group.sell_rates.length,
            created_at: group.date
        }))
    }

    // For 'year' period with more than 100 points, group by week
    if (activePeriod.value === 'year' && points.length > 100) {
        const grouped = {}
        points.forEach(point => {
            const date = new Date(point.created_at)
            const weekNumber = getWeekNumber(date)
            const key = `${date.getFullYear()}-${weekNumber}`
            if (!grouped[key]) {
                grouped[key] = {
                    buy_rates: [],
                    sell_rates: [],
                    date: date
                }
            }
            grouped[key].buy_rates.push(Number(point.buy_rate))
            grouped[key].sell_rates.push(Number(point.sell_rate))
        })
        return Object.values(grouped).map(group => ({
            buy_rate: group.buy_rates.reduce((a, b) => a + b, 0) / group.buy_rates.length,
            sell_rate: group.sell_rates.reduce((a, b) => a + b, 0) / group.sell_rates.length,
            created_at: group.date
        }))
    }

    return points
}

// Use grouped data for display
const displayPoints = computed(() => {
    return groupDataByPeriod()
})

// ── Chart Building - BAR CHART VERSION ─────────────────
const buildChart = () => {
    if (!chartCanvas.value || !displayPoints.value.length) return

    const ctx = chartCanvas.value.getContext('2d')
    const points = displayPoints.value

    if (chartInstance) {
        chartInstance.destroy()
    }

    // Create gradient colors for bars
    const buyGradient = ctx.createLinearGradient(0, 0, 0, 400)
    buyGradient.addColorStop(0, '#22C55E')
    buyGradient.addColorStop(1, '#16A34A')

    const sellGradient = ctx.createLinearGradient(0, 0, 0, 400)
    sellGradient.addColorStop(0, '#EF4444')
    sellGradient.addColorStop(1, '#DC2626')

    chartInstance = new Chart(ctx, {
        type: 'bar',  // Changed from 'line' to 'bar'
        data: {
            labels: getChartLabels(),
            datasets: [
                {
                    label: 'Buy Rate',
                    data: points.map(p => p.buy_rate),
                    backgroundColor: buyGradient,
                    borderColor: '#16A34A',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8,
                },
                {
                    label: 'Sell Rate',
                    data: points.map(p => p.sell_rate),
                    backgroundColor: sellGradient,
                    borderColor: '#DC2626',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8,
                }
            ]
        },
        options: getResponsiveBarOptions()
    })
}

const getResponsiveBarOptions = () => {
    const isMobile = window.innerWidth < 640;

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'end',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: isMobile ? 10 : 20,
                    font: { size: 11, weight: '600', family: 'sans-serif' }
                }
            },
            tooltip: {
                enabled: true,
                padding: 12,
                backgroundColor: 'rgba(24, 24, 27, 0.95)',
                titleFont: { size: 13, weight: '700' },
                bodyFont: { size: 12, family: 'monospace' },
                cornerRadius: 12,
                callbacks: {
                    label: (context) => {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y;
                        return `${label}: ${proxy.$formatMoney(value, 2)} MMK`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45,
                    autoSkip: true,
                    maxTicksLimit: isMobile ? 4 : 8,
                    font: { size: isMobile ? 8 : 10, weight: '500' },
                    color: '#94a3b8'
                }
            },
            y: {
                position: 'right',
                grid: {
                    color: 'rgba(226, 232, 240, 0.1)',
                    drawBorder: false
                },
                ticks: {
                    font: { size: 10, family: 'monospace', weight: '600' },
                    color: '#94a3b8',
                    callback: (value) => proxy.$formatMoney(value, 0, true)
                }
            }
        }
    }
}

const getChartLabels = () => {
    return chartPoints.value.map(point => {
        const date = new Date(point.created_at)
        const formatters = {
            week: () => date.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric' }),
            month: () => date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }),
            quarter: () => date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }),
            year: () => date.toLocaleDateString('en-GB', { month: 'short', year: '2-digit' }),
            all: () => date.toLocaleDateString('en-GB', { month: 'short', year: '2-digit' })
        }
        const formatter = formatters[activePeriod.value] || formatters.month
        return formatter()
    })
}

const setPeriod = (period) => {
    if (activePeriod.value === period) return
    activePeriod.value = period
    emit('period-change', period)
}

const retry = () => {
    emit('retry')
}

// ── Watchers ─────────────────────────────────────────
watch([chartPoints, () => props.loading], async () => {
    await nextTick()
    if (!props.loading) {
        buildChart()
    }
})

// ── Lifecycle ────────────────────────────────────────
onMounted(() => {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                handleThemeChange(chartInstance, getChartOptions)
            }
        })
    })
    observer.observe(document.documentElement, { attributes: true })

    const handleResize = () => {
        if (chartInstance) {
            chartInstance.resize()
        }
    }
    window.addEventListener('resize', handleResize);

    onUnmounted(() => {
        observer.disconnect()
        window.removeEventListener('resize', handleResize)
        if (chartInstance) {
            chartInstance.destroy()
            chartInstance = null
        }
    })
})

// ── Expose ────────────────────────────────────────────
defineExpose({
    refresh: buildChart,
    resetPeriod: () => setPeriod(props.options.defaultPeriod || 'month'),
    getStats: () => chartStats.value
})
</script>