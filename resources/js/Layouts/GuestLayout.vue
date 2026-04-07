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
                        <GoogleAnalytics />
                        <slot />
                    </main>
                </div>
            </div>
        </div>

        <footer class="bg-gray-800 text-white py-8 mt-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">MMRatePro Currency</h3>
                        <p class="text-gray-400">Real-time exchange rates for Myanmar</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="/privacy" class="hover:text-white">Privacy Policy</a></li>
                            <li><a href="/contact" class="hover:text-white">Contact Us</a></li>
                            <li><a href="/terms" class="hover:text-white">Terms of Service</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contact</h3>
                        <p class="text-gray-400">Email: support@luckeymm.online</p>
                    </div>
                </div>
                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} MMRatePro Currency. All rights reserved.</p>
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