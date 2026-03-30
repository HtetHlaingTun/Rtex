<template>
    <div class="px-4 py-3 flex flex-col items-center sm:items-start">
        <span class="text-[10px] font-sans uppercase tracking-widest text-slate-400 dark:text-zinc-500 mb-1">
            {{ label }}
        </span>

        <div class="flex flex-col items-center sm:items-start leading-tight">
            <span :class="['text-sm font-mono font-black tabular-nums', valueClass]">
                {{ formatter(value) }}
            </span>

            <div v-if="secondaryValue !== undefined" class="mt-0.5">
                <span v-if="isRecordCount" class="text-sm font-sans font-bold text-black dark:text-white">
                    {{ secondaryValue }}
                </span>

                <span v-else :class="['text-sm font-mono font-black tabular-nums', valueClass]">
                    S$ {{ (secondaryValue.toLocaleString(undefined, {
                        minimumFractionDigits: 0, maximumFractionDigits: 0
                    })) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [Number, String],
    secondaryValue: Number,
    isRecordCount: { type: Boolean, default: false },
    formatter: Function,
    valueClass: { type: String, default: 'text-slate-900 dark:text-zinc-100' }
})
</script>