<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    backUrl: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
        type: String,
        default: 'default', // default, white, transparent
        validator: (value) => ['default', 'white', 'transparent'].includes(value)
    }
});

const goBack = () => {
    if (props.backUrl) {
        router.visit(props.backUrl);
    } else {
        router.back();
    }
};

const sizeClasses = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12'
};

const variantClasses = {
    default: 'bg-white border border-slate-200 hover:bg-slate-50 text-slate-700',
    white: 'bg-white/90 backdrop-blur-sm border border-slate-100 hover:bg-white text-slate-700',
    transparent: 'bg-transparent hover:bg-slate-100 text-slate-600'
};

const iconSizes = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6'
};
</script>

<template>
    <button @click="goBack" :class="[
        'rounded-full flex items-center justify-center transition-all duration-200 active:scale-95 shadow-sm ',
        sizeClasses[size],
        variantClasses[variant]
    ]">
        <svg :class="iconSizes[size]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
    </button>
</template>