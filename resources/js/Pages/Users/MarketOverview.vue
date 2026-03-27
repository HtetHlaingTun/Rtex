<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Market Pulse</h2>
            <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Live Updates Active</p>
            </div>
        </div>

        <div class="relative overflow-hidden bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-slate-200">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10">
                <div class="flex justify-between items-center mb-10">
                    <div class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-400">USD / MMK</span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase italic">Mid-Market Rate</span>
                </div>

                <div class="flex flex-col items-center mb-12">
                    <h3 class="text-6xl font-mono font-black italic tracking-tighter">
                        {{ $formatNumber(worldGoldSnapshot.usd_mmk_rate) }}
                    </h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase mt-2 tracking-[0.3em]">Myanmar Kyats</p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-sm">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Market
                                Premium</span>
                            <span class="text-lg font-mono font-black">+2.04%</span>
                        </div>
                        <div class="h-10 w-px bg-white/10"></div>
                        <div class="flex flex-col text-right">
                            <span
                                class="text-[9px] font-black text-slate-500 uppercase tracking-widest text-emerald-400">Effective
                                Rate</span>
                            <span class="text-lg font-mono font-black">
                                {{ $formatNumber(worldGoldSnapshot.usd_mmk_rate * 1.02041) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-white/5">
                        <div class="space-y-1">
                            <p class="text-[8px] text-slate-500 font-bold uppercase">New (16.329g)</p>
                            <p class="text-sm font-black text-slate-200">{{
                                $formatMoney(worldGoldSnapshot.mmk_price_new) }}</p>
                        </div>
                        <div class="space-y-1 text-right">
                            <p class="text-[8px] text-slate-500 font-bold uppercase">Old (16.606g)</p>
                            <p class="text-sm font-black text-slate-200">{{
                                $formatMoney(worldGoldSnapshot.mmk_price_old) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl flex gap-3 items-start">
            <div class="p-2 bg-blue-500 rounded-lg text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-black text-blue-900 uppercase tracking-tight">System Insight</p>
                <p class="text-xs text-blue-700 font-medium">The 2.04% premium accounts for physical logistics and
                    market spread in Yangon.</p>
            </div>
        </div>
    </div>
</template>