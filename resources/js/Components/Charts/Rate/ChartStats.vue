<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">High (MMK)</div>
            <div class="text-sm font-mono font-bold text-slate-800 dark:text-white">
                {{ formatNumber(stats.high) }}
            </div>
        </div>

        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">Low (MMK)</div>
            <div class="text-sm font-mono font-bold text-slate-800 dark:text-white">
                {{ formatNumber(stats.low) }}
            </div>
        </div>

        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">Avg (MMK)</div>
            <div class="text-sm font-mono font-bold text-slate-800 dark:text-white">
                {{ formatNumber(stats.avg) }}
            </div>
        </div>

        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">Volatility</div>
            <div class="text-sm font-mono font-bold text-slate-800 dark:text-white">
                {{ stats.volatility || '—' }}%
            </div>
        </div>

        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">Days</div>
            <div class="text-sm font-mono font-bold text-slate-800 dark:text-white">
                {{ pointsCount }}
            </div>
            <div class="text-[8px] text-slate-400 mt-1">
                Based on {{ pointsCount }} {{ pointsCount === 1 ? 'day' : 'days' }} of data
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            high: null,
            low: null,
            avg: null,
            volatility: null
        })
    },
    pointsCount: {
        type: Number,
        default: 0
    }
})

const formatNumber = (value) => {
    if (value === null || value === undefined) return '—'
    return Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}
</script>