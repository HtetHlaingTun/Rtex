<script setup>
import { computed, onMounted, ref, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';
import Paginations from '@/Components/Layouts/Paginations.vue';


const props = defineProps({
    goldType: Object,
    history: Object,
    chartData: Array,
    todayPrice: Object,
    yesterdayPrice: Object,
    stats: Object,
    latestSnapshot: Object,
    usdRate: Object, // Add this
});



const breadcrumbs = [
    { label: 'Gold Prices', href: route('gold.index') },
    { label: props.goldType.name }
];

const priceChange = computed(() => {
    if (!props.todayPrice || !props.yesterdayPrice) return null;
    const diff = floatVal(props.todayPrice.price) - floatVal(props.yesterdayPrice.price);
    const pct = ((diff / floatVal(props.yesterdayPrice.price)) * 100).toFixed(2);
    return { amount: diff, percent: pct, isUp: diff >= 0 };
});

const floatVal = (v) => parseFloat(v) || 0;

// SVG chart path builder
const chartPath = computed(() => {
    if (!props.chartData?.length) return { line: '', area: '' };
    const pts = props.chartData;
    const minP = Math.min(...pts.map(p => p.price));
    const maxP = Math.max(...pts.map(p => p.price));
    const range = maxP - minP || 1;
    const toX = (i) => (i / (pts.length - 1)) * 800;
    const toY = (v) => 190 - ((v - minP) / range) * 170;
    const line = pts.map((p, i) => `${i === 0 ? 'M' : 'L'}${toX(i)},${toY(p.price)}`).join(' ');
    return {
        line,
        area: `${line} L${toX(pts.length - 1)},200 L0,200 Z`
    };
});





const chartColor = computed(() => priceChange.value?.isUp !== false ? '#10b981' : '#f43f5e');

onMounted(() => {

    if (window.Echo) {
        window.Echo.channel('rates-updates')
            .listen('.RateUpdated', (e) => {
                console.log('New rate received:', e);

            });
    } else {
        console.warn('Laravel Echo is not defined. Check your app.js or bootstrap.js configuration.');
    }
})
onUnmounted(() => {

    window.Echo.leave('gold-markets');
});
</script>

<template>

    <Head :title="`${goldType.name} History`" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="max-w-6xl mx-auto space-y-8 pb-16 ">

            <!-- ===== HEADER ===== -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 md:max-w-6xl md:mx-auto">
                <div class="flex items-center gap-4">
                    <!-- System badge -->
                    <div :class="goldType.system === 'old'
                        ? 'from-orange-400 to-orange-600 border-orange-300'
                        : 'from-amber-300 to-yellow-500 border-amber-200'"
                        class="w-14 h-14 rounded-2xl border-2 flex flex-col items-center justify-center shrink-0 relative overflow-hidden bg-gradient-to-br shadow-inner-soft group">

                        <div
                            class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full animate-shine group-hover:pause">
                        </div>

                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-9 h-9 text-white/90 drop-shadow-md mb-1">
                            <path d="M3 17l2-3h6l2 3H3z" opacity="0.5" />
                            <path d="M11 17l2-3h6l2 3h-10z" opacity="0.7" />
                            <path d="M6 13l2-4h8l2 4H6z" />
                            <path d="M8 10h8l.5 1h-9l.5-1z" fill="white" opacity="0.3" />
                        </svg>

                        <div
                            class="absolute bottom-0 w-full bg-black/20 backdrop-blur-[1px] py-0.5 flex justify-center">
                            <span class="text-[8px] font-black uppercase text-white tracking-widest leading-none">
                                {{ goldType.system }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 italic tracking-tight">
                            {{ goldType.name }}
                        </h1>

                    </div>

                </div>


                <!-- Live world reference -->
                <div v-if="latestSnapshot"
                    class="flex items-center gap-2 px-4 py-2 bg-slate-900 rounded-2xl text-white shrink-0">
                    <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-black text-slate-400 uppercase">World Gold</span>
                    <span class="text-sm font-mono font-black text-amber-400">
                        ${{ $formatNumber(latestSnapshot.usd_price) }}
                    </span>
                </div>

            </div>

            <div
                class="md:max-w-full sm:max-w-full xl:max-w-full xl:mx-auto sm:mx-auto md:mx-[auto] xs:max-w-[200px] text-center justify-start">
                <p class="text-[10px] xs:text-xs font-sans text-slate-400 font-bold uppercase mt-0.5 tracking-tight">
                    {{ goldType.system === 'old' ? '1 Kyatthar = 16.606g' : '1 Kyatthar = 16.329g' }}
                    · {{ goldType.purity || goldType.unit }} <br>
                    <span class="flex items-center justify-center gap-1.5 mt-1">
                        <span class="relative flex h-1.5 w-1.5">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                        </span>
                        Live Market Monitoring · Updates on Change
                    </span>
                </p>
            </div>

            <!-- ===== TODAY'S PRICE + STATS ===== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:max-w-6xl md:mx-auto">

                <!-- Today's Price -->
                <div :class="goldType.system === 'old'
                    ? 'bg-gradient-to-br from-orange-500 via-orange-600 to-red-600 border-orange-400/30 shadow-orange-900/20'
                    : 'bg-gradient-to-br from-amber-400 via-yellow-500 to-amber-600 border-amber-300/30 shadow-amber-900/20'"
                    class="col-span-2 p-7 rounded-[2.5rem] text-white border-2 relative overflow-hidden shadow-2xl transition-all duration-500 hover:scale-[1.01] group">

                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/20 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full border border-white/10">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-start justify-between mb-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-3 bg-black/40 rounded-full"></span>
                                    <span class="text-[10px] font-black text-black/80 uppercase tracking-[0.2em]">
                                        Local Market Price
                                    </span>
                                </div>

                                <div v-if="usdRate"
                                    class="flex flex-col gap-1 bg-black/10 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10 w-fit group/usd">

                                    <div class="flex items-center gap-2">
                                        <span class="text-[8px] font-black text-white/70 uppercase tracking-tighter">USD
                                            Mid Rate</span>
                                        <div v-if="$isToday(usdRate.rate_date)"
                                            class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                                    </div>

                                    <div class="flex items-baseline gap-1">
                                        <span class="text-sm font-mono font-black text-white italic leading-none">
                                            {{ $formatNumber(usdRate.mid_rate) }}
                                        </span>
                                        <span class="text-[9px] font-bold text-white/50">MMK</span>
                                    </div>

                                    <span class="text-[7px] text-white/30 font-bold uppercase">
                                        Ref: {{ $isToday(usdRate.rate_date) ? 'Market Live' :
                                            $formatDate(usdRate.rate_date) }}
                                    </span>
                                </div>
                            </div>

                            <span v-if="todayPrice?.source_type === 'auto'"
                                class="text-[8px] font-black text-black bg-white/30 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/20 uppercase tracking-tighter">
                                Live Sync
                            </span>
                        </div>

                        <div v-if="todayPrice" class="flex flex-col xs:flex-row xs:items-end gap-2 xs:gap-4">
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-3xl sm:text-4xl text-black font-mono font-black italic tracking-tighter tabular-nums drop-shadow-sm">
                                    {{ $formatNumber(todayPrice.price) }}
                                </span>
                                <span class="text-xs xs:text-sm font-black text-black/60 font-mono">MMK</span>
                            </div>

                            <span v-if="todayPrice.price_date !== $isToday()"
                                class="text-[9px] font-bold text-black/40 uppercase mb-2">
                                (Last Price: {{ $formatDate(todayPrice.price_date) }})
                            </span>
                        </div>


                        <div v-else class="py-4">
                            <div class="flex flex-col">
                                <span class="text-3xl text-black/20 font-mono font-black italic tracking-tighter">
                                    --- , ---
                                </span>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="w-2 h-2 bg-black/20 rounded-full"></div>
                                    <span class="text-xs font-black text-black/40 uppercase tracking-widest italic">
                                        Market Closed
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="yesterdayPrice" class="flex items-center gap-2 mt-6 pt-4 border-t border-black/10">
                            <p class="text-[9px] text-black/40 font-black uppercase tracking-widest">
                                Yesterday: <span class="text-black/80 font-mono">{{ $formatNumber(yesterdayPrice.price)
                                }} MMK</span>
                            </p>
                        </div>
                    </div>
                </div>



                <!-- All Time High -->
                <div class="bg-emerald-50 border border-emerald-100 rounded-[2rem] p-6">
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-3">
                        All-Time High
                    </p>
                    <p class="text-xs sm:text-xl font-mono font-black text-emerald-900 italic">
                        {{ stats.highest ? $formatNumber(stats.highest) : '—' }}
                    </p>
                    <p class="text-[9px] text-emerald-500 mt-1 uppercase font-bold">MMK</p>
                </div>

                <!-- All Time Low -->
                <div class="bg-rose-50 border border-rose-100 rounded-[2rem] p-6">
                    <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mb-3">
                        All-Time Low
                    </p>
                    <p class="text-xs sm:text-xl font-mono font-black text-rose-900 italic">
                        {{ stats.lowest ? $formatNumber(stats.lowest) : '—' }}
                    </p>
                    <p class="text-[9px] text-rose-500 mt-1 uppercase font-bold">MMK</p>
                </div>
            </div>

            <!-- ===== 30-DAY CHART ===== -->
            <div
                class="bg-white border border-slate-200 rounded-[2rem] xs:rounded-[2.5rem] p-5 xs:p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden group min-w-0">

                <div class="absolute -top-32 -right-32 w-72 h-72 rounded-full blur-[110px] opacity-[0.15] pointer-events-none"
                    :class="priceChange?.isUp !== false ? 'bg-emerald-400' : 'bg-rose-400'">
                </div>

                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8 relative z-10">
                    <div class="min-w-0">
                        <h2
                            class="text-[10px] xs:text-[11px] font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="shrink-0 w-1.5 h-4 bg-slate-900 rounded-full"></span>
                            <span class="truncate">
                                {{ chartData.length > 0 && chartData[0]?.date?.includes(':') ? 'Intraday Movement' :
                                    'Market History' }}
                            </span>
                        </h2>
                        <p
                            class="text-[9px] xs:text-[10px] text-slate-400 font-bold mt-1 font-mono uppercase whitespace-nowrap">
                            {{ chartData.length }} Snapshots <span class="mx-1 text-slate-200">|</span> MMK / Kyatthar
                        </p>
                    </div>

                    <div :class="priceChange?.isUp !== false ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-[8px] xs:text-[9px] font-black uppercase tracking-tighter transition-all duration-500 self-start sm:self-auto shadow-sm">
                        <span class="relative flex h-1.5 w-1.5 xs:h-2 xs:w-2">
                            <span :class="priceChange?.isUp !== false ? 'bg-emerald-400' : 'bg-rose-400'"
                                class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                            <span :class="priceChange?.isUp !== false ? 'bg-emerald-500' : 'bg-rose-500'"
                                class="relative inline-flex rounded-full h-1.5 w-1.5 xs:h-2 xs:w-2"></span>
                        </span>
                        {{ priceChange?.isUp !== false ? 'Bullish Trend' : 'Bearish Trend' }}
                    </div>
                </div>

                <div v-if="chartData.length > 1"
                    class="relative h-48 xs:h-64 bg-gradient-to-b from-slate-50/50 to-white rounded-2xl xs:rounded-3xl overflow-hidden border border-slate-100/80 shadow-inner-soft">

                    <div class="absolute inset-0 pointer-events-none opacity-[0.03]"
                        style="background-image: radial-gradient(#000 0.5px, transparent 0.5px); background-size: 24px 24px;">
                    </div>

                    <div class="absolute inset-0 p-2">
                        <GoldPriceChart :chart-data="chartData" :is-up="priceChange?.isUp !== false" />
                    </div>
                </div>

                <div v-else
                    class="h-48 xs:h-64 bg-slate-50/50 rounded-2xl xs:rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-3">
                    <div
                        class="w-10 h-10 xs:w-12 xs:h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 xs:w-6 xs:h-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <p
                        class="text-slate-400 text-[9px] xs:text-[10px] font-black uppercase tracking-widest italic text-center px-4">
                        Analyzing Market Data...
                    </p>
                </div>
            </div>

            <!-- ===== HISTORY TABLE ===== -->
            <div class="bg-white border border-slate-200 rounded-[2rem] overflow-hidden shadow-sm">
                <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">
                            Price History
                        </h2>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            {{ stats.total_entries }} total records
                        </p>
                    </div>
                    <!-- Legend -->
                    <div class="flex items-center gap-4 text-[9px] font-black uppercase text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full"></div>
                            Auto-sync
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                            Manual
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 font-black text-slate-400 uppercase tracking-widest">Date
                                </th>
                                <th class="text-right px-6 py-3 font-black text-slate-400 uppercase tracking-widest">
                                    Price (MMK)
                                </th>
                                <th class="text-right px-6 py-3 font-black text-slate-400 uppercase tracking-widest ">
                                    World Gold</th>
                                <th
                                    class="text-center px-6 py-3 font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">
                                    Source
                                </th>
                                <th
                                    class="text-center px-6 py-3 font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">
                                    Status
                                </th>
                                <th
                                    class="text-left px-6 py-3 font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell hidden lg:table-cell">
                                    Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="record in history.data" :key="record.id"
                                class="hover:bg-slate-50 transition-colors">

                                <!-- Date -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="hidden xs:block font-bold text-slate-700">
                                            {{ $formatDate(record.price_date) }}
                                        </span>

                                        <span class="block xs:hidden font-bold text-slate-700 tabular-nums">
                                            {{ $formatShortDate(record.price_date) }}
                                        </span>
                                    </div>
                                    <span class="xs:block hidden text-[9px] text-slate-400 font-medium mt-0.5  ">
                                        {{ record.created_at ? $formatTimeOnly(record.created_at) : '' }}
                                    </span>
                                    <span class="block xs:hidden  text-[9px] text-slate-400 font-medium mt-0.5  ">
                                        {{ record.created_at ? $formatTime24(record.created_at) : '' }}
                                    </span>

                                    <span v-if="$isToday(record.price_date)"
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-500/10 text-emerald-600 rounded text-[7px] font-black uppercase tracking-tighter border border-emerald-500/20">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span
                                                class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                        </span>
                                        Live
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-mono font-black text-slate-900 text-xs">
                                            {{ $formatNumber(record.price) }}
                                        </span>

                                        <div class="flex items-center justify-end gap-1 mt-0.5">
                                            <TrendIcon :current="record.price" :previous="record.previous_price" />

                                            <span
                                                :class="record.trend === 'up' ? 'text-emerald-600' : record.trend === 'down' ? 'text-rose-600' : 'text-slate-400'"
                                                class="text-[9px] font-black tabular-nums flex flex-col items-end leading-tight">

                                                <span>
                                                    {{ record.price - record.previous_price > 0 ? '+' : '' }}{{
                                                        $formatNumber(record.price - record.previous_price) }}
                                                </span>

                                                <span v-if="record.change_percentage" class="opacity-80">
                                                    ({{ Math.abs(parseFloat(record.change_percentage)).toFixed(2) }}%)
                                                </span>
                                            </span>


                                        </div>
                                    </div>
                                </td>

                                <!-- World Gold USD -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-slate-500 font-mono font-bold text-xs">
                                            {{ record.world_gold_usd ? '$' + $formatNumber(record.world_gold_usd) : '—'
                                            }}
                                        </span>

                                        <div v-if="record.world_gold_change !== null"
                                            class="flex items-center justify-end gap-1 mt-0.5">

                                            <TrendIcon :current="$formatNumber(record.world_gold_change)"
                                                :previous="0" />

                                            <span :class="{
                                                'text-emerald-600': $formatNumber(record.world_gold_change) > 0,
                                                'text-rose-600': $formatNumber(record.world_gold_change) < 0,
                                                'text-slate-400': $formatNumber(record.world_gold_change) === 0
                                            }"
                                                class="text-[9px] font-black tabular-nums flex flex-col items-end leading-tight">

                                                <span>
                                                    {{ $formatNumber(record.world_gold_change) > 0 ? '+' : '' }}{{
                                                        record.world_gold_change }}
                                                </span>

                                                <span v-if="record.world_gold_change_percent" class="opacity-80">
                                                    ({{
                                                        Math.abs(parseFloat(record.world_gold_change_percent)).toFixed(2)
                                                    }}%)
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Source -->
                                <td class="px-6 py-4 text-center hidden md:table-cell">
                                    <span v-if="record.source_type === 'auto'"
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-full text-[8px] font-black uppercase">
                                        <div class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></div>
                                        Auto
                                    </span>
                                    <span v-else
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-full text-[8px] font-black uppercase">
                                        Manual
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 text-center hidden md:table-cell">
                                    <span :class="record.status === 'verified'
                                        ? 'bg-emerald-50 border-emerald-100 text-emerald-700'
                                        : 'bg-amber-50 border-amber-100 text-amber-700'"
                                        class="inline-block px-2 py-1 border rounded-full text-[8px] font-black uppercase">
                                        {{ record.status }}
                                    </span>
                                </td>

                                <!-- Notes -->
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <span class="text-slate-400 italic text-[10px] line-clamp-1">
                                        {{ record.market_notes || '—' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>


            </div>

            <!-- Pagination -->
            <div class="border-t border-slate-100 xs:max-w-2xl xs:mx-auto md:max-w-full md:mx-auto">
                <div class="px-6 pt-0 flex flex-col flex-row items-center justify-between">
                    <p class="text-[10px] text-slate-400 font-medium pt-4 hidden md:block">
                        Showing {{ history.from }}–{{ history.to }} of {{ history.total }} records
                    </p>
                    <Paginations :links="history.links" />
                </div>
            </div>



        </div>
    </AdminLayout>
</template>
