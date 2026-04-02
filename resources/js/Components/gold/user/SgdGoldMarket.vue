<template>
    <div
        class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-[2rem] overflow-hidden border border-white/10 shadow-2xl shadow-black/20 group transition-all duration-500 hover:shadow-2xl hover:shadow-blue-500/5">
        <!-- Animated background effects -->
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl animate-pulse-slower">
        </div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-500/5 to-cyan-500/5 rounded-full blur-3xl">
        </div>

        <!-- Faint horizontal grid lines -->
        <div class="absolute inset-0 flex flex-col justify-around pointer-events-none opacity-[0.02]">
            <div v-for="n in 6" :key="n" class="w-full h-px bg-white"></div>
        </div>

        <!-- Decorative corner accents -->
        <div class="absolute top-4 left-4 w-20 h-20 border-l-2 border-t-2 border-white/5 rounded-tl-2xl"></div>
        <div class="absolute bottom-4 right-4 w-20 h-20 border-r-2 border-b-2 border-white/5 rounded-br-2xl"></div>

        <!-- Price + metrics grid -->
        <div class="relative grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-8 p-6 md:p-8 pb-6">
            <!-- Left: big price section -->
            <div class="space-y-5">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <span
                        class="text-[9px] font-black uppercase tracking-[0.2em] text-white/50 group-hover:text-white/70 transition-colors duration-500">
                        SGD Spot Value
                    </span>
                    <span class="text-white/20 text-[9px]">·</span>
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white/40">
                        XAU / SGD
                    </span>
                </div>

                <!-- Main price display -->
                <div class="flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold text-white/40 leading-none">S$</span>
                    <span
                        class="font-mono text-5xl sm:text-6xl md:text-7xl font-black italic text-white leading-none tracking-tighter transition-all duration-300 group-hover:scale-[1.02]"
                        :class="{ 'text-blue-400 scale-105': priceFlash }">
                        {{ $formatNumber(sgdGoldPrice) }}
                    </span>
                    <span class="text-sm text-white/30 font-mono">/ oz</span>
                </div>

                <!-- Price change indicators -->
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span
                            class="font-mono text-[11px] font-black px-3 py-1.5 rounded-full border backdrop-blur-sm transition-all duration-300 hover:scale-105"
                            :class="priceUp
                                ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30 hover:bg-emerald-500/20'
                                : 'text-rose-400 bg-rose-500/10 border-rose-500/30 hover:bg-rose-500/20'">
                            {{ priceUp ? '▲' : '▼' }} {{ changeAbs }} ({{ changePct }}%)
                        </span>
                        <span
                            class="text-[9px] text-white/40 font-medium uppercase tracking-wider flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $formatDateTime(sgdRate?.fetched_at ?? now) }}
                        </span>
                    </div>
                </div>

                <!-- Live market badge -->
                <div class="flex items-center gap-2 pt-1">
                    <div
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/5 rounded-full border border-white/10 backdrop-blur-sm">
                        <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></div>
                        <span class="text-[7px] font-black text-white/60 uppercase tracking-wider">Live FX Rate</span>
                    </div>
                </div>
            </div>

            <!-- Right: metric breakdown cards -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-1 gap-2 min-w-[180px]">
                <!-- USD/SGD Rate Card -->
                <div v-if="sgdRate && sgdRate.usd_sgd_rate"
                    class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 backdrop-blur-sm border border-blue-500/30 rounded-xl p-3 hover:border-blue-500/50 transition-all duration-300 hover:scale-[1.02] cursor-help group/card">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[8px] font-black uppercase tracking-[0.12em] text-blue-400/80">USD / SGD</p>
                        <div class="flex items-center gap-0.5">
                            <span :class="sgdRate.change >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[6px] font-black group-hover/card:scale-105 transition-transform">
                                {{ sgdRate.change >= 0 ? '▲' : '▼' }} {{ Math.abs(sgdRate.change_percent).toFixed(1) }}%
                            </span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span
                            class="font-mono text-base font-black italic text-blue-300 group-hover/card:text-blue-200 transition-colors">
                            {{ $formatNumber(sgdRate.usd_sgd_rate, 4) }}
                        </span>
                        <span class="text-[6px] font-bold text-white/40">SGD</span>
                    </div>
                    <p class="text-[6px] font-bold uppercase tracking-wide text-white/40 mt-1">
                        1 USD = {{ $formatNumber(sgdRate.usd_sgd_rate, 4) }} SGD
                    </p>
                </div>

                <!-- SGD Kyatthar New System Card -->
                <div
                    class="bg-gradient-to-br from-amber-500/10 to-yellow-500/10 backdrop-blur-sm border border-amber-500/30 rounded-xl p-3 hover:border-amber-500/50 transition-all duration-300 hover:scale-[1.02] cursor-help group/card">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[8px] font-black uppercase tracking-[0.12em] text-amber-400/80">Kyatthar (SGD)
                        </p>
                        <span
                            class="text-[6px] font-black text-amber-400/60 bg-amber-500/20 px-1.5 py-0.5 rounded-full group-hover/card:bg-amber-500/30 transition-all">New</span>
                    </div>
                    <p
                        class="font-mono text-base font-black italic text-amber-300 group-hover/card:text-amber-200 transition-colors">
                        S${{ $formatNumber(sgdKyattharNew) }}
                    </p>
                    <p class="text-[6px] font-bold uppercase tracking-wide text-white/40 mt-1">16.329g · Premium</p>
                </div>

                <!-- SGD Kyatthar Old System Card -->
                <div
                    class="bg-gradient-to-br from-orange-500/10 to-red-500/10 backdrop-blur-sm border border-orange-500/30 rounded-xl p-3 hover:border-orange-500/50 transition-all duration-300 hover:scale-[1.02] cursor-help group/card">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[8px] font-black uppercase tracking-[0.12em] text-orange-400/80">Kyatthar (SGD)
                        </p>
                        <span
                            class="text-[6px] font-black text-orange-400/60 bg-orange-500/20 px-1.5 py-0.5 rounded-full group-hover/card:bg-orange-500/30 transition-all">Old</span>
                    </div>
                    <p
                        class="font-mono text-base font-black italic text-orange-300 group-hover/card:text-orange-200 transition-colors">
                        S${{ $formatNumber(sgdKyattharOld) }}
                    </p>
                    <p class="text-[6px] font-bold uppercase tracking-wide text-white/40 mt-1">16.606g · Standard</p>
                </div>
            </div>
        </div>

        <!-- SGD Weight Conversion Section -->
        <div class="border-t border-white/10 bg-black/20 px-6 py-5 backdrop-blur-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-1 h-4 bg-gradient-to-b from-blue-400 to-cyan-500 rounded-full"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.12em] text-white/60">SGD Weight
                    Conversions</span>
                <span class="text-[8px] text-white/40">1g · 50g · 100g</span>
            </div>

            <div class="bg-gradient-to-r from-blue-500/5 to-cyan-500/5 rounded-xl p-5 border border-blue-500/20">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- 1 Gram Card -->
                    <div
                        class="bg-white/5 rounded-xl p-4 text-center hover:bg-white/10 transition-all duration-300 group hover:scale-[1.02] cursor-pointer">
                        <div class="flex items-center justify-center gap-1 mb-2">
                            <span class="text-base">⚖️</span>
                            <span class="text-[10px] font-black text-white/70">1 Gram</span>
                            <span class="text-[7px] text-white/40">(0.03215 oz)</span>
                        </div>
                        <div class="flex items-baseline justify-center gap-1">
                            <span
                                class="text-2xl font-mono font-black italic text-blue-300 group-hover:text-blue-200 transition-colors">
                                S${{ $formatNumber(sgdPerGram) }}
                            </span>
                            <span class="text-[9px] text-white/40">SGD</span>
                        </div>
                        <div class="mt-2 text-[8px] text-white/40">
                            ≈ ${{ $formatNumber(sgdPerGram / (sgdRate?.usd_sgd_rate || 1.35)) }} USD
                        </div>
                    </div>

                    <!-- 50 Grams Card -->
                    <div
                        class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-xl p-4 text-center border border-blue-500/30 hover:border-blue-500/60 transition-all duration-300 group hover:scale-[1.02] cursor-pointer">
                        <div class="flex items-center justify-center gap-1 mb-2">
                            <span class="text-base">📦</span>
                            <span class="text-[10px] font-black text-white/80">50 Grams</span>
                            <span class="text-[7px] text-white/40">(1.6075 oz)</span>
                        </div>
                        <div class="flex items-baseline justify-center gap-1">
                            <span
                                class="text-3xl font-mono font-black italic text-blue-400 group-hover:text-blue-300 transition-colors">
                                S${{ $formatNumber(sgdPerGram * 50) }}
                            </span>
                            <span class="text-[9px] text-white/40">SGD</span>
                        </div>
                        <div class="mt-2 text-[8px] text-white/40">
                            ≈ ${{ $formatNumber((sgdPerGram * 50) / (sgdRate?.usd_sgd_rate || 1.35)) }} USD
                        </div>
                    </div>

                    <!-- 100 Grams Card -->
                    <div
                        class="bg-gradient-to-br from-blue-500/15 to-cyan-500/15 rounded-xl p-4 text-center border border-blue-500/40 hover:border-blue-500/80 transition-all duration-300 group hover:scale-[1.02] cursor-pointer">
                        <div class="flex items-center justify-center gap-1 mb-2">
                            <span class="text-base">🏆</span>
                            <span class="text-[10px] font-black text-white-90">100 Grams</span>
                            <span class="text-[7px] text-white/40">(3.215 oz)</span>
                        </div>
                        <div class="flex items-baseline justify-center gap-1">
                            <span
                                class="text-4xl font-mono font-black italic text-blue-500 group-hover:text-blue-400 transition-colors">
                                S${{ $formatNumber(sgdPerGram * 100) }}
                            </span>
                            <span class="text-[9px] text-white/40">SGD</span>
                        </div>
                        <div class="mt-2 text-[8px] text-white/40">
                            ≈ ${{ $formatNumber((sgdPerGram * 100) / (sgdRate?.usd_sgd_rate || 1.35)) }} USD
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Reference - Gold Bar Sizes -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 px-6 pb-4">
            <div class="bg-white/5 rounded-xl p-2 text-center hover:bg-white/10 transition-all group">
                <span class="text-[7px] font-black text-white/50 uppercase">1 oz Bar</span>
                <p class="text-sm font-mono font-black text-white/90 group-hover:text-blue-400 transition-colors">S${{
                    $formatNumber(sgdGoldPrice) }}</p>
                <p class="text-[6px] text-white/30">≈ ${{ $formatNumber(sgdGoldPrice / (sgdRate?.usd_sgd_rate || 1.35))
                }} USD</p>
            </div>
            <div class="bg-white/5 rounded-xl p-2 text-center hover:bg-white/10 transition-all group">
                <span class="text-[7px] font-black text-white/50 uppercase">10g Bar</span>
                <p class="text-sm font-mono font-black text-white/90 group-hover:text-blue-400 transition-colors">S${{
                    $formatNumber(sgdPerGram * 10) }}</p>
                <p class="text-[6px] text-white/30">{{ $formatNumber((sgdPerGram * 10) / (sgdRate?.usd_sgd_rate ||
                    1.35), 2) }} USD</p>
            </div>
            <div class="bg-white/5 rounded-xl p-2 text-center hover:bg-white/10 transition-all group">
                <span class="text-[7px] font-black text-white/50 uppercase">100g Bar</span>
                <p class="text-sm font-mono font-black text-white/90 group-hover:text-blue-400 transition-colors">S${{
                    $formatNumber(sgdPerGram * 100) }}</p>
                <p class="text-[6px] text-white/30">{{ $formatNumber((sgdPerGram * 100) / (sgdRate?.usd_sgd_rate ||
                    1.35), 0) }} USD</p>
            </div>
            <div class="bg-white/5 rounded-xl p-2 text-center hover:bg-white/10 transition-all group">
                <span class="text-[7px] font-black text-white/50 uppercase">1 kg Bar</span>
                <p class="text-sm font-mono font-black text-white/90 group-hover:text-blue-400 transition-colors">S${{
                    $formatNumber(sgdPerGram * 1000) }}</p>
                <p class="text-[6px] text-white/30">{{ $formatNumber((sgdPerGram * 1000) / (sgdRate?.usd_sgd_rate ||
                    1.35), 0) }} USD</p>
            </div>
        </div>

        <!-- Analysis Strip -->
        <div v-if="sgdRate"
            class="relative border-t border-white/[0.08] bg-gradient-to-r from-white/5 to-transparent backdrop-blur-sm px-6 py-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                <div class="flex flex-col gap-0.5">
                    <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white/40">FX Rate</span>
                    <span class="font-mono text-[11px] font-black text-blue-400">{{ $formatNumber(sgdRate.usd_sgd_rate,
                        4) }}</span>
                    <span class="text-[5px] text-white/20">USD/SGD</span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white/40">24h Change</span>
                    <span class="font-mono text-[11px] font-black"
                        :class="sgdRate.change >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                        {{ sgdRate.change >= 0 ? '+' : '' }}{{ $formatNumber(sgdRate.change_percent, 2) }}%
                    </span>
                    <span class="text-[5px] text-white/20">vs Previous</span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white/40">SGD Gold</span>
                    <span class="font-mono text-[11px] font-black text-white/80">S${{ $formatNumber(sgdGoldPrice)
                    }}</span>
                    <span class="text-[5px] text-white/20">per Troy Ounce</span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white/40">Conversion</span>
                    <span class="font-mono text-[11px] font-black text-white/70">1 oz = 31.1035 g</span>
                    <span class="text-[5px] text-white/20">Troy Ounce Standard</span>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white/40">FX Source</span>
                    <span class="font-mono text-[11px] font-black text-blue-400/80">Yahoo Finance</span>
                    <span class="text-[5px] text-white/20">Real-time USD/SGD</span>
                </div>
            </div>

            <!-- Quick Summary Strip -->
            <div
                class="mt-3 pt-2 border-t border-white/5 flex flex-wrap justify-between items-center gap-2 text-[7px] text-white/30">
                <span>1g: S${{ $formatNumber(sgdPerGram) }}</span>
                <span class="text-white/20">|</span>
                <span>50g: S${{ $formatNumber(sgdPerGram * 50) }}</span>
                <span class="text-white/20">|</span>
                <span>100g: S${{ $formatNumber(sgdPerGram * 100) }}</span>
                <span class="text-white/20">|</span>
                <span>1kg: S${{ $formatNumber(sgdPerGram * 1000) }}</span>
            </div>
        </div>

        <!-- Bottom gradient border -->
        <div
            class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-blue-500/40 to-transparent">
        </div>

        <!-- Link to history -->
        <div
            class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-white/5 to-transparent border-t border-white/10">
            <div class="flex items-center gap-2">
                <div class="w-1 h-1 bg-blue-400 rounded-full animate-pulse"></div>
                <span class="text-[7px] text-white/40 font-mono">Last update: {{ $formatTime24(sgdRate?.fetched_at)
                }}</span>
            </div>
            <Link :href="route('user.gold.history', { type: 'world_oz' })"
                class="text-[9px] font-black text-blue-400 uppercase flex items-center gap-2 hover:text-blue-300 transition-all duration-300 hover:gap-3 group/link">
                Analyze Trends
                <span class="text-sm transition-transform duration-300 group-hover/link:translate-x-1">→</span>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    sgdRate: Object,
    snapshot: Object,
    sgdGoldPrice: Number,
    sgdPerGram: Number,
    sgdKyattharNew: Number,
    sgdKyattharOld: Number,
    usdPerGram: Number,
    priceFlash: { type: Boolean, default: false },
    priceUp: { type: Boolean, default: true },
    changeAbs: { type: Number, default: 0 },
    changePct: { type: Number, default: 0 },
});
</script>

<style scoped>
@keyframes pulse-slow {

    0%,
    100% {
        opacity: 0.4;
        transform: scale(1);
    }

    50% {
        opacity: 0.7;
        transform: scale(1.05);
    }
}

@keyframes pulse-slower {

    0%,
    100% {
        opacity: 0.3;
        transform: scale(1);
    }

    50% {
        opacity: 0.6;
        transform: scale(1.1);
    }
}

.animate-pulse-slow {
    animation: pulse-slow 5s ease-in-out infinite;
}

.animate-pulse-slower {
    animation: pulse-slower 7s ease-in-out infinite;
}
</style>