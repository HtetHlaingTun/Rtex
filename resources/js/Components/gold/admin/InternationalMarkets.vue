<template>
    <section class="space-y-4">
        <div class="flex items-center gap-2.5">
            <span class="block w-[3px] h-5 bg-slate-900 rounded-full flex-shrink-0"></span>
            <span class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-700">
                International Markets
            </span>
            <span class="text-[11px] text-slate-400 font-medium pl-2.5 border-l border-slate-200">
                Prices in USD
            </span>
        </div>

        <div class="bg-slate-900 rounded-[2rem] overflow-hidden border border-white/5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4
                        divide-y sm:divide-y-0 sm:divide-x divide-white/[0.06]">
                <Link v-for="type in goldTypes" :key="type.id" :href="route('gold.history', type.id)"
                    class="group/item relative flex flex-col px-7 py-6 hover:bg-white/[0.04] transition-all">
                    <span class="block text-[9px] font-black uppercase tracking-[0.14em] text-white mb-4">
                        {{ type.name }}
                    </span>
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="font-mono text-sm font-bold text-white">$</span>
                        <span class="font-mono text-[28px] font-black italic text-white tracking-tight leading-none
                                     group-hover/item:text-amber-300 transition-colors">
                            {{ type.latestVerifiedPrice ? $formatNumber(type.latestVerifiedPrice.price) : '0.00' }}
                        </span>
                    </div>
                    <span class="text-[9px] text-white/20 font-medium uppercase tracking-wide">
                        / {{ type.unit }}
                    </span>
                    <span
                        class="absolute top-4 right-4 text-[13px] text-white/10 transition-all
                                 group-hover/item:text-amber-400 group-hover/item:translate-x-0.5 group-hover/item:-translate-y-0.5">
                        ↗
                    </span>
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    goldTypes: Array,
});
</script>