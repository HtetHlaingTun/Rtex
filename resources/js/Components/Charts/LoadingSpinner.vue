<template>
    <div
        class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-zinc-900/50 z-10 backdrop-blur-[1px]">
        <div class="w-6 h-6 border-2 border-slate-200 dark:border-zinc-700 border-t-transparent animate-spin rounded-full"
            :style="{ borderTopColor: color }"></div>
    </div>
</template>

<script setup>
defineProps({
    color: { type: String, default: '#2563EB' }
})
</script>