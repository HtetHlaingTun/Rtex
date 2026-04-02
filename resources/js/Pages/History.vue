<template>
    <GuestLayout>

        <Head :title="`${currency.name} (${currency.code}) — Rate History`" />

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-[#111] dark:text-zinc-100 transition-colors duration-300">

            <!-- Sticky Header -->
            <header
                class="sticky top-[145px] sm:top-[100px] z-40 w-full bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-b border-slate-200 dark:border-zinc-800 shadow-sm transition-all duration-300">
                <div class="max-w-[960px] mx-auto px-5 sm:px-8 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

                        <!-- Left: Back Button & Title -->
                        <div class="flex items-center gap-4">

                            <div>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                        <span class="text-sm font-black text-slate-600 dark:text-slate-300">{{
                                            currency.code?.charAt(0) }}</span>
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
                        <div class="flex items-center gap-3  dark:bg-zinc-800/50 rounded-2xl p-3">
                            <div class="flex items-center gap-1.5">

                                <span class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400">Latest
                                    Rate</span>
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-2xl font-mono font-black tracking-tighter text-slate-900 dark:text-white">
                                    {{ formatMoney(getLatestRate(), 2, currency.code === 'MMK') }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">MMK</span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="max-w-[960px] mx-auto px-4 sm:px-8 py-8 pb-16 flex flex-col gap-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">All Time High</span>
                        <p class="text-sm font-mono font-bold text-slate-800 dark:text-white mt-1">{{ allTimeHigh }}</p>
                        <p class="text-[7px] text-slate-400">MMK per unit</p>
                    </div>
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">All Time Low</span>
                        <p class="text-sm font-mono font-bold text-slate-800 dark:text-white mt-1">{{ allTimeLow }}</p>
                        <p class="text-[7px] text-slate-400">MMK per unit</p>
                    </div>
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">30-Day Avg</span>
                        <p class="text-sm font-mono font-bold text-slate-800 dark:text-white mt-1">{{ averageRate }}</p>
                        <p class="text-[7px] text-slate-400">Mid rate</p>
                    </div>
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-sm">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">Total Records</span>
                        <p class="text-sm font-mono font-bold text-slate-800 dark:text-white mt-1">{{ totalRecords }}
                        </p>
                        <p class="text-[7px] text-slate-400">Historical entries</p>
                    </div>
                </div>

                <!-- Chart Component -->
                <RateHistoryChart :data="chartData" :loading="chartLoading" :error="chartError" :options="chartOptions"
                    @period-change="handlePeriodChange" @retry="fetchChartData" />

                <!-- History Table -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm transition-all duration-300">
                    <div v-if="paginatedData.length > 0">
                        <!-- Table Header -->
                        <div
                            class="grid grid-cols-[2fr_1fr_1fr_1fr_0.9fr] max-md:grid-cols-[2fr_1fr_1fr] px-5 py-3.5 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800 text-[10px] font-black tracking-[0.15em] uppercase text-slate-400 dark:text-zinc-500">
                            <span>Date & Time</span>
                            <span class="text-right">Buy (MMK)</span>
                            <span class="text-right">Sell (MMK)</span>
                            <span class="text-right max-md:hidden">Spread</span>
                            <span class="text-right max-md:hidden">Change</span>
                        </div>

                        <!-- Table Body -->
                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <div v-for="(record, index) in paginatedData" :key="record.id"
                                class="group grid grid-cols-[2fr_1fr_1fr_1fr_0.9fr] max-md:grid-cols-[2fr_1fr_1fr] items-center px-5 py-3.5 hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-all duration-200 hover:pl-6">

                                <!-- Date Column -->
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[13px] font-bold text-slate-800 dark:text-white">
                                        {{ formatDate(record.rate_date || record.created_at) }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-[10px] font-mono text-slate-400">{{
                                            formatTime(record.created_at) }}</span>
                                        <span v-if="$isToday(record.created_at)"
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-full text-[8px] font-black uppercase">
                                            <span class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></span>
                                            Live
                                        </span>
                                    </div>
                                </div>

                                <!-- Buy Rate -->
                                <div class="text-right">
                                    <span
                                        class="text-[13px] font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ formatMoney(record.buy_rate) }}
                                    </span>
                                    <div class="flex items-center justify-end gap-0.5 mt-0.5">
                                        <TrendIcon :current="record.buy_rate"
                                            :previous="paginatedData[index + 1]?.buy_rate" class="scale-75" />
                                    </div>
                                </div>

                                <!-- Sell Rate -->
                                <div class="text-right">
                                    <span class="text-[13px] font-mono font-bold text-rose-600 dark:text-rose-400">
                                        {{ formatMoney(record.sell_rate) }}
                                    </span>
                                    <div class="flex items-center justify-end gap-0.5 mt-0.5">
                                        <TrendIcon :current="record.sell_rate"
                                            :previous="paginatedData[index + 1]?.sell_rate" class="scale-75" />
                                    </div>
                                </div>

                                <!-- Spread -->
                                <div class="text-right max-md:hidden">
                                    <span class="text-[11px] font-mono font-bold text-slate-500 dark:text-slate-400">
                                        {{ formatSpread(record) }}
                                    </span>
                                </div>

                                <!-- Change -->
                                <!-- Change Column -->
                                <div class="text-right max-md:hidden">
                                    <span v-if="getChange(record, index)" :class="[
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black',
                                        getChange(record, index).dir === 'up'
                                            ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                            : getChange(record, index).dir === 'down'
                                                ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'
                                                : 'bg-slate-100 text-slate-500 dark:bg-zinc-800 dark:text-zinc-400'
                                    ]">
                                        <span v-if="getChange(record, index).dir === 'up'">▲</span>
                                        <span v-else-if="getChange(record, index).dir === 'down'">▼</span>
                                        <span v-else>—</span>
                                        {{ getChange(record, index).text }}
                                    </span>
                                    <span v-else class="text-slate-300 dark:text-zinc-700 text-[9px]">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="history.last_page > 1"
                            class="flex flex-col sm:flex-row justify-between items-center gap-4 px-5 py-4 bg-slate-50/50 dark:bg-zinc-800/20 border-t border-slate-200 dark:border-zinc-800">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500">
                                {{ history.total }} records · Page {{ history.current_page }} of {{ history.last_page }}
                            </span>
                            <Paginations :links="history.links" />
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-16 text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-zinc-800 mb-4">
                            <svg class="w-8 h-8 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">No historical data available
                        </p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500 mt-1">Data will appear after the first
                            rate sync</p>
                    </div>
                </div>

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
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import RateHistoryChart from '@/Components/Charts/Rate/RateHistoryChart.vue'


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
            params: { period }
        })
        chartData.value = response.data
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

const paginatedData = computed(() => props.history.data ?? [])

const formatMoney = (v, decimals = 2, isMmk = false) => {
    if (v == null) return '—'
    const n = parseFloat(v)
    if (isNaN(n)) return '—'
    const formatted = new Intl.NumberFormat('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(n)
    return formatted
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
    const spread = parseFloat(r.sell_rate) - parseFloat(r.buy_rate)
    return formatMoney(spread, 2)
}

const getBadgeClass = (dir) => {
    if (dir === 'up') return 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800'
    if (dir === 'down') return 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-800'
    return 'bg-slate-100 text-slate-500 border-slate-100 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
}

const getChange = (record, index) => {
    const next = props.history.data?.[index + 1]
    if (!next) return null
    const curr = (Number(record.buy_rate) + Number(record.sell_rate)) / 2
    const prev = (Number(next.buy_rate) + Number(next.sell_rate)) / 2
    if (!prev) return null
    const pct = ((curr - prev) / prev) * 100

    // Handle neutral/stable case
    if (Math.abs(pct) < 0.01) {
        return {
            text: '0.00%',
            dir: 'neutral'
        }
    }

    return {
        text: `${pct > 0 ? '+' : ''}${pct.toFixed(2)}%`,
        dir: pct > 0 ? 'up' : 'down'
    }
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