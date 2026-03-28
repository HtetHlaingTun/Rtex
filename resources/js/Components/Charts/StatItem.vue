<template>
    <div class="flex flex-col p-3 sm:p-4">
        <span class="text-[10px] font-medium text-slate-400 dark:text-zinc-500 uppercase tracking-wider mb-1">
            {{ label }}
        </span>

        <span :class="[
            valueClass || 'text-slate-900 dark:text-zinc-100',
            'font-mono font-bold leading-tight break-all',
            formattedValue.length > 10 ? 'text-[11px]' : 'text-xs sm:text-sm'
        ]">
            {{ formattedValue }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: String,
    value: [Number, String],
    formatter: Function,
    valueClass: String
})

const formattedValue = computed(() => {
    if (props.value === null || props.value === undefined) return '—'
    return props.formatter(props.value)
})
</script>