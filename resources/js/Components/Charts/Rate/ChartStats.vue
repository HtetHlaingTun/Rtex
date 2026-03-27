<template>
    <div
        class="grid grid-cols-4 divide-x divide-[#EBEBEA] dark:divide-zinc-800 border-t border-[#EBEBEA] dark:border-zinc-800 bg-[#FAFAF9] dark:bg-zinc-800/30">
        <div class="px-4 py-3 text-center">
            <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">High</p>
            <p class="text-xs font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                {{ stats.high }}
            </p>
        </div>
        <div class="px-4 py-3 text-center">
            <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">Low</p>
            <p class="text-xs font-bold tabular-nums text-rose-600 dark:text-rose-400">
                {{ stats.low }}
            </p>
        </div>
        <div class="px-4 py-3 text-center">
            <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">Avg</p>
            <p class="text-xs font-bold tabular-nums text-[#666] dark:text-zinc-400">
                {{ stats.avg }}
            </p>
        </div>
        <div class="px-4 py-3 text-center">
            <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">Points</p>
            <p class="text-xs font-bold tabular-nums text-[#666] dark:text-zinc-400">
                {{ pointsCount }}
            </p>
        </div>
    </div>
</template>

<script setup>
defineProps({
    stats: {
        type: Object,
        required: true
    },
    pointsCount: {
        type: Number,
        required: true
    }
})
</script>