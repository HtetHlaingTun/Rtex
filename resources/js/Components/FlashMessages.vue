<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false); // Start as false so it doesn't "blink" on load

const flash = computed(() => page.props.flash);

// Watch for changes in flash props to trigger the alert
watch(flash, (newVal) => {
    if (newVal.success || newVal.error) {
        show.value = true;
        // Auto-hide after 5 seconds for inline alerts
        setTimeout(() => show.value = false, 5000);
    }
}, { deep: true, immediate: true });
</script>

<template>
    <Transition enter-active-class="transform transition ease-out duration-300"
        enter-from-class="-translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show && (flash.success || flash.error)" class="w-full mb-6">
            <div v-if="flash.success"
                class="flex items-center justify-between bg-emerald-50 border border-emerald-200 p-4 rounded-2xl shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="bg-emerald-500 p-1.5 rounded-lg text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-900">{{ flash.success
                    }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div v-if="flash.error"
                class="flex items-center justify-between bg-rose-50 border border-rose-200 p-4 rounded-2xl shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="bg-rose-500 p-1.5 rounded-lg text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest text-rose-900">{{ flash.error }}</span>
                </div>
                <button @click="show = false" class="text-rose-400 hover:text-rose-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>