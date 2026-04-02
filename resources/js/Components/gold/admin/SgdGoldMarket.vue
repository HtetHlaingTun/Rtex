<template>
    <section class="space-y-4">
        <div class="flex items-center gap-2.5">
            <span class="block w-[3px] h-5 bg-blue-500 rounded-full flex-shrink-0"></span>
            <span class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-700">
                SGD Gold Market
            </span>
            <span class="text-[11px] text-slate-400 font-medium pl-2.5 border-l border-slate-200">
                Real-time from Yahoo Finance
            </span>
        </div>

        <div
            class="bg-gradient-to-br from-blue-900/10 to-slate-900 rounded-[2rem] overflow-hidden border border-blue-500/10">
            <!-- Main Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                <!-- USD/SGD Rate Card -->
                <div
                    class="bg-white/5 rounded-2xl p-4 border border-blue-500/20 hover:border-blue-500/40 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[8px] font-black uppercase tracking-[0.12em] text-blue-400">USD / SGD</span>
                        <TrendIcon :current="sgdRate.usd_sgd_rate"
                            :previous="sgdRate.usd_sgd_rate - (sgdRate.change || 0)" />
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-mono text-2xl font-black italic text-blue-400">
                            {{ $formatNumber(sgdRate.usd_sgd_rate, 4) }}
                        </span>
                    </div>
                    <p class="text-[8px] text-white mt-2">1 USD = {{ $formatNumber(sgdRate.usd_sgd_rate, 4) }} SGD</p>
                    <div class="flex items-center gap-2 mt-2 text-[7px] text-white">
                        <span>24h: {{ sgdRate.change >= 0 ? '+' : '' }}{{ $formatNumber(sgdRate.change, 4) }}</span>
                        <span>({{ sgdRate.change_percent >= 0 ? '+' : '' }}{{ $formatNumber(sgdRate.change_percent, 2)
                        }}%)</span>
                    </div>
                </div>

                <!-- SGD Kyatthar New System -->
                <div
                    class="bg-white/5 rounded-2xl p-4 border border-slate-700/50 hover:border-amber-500/30 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[9px] font-black uppercase tracking-[0.12em] text-white/50">SGD Kyatthar
                            (New)</span>
                        <span
                            class="text-[6px] font-black text-amber-400/60 bg-amber-500/20 px-1.5 py-0.5 rounded-full">Premium</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-mono text-2xl font-black italic text-white/80">
                            S${{ $formatNumber(sgdKyattharNew) }}
                        </span>
                    </div>
                    <p class="text-[7px] text-white mt-2">16.329g · High Purity</p>
                    <p class="text-[6px] text-white mt-1">≈ S${{ $formatNumber(sgdKyattharNew / 16.329, 2) }} / Gram</p>
                </div>

                <!-- SGD Kyatthar Old System -->
                <div
                    class="bg-white/5 rounded-2xl p-4 border border-slate-700/50 hover:border-orange-500/30 transition-all">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[9px] font-black uppercase tracking-[0.12em] text-white/50">SGD Kyatthar
                            (Old)</span>
                        <span
                            class="text-[6px] font-black text-orange-400/60 bg-orange-500/20 px-1.5 py-0.5 rounded-full">Standard</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="font-mono text-2xl font-black italic text-white/80">
                            S${{ $formatNumber(sgdKyattharOld) }}
                        </span>
                    </div>
                    <p class="text-[7px] text-white mt-2">16.606g · Standard</p>
                    <p class="text-[6px] text-white mt-1">≈ S${{ $formatNumber(sgdKyattharOld / 16.606, 2) }} / Gram</p>
                </div>
            </div>

            <!-- SGD Weight Conversion Section -->
            <div class="border-t border-white/10 bg-black/20 px-6 py-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-4 bg-gradient-to-b from-blue-400 to-cyan-400 rounded-full"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.12em] text-white/60">SGD Weight
                        Conversions</span>
                    <span class="text-[8px] text-white">1g · 50g · 100g</span>
                </div>

                <div class="bg-gradient-to-r from-blue-500/5 to-cyan-500/5 rounded-xl p-5 border border-blue-500/20">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- 1 Gram Card -->
                        <div class="bg-white/5 rounded-xl p-4 text-center hover:bg-white/10 transition-all">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <span class="text-xs font-black text-white/60">⚖️ 1 Gram</span>
                            </div>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-xl font-mono font-black italic text-blue-300">
                                    S${{ $formatNumber(sgdPerGram) }}
                                </span>
                            </div>
                            <div class="mt-2 text-[9px] text-white">
                                ≈ ${{ $formatNumber(usdPerGram) }} USD
                            </div>
                        </div>

                        <!-- 50 Grams Card -->
                        <div
                            class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-xl p-4 text-center border border-blue-500/30">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <span class="text-xs font-black text-white/70">📦 50 Grams</span>
                            </div>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-2xl font-mono font-black italic text-blue-400">
                                    S${{ $formatNumber(sgdPerGram * 50) }}
                                </span>
                            </div>
                            <div class="mt-2 text-[9px] text-white">
                                ≈ ${{ $formatNumber(usdPerGram * 50) }} USD
                            </div>
                        </div>

                        <!-- 100 Grams Card -->
                        <div
                            class="bg-gradient-to-br from-blue-500/15 to-cyan-500/15 rounded-xl p-4 text-center border border-blue-500/40">
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <span class="text-xs font-black text-white-80">🏆 100 Grams</span>
                            </div>
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-3xl font-mono font-black italic text-blue-500">
                                    S${{ $formatNumber(sgdPerGram * 100) }}
                                </span>
                            </div>
                            <div class="mt-2 text-[9px] text-white">
                                ≈ ${{ $formatNumber(usdPerGram * 100) }} USD
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Reference - Gold Bar Sizes -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 px-6 pb-4">
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">1 oz Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">S${{ $formatNumber(sgdGoldPrice) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">10g Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">S${{ $formatNumber(sgdPerGram * 10) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">100g Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">S${{ $formatNumber(sgdPerGram * 100) }}</p>
                </div>
                <div class="bg-white/5 rounded-xl p-2 text-center">
                    <span class="text-[7px] font-black text-white/40 uppercase">1 kg Bar</span>
                    <p class="text-sm font-mono font-black text-white/80">S${{ $formatNumber(sgdPerGram * 1000) }}</p>
                </div>
            </div>

            <!-- Market Analysis Strip -->
            <div class="border-t border-white/5 px-6 py-4 flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-[8px] font-black text-blue-400 uppercase tracking-wider">FX Rate Source</span>
                    <span class="text-[8px] text-white/40">Yahoo Finance · Real-time</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[8px] text-white/40">1g: S${{ $formatNumber(sgdPerGram) }}</span>
                    <span class="text-[8px] text-white/40">|</span>
                    <span class="text-[8px] text-white/40">50g: S${{ $formatNumber(sgdPerGram * 50) }}</span>
                    <span class="text-[8px] text-white/40">|</span>
                    <span class="text-[8px] text-white/40">100g: S${{ $formatNumber(sgdPerGram * 100) }}</span>
                    <span class="text-[8px] text-white-40">|</span>
                    <span class="text-[8px] text-white/40">Last update: {{ $formatTime24(sgdRate.fetched_at) }}</span>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import TrendIcon from '@/Components/TrendIcon.vue';

defineProps({
    sgdRate: Object,
    snapshot: Object,
    sgdGoldPrice: Number,
    sgdPerGram: Number,
    sgdKyattharNew: Number,
    sgdKyattharOld: Number,
    usdPerGram: Number,
});
</script>