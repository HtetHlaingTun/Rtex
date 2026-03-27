<template>
    <div class="p-3 text-center">
        <span class="block text-[9px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-widest">
            {{ label }}
        </span>
        <span class="text-xs font-bold tabular-nums" :class="valueClass || 'text-slate-600 dark:text-zinc-300'">
            {{ formattedValue }}
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [Number, null], default: null },
    formatter: { type: Function, required: true },
    valueClass: { type: String, default: '' }
})

const formattedValue = computed(() => {
    if (props.value == null) return '—'
    return props.formatter(props.value)
})
</script>