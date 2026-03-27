<template>
    <div
        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm transition-colors duration-300">
        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-3 px-5 pt-5 pb-0">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: chartColor }"></span>
                <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">
                    {{ priceLabel }}
                </span>
                <span v-if="dataContext"
                    class="text-[10px] text-slate-300 dark:text-zinc-600 border-l border-slate-200 dark:border-zinc-800 pl-2 ml-1 italic">
                    {{ dataContext }}
                </span>
            </div>

            <!-- Period Selector -->
            <PeriodSelector :periods="periods" :active="activePeriod" :disabled="loading" @select="selectPeriod" />
        </div>

        <!-- Chart Area -->
        <div class="relative min-h-[240px] px-5 pt-4">
            <LoadingSpinner v-if="loading" :color="chartColor" />

            <div v-if="error" class="h-60 flex items-center justify-center text-sm text-rose-500 italic">
                {{ error }}
            </div>

            <div v-else-if="chartPoints.length"
                class="overflow-x-auto scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-zinc-800">
                <div :style="{ width: canvasWidth }" class="h-60">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <EmptyState v-else />
        </div>

        <!-- Stats Footer -->
        <ChartStats v-if="!loading && chartPoints.length" :stats="stats" :formatter="formatPrice" />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import axios from 'axios'
import Chart from 'chart.js/auto'
import 'chartjs-adapter-date-fns'

// Import sub-components
import PeriodSelector from './PeriodSelector.vue'
import LoadingSpinner from './LoadingSpinner.vue'
import EmptyState from './EmptyState.vue'
import ChartStats from './ChartStats.vue'

// ── Props with validation ──────────────────────────────
const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['world_oz', 'new_system', 'traditional'].includes(value)
    },
    chartColor: {
        type: String,
        default: '#2563EB'
    },
    currency: {
        type: String,
        default: ''
    },
    symbol: {
        type: String,
        default: '$'
    },
    id: {
        type: [Number, String],
        default: null
    },
    enableZoom: {
        type: Boolean,
        default: false
    }
})

// ── Constants ──────────────────────────────────────────
const PERIODS = {
    '7d': { label: '7D', days: 7, granularity: 'hourly' },
    '1m': { label: '1M', days: 30, granularity: 'daily' },
    '3m': { label: '3M', days: 90, granularity: 'daily' },
    '1y': { label: '1Y', days: 365, granularity: 'weekly' },
    'all': { label: 'All', days: null, granularity: 'monthly' }
}

const periods = Object.entries(PERIODS).map(([value, config]) => ({
    value,
    label: config.label
}))

// ── State ────────────────────────────────────────────
const rawData = ref([])
const loading = ref(false)
const error = ref(null)
const chartCanvas = ref(null)
let chartInstance = null
let abortController = null

// Default period based on type
const getDefaultPeriod = () => props.type === 'world_oz' ? '7d' : '7d'
const activePeriod = ref(getDefaultPeriod())

// ── Helper: Extract price from various data structures ──
const extractPrice = (item) => {
    // Try different possible field names
    const possiblePriceFields = ['price', 'value', 'rate', 'mmk_price', 'usd_price', 'buy_rate', 'sell_rate']

    for (const field of possiblePriceFields) {
        if (item[field] !== undefined && item[field] !== null) {
            const value = parseFloat(item[field])
            if (!isNaN(value) && value > 0) {
                return value
            }
        }
    }

    // If we have both buy and sell rates, use average for gold charts
    if (item.buy_rate !== undefined && item.sell_rate !== undefined) {
        const avg = (parseFloat(item.buy_rate) + parseFloat(item.sell_rate)) / 2
        if (!isNaN(avg) && avg > 0) {
            return avg
        }
    }


    return null
}

// ── Helper: Extract date from various data structures ──
const extractDate = (item) => {
    // Try different possible date fields
    const possibleDateFields = ['recorded_at', 'created_at', 'date', 'timestamp', 'updated_at']

    for (const field of possibleDateFields) {
        if (item[field]) {
            const date = new Date(item[field])
            if (!isNaN(date.getTime())) {
                return date
            }
        }
    }

    console.warn('No valid date found in item:', item)
    return null
}

// ── Computed Properties ──────────────────────────────
const chartPoints = computed(() => {
    if (!rawData.value.length) return []

    const processed = []

    for (const item of rawData.value) {
        const price = extractPrice(item)
        const date = extractDate(item)

        if (price !== null && date !== null) {
            processed.push({
                price: price,
                date: date,
                original: item
            })
        }
    }

    // Sort by date
    return processed.sort((a, b) => a.date - b.date)
})

const stats = computed(() => {
    const prices = chartPoints.value.map(p => p.price)
    if (!prices.length) return { high: null, low: null, avg: null, count: 0 }

    return {
        high: Math.max(...prices),
        low: Math.min(...prices),
        avg: prices.reduce((a, b) => a + b, 0) / prices.length,
        count: prices.length
    }
})

const priceLabel = computed(() => {
    if (props.type === 'world_oz') return 'Price · USD / oz'
    return `Price · ${props.currency}`
})

const dataContext = computed(() => {
    const points = chartPoints.value
    if (!points.length) return null

    if (activePeriod.value === '7d' && props.type === 'world_oz') {
        return `${points.length} snapshots`
    }

    if (activePeriod.value === 'all') {
        const firstDate = points[0].date
        return `since ${firstDate.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' })}`
    }

    return null
})

const isScrollable = computed(() => {
    const points = chartPoints.value.length
    return (activePeriod.value === 'all' || activePeriod.value === '1y') && points > 40
})

const canvasWidth = computed(() => {
    if (!isScrollable.value) return '100%'
    const points = chartPoints.value.length
    const pointWidth = activePeriod.value === 'all' ? 12 : 15
    return Math.max(points * pointWidth, 800) + 'px'
})

// ── Data Fetching ───────────────────────────────
const fetchData = async () => {
    if (abortController) {
        abortController.abort()
    }

    abortController = new AbortController()
    loading.value = true
    error.value = null

    try {
        const idPath = props.type === 'world_oz' ? 'world' : props.id
        const response = await axios.get(`/gold/chart-data/${idPath}`, {
            params: {
                type: props.type,
                period: activePeriod.value
            },
            signal: abortController.signal
        })

        // Handle different response structures
        let data = response.data

        // Check if data is wrapped in a data property
        if (data && data.data && Array.isArray(data.data)) {
            data = data.data
        }

        // Ensure we have an array
        if (!Array.isArray(data)) {
            console.error('Expected array but got:', typeof data)
            rawData.value = []
            error.value = 'Invalid data format received'
            return
        }



        if (data.length > 0) {


            // Test extraction
            const testPrice = extractPrice(data[0])
            const testDate = extractDate(data[0])

        }

        rawData.value = data

        // Check if we have valid points after processing
        if (chartPoints.value.length === 0 && data.length > 0) {

            error.value = 'Unable to process chart data. Please check the data format.'
        } else {

        }

    } catch (e) {
        console.error('Fetch error:', e)
        if (e.name !== 'AbortError') {
            error.value = e.response?.data?.message || 'Could not load chart data. Please try again.'
        }
    } finally {
        loading.value = false
        await nextTick()
        buildChart()
    }
}

// ── Chart Configuration ───────────────────────────────
const getChartOptions = () => {
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 300 },
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: '#fff',
                titleColor: '#111',
                titleFont: { size: 11, weight: '600' },
                bodyColor: '#555',
                bodyFont: { size: 12 },
                borderColor: '#EBEBEA',
                borderWidth: 1,
                padding: 10,
                displayColors: false,
                callbacks: {
                    title: (items) => {
                        const point = chartPoints.value[items[0].dataIndex]
                        if (!point || !point.date) return ''
                        return point.date.toLocaleDateString('en-GB', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    },
                    label: (ctx) => `Price: ${formatPrice(ctx.raw.y)}`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: { color: '#F0F0EE' },
                border: { display: false },
                ticks: {
                    color: '#C0C0BC',
                    font: { size: 11 },
                    maxTicksLimit: 6,
                    callback: (v) => formatYAxisTick(v)
                }
            },
            x: {
                type: 'time',
                time: {
                    unit: getTimeUnit(),
                    displayFormats: getTimeFormats(),
                    tooltipFormat: 'PPpp'
                },
                grid: { display: false },
                border: { display: false },
                ticks: {
                    color: '#C0C0BC',
                    font: { size: 10 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: activePeriod.value === 'all' ? 24 : 8
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        },
        elements: {
            point: {
                radius: 0,
                hoverRadius: 5,
                hoverBorderWidth: 2
            }
        }
    }

    // Add zoom if enabled
    if (props.enableZoom) {
        options.plugins.zoom = {
            zoom: {
                wheel: { enabled: true },
                pinch: { enabled: true },
                mode: 'x',
                speed: 0.1
            },
            pan: {
                enabled: true,
                mode: 'x',
                speed: 0.1
            },
            limits: {
                x: { min: 'original', max: 'original' }
            }
        }
    }

    return options
}

const getTimeUnit = () => {
    switch (activePeriod.value) {
        case '7d': return 'hour'
        case '1m': return 'day'
        case '3m': return 'day'
        case '1y': return 'week'
        case 'all': return 'month'
        default: return 'day'
    }
}

const getTimeFormats = () => {
    switch (activePeriod.value) {
        case '7d': return { hour: 'HH:mm' }
        case '1m': return { day: 'MMM d' }
        case '3m': return { day: 'MMM d' }
        case '1y': return { week: 'MMM d' }
        case 'all': return { month: 'MMM yyyy' }
        default: return { day: 'MMM d' }
    }
}

const formatYAxisTick = (value) => {
    if (props.type === 'world_oz') {
        return `$${value.toFixed(0)}`
    }

    if (value >= 1_000_000) {
        return `${(value / 1_000_000).toFixed(1)}M`
    }
    if (value >= 1_000) {
        return `${(value / 1_000).toFixed(0)}K`
    }
    return value.toLocaleString()
}

// ── Chart Building ────────────────────────────
const buildChart = () => {
    if (!chartCanvas.value || !chartPoints.value.length) {

        return
    }

    const ctx = chartCanvas.value.getContext('2d')
    const color = props.chartColor
    const points = chartPoints.value



    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 260)
    gradient.addColorStop(0, `${color}28`)
    gradient.addColorStop(1, `${color}00`)

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
    }

    // Create new chart
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [{
                data: points.map(p => ({ x: p.date, y: p.price })),
                borderColor: color,
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: color,
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: getChartOptions()
    })
}

// ── Actions ───────────────────────────────────────────
const selectPeriod = (period) => {
    if (activePeriod.value === period) return
    activePeriod.value = period
    fetchData()
}

const refresh = () => {
    fetchData()
}

const formatPrice = (value) => {
    if (value == null || isNaN(value)) return '—'
    const formatted = Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
    return props.symbol ? `${props.symbol}${formatted}` : `${formatted} ${props.currency}`
}

// ── Watchers ──────────────────────────────────────────
watch(() => props.type, () => {
    activePeriod.value = getDefaultPeriod()
    fetchData()
})

watch(() => props.id, () => {
    if (props.type !== 'world_oz') {
        fetchData()
    }
})

watch(chartPoints, async () => {
    await nextTick()
    buildChart()
})

// ── Lifecycle ─────────────────────────────────────────
onMounted(() => {
    fetchData()

    const handleResize = () => {
        if (chartInstance) {
            chartInstance.resize()
        }
    }
    window.addEventListener('resize', handleResize)

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize)
        if (abortController) abortController.abort()
        if (chartInstance) chartInstance.destroy()
    })
})

// Expose methods for parent components
defineExpose({
    refresh,
    activePeriod,
    stats
})
</script>

<style scoped>
/* Only keep essential scrollbar styling that Tailwind doesn't cover */
.scrollbar-thin::-webkit-scrollbar {
    height: 3px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #EBEBEA;
    border-radius: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

/* Dark mode scrollbar */
.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #3f3f46;
}
</style>