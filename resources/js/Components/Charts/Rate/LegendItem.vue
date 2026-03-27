<template>
    <div class="flex items-center gap-1">
        <span :class="`w-2 h-2 rounded-full bg-${color}-500 dark:bg-${color}-400`"></span>
        <span class="text-xs text-[#999] dark:text-zinc-500 ml-1">{{ label }}</span>
    </div>
</template>

<script setup>
defineProps({
    color: {
        type: String,
        required: true,
        validator: (value) => ['emerald', 'rose', 'blue', 'amber'].includes(value)
    },
    label: {
        type: String,
        required: true
    }
})
</script>