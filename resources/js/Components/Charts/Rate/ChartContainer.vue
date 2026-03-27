<template>
    <div class="relative p-5 pt-4">
        <!-- Loading Overlay -->
        <div v-if="loading"
            class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-zinc-900/80 z-10 rounded-lg">
            <div class="flex flex-col items-center gap-2">
                <div class="w-8 h-8 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                <span class="text-xs text-[#999] dark:text-zinc-500">Loading chart data...</span>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="h-[300px] flex items-center justify-center">
            <div class="text-center">
                <svg class="w-12 h-12 mx-auto text-rose-400 dark:text-rose-500 mb-2" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm text-rose-600 dark:text-rose-400">{{ error }}</p>
                <button @click="$emit('retry')"
                    class="mt-3 px-4 py-1.5 text-xs font-medium bg-[#F7F7F5] dark:bg-zinc-800 text-[#666] dark:text-zinc-400 rounded-md hover:bg-[#EBEBEA] dark:hover:bg-zinc-700 transition-colors">
                    Try Again
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!hasData" class="h-[300px] flex items-center justify-center">
            <div class="text-center">
                <svg class="w-12 h-12 mx-auto text-[#C0C0BC] dark:text-zinc-600 mb-2" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-sm text-[#999] dark:text-zinc-500">No data available for this period</p>
            </div>
        </div>

        <!-- Chart Canvas -->
        <div v-else class="overflow-x-auto overflow-y-hidden scrollbar-thin" :class="{ 'cursor-grab': isScrollable }">
            <div :style="{ width: canvasWidth, minWidth: '100%' }" class="h-[300px]">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: null
    },
    hasData: {
        type: Boolean,
        default: false
    },
    isScrollable: {
        type: Boolean,
        default: false
    },
    canvasWidth: {
        type: String,
        default: '100%'
    },
    chartPoints: {
        type: Array,
        default: () => []
    }
})

defineEmits(['retry'])
</script>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    height: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dark .scrollbar-thin::-webkit-scrollbar-track {
    background: #1f2937;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background: #4b5563;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
}

.overflow-x-auto {
    overflow-y: hidden;
}

.cursor-grab {
    cursor: grab;
}

.cursor-grab:active {
    cursor: grabbing;
}
</style>