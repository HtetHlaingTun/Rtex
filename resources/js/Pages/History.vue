<template>
    <GuestLayout>

        <Head :title="`${currency.name} (${currency.code}) — Rate History`" />

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Sticky Header -->
            <header
                class="sticky top-[145px] sm:top-[100px] z-40 w-full bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-b border-slate-200 dark:border-zinc-800 shadow-sm">
                <div class="max-w-[960px] mx-auto px-5 sm:px-8 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

                        <!-- Left: Title -->
                        <div class="flex items-center gap-4">
                            <Link :href="route('welcome')"
                                class="p-2 -ml-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </Link>
                            <div>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                        <span class="text-sm font-black text-slate-600 dark:text-slate-300">
                                            {{ currency.code?.charAt(0) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">
                                            {{ currency.code }}
                                        </h1>
                                        <p
                                            class="text-[10px] font-medium text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                                            {{ currency.name }} · Rate History
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Latest Rate Card -->
                        <div class="flex items-center gap-3 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl px-4 py-2">
                            <span class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400">Latest
                                Rate</span>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-2xl font-mono font-black tracking-tighter text-slate-900 dark:text-white">
                                    {{ formatMoney(getLatestRate(), 2) }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">MMK</span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="max-w-[960px] mx-auto px-4 sm:px-8 py-8 pb-16 flex flex-col gap-6">



                <!-- Chart Component -->
                <RateHistoryChart :data="chartData" :loading="chartLoading" :error="chartError" :options="chartOptions"
                    @period-change="handlePeriodChange" @retry="fetchChartData" />

                <!-- Enhanced History Component -->
                <PublicCurrencyRate :history="history" />

                <!-- Disclaimer -->
                <div class="mt-2 text-center text-[9px] text-slate-400 dark:text-zinc-600 italic">
                    <p>* Historical rates are for reference only. Actual market prices may vary and are subject to
                        change at the time of transaction.</p>
                </div>

            </main>
        </div>
    </GuestLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import axios from 'axios'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import RateHistoryChart from '@/Components/Charts/Rate/RateHistoryChart.vue'
import PublicCurrencyRate from '@/Components/PublicCurrencyRate.vue'

const props = defineProps({
    currency: { type: Object, required: true },
    history: {
        type: Object,
        required: true,
        default: () => ({ data: [], links: [], current_page: 1, last_page: 1, total: 0 })
    },
    selectedType: { type: String, default: 'exchange' }
})

// Chart State
const chartData = ref([])
const chartLoading = ref(false)
const chartError = ref(null)
const chartOptions = ref({
    showStats: true,
    defaultPeriod: 'month'
})

// Chart Event Handlers
const handlePeriodChange = async (period) => {
    chartLoading.value = true
    chartError.value = null

    try {
        // Make sure the URL is correct
        const response = await axios.get(`/history/${props.currency.id}/chart-data`, {
            params: { period: period }  // period will be 'week', 'month', etc.
        })
        chartData.value = response.data

        console.log(`Fetched ${response.data.length} records for period: ${period}`)

    } catch (error) {
        console.error('Failed to fetch chart data:', error)
        chartError.value = error.response?.data?.message || 'Failed to load chart data'
        chartData.value = []
    } finally {
        chartLoading.value = false
    }
}

const fetchChartData = async () => {
    await handlePeriodChange('month')
}

// Initialize
onMounted(() => {
    chartData.value = props.history.data || []
    if (!chartData.value.length) {
        fetchChartData()
    }
})

// Helpers
const getLatestRate = () => props.history.data?.[0]?.sell_rate ?? 0

const formatMoney = (v, decimals = 2) => {
    if (v == null) return '—'
    const n = parseFloat(v)
    if (isNaN(n)) return '—'
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(n)
}

// Stats
const allTimeHigh = computed(() => {
    const vals = props.history.data?.map(r => Number(r.sell_rate)).filter(v => !isNaN(v)) ?? []
    return vals.length ? formatMoney(Math.max(...vals), 2) : '—'
})

const allTimeLow = computed(() => {
    const vals = props.history.data?.map(r => Number(r.sell_rate)).filter(v => !isNaN(v)) ?? []
    return vals.length ? formatMoney(Math.min(...vals), 2) : '—'
})

const averageRate = computed(() => {
    const vals = props.history.data?.slice(0, 30).map(r => Number(r.sell_rate)).filter(v => !isNaN(v)) ?? []
    if (!vals.length) return '—'
    return formatMoney(vals.reduce((a, b) => a + b, 0) / vals.length, 2)
})

const totalRecords = computed(() => props.history.total ?? props.history.data?.length ?? 0)
</script>