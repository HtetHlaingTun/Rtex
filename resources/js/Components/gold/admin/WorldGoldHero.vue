<template>
    <section class="space-y-4">
        <!-- Section label with enhanced styling -->
        <div class="flex items-center gap-2.5">
            <span
                class="block w-[3px] h-6 bg-gradient-to-b from-amber-400 to-amber-600 rounded-full flex-shrink-0"></span>
            <span class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-700">
                World Gold Spot
            </span>
            <span class="text-[11px] text-slate-400 font-medium pl-2.5 border-l border-slate-200">
                XAU/USD · Live Spot Price
            </span>
            <span class="text-[9px] text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full ml-2">Real-time</span>
        </div>

        <!-- Dark hero card -->
        <div
            class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[2rem] overflow-hidden border border-white/5 shadow-2xl relative group">
            <!-- Animated background effects -->
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-500/5 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/5 rounded-full blur-3xl"></div>

            <!-- Faint horizontal grid lines -->
            <div class="absolute inset-0 flex flex-col justify-around pointer-events-none opacity-[0.03]">
                <div v-for="n in 5" :key="n" class="w-full h-px bg-white"></div>
            </div>

            <!-- Price + metrics grid -->
            <div class="relative grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-6 p-6 md:p-8 pb-6">
                <!-- Left: big price section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span class="text-[9px] font-black uppercase tracking-[0.18em] text-white/40">
                            Global Spot Value
                        </span>
                        <span class="text-white/20 text-[9px]">·</span>
                        <span class="text-[9px] font-black uppercase tracking-[0.18em] text-white/40">
                            USD / Troy Ounce
                        </span>
                    </div>

                    <!-- Main price display -->
                    <div class="flex items-baseline gap-2">
                        <span class="font-mono text-3xl font-bold text-white/50 leading-none">$</span>
                        <span
                            class="font-mono text-5xl sm:text-6xl md:text-7xl font-black italic text-white leading-none tracking-tighter transition-all duration-300"
                            :class="{ 'text-amber-400 scale-105': priceFlash }">
                            {{ $formatNumber(snapshot?.usd_price ?? 0) }}
                        </span>
                        <span class="text-sm text-white font-mono">/ oz</span>
                    </div>

                    <!-- Price change indicators -->
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span
                                class="font-mono text-[11px] font-black px-3 py-1.5 rounded-full border backdrop-blur-sm"
                                :class="priceUp
                                    ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30'
                                    : 'text-rose-400 bg-rose-500/10 border-rose-500/30'">
                                {{ priceUp ? '▲' : '▼' }} {{ changeAbs }} ({{ changePct }}%)
                            </span>
                            <span
                                class="text-[9px] text-white font-medium uppercase tracking-wider flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $formatDateTime(snapshot?.fetched_at ?? now) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: metric breakdown cards -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-1 gap-2 min-w-[180px]">
                    <!-- Kyatthar New System Card -->
                    <div
                        class="bg-gradient-to-br from-amber-500/10 to-yellow-500/10 backdrop-blur-sm border border-amber-500/30 rounded-xl p-3 hover:border-amber-500/50 transition-all group/card">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[8px] font-black uppercase tracking-[0.12em] text-amber-400/80">Kyatthar</p>
                            <span
                                class="text-[6px] font-black text-amber-400/60 bg-amber-500/20 px-1.5 py-0.5 rounded-full">New</span>
                        </div>
                        <p class="font-mono text-base font-black italic text-amber-300">
                            ${{ $formatNumber(usdPerKyattharNew) }}
                        </p>
                        <p class="text-[6px] font-bold uppercase tracking-wide text-white/40 mt-1">16.329g · Premium
                            (New)</p>
                    </div>

                    <!-- Kyatthar Old System Card -->
                    <div
                        class="bg-gradient-to-br from-orange-500/10 to-red-500/10 backdrop-blur-sm border border-orange-500/30 rounded-xl p-3 hover:border-orange-500/50 transition-all group/card">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[8px] font-black uppercase tracking-[0.12em] text-orange-400/80">Kyatthar</p>
                            <span
                                class="text-[6px] font-black text-orange-400/60 bg-orange-500/20 px-1.5 py-0.5 rounded-full">Old</span>
                        </div>
                        <p class="font-mono text-base font-black italic text-orange-300">
                            ${{ $formatNumber(usdPerKyattharOld) }}
                        </p>
                        <p class="text-[6px] font-bold uppercase tracking-wide text-white/40 mt-1">16.606g · Standard
                            (OLD)</p>
                    </div>
                </div>
            </div>

            <!-- USD Weight Conversion Section -->
            <div class="border-t border-white/10 bg-black/20 px-6 py-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-4 bg-gradient-to-b from-amber-400 to-yellow-500 rounded-full"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.12em] text-white/60">USD Weight
                        Conversions</span>
                    <span class="text-[8px] text-white">1g · 50g · 100g</span>
                </div>

                <div
                    class="bg-gradient-to-r from-amber-500/5 to-yellow-500/5 rounded-xl p-5 border border-amber-500/20">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- 1 Gram Card -->
                        <div class="bg-white/5 rounded-xl p-4 text-center hover:bg-white/10 transition-all group">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <span class="text-xs font-black text-white/60">⚖️ 1 Gram</span>
                                <span class="text-[8px] text-white">(0.03215 oz)</span>
                            </div>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-2xl font-mono font-black italic text-amber-300">
                                    ${{ $formatNumber(usdPerGram) }}
                                </span>
                                <span class="text-[9px] text-white/40">USD</span>
                            </div>
                            <div class="mt-2 text-[9px] text-white">
                                ≈ S${{ $formatNumber(usdPerGram * (sgdRate?.usd_sgd_rate || 1.35)) }} SGD
                            </div>
                        </div>

                        <!-- 50 Grams Card -->
                        <div
                            class="bg-gradient-to-br from-amber-500/10 to-yellow-500/10 rounded-xl p-4 text-center border border-amber-500/30 hover:border-amber-500/60 transition-all group">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <span class="text-xs font-black text-white/70">📦 50 Grams</span>
                                <span class="text-[8px] text-white">(1.6075 oz)</span>
                            </div>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-3xl font-mono font-black italic text-amber-400">
                                    ${{ $formatNumber(usdPerGram * 50) }}
                                </span>
                                <span class="text-[9px] text-white/40">USD</span>
                            </div>
                            <div class="mt-2 text-[9px] text-white">
                                ≈ S${{ $formatNumber((usdPerGram * 50) * (sgdRate?.usd_sgd_rate || 1.35)) }} SGD
                            </div>
                        </div>

                        <!-- 100 Grams Card -->
                        <div
                            class="bg-gradient-to-br from-amber-500/15 to-yellow-500/15 rounded-xl p-4 text-center border border-amber-500/40 hover:border-amber-500/80 transition-all group">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <span class="text-xs font-black text-white-80">🏆 100 Grams</span>
                                <span class="text-[8px] text-white">(3.215 oz)</span>
                            </div>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-4xl font-mono font-black italic text-amber-500">
                                    ${{ $formatNumber(usdPerGram * 100) }}
                                </span>
                                <span class="text-[9px] text-white/40">USD</span>
                            </div>
                            <div class="mt-2 text-[9px] text-white">
                                ≈ S${{ $formatNumber((usdPerGram * 100) * (sgdRate?.usd_sgd_rate || 1.35)) }} SGD
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Reference - Gold Bar Sizes -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 px-6 pb-4">
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">1 oz Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">${{ $formatNumber(snapshot?.usd_price ?? 0) }}
                    </p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">10g Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">${{ $formatNumber(usdPerGram * 10) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">100g Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">${{ $formatNumber(usdPerGram * 100) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">1 kg Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">${{ $formatNumber(usdPerGram * 1000) }}</p>
                </div>
            </div>

            <!-- Analysis Strip -->
            <div v-if="worldGoldSnapshot"
                class="relative border-t border-white/[0.08] bg-white/5 backdrop-blur-sm px-6 py-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white">Market Premium</span>
                        <span class="font-mono text-[10px] font-black text-amber-400">+2.04%</span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white">Implied
                            USD/MMK</span>
                        <span class="font-mono text-[10px] font-black text-white/80">
                            {{ $formatMoney(worldGoldSnapshot.usd_mmk_rate * 1.02041) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white">Spread vs Mid</span>
                        <span class="font-mono text-[10px] font-black text-orange-400">
                            +{{ $formatMoney((worldGoldSnapshot.usd_mmk_rate * 1.02041) -
                                worldGoldSnapshot.usd_mmk_rate) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white">Conversion</span>
                        <span class="font-mono text-[10px] font-black text-white/70">1 oz = 31.1035 g</span>
                    </div>
                    <div v-if="sgdRate" class="flex flex-col gap-0.5">
                        <span class="text-[7px] font-black uppercase tracking-[0.12em] text-white">FX Source</span>
                        <span class="font-mono text-[10px] font-black text-blue-400/80">Yahoo Finance</span>
                    </div>
                </div>

                <!-- Quick Summary Strip -->
                <div
                    class="mt-3 pt-2 border-t border-white/5 flex flex-wrap justify-between items-center gap-2 text-[7px] text-white/40">
                    <span>1g: ${{ $formatNumber(usdPerGram) }}</span>
                    <span>•</span>
                    <span>50g: ${{ $formatNumber(usdPerGram * 50) }}</span>
                    <span>•</span>
                    <span>100g: ${{ $formatNumber(usdPerGram * 100) }}</span>
                    <span>•</span>
                    <span>1kg: ${{ $formatNumber(usdPerGram * 1000) }}</span>
                </div>
            </div>

            <!-- Bottom gradient border -->
            <div
                class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent">
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    snapshot: Object,
    priceFlash: Boolean,
    priceUp: Boolean,
    changeAbs: Number,
    changePct: Number,
    usdPerGram: Number,
    usdPerKyattharNew: Number,
    usdPerKyattharOld: Number,
    worldGoldSnapshot: Object,
    sgdRate: Object,
});
</script>