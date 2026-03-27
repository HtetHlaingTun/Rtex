<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    }
});

// 1. Filter out the 'live' item from the main link list
const breadcrumbLinks = computed(() =>
    props.items.filter(item => item.label.toLowerCase() !== 'live')
);

// 2. Check if 'live' exists anywhere in the items array
const hasLiveStatus = computed(() =>
    props.items.some(item => item.label.toLowerCase() === 'live')
);
</script>

<template>
    <nav class="flex py-3 text-gray-700 bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex items-center text-sm font-medium">

            <Link :href="route('dashboard')" class="text-gray-400 hover:text-slate-900 transition flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </Link>

            <div v-for="(item, index) in breadcrumbLinks" :key="index" class="flex items-center">
                <svg class="h-5 w-5 text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>

                <Link v-if="item.href" :href="item.href"
                    class="ml-2 text-gray-400 hover:text-slate-900 uppercase tracking-widest text-[10px] font-black transition-colors">
                    {{ item.label }}
                </Link>
                <span v-else class="ml-2 text-slate-900 font-black uppercase tracking-widest text-[10px]">
                    {{ item.label }}
                </span>
            </div>

            <div class="flex-grow"></div>

            <div v-if="hasLiveStatus"
                class="flex items-center gap-2 bg-white px-2.5 py-1 rounded-lg border border-emerald-100 shadow-sm">
                <div class="relative flex h-1.5 w-1.5">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                </div>
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-600 italic">
                    Live
                </span>
            </div>
        </div>
    </nav>
</template>