<template>
    <div class="h-[220px] flex items-center justify-center">
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
</template>

<script setup>
defineProps({
    error: {
        type: String,
        required: true
    }
})

defineEmits(['retry'])
</script>