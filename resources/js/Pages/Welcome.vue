<template>
    <GuestLayout>

        <Head title="MMRatePro - Live Exchange Rates & Gold Prices in Myanmar" />

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero Section - Enhanced Mobile -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <div class="absolute inset-0 opacity-10">
                    <div
                        class="absolute top-20 left-10 w-48 sm:w-72 h-48 sm:h-72 bg-orange-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-64 sm:w-96 h-64 sm:h-96 bg-emerald-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-24">
                    <div class="text-center">



                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white mb-3 sm:mb-4 px-2">
                            Live
                            <span class="bg-gradient-to-r from-orange-400 to-orange-500 bg-clip-text text-transparent">
                                Exchange Rates
                            </span>

                            <br />
                            <span class="bg-gradient-to-r from-yellow-400 to-amber-500 bg-clip-text text-transparent">
                                & Gold Prices
                            </span>
                            <br />
                            <span class="bg-gradient-to-r from-yellow-100 to-orange-300 bg-clip-text text-transparent">
                                & Fuel Rates
                            </span>
                        </h1>

                        <p class="text-slate-300 max-w-2xl mx-auto text-xs sm:text-sm md:text-base px-4">
                            Real-time foreign exchange rates and gold prices in Myanmar Kyat (MMK).
                            Updated every 30 minutes from local banks and global markets.
                        </p>

                        <!-- Stats Row - Mobile Friendly -->
                        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 mt-6 sm:mt-8">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                <span class="text-[10px] sm:text-xs text-slate-300">Live Updates</span>
                            </div>
                            <div class="w-px h-3 sm:h-4 bg-slate-600"></div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-[10px] sm:text-xs text-slate-300">Updated {{ currentTime }}</span>
                            </div>
                            <div class="w-px h-3 sm:h-4 bg-slate-600"></div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="text-[10px] sm:text-xs text-slate-300">Every 30 min</span>
                            </div>
                        </div>

                        <!-- CTA Buttons - Stack on mobile -->
                        <div
                            class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mt-8 sm:mt-10 px-4">
                            <Link :href="route('rates.index')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition-all hover:scale-105 text-sm sm:text-base">
                                View Exchange Rates
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                            <Link :href="route('goldPage.index')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-transparent border-2 border-orange-600 text-orange-600 rounded-xl font-bold hover:bg-orange-600 hover:text-white transition-all text-sm sm:text-base">
                                View Gold Prices
                            </Link>
                            <Link :href="route('fuel-prices')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 sm:px-6 py-2.5 sm:py-3 bg-transparent border-2 border-yellow-600 text-yellow-100 rounded-xl font-bold hover:bg-yellow-600 hover:text-white transition-all text-sm sm:text-base">
                                View Fuel Prices
                            </Link>

                        </div>
                    </div>
                </div>

                <!-- Wave Divider - Responsive -->
                <div class="absolute bottom-0 left-0 right-0">
                    <svg class="w-full h-8 sm:h-12 text-[#F7F7F5] dark:text-zinc-950" preserveAspectRatio="none"
                        viewBox="0 0 1440 54" fill="currentColor">
                        <path d="M0 22L120 16.7C240 11 480 0 720 0C960 0 1200 11 1320 16.7L1440 22V54H0V22Z" />
                    </svg>
                </div>
            </div>



            <!-- Quick Stats Cards - Responsive Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 sm:-mt-8 relative z-10">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-5 pt-4 sm:pt-5">
                    <!-- USD Card -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1 sm:mb-2">
                            <span
                                class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">USD/MMK</span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-amber-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-lg sm:text-2xl font-bold text-slate-800 dark:text-white">{{ getUsdRate() }}</p>
                        <p class="text-[7px] sm:text-[9px] text-slate-400 mt-0.5 sm:mt-1">US Dollar to Kyat</p>
                    </div>

                    <!-- SGD Card -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1 sm:mb-2">
                            <span
                                class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">SGD/MMK</span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-emerald-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-lg sm:text-2xl font-bold text-slate-800 dark:text-white">{{ getSgdRate() }}</p>
                        <p class="text-[7px] sm:text-[9px] text-slate-400 mt-0.5 sm:mt-1">Singapore Dollar to Kyat</p>
                    </div>

                    <!-- EUR Card -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1 sm:mb-2">
                            <span
                                class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">EUR/MMK</span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-lg sm:text-2xl font-bold text-slate-800 dark:text-white">{{ getEurRate() }}</p>
                        <p class="text-[7px] sm:text-[9px] text-slate-400 mt-0.5 sm:mt-1">Euro to Kyat</p>
                    </div>

                    <!-- Gold Card -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-1 sm:mb-2">
                            <span
                                class="text-[8px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gold(New)/MMK</span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-yellow-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-lg sm:text-2xl font-bold text-slate-800 dark:text-white">{{ getGoldPrice() }}</p>
                        <p class="text-[7px] sm:text-[9px] text-slate-400 mt-0.5 sm:mt-1">Per Kyatthar (16.329g)</p>
                    </div>
                </div>
            </div>



            <!-- Feature Cards - Stack on mobile -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="grid md:grid-cols-2 gap-6 sm:gap-8">
                    <!-- Exchange Rates Card -->
                    <Link :href="route('rates.index')"
                        class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-5 sm:p-8 hover:shadow-xl hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-3 sm:gap-4 mb-3 sm:mb-4">
                            <div
                                class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900/30 dark:to-orange-800/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 sm:w-7 sm:h-7 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2
                                    class="text-base sm:text-xl font-bold text-slate-800 dark:text-white group-hover:text-orange-600 transition-colors">
                                    Exchange Rates</h2>
                                <p class="text-xs sm:text-sm text-slate-500">Live currency rates</p>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-3 sm:mb-4">
                            Track real-time foreign exchange rates for USD, SGD, EUR, THB and more against Myanmar Kyat.
                        </p>
                        <div
                            class="flex items-center gap-2 text-orange-600 font-bold text-xs sm:text-sm group-hover:gap-3 transition-all">
                            View all rates
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 transition-transform group-hover:translate-x-1"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>

                    <!-- Gold Prices Card -->
                    <Link :href="route('goldPage.index')"
                        class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-5 sm:p-8 hover:shadow-xl hover:border-amber-300 dark:hover:border-amber-700 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-3 sm:gap-4 mb-3 sm:mb-4">
                            <div
                                class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-100 to-yellow-200 dark:from-amber-900/30 dark:to-yellow-800/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 sm:w-7 sm:h-7 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2
                                    class="text-base sm:text-xl font-bold text-slate-800 dark:text-white group-hover:text-amber-600 transition-colors">
                                    Gold Prices</h2>
                                <p class="text-xs sm:text-sm text-slate-500">Live precious metals</p>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-3 sm:mb-4">
                            Track live gold prices in MMK, USD, and SGD. 24K, 22K, and 18K rates updated every 30
                            minutes.
                        </p>
                        <div
                            class="flex items-center gap-2 text-amber-600 font-bold text-xs sm:text-sm group-hover:gap-3 transition-all">
                            View gold prices
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 transition-transform group-hover:translate-x-1"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>

                    <!-- Fuel Prices Card -->
                    <Link :href="route('fuel-prices')"
                        class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-5 sm:p-8 hover:shadow-xl hover:border-amber-300 dark:hover:border-amber-700 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-3 sm:gap-4 mb-3 sm:mb-4">
                            <div
                                class="w-10 h-10 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-100 to-yellow-200 dark:from-amber-900/30 dark:to-yellow-800/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 sm:w-7 sm:h-7 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 21v-8a2 2 0 012-2h2a2 2 0 012 2v8m-6 0h6m-6 0v2h6v-2m6-10h3v8h-3m0-8V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v3m8 0v8m0 0v3m-8-3v3m4-7h.01" />
                                </svg>
                            </div>
                            <div>
                                <h2
                                    class="text-base sm:text-xl font-bold text-slate-800 dark:text-white group-hover:text-amber-600 transition-colors">
                                    Fuel Prices
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500">Live fuel prices in Myanmar</p>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-3 sm:mb-4">
                            Track live fuel prices including Octane 92, Octane 95, Diesel, and Premium Diesel. Updated
                            daily.
                        </p>
                        <div
                            class="flex items-center gap-2 text-amber-600 font-bold text-xs sm:text-sm group-hover:gap-3 transition-all">
                            View Fuel Prices
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 transition-transform group-hover:translate-x-1"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Trust Badges - Mobile Friendly -->
            <div class="border-t border-slate-200 dark:border-zinc-800 py-6 sm:py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8 mb-6 sm:mb-8">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="text-[10px] sm:text-xs text-slate-600 dark:text-slate-400">Bank-Grade
                                Data</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span class="text-[10px] sm:text-xs text-slate-600 dark:text-slate-400">Real-Time
                                Updates</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] sm:text-xs text-slate-600 dark:text-slate-400">24/7
                                Availability</span>
                        </div>
                    </div>

                    <!-- Disclaimer - Mobile Optimized -->
                    <div class="text-center">
                        <p class="text-[8px] sm:text-[9px] text-slate-400 leading-relaxed px-2">
                            💰 Real-time exchange rates and gold prices. Updated every 30 minutes and 2 minutes
                            accordingly.
                            Actual rates at local money changers may vary by 5-10%.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, Head } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
    rates: { type: Array, default: () => [] },
    gold: { type: Object, default: null }
})

const currentTime = ref(new Date().toLocaleTimeString())
let timer = null

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = new Date().toLocaleTimeString()
    }, 1000)
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})

const getUsdRate = () => {
    const usd = props.rates?.find(r => r.currency?.code === 'USD')
    return usd ? `≈ ${Math.round((usd.buy_rate + usd.sell_rate) / 2).toLocaleString()}` : '—'
}

const getSgdRate = () => {
    const sgd = props.rates?.find(r => r.currency?.code === 'SGD')
    return sgd ? `≈ ${Math.round((sgd.buy_rate + sgd.sell_rate) / 2).toLocaleString()}` : '—'
}

const getEurRate = () => {
    const eur = props.rates?.find(r => r.currency?.code === 'EUR')
    return eur ? `≈ ${Math.round((eur.buy_rate + eur.sell_rate) / 2).toLocaleString()}` : '—'
}

const getGoldPrice = () => {
    return props.gold?.mmk_price_new ? Math.round(props.gold.mmk_price_new).toLocaleString() : '—'
}
</script>

<style scoped>
/* Smooth animations */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-1000 {
    animation-delay: 1s;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 0.3;
        transform: scale(1);
    }

    50% {
        opacity: 0.6;
        transform: scale(1.05);
    }
}

/* Touch-friendly tap highlights */
@media (max-width: 640px) {
    .group:active {
        transform: scale(0.98);
    }
}
</style>