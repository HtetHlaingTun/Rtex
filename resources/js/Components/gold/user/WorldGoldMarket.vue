<template>
    <Link :href="route('user.gold.history', { type: 'world_oz' })">
        <div
            class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl shadow-black/20 group transition-all duration-500 hover:shadow-2xl hover:shadow-amber-500/5">

            <!-- Animated background effects -->
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl animate-pulse-slow">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse-slower">
            </div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-amber-500/5 to-blue-500/5 rounded-full blur-3xl">
            </div>

            <!-- Faint horizontal grid lines -->
            <div class="absolute inset-0 flex flex-col justify-around pointer-events-none opacity-[0.02]">
                <div v-for="n in 6" :key="n" class="w-full h-px bg-white"></div>
            </div>

            <!-- Decorative corner accents -->
            <div class="absolute top-4 left-4 w-20 h-20 border-l-2 border-t-2 border-white/5 rounded-tl-2xl"></div>
            <div class="absolute bottom-4 right-4 w-20 h-20 border-r-2 border-b-2 border-white/5 rounded-br-2xl"></div>

            <!-- Dual Currency Header -->
            <div class="relative px-6 pt-6 pb-2 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></div>
                            <span class="text-[8px] font-black text-white/60 uppercase tracking-wider">USD</span>
                        </div>
                        <span class="text-white/30 text-xs">/</span>
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></div>
                            <span class="text-[8px] font-black text-white/60 uppercase tracking-wider">SGD</span>
                        </div>
                        <span class="text-[8px] text-white/30 ml-2">Dual Market Monitor</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 px-2 py-1 bg-white/5 rounded-full border border-white/10">
                            <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span class="text-[6px] font-black text-white/50 uppercase">Live Streaming</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dual Price Display -->
            <div class="relative grid grid-cols-1 sm:grid-cols-2 gap-6 p-6 md:p-8 pb-6">
                <!-- USD Section -->
                <div class="space-y-4 border-r border-white/10 pr-6">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white/50">USD Spot</span>
                    </div>

                    <div class="flex items-baseline gap-2">
                        <span class="font-mono text-3xl font-bold text-white/40 leading-none">$</span>
                        <span
                            class="font-mono text-4xl sm:text-5xl font-black italic text-white tracking-tighter transition-all duration-300 group-hover:scale-[1.02]"
                            :class="{ 'text-amber-400 scale-105': priceFlash }">
                            {{ formatNumber(snapshot?.usd_price ?? 0) }}
                        </span>
                        <span class="text-sm text-white/30 font-mono">/ oz</span>
                    </div>

                    <!-- USD Daily Change (vs previous day's close) -->
                    <div class="mt-2 pt-1.5 border-t border-white/20">
                        <div class="flex items-center gap-2">
                            <span :class="usdPriceUp ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[11px] font-black">
                                {{ usdPriceUp ? '▲' : '▼' }}
                            </span>
                            <span :class="usdPriceUp ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[13px] font-mono font-bold">
                                ${{ formatChangeAmount(Math.abs(usdChangeAbs)) }}
                            </span>
                            <span :class="usdPriceUp ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[11px] font-mono">
                                ({{ usdChangePct }}%)
                            </span>
                        </div>
                        <div class="text-[7px] text-white/30 mt-1">
                            vs previous close ({{ formatPreviousCloseDate(previousCloseDate) }})
                        </div>
                    </div>
                </div>

                <!-- SGD Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white/50">SGD Spot</span>
                    </div>

                    <div class="flex items-baseline gap-2">
                        <span class="font-mono text-3xl font-bold text-white/40 leading-none">S$</span>
                        <span
                            class="font-mono text-4xl sm:text-5xl font-black italic text-white tracking-tighter transition-all duration-300 group-hover:scale-[1.02]">
                            {{ formatNumber(sgdGoldPrice) }}
                        </span>
                        <span class="text-sm text-white/30 font-mono">/ oz</span>
                    </div>

                    <!-- SGD Daily Change (vs previous day's close) -->
                    <div class="mt-2 pt-1.5 border-t border-white/20">
                        <div class="flex items-center gap-2">
                            <span :class="sgdPriceUp ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[11px] font-black">
                                {{ sgdPriceUp ? '▲' : '▼' }}
                            </span>
                            <span :class="sgdPriceUp ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[13px] font-mono font-bold">
                                S${{ formatChangeAmount(Math.abs(sgdChangeAbs)) }}
                            </span>
                            <span :class="sgdPriceUp ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[11px] font-mono">
                                ({{ sgdChangePct }}%)
                            </span>
                        </div>
                        <div class="text-[7px] text-white/30 mt-1">
                            vs previous close ({{ formatPreviousCloseDate(previousCloseDate) }})
                        </div>
                    </div>
                </div>
            </div>

            <!-- USD/SGD Exchange Rate Strip -->
            <div v-if="sgdRate" class="mx-6 mb-4 px-4 py-2 bg-white/5 rounded-xl border border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-[7px] font-black text-white/50 uppercase tracking-wider">USD/SGD FX
                            Rate</span>
                        <span class="text-[10px] font-mono font-black text-blue-300">{{
                            formatNumber(sgdRate.usd_sgd_rate, 4) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[7px] text-white/40">24h:</span>
                        <span :class="sgdRate.change >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                            class="text-[8px] font-black">
                            {{ sgdRate.change >= 0 ? '▲' : '▼' }} {{ formatNumber(Math.abs(sgdRate.change), 4) }} ({{
                                formatNumber(Math.abs(sgdRate.change_percent), 2) }}%)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 px-6 pb-4">
                <div
                    class="bg-gradient-to-br from-amber-500/10 to-yellow-500/10 backdrop-blur-sm border border-amber-500/30 rounded-xl p-3">
                    <p class="text-[7px] font-black uppercase tracking-[0.12em] text-amber-400/80">Kyatthar (USD)</p>
                    <p class="font-mono text-sm font-black italic text-amber-300">${{ formatNumber(usdPerKyattharNew) }}
                    </p>
                    <p class="text-[6px] text-white/40">16.329g · New</p>
                </div>
                <div
                    class="bg-gradient-to-br from-orange-500/10 to-red-500/10 backdrop-blur-sm border border-orange-500/30 rounded-xl p-3">
                    <p class="text-[7px] font-black uppercase tracking-[0.12em] text-orange-400/80">Kyatthar (USD)</p>
                    <p class="font-mono text-sm font-black italic text-orange-300">${{ formatNumber(usdPerKyattharOld)
                    }}</p>
                    <p class="text-[6px] text-white/40">16.606g · Old</p>
                </div>
                <div
                    class="bg-gradient-to-br from-amber-500/10 to-yellow-500/10 backdrop-blur-sm border border-amber-500/30 rounded-xl p-3">
                    <p class="text-[7px] font-black uppercase tracking-[0.12em] text-amber-400/80">Kyatthar (SGD)</p>
                    <p class="font-mono text-sm font-black italic text-amber-300">S${{ formatNumber(sgdKyattharNew) }}
                    </p>
                    <p class="text-[6px] text-white/40">16.329g · New</p>
                </div>
                <div
                    class="bg-gradient-to-br from-orange-500/10 to-red-500/10 backdrop-blur-sm border border-orange-500/30 rounded-xl p-3">
                    <p class="text-[7px] font-black uppercase tracking-[0.12em] text-orange-400/80">Kyatthar (SGD)</p>
                    <p class="font-mono text-sm font-black italic text-orange-300">S${{ formatNumber(sgdKyattharOld) }}
                    </p>
                    <p class="text-[6px] text-white/40">16.606g · Old</p>
                </div>
            </div>

            <!-- Weight Conversions -->
            <div class="border-t border-white/10 bg-black/20 px-6 py-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-4 bg-gradient-to-b from-amber-400 to-blue-500 rounded-full"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.12em] text-white/60">Weight
                        Conversions</span>
                    <span class="text-[8px] text-white/40">1g · 50g · 100g</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white/5 rounded-xl p-4 text-center">
                        <span class="text-[10px] font-black text-white/70">1 Gram</span>
                        <div class="flex items-center justify-center gap-3 mt-1">
                            <span class="text-[8px] text-white/40">USD</span>
                            <p class="text-lg font-mono font-black text-amber-300">${{ formatNumber(usdPerGram) }}</p>
                            <span class="text-white/30">|</span>
                            <span class="text-[8px] text-white/40">SGD</span>
                            <p class="text-lg font-mono font-black text-blue-300">S${{ formatNumber(sgdPerGram) }}</p>
                        </div>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 text-center">
                        <span class="text-[10px] font-black text-white/70">50 Grams</span>
                        <div class="flex items-center justify-center gap-3 mt-1">
                            <span class="text-[8px] text-white/40">USD</span>
                            <p class="text-lg font-mono font-black text-amber-300">${{ formatNumber(usdPerGram * 50) }}
                            </p>
                            <span class="text-white/30">|</span>
                            <span class="text-[8px] text-white/40">SGD</span>
                            <p class="text-lg font-mono font-black text-blue-300">S${{ formatNumber(sgdPerGram * 50) }}
                            </p>
                        </div>
                    </div>
                    <div class="bg-white/5 rounded-xl p-4 text-center">
                        <span class="text-[10px] font-black text-white/70">100 Grams</span>
                        <div class="flex items-center justify-center gap-3 mt-1">
                            <span class="text-[8px] text-white/40">USD</span>
                            <p class="text-lg font-mono font-black text-amber-300">${{ formatNumber(usdPerGram * 100) }}
                            </p>
                            <span class="text-white/30">|</span>
                            <span class="text-[8px] text-white/40">SGD</span>
                            <p class="text-lg font-mono font-black text-blue-300">S${{ formatNumber(sgdPerGram * 100) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metrics Section -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 px-6 pb-4">
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/50 uppercase">24h Range</span>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-mono font-black text-white/80">H: ${{ formatNumber(dayHigh)
                        }}</span>
                        <span class="text-[9px] font-mono font-black text-white/60">L: ${{ formatNumber(dayLow)
                        }}</span>
                    </div>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/50 uppercase">7d Momentum</span>
                    <p class="text-sm font-mono font-black"
                        :class="momentum7d > 0 ? 'text-emerald-400' : 'text-rose-400'">
                        {{ momentum7d > 0 ? '+' : '' }}{{ momentum7d }}%
                    </p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/50 uppercase">FX Impact</span>
                    <p class="text-[10px] font-mono font-black text-white/90">
                        {{ sgdRate?.change >= 0 ? 'SGD +' : 'SGD -' }}{{ Math.abs(sgdRate?.change_percent ||
                            0).toFixed(2) }}%
                    </p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/50 uppercase">vs Prev Close</span>
                    <p class="text-sm font-mono font-black"
                        :class="premium200d > 0 ? 'text-amber-400' : 'text-emerald-400'">
                        {{ premium200d > 0 ? '+' : '' }}{{ premium200d }}%
                    </p>
                </div>
            </div>

            <!-- Analysis Strip -->
            <div v-if="worldGoldSnapshot && sgdRate"
                class="relative border-t border-white/[0.08] bg-gradient-to-r from-white/5 to-transparent backdrop-blur-sm px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="flex flex-col">
                        <span class="text-[6px] font-black text-white/40">Market Premium</span>
                        <span class="font-mono text-[10px] font-black text-amber-400">+2.04%</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[6px] font-black text-white/40">USD/MMK</span>
                        <span class="font-mono text-[10px] font-black text-white/80">{{
                            formatMoney(worldGoldSnapshot.usd_mmk_rate * 1.02041) }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[6px] font-black text-white/40">USD/SGD</span>
                        <span class="font-mono text-[10px] font-black text-blue-400">{{
                            formatNumber(sgdRate.usd_sgd_rate, 4) }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[6px] font-black text-white/40">SGD Gold</span>
                        <span class="font-mono text-[10px] font-black text-white/80">S${{ formatNumber(sgdGoldPrice)
                        }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[6px] font-black text-white/40">Conversion</span>
                        <span class="font-mono text-[10px] font-black text-white/70">1 oz = 31.1g</span>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-t border-white/5 flex justify-between text-[6px] text-white/30">
                    <span>USD: 1g ${{ formatNumber(usdPerGram) }}</span>
                    <span>SGD: 1g S${{ formatNumber(sgdPerGram) }}</span>
                    <span>FX: {{ formatNumber(sgdRate.usd_sgd_rate, 4) }}</span>
                </div>
            </div>

            <!-- Bottom gradient border -->
            <div
                class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-amber-500/40 to-transparent">
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-white/5 to-transparent">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-[6px] text-white/40 font-mono">Updated: {{ formatTime24(snapshot?.fetched_at)
                    }}</span>
                </div>
                <span class="text-[8px] text-blue-400/60 uppercase">Dual Market Monitor</span>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    snapshot: Object,
    worldGoldSnapshot: Object,
    sgdRate: Object,
    sgdGoldPrice: Number,
    sgdPerGram: Number,
    sgdKyattharNew: Number,
    sgdKyattharOld: Number,
    usdPerGram: Number,
    usdPerKyattharNew: Number,
    usdPerKyattharOld: Number,
    priceFlash: Boolean,
    dayHigh: { type: Number, default: 0 },
    dayLow: { type: Number, default: 0 },
    momentum7d: { type: Number, default: 0 },
    premium200d: { type: Number, default: 0 },
    previousDayUsdPrice: { type: Number, default: null },
    previousDaySgdPrice: { type: Number, default: null },
    previousCloseDate: { type: String, default: null },
});

// USD Daily Change (vs previous day's close from controller)
const usdChangeAbs = computed(() => {
    const current = props.snapshot?.usd_price || 0;
    const previous = props.previousDayUsdPrice;
    if (!previous || previous === 0) return 0;
    return current - previous;
});

const usdChangePct = computed(() => {
    const current = props.snapshot?.usd_price || 0;
    const previous = props.previousDayUsdPrice;
    if (!previous || previous === 0) return '0.00';
    const pct = (usdChangeAbs.value / previous) * 100;
    return pct.toFixed(2);
});

const usdPriceUp = computed(() => usdChangeAbs.value >= 0);

// SGD Daily Change (vs previous day's close from controller)
const sgdChangeAbs = computed(() => {
    const current = props.sgdGoldPrice || 0;
    const previous = props.previousDaySgdPrice;
    if (!previous || previous === 0) return 0;
    return current - previous;
});

const sgdChangePct = computed(() => {
    const current = props.sgdGoldPrice || 0;
    const previous = props.previousDaySgdPrice;
    if (!previous || previous === 0) return '0.00';
    const pct = (sgdChangeAbs.value / previous) * 100;
    return pct.toFixed(2);
});

const sgdPriceUp = computed(() => sgdChangeAbs.value >= 0);

// Helper functions
const formatNumber = (value, decimals = 0) => {
    if (!value && value !== 0) return '0';
    return value.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
};

const formatChangeAmount = (value) => {
    if (!value && value !== 0) return '0';
    return Math.round(value).toLocaleString();
};

const formatMoney = (value) => {
    if (!value) return '0';
    return Math.round(value).toLocaleString();
};

const formatTime24 = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
};

const formatPreviousCloseDate = (dateString) => {
    if (!dateString) return 'previous day';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<style scoped>
@keyframes pulse-slow {

    0%,
    100% {
        opacity: 0.3;
        transform: scale(1);
    }

    50% {
        opacity: 0.5;
        transform: scale(1.05);
    }
}

@keyframes pulse-slower {

    0%,
    100% {
        opacity: 0.2;
        transform: scale(1);
    }

    50% {
        opacity: 0.4;
        transform: scale(1.1);
    }
}

.animate-pulse-slow {
    animation: pulse-slow 6s ease-in-out infinite;
}

.animate-pulse-slower {
    animation: pulse-slower 8s ease-in-out infinite;
}
</style>