<template>
    <div class="flex gap-1 bg-slate-100 dark:bg-zinc-800 p-1 rounded-lg">
        <button v-for="period in periods" :key="period.value" @click="$emit('select', period.value)"
            class="px-3 py-1 text-[11px] font-bold rounded-md transition-all duration-200" :class="[
                active === period.value
                    ? 'bg-white dark:bg-zinc-700 text-slate-900 dark:text-white shadow-sm'
                    : 'text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200'
            ]" :disabled="disabled">
            {{ period.label }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    periods: { type: Array, required: true },
    active: { type: String, required: true },
    disabled: { type: Boolean, default: false }
})

defineEmits(['select'])
</script>