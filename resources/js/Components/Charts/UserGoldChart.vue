<template>
    <div
        class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm transition-all duration-300">
        <div class="flex flex-col xs:flex-row justify-between items-start xs:items-center gap-3 px-4 sm:px-6 pt-5 pb-2">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: chartColor }"></span>
                <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">
                    {{ priceLabel }}
                </span>
            </div>

            <PeriodSelector :periods="periods" :active="activePeriod" :disabled="loading" @select="selectPeriod"
                class="w-full xs:w-auto" />
        </div>

        <div class="relative px-2 sm:px-4 pt-4">
            <div :class="[
                'w-full transition-all duration-500',
                isMobile ? 'h-[240px]' : 'xs:h-[300px] sm:h-[400px]'
            ]">
                <LoadingSpinner v-if="loading" :color="chartColor" />

                <div v-if="error"
                    class="h-full flex items-center justify-center text-sm text-rose-500 italic px-10 text-center">
                    {{ error }}
                </div>

                <div v-else-if="chartPoints.length" class="h-full w-full">
                    <canvas ref="chartCanvas"></canvas>
                </div>

                <EmptyState v-else />
            </div>
        </div>

        <div class="px-4 sm:px-6 pb-5 mt-2">
            <ChartStats v-if="!loading && chartPoints.length" :stats="stats" :type="chartType"
                :currency-symbol="currencySymbol" :compact="isMobile" />
        </div>
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
    },
    isMobile: {
        type: Boolean,
        default: false
    },
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

const chartOptions = computed(() => ({
    maintainAspectRatio: false,
    scales: {
        x: {
            // Only show labels if we aren't on the smallest screen
            display: !props.isMobile,
            ticks: { maxTicksLimit: props.isMobile ? 3 : 8 }
        },
        y: {
            // Move labels inside or hide them to give the line more room
            ticks: { display: !props.isMobile }
        }
    },
    elements: {
        point: {
            radius: props.isMobile ? 0 : 3 // Hide points on mobile for a smoother line
        }
    }
}));

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
        const price = extractPrice(item) // This is USD for world_oz
        const date = extractDate(item)
        // Capture SGD Price
        const sgdPrice = item.sgd_price ? parseFloat(item.sgd_price) : null

        if (price !== null && date !== null) {
            processed.push({
                price: price,
                sgd_price: sgdPrice, // Add this
                date: date,
                original: item
            })
        }
    }
    return processed.sort((a, b) => a.date - b.date)
})

const chartType = computed(() => {
    if (props.type === 'world_oz') return 'world'
    if (props.type === 'new_system') return 'new'
    if (props.type === 'traditional') return 'traditional'
    return 'myanmar'
})

const currencySymbol = computed(() => {
    if (props.type === 'world_oz') return '$'
    return 'MMK'
})

const stats = computed(() => {
    const points = chartPoints.value;
    const usdPrices = points.map(p => p.price);

    // Filter for SGD points
    const sgdPoints = points.filter(p => p.sgd_price !== null);
    const sgdPrices = sgdPoints.map(p => p.sgd_price);

    if (!usdPrices.length) return {
        high: null,
        low: null,
        avg: null,
        count: 0,
        sgd: null
    };

    const result = {
        high: Math.max(...usdPrices),
        low: Math.min(...usdPrices),
        avg: usdPrices.reduce((a, b) => a + b, 0) / usdPrices.length,
        count: points.length,
    };

    // Add SGD stats for world gold
    if (props.type === 'world_oz' && sgdPrices.length) {
        result.sgd = {
            high: Math.max(...sgdPrices),
            low: Math.min(...sgdPrices),
            avg: sgdPrices.reduce((a, b) => a + b, 0) / sgdPrices.length,
        };
    }

    return result;
});

const priceLabel = computed(() => {
    if (props.type === 'world_oz') return 'Price · USD & SGD / oz'
    return `Price · ${props.currency}`
})

const dataContext = computed(() => {
    const points = chartPoints.value
    if (!points.length) return null

    // If mobile, return shorter strings
    if (props.isMobile) {
        return `${points.length} pts`
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
    const isWorld = props.type === 'world_oz';
    return {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 400, easing: 'easeOutQuart' },
        plugins: {
            // Legend must be a sibling of tooltip, not inside it
            legend: {
                display: props.type === 'world_oz',
                position: 'top',
                align: 'end',
                labels: {
                    usePointStyle: true,
                    boxWidth: 8,
                    padding: 15,
                    font: { size: 11, weight: '600' },
                    color: '#a1a1aa'
                }
            },
            tooltip: {
                enabled: true,
                mode: 'index', // Important: shows both values on one hover
                intersect: false,
                backgroundColor: 'rgba(255, 255, 255, 0.98)',
                titleColor: '#18181b',
                bodyColor: '#27272a',
                borderColor: '#e4e4e7',
                borderWidth: 1,
                padding: 12,
                displayColors: true, // Set to true to see the line colors in tooltip
                callbacks: {
                    label: (context) => {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y;
                        const symbol = label.includes('USD') ? '$' : 'S$';

                        if (label === 'USD ') {
                            return `${label}: $${value.toFixed(2)}`;
                        } else if (label === 'SGD ') {
                            return `${label}: S$${value.toFixed(2)}`;
                        } else {
                            // MMK Price - NO dollar symbol
                            return `${label}: ${value.toLocaleString()} `;
                        }
                        // return `${label}: ${symbol}${value.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    }
                }
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: false,
                grace: '40%',
                grid: {
                    display: !props.isMobile,
                    color: 'rgba(240, 240, 238, 0.5)'
                },
                ticks: {
                    color: props.chartColor, // Match USD line color
                    font: { size: props.isMobile ? 9 : 11 },
                    callback: (v) => isWorld ? `$${v}` : formatYAxisTick(v)
                }
            },
            y1: {
                type: 'linear',
                display: isWorld, // ONLY show on world_oz
                position: 'right',
                beginAtZero: false,
                grace: '40%',
                grid: {
                    drawOnChartArea: false, // Don't overlap grid lines
                },
                ticks: {
                    display: !props.isMobile,
                    color: '#10b981', // Match SGD green line color
                    font: { size: props.isMobile ? 9 : 11 },
                    callback: (v) => `S$${v}`
                }
            },
            x: {
                type: 'time',
                time: {
                    unit: getTimeUnit(),
                    displayFormats: getTimeFormats(),
                },
                ticks: {
                    display: !props.isMobile,
                    color: '#a1a1aa',
                    maxTicksLimit: props.isMobile ? 3 : 8
                },
                grid: { display: false }
            }
        }
    }
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
    if (!chartCanvas.value || !chartPoints.value.length) return;

    const ctx = chartCanvas.value.getContext('2d');
    const primaryColor = props.chartColor; // Usually Blue
    const secondaryColor = '#10b981'; // Emerald/Green for SGD
    const points = chartPoints.value;

    if (chartInstance) {
        chartInstance.destroy();
    }

    // Determine the label and symbol based on type
    let primaryLabel = '';
    let primarySymbol = '';

    if (props.type === 'world_oz') {
        primaryLabel = 'USD ';
        primarySymbol = '$';
    } else if (props.type === 'sgd_oz') {
        primaryLabel = 'SGD ';
        primarySymbol = 'S$';
    } else {
        primaryLabel = 'MMK ';
        primarySymbol = ''; // No symbol for MMK
    }

    const datasets = [
        {
            label: primaryLabel,
            data: points.map(p => ({ x: p.date, y: p.price })),
            borderColor: primaryColor,
            yAxisID: 'y',
            backgroundColor: 'transparent',
            borderWidth: 3,
            tension: 0.3,
            pointRadius: 0,
        }
    ];

    // Only add SGD dataset if we are on world_oz and have data
    if (props.type === 'world_oz' && points.some(p => p.sgd_price)) {
        datasets.push({
            label: 'SGD ',
            data: points.map(p => ({ x: p.date, y: p.sgd_price })),
            borderColor: secondaryColor,
            yAxisID: 'y1',
            backgroundColor: 'transparent',
            borderWidth: 2,
            borderDash: [5, 5],
            tension: 0.3,
            pointRadius: 0,
        });
    }

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: { datasets },
        options: getChartOptions(primarySymbol) // Pass symbol to options
    });
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