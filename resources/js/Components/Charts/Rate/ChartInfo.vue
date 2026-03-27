<template>
    <div class="hidden sm:block">
        <span
            class="text-[10px] text-[#C0C0BC] dark:text-zinc-600 border-l border-[#EBEBEA] dark:border-zinc-800 pl-2 ml-1">
            {{ info }}
        </span>
    </div>
</template>

<script setup>
defineProps({
    info: {
        type: String,
        required: true
    }
})
</script>