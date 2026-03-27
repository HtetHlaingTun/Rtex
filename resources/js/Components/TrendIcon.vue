<script setup>
import { computed } from 'vue';

const props = defineProps({
    current: [Number, String],
    previous: [Number, String],
    // Added showAmount prop to control text visibility
    showAmount: {
        type: Boolean,
        default: true
    }
});

// Calculate the numerical difference
const difference = computed(() => {
    if (props.previous === null || props.previous === undefined) return 0;
    return Number(props.current) - Number(props.previous);
});

const trend = computed(() => {
    const curr = Number(props.current);
    const prev = Number(props.previous);

    if (props.previous === null || props.previous === undefined || curr === prev) {
        return 'neutral';
    }

    return curr > prev ? 'up' : 'down';
});

const config = computed(() => {
    if (trend.value === 'up') {
        return {
            color: 'text-emerald-500',
            // Simple up arrow path
            icon: 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z'
        };
    }
    if (trend.value === 'down') {
        return {
            color: 'text-rose-500',
            // Simple down arrow path
            icon: 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 112 0v7.586l2.293-2.293a1 1 0 011.414 0z'
        };
    }
    return null;
});
</script>

<template>
    <div v-if="trend !== 'neutral'" :class="config.color"
        class="flex items-center gap-0.5 transition-all duration-300 mt-0.5">

        <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" :d="config.icon" clip-rule="evenodd" />
        </svg>

        <span v-if="showAmount" class="text-[10px] font-mono font-bold tracking-tighter">
            {{ trend === 'up' ? '+' : '' }}{{ $formatMoney(Math.abs(difference)) }}
        </span>
    </div>

    <div v-else class="h-4 flex items-center justify-end pr-1">
        <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
    </div>
</template>