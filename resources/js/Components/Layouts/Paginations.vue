<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: Array,
});

/**
 * Cleans up Laravel's default labels (e.g., "&laquo; Previous" to "Prev")
 * specifically for mobile views to save space.
 */
const cleanLabel = (label) => {
    if (label.includes('Previous')) return 'Prev';
    if (label.includes('Next')) return 'Next';
    return label;
};
</script>

<template>
    <div v-if="links.length > 3" class="xs:max-w-5xl xs:mx-auto flex items-center justify-center gap-1.5 xs:gap-2 py-8">
        <template v-for="(link, key) in links" :key="key">

            <template v-if="link.label.includes('Previous') || link.label.includes('Next')">
                <div v-if="link.url === null"
                    class="px-3 xs:px-5 py-2 text-slate-300 text-[10px] font-black uppercase tracking-widest border border-slate-100 rounded-xl cursor-not-allowed opacity-50 select-none"
                    v-html="cleanLabel(link.label)" />

                <Link v-else :href="link.url"
                    class="px-3 xs:px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all active:scale-95 bg-white text-slate-900 border-2 border-slate-900 hover:bg-slate-50 shadow-sm flex items-center justify-center"
                    v-html="cleanLabel(link.label)" />
            </template>

            <template v-else>
                <div v-if="link.url === null"
                    class="hidden xs:flex w-9 h-9 items-center justify-center text-slate-400 text-xs font-black select-none"
                    v-html="link.label" />

                <Link v-else :href="link.url"
                    class="hidden xs:flex w-9 h-9 text-[11px] font-black rounded-xl transition-all active:scale-95 items-center justify-center border"
                    :class="{
                        'bg-slate-900 text-white border-slate-900 shadow-lg scale-110 z-10': link.active,
                        'bg-white text-slate-600 hover:bg-slate-100 border-slate-200': !link.active
                    }" v-html="link.label" />
            </template>

        </template>
    </div>
</template>

<style scoped>
/* Ensure the active button has a nice smooth transition */
.transition-all {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>