<template>
    <div
        class="bg-white dark:bg-zinc-900 border border-[#EBEBEA] dark:border-zinc-800 rounded-xl overflow-hidden transition-colors duration-300">
        <!-- Chart Header -->
        <div class="flex justify-between items-center gap-3 flex-wrap p-5 pb-0">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                    <span class="text-xs text-[#999] dark:text-zinc-500 ml-1">Buy</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                    <span class="text-xs text-[#999] dark:text-zinc-500 ml-1">Sell</span>
                </div>
                <div v-if="chartInfo" class="hidden sm:block">
                    <span
                        class="text-[10px] text-[#C0C0BC] dark:text-zinc-600 border-l border-[#EBEBEA] dark:border-zinc-800 pl-2 ml-1">
                        {{ chartInfo }}
                    </span>
                </div>
            </div>

            <!-- Period Selector -->
            <div class="flex gap-0.5 bg-[#FAFAF9] dark:bg-zinc-800 p-1 rounded-lg">
                <button v-for="period in periods" :key="period.value" @click="setPeriod(period.value)"
                    :disabled="loading" class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
                    :class="[
                        activePeriod === period.value
                            ? 'bg-white dark:bg-zinc-900 text-[#111] dark:text-zinc-100 shadow-sm border border-[#EBEBEA] dark:border-zinc-700'
                            : 'text-[#999] dark:text-zinc-500 hover:text-[#333] dark:hover:text-zinc-300'
                    ]">
                    {{ period.label }}
                </button>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="relative p-5 pt-4">
            <!-- Loading Overlay -->
            <div v-if="loading"
                class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-900/80 z-10 rounded-lg">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-8 h-8 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin">
                    </div>
                    <span class="text-xs text-[#999] dark:text-zinc-500">Loading chart data...</span>
                </div>
            </div>

            <!-- Error State -->
            <div v-if="error" class="h-[220px] flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-rose-400 dark:text-rose-500 mb-2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm text-rose-600 dark:text-rose-400">{{ error }}</p>
                    <button @click="retry"
                        class="mt-3 px-4 py-1.5 text-xs font-medium bg-[#F7F7F5] dark:bg-zinc-800 text-[#666] dark:text-zinc-400 rounded-md hover:bg-[#EBEBEA] dark:hover:bg-zinc-700 transition-colors">
                        Try Again
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!chartPoints.length" class="h-[220px] flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto text-[#C0C0BC] dark:text-zinc-600 mb-2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="text-sm text-[#999] dark:text-zinc-500">No data available for this period</p>
                </div>
            </div>

            <!-- Chart Canvas -->
            <div v-else class="h-[220px] w-full" :class="{ 'overflow-x-auto': isScrollable }">
                <div :style="{ width: canvasWidth }" class="h-full">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart Stats -->
        <div v-if="!loading && chartPoints.length && chartStats"
            class="grid grid-cols-4 divide-x divide-[#EBEBEA] dark:divide-zinc-800 border-t border-[#EBEBEA] dark:border-zinc-800 bg-[#FAFAF9] dark:bg-zinc-800/30">
            <div class="px-4 py-3 text-center">
                <p class="text-[9px] font-sans uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">High</p>
                <p class="text-xs font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                    {{ chartStats.high }}
                </p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">Low</p>
                <p class="text-xs font-bold tabular-nums text-rose-600 dark:text-rose-400">
                    {{ chartStats.low }}
                </p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">Avg</p>
                <p class="text-xs font-bold tabular-nums text-[#666] dark:text-zinc-400">
                    {{ chartStats.avg }}
                </p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">Points</p>
                <p class="text-xs font-bold tabular-nums text-[#666] dark:text-zinc-400">
                    {{ chartPoints.length }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import Chart from 'chart.js/auto'

// ── Props ──────────────────────────────────────────────
const props = defineProps({
    // Chart data
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
    // Chart configuration
    options: {
        type: Object,
        default: () => ({
            showStats: true,
            enableZoom: false,
            defaultPeriod: 'month'
        })
    },
    // Loading state
    loading: {
        type: Boolean,
        default: false
    },
    // Error state
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

// ── State ──────────────────────────────────────────────
const chartCanvas = ref(null)
let chartInstance = null
const activePeriod = ref(props.options.defaultPeriod || 'month')

// ── Computed ───────────────────────────────────────────
const filteredData = computed(() => {
    if (!props.data.length) return []

    const period = periods.find(p => p.value === activePeriod.value)
    if (!period || !period.days) return [...props.data]

    const cutoffDate = new Date()
    cutoffDate.setDate(cutoffDate.getDate() - period.days)

    return props.data.filter(item => new Date(item.created_at) >= cutoffDate)
})

const chartPoints = computed(() => {
    if (!filteredData.value.length) return []

    // Sort by date ascending
    return [...filteredData.value].sort((a, b) =>
        new Date(a.created_at) - new Date(b.created_at)
    )
})

const chartStats = computed(() => {
    if (!chartPoints.value.length) return null

    const buyRates = chartPoints.value.map(p => Number(p.buy_rate)).filter(v => !isNaN(v))
    const sellRates = chartPoints.value.map(p => Number(p.sell_rate)).filter(v => !isNaN(v))

    if (!buyRates.length || !sellRates.length) return null

    const avgBuy = buyRates.reduce((a, b) => a + b, 0) / buyRates.length
    const avgSell = sellRates.reduce((a, b) => a + b, 0) / sellRates.length

    return {
        high: formatPrice(Math.max(...sellRates)),
        low: formatPrice(Math.min(...sellRates)),
        avg: formatPrice(avgSell),
        avgBuy: formatPrice(avgBuy),
        avgSell: formatPrice(avgSell)
    }
})

const chartInfo = computed(() => {
    if (!chartPoints.value.length) return null

    const period = periods.find(p => p.value === activePeriod.value)
    if (period?.value === 'all') {
        const firstDate = new Date(chartPoints.value[0].created_at)
        return `since ${firstDate.toLocaleDateString('en-GB', { month: 'short', year: 'numeric' })}`
    }

    return `${chartPoints.value.length} data points`
})

const isScrollable = computed(() => {
    return (activePeriod.value === 'all' || activePeriod.value === 'year') && chartPoints.value.length > 60
})

const canvasWidth = computed(() => {
    if (!isScrollable.value) return '100%'
    const points = chartPoints.value.length
    const pointWidth = activePeriod.value === 'all' ? 12 : 15
    return Math.max(points * pointWidth, 800) + 'px'
})

// ── Chart Configuration ───────────────────────────────
const getChartOptions = () => {
    const isDark = document.documentElement.classList.contains('dark')

    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 300 },
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: isDark ? '#1f1f1f' : '#fff',
                titleColor: isDark ? '#e5e5e5' : '#111',
                bodyColor: isDark ? '#a3a3a3' : '#555',
                borderColor: isDark ? '#2e2e2e' : '#E8E8E4',
                borderWidth: 1,
                padding: 10,
                callbacks: {
                    title: (items) => {
                        const point = chartPoints.value[items[0].dataIndex]
                        if (!point) return ''
                        return new Date(point.created_at).toLocaleDateString('en-GB', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    },
                    label: (ctx) => {
                        const label = ctx.dataset.label
                        const value = formatPrice(ctx.raw)
                        return `${label}: ${value}`
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: {
                    color: isDark ? '#27272a' : '#F0F0EE',
                    drawBorder: false,
                },
                ticks: {
                    color: isDark ? '#71717a' : '#C0C0BC',
                    font: { size: 11 },
                    callback: (v) => v.toLocaleString()
                }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: isDark ? '#71717a' : '#C0C0BC',
                    font: { size: 11 },
                    maxTicksLimit: getMaxTicksLimit(),
                    maxRotation: 0,
                    autoSkip: true
                }
            }
        }
    }
}

const getMaxTicksLimit = () => {
    switch (activePeriod.value) {
        case 'week': return 7
        case 'month': return 10
        case 'quarter': return 12
        case 'year': return 12
        case 'all': return 10
        default: return 8
    }
}

const getChartLabels = () => {
    const points = chartPoints.value
    if (!points.length) return []

    return points.map((point, index) => {
        const date = new Date(point.created_at)
        const total = points.length

        // For 'all' and 'year' periods, show fewer labels
        if (activePeriod.value === 'all' || activePeriod.value === 'year') {
            // Show label for first, last, and every nth point
            const shouldShowLabel = index === 0 ||
                index === total - 1 ||
                index % Math.ceil(total / 10) === 0

            if (!shouldShowLabel) return ''

            // For 'all', show month and year
            if (activePeriod.value === 'all') {
                return date.toLocaleDateString('en-GB', {
                    month: 'short',
                    year: 'numeric'
                })
            }

            // For 'year', show month and day
            return date.toLocaleDateString('en-GB', {
                month: 'short',
                day: 'numeric'
            })
        }

        // For other periods
        switch (activePeriod.value) {
            case 'week':
                return date.toLocaleDateString('en-GB', {
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short'
                })
            case 'month':
            case 'quarter':
                return date.toLocaleDateString('en-GB', {
                    day: 'numeric',
                    month: 'short'
                })
            default:
                return date.toLocaleDateString('en-GB', {
                    day: 'numeric',
                    month: 'short'
                })
        }
    })
}

// ── Chart Building ───────────────────────────────────
const buildChart = () => {
    if (!chartCanvas.value || !chartPoints.value.length) return

    const ctx = chartCanvas.value.getContext('2d')
    const labels = getChartLabels()
    const buyRates = chartPoints.value.map(p => Number(p.buy_rate))
    const sellRates = chartPoints.value.map(p => Number(p.sell_rate))

    // Destroy existing chart
    if (chartInstance) {
        chartInstance.destroy()
        chartInstance = null
    }

    // Create gradient backgrounds
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

// ── Formatting Helpers ───────────────────────────────
const formatPrice = (value) => {
    if (value == null || isNaN(value)) return '—'
    return value.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
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

// Watch for theme changes
const handleThemeChange = () => {
    if (chartInstance) {
        chartInstance.options = getChartOptions()
        chartInstance.update()
    }
}

// ── Lifecycle ────────────────────────────────────────
onMounted(() => {
    // Listen for theme changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                handleThemeChange()
            }
        })
    })

    observer.observe(document.documentElement, { attributes: true })

    // Handle window resize
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