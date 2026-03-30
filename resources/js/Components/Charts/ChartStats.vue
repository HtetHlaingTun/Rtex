<template>
    <div
        class="grid grid-cols-2 sm:grid-cols-4 divide-x-0 sm:divide-x divide-y sm:divide-y-0 divide-slate-100 dark:divide-zinc-800 border-t border-slate-100 dark:border-zinc-800 mt-4 bg-slate-50/50 dark:bg-zinc-800/20">

        <StatItem label="High" :value="stats.high" :secondary-value="stats.sgd?.high" :formatter="formatter"
            value-class="text-emerald-600 dark:text-emerald-400"
            class="border-b sm:border-b-0 border-r border-slate-100 dark:border-zinc-800" />

        <StatItem label="Low" :value="stats.low" :secondary-value="stats.sgd?.low" :formatter="formatter"
            value-class="text-rose-600 dark:text-rose-400" class="border-b sm:border-b-0" />

        <StatItem label="Avg" :value="stats.avg" :secondary-value="stats.sgd?.avg" :formatter="formatter"
            class="border-r border-slate-100 dark:border-zinc-800" />

        <StatItem label="Records" :value="stats.count" :secondary-value="stats.sgdCount"
            :formatter="(v) => v.toString()" :is-record-count="true" class="sm:border-r-0" />
    </div>
</template>

<script setup>
import StatItem from './StatItem.vue'

defineProps({
    stats: {
        type: Object,
        required: true
    },
    formatter: { type: Function, required: true }
})
</script>