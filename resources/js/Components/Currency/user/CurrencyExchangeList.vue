<template>
    <section class="mt-12">
        <!-- Section Header with Enhanced Styling -->
        <div class="flex items-center justify-between mb-8 px-2">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    <h2 class="text-[12px] font-black tracking-[0.2em] uppercase text-slate-500 dark:text-slate-400">
                        Foreign Exchange
                    </h2>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-black text-slate-800 dark:text-slate-200">Live Exchange Rates</span>
                    <span class="text-[9px] text-slate-400 font-mono">MMK per unit</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5">Real-time rates </p>
            </div>

            <!-- Reliability Badge -->
            <div
                class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-full">
                <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                <span
                    class="text-[8px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-tighter">Live
                </span>
            </div>
        </div>


        <!-- Exchange Rates Table -->
        <div v-if="rates && rates.length > 0">
            <!-- Desktop Table Header -->
            <div
                class="hidden sm:grid grid-cols-[2.5fr_1.2fr_1.2fr_1fr] px-6 py-3 mb-2 bg-slate-50 dark:bg-zinc-800/50 rounded-xl text-[10px] font-black tracking-widest uppercase text-slate-500 dark:text-slate-400">
                <span>Currency / Market Time</span>
                <span class="text-right pr-4">Buy (MMK)</span>
                <span class="text-right pr-4">Sell (MMK)</span>
                <span class="text-right">24h Change</span>
            </div>

            <div class="space-y-3">
                <Link v-for="rate in rates" :key="rate.id" :href="route('history', rate.currency?.id || rate.id)"
                    class="group block rounded-2xl transition-all duration-300
            bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800
            hover:border-orange-300 dark:hover:border-orange-700 hover:shadow-xl hover:shadow-orange-500/10 hover:-translate-y-0.5">

                    <!-- Desktop Layout (sm and up) -->
                    <div class="hidden sm:grid sm:grid-cols-[2.5fr_1.2fr_1.2fr_1fr] items-center p-5">
                        <!-- LEFT: Currency Info -->
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center text-sm font-black text-slate-600 dark:text-slate-300 shadow-md">
                                    {{ rate.currency?.code?.substring(0, 2) }}
                                </div>
                                <div
                                    class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xl font-black text-slate-900 dark:text-white group-hover:text-orange-500 transition-colors">
                                        {{ rate.currency?.code }}
                                    </span>
                                    <span
                                        class="hidden md:inline-block px-2 py-0.5 rounded-md bg-slate-100 dark:bg-zinc-800 text-[9px] font-bold text-slate-500 uppercase tracking-tighter">
                                        {{ rate.currency?.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 mt-1">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-[9px] font-mono text-slate-400">
                                        {{ $formatTime24(rate.created_at) }} · {{ $formatShortDate(rate.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- BUY Rate -->
                        <div class="flex flex-col items-end">
                            <div class="flex flex-col items-end">
                                <span class="text-xl font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $formatMoney(rate.buy_rate) }}
                                </span>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <TrendIcon :current="rate.buy_rate" :previous="rate.prev_buy_rate"
                                        class="scale-75" />
                                    <span class="text-[8px] text-slate-400">MMK</span>
                                </div>
                            </div>
                        </div>

                        <!-- SELL Rate -->
                        <div class="flex flex-col items-end">
                            <div class="flex flex-col items-end">
                                <span class="text-xl font-mono font-black text-rose-600 dark:text-rose-400">
                                    {{ $formatMoney(rate.sell_rate) }}
                                </span>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <TrendIcon :current="rate.sell_rate" :previous="rate.prev_sell_rate"
                                        class="scale-75" />
                                    <span class="text-[8px] text-slate-400">MMK</span>
                                </div>
                            </div>
                        </div>

                        <!-- CHANGE & SPREAD -->
                        <div class="flex justify-end">
                            <div v-if="rate.change_percentage" class="flex flex-col items-end">
                                <div :class="`inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black ${rate.market_trend === 'up'
                                    ? 'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    : 'text-rose-600 bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400'
                                    }`">
                                    <span>{{ rate.market_trend === 'up' ? '▲' : '▼' }}</span>
                                    <span>{{ Math.abs(rate.change_percentage).toFixed(2) }}%</span>
                                </div>
                                <div class="mt-1 flex items-center gap-1 text-[9px] text-slate-400">
                                    <span class="font-black">Spread:</span>
                                    <span class="font-mono font-bold">{{ $formatMoney(rate.sell_rate - rate.buy_rate)
                                        }}</span>
                                    <span>MMK</span>
                                </div>
                            </div>
                            <span v-else class="text-[11px] font-mono text-slate-400">No data</span>
                        </div>
                    </div>

                    <!-- Mobile Layout (sm and below) - Currency Name on its own row -->
                    <div class="sm:hidden p-4 space-y-3">
                        <!-- Currency Header Row -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center text-sm font-black text-slate-600 dark:text-slate-300 shadow-sm">
                                    {{ rate.currency?.code?.substring(0, 2) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-base font-black text-slate-900 dark:text-white">
                                            {{ rate.currency?.code }}
                                        </span>
                                        <span class="text-[8px] font-medium text-slate-500 uppercase">
                                            {{ rate.currency?.name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-[8px] text-slate-400">
                                            {{ $formatTime24(rate.created_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div :class="`px-2 py-1 rounded-lg text-[9px] font-bold ${rate.market_trend === 'up' ? 'bg-emerald-50 text-emerald-600' :
                                rate.market_trend === 'down' ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-500'
                                }`">
                                {{ rate.market_trend === 'up' ? '▲' : rate.market_trend === 'down' ? '▼' : '—' }}
                                {{ Math.abs(rate.change_percentage || 0).toFixed(1) }}%
                            </div>
                        </div>

                        <!-- Buy & Sell Row -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-emerald-50/40 dark:bg-emerald-950/20 rounded-xl p-2.5 text-center">
                                <div class="text-[8px] font-bold text-emerald-600 uppercase tracking-wider">Buy</div>
                                <div class="text-base font-mono font-black text-emerald-700 dark:text-emerald-400">
                                    {{ $formatMoney(rate.buy_rate) }}
                                </div>
                                <div class="flex items-center justify-center gap-0.5 mt-0.5">
                                    <TrendIcon :current="rate.buy_rate" :previous="rate.prev_buy_rate"
                                        class="scale-75" />
                                    <span class="text-[7px] text-slate-400">MMK</span>
                                </div>
                            </div>
                            <div class="bg-rose-50/40 dark:bg-rose-950/20 rounded-xl p-2.5 text-center">
                                <div class="text-[8px] font-bold text-rose-600 uppercase tracking-wider">Sell</div>
                                <div class="text-base font-mono font-black text-rose-700 dark:text-rose-400">
                                    {{ $formatMoney(rate.sell_rate) }}
                                </div>
                                <div class="flex items-center justify-center gap-0.5 mt-0.5">
                                    <TrendIcon :current="rate.sell_rate" :previous="rate.prev_sell_rate"
                                        class="scale-75" />
                                    <span class="text-[7px] text-slate-400">MMK</span>
                                </div>
                            </div>
                        </div>

                        <!-- Spread & Details Row -->
                        <div class="flex items-center justify-between pt-1 text-[8px] text-slate-400">
                            <div class="flex items-center gap-1">
                                <span class="font-bold">Spread:</span>
                                <span class="font-mono">{{ $formatMoney(rate.sell_rate - rate.buy_rate) }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="font-bold">CBM:</span>
                                <span class="font-mono">{{ $formatMoney(rate.cbm_rate) }}</span>
                            </div>
                            <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Loading Skeleton -->
        <div v-else-if="loading" class="space-y-3">
            <div v-for="i in 4" :key="i"
                class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-slate-200 dark:border-zinc-800 animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>
                    <div class="flex-1">
                        <div class="h-5 bg-slate-200 dark:bg-zinc-700 rounded w-20 mb-2"></div>
                        <div class="h-3 bg-slate-200 dark:bg-zinc-700 rounded w-32"></div>
                    </div>
                    <div class="text-right">
                        <div class="h-6 bg-slate-200 dark:bg-zinc-700 rounded w-24 mb-1"></div>
                        <div class="h-3 bg-slate-200 dark:bg-zinc-700 rounded w-16"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State with Better Design -->
        <div v-else
            class="text-center py-16 bg-gradient-to-br from-slate-50 to-white dark:from-zinc-900 dark:to-zinc-800 rounded-[2rem] border border-slate-200 dark:border-zinc-800">
            <div
                class="w-20 h-20 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] mb-2">Waiting for market data</p>
            <p class="text-[10px] text-slate-400">Rates will appear after the first bank sync</p>
        </div>

        <!-- Footer Note -->
        <div
            class="mt-6 px-2 text-[8px] text-slate-400 text-center border-t border-slate-100 dark:border-zinc-800 pt-4">
            <p>Rates are calculated from bank averages with configured markup and spreads. Last updated automatically
                every 30 minutes.</p>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import TrendIcon from '@/Components/TrendIcon.vue';

const props = defineProps({
    rates: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const getLatestSyncTime = () => {
    if (!props.rates.length) return 'Pending';
    const latestRate = props.rates.reduce((latest, rate) => {
        const rateDate = new Date(rate.created_at);
        return rateDate > latest ? rateDate : latest;
    }, new Date(0));

    if (latestRate.getTime() === 0) return 'Never';
    return latestRate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
/* Custom transition for hover effects */
.group:hover .group-hover\:translate-x-0\.5 {
    transform: translateX(0.125rem);
}
</style>