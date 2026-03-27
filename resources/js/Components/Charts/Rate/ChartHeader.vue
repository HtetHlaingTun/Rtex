<template>
    <div class="flex justify-between items-center gap-3 flex-wrap p-5 pb-0">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                <span class="text-xs text-[#999] dark:text-zinc-500 ml-1">Buy Rate</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                <span class="text-xs text-[#999] dark:text-zinc-500 ml-1">Sell Rate</span>
            </div>
            <div v-if="chartInfo" class="hidden sm:block">
                <span
                    class="text-[10px] text-[#C0C0BC] dark:text-zinc-600 border-l border-[#EBEBEA] dark:border-zinc-800 pl-2 ml-1">
                    {{ chartInfo }}
                </span>
            </div>
            <div v-if="dateRange" class="hidden sm:block">
                <span
                    class="text-[10px] text-[#C0C0BC] dark:text-zinc-600 border-l border-[#EBEBEA] dark:border-zinc-800 pl-2 ml-1">
                    {{ dateRange.start }} → {{ dateRange.end }}
                </span>
            </div>
        </div>

        <!-- Period Selector -->
        <div class="flex gap-0.5 bg-[#FAFAF9] dark:bg-zinc-800 p-1 rounded-lg">
            <button v-for="period in periods" :key="period.value" @click="$emit('period-change', period.value)"
                :disabled="loading" class="px-3 py-1 text-xs font-medium rounded-md transition-all duration-150" :class="[
                    activePeriod === period.value
                        ? 'bg-white dark:bg-zinc-900 text-[#111] dark:text-zinc-100 shadow-sm border border-[#EBEBEA] dark:border-zinc-700'
                        : 'text-[#999] dark:text-zinc-500 hover:text-[#333] dark:hover:text-zinc-300'
                ]">
                {{ period.label }}
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    chartInfo: {
        type: String,
        default: null
    },
    periods: {
        type: Array,
        required: true
    },
    activePeriod: {
        type: String,
        required: true
    },
    loading: {
        type: Boolean,
        default: false
    },
    dateRange: {
        type: Object,
        default: null
    }
})

defineEmits(['period-change'])
</script>