<template>
    <div class="rate-history-chart">
        <!-- Chart Header -->
        <ChartHeader :chart-info="chartInfo" :periods="periods" :active-period="activePeriod" :loading="loading"
            @period-change="setPeriod" />

        <!-- Chart Container -->
        <ChartContainer :loading="loading" :error="error" :has-data="chartPoints.length > 0"
            :is-scrollable="isScrollable" :canvas-width="canvasWidth" :chart-points="chartPoints" @retry="retry">
            <canvas ref="chartCanvas" class="w-full h-full"></canvas>
        </ChartContainer>

        <!-- Chart Stats -->
        <ChartStats v-if="!loading && chartPoints.length && chartStats" :stats="chartStats"
            :points-count="chartPoints.length" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
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

// ── Constants ──────────────────────────────────────────
const periods = [
    { value: 'week', label: '7D', days: 7 },
    { value: 'month', label: '1M', days: 30 },
    { value: 'quarter', label: '3M', days: 90 },
    { value: 'year', label: '1Y', days: 365 },
    { value: 'all', label: 'All', days: null }
]

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

// Helper function to get week number
const getWeekNumber = (date) => {
    const d = new Date(date)
    const dayNum = d.getUTCDay() || 7
    d.setUTCDate(d.getUTCDate() + 4 - dayNum)
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
}

// Use grouped data for display
const displayPoints = computed(() => {
    return groupDataByPeriod()
})

// ── State ──────────────────────────────────────────────
const chartCanvas = ref(null)
let chartInstance = null
const activePeriod = ref(props.options.defaultPeriod || 'month')

// ── Composables ────────────────────────────────────────
const { chartPoints, chartStats, chartInfo, filteredData } = useChartData(props, activePeriod, periods)
const { getChartOptions, handleThemeChange } = useChartTheme(chartPoints, activePeriod)
const { isScrollable, canvasWidth } = useChartResize(chartPoints, activePeriod)

// ── Chart Building ───────────────────────────────────
const buildChart = () => {
    if (!chartCanvas.value || !displayPoints.value.length) return

    const ctx = chartCanvas.value.getContext('2d')

    // Get grouped data for better label display
    const points = displayPoints.value
    const labels = points.map((point, index) => {
        const date = new Date(point.created_at)
        const total = points.length

        // Smart label spacing for large datasets
        if (activePeriod.value === 'all' || activePeriod.value === 'year') {
            const shouldShowLabel = index === 0 ||
                index === total - 1 ||
                index % Math.ceil(total / 10) === 0
            if (!shouldShowLabel) return ''

            if (activePeriod.value === 'all') {
                return date.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' })
            }
            return date.toLocaleDateString('en-GB', { month: 'short', day: 'numeric' })
        }

        // For other periods
        switch (activePeriod.value) {
            case 'week':
                return date.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric' })
            case 'month':
            case 'quarter':
                return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
            default:
                return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
        }
    })

    const buyRates = points.map(p => Number(p.buy_rate))
    const sellRates = points.map(p => Number(p.sell_rate))

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
    }

    // Create gradients
    const buyGradient = ctx.createLinearGradient(0, 0, 0, 220)
    buyGradient.addColorStop(0, 'rgba(22, 163, 74, 0.1)')
    buyGradient.addColorStop(1, 'rgba(22, 163, 74, 0.01)')

    const sellGradient = ctx.createLinearGradient(0, 0, 0, 220)
    sellGradient.addColorStop(0, 'rgba(220, 38, 38, 0.08)')
    sellGradient.addColorStop(1, 'rgba(220, 38, 38, 0.01)')

    // Create new chart
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Buy Rate',
                    data: buyRates,
                    borderColor: '#16A34A',
                    backgroundColor: buyGradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#16A34A',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                },
                {
                    label: 'Sell Rate',
                    data: sellRates,
                    borderColor: '#DC2626',
                    backgroundColor: sellGradient,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#DC2626',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }
            ]
        },
        options: getChartOptions()
    })
}

const createDataset = (label, data, color, gradient) => ({
    label,
    data,
    borderColor: color,
    backgroundColor: gradient,
    borderWidth: 2,
    fill: true,
    tension: 0.35,
    pointRadius: 0,
    pointHoverRadius: 5,
    pointHoverBackgroundColor: color,
    pointHoverBorderColor: '#fff',
    pointHoverBorderWidth: 2,
})

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

// ── Actions ──────────────────────────────────────────
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
    // Setup theme observer
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                handleThemeChange(chartInstance, getChartOptions)
            }
        })
    })
    observer.observe(document.documentElement, { attributes: true })

    // Setup resize handler
    const handleResize = () => {
        if (chartInstance) {
            chartInstance.resize()
        }
    }
    window.addEventListener('resize', handleResize)

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