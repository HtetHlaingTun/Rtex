<template>
    <!-- ADD THIS HEAD COMPONENT AT THE VERY TOP -->

    <Head>
        <title>MMRatePro - Live Exchange Rates & Gold Prices</title>
        <meta name="description"
            content="Real-time USD, SGD, EUR, THB exchange rates to MMK. Live gold prices in Myanmar kyat.">

        <!-- JSON-LD Structured Data for SEO -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FinancialService",
            "name": "MMRatePro",
            "description": "Real-time Myanmar Kyat exchange rates and gold prices from local banks",
            "url": "https://luckeymm.online",
            "logo": "https://luckeymm.online/logo.png",
            "sameAs": [
                "https://facebook.com/mmratepro",
                "https://twitter.com/mmratepro"
            ],
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "MM"
            },
            "areaServed": {
                "@type": "Country",
                "name": "Myanmar"
            },
            "currency": "MMK",
            "openingHours": "24/7"
        }
        </script>
    </Head>

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
            <div
                class="sticky top-[64px] z-50 w-full backdrop-blur-md bg-white/70 dark:bg-zinc-900/70 transition-all duration-300">
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-2">
                    <PublicBreadcrumb :breadcrumbs="breadcrumbs" />
                </div>
            </div>

            <PullToRefresh :message="globalLoadingMessage" />

            <div class="py-3 w-full">
                <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 w-full box-border">
                    <div class="flex flex-col md:flex-row gap-6">
                        <!-- Main Content Area -->
                        <main class="flex-1 w-full">
                            <!-- Native Ad Banner Container -->
                            <div id="native-ad-container" class="native-ad-container my-6"></div>



                            <GoogleAnalytics />
                            <slot />
                        </main>


                    </div>
                </div>
            </div>
        </div>

        <!-- Social Bar Container -->
        <div id="social-bar-container" class="fixed bottom-0 left-0 right-0 z-40"></div>

        <footer class="bg-gray-800 text-white py-8 mt-12">
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
import { router, usePage, Head } from '@inertiajs/vue3'
import { useDarkMode } from '@/Composables/useDarkMode';
import axios from 'axios';
import { useGA4 } from '@/Composables/useGA4';

const { isDark, toggleDark } = useDarkMode()
const { trackFormStart, trackFormSubmit, trackSubscribe, trackScroll, trackClick } = useGA4();
const page = usePage()



// --- Flash Message State with Auto-Dismiss ---
const showSuccessMessage = ref(false)
const showErrorMessage = ref(false)
const successMessageText = ref('')
const errorMessageText = ref('')
let successTimer = null
let errorTimer = null

// --- Scroll depth tracking ---
const maxScrollDepth = ref(0);
const scrollThresholds = [25, 50, 75, 90];

// Track scroll depth for GA4
const trackScrollDepth = () => {
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;
    const scrollTop = window.scrollY;
    const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;
    const roundedScroll = Math.round(scrollPercent);

    for (const threshold of scrollThresholds) {
        if (roundedScroll >= threshold && maxScrollDepth.value < threshold) {
            maxScrollDepth.value = threshold;
            trackScroll(threshold, document.title);
        }
    }
};

// Track form interactions
const trackFormInteraction = (event, formType) => {
    const target = event.target;
    if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'FORM') {
        trackFormStart(formType);
    }
};

// Track all link clicks
const trackLinkClicks = (event) => {
    const link = event.target.closest('a');
    if (link && link.href && !link.href.startsWith('javascript:')) {
        // Don't track internal anchor links
        if (link.getAttribute('href') === '#') return;

        trackClick(
            link.innerText?.trim() || link.getAttribute('aria-label') || 'link',
            link.id || null,
            link.className || null,
            link.href
        );
    }
};

// Watch for flash messages from server
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        showSuccessMessage.value = true
        successMessageText.value = flash.success
        if (successTimer) clearTimeout(successTimer)
        successTimer = setTimeout(() => {
            showSuccessMessage.value = false
            successMessageText.value = ''
        }, 5000)
    }

    if (flash?.error) {
        showErrorMessage.value = true
        errorMessageText.value = flash.error
        if (errorTimer) clearTimeout(errorTimer)
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

// --- Newsletter Subscribe Function with GA4 tracking ---
const subscribeEmail = async () => {
    if (!subscriberEmail.value) return;

    // Track form start
    trackFormStart('newsletter_subscribe');

    subscribing.value = true
    subscribeSuccess.value = false
    subscribeError.value = ''

    try {
        const response = await axios.post('/subscribe', {
            email: subscriberEmail.value
        })

        if (response.data.success) {
            subscribeSuccess.value = true
            // Track successful subscription (without sending actual email to GA4)
            trackSubscribe(true);
            trackFormSubmit('newsletter_subscribe', true);
            subscriberEmail.value = ''
            setTimeout(() => {
                subscribeSuccess.value = false
            }, 3000)
        } else {
            subscribeError.value = response.data.message || 'Subscription failed'
            // Track failed subscription
            trackSubscribe(false);
            trackFormSubmit('newsletter_subscribe', false, subscribeError.value);
        }
    } catch (error) {
        subscribeError.value = error.response?.data?.message || 'Something went wrong. Please try again.'
        // Track error
        trackSubscribe(false);
        trackFormSubmit('newsletter_subscribe', false, subscribeError.value);
    } finally {
        subscribing.value = false
    }
}

// --- Load Ads Function ---
const loadAds = () => {
    // Only load ads in production
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('Ads skipped: Local environment')
        return
    }

    // Native Banner Ad
    const nativeScript = document.createElement('script')
    nativeScript.async = true
    nativeScript.setAttribute('data-cfasync', 'false')
    nativeScript.src = 'https://pl29084878.profitablecpmratenetwork.com/bf7ea0869d15921e230d365bc66d753b/invoke.js'
    document.getElementById('native-ad-container')?.appendChild(nativeScript)

    // Social Bar Ad
    const socialScript = document.createElement('script')
    socialScript.src = 'https://pl29087167.profitablecpmratenetwork.com/3b/a4/ce/3ba4ce928386d4ee553ae57212063c1d.js'
    document.getElementById('social-bar-container')?.appendChild(socialScript)
}

// --- Scroll Logic for Navbar Shadow and GA4 tracking ---
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20
    trackScrollDepth(); // Track scroll depth for GA4
}




let previousPage = '';

const trackPageView = () => {
    const currentPage = window.location.pathname;

    // Only send if page actually changed
    if (currentPage !== previousPage && typeof window !== 'undefined' && window.gtag) {
        window.gtag('config', 'G-ZSY1190X65', {
            page_title: document.title,
            page_location: window.location.href
        });
        previousPage = currentPage;
        console.log(`GA4 Page View: ${document.title}`);
    }
};


onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    document.addEventListener('click', trackLinkClicks);
    document.addEventListener('focusin', (e) => trackFormInteraction(e, 'footer_newsletter'));

    // Track initial page view
    trackPageView();

    // Load ads after DOM is ready
    loadAds()
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    document.removeEventListener('click', trackLinkClicks);
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
    // Track page view after navigation completes
    setTimeout(() => {
        trackPageView();
    }, 100);
});

// Track currency rate clicks if they exist on the page
const trackRateClick = (currency, rateType) => {
    if (typeof window !== 'undefined' && window.gtag) {
        window.gtag('event', 'view_item', {
            currency: currency,
            rate_type: rateType,
            engagement_time_msec: 500
        });
    }
};

// Expose rate tracking to child components via provide/inject if needed
import { provide } from 'vue';
provide('trackRateClick', trackRateClick);
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

/* Add padding to prevent content from hiding under social bar */
body {
    padding-bottom: 80px;
}

@media (max-width: 768px) {
    body {
        padding-bottom: 60px;
    }
}

#social-bar-container {
    background: rgba(0, 0, 0, 0.03);
    backdrop-filter: blur(8px);
}
</style>