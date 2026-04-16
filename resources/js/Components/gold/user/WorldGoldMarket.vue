<template>
    <Link :href="route('user.gold.history', { type: 'world_oz' })" data-gold="World Gold Spot"
        data-gold-system="international">
        <div
            class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-2xl overflow-hidden border border-white/10 shadow-lg group transition-all duration-300 hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-0.5">

            <!-- Animated background -->
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl animate-pulse-slow">
            </div>
            <div
                class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse-slower">
            </div>

            <!-- Header -->
            <div class="relative px-5 pt-4 pb-2 border-b border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></div>
                            <span class="text-[10px] font-bold text-white/80 uppercase tracking-wider">USD</span>
                        </div>
                        <span class="text-white/40 text-xs">/</span>
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></div>
                            <span class="text-[10px] font-bold text-white/80 uppercase tracking-wider">SGD</span>
                        </div>
                        <span class="text-[8px] text-white/40 ml-1">Dual Market</span>
                    </div>
                    <div class="flex items-center gap-1 px-2 py-0.5 bg-white/5 rounded-full border border-white/10">
                        <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                        <span class="text-[6px] font-bold text-white/60 uppercase">LIVE</span>
                    </div>
                </div>
            </div>

            <!-- Dual Price Display -->
            <div class="grid grid-cols-2 gap-4 p-5">
                <!-- USD -->
                <div>
                    <div class="text-[9px] font-medium text-white/50 mb-1">Spot Price</div>
                    <div class="flex items-baseline gap-0.5">
                        <span class="text-lg font-bold text-white/50">$</span>
                        <span class="text-2xl sm:text-3xl font-bold text-white"
                            :class="{ 'text-amber-400 scale-105': priceFlash }">
                            {{ formatNumber(snapshot?.usd_price ?? 0) }}
                        </span>
                        <span class="text-xs text-white/30">/oz</span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        <span :class="usdPriceUp ? 'text-emerald-400' : 'text-rose-400'" class="text-[11px] font-bold">
                            {{ usdPriceUp ? '▲' : '▼' }}
                        </span>
                        <span :class="usdPriceUp ? 'text-emerald-400' : 'text-rose-400'" class="text-[11px] font-mono">
                            ${{ formatChangeAmount(Math.abs(usdChangeAbs)) }}
                        </span>
                        <span :class="usdPriceUp ? 'text-emerald-400' : 'text-rose-400'" class="text-[10px]">
                            ({{ usdChangePct }}%)
                        </span>
                    </div>
                </div>

                <!-- SGD -->
                <div>
                    <div class="text-[9px] font-medium text-white/50 mb-1">Spot Price</div>
                    <div class="flex items-baseline gap-0.5">
                        <span class="text-lg font-bold text-white/50">S$</span>
                        <span class="text-2xl sm:text-3xl font-bold text-white">
                            {{ formatNumber(sgdGoldPrice) }}
                        </span>
                        <span class="text-xs text-white/30">/oz</span>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        <span :class="sgdPriceUp ? 'text-emerald-400' : 'text-rose-400'" class="text-[11px] font-bold">
                            {{ sgdPriceUp ? '▲' : '▼' }}
                        </span>
                        <span :class="sgdPriceUp ? 'text-emerald-400' : 'text-rose-400'" class="text-[11px] font-mono">
                            S${{ formatChangeAmount(Math.abs(sgdChangeAbs)) }}
                        </span>
                        <span :class="sgdPriceUp ? 'text-emerald-400' : 'text-rose-400'" class="text-[10px]">
                            ({{ sgdChangePct }}%)
                        </span>
                    </div>
                </div>
            </div>

            <!-- FX Rate -->
            <div v-if="sgdRate" class="mx-5 mb-3 px-3 py-1.5 bg-white/5 rounded-lg border border-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-[8px] font-medium text-white/50">USD/SGD</span>
                    <span class="text-sm font-mono font-bold text-blue-300">{{ formatNumber(sgdRate.usd_sgd_rate, 4)
                        }}</span>
                    <div class="flex items-center gap-1">
                        <span class="text-[7px] text-white/40">24h:</span>
                        <span :class="sgdRate.change >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                            class="text-[8px] font-bold">
                            {{ sgdRate.change >= 0 ? '▲' : '▼' }} {{ formatNumber(Math.abs(sgdRate.change), 4) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Weight Conversion Table -->
            <div class="border-t border-white/10 bg-black/20 px-5 py-3">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-1 h-3 bg-gradient-to-b from-amber-400 to-blue-500 rounded-full"></span>
                    <span class="text-[8px] font-bold text-white/50 uppercase tracking-wider">Weight Conversion</span>
                </div>

                <!-- Table Layout -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-left py-2 text-[8px] font-bold text-white/40 uppercase tracking-wider">
                                    Weight</th>
                                <th class="text-right py-2 text-[8px] font-bold text-white/40 uppercase tracking-wider">
                                    USD</th>
                                <th class="text-right py-2 text-[8px] font-bold text-white/40 uppercase tracking-wider">
                                    SGD</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-2.5 text-[11px] font-bold text-white/70">1 Gram</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-amber-300">${{
                                    formatNumber(usdPerGram) }}</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-blue-300">S${{
                                    formatNumber(sgdPerGram) }}</td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-2.5 text-[11px] font-bold text-white/70">50 Grams</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-amber-300">${{
                                    formatNumber(usdPerGram * 50) }}</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-blue-300">S${{
                                    formatNumber(sgdPerGram * 50) }}</td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-2.5 text-[11px] font-bold text-white/70">100 Grams</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-amber-300">${{
                                    formatNumber(usdPerGram * 100) }}</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-blue-300">S${{
                                    formatNumber(sgdPerGram * 100) }}</td>
                            </tr>
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-2.5 text-[11px] font-bold text-white/70">1 Kilogram (1000g)</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-amber-300">${{
                                    formatNumber(usdPerGram * 1000) }}</td>
                                <td class="py-2.5 text-right font-mono text-[11px] text-blue-300">S${{
                                    formatNumber(sgdPerGram * 1000) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-5 py-2 bg-white/5">
                <div class="flex items-center gap-1.5">
                    <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                    <span class="text-[7px] text-white/40">{{ formatTime24(snapshot?.fetched_at) }}</span>
                </div>
                <span class="text-[7px] text-white/30">International Spot</span>
            </div>

            <!-- Bottom glow -->
            <div
                class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent">
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    snapshot: Object,
    sgdRate: Object,
    sgdGoldPrice: Number,
    sgdPerGram: Number,
    usdPerGram: Number,
    priceFlash: Boolean,
    previousDayUsdPrice: { type: Number, default: null },
    previousDaySgdPrice: { type: Number, default: null },
});

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
    return ((usdChangeAbs.value / previous) * 100).toFixed(2);
});

const usdPriceUp = computed(() => usdChangeAbs.value >= 0);

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
    return ((sgdChangeAbs.value / previous) * 100).toFixed(2);
});

const sgdPriceUp = computed(() => sgdChangeAbs.value >= 0);

const formatNumber = (value, decimals = 0) => {
    if (!value && value !== 0) return '0';
    return value.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
};

const formatChangeAmount = (value) => {
    if (!value && value !== 0) return '0';
    return Math.round(value).toLocaleString();
};

const formatTime24 = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
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