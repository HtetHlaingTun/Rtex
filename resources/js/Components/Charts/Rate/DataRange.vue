<template>
    <div class="hidden sm:block">
        <span
            class="text-[10px] text-[#C0C0BC] dark:text-zinc-600 border-l border-[#EBEBEA] dark:border-zinc-800 pl-2 ml-1">
            {{ range.start }} → {{ range.end }}
        </span>
    </div>
</template>

<script setup>
defineProps({
    range: {
        type: Object,
        required: true,
        validator: (value) => 'start' in value && 'end' in value
    }
})
</script>