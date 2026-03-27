<template>
    <div class="px-4 py-3 text-center">
        <p class="text-[9px] font-bold uppercase tracking-wider text-[#C0C0BC] dark:text-zinc-600">
            {{ label }}
        </p>
        <p :class="`text-xs font-bold tabular-nums ${valueClass} ${isNumber ? 'text-[#666] dark:text-zinc-400' : ''}`">
            {{ formattedValue }}
        </p>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    label: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    color: {
        type: String,
        default: null,
        validator: (value) => ['emerald', 'rose', null].includes(value)
    },
    isNumber: {
        type: Boolean,
        default: false
    }
})

const valueClass = computed(() => {
    if (props.color === 'emerald') return 'text-emerald-600 dark:text-emerald-400'
    if (props.color === 'rose') return 'text-rose-600 dark:text-rose-400'
    return 'text-[#666] dark:text-zinc-400'
})

const formattedValue = computed(() => {
    if (props.isNumber) return props.value
    return props.value
})
</script>