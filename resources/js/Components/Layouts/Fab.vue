<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    href: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        default: 'plus'
    }
});

const isVisible = ref(true);
let lastScrollY = 0;

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    // Show if scrolling up, hide if scrolling down and moved > 10px
    if (currentScrollY > lastScrollY && currentScrollY > 100) {
        isVisible.value = false;
    } else {
        isVisible.value = true;
    }
    lastScrollY = currentScrollY;
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <div class="fixed bottom-8 right-6 z-[60] transition-all duration-300 transform"
        :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-20 opacity-0'">
        <Link :href="href"
            class="flex items-center justify-center w-14 h-14 bg-slate-900 text-white rounded-2xl shadow-2xl hover:bg-amber-500 hover:scale-110 active:scale-95 transition-all duration-300 group">
            <svg v-if="icon === 'plus'" class="w-7 h-7 transition-transform group-hover:rotate-90" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <svg v-else-if="icon === 'currency'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>

            <span
                class="absolute right-16 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-xl">
                Update Rates
            </span>
        </Link>
    </div>
</template>