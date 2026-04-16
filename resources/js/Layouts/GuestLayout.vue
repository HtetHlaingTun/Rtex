<template>

    <Head>
        <title>MMRatePro - Live Exchange Rates & Gold Prices</title>
        <meta name="description"
            content="Real-time USD, SGD, EUR, THB exchange rates to MMK. Live gold prices in Myanmar kyat.">

        <!-- JSON-LD Structured Data for SEO -->
        <meta v-html="jsonLd" />
    </Head>

    <div
        class="relative min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 overflow-visible">

        <!-- Flash Messages with Auto-Dismiss -->
        <div v-if="showSuccessMessage"
            class="fixed top-20 right-4 z-50 p-4 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl shadow-lg transition-all duration-300 backdrop-blur-sm"
            :class="{ 'opacity-0': !showSuccessMessage }">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ successMessageText }}
            </div>
        </div>

        <div v-if="showErrorMessage"
            class="fixed top-20 right-4 z-50 p-4 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-xl shadow-lg transition-all duration-300 backdrop-blur-sm"
            :class="{ 'opacity-0': !showErrorMessage }">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ errorMessageText }}
            </div>
        </div>

        <!-- Loader -->
        <Loader :show="isGlobalLoading" :message="globalLoadingMessage" />

        <!-- Header -->
        <header class="sticky top-0 z-[100] w-full">
            <UserNavbar />
        </header>

        <!-- Animated Background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
            <div
                class="absolute -top-[40%] -right-[20%] w-[80%] h-[80%] bg-gradient-to-br from-orange-500/10 via-amber-500/5 to-transparent rounded-full blur-3xl animate-float">
            </div>
            <div
                class="absolute -bottom-[40%] -left-[20%] w-[80%] h-[80%] bg-gradient-to-tr from-blue-500/10 via-indigo-500/5 to-transparent rounded-full blur-3xl animate-float delay-1000">
            </div>
        </div>

        <!-- Main Content -->
        <div class="relative z-10 flex flex-col w-full min-w-0">
            <!-- Breadcrumb Bar -->
            <div
                class="sticky top-[64px] z-50 w-full backdrop-blur-md bg-white/70 dark:bg-zinc-900/70 border-b border-slate-200/50 dark:border-zinc-800/50 transition-all duration-300">
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-3">
                    <PublicBreadcrumb :breadcrumbs="breadcrumbs" />
                </div>
            </div>

            <PullToRefresh :message="globalLoadingMessage" />

            <div class="py-4 sm:py-6 w-full">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full box-border">
                    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                        <!-- Main Content Area -->
                        <main class="flex-1 w-full min-w-0">
                            <slot />
                        </main>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer - Enhanced -->
        <footer
            class="bg-gradient-to-br from-slate-900 to-slate-800 dark:from-zinc-900 dark:to-zinc-950 text-white mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                    <!-- Brand Column -->
                    <div>
                        <div class="flex items-center gap-2 mb-4">

                            <h3 class="text-lg font-black tracking-tight">MMRatePro </h3>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Real-time exchange rates and gold prices for Myanmar. Updated live from local banks and
                            global markets.
                        </p>
                        <!-- <div class="flex gap-3 mt-4">
                            <a href="https://facebook.com/mmratepro" target="_blank" rel="noopener noreferrer"
                                class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                            <a href="https://twitter.com/mmratepro" target="_blank" rel="noopener noreferrer"
                                class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                        </div> -->
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-amber-500 mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="/privacy"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">Privacy Policy</a>
                            </li>
                            <li><a href="/contact"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">Contact Us</a>
                            </li>
                            <li><a href="/terms" class="text-slate-400 hover:text-white text-sm transition-colors">Terms
                                    of Service</a></li>
                            <li><a href="/blog"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">Blog</a></li>
                        </ul>
                    </div>

                    <!-- Exchange Rates -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-amber-500 mb-4">Exchange Rates</h3>
                        <ul class="space-y-2">
                            <li><a href="/history/USD"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">USD to MMK</a>
                            </li>
                            <li><a href="/history/SGD"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">SGD to MMK</a>
                            </li>
                            <li><a href="/history/EUR"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">EUR to MMK</a>
                            </li>
                            <li><a href="/history/THB"
                                    class="text-slate-400 hover:text-white text-sm transition-colors">THB to MMK</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Newsletter -->
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-amber-500 mb-4">Daily Rate Alerts
                        </h3>
                        <p class="text-slate-400 text-sm mb-3">Get exchange rates delivered to your inbox daily</p>
                        <form @submit.prevent="subscribeEmail" class="flex flex-col gap-2">
                            <div class="relative">
                                <input type="email" v-model="subscriberEmail" required placeholder="Your email address"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <button type="submit" :disabled="subscribing"
                                class="bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 px-4 py-2.5 rounded-xl font-semibold transition-all disabled:opacity-50 shadow-lg shadow-amber-500/20">
                                {{ subscribing ? 'Subscribing...' : 'Subscribe' }}
                            </button>
                        </form>
                        <div v-if="subscribeSuccess" class="mt-2 text-emerald-400 text-xs flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Subscribed successfully!
                        </div>
                        <div v-if="subscribeError" class="mt-2 text-rose-400 text-xs flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ subscribeError }}
                        </div>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="border-t border-slate-800 dark:border-zinc-800 mt-10 pt-8 text-center">
                    <p class="text-slate-500 text-xs">
                        &copy; {{ new Date().getFullYear() }} MMRatePro Currency. All rights reserved.
                    </p>
                    <p class="text-slate-600 text-[10px] mt-2">
                        Real-time exchange rates and gold prices for Myanmar
                    </p>
                </div>
            </div>
        </footer>

        <!-- Mobile Bottom Navigation -->
        <MobileBottomNav />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, provide } from 'vue'
import { router, usePage, Head } from '@inertiajs/vue3'
import { useDarkMode } from '@/Composables/useDarkMode';
import axios from 'axios';
import { useGA4 } from '@/Composables/useGA4';




const { isDark, toggleDark } = useDarkMode()
const { trackFormStart, trackFormSubmit, trackSubscribe, trackScroll, trackClick } = useGA4();
const page = usePage()

// --- Flash Message State ---
const showSuccessMessage = ref(false)
const showErrorMessage = ref(false)
const successMessageText = ref('')
const errorMessageText = ref('')
let successTimer = null
let errorTimer = null

// --- Scroll depth tracking ---
const maxScrollDepth = ref(0);
const scrollThresholds = [25, 50, 75, 90];

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

// JSON-LD Structured Data
const jsonLd = computed(() => {
    const data = {
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
    };

    return `<script type="application/ld+json">${JSON.stringify(data)}<\/script>`;
});

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
        if (link.getAttribute('href') === '#') return;
        trackClick(
            link.innerText?.trim() || link.getAttribute('aria-label') || 'link',
            link.id || null,
            link.className || null,
            link.href
        );
    }
};

// Watch for flash messages
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

// Newsletter State
const subscriberEmail = ref('')
const subscribing = ref(false)
const subscribeSuccess = ref(false)
const subscribeError = ref('')
const isScrolled = ref(false)
const isGlobalLoading = ref(false)
const globalLoadingMessage = ref('Processing...')
const isOnline = ref(navigator.onLine);

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
};

// Newsletter Subscribe
const subscribeEmail = async () => {
    if (!subscriberEmail.value) return;
    trackFormStart('newsletter_subscribe');

    subscribing.value = true
    subscribeSuccess.value = false
    subscribeError.value = ''

    try {
        const response = await axios.post('/subscribe', { email: subscriberEmail.value })
        if (response.data.success) {
            subscribeSuccess.value = true
            trackSubscribe(true);
            trackFormSubmit('newsletter_subscribe', true);
            subscriberEmail.value = ''
            setTimeout(() => { subscribeSuccess.value = false }, 3000)
        } else {
            subscribeError.value = response.data.message || 'Subscription failed'
            trackSubscribe(false);
            trackFormSubmit('newsletter_subscribe', false, subscribeError.value);
        }
    } catch (error) {
        subscribeError.value = error.response?.data?.message || 'Something went wrong'
        trackSubscribe(false);
        trackFormSubmit('newsletter_subscribe', false, subscribeError.value);
    } finally {
        subscribing.value = false
    }
}

// Scroll handling
const handleScroll = () => {
    isScrolled.value = window.scrollY > 20
    trackScrollDepth();
}

let previousPage = '';

const trackPageView = () => {
    const currentPage = window.location.pathname;
    if (currentPage !== previousPage && typeof window !== 'undefined' && window.gtag) {
        window.gtag('config', 'G-ZSY1190X65', {
            page_title: document.title,
            page_location: window.location.href
        });
        previousPage = currentPage;
    }
};

// Breadcrumbs
const breadcrumbs = computed(() => {
    if (page.props.breadcrumbs && page.props.breadcrumbs.length > 0) {
        return page.props.breadcrumbs
    }
    const currentUrl = page.url
    if (currentUrl === '/') {
        return [{ label: 'Home', route: 'welcome' }]
    }
    return [{ label: 'Home', route: 'welcome' }, { label: 'Current Page' }]
});

// Inertia Progress
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
    setTimeout(() => { trackPageView(); }, 100);
});

// Rate tracking
const trackRateClick = (currency, rateType) => {
    if (typeof window !== 'undefined' && window.gtag) {
        window.gtag('event', 'view_item', {
            currency: currency,
            rate_type: rateType,
            engagement_time_msec: 500
        });
    }
};

provide('trackRateClick', trackRateClick);

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    document.addEventListener('click', trackLinkClicks);
    document.addEventListener('focusin', (e) => trackFormInteraction(e, 'footer_newsletter'));
    trackPageView();
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
    document.removeEventListener('click', trackLinkClicks);
    if (successTimer) clearTimeout(successTimer)
    if (errorTimer) clearTimeout(errorTimer)
})
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