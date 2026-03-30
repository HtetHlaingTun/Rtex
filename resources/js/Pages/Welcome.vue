<template>
    <GuestLayout>

        <Head title="Live Market Rates" />

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-[#111] dark:text-zinc-100 transition-colors duration-300">
            <!-- Page Header -->
            <header class="sticky top-[64px] z-40 w-full transition-all duration-300 border-b" :class="[
                isScrolled
                    ? 'py-2 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-slate-200/80 dark:border-zinc-800'
                    : 'py-4 sm:py-6 bg-white/60 dark:bg-zinc-950/60 backdrop-blur-xl border-slate-200/50 dark:border-zinc-800/50'
            ]">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between px-2">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center gap-2">
                                <h1 :class="isScrolled ? 'text-lg' : 'text-xl sm:text-2xl'"
                                    class="font-sans tracking-tight text-slate-900 dark:text-white transition-all duration-300">
                                    Market Rates
                                </h1>
                                <span
                                    class="hidden sm:inline-block px-1.5 py-0.5 rounded text-[9px] font-mono uppercase tracking-wider bg-orange-100 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400">
                                    Live
                                </span>
                            </div>
                            <p
                                class="text-[10px] sm:text-xs font-monobold text-slate-400 tabular-nums uppercase tracking-wide flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Last Update: {{ lastRefreshedTime }}
                            </p>
                        </div>

                        <div
                            class="group flex items-center gap-3 pl-2 pr-4 py-1 bg-white dark:bg-zinc-900 shadow-sm border-slate-200 dark:border-zinc-800">
                            <div class="flex -space-x-1">
                                <div
                                    class="w-5 h-5 rounded-full border-2 border-white dark:border-zinc-900 bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-[10px]">
                                    🇺🇸
                                </div>
                                <div
                                    class="w-5 h-5 rounded-full border-2 border-white dark:border-zinc-900 bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-[10px]">
                                    🇲🇲
                                </div>
                            </div>

                            <div class="h-4 w-[1px] bg-slate-200 dark:border-zinc-800"></div>

                            <div class="flex items-center gap-2.5 px-3 py-1 transition-all duration-500 shadow-sm"
                                :class="isOnline
                                    ? 'bg-emerald-50/40 border-emerald-100 dark:bg-emerald-500/5 dark:border-emerald-500/20'
                                    : 'bg-rose-50/40 border-rose-100 dark:bg-rose-500/5 dark:border-rose-500/20'
                                    ">
                                <div class="relative flex h-2 w-2 flex-shrink-0">
                                    <span v-if="isOnline"
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2"
                                        :class="isOnline ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                </div>

                                <span
                                    class="hidden sm:block text-[10px] font-mono uppercase tracking-wider leading-none"
                                    :class="isOnline
                                        ? 'text-emerald-700 dark:text-emerald-400'
                                        : 'text-rose-600 dark:text-rose-400'
                                        ">
                                    {{ isOnline ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="max-w-[960px] mx-auto px-6 pb-20 pt-8 flex flex-col gap-10">
                <!-- Gold Prices -->
                <section v-if="gold" class="mt-8">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h2
                            class="text-[11px] font-bold tracking-[0.12em] uppercase text-slate-400 dark:text-zinc-500 flex items-center gap-2">
                            <span class="w-1.5 h-3 bg-amber-500 rounded-full"></span>
                            Gold Market
                        </h2>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800 rounded-3xl overflow-hidden">
                        <div
                            class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50/50 dark:bg-zinc-800/30 border-b border-slate-100 dark:border-zinc-800">
                            <div class="col-span-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Market System</div>
                            <div
                                class="col-span-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                Price (MMK)</div>
                            <div
                                class="col-span-3 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                1 Pe (၁ ပဲ)</div>
                            <div
                                class="col-span-2 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                USD Equiv.</div>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <Link :href="route('public.gold.history', { type: 'new_system' })"
                                class="group grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-5 items-center hover:bg-amber-500/[0.02] transition-colors">

                                <div class="col-span-4 flex items-center gap-4">
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-amber-600 transition-colors">
                                            New System</h4>
                                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-tight">16.329g
                                            / ကျပ်သား</p>
                                    </div>
                                </div>

                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">Price</span>

                                    <div class="flex flex-col md:items-end items-end">
                                        <span
                                            class="text-[9px] font-black font-sans text-amber-600/60 dark:text-amber-500/40 uppercase tracking-tighter mb-0.5">
                                            MMK
                                        </span>

                                        <span
                                            class="text-xl font-mono font-black text-slate-900 dark:text-white tabular-nums leading-none">
                                            {{ $formatMoney(gold.mmk_price_new) }}
                                        </span>

                                        <div class="mt-1.5 flex items-center gap-1 opacity-80">
                                            <TrendIcon :current="gold.mmk_price_new" :previous="gold.prev_mmk_price_new"
                                                class="scale-90" />

                                        </div>
                                    </div>
                                </div>

                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">1 Pe</span>
                                    <span class="text-base font-mono font-bold text-amber-600 tabular-nums">
                                        {{ $formatMoney(gold.mmk_price_new / 16) }}
                                    </span>
                                </div>

                                <div class="col-span-2 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">USD</span>
                                    <span class="text-sm font-mono text-slate-500 tabular-nums">
                                        ${{ $formatDecimal(calculateUsdPrice(gold.mmk_price_new, gold.usd_mmk_rate)) }}
                                    </span>
                                </div>
                            </Link>

                            <Link :href="route('public.gold.history', { type: 'traditional' })"
                                class="group grid grid-cols-1 md:grid-cols-12 gap-4 px-6 py-5 items-center hover:bg-indigo-500/[0.02] transition-colors">

                                <div class="col-span-4 flex items-center gap-4">
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 transition-colors">
                                            Traditional</h4>
                                        <p class="text-[10px] font-mono text-slate-400 uppercase tracking-tight">16.606g
                                            / ကျပ်သား</p>
                                    </div>
                                </div>

                                <div class="col-span-3 md:text-right flex md:block justify-between items-center">
                                    <span class="md:hidden text-[9px] font-bold text-slate-400 uppercase">Price</span>

                                    <div class="flex flex-col md:items-end items-end">
                                        <span
                                            class="text-[9px] font-sans font-black text-amber-600/60 dark:text-amber-500/40 uppercase tracking-tighter mb-0.5">
                                            MMK
                                        </span>

                                        <span
                                            class="text-xl font-mono font-black text-slate-900 dark:text-white tabular-nums leading-none">
                                            {{ $formatMoney(gold.mmk_price_old) }}
                                        </span>

                                        <div class="mt-1.5 flex items-center gap-1 opacity-80">
                                            <TrendIcon :current="gold.mmk_price_old" :previous="gold.prev_mmk_price_old"
                                                class="scale-90" />

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

                        <div class="bg-emerald-500/5 dark:bg-emerald-500/10 px-6 py-4 border-t border-emerald-500/20">
                            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </div>
                                    <span
                                        class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">World
                                        Reference</span>
                                </div>

                                <div
                                    class="flex flex-wrap justify-center md:justify-end items-center gap-x-6 gap-y-2 md:gap-8 font-mono text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 uppercase text-[9px] font-bold">Spot</span>
                                        <span class="text-emerald-600 font-bold">${{
                                            $formatDecimal(gold.world_gold_price) }} <span
                                                class="text-[9px] opacity-60">/oz</span></span>
                                    </div>

                                    <div class="hidden sm:block w-px h-3 bg-slate-200 dark:bg-zinc-700"></div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-400 uppercase text-[9px] font-bold">Gram</span>
                                        <span class="text-emerald-600 font-bold">${{
                                            $formatDecimal(gold.world_gold_price / 31.1035) }} <span
                                                class="text-[9px] opacity-60">/g</span></span>
                                    </div>

                                    <div class="hidden sm:block w-px h-3 bg-slate-200 dark:bg-zinc-700"></div>

                                    <!-- <div class="flex items-center gap-2">
                                        <span class="text-slate-400 uppercase text-[9px] font-bold">FX Rate</span>
                                        <span class="text-slate-700 dark:text-zinc-300 font-bold">
                                            {{ $formatDecimal(gold.usd_mmk_rate) }} <span class="text-[9px]">MMK</span>
                                        </span>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Exchange Rates -->
                <section class="mt-8">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <h2
                            class="text-[11px] font-mono tracking-[0.12em] uppercase text-slate-400 dark:text-zinc-500 flex items-center gap-2">
                            <span class="w-1.5 h-3 bg-orange-500 rounded-full"></span>
                            Exchange Rates
                        </h2>

                    </div>

                    <div v-if="rates && rates.length > 0">
                        <div
                            class="hidden sm:grid grid-cols-[2.5fr_1.2fr_1.2fr_1fr] px-6 pb-3 text-[10px] font-mono tracking-widest uppercase text-slate-400">
                            <span>Market / Time</span>
                            <span class="text-right">Buy (MMK)</span>
                            <span class="text-right">Sell (MMK)</span>
                            <span class="text-right">24h Change</span>
                        </div>

                        <div class="space-y-2">
                            <Link v-for="rate in rates" :key="rate.id"
                                :href="route('history', rate.currency?.id || rate.id)"
                                class="group block bg-white dark:bg-zinc-900 border border-slate-200/60 dark:border-zinc-800 rounded-xl overflow-hidden hover:border-orange-500/30 hover:shadow-md transition-all duration-300">
                                <div
                                    class="grid grid-cols-2 sm:grid-cols-[2.5fr_1.2fr_1.2fr_1fr] items-center p-4 sm:p-5 gap-y-4 sm:gap-0">

                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-zinc-800 flex items-center justify-center text-xs font-mono text-slate-400">
                                            {{ rate.currency?.code?.substring(0, 2) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-2">
                                                <span class="text-base font-monok text-slate-900 dark:text-white">{{
                                                    rate.currency?.code }}</span>
                                                <span
                                                    class="hidden md:inline text-[10px] font-mono text-slate-400 uppercase tracking-tighter">{{
                                                        rate.currency?.name }}</span>
                                            </div>
                                            <span class="text-[10px] font-monobold text-slate-400 tabular-nums">
                                                {{ $formatShortDate(rate.created_at) }} • {{
                                                    $formatTime24(rate.created_at) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex justify-end sm:order-last">
                                        <span v-if="rate.change_percentage"
                                            class="inline-flex items-center gap-1 text-[11px] font-mono px-2.5 py-1 rounded-lg transition-colors"
                                            :class="rate.market_trend === 'up'
                                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                                : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'
                                                ">
                                            <span class="text-[8px]">{{ rate.market_trend === 'up' ? '▲' : '▼' }}</span>
                                            {{ Math.abs(rate.change_percentage).toFixed(2) }}%
                                        </span>
                                        <span v-else class="text-[11px] font-mono text-slate-300">—</span>
                                    </div>

                                    <div
                                        class="flex flex-col sm:items-end border-t sm:border-t-0 border-slate-50 dark:border-zinc-800 pt-3 sm:pt-0">
                                        <span class="text-[9px] font-mono text-slate-400 uppercase sm:hidden mb-1">Buy
                                            Rate</span>
                                        <span
                                            class="text-sm font-monok tabular-nums text-emerald-600 dark:text-emerald-400">
                                            {{ $formatMoney(rate.buy_rate) }}
                                        </span>
                                        <TrendIcon :current="rate.buy_rate" :previous="rate.prev_buy_rate"
                                            class="scale-90 origin-right" />
                                    </div>

                                    <div
                                        class="flex flex-col items-end border-t sm:border-t-0 border-slate-50 dark:border-zinc-800 pt-3 sm:pt-0">
                                        <span class="text-[9px] font-mono text-slate-400 uppercase sm:hidden mb-1">Sell
                                            Rate</span>
                                        <span class="text-sm font-monok tabular-nums text-rose-600 dark:text-rose-400">
                                            {{ $formatMoney(rate.sell_rate) }}
                                        </span>
                                        <TrendIcon :current="rate.sell_rate" :previous="rate.prev_sell_rate"
                                            class="scale-90 origin-right" />
                                    </div>

                                </div>
                            </Link>
                        </div>
                    </div>

                    <div v-else
                        class="bg-white dark:bg-zinc-900 border border-dashed border-slate-200 dark:border-zinc-800 rounded-2xl p-16 text-center">
                        <div
                            class="w-12 h-12 bg-slate-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-mono text-slate-400 uppercase tracking-widest">No market data available
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