<template>
    <GuestLayout>

        <Head title="Live Market Rates" />



        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-[#111] dark:text-zinc-100 transition-colors duration-300">
            <!-- Page Header -->


            <main class="max-w-[960px] mx-auto px-6 pb-20 pt-8 flex flex-col gap-10">



                <!-- Gold Prices -->
                <section v-if="gold" class="mt-8">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h2
                            class="text-[11px] font-black tracking-[0.15em] uppercase text-slate-400 dark:text-zinc-500 flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                            Gold Market
                        </h2>

                        <!-- NEW: mini badge -->
                        <span class="text-[9px] font-mono text-slate-400 uppercase">
                            Live
                        </span>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800 rounded-3xl overflow-hidden">


                        <!-- WORLD MARKET -->
                        <div class="bg-white dark:bg-zinc-900 px-6 py-6 border-t border-slate-100 dark:border-zinc-800">
                            <div class="flex flex-col gap-5">

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">

                                        <span
                                            class="text-[10px] font-black text-slate-500 dark:text-zinc-400 uppercase tracking-[0.2em]">
                                            Global Market Reference
                                        </span>
                                    </div>

                                </div>

                                <div class="flex flex-col gap-2">

                                    <div
                                        class="grid grid-cols-2 gap-4 p-3 rounded-2xl bg-slate-50/50 dark:bg-zinc-800/30 border border-slate-100 dark:border-zinc-800/50">

                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase mb-1">
                                                USD Spot / oz
                                            </span>

                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-lg font-mono font-black text-slate-900 dark:text-white leading-none">
                                                    ${{ $formatDecimal(gold.world_gold_price) }}
                                                </span>

                                                <TrendIcon :current="gold.world_gold_price"
                                                    :previous="gold.prev_world_gold_price" />
                                            </div>
                                        </div>


                                        <div class="flex flex-col border-l border-slate-200 dark:border-zinc-700 pl-4">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase mb-1">USD /
                                                Gram</span>

                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-sm font-mono font-bold text-slate-600 dark:text-zinc-400">
                                                    ${{ $formatDecimal(gold.world_gold_price / 31.1035) }}
                                                </span>

                                                <TrendIcon :current="gold.world_gold_price / 31.1035"
                                                    :previous="gold.prev_world_gold_price / 31.1035" />
                                            </div>


                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-2 gap-4 p-3 rounded-2xl bg-blue-50/30 dark:bg-blue-500/5 border border-blue-100/50 dark:border-blue-500/10">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-blue-500 uppercase mb-1">SGD Spot /
                                                oz</span>


                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-lg font-mono font-black text-blue-600 dark:text-blue-400 leading-none">
                                                    ${{ $formatDecimal(gold.sgd_price) }}
                                                </span>

                                                <TrendIcon :current="gold.sgd_price" :previous="gold.prev_sgd_price" />
                                            </div>


                                        </div>
                                        <div
                                            class="flex flex-col border-l border-blue-100 dark:border-blue-500/20 pl-4">
                                            <span class="text-[9px] font-bold text-blue-500 uppercase mb-1">SGD /
                                                Gram</span>

                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                                                    ${{ $formatDecimal(gold.sgd_price / 31.1035) }}
                                                </span>

                                                <TrendIcon :current="gold.sgd_price / 31.1035"
                                                    :previous="gold.prev_sgd_price / 31.1035" />
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <!-- TABLE HEADER -->
                        <div
                            class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50/50 dark:bg-zinc-800/30 border-b border-slate-100 dark:border-zinc-800">
                            <div class="col-span-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Local Market System</div>
                            <div
                                class="col-span-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Price</div>
                            <div
                                class="col-span-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                1 Pe</div>
                            <div
                                class="col-span-2 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                USD</div>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">

                            <!-- NEW SYSTEM -->
                            <Link :href="route('public.gold.history', { type: 'new_system' })" class="group grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-5 items-center
                hover:bg-amber-500/[0.03] transition-all duration-500 hover:scale-[1.01]">

                                <!-- LEFT -->
                                <div class="col-span-4 flex items-center gap-4">
                                    <div>
                                        <h4
                                            class="text-sm font-black text-slate-900 dark:text-white group-hover:text-amber-600 transition-colors">
                                            New System
                                        </h4>
                                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-tight">
                                            16.329g / ကျပ်သား
                                        </p>
                                    </div>
                                </div>

                                <!-- PRICE -->
                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">Price</span>

                                    <div class="flex flex-col md:items-end items-end">
                                        <span
                                            class="text-[9px] font-black text-amber-600/60 uppercase tracking-tighter mb-0.5">
                                            MMK
                                        </span>

                                        <span :class="[
                                            'text-xl font-mono font-black tabular-nums leading-none transition-all duration-500',
                                            'text-slate-900 dark:text-white',
                                            gold.mmk_price_new > gold.prev_mmk_price_new ? 'scale-105 text-emerald-500' : ''
                                        ]">
                                            {{ $formatMoney(gold.mmk_price_new) }}
                                        </span>

                                        <div class="mt-1.5 flex items-center gap-1 opacity-80">
                                            <TrendIcon :current="gold.mmk_price_new"
                                                :previous="gold.prev_mmk_price_new" />
                                        </div>
                                    </div>
                                </div>

                                <!-- 1 PE -->
                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">1 Pe</span>

                                    <span class="text-base font-mono font-bold text-amber-600 tabular-nums">
                                        {{ $formatMoney(gold.mmk_price_new / 16) }}
                                    </span>
                                </div>

                                <!-- USD -->
                                <div class="col-span-2 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">USD</span>

                                    <span class="text-sm font-mono text-slate-500 tabular-nums">
                                        ${{ $formatDecimal(calculateUsdPrice(gold.mmk_price_new, gold.usd_mmk_rate)) }}
                                    </span>
                                </div>
                            </Link>

                            <!-- TRADITIONAL -->
                            <Link :href="route('public.gold.history', { type: 'traditional' })" class="group grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-5 items-center
                hover:bg-indigo-500/[0.03] transition-all duration-500 hover:scale-[1.01]">

                                <div class="col-span-4 flex items-center gap-4">
                                    <div>
                                        <h4
                                            class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">
                                            Traditional
                                        </h4>
                                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-tight">
                                            16.606g / ကျပ်သား
                                        </p>
                                    </div>
                                </div>

                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">Price</span>

                                    <div class="flex flex-col md:items-end items-end">
                                        <span
                                            class="text-[9px] font-black text-amber-600/60 uppercase tracking-tighter mb-0.5">
                                            MMK
                                        </span>

                                        <span :class="[
                                            'text-xl font-mono font-black tabular-nums leading-none transition-all duration-500',
                                            'text-slate-900 dark:text-white',
                                            gold.mmk_price_old > gold.prev_mmk_price_old ? 'scale-105 text-emerald-500' : ''
                                        ]">
                                            {{ $formatMoney(gold.mmk_price_old) }}
                                        </span>

                                        <div class="mt-1.5 flex items-center gap-1 opacity-80">
                                            <TrendIcon :current="gold.mmk_price_old"
                                                :previous="gold.prev_mmk_price_old" />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">1 Pe</span>

                                    <span class="text-base font-mono font-bold text-indigo-600 tabular-nums">
                                        {{ $formatMoney(gold.mmk_price_old / 16) }}
                                    </span>
                                </div>

                                <div class="col-span-2 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">USD</span>

                                    <span class="text-sm font-mono text-slate-500 tabular-nums">
                                        ${{ $formatDecimal(calculateUsdPrice(gold.mmk_price_old, gold.usd_mmk_rate)) }}
                                    </span>
                                </div>
                            </Link>

                        </div>



                    </div>
                </section>





                <!-- Exchange Rates -->
                <section class="mt-8">

                    <!-- HEADER -->
                    <div class="flex items-center justify-between mb-6 px-1">
                        <div class="flex flex-col gap-1">
                            <h2
                                class="text-[11px] font-black tracking-[0.15em] uppercase text-slate-400 dark:text-zinc-500 flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                </span>
                                Live Exchange Rates
                            </h2>

                        </div>
                    </div>

                    <!-- DATA -->
                    <div v-if="rates && rates.length > 0">

                        <!-- TABLE HEADER -->
                        <div
                            class="hidden sm:grid grid-cols-[2.5fr_1.2fr_1.2fr_1fr] px-6 pb-4 text-[10px] font-black tracking-widest uppercase text-slate-400/70">
                            <span>Currency / Market Time</span>
                            <span class="text-right pr-4">Buy (MMK)</span>
                            <span class="text-right pr-4">Sell (MMK)</span>
                            <span class="text-right">24h Change</span>
                        </div>

                        <div class="space-y-3">

                            <Link v-for="rate in rates" :key="rate.id"
                                :href="route('history', rate.currency?.id || rate.id)" class="group block rounded-2xl overflow-hidden transition-all duration-500
                bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800
                hover:scale-[1.015] hover:-translate-y-0.5
                hover:border-orange-500/30 hover:shadow-xl hover:shadow-orange-500/5
                hover:bg-gradient-to-r hover:from-orange-500/5 hover:to-transparent">

                                <div
                                    class="grid grid-cols-2 sm:grid-cols-[2.5fr_1.2fr_1.2fr_1fr] items-center p-4 sm:p-5">

                                    <!-- LEFT -->
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-[10px] font-black text-slate-500 border-2 border-white dark:border-zinc-900 shadow-sm">
                                            {{ rate.currency?.code?.substring(0, 2) }}
                                        </div>

                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <!-- Bigger & cleaner -->
                                                <span
                                                    class="text-xl font-black text-slate-900 dark:text-white leading-tight group-hover:text-orange-500 transition-colors">
                                                    {{ rate.currency?.code }}
                                                </span>

                                                <span
                                                    class="hidden md:inline-block px-1.5 py-0.5 rounded-md bg-slate-50 dark:bg-zinc-800 text-[9px] font-bold text-slate-400 uppercase tracking-tighter border border-slate-100 dark:border-zinc-700">
                                                    {{ rate.currency?.name }}
                                                </span>
                                            </div>

                                            <span class="text-[10px] font-medium text-slate-400 tabular-nums mt-0.5">
                                                {{ $formatTime24(rate.created_at) }}
                                                <span class="mx-1 opacity-30">|</span>
                                                {{ $formatShortDate(rate.created_at) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="sm:hidden "></div>

                                    <!-- BUY -->
                                    <div
                                        class="flex flex-col sm:items-end justify-center px-4 py-2 sm:py-0 rounded-xl  border border-emerald-100/20 sm:border-none">

                                        <span
                                            class="text-[8px] font-black text-emerald-600/60 uppercase sm:hidden mb-1">
                                            Buy
                                        </span>

                                        <span :class="[
                                            'text-base font-mono font-black tabular-nums leading-none transition-all duration-500',
                                            'text-emerald-600 dark:text-emerald-400',
                                            rate.market_trend === 'up' ? 'scale-105' : ''
                                        ]">
                                            {{ $formatMoney(rate.buy_rate) }}
                                        </span>

                                        <div class="mt-1 flex items-center gap-1">
                                            <TrendIcon :current="rate.buy_rate" :previous="rate.prev_buy_rate"
                                                class="scale-100" />
                                            <span class="text-[9px] font-mono text-emerald-600/50">MMK</span>
                                        </div>
                                    </div>

                                    <!-- SELL -->
                                    <div
                                        class="flex flex-col items-end justify-center px-4 py-2 sm:py-0 rounded-xl  border border-rose-100/20 sm:border-none">

                                        <span class="text-[8px] font-black text-rose-600/60 uppercase sm:hidden mb-1">
                                            Sell
                                        </span>

                                        <span :class="[
                                            'text-base font-mono font-black tabular-nums leading-none transition-all duration-500',
                                            'text-rose-600 dark:text-rose-400',
                                            rate.market_trend === 'down' ? 'scale-105' : ''
                                        ]">
                                            {{ $formatMoney(rate.sell_rate) }}
                                        </span>

                                        <div class="mt-1 flex items-center gap-1">
                                            <TrendIcon :current="rate.sell_rate" :previous="rate.prev_sell_rate"
                                                class="scale-100" />
                                            <span class="text-[9px] font-mono text-rose-600/50">MMK</span>
                                        </div>


                                    </div>

                                    <!-- CHANGE -->
                                    <div class="sm:flex justify-end mt-4 sm:mt-0 hidden ">

                                        <div v-if="rate.change_percentage"
                                            class="inline-flex flex-col items-end justify-content-end gap-1 px-3 py-1.5 rounded-xl transition-all"
                                            :class="rate.market_trend === 'up'
                                                ? ' text-emerald-600 dark:text-emerald-400'
                                                : ' text-rose-600 dark:text-rose-400'">

                                            <div class="flex items-center gap-1">
                                                <span class="text-[8px]">
                                                    {{ rate.market_trend === 'up' ? '▲' : '▼' }}
                                                </span>
                                                <span class="text-xs font-black font-mono">
                                                    {{ Math.abs(rate.change_percentage).toFixed(2) }}%
                                                </span>
                                            </div>

                                            <!-- FIXED LABEL -->
                                            <span
                                                class="text-[8px] text-black dark:text-white font-bold uppercase opacity-80">
                                                Spread: {{ $formatMoney(rate.sell_rate - rate.buy_rate) }}
                                            </span>
                                        </div>

                                        <span v-else class="text-[11px] font-mono text-slate-300">—</span>
                                    </div>

                                </div>
                            </Link>

                        </div>
                    </div>

                    <!-- EMPTY -->
                    <div v-else
                        class="bg-white dark:bg-zinc-900 border-2 border-dashed border-slate-100 dark:border-zinc-800 rounded-[2rem] p-20 text-center">

                        <div
                            class="w-16 h-16 bg-slate-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">
                            Waiting for market pulse...
                        </p>
                    </div>

                </section>
            </main>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    laravelVersion: { type: String, default: '' },
    phpVersion: { type: String, default: '' },
    rates: { type: Array, default: () => [] },
    gold: { type: Object, default: null }
})

const isScrolled = ref(false)
const lastRefreshedTime = ref(
    new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
)

const updateTimestamp = () => {
    lastRefreshedTime.value = new Date().toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

const isOnline = ref(navigator.onLine)
const updateStatus = () => {
    isOnline.value = navigator.onLine
}

const calculateUsdPrice = (mmkPrice, usdMmkRate) => {
    if (!mmkPrice || !usdMmkRate || usdMmkRate === 0) return 0
    return mmkPrice / usdMmkRate
}

// Scroll handler for header styling
const handleScroll = () => {
    isScrolled.value = window.scrollY > 10
}

onMounted(() => {
    window.addEventListener('online', updateStatus)
    window.addEventListener('offline', updateStatus)
    window.addEventListener('scroll', handleScroll)
    handleScroll() // Initial check
})

onUnmounted(() => {
    window.removeEventListener('online', updateStatus)
    window.removeEventListener('offline', updateStatus)
    window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
/* Only custom animations and complex transitions that Tailwind can't handle */
@keyframes pulse-green {
    0% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.7;
        transform: scale(1.2);
    }

    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-pulse-green {
    animation: pulse-green 2s infinite ease-in-out;
}

/* Custom shadow for header (Tailwind's shadow utilities don't have mask-image) */
.sticky-shadow {
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
    mask-image: linear-gradient(to bottom, black 90%, transparent 100%);
}

/* Custom emerald glow for live indicator */
.bg-emerald-500 {
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
}
</style>