<template>
    <div class="h-[220px] w-full" :class="{ 'overflow-x-auto': isScrollable }">
        <div :style="{ width: canvasWidth }" class="h-full">
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    isScrollable: {
        type: Boolean,
        default: false
    },
    canvasWidth: {
        type: String,
        default: '100%'
    }
})
</script>