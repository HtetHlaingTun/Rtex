<script setup>
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    backUrl: {
        type: String,
        default: null
    },
    backText: {
        type: String,
        default: 'Back'
    },
    bordered: {
        type: Boolean,
        default: true
    },
    customClass: {
        type: String,
        default: ''
    },
    position: {
        type: String,
        default: 'top-left',
        validator: (value) => ['top-left', 'top-right', 'bottom-left', 'bottom-right', 'inline'].includes(value)
    },
    // Custom offset values - using standard Tailwind spacing values
    offsetX: {
        type: String,
        default: '4' // Tailwind spacing: 4 = 1rem
    },
    offsetY: {
        type: String,
        default: '4' // Tailwind spacing: 4 = 1rem
    }
});

const goBack = () => {
    if (props.backUrl) {
        router.visit(props.backUrl);
    } else {
        router.back();
    }
};

const positionClasses = computed(() => {
    // Map offset values to Tailwind classes
    const offsetXClass = `left-${props.offsetX}`;
    const offsetYClass = `top-${props.offsetY}`;
    const rightXClass = `right-${props.offsetX}`;
    const bottomYClass = `bottom-${props.offsetY}`;

    const positions = {
        'top-left': `absolute ${offsetYClass} ${offsetXClass} z-10`,
        'top-right': `absolute ${offsetYClass} ${rightXClass} z-10`,
        'bottom-left': `absolute ${bottomYClass} ${offsetXClass} z-10`,
        'bottom-right': `absolute ${bottomYClass} ${rightXClass} z-10`,
        'inline': 'relative'
    };
    return positions[props.position] || 'inline-block';
});
</script>

<template>
    <div :class="[positionClasses, { 'mb-4': position === 'inline' }]">
        <button @click="goBack" :class="[
            // Base Styles & Layout
            'group flex items-center gap-2 px-4 py-2 text-sm font-bold transition-all duration-300 rounded-xl active:scale-95',

            // Light Mode: White bg, slate text
            'bg-white text-slate-700 hover:bg-slate-50',

            // Dark Mode: Zinc bg, subtle text, hover lift
            'dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white',

            // Border Logic: Slate in light, Zinc-800 in dark
            bordered ? 'border border-slate-200 hover:border-slate-300 dark:border-zinc-800 dark:hover:border-zinc-700' : 'border-transparent',

            customClass
        ]">
            <svg class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>

            <span class="tracking-tight">{{ backText }}</span>
        </button>
    </div>
</template>

<style scoped>
.transition-all {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>