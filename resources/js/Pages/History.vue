<template>
    <GuestLayout>

        <Head :title="`${currency.name} (${currency.code}) — Rate History`" />

        <template #navigation>
            <PublicBreadcrumb :type="selectedType" />
        </template>

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-[#111] dark:text-zinc-100 transition-colors duration-300">
            <!-- Header -->
            <header
                class="sticky top-0 z-40 w-full bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md border-b border-slate-100 dark:border-zinc-800 transition-all duration-300">
                <div class="max-w-[960px] mx-auto px-5 sm:px-8 py-4 sm:py-5">
                    <div class="flex flex-col xs:flex-row items-start xs:items-center justify-between gap-y-3 gap-x-6">

                        <div class="flex items-center gap-4">
                            <PublicBackButton backUrl="/"
                                class="p-2 -ml-2 rounded-full hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors group"
                                position="inline" backText="" />
                            <div class="w-px h-6 bg-slate-200 dark:bg-zinc-800 hidden xs:block"></div>
                            <div>
                                <h1
                                    class="text-xl font-black tracking-tight text-slate-900 dark:text-zinc-100 leading-none">
                                    {{ currency.code }}
                                </h1>
                                <p
                                    class="text-[11px] font-medium text-slate-400 dark:text-zinc-500 mt-1 uppercase tracking-wider">
                                    {{ currency.name }} <span class="opacity-40">/</span> Rate History
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex flex-row xs:flex-col items-baseline xs:items-end gap-2 xs:gap-0.5 w-full xs:w-auto pt-3 xs:pt-0 border-t xs:border-t-0 border-slate-50 dark:border-zinc-800/50">
                            <div class="flex items-center gap-1.5">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                </span>
                                <span
                                    class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-600">
                                    Latest Rate
                                </span>
                            </div>

                            <div class="flex items-baseline gap-1.5 ml-auto xs:ml-0">
                                <span
                                    class="text-2xl font-mono font-black tabular-nums tracking-tighter text-slate-900 dark:text-zinc-100">
                                    {{ formatMoney(getLatestRate(), 2, currency.code === 'MMK') }}
                                </span>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-600 uppercase">
                                    MMK
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </header>

            <main class="max-w-[960px] mx-auto px-4 sm:px-8 py-10 pb-20 flex flex-col gap-4">



                <!-- Chart Component -->
                <RateHistoryChart :data="chartData" :loading="chartLoading" :error="chartError" :options="chartOptions"
                    @period-change="handlePeriodChange" @retry="fetchChartData" />

                <!-- Table -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-[#EBEBEA] dark:border-zinc-800 rounded-xl overflow-hidden transition-colors duration-300">
                    <div v-if="paginatedData.length > 0">
                        <!-- Table Header -->
                        <div
                            class="grid grid-cols-[2fr_1fr_1fr_1fr_0.9fr] max-md:grid-cols-[2fr_1fr_1fr] px-5 py-2.5 bg-[#FAFAF9] dark:bg-zinc-800/50 border-b border-[#EBEBEA] dark:border-zinc-800 text-[11px] font-monobold tracking-[0.07em] uppercase text-[#C0C0BC] dark:text-zinc-600">
                            <span>Date</span>
                            <span class="text-right">Buy</span>
                            <span class="text-right">Sell</span>
                            <span class="text-right max-md:hidden">Spread</span>
                            <span class="text-right max-md:hidden">Change</span>
                        </div>

                        <!-- Table Body -->
                        <div>
                            <div v-for="(record, index) in paginatedData" :key="record.id"
                                class="grid grid-cols-[2fr_1fr_1fr_1fr_0.9fr] max-md:grid-cols-[2fr_1fr_1fr] items-center px-5 py-3 border-b border-[#F5F5F3] dark:border-zinc-800 last:border-b-0 hover:bg-[#FAFAF9] dark:hover:bg-zinc-800/50 transition-colors">
                                <!-- Date Column -->
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[13px] font-monoum text-[#222] dark:text-zinc-200">{{
                                        formatDate(record.rate_date || record.created_at) }}</span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[11px] text-[#C0C0BC] dark:text-zinc-500">{{
                                            formatTime(record.created_at) }}</span>
                                        <span v-if="$isToday(record.created_at)"
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded text-[10px] font-mono uppercase tracking-wide">
                                            <span
                                                class="w-1.5 h-1.5 bg-emerald-500 dark:bg-emerald-400 rounded-full animate-pulse"></span>
                                            Live
                                        </span>
                                    </div>
                                </div>

                                <!-- Buy Rate -->
                                <div class="text-right flex flex-col items-end justify-center">
                                    <span
                                        class="text-[13px] font-monoum tabular-nums text-emerald-600 dark:text-emerald-400">{{
                                            formatMoney(record.buy_rate) }}</span>
                                    <TrendIcon :current="record.buy_rate"
                                        :previous="paginatedData[index + 1]?.buy_rate" />
                                </div>

                                <!-- Sell Rate -->
                                <div class="text-right flex flex-col items-end justify-center">
                                    <span
                                        class="text-[13px] font-monoum tabular-nums text-rose-600 dark:text-rose-400">{{
                                            formatMoney(record.sell_rate) }}</span>
                                    <TrendIcon :current="record.sell_rate"
                                        :previous="paginatedData[index + 1]?.sell_rate" />
                                </div>

                                <!-- Spread -->
                                <div class="text-right max-md:hidden">
                                    <span class="text-xs font-monoal tabular-nums text-[#C0C0BC] dark:text-zinc-500">{{
                                        formatSpread(record) }}</span>
                                </div>

                                <!-- Change -->
                                <div class="text-right max-md:hidden">
                                    <span v-if="getChange(record, index)" :class="[
                                        'inline-block px-2 py-0.5 rounded text-[11px] font-monobold',
                                        getChange(record, index).dir === 'up'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400'
                                            : 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400'
                                    ]">
                                        {{ getChange(record, index).text }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="history.last_page > 1"
                            class="flex justify-between items-center px-5 py-3.5 border-t border-[#EBEBEA] dark:border-zinc-800 bg-[#FAFAF9] dark:bg-zinc-800/50 gap-3 flex-wrap">
                            <Paginations :links="history.links" />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-14 px-5 text-center text-[#bbb] dark:text-zinc-500 text-sm">
                        <p>No historical data for {{ currency.code }} yet.</p>
                    </div>
                </div>

                <!-- Note -->
                <p class="text-[11px] text-slate-400 dark:text-zinc-600 italic leading-relaxed mt-4 text-center">
                    * Historical rates are for reference only. Actual market prices may vary and are subject to change
                    at the time of transaction.
                </p>
            </main>
        </div>
    </GuestLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import GuestLayout from '@/Layouts/GuestLayout.vue'


const props = defineProps({
    currency: { type: Object, required: true },
    history: {
        type: Object,
        required: true,
        default: () => ({ data: [], links: [], current_page: 1, last_page: 1 })
    },
    selectedType: { type: String, default: 'exchange' }
})

// ── Chart State ──────────────────────────────────────────
const chartData = ref([])
const chartLoading = ref(false)
const chartError = ref(null)
const chartOptions = ref({
    showStats: true,
    defaultPeriod: 'month'
})

// ── Chart Event Handlers ─────────────────────────────────
const handlePeriodChange = async (period) => {
    chartLoading.value = true
    chartError.value = null

    try {
        // Make sure this URL matches your route
        const response = await axios.get(`/history/${props.currency.id}/chart-data`, {
            params: { period }
        })

        chartData.value = response.data
        console.log(`Loaded ${chartData.value.length} data points for period: ${period}`)

    } catch (error) {
        console.error('Failed to fetch chart data:', error)
        chartError.value = error.response?.data?.message || 'Failed to load chart data'
        chartData.value = []
    } finally {
        chartLoading.value = false
    }
}

const fetchChartData = async () => {
    // Default to month period
    await handlePeriodChange('month')
}

// ── Initialize Chart Data ─────────────────────────────────
onMounted(() => {
    // Initialize with existing history data
    chartData.value = props.history.data || []

    // If there's no data, fetch it
    if (!chartData.value.length) {
        fetchChartData()
    }
})

// ── Helpers ────────────────────────────────────────
const getLatestRate = () => props.history.data?.[0]?.sell_rate ?? 0

const paginatedData = computed(() => props.history.data ?? [])

const formatMoney = (v) => {
    if (v == null) return '—'
    const n = parseFloat(v)
    if (isNaN(n)) return '—'
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n)
}

const formatDate = (s) => {
    if (!s) return '—'
    return new Date(s).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatTime = (s) => {
    if (!s) return ''
    return new Date(s).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })
}

const formatSpread = (r) => {
    if (!r.buy_rate || !r.sell_rate) return '—'
    return formatMoney(r.sell_rate - r.buy_rate)
}

const getChange = (record, index) => {
    const next = props.history.data?.[index + 1]
    if (!next) return null
    const curr = (Number(record.buy_rate) + Number(record.sell_rate)) / 2
    const prev = (Number(next.buy_rate) + Number(next.sell_rate)) / 2
    if (!prev) return null
    const pct = ((curr - prev) / prev) * 100
    return {
        text: `${pct > 0 ? '+' : ''}${pct.toFixed(2)}%`,
        dir: pct > 0 ? 'up' : 'dn'
    }
}

// ── Stats ──────────────────────────────────────────
const allTimeHigh = computed(() => {
    const vals = props.history.data?.map(r => Number(r.sell_rate)).filter(v => !isNaN(v)) ?? []
    return vals.length ? formatMoney(Math.max(...vals)) : '—'
})

const allTimeLow = computed(() => {
    const vals = props.history.data?.map(r => Number(r.sell_rate)).filter(v => !isNaN(v)) ?? []
    return vals.length ? formatMoney(Math.min(...vals)) : '—'
})

const averageRate = computed(() => {
    const vals = props.history.data?.slice(0, 30).map(r => Number(r.sell_rate)).filter(v => !isNaN(v)) ?? []
    if (!vals.length) return '—'
    return formatMoney(vals.reduce((a, b) => a + b, 0) / vals.length)
})

const totalRecords = computed(() => props.history.total ?? props.history.data?.length ?? 0)
</script>