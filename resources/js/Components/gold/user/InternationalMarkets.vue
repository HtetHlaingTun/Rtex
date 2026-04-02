<template>
    <section class="space-y-6">
        <div class="flex items-center justify-between px-2">
            <div class="flex items-center gap-3">
                <div class="w-1 h-6 bg-amber-500 rounded-full"></div>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-800">
                        Global Gold Spot
                    </h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">
                        Real-time International Rates (USD)
                    </p>
                </div>
            </div>

            <div class="hidden sm:flex items-center gap-2 px-3 py-1 bg-slate-100 rounded-full">
                <span class="relative flex h-2 w-2">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[9px] font-black text-slate-500 uppercase">Live</span>
            </div>
        </div>

        <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden border border-slate-800">
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-white/[0.05]">

                <Link v-for="type in goldTypes" :key="type.id" :href="route('gold.public-history', type.id)"
                    class="group/item relative flex flex-col p-6 sm:p-8 hover:bg-white/[0.02] transition-all duration-500">
                    <span
                        class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-6 group-hover/item:text-amber-500 transition-colors">
                        {{ type.name }}
                    </span>

                    <div class="flex flex-col mb-2">
                        <div class="flex items-baseline gap-1">
                            <span
                                class="text-3xl font-mono font-black italic text-white tracking-tighter group-hover/item:scale-105 transition-transform duration-300">
                                ${{ type.latestVerifiedPrice ? $formatNumber(type.latestVerifiedPrice.price) : '0.00' }}
                            </span>
                        </div>
                        <span class="text-[9px] text-slate-500 font-bold uppercase mt-2">
                            Per {{ type.unit }}
                        </span>
                    </div>

                    <div
                        class="absolute bottom-4 right-6 opacity-0 group-hover/item:opacity-100 transition-all duration-500 transform translate-y-2 group-hover/item:translate-y-0">
                        <span
                            class="text-amber-500 text-xs font-black uppercase tracking-widest flex items-center gap-1">
                            Details <span class="text-lg">→</span>
                        </span>
                    </div>
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