<template>
    <GuestLayout>

        <Head :title="pageInfo.title" />

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-slate-900 dark:text-zinc-100 transition-colors duration-300">

            <header
                class="sticky top-[100px] z-50 w-full bg-white/90 dark:bg-zinc-900/90 border-b border-slate-200 dark:border-zinc-800 backdrop-blur-md">

                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">

                        <!-- Page Title & Description -->
                        <div class="flex items-start gap-4">
                            <div class="flex flex-col">
                                <h1
                                    class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">
                                    {{ pageInfo.title }}
                                </h1>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <p
                                        class="text-[10px] sm:text-xs text-slate-500 dark:text-zinc-500 font-bold uppercase tracking-wider">
                                        {{ pageInfo.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Price & Stats -->
                        <div class="flex items-center justify-between gap-2">
                            <div v-if="latestPrice" class="flex flex-col md:items-end">
                                <span
                                    class="text-[8px] md:text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-zinc-500 mb-0.5 md:mb-1">
                                    Live Market Price
                                </span>
                                <div class="flex items-baseline gap-1" :class="getTextColor(pageInfo.colorClass)">
                                    <span class="text-xs font-bold opacity-60 self-center">{{ pageInfo.symbol }}</span>
                                    <span
                                        class="text-xl md:text-3xl font-mono font-black tracking-tighter tabular-nums leading-none">
                                        {{ $formatMoney(latestPrice, 2, pageInfo.currency === 'MMK') }}
                                    </span>
                                    <span v-if="pageInfo.currency === 'MMK'"
                                        class="text-[10px] font-black opacity-40 ml-0.5">MMK</span>
                                </div>

                                <div v-if="selectedType === 'world_oz' && stats?.current_sgd"
                                    class="flex items-center gap-1 mt-1 text-blue-600 dark:text-blue-400 opacity-80">
                                    <span class="text-[10px] font-bold">S$</span>
                                    <span class="text-xs md:text-xl font-mono font-bold tabular-nums">
                                        {{ $formatMoney(stats.current_sgd, 2) }}
                                    </span>
                                </div>
                            </div>


                            <!-- Stats Pill -->
                            <div v-if="stats"
                                class="flex items-center gap-2.5  rounded-xl text-[10px] md:text-[11px] font-black shadow-sm border transition-all duration-500"
                                :class="getPillClass(stats.trend)">

                                <div
                                    class="flex items-center justify-center w-5 h-5 rounded-full bg-white/20 dark:bg-black/20 flex-shrink-0">
                                    <span class="text-[10px]">{{ stats.trend === 'up' ? '▲' : stats.trend === 'down' ?
                                        '▼' : '—' }}</span>
                                </div>

                                <div class="flex flex-col leading-tight">
                                    <span class="whitespace-nowrap tabular-nums">
                                        {{ stats.symbol }}{{ $formatMoney(stats.diff, 0) }}
                                        <span class="opacity-70 text-[8px] font-medium ml-1">({{ stats.percent
                                        }}%)</span>
                                    </span>

                                    <span v-if="selectedType === 'world_oz' && stats.diff_sgd"
                                        class="whitespace-nowrap tabular-nums text-[9px] opacity-80 mt-0.5 border-t border-current/10 pt-0.5">
                                        S$ {{ $formatMoney(stats.diff_sgd, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </header>


            <main class="max-w-4xl mx-auto px-8 sm:px-4 lg:px-8 pt-9 pb-20 flex flex-col gap-6">

                <div class="overflow-x-auto">
                    <div class="flex gap-2 min-w-max">
                        <Link v-for="tab in tabs" :key="tab.value"
                            :href="route('public.gold.history', { type: tab.value })"
                            class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm font-sans border rounded-lg transition-all duration-200 whitespace-nowrap text-center"
                            :class="selectedType === tab.value
                                ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-zinc-950 dark:border-white'
                                : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-800 dark:hover:bg-zinc-800'">
                            {{ tab.label }}
                        </Link>
                    </div>
                </div>


                <!-- chart  -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-4 sm:p-6 shadow-sm">
                    <UserGoldChart :key="selectedType" :id="history.data[0]?.gold_type_id || 'world'"
                        :type="selectedType" :chartColor="pageInfo.chartColor"
                        :currency="selectedType === 'world_oz' ? 'USD' : 'MMK'"
                        :symbol="selectedType === 'world_oz' ? '$' : ''" />
                </div>




                <!-- table  -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
                    <div v-if="historyData.length">
                        <div
                            class="grid grid-cols-2 sm:grid-cols-[2fr_1.4fr_1fr] px-6 py-3 bg-slate-50/80 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800">
                            <span
                                class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500">Recorded
                                At</span>
                            <span
                                class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 text-right">Price
                                Trend</span>
                            <span
                                class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 text-right hidden sm:block">Total
                                Change</span>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <div v-for="(entry, index) in historyData" :key="entry.id"
                                class="grid grid-cols-2 sm:grid-cols-[2fr_1.4fr_1fr] items-center px-6 py-4 transition-colors hover:bg-slate-50/80 dark:hover:bg-zinc-800/30 even:bg-slate-50/30 dark:even:bg-zinc-800/10">

                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 dark:text-zinc-100 leading-snug">
                                        {{ formatDate(entry.price_date || entry.created_at) }}
                                    </span>
                                    <span class="text-[11px] font-mono text-slate-400 dark:text-zinc-500 mt-0.5">
                                        {{ formatTime(entry.created_at) }}
                                    </span>
                                </div>

                                <div class="text-right flex flex-col items-end gap-1">
                                    <div class="flex items-baseline gap-1" :class="getTextColor(pageInfo.colorClass)">
                                        <span class="text-[10px] font-bold opacity-60">{{ pageInfo.symbol }}</span>
                                        <span class="text-sm font-mono font-black tabular-nums">
                                            {{ $formatMoney(entry.price, 2, pageInfo.currency === 'MMK') }}
                                        </span>
                                        <TrendIcon :current="entry.price" :previous="historyData[index + 1]?.price"
                                            :show-amount="true" />
                                    </div>

                                    <div v-if="selectedType === 'world_oz' && entry.sgd_price"
                                        class="flex items-baseline gap-1 ">
                                        <span class="text-[10px] font-bold opacity-60">S$</span>
                                        <span class="text-sm font-mono font-bold tabular-nums">
                                            {{ $formatMoney(entry.sgd_price, 2) }}
                                        </span>
                                        <TrendIcon :current="entry.sgd_price"
                                            :previous="historyData[index + 1]?.sgd_price" :show-amount="true" />
                                    </div>


                                </div>

                                <div class="text-right hidden sm:block">
                                    <span v-if="index < historyData.length - 1"
                                        class="inline-block text-[10px] font-bold px-2.5 py-1 rounded-full border border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-500 shadow-sm"
                                        :class="getBadgeClass(getRowChange(entry.price, historyData[index + 1]?.price).dir)">
                                        {{ getRowChange(entry.price, historyData[index + 1]?.price).text }}
                                    </span>
                                    <span v-else class="text-slate-200 dark:text-zinc-800">—</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="history.last_page > 1"
                            class="flex flex-col sm:flex-row justify-between items-center gap-4 px-6 py-5 bg-slate-50/50 dark:bg-zinc-800/20 border-t border-slate-200 dark:border-zinc-800">
                            <span
                                class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider">
                                {{ history.total }} Records · Page {{ history.current_page }} / {{ history.last_page }}
                            </span>

                            <div class="flex gap-1.5 flex-wrap justify-center">
                                <Link v-for="link in history.links" :key="link.label" :href="link.url || '#'"
                                    class="min-w-[34px] h-8 flex items-center justify-center px-2 text-[11px] font-black border rounded-lg transition-all"
                                    :class="link.active
                                        ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-zinc-950 dark:border-white'
                                        : 'bg-white text-slate-500 border-slate-200 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-800 hover:border-slate-400',
                                        !link.url && 'opacity-20 pointer-events-none'" v-html="link.label" />
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-24 text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-50 dark:bg-zinc-800 mb-3 text-slate-300">
                            <i class="fas fa-history text-lg"></i>
                        </div>
                        <p class="text-sm text-slate-400 dark:text-zinc-500 italic">No price data history available.</p>
                    </div>
                </div>

                <p class="text-center text-[11px] text-slate-400 dark:text-zinc-600 px-10">
                    {{ isWorldGold ? 'World gold sourced from Yahoo Finance. Snapshots taken throughout the day.' :
                        'Local gold prices and world gold prices are updated daily.' }}
                </p>

            </main>
        </div>
    </GuestLayout>
</template>

<script setup>
import UserGoldChart from '@/Components/Charts/UserGoldChart.vue'
import TrendIcon from '@/Components/TrendIcon.vue'
import PublicBackButton from '@/Components/UI/PublicBackButton.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'


const props = defineProps({
    history: { type: Object, required: true },
    selectedType: { type: String, required: true },
    breadcrumbs: { type: Array, default: () => [] },
    stats: Object, // Contains trend, diff, percent, etc.
    latestPrice: Number // Change from Object to Number
})

const currentBreakpoint = ref('default');

const updateBreakpoint = () => {
    const width = window.innerWidth;
    if (width >= 758) {
        currentBreakpoint.value = 'sm';
    } else if (width >= 471) {
        currentBreakpoint.value = 'xs';
    } else {
        currentBreakpoint.value = 'default';
    }
};

// ── Config ───────────────────────────────────────────
const tabs = [
    { value: 'new_system', label: 'New System' },
    { value: 'traditional', label: 'Traditional' },
    { value: 'world_oz', label: 'World Spot' },
]

const typeMap = {
    world_oz: {
        title: 'World Gold Spot',
        description: 'USD & SGD / Troy oz · 31.1 g',
        currency: 'USD', // Primary currency
        symbol: '$',
        secondaryCurrency: 'SGD',
        secondarySymbol: 'S$',
        colorClass: 'blue',
        chartColor: '#2563EB',
        secondaryChartColor: '#10b981' // Emerald color for the SGD line
    },
    new_system: { title: 'New System Gold', description: 'MMK / Kyatthar · 16.329 g', currency: 'MMK', symbol: '', colorClass: 'green', chartColor: '#16A34A' },
    traditional: { title: 'Traditional Gold', description: 'MMK / Kyatthar · 16.606 g', currency: 'MMK', symbol: '', colorClass: 'amber', chartColor: '#D97706' },
}

const pageInfo = computed(() => typeMap[props.selectedType] ?? { title: 'Gold History', description: '', currency: '', symbol: '', colorClass: '', chartColor: '#888' })

const isWorldGold = computed(() => props.selectedType === 'world_oz')
const chartPeriod = ref(isWorldGold.value ? '7d' : '1m')

// ── Data ─────────────────────────────────────────────
const historyData = computed(() => props.history?.data ?? [])



// Price change: latest vs first record from a different day
const priceChange = computed(() => {
    const data = historyData.value
    if (data.length < 2) return null

    const latest = data[0]
    const latestPrice = Number(latest.price)
    const dateField = latest.price_date ? 'price_date' : 'created_at'
    const todayStr = new Date(latest[dateField]).toDateString()

    const ref = data.find(r => new Date(r[dateField]).toDateString() !== todayStr)
        ?? data[data.length - 1]

    const prevPrice = Number(ref.price)
    if (!prevPrice) return null

    const diff = latestPrice - prevPrice
    const pct = ((diff / prevPrice) * 100).toFixed(2)

    return {
        formattedValue: (diff >= 0 ? '+' : '') + diff.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
        percentage: (diff >= 0 ? '+' : '') + pct + '%',
        trend: diff > 0 ? 'up' : diff < 0 ? 'down' : 'neutral',
        compareDate: new Date(ref[dateField]).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }),
    }
})

// ── Helpers ──────────────────────────────────────────
const formatDate = (s) =>
    s ? new Date(s).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

const formatTime = (s) => {
    if (!s || s.length === 10) return ''
    return new Date(s).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false })
}

const getRowChange = (current, previous) => {
    if (previous == null) return { text: '—', dir: '' }
    const c = Number(current), p = Number(previous)
    if (!p) return { text: '—', dir: '' }
    const pct = (c - p) / p * 100
    return { text: `${pct > 0 ? '+' : ''}${pct.toFixed(2)}%`, dir: pct > 0 ? 'up' : pct < 0 ? 'dn' : 'neutral' }
}

// Add these helper functions for dynamic Tailwind classes
const getTextColor = (color) => {
    const map = {
        green: 'text-emerald-600 dark:text-emerald-400',
        blue: 'text-blue-600 dark:text-blue-400',
        amber: 'text-amber-600 dark:text-amber-500'
    }
    return map[color] || 'text-slate-900 dark:text-white'
}

const getPillClass = (trend) => {
    if (trend === 'up') return ' text-emerald-700   dark:text-emerald-400 dark:border-emerald-500/20'
    if (trend === 'down') return 'text-rose-700   dark:text-rose-400 dark:border-rose-500/20'
    return ' text-slate-600  dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
}

const getBadgeClass = (dir) => {
    if (dir === 'up') return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
    if (dir === 'dn') return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'
    return 'text-slate-400'
}
onMounted(() => {
    updateBreakpoint();
    window.addEventListener('resize', updateBreakpoint);
})
onUnmounted(() => {
    window.removeEventListener('resize', updateBreakpoint);
})
</script>
