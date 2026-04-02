<template>
    <div
        class="relative min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 overflow-visible">

        <Loader :show="isGlobalLoading" :message="globalLoadingMessage" />

        <header class="sticky top-0 z-[100] w-full">
            <UserNavbar />
        </header>





        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div
                class="absolute -top-[40%] -right-[20%] w-[80%] h-[80%] bg-gradient-to-br from-orange-500/10 via-amber-500/5 to-transparent rounded-full blur-3xl animate-float">
            </div>
            <div
                class="absolute -bottom-[40%] -left-[20%] w-[80%] h-[80%] bg-gradient-to-tr from-blue-500/10 via-indigo-500/5 to-transparent rounded-full blur-3xl animate-float delay-1000">
            </div>
        </div>

        <div class="relative z-10 flex flex-col w-full min-w-0">

            <div class="sticky top-[64px] z-50 w-full backdrop-blur-md bg-white/70 dark:bg-zinc-900/70
                transition-all duration-300">
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-2">
                    <PublicBreadcrumb :breadcrumbs="breadcrumbs" />
                </div>
            </div>

            <PullToRefresh :message="globalLoadingMessage" />

            <div class="py-3 w-full">
                <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 w-full box-border">
                    <main class="w-full">
                        <slot />
                    </main>
                </div>
            </div>
        </div>

        <footer
            class="hidden sm:block relative z-10 border-t border-slate-200 dark:border-zinc-800 py-8 mt-12 bg-white/30 dark:bg-transparent backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-6">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            MMRatePro • Real-Time Currency Exchange Rates
                        </p>
                        <div class="flex gap-4">
                            <a href="#" class="text-[10px] text-slate-400 hover:text-orange-500 transition">About</a>
                            <a href="#" class="text-[10px] text-slate-400 hover:text-orange-500 transition">Privacy</a>
                            <a href="#" class="text-[10px] text-slate-400 hover:text-orange-500 transition">Terms</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <p class="text-[10px] text-slate-400 font-medium">Systems Operational</p>
                    </div>
                </div>
            </div>
        </footer>
        <MobileBottomNav />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useDarkMode } from '@/Composables/useDarkMode';


const { isDark, toggleDark } = useDarkMode()


const page = usePage()

// --- UI State ---
const isScrolled = ref(false)
const isGlobalLoading = ref(false)
const globalLoadingMessage = ref('Processing...')
const isOnline = ref(navigator.onLine);

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
};

// --- Scroll Logic for Navbar Shadow ---
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20
}

onMounted(() => {

    window.addEventListener('scroll', handleScroll);
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);


    window.matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", (e) => {
            if (!localStorage.getItem("theme")) {
                applyTheme(e.matches);
            }
        });

})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
})

// --- Breadcrumbs ---
const breadcrumbs = computed(() => {
    if (page.props.breadcrumbs && page.props.breadcrumbs.length > 0) {
        return page.props.breadcrumbs
    }
    const currentUrl = page.url
    if (currentUrl === '/') {
        return [{ label: 'Home', route: 'welcome' }]
    }
    return [{ label: 'Home', route: 'welcome' }, { label: 'Current Page' }]
})

// --- Inertia Progress Logic ---
router.on('start', (event) => {
    isGlobalLoading.value = true; // Always start the loader

    const isGet = event.detail.visit.method === 'get';
    const customMessage = event.detail.visit.data?._loader_message;

    if (customMessage) {
        globalLoadingMessage.value = customMessage;
    } else if (isGet) {
        globalLoadingMessage.value = 'Loading Page...';
    } else {
        globalLoadingMessage.value = 'Processing...';
    }
});

router.on('finish', () => {
    isGlobalLoading.value = false;
});
</script>

<style>
/* 1. Global Animations */
@keyframes float {
    0% {
        transform: translate(0, 0);
    }

    50% {
        transform: translate(20px, -15px);
    }

    100% {
        transform: translate(0, 0);
    }
}

.animate-float {
    animation: float 12s ease-in-out infinite;
}

/* Add this to force the sticky context to the top-most level */
:deep(header) {
    position: sticky !important;
    top: 0 !important;
    z-index: 100 !important;
}

/* Ensure nothing in the chain is trapping the scroll */
html,
body,
#app,
.relative.min-h-screen {
    overflow: visible !important;
    height: auto !important;
}
</style>