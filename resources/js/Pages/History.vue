<template>
    <GuestLayout>

        <Head :title="`${currency.name} (${currency.code}) — Rate History`" />

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Sticky Header - Responsive -->
            <header
                class="sticky top-[145px] sm:top-[100px] z-40 w-full bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-b border-slate-200 dark:border-zinc-800 shadow-sm">
                <div class="max-w-[960px] mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">

                        <!-- Left: Title with Back Button -->
                        <div class="flex items-center gap-3 sm:gap-4 w-full sm:w-auto">
                            <Link :href="route('rates.index')"
                                class="p-1.5 sm:p-2 -ml-1.5 sm:-ml-2 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors active:scale-95">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </Link>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                        <span class="text-xs sm:text-sm font-black text-slate-600 dark:text-slate-300">
                                            {{ currency.code?.charAt(0) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h1
                                            class="text-base sm:text-xl font-black tracking-tight text-slate-900 dark:text-white">
                                            {{ currency.code }}
                                        </h1>
                                        <p
                                            class="text-[8px] sm:text-[10px] font-medium text-slate-400 dark:text-zinc-500 uppercase tracking-wider hidden sm:block">
                                            {{ currency.name }} · Rate History
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Latest Rate Card - Responsive -->
                        <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-3">
                            <div
                                class="flex items-center gap-2 sm:gap-3 bg-slate-50 dark:bg-zinc-800/50 rounded-xl sm:rounded-2xl px-3 sm:px-4 py-1.5 sm:py-2 flex-1 sm:flex-none justify-between">
                                <span
                                    class="text-[7px] sm:text-[8px] font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-400">Latest</span>
                                <div class="flex items-baseline gap-0.5 sm:gap-1">
                                    <span
                                        class="text-base sm:text-2xl font-mono font-black tracking-tighter text-slate-900 dark:text-white">
                                        {{ formatMoney(getLatestRate(), 2) }}
                                    </span>
                                    <span class="text-[7px] sm:text-[9px] font-bold text-slate-400 uppercase">MMK</span>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </header>

            <main class="max-w-[960px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-16 flex flex-col gap-5 sm:gap-6">

                <!-- Stats Cards Row - Responsive Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <!-- All Time High -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1">
                            <span
                                class="text-[7px] sm:text-[8px] font-black text-slate-400 uppercase tracking-wider">All
                                Time High</span>
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <p class="text-sm sm:text-base font-mono font-bold text-slate-800 dark:text-white mt-0.5">{{
                            allTimeHigh }}</p>
                        <p class="text-[6px] sm:text-[7px] text-slate-400">MMK per unit</p>
                    </div>

                    <!-- All Time Low -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1">
                            <span
                                class="text-[7px] sm:text-[8px] font-black text-slate-400 uppercase tracking-wider">All
                                Time Low</span>
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        </div>
                        <p class="text-sm sm:text-base font-mono font-bold text-slate-800 dark:text-white mt-0.5">{{
                            allTimeLow }}</p>
                        <p class="text-[6px] sm:text-[7px] text-slate-400">MMK per unit</p>
                    </div>

                    <!-- 30-Day Avg -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1">
                            <span
                                class="text-[7px] sm:text-[8px] font-black text-slate-400 uppercase tracking-wider">30-Day
                                Avg</span>
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <p class="text-sm sm:text-base font-mono font-bold text-slate-800 dark:text-white mt-0.5">{{
                            averageRate }}</p>
                        <p class="text-[6px] sm:text-[7px] text-slate-400">Mid rate</p>
                    </div>

                    <!-- Total Records -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1">
                            <span
                                class="text-[7px] sm:text-[8px] font-black text-slate-400 uppercase tracking-wider">Records</span>
                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2 1.5 4 4 4h8c2.5 0 4-2 4-4V7c0-2-1.5-4-4-4H8C5.5 3 4 5 4 7z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3v4h8V3" />
                            </svg>
                        </div>
                        <p class="text-sm sm:text-base font-mono font-bold text-slate-800 dark:text-white mt-0.5">{{
                            totalRecords }}</p>
                        <p class="text-[6px] sm:text-[7px] text-slate-400">Historical entries</p>
                    </div>
                </div>

                <!-- Chart Component -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm">
                    <RateHistoryChart :data="chartData" :loading="chartLoading" :error="chartError"
                        :options="chartOptions" @period-change="handlePeriodChange" @retry="fetchChartData" />
                </div>

                <!-- Enhanced History Component -->
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm">
                    <PublicCurrencyRate :history="history" />
                </div>

                <!-- Disclaimer -->
                <div class="mt-4 text-center text-[8px] sm:text-[9px] text-slate-400 dark:text-zinc-600 italic px-4">
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
        const response = await axios.get(`/history/${props.currency.id}/chart-data`, {
            params: { period: period }
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

<style scoped>
/* Touch-friendly tap highlights */
@media (max-width: 640px) {
    .hover\:-translate-y-1:active {
        transform: translateY(-2px);
    }

    .active\:scale-95:active {
        transform: scale(0.95);
    }
}
</style>