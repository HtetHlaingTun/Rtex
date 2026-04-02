<template>
    <section class="space-y-4">
        <div class="flex items-center gap-2.5">
            <span :class="`block w-[3px] h-5 rounded-full flex-shrink-0 ${systemColor === 'amber' ? 'bg-amber-500' :
                systemColor === 'orange' ? 'bg-orange-500' : 'bg-amber-500'
                }`"></span>
            <span class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-700">
                Myanmar Bullion — {{ systemLabel }}
            </span>
            <span class="text-[11px] text-slate-400 font-medium pl-2.5 border-l border-slate-200">
                1 Kyatthar = {{ weight }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:mx-auto gap-3">
            <Link v-for="type in goldTypes" :key="type.id" :href="route('gold.history', type.id)" class="group block bg-white border border-slate-100 rounded-2xl p-6
                         transition-all duration-200 active:scale-[0.99]" :class="systemColor === 'amber'
                            ? 'hover:border-amber-300 hover:shadow-[0_8px_32px_rgba(212,168,67,0.10)]'
                            : 'hover:border-orange-300 hover:shadow-[0_8px_32px_rgba(234,88,12,0.08)]'">

                <!-- Header -->
                <div class="flex justify-between items-start mb-5">
                    <div>
                        <span :class="`inline-block text-[8px] font-black uppercase tracking-[0.12em]
                                     px-2 py-0.5 rounded-full mb-2 ${systemColor === 'amber'
                                ? 'text-amber-700 bg-amber-50 border border-amber-200'
                                : 'text-orange-700 bg-orange-50 border border-orange-200'
                            }`">
                            {{ systemLabel }}
                        </span>
                        <h3 :class="`text-[19px] font-black text-slate-900 leading-tight tracking-tight transition-colors ${systemColor === 'amber'
                            ? 'group-hover:text-amber-600'
                            : 'group-hover:text-orange-500'
                            }`">
                            {{ type.name }}
                        </h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                            {{ type.purity }} · {{ type.unit }}
                        </p>
                    </div>
                    <span :class="`text-slate-300 text-lg leading-none mt-1 transition-all ${systemColor === 'amber'
                        ? 'group-hover:text-amber-400'
                        : 'group-hover:text-orange-400'
                        } group-hover:translate-x-1 group-hover:-translate-y-1`">
                        →
                    </span>
                </div>

                <!-- Main Price Card -->
                <div class="flex items-end justify-between mb-5">
                    <div>
                        <span class="block text-[8px] font-black uppercase tracking-[0.12em] text-slate-400 mb-1.5">
                            Current Value
                        </span>
                        <div class="flex items-baseline gap-1.5">
                            <span class="font-mono text-2xl font-black italic text-slate-900 tracking-tight">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) : '—' }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">MMK</span>
                        </div>
                        <div class="text-[9px] text-slate-400 mt-1">
                            per Kyatthar ({{ weight }})
                        </div>
                    </div>

                    <div v-if="type.latest_verified_price?.status === 'verified'"
                        class="flex flex-col items-end gap-1.5">
                        <span class="font-mono text-[11px] font-black px-2.5 py-1 rounded-lg border" :class="type.latest_verified_price.trend === 'up'
                            ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                            : 'text-rose-700 bg-rose-50 border-rose-200'">
                            {{ type.latest_verified_price.trend === 'up' ? '▲' : '▼' }}
                            {{ $formatNumber(type.latest_verified_price.change_percentage || '0') }}%
                        </span>
                        <div v-if="type.latest_verified_price?.high_price" class="flex flex-col items-end">
                            <span class="font-mono text-[9px] font-bold text-emerald-600">
                                H {{ $formatNumber(type.latest_verified_price.high_price) }}
                            </span>
                            <span class="font-mono text-[9px] font-bold text-rose-600">
                                L {{ $formatNumber(type.latest_verified_price.low_price) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Traditional Units Section with Gram Weights -->
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-2 mb-3">
                        <span :class="`text-[9px] font-black uppercase tracking-wider ${systemColor === 'amber' ? 'text-amber-600' : 'text-orange-600'
                            }`">
                            🇲🇲 Traditional Units
                        </span>
                        <span class="text-[7px] text-slate-400">(with gram weights)</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <!-- Kyat (ကျပ်သား) - Full unit -->
                        <div :class="`rounded-xl p-3 text-center border ${systemColor === 'amber'
                            ? 'bg-amber-50/50 border-amber-100'
                            : 'bg-orange-50/50 border-orange-100'
                            }`">
                            <span :class="`text-[11px] font-black ${systemColor === 'amber' ? 'text-amber-700' : 'text-orange-700'
                                }`">ကျပ်သား</span>
                            <div class="text-[8px] text-slate-500 font-medium mt-0.5">Kyatthar</div>
                            <div class="flex flex-col items-center mt-2">
                                <span class="text-base font-mono font-black text-slate-800">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) :
                                        '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[8px] text-slate-400 mt-1">
                                ⚖️ {{ weight === '16.329g' ? '16.329g' : '16.606g' }}
                            </div>
                        </div>

                        <!-- Pae (ပဲ) - 1/16 of Kyat -->
                        <div class="bg-slate-50/80 rounded-xl p-3 text-center border border-slate-100">
                            <span class="text-[11px] font-black text-slate-600">ပဲ</span>
                            <div class="text-[8px] text-slate-500 font-medium mt-0.5">Pae</div>
                            <div class="flex flex-col items-center mt-2">
                                <span class="text-sm font-mono font-black text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 16)
                                        : '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[8px] text-slate-400 mt-1">
                                ⚖️ {{ (weight === '16.329g' ? 16.329 : 16.606) / 16 | formatNumber(3) }}g
                            </div>
                        </div>

                        <!-- Mat (မတ်) - 1/4 of Kyat -->
                        <div class="bg-slate-50/80 rounded-xl p-3 text-center border border-slate-100">
                            <span class="text-[11px] font-black text-slate-600">မတ်</span>
                            <div class="text-[8px] text-slate-500 font-medium mt-0.5">Mat</div>
                            <div class="flex flex-col items-center mt-2">
                                <span class="text-sm font-mono font-black text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 4)
                                        : '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[8px] text-slate-400 mt-1">
                                ⚖️ {{ (weight === '16.329g' ? 16.329 : 16.606) / 4 | formatNumber(3) }}g
                            </div>
                        </div>

                        <!-- Ywae (ရွေး) - 1/8 of Pae (1/128 of Kyat) -->
                        <div class="bg-slate-50/80 rounded-xl p-3 text-center border border-slate-100">
                            <span class="text-[11px] font-black text-slate-600">ရွေး</span>
                            <div class="text-[8px] text-slate-500 font-medium mt-0.5">Ywae</div>
                            <div class="flex flex-col items-center mt-2">
                                <span class="text-sm font-mono font-black text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                        128) : '—' }}
                                </span>
                                <span class="text-[9px] text-slate-400">MMK</span>
                            </div>
                            <div class="text-[8px] text-slate-400 mt-1">
                                ⚖️ {{ (weight === '16.329g' ? 16.329 : 16.606) / 128 | formatNumber(3) }}g
                            </div>
                        </div>
                    </div>

                    <!-- Complete Reference Table with Gram Weights -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-[8px]">
                            <div class="flex justify-between items-center py-1.5 px-2 bg-slate-50 rounded-lg">
                                <span class="font-bold text-slate-600">1 Kyatthar:</span>
                                <span class="font-mono font-bold text-slate-800">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) :
                                        '—' }} MMK
                                </span>
                                <span class="text-slate-400">({{ weight }})</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 px-2 bg-slate-50 rounded-lg">
                                <span class="font-bold text-slate-600">MMK per Gram:</span>
                                <span :class="`font-mono font-bold ${systemColor === 'amber' ? 'text-amber-600' : 'text-orange-600'
                                    }`">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                        (weight === '16.329g' ? 16.329 : 16.606)) : '—' }} MMK/g
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 px-2 bg-slate-50 rounded-lg">
                                <span class="font-bold text-slate-600">1 Pae (ပဲ):</span>
                                <span class="font-mono font-bold text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 16)
                                        : '—' }} MMK
                                </span>
                                <span class="text-slate-400">({{ (weight === '16.329g' ? 16.329 : 16.606) / 16 |
                                    formatNumber(3) }}g)</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 px-2 bg-slate-50 rounded-lg">
                                <span class="font-bold text-slate-600">1 Mat (မတ်):</span>
                                <span class="font-mono font-bold text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 4)
                                        : '—' }} MMK
                                </span>
                                <span class="text-slate-400">({{ (weight === '16.329g' ? 16.329 : 16.606) / 4 |
                                    formatNumber(3) }}g)</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 px-2 bg-slate-50 rounded-lg">
                                <span class="font-bold text-slate-600">1 Ywae (ရွေး):</span>
                                <span class="font-mono font-bold text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                        128) : '—' }} MMK
                                </span>
                                <span class="text-slate-400">({{ (weight === '16.329g' ? 16.329 : 16.606) / 128 |
                                    formatNumber(3) }}g)</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 px-2 bg-slate-50 rounded-lg">
                                <span class="font-bold text-slate-600">1 Ounce (oz):</span>
                                <span class="font-mono font-bold text-slate-700">
                                    {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price /
                                        (weight === '16.329g' ? 16.329 : 16.606) * 31.1035) : '—' }} MMK
                                </span>
                                <span class="text-slate-400">(31.1035g)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-4 mt-2 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span v-if="isFresh(type.latest_verified_price?.created_at)"
                            class="relative flex h-2 w-2 flex-shrink-0">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">
                            {{ $formatDateTime(type.latest_verified_price?.created_at || 'N/A') }}
                        </span>
                    </div>
                    <span :class="`text-[9px] font-black uppercase tracking-widest transition-all duration-200 ${systemColor === 'amber'
                        ? 'text-transparent group-hover:text-amber-600'
                        : 'text-transparent group-hover:text-orange-500'
                        }`">
                        View history
                    </span>
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
});

// Custom filter for formatting numbers with decimal places
const formatNumber = (value, decimals = 2) => {
    if (!value) return '0';
    return value.toFixed(decimals);
};
</script>