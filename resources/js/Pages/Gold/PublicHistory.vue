<template>
    <GuestLayout>

        <Head :title="pageInfo.title" />

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-slate-900 dark:text-zinc-100 transition-colors duration-300">

            <!-- Sticky Header -->
            <header
                class="sticky top-[150px] sm:top-[100px] z-40 w-full bg-white/95 dark:bg-zinc-900/95 border-b border-slate-200 dark:border-zinc-800 backdrop-blur-md shadow-sm">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                        <!-- Page Title & Description -->
                        <div class="flex items-start gap-3">
                            <div :class="`w-1 h-10 rounded-full mt-0.5 ${selectedType === 'world_oz' ? 'bg-blue-500' :
                                selectedType === 'new_system' ? 'bg-emerald-500' : 'bg-amber-500'
                                }`"></div>
                            <div>
                                <h1
                                    class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                                    {{ pageInfo.title }}
                                </h1>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span
                                            class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                    </span>
                                    <p
                                        class="text-[10px] sm:text-xs text-slate-500 dark:text-zinc-500 font-bold uppercase tracking-wider">
                                        {{ pageInfo.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Price Cards - Conditional based on type -->
                        <div class="flex items-center gap-3">
                            <!-- For World Gold: Show USD + SGD -->
                            <template v-if="selectedType === 'world_oz'">
                                <div class="flex items-center gap-3">
                                    <!-- USD Card -->
                                    <div
                                        class="bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-950/30 dark:to-blue-900/20 rounded-2xl p-3 min-w-[140px] border border-blue-200 dark:border-blue-800">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span
                                                class="text-[8px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">USD</span>
                                            <div class="w-1 h-1 rounded-full bg-blue-500"></div>
                                        </div>
                                        <div class="flex items-baseline gap-1 text-blue-700 dark:text-blue-300">
                                            <span class="text-xs font-bold opacity-60">$</span>
                                            <span class="text-xl md:text-2xl font-mono font-black tracking-tighter">
                                                {{ $formatMoney(latestPrice, 2) }}
                                            </span>
                                        </div>
                                        <div v-if="stats"
                                            class="mt-2 pt-1.5 border-t border-blue-200 dark:border-blue-800">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-1">
                                                    <span
                                                        :class="stats.trend === 'up' ? 'text-emerald-600' : 'text-rose-600'"
                                                        class="text-[9px] font-black">
                                                        {{ stats.trend === 'up' ? '▲' : '▼' }}
                                                    </span>
                                                    <span
                                                        :class="stats.trend === 'up' ? 'text-emerald-600' : 'text-rose-600'"
                                                        class="text-[10px] font-mono font-bold">
                                                        ${{ $formatMoney(Math.abs(stats.diff), 0) }}
                                                    </span>
                                                </div>
                                                <span class="text-[9px] text-slate-500">({{ Math.abs(stats.percent)
                                                }}%)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- SGD Card - Same blue theme -->
                                    <div v-if="stats?.diff_sgd"
                                        class="bg-gradient-to-br from-blue-50 to-blue-100/50 dark:from-blue-950/30 dark:to-blue-900/20 rounded-2xl p-3 min-w-[140px] border border-blue-200 dark:border-blue-800">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span
                                                class="text-[8px] font-black uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">SGD</span>
                                            <div class="w-1 h-1 rounded-full bg-blue-400"></div>
                                        </div>
                                        <div class="flex items-baseline gap-1 text-blue-700 dark:text-blue-300">
                                            <span class="text-xs font-bold opacity-60">S$</span>
                                            <span class="text-xl md:text-2xl font-mono font-black tracking-tighter">
                                                {{ $formatMoney(latestSgdPrice, 2) }}
                                            </span>
                                        </div>
                                        <div class="mt-2 pt-1.5 border-t border-blue-200 dark:border-blue-800">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-1">
                                                    <span
                                                        :class="stats.diff_sgd >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                                        class="text-[9px] font-black">
                                                        {{ stats.diff_sgd >= 0 ? '▲' : '▼' }}
                                                    </span>
                                                    <span
                                                        :class="stats.diff_sgd >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                                        class="text-[10px] font-mono font-bold">
                                                        S${{ $formatMoney(Math.abs(stats.diff_sgd), 0) }}
                                                    </span>
                                                </div>
                                                <span class="text-[9px] text-slate-500">({{ Math.abs(stats.percent_sgd)
                                                }}%)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- For Myanmar Gold: Show MMK only -->
                            <template v-else>
                                <div v-if="latestPrice"
                                    class="bg-slate-50 dark:bg-zinc-800/50 rounded-2xl p-3 min-w-[160px]">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span
                                            class="text-[8px] font-black uppercase tracking-[0.2em] text-slate-400">Live
                                            Market Price</span>
                                        <div
                                            :class="`w-1 h-1 rounded-full ${selectedType === 'new_system' ? 'bg-emerald-500' : 'bg-amber-500'}`">
                                        </div>
                                    </div>
                                    <div class="flex items-baseline gap-1" :class="getTextColor(pageInfo.colorClass)">
                                        <span class="text-xl md:text-2xl font-mono font-black tracking-tighter">
                                            {{ $formatMoney(latestPrice, 2, true) }}
                                        </span>
                                        <span class="text-[9px] font-black opacity-40">MMK</span>
                                    </div>
                                    <div v-if="stats"
                                        class="flex items-center gap-1 mt-1.5 pt-1 border-t border-slate-200 dark:border-zinc-700">
                                        <span :class="stats.trend === 'up' ? 'text-emerald-600' : 'text-rose-600'"
                                            class="text-[9px] font-black">
                                            {{ stats.trend === 'up' ? '▲' : '▼' }}
                                        </span>
                                        <span :class="stats.trend === 'up' ? 'text-emerald-600' : 'text-rose-600'"
                                            class="text-[10px] font-mono font-bold">
                                            {{ $formatMoney(Math.abs(stats.diff), 0) }}
                                        </span>
                                        <span class="text-[9px] text-slate-400">({{ Math.abs(stats.percent) }}%)</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </header>


            <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16 flex flex-col gap-6">

                <!-- Tab Navigation - Enhanced -->
                <div class="overflow-x-auto pb-1">
                    <div class="flex gap-2 min-w-max">
                        <Link v-for="tab in tabs" :key="tab.value"
                            :href="route('user.gold.history', { type: tab.value })"
                            class="px-4 sm:px-5 py-2 text-sm font-bold rounded-xl transition-all duration-200 whitespace-nowrap"
                            :class="selectedType === tab.value
                                ? `bg-${tab.value === 'world_oz' ? 'blue' : tab.value === 'new_system' ? 'emerald' : 'amber'}-500 text-white shadow-lg shadow-${tab.value === 'world_oz' ? 'blue' : tab.value === 'new_system' ? 'emerald' : 'amber'}-500/20`
                                : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-400 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
                            {{ tab.label }}
                        </Link>
                    </div>
                </div>

                <!-- Chart Card - Enhanced -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div
                        class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">

                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Price History
                                Chart</span>
                        </div>

                    </div>
                    <UserGoldChart :key="selectedType" :id="history.data[0]?.gold_type_id || 'world'"
                        :type="selectedType" :chartColor="pageInfo.chartColor"
                        :currency="selectedType === 'world_oz' ? 'USD' : 'MMK'"
                        :symbol="selectedType === 'world_oz' ? '$' : ''" />
                </div>

                <!-- History Table - Enhanced -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">
                    <div v-if="historyData.length">
                        <!-- Table Header -->
                        <div
                            class="grid grid-cols-2 sm:grid-cols-[2fr_1.4fr_1fr] px-6 py-3.5 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800">
                            <span
                                class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-zinc-500">Date
                                & Time</span>
                            <span
                                class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-zinc-500 text-right">Price</span>
                            <span
                                class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 dark:text-zinc-500 text-right hidden sm:block">24h
                                Change</span>
                        </div>

                        <!-- Table Rows -->
                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <div v-for="(entry, index) in historyData" :key="entry.id"
                                class="group grid grid-cols-2 sm:grid-cols-[2fr_1.4fr_1fr] items-center px-6 py-4 transition-all duration-200 hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 hover:pl-7">

                                <!-- Date Column -->
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 dark:text-zinc-100">
                                        {{ formatDate(entry.price_date || entry.created_at) }}
                                    </span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-[10px] font-mono text-slate-400">{{
                                            formatTime(entry.created_at) }}</span>
                                    </div>
                                </div>

                                <!-- Price Column -->
                                <div class="text-right">
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="flex items-baseline gap-1"
                                            :class="getTextColor(pageInfo.colorClass)">
                                            <span class="text-[9px] font-bold opacity-60">{{ pageInfo.symbol }}</span>
                                            <span class="text-base font-mono font-black tabular-nums">
                                                {{ $formatMoney(entry.price, 2, pageInfo.currency === 'MMK') }}
                                            </span>
                                        </div>
                                        <TrendIcon :current="entry.price" :previous="historyData[index + 1]?.price"
                                            :show-amount="true" class="scale-90" />
                                    </div>

                                    <!-- SGD Price for World Gold -->
                                    <div v-if="selectedType === 'world_oz' && entry.sgd_price"
                                        class="mt-1.5 pt-1 border-t border-slate-100 dark:border-zinc-700">
                                        <div class="flex items-center justify-end gap-1">
                                            <span class="text-[9px] font-bold text-blue-600/60">S$</span>
                                            <span class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400">
                                                {{ $formatMoney(entry.sgd_price, 2) }}
                                            </span>
                                            <TrendIcon :current="entry.sgd_price"
                                                :previous="historyData[index + 1]?.sgd_price" class="scale-75" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Change Column -->
                                <div class="text-right hidden sm:block">
                                    <span v-if="index < historyData.length - 1"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[9px] font-black border"
                                        :class="getBadgeClass(getRowChange(entry.price, historyData[index + 1]?.price).dir)">
                                        <span>{{ getRowChange(entry.price, historyData[index + 1]?.price).dir === 'up' ?
                                            '▲' : getRowChange(entry.price, historyData[index + 1]?.price).dir ===
                                                'down' ? '▼' : '—' }}</span>
                                        {{ getRowChange(entry.price, historyData[index + 1]?.price).text }}
                                    </span>
                                    <span v-else class="text-slate-300 dark:text-zinc-700 text-[9px]">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="history.last_page > 1"
                            class="flex flex-col sm:flex-row justify-between items-center gap-4 px-6 py-4 bg-slate-50/50 dark:bg-zinc-800/20 border-t border-slate-200 dark:border-zinc-800">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500">
                                {{ history.total }} records · Page {{ history.current_page }} of {{ history.last_page }}
                            </span>
                            <div class="flex gap-1.5 flex-wrap justify-center">
                                <Link v-for="link in history.links" :key="link.label" :href="link.url || '#'"
                                    class="min-w-[32px] h-7 flex items-center justify-center px-2 text-[10px] font-black rounded-lg transition-all"
                                    :class="link.active
                                        ? 'bg-slate-800 text-white border-slate-800 dark:bg-white dark:text-zinc-900'
                                        : 'bg-white text-slate-500 border-slate-200 dark:bg-zinc-900 dark:text-zinc-400 dark:border-zinc-800 hover:border-slate-400 dark:hover:border-zinc-600',
                                        !link.url && 'opacity-30 pointer-events-none'" v-html="link.label" />
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-20 text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-zinc-800 mb-4">
                            <svg class="w-8 h-8 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 dark:text-zinc-500 font-medium">No price history available</p>
                        <p class="text-[10px] text-slate-400 dark:text-zinc-600 mt-1">Data will appear after the first
                            price sync</p>
                    </div>
                </div>

                <!-- Info Footer -->
                <div
                    class="text-center text-[10px] text-slate-400 dark:text-zinc-600 px-4 py-3 bg-slate-50 dark:bg-zinc-800/30 rounded-xl">
                    <p v-if="isWorldGold">
                        World gold prices sourced from Yahoo Finance · Real-time updates every 2 minutes
                    </p>
                    <p v-else>
                        Local gold prices based on market rates · Updated daily with bank averages
                    </p>
                </div>

            </main>
        </div>
    </GuestLayout>
</template>

<script setup>
import UserGoldChart from '@/Components/Charts/UserGoldChart.vue'
import TrendIcon from '@/Components/TrendIcon.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
    history: { type: Object, required: true },
    selectedType: { type: String, required: true },
    breadcrumbs: { type: Array, default: () => [] },
    stats: Object,
    latestPrice: Number,
    latestSgdPrice: { type: Number, default: 0 }, // Add this
})

// ── Config ───────────────────────────────────────────
const tabs = [
    { value: 'new_system', label: 'New System', color: 'emerald' },
    { value: 'traditional', label: 'Traditional', color: 'amber' },
    { value: 'world_oz', label: 'World Spot', color: 'blue' },
]

const typeMap = {
    world_oz: {
        title: 'World Gold Spot',
        description: 'USD & SGD / Troy oz · 31.1 g',
        currency: 'USD',
        symbol: '$',
        secondaryCurrency: 'SGD',
        secondarySymbol: 'S$',
        colorClass: 'blue',
        chartColor: '#2563EB',
    },
    new_system: {
        title: 'New System Gold',
        description: 'MMK / Kyatthar · 16.329 g',
        currency: 'MMK',
        symbol: '',
        colorClass: 'emerald',
        chartColor: '#10B981'
    },
    traditional: {
        title: 'Traditional Gold',
        description: 'MMK / Kyatthar · 16.606 g',
        currency: 'MMK',
        symbol: '',
        colorClass: 'amber',
        chartColor: '#F59E0B'
    },
}

const pageInfo = computed(() => typeMap[props.selectedType] ?? { title: 'Gold History', description: '', currency: '', symbol: '', colorClass: '', chartColor: '#888' })
const isWorldGold = computed(() => props.selectedType === 'world_oz')
const historyData = computed(() => props.history?.data ?? [])

// ── Helpers ──────────────────────────────────────────
const formatDate = (s) => s ? new Date(s).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatTime = (s) => s && s.length > 10 ? new Date(s).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false }) : ''

const getRowChange = (current, previous) => {
    if (previous == null) return { text: '—', dir: '' }
    const c = Number(current), p = Number(previous)
    if (!p) return { text: '—', dir: '' }
    const pct = (c - p) / p * 100
    return { text: `${pct > 0 ? '+' : ''}${pct.toFixed(2)}%`, dir: pct > 0 ? 'up' : pct < 0 ? 'down' : 'neutral' }
}

const getTextColor = (color) => {
    const map = {
        emerald: 'text-emerald-600 dark:text-emerald-400',
        blue: 'text-blue-600 dark:text-blue-400',
        amber: 'text-amber-600 dark:text-amber-500'
    }
    return map[color] || 'text-slate-900 dark:text-white'
}

const getPillClass = (trend) => {
    if (trend === 'up') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400'
    if (trend === 'down') return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-400'
    return 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400'
}

const getBadgeClass = (dir) => {
    if (dir === 'up') return 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-800'
    if (dir === 'down') return 'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-800'
    return 'bg-slate-100 text-slate-400 border-slate-100 dark:bg-zinc-800 dark:text-zinc-500 dark:border-zinc-700'
}

onMounted(() => { })
onUnmounted(() => { })
</script>