<template>
    <div class="flex gap-0.5 bg-[#FAFAF9] dark:bg-zinc-800 p-1 rounded-lg">
        <button v-for="period in periods" :key="period.value" @click="$emit('select', period.value)"
            :disabled="disabled" class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150"
            :class="getButtonClass(period.value)">
            {{ period.label }}
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    periods: {
        type: Array,
        required: true
    },
    active: {
        type: String,
        required: true
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

defineEmits(['select'])

const getButtonClass = (periodValue) => {
    return computed(() => {
        const isActive = props.active === periodValue

        return [
            isActive
                ? 'bg-white dark:bg-zinc-900 text-[#111] dark:text-zinc-100 shadow-sm border border-[#EBEBEA] dark:border-zinc-700'
                : 'text-[#999] dark:text-zinc-500 hover:text-[#333] dark:hover:text-zinc-300',
            props.disabled && 'opacity-50 cursor-not-allowed'
        ]
    })
}
</script>