<template>
    <Link :href="route('user.gold.history', { type: system === 'new' ? 'new_system' : 'traditional' })"
        class="group block">
        <section class="space-y-6">
            <!-- Section Header -->
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div :class="`w-1.5 h-7 rounded-full shadow-[0_0_12px_rgba(245,158,11,0.5)] animate-pulse-slow ${systemColor === 'amber' ? 'bg-gradient-to-b from-amber-500 to-yellow-500' : 'bg-gradient-to-b from-orange-500 to-red-500'
                            }`"></div>
                    </div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-800 dark:text-slate-200">
                            Myanmar Bullion — {{ systemLabel }}
                        </h2>
                        <p class="text-[11px] text-slate-500 font-bold uppercase tracking-tight">
                            1 Kyatthar = {{ weight }} · {{ systemLabel === 'New System' ? 'Premium Purity'
                                : 'Standard Purity' }}
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-950/30 dark:to-yellow-950/30 border border-amber-200 dark:border-amber-800 rounded-full shadow-sm">
                    <div class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></div>
                    <span
                        class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-tighter">Live
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5">
                <div v-for="type in goldTypes" :key="type.id"
                    class="group block bg-white dark:bg-zinc-800/50 border border-slate-200 dark:border-zinc-700 rounded-2xl p-6 transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                    :class="systemColor === 'amber'
                        ? 'hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-amber-500/10'
                        : 'hover:border-orange-300 dark:hover:border-orange-700 hover:shadow-orange-500/10'">

                    <!-- Header -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span :class="`inline-block text-[11px] font-black uppercase tracking-[0.12em] px-2 py-0.5 rounded-full mb-2 ${systemColor === 'amber'
                                ? 'text-amber-700 bg-amber-50 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800'
                                : 'text-orange-700 bg-orange-50 border border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800'
                                }`">
                                {{ systemLabel }}
                            </span>
                            <h3 :class="`text-xl font-black text-slate-900 dark:text-white leading-tight tracking-tight transition-colors ${systemColor === 'amber'
                                ? 'group-hover:text-amber-600 dark:group-hover:text-amber-400'
                                : 'group-hover:text-orange-600 dark:group-hover:text-orange-400'
                                }`">
                                {{ type.name }}
                            </h3>
                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                                {{ type.purity || 'Premium' }} · {{ type.unit || 'Kyatthar' }}
                            </p>
                        </div>
                        <div :class="`w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 group-hover:rotate-12 ${systemColor === 'amber'
                            ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-500'
                            : 'bg-orange-100 dark:bg-orange-900/50 text-orange-500'
                            }`">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>

                    <!-- Main Price Card -->
                    <div
                        class="bg-gradient-to-br from-slate-50 to-white dark:from-zinc-800 dark:to-zinc-800/50 rounded-xl p-5 mb-6 border border-slate-100 dark:border-zinc-700">
                        <div class="flex items-end justify-between flex-wrap gap-4">
                            <!-- Current Price Section -->
                            <div>
                                <span
                                    class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.12em] mb-2">Current
                                    Value</span>
                                <div class="flex items-baseline gap-2">
                                    <span
                                        class="text-3xl font-mono font-black italic text-slate-900 dark:text-white tracking-tight">
                                        {{ formatNumber(currentPrice) }}
                                    </span>
                                    <span
                                        class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">MMK</span>
                                </div>
                                <div class="text-[9px] text-slate-400 mt-1">
                                    per Kyatthar ({{ weight }})
                                </div>
                                <!-- USD/SGD Conversion -->
                                <div v-if="currentPrice > 0" class="flex items-center gap-2 mt-1">
                                    <span class="text-[11px] text-amber-600 dark:text-amber-400 font-mono">
                                        ≈ ${{ formatNumber(currentPrice / (usdMmkRate || 4385)) }} USD
                                    </span>
                                    <span class="text-[11px] text-blue-400 font-mono">
                                        ≈ S${{ formatNumber(currentPrice / (usdMmkRate || 4385) * (sgdRate?.usd_sgd_rate
                                            ||
                                            1.35)) }} SGD
                                    </span>
                                </div>
                            </div>

                            <!-- Daily Change (vs previous day's close) -->
                            <div v-if="currentPrice > 0" class="flex flex-col items-end gap-2">
                                <!-- Change Badge -->
                                <div class="flex items-center gap-2">
                                    <span :class="`font-mono text-[11px] font-black px-3 py-1 rounded-full border ${priceUp
                                        ? 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800'
                                        : changeAbs !== 0
                                            ? 'text-rose-700 bg-rose-50 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800'
                                            : 'text-slate-500 bg-slate-50 border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-700'
                                        }`">
                                        {{ priceUp ? '▲' : changeAbs !== 0 ? '▼' : '—' }}
                                        {{ formatNumber(Math.abs(changeAbs)) }} MMK
                                        ({{ changePct }}%)
                                    </span>
                                </div>

                                <!-- 24h High/Low -->
                                <div v-if="type.high_price || type.low_price" class="flex gap-3 text-[11px]">
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">H: {{
                                        formatNumber(type.high_price || currentPrice) }}</span>
                                    <span class="font-bold text-rose-600 dark:text-rose-400">L: {{
                                        formatNumber(type.low_price || currentPrice) }}</span>
                                </div>

                                <!-- 24h Range Percentage -->
                                <div v-if="type.high_price && type.low_price" class="text-[9px] text-slate-400">
                                    24h Range: {{ ((type.high_price - type.low_price) / type.low_price * 100).toFixed(2)
                                    }}%
                                </div>

                                <!-- Previous Close Info -->
                                <div class="text-[9px] text-slate-400">
                                    vs previous close ({{ formatDate(previousCloseDate) }})
                                </div>
                            </div>
                            <div v-else-if="currentPrice === 0" class="text-[11px] text-slate-400">
                                No price data available
                            </div>
                        </div>
                    </div>

                    <!-- Myanmar Units Grid -->
                    <div v-if="currentPrice > 0" class="mt-4 pt-2">
                        <div class="flex items-center gap-2 mb-4">
                            <span
                                :class="`text-[9px] font-black uppercase tracking-wider ${systemColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-orange-600 dark:text-orange-400'}`">
                                🇲🇲 Myanmar Gold Units
                            </span>
                            <span class="text-[9px] text-slate-400">Complete weight & value guide</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <!-- Kyat -->
                            <div :class="`rounded-xl p-3 text-center transition-all duration-300 hover:scale-105 ${systemColor === 'amber'
                                ? 'bg-amber-50/50 border border-amber-100 dark:bg-amber-900/20 dark:border-amber-800'
                                : 'bg-orange-50/50 border border-orange-100 dark:bg-orange-900/20 dark:border-orange-800'
                                }`">
                                <div class="flex items-center justify-center gap-1 mb-1">
                                    <span class="text-[12px] me-2">🏆</span>
                                    <span
                                        :class="`text-[11px] font-black ${systemColor === 'amber' ? 'text-amber-700 dark:text-amber-400' : 'text-orange-700 dark:text-orange-400'}`">ကျပ်သား</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium">Kyatthar</div>
                                <div class="flex flex-col items-center mt-2">
                                    <span class="text-base font-mono font-black text-slate-800 dark:text-white">
                                        {{ formatNumber(currentPrice) }}
                                    </span>
                                    <span class="text-[9px] text-slate-400">MMK</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    ⚖️ {{ weight }}
                                </div>
                            </div>

                            <!-- Mat -->
                            <div
                                class="bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-3 text-center border border-slate-100 dark:border-zinc-700 transition-all duration-300 hover:scale-105">
                                <div class="flex items-center justify-center gap-1 mb-1">
                                    <span class="text-[12px] me-2">📿</span>
                                    <span class="text-[11px] font-black text-slate-600 dark:text-slate-400">မတ်</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium">Mat</div>
                                <div class="flex flex-col items-center mt-2">
                                    <span class="text-sm font-mono font-black text-slate-700 dark:text-white">
                                        {{ formatNumber(currentPrice / 4) }}
                                    </span>
                                    <span class="text-[9px] text-slate-400">MMK</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    ⚖️ {{ ((weight === '16.329g' ? 16.329 : 16.606) / 4).toFixed(3) }}g
                                </div>
                            </div>

                            <!-- Pae -->
                            <div
                                class="bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-3 text-center border border-slate-100 dark:border-zinc-700 transition-all duration-300 hover:scale-105">
                                <div class="flex items-center justify-center gap-1 mb-1">
                                    <span class="text-[12px] me-2">🪙</span>
                                    <span class="text-[11px] font-black text-slate-600 dark:text-slate-400">ပဲ</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium">Pae</div>
                                <div class="flex flex-col items-center mt-2">
                                    <span class="text-sm font-mono font-black text-slate-700 dark:text-white">
                                        {{ formatNumber(currentPrice / 16) }}
                                    </span>
                                    <span class="text-[9px] text-slate-400">MMK</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    ⚖️ {{ ((weight === '16.329g' ? 16.329 : 16.606) / 16).toFixed(3) }}g
                                </div>
                            </div>

                            <!-- Ywae -->
                            <div
                                class="bg-slate-50/80 dark:bg-zinc-800/50 rounded-xl p-3 text-center border border-slate-100 dark:border-zinc-700 transition-all duration-300 hover:scale-105">
                                <div class="flex items-center justify-center gap-1 mb-1">
                                    <span class="text-[12px] me-2">✨</span>
                                    <span class="text-[11px] font-black text-slate-600 dark:text-slate-400">ရွေး</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-medium">Ywae</div>
                                <div class="flex flex-col items-center mt-2">
                                    <span class="text-sm font-mono font-black text-slate-700 dark:text-white">
                                        {{ formatNumber(currentPrice / 128) }}
                                    </span>
                                    <span class="text-[9px] text-slate-400">MMK</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    ⚖️ {{ ((weight === '16.329g' ? 16.329 : 16.606) / 128).toFixed(3) }}g
                                </div>
                            </div>
                        </div>

                        <!-- Quick Reference Table -->
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                            <div
                                class="flex justify-between items-center py-2 px-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                                <span class="font-bold text-amber-700 dark:text-amber-400">Kyatthar (ကျပ်သား)</span>
                                <span class="font-mono font-bold text-slate-800 dark:text-white">{{
                                    formatNumber(currentPrice) }} MMK</span>
                                <span class="text-slate-400">{{ weight }}</span>
                            </div>
                            <div
                                class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                                <span class="font-bold text-slate-600 dark:text-slate-400">MMK per Gram</span>
                                <span
                                    :class="`font-mono font-bold ${systemColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-orange-600 dark:text-orange-400'}`">
                                    {{ formatNumber(currentPrice / (weight === '16.329g' ? 16.329 : 16.606)) }} MMK/g
                                </span>
                            </div>
                        </div>

                        <!-- Market Insights -->
                        <div
                            class="mt-3 p-3 bg-gradient-to-r from-amber-50/30 to-yellow-50/30 dark:from-amber-900/10 dark:to-yellow-900/10 rounded-lg border border-amber-100 dark:border-amber-800">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[11px]">📊</span>
                                <span
                                    class="text-[9px] font-black text-amber-700 dark:text-amber-400 uppercase tracking-wider">Myanmar
                                    Market Insights</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[9px]">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">1 Kyatthar =</span>
                                    <span class="font-mono font-bold text-slate-700 dark:text-white">{{ weight ===
                                        '16.329g'
                                        ? '16.329g' : '16.606g' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">1 Pae =</span>
                                    <span class="font-mono font-bold text-slate-700 dark:text-white">{{ ((weight ===
                                        '16.329g' ? 16.329 : 16.606) / 16).toFixed(3) }}g</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Trading Spread</span>
                                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">0.5% -
                                        1%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Market Status</span>
                                    <span
                                        class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Data Message -->
                    <div v-else class="text-center py-8 text-slate-400">
                        <p>No price data available yet</p>
                        <p class="text-[10px] mt-1">Prices will appear after the first sync</p>
                    </div>

                    <!-- Footer -->
                    <div
                        class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <div v-if="isFresh && isFresh(type.created_at)" class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">
                                {{ formatDateTime(type.created_at) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span v-if="type.source_type === 'auto'"
                                class="text-[9px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                                Auto-sync
                            </span>
                            <Link
                                :href="route('user.gold.history', { type: system === 'new' ? 'new_system' : 'traditional' })"
                                :class="`text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-all duration-300 hover:gap-2 ${systemColor === 'amber'
                                    ? 'text-amber-500 dark:text-amber-400'
                                    : 'text-orange-500 dark:text-orange-400'
                                    }`">
                                View history
                                <span
                                    class="text-sm transition-transform duration-300 group-hover:translate-x-1">→</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    goldTypes: {
        type: Array,
        default: () => []
    },
    system: {
        type: String,
        required: true
    },
    systemColor: {
        type: String,
        required: true
    },
    systemLabel: {
        type: String,
        required: true
    },
    weight: {
        type: String,
        required: true
    },
    isFresh: {
        type: Function,
        default: null
    },
    previousCloseDate: {
        type: String,
        default: null
    },
    usdMmkRate: {
        type: Number,
        default: 4385
    },
    sgdRate: {
        type: Object,
        default: () => ({ usd_sgd_rate: 1.35 })
    }
});

// Get current price from the gold type data
const currentPrice = computed(() => {
    if (props.goldTypes && props.goldTypes.length > 0) {
        const goldType = props.goldTypes[0];
        return goldType.latest_verified_price || 0;
    }
    return 0;
});

// Get previous day price
const previousPrice = computed(() => {
    if (props.goldTypes && props.goldTypes.length > 0) {
        const goldType = props.goldTypes[0];
        return goldType.previous_day_price || 0;
    }
    return 0;
});

// Calculate change
const changeAbs = computed(() => {
    return currentPrice.value - previousPrice.value;
});

const changePct = computed(() => {
    const previous = previousPrice.value;
    if (previous === 0) return '0.00';
    const pct = (changeAbs.value / previous) * 100;
    return pct.toFixed(2);
});

const priceUp = computed(() => changeAbs.value >= 0);

const formatNumber = (value) => {
    if (!value && value !== 0) return '0';
    return Math.round(value).toLocaleString();
};

const formatDate = (dateString) => {
    if (!dateString) return 'previous day';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const formatDateTime = (dateString) => {
    if (!dateString) return 'No data';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
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

.animate-pulse-slow {
    animation: pulse-slow 5s ease-in-out infinite;
}
</style>