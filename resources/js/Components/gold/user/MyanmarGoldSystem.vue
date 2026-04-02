<template>
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
                <span class="text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-tighter">Live
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5">
            <Link v-for="type in goldTypes" :key="type.id"
                :href="route('user.gold.history', { type: type.system === 'new' ? 'new_system' : 'traditional' })"
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
                            {{ type.purity }} · {{ type.unit }}
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
                        <div>
                            <span
                                class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.12em] mb-2">Current
                                Value</span>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-3xl font-mono font-black italic text-slate-900 dark:text-white tracking-tight">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) :
                                        '—' }}
                                </span>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">MMK</span>
                            </div>
                            <div class="text-[9px] text-slate-400 mt-1">
                                per Kyatthar ({{ weight }})
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-mono">
                                    ≈ ${{ $formatNumber(type.latest_verified_price?.price / (usdMmkRate || 4385)) }} USD
                                </span>
                                <span class="text-[11px] text-blue-400 font-mono">
                                    ≈ S${{ $formatNumber(type.latest_verified_price?.price / (usdMmkRate || 4385) *
                                        (sgdRate?.usd_sgd_rate || 1.35)) }} SGD
                                </span>
                            </div>
                        </div>

                        <div v-if="type.latest_verified_price?.status === 'verified'"
                            class="flex flex-col items-end gap-2">
                            <div class="flex items-center gap-2">
                                <span :class="`font-mono text-[11px] font-black px-3 py-1 rounded-full border ${(type.latest_verified_price.change_percentage || 0) > 0
                                    ? 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800'
                                    : (type.latest_verified_price.change_percentage || 0) < 0
                                        ? 'text-rose-700 bg-rose-50 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800'
                                        : 'text-slate-500 bg-slate-50 border-slate-200 dark:bg-slate-800/50 dark:text-slate-400 dark:border-slate-700'
                                    }`">
                                    {{ (type.latest_verified_price.change_percentage || 0) > 0 ? '▲' :
                                        (type.latest_verified_price.change_percentage || 0) < 0 ? '▼' : '—' }} {{
                                        Math.abs(type.latest_verified_price.change_percentage || 0).toFixed(2) }}%
                                        </span>
                            </div>
                            <div class="flex gap-3 text-[11px]">
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">H: {{
                                    $formatNumber(type.latest_verified_price.high_price) }}</span>
                                <span class="font-bold text-rose-600 dark:text-rose-400">L: {{
                                    $formatNumber(type.latest_verified_price.low_price) }}</span>
                            </div>
                            <div class="text-[9px] text-slate-400">
                                24h Range: {{ ((type.latest_verified_price.high_price -
                                    type.latest_verified_price.low_price) / type.latest_verified_price.low_price *
                                    100).toFixed(2) }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Myanmar Units Grid - No Tooltips -->
                <div class="mt-4 pt-2">
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
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) :
                                        '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                ⚖️ {{ weight === '16.329g' ? '16.329g' : '16.606g' }}
                            </div>
                            <div class="flex items-center justify-center gap-2 mt-1">
                                <span class="text-[8px] text-amber-600 dark:text-amber-400">≈ ${{
                                    $formatNumber(type.latest_verified_price?.price / (usdMmkRate || 4385)) }}</span>
                                <span class="text-[8px] text-blue-400">≈ S${{
                                    $formatNumber(type.latest_verified_price?.price / (usdMmkRate || 4385) *
                                        (sgdRate?.usd_sgd_rate || 1.35)) }}</span>
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
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 4)
                                        : '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                ⚖️ {{ ((weight === '16.329g' ? 16.329 : 16.606) / 4).toFixed(3) }}g
                            </div>
                            <div class="flex items-center justify-center gap-2 mt-1">
                                <span class="text-[8px] text-amber-600">≈ ${{
                                    $formatNumber(type.latest_verified_price?.price / 4 / (usdMmkRate || 4385))
                                    }}</span>
                                <span class="text-[8px] text-blue-400">≈ S${{
                                    $formatNumber(type.latest_verified_price?.price / 4 / (usdMmkRate || 4385) *
                                        (sgdRate?.usd_sgd_rate || 1.35)) }}</span>
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
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 16)
                                        : '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                ⚖️ {{ ((weight === '16.329g' ? 16.329 : 16.606) / 16).toFixed(3) }}g
                            </div>
                            <div class="flex items-center justify-center gap-2 mt-1">
                                <span class="text-[8px] text-amber-600">≈ ${{
                                    $formatNumber(type.latest_verified_price?.price / 16 / (usdMmkRate || 4385))
                                    }}</span>
                                <span class="text-[8px] text-blue-400">≈ S${{
                                    $formatNumber(type.latest_verified_price?.price / 16 / (usdMmkRate || 4385) *
                                        (sgdRate?.usd_sgd_rate || 1.35)) }}</span>
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
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                        128) : '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                ⚖️ {{ ((weight === '16.329g' ? 16.329 : 16.606) / 128).toFixed(3) }}g
                            </div>
                            <div class="flex items-center justify-center gap-2 mt-1">
                                <span class="text-[8px] text-amber-600">≈ ${{
                                    $formatNumber(type.latest_verified_price?.price / 128 / (usdMmkRate || 4385))
                                    }}</span>
                                <span class="text-[8px] text-blue-400">≈ S${{
                                    $formatNumber(type.latest_verified_price?.price / 128 / (usdMmkRate || 4385) *
                                        (sgdRate?.usd_sgd_rate || 1.35)) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reference Table -->
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                        <div
                            class="flex justify-between items-center py-2 px-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                            <span class="font-bold text-amber-700 dark:text-amber-400">Kyatthar (ကျပ်သား)</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-white">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) : '—' }}
                                MMK
                            </span>
                            <span class="text-slate-400">{{ weight }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                            <span class="font-bold text-slate-600 dark:text-slate-400">MMK per Gram</span>
                            <span
                                :class="`font-mono font-bold ${systemColor === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-orange-600 dark:text-orange-400'}`">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / (weight
                                    === '16.329g' ? 16.329 : 16.606)) : '—' }} MMK/g
                            </span>
                        </div>
                        <div
                            class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                            <span class="font-bold text-slate-600 dark:text-slate-400">USD per Gram</span>
                            <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                ${{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                    (weight === '16.329g' ? 16.329 : 16.606) / (usdMmkRate || 4385)) : '—' }}
                            </span>
                        </div>
                        <div
                            class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                            <span class="font-bold text-slate-600 dark:text-slate-400">SGD per Gram</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">
                                S${{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                    (weight === '16.329g' ? 16.329 : 16.606) / (usdMmkRate || 4385) * (sgdRate?.usd_sgd_rate
                                        || 1.35)) : '—' }}
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
                                <span class="font-mono font-bold text-slate-700 dark:text-white">{{ weight === '16.329g'
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
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4 mt-4 border-t border-slate-100 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <div v-if="isFresh(type.latest_verified_price?.created_at)" class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </div>
                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">
                            {{ $formatDateTime(type.latest_verified_price?.created_at || 'N/A') }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span v-if="type.latest_verified_price?.source_type === 'auto'"
                            class="text-[9px] font-black text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">
                            Auto-sync
                        </span>
                        <span :class="`text-[9px] font-black uppercase tracking-widest flex items-center gap-1 transition-all duration-300 hover:gap-2 ${systemColor === 'amber'
                            ? 'text-amber-500 dark:text-amber-400'
                            : 'text-orange-500 dark:text-orange-400'
                            }`">
                            View history
                            <span class="text-sm transition-transform duration-300 group-hover:translate-x-1">→</span>
                        </span>
                    </div>
                </div>
            </Link>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    goldTypes: Array,
    system: String,
    systemColor: String,
    systemLabel: String,
    weight: String,
    isFresh: Function,
    usdMmkRate: { type: Number, default: 4385 },
    sgdRate: { type: Object, default: null },
});

const formatNumber = (value, decimals = 2) => {
    if (!value && value !== 0) return '0';
    return value.toFixed(decimals);
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