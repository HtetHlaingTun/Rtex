<script setup>
import FlashMessages from '@/Components/FlashMessages.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    title: String,
    breadcrumbItems: Array
});

const isGlobalLoading = ref(false);
const globalLoadingMessage = ref('Processing...');

// Safe area values
const safeAreaTop = ref('0px')
const safeAreaBottom = ref('0px')

// Get safe area values for native platforms
const getSafeAreaValues = async () => {
    // Check if running in Capacitor native platform
    if (window.Capacitor && window.Capacitor.isNativePlatform()) {
        try {
            const { SafeArea } = await import('@capacitor-community/safe-area')
            const insets = await SafeArea.getSafeAreaInsets()
            safeAreaTop.value = `${insets.top}px`
            safeAreaBottom.value = `${insets.bottom}px`

            // Listen for changes (orientation, keyboard)
            SafeArea.addListener('safeAreaChanged', (insets) => {
                safeAreaTop.value = `${insets.top}px`
                safeAreaBottom.value = `${insets.bottom}px`
            })
        } catch (e) {
            console.log('Safe area plugin not available, using CSS env')
            safeAreaTop.value = 'env(safe-area-inset-top, 0px)'
            safeAreaBottom.value = 'env(safe-area-inset-bottom, 0px)'
        }
    } else {
        // Web fallback
        safeAreaTop.value = 'env(safe-area-inset-top, 0px)'
        safeAreaBottom.value = 'env(safe-area-inset-bottom, 0px)'
    }
}

router.on('start', (event) => {
    isGlobalLoading.value = true;

    const data = event.detail.visit.data;

    if (data && data._loader_message) {
        globalLoadingMessage.value = data._loader_message;
    } else if (event.detail.visit.method === 'get') {
        globalLoadingMessage.value = 'Loading Page...';
    } else {
        globalLoadingMessage.value = 'Please wait...';
    }
});

router.on('finish', () => {
    isGlobalLoading.value = false;
});

onMounted(() => {
    getSafeAreaValues()
})
</script>

<template>
    <div class="min-h-dvh w-full font-sans bg-gray-100 flex flex-col antialiased relative">

        <Head :title="title" />

        <header class="bg-white border-b border-gray-200 sticky top-0 z-[60] w-full box-border shadow-sm"
            :style="{ paddingTop: safeAreaTop }">
            <Navbar :user="$page.props.auth.user" />
        </header>

        <Loader :show="isGlobalLoading" :message="globalLoadingMessage" />

        <div class="flex-1 flex flex-col w-full min-w-0">

            <div v-if="$slots.breadcrumbs" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <slot name="breadcrumbs" />
            </div>

            <PullToRefresh :message="globalLoadingMessage" />

            <div class="py-6 sm:py-8 w-full">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full box-border">
                    <FlashMessages />

                    <header v-if="$slots.header" class="mb-6 sm:mb-8">
                        <slot name="header" />
                    </header>

                    <main class="w-full">
                        <slot />
                    </main>
                </div>
            </div>
        </div>

        <footer class="bg-white border-t border-gray-200 mt-auto w-full" :style="{ paddingBottom: safeAreaBottom }">
            <div class="py-6 text-center text-[10px] uppercase tracking-widest text-gray-400">
                &copy; 2026 Currency Exchange System - v1.0.0
            </div>
        </footer>
    </div>
</template>