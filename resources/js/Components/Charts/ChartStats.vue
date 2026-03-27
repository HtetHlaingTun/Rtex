<template>
    <div
        class="grid grid-cols-4 divide-x divide-slate-100 dark:divide-zinc-800 border-t border-slate-100 dark:border-zinc-800 mt-4 bg-slate-50/50 dark:bg-zinc-800/20">
        <StatItem label="High" :value="stats.high" :formatter="formatter"
            value-class="text-emerald-600 dark:text-emerald-400" />
        <StatItem label="Low" :value="stats.low" :formatter="formatter"
            value-class="text-rose-600 dark:text-rose-400" />
        <StatItem label="Avg" :value="stats.avg" :formatter="formatter" />
        <StatItem label="Count" :value="stats.count" :formatter="(v) => v.toString()" />
    </div>
</template>

<script setup>
import StatItem from './StatItem.vue'

defineProps({
    stats: {
        type: Object,
        required: true,
        validator: (value) => {
            return ['high', 'low', 'avg', 'count'].every(key => key in value)
        }
    },
    formatter: { type: Function, required: true }
})
</script>