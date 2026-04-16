<template>
    <Link :href="route('user.gold.history', { type: system === 'new' ? 'new_system' : 'traditional' })"
        :data-gold="`myanmar-${system}`" :data-gold-system="system" class="group block">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5"
            :class="systemColor === 'amber'
                ? 'hover:border-amber-300 dark:hover:border-amber-700'
                : 'hover:border-orange-300 dark:hover:border-orange-700'">

            <!-- Header -->
            <div class="px-5 py-4 border-b border-slate-100 dark:border-zinc-800"
                :class="systemColor === 'amber' ? 'bg-amber-50/30 dark:bg-amber-950/20' : 'bg-orange-50/30 dark:bg-orange-950/20'">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div
                                :class="`w-1.5 h-5 rounded-full ${systemColor === 'amber' ? 'bg-amber-500' : 'bg-orange-500'}`">
                            </div>
                            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                Myanmar Bullion — {{ systemLabel }}
                            </h2>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">
                            1 Kyatthar = {{ weight }} · {{ systemLabel === 'New System' ? 'Premium Purity'
                                : 'Standard Purity' }}
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-1.5 px-2 py-1 bg-white dark:bg-zinc-800 rounded-full border border-slate-200 dark:border-zinc-700">
                        <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-[8px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">Live</span>
                    </div>
                </div>
            </div>

            <!-- Gold Types -->
            <div v-for="type in goldTypes" :key="type.id"
                class="p-5 border-b border-slate-100 dark:border-zinc-800 last:border-b-0 hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-all">

                <!-- Gold Type Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white"
                            :class="systemColor === 'amber' ? 'group-hover:text-amber-600' : 'group-hover:text-orange-600'">
                            {{ type.name }}
                        </h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            {{ type.purity || 'Premium' }} · {{ type.unit || 'Kyatthar' }}
                        </p>
                    </div>
                    <div
                        :class="`w-10 h-10 rounded-full flex items-center justify-center ${systemColor === 'amber' ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-orange-100 dark:bg-orange-900/30'}`">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Price Card -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4 mb-4">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <!-- Price -->
                        <div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Current Value
                            </div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-mono font-bold text-slate-800 dark:text-white">
                                    {{ formatNumber(currentPrice(type)) }}
                                </span>
                                <span class="text-[9px] font-bold text-slate-400">MMK</span>
                            </div>
                            <div class="text-[8px] text-slate-400 mt-0.5">per Kyatthar ({{ weight }})</div>

                            <!-- USD/SGD Conversion -->
                            <div v-if="currentPrice(type) > 0" class="flex flex-wrap gap-2 mt-1.5">
                                <span class="text-[9px] text-amber-600 dark:text-amber-400">
                                    ${{ formatNumber(currentPrice(type) / (usdMmkRate || 4385)) }} USD
                                </span>
                                <span class="text-[9px] text-blue-400">|</span>
                                <span class="text-[9px] text-blue-500 dark:text-blue-400">
                                    S${{ formatNumber(currentPrice(type) / (usdMmkRate || 4385) * (sgdRate?.usd_sgd_rate
                                        || 1.35)) }} SGD
                                </span>
                            </div>
                        </div>

                        <!-- Change Badge -->
                        <div v-if="currentPrice(type) > 0">
                            <div :class="`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold ${getPriceUp(type)
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800'
                                : getChangeAbs(type) !== 0
                                    ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800'
                                    : 'bg-slate-100 text-slate-500 dark:bg-slate-800/50 dark:text-slate-400 border border-slate-200 dark:border-slate-700'
                                }`">
                                <span>{{ getPriceUp(type) ? '▲' : getChangeAbs(type) !== 0 ? '▼' : '—' }}</span>
                                <span>{{ formatNumber(Math.abs(getChangeAbs(type))) }} MMK</span>
                                <span>({{ getChangePct(type) }}%)</span>
                            </div>
                            <div class="text-[8px] text-slate-400 text-right mt-1">
                                vs {{ formatDate(previousCloseDate) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Myanmar Units Table (New Table Format) -->
                <div v-if="currentPrice(type) > 0">
                    <div class="flex items-center gap-1.5 mb-3">
                        <span class="text-[9px]">🇲🇲</span>
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">Myanmar Gold
                            Units</span>
                    </div>

                    <!-- Table Layout -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-zinc-700">
                                    <th
                                        class="text-left py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Unit</th>
                                    <th
                                        class="text-left py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Burmese</th>
                                    <th
                                        class="text-right py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Value (MMK)</th>
                                    <th
                                        class="text-right py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        Weight</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Kyatthar
                                    </td>
                                    <td class="py-2.5 text-[11px] text-slate-500 dark:text-slate-400">ကျပ်သား</td>
                                    <td
                                        class="py-2.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ formatNumber(currentPrice(type)) }}
                                    </td>
                                    <td class="py-2.5 text-right text-[10px] text-slate-400">{{ weight }}</td>
                                </tr>
                                <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Mat</td>
                                    <td class="py-2.5 text-[11px] text-slate-500 dark:text-slate-400">မတ်</td>
                                    <td
                                        class="py-2.5 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ formatNumber(currentPrice(type) / 4) }}
                                    </td>
                                    <td class="py-2.5 text-right text-[10px] text-slate-400">{{ (getWeight(weight) /
                                        4).toFixed(3) }}g</td>
                                </tr>
                                <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Pae</td>
                                    <td class="py-2.5 text-[11px] text-slate-500 dark:text-slate-400">ပဲ</td>
                                    <td
                                        class="py-2.5 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ formatNumber(currentPrice(type) / 16) }}
                                    </td>
                                    <td class="py-2.5 text-right text-[10px] text-slate-400">{{ (getWeight(weight) /
                                        16).toFixed(3) }}g</td>
                                </tr>
                                <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="py-2.5 text-[11px] font-bold text-slate-700 dark:text-slate-300">Ywae
                                    </td>
                                    <td class="py-2.5 text-[11px] text-slate-500 dark:text-slate-400">ရွေး</td>
                                    <td
                                        class="py-2.5 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ formatNumber(currentPrice(type) / 128) }}
                                    </td>
                                    <td class="py-2.5 text-right text-[10px] text-slate-400">{{ (getWeight(weight) /
                                        128).toFixed(3) }}g</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick Stats Row -->
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div
                            class="flex justify-between items-center py-2 px-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800">
                            <span class="text-[9px] font-bold text-amber-700 dark:text-amber-400">Kyatthar → MMK</span>
                            <span class="text-[10px] font-mono font-bold text-slate-800 dark:text-white">{{
                                formatNumber(currentPrice(type)) }} MMK</span>
                        </div>
                        <div
                            class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-zinc-800 rounded-lg border border-slate-200 dark:border-zinc-700">
                            <span class="text-[9px] font-bold text-slate-600 dark:text-slate-400">MMK per Gram</span>
                            <span
                                :class="`text-[10px] font-mono font-bold ${systemColor === 'amber' ? 'text-amber-600' : 'text-orange-600'}`">
                                {{ formatNumber(currentPrice(type) / getWeight(weight)) }} MMK/g
                            </span>
                        </div>
                    </div>
                </div>

                <!-- No Data -->
                <div v-else class="text-center py-6">
                    <p class="text-sm text-slate-400">No price data available</p>
                    <p class="text-[9px] text-slate-400 mt-1">Prices will appear after first sync</p>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-1.5">
                        <div v-if="isFresh && isFresh(type.created_at)"
                            class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                        <span class="text-[8px] text-slate-400">{{ formatTime(type.created_at) }}</span>
                    </div>
                    <span
                        :class="`text-[8px] font-bold uppercase tracking-wider flex items-center gap-1 ${systemColor === 'amber' ? 'text-amber-500' : 'text-orange-500'}`">
                        View History →
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    goldTypes: { type: Array, default: () => [] },
    system: { type: String, required: true },
    systemColor: { type: String, required: true },
    systemLabel: { type: String, required: true },
    weight: { type: String, required: true },
    isFresh: { type: Function, default: null },
    previousCloseDate: { type: String, default: null },
    usdMmkRate: { type: Number, default: 4385 },
    sgdRate: { type: Object, default: () => ({ usd_sgd_rate: 1.35 }) }
});

const getWeight = (weightStr) => {
    return weightStr === '16.329g' ? 16.329 : 16.606;
};

const getCurrentPrice = (type) => {
    return type.latest_verified_price || 0;
};

const getPreviousPrice = (type) => {
    return type.previous_day_price || 0;
};

const getChangeAbs = (type) => {
    return getCurrentPrice(type) - getPreviousPrice(type);
};

const getChangePct = (type) => {
    const prev = getPreviousPrice(type);
    if (prev === 0) return '0.00';
    return ((getChangeAbs(type) / prev) * 100).toFixed(2);
};

const getPriceUp = (type) => {
    return getChangeAbs(type) >= 0;
};

const formatNumber = (value) => {
    if (!value && value !== 0) return '0';
    return Math.round(value).toLocaleString();
};

const formatDate = (dateString) => {
    if (!dateString) return 'previous day';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const formatTime = (dateString) => {
    if (!dateString) return 'No data';
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};

const currentPrice = (type) => getCurrentPrice(type);
</script>