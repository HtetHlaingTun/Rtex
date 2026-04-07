<template>

    <div
        class="relative min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 overflow-visible">



        <!-- Flash Messages with Auto-Dismiss -->
        <div v-if="showSuccessMessage"
            class="fixed top-20 right-4 z-50 p-4 bg-green-100 text-green-700 rounded-lg shadow-lg transition-all duration-300"
            :class="{ 'opacity-0': !showSuccessMessage }">
            {{ successMessageText }}
        </div>

        <div v-if="showErrorMessage"
            class="fixed top-20 right-4 z-50 p-4 bg-red-100 text-red-700 rounded-lg shadow-lg transition-all duration-300"
            :class="{ 'opacity-0': !showErrorMessage }">
            {{ errorMessageText }}
        </div>



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

        <footer class="bg-gray-800 text-white py-8 mt-12 p-12">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid md:grid-cols-4 gap-8">
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
                            <li><a href="/blog" class="hover:text-white">Blog</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Exchange Rates</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="/history/USD" class="hover:text-white">USD to MMK</a></li>
                            <li><a href="/history/SGD" class="hover:text-white">SGD to MMK</a></li>
                            <li><a href="/history/EUR" class="hover:text-white">EUR to MMK</a></li>
                            <li><a href="/history/THB" class="hover:text-white">THB to MMK</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Daily Rate Alerts</h3>
                        <p class="text-gray-400 text-sm mb-3">Get exchange rates delivered to your inbox daily</p>
                        <form @submit.prevent="subscribeEmail" class="flex flex-col gap-2">
                            <input type="email" v-model="subscriberEmail" required placeholder="Your email address"
                                class="px-3 py-2 rounded bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <button type="submit" :disabled="subscribing"
                                class="bg-amber-500 hover:bg-amber-600 px-4 py-2 rounded font-semibold transition disabled:opacity-50">
                                {{ subscribing ? 'Subscribing...' : 'Subscribe' }}
                            </button>
                        </form>
                        <div v-if="subscribeSuccess" class="mt-2 text-green-400 text-sm">
                            ✅ Subscribed successfully!
                        </div>
                        <div v-if="subscribeError" class="mt-2 text-red-400 text-sm">
                            ❌ {{ subscribeError }}
                        </div>
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useDarkMode } from '@/Composables/useDarkMode';
import axios from 'axios';

const { isDark, toggleDark } = useDarkMode()
const page = usePage()

// --- Flash Message State with Auto-Dismiss ---
const showSuccessMessage = ref(false)
const showErrorMessage = ref(false)
const successMessageText = ref('')
const errorMessageText = ref('')
let successTimer = null
let errorTimer = null

// Watch for flash messages from server
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showSuccessMessage.value = true
        successMessageText.value = flash.success

        // Clear existing timer
        if (successTimer) clearTimeout(successTimer)

        // Auto-dismiss after 5 seconds
        successTimer = setTimeout(() => {
            showSuccessMessage.value = false
            successMessageText.value = ''
        }, 5000)
    }

    if (flash?.error) {
        showErrorMessage.value = true
        errorMessageText.value = flash.error

        // Clear existing timer
        if (errorTimer) clearTimeout(errorTimer)

        // Auto-dismiss after 5 seconds
        errorTimer = setTimeout(() => {
            showErrorMessage.value = false
            errorMessageText.value = ''
        }, 5000)
    }
}, { deep: true, immediate: true })

// --- Newsletter State ---
const subscriberEmail = ref('')
const subscribing = ref(false)
const subscribeSuccess = ref(false)
const subscribeError = ref('')

// --- UI State ---
const isScrolled = ref(false)
const isGlobalLoading = ref(false)
const globalLoadingMessage = ref('Processing...')
const isOnline = ref(navigator.onLine);

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
};

// --- Newsletter Subscribe Function ---
const subscribeEmail = async () => {
    if (!subscriberEmail.value) return;

    subscribing.value = true
    subscribeSuccess.value = false
    subscribeError.value = ''

    try {
        const response = await axios.post('/subscribe', {
            email: subscriberEmail.value
        })

        if (response.data.success) {
            subscribeSuccess.value = true
            subscriberEmail.value = ''
            setTimeout(() => {
                subscribeSuccess.value = false
            }, 3000)
        } else {
            subscribeError.value = response.data.message || 'Subscription failed'
        }
    } catch (error) {
        subscribeError.value = error.response?.data?.message || 'Something went wrong. Please try again.'
    } finally {
        subscribing.value = false
    }
}

// --- Scroll Logic for Navbar Shadow ---
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);

    // Cleanup timers on unmount
    if (successTimer) clearTimeout(successTimer)
    if (errorTimer) clearTimeout(errorTimer)
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
    isGlobalLoading.value = true;
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

:deep(header) {
    position: sticky !important;
    top: 0 !important;
    z-index: 100 !important;
}

html,
body,
#app,
.relative.min-h-screen {
    overflow: visible !important;
    height: auto !important;
}
</style>