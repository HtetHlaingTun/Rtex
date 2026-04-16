<template>
    <GuestLayout>

        <Head title="MMRatePro - Live Exchange Rates & Gold Prices in Myanmar" />

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero Section -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-20 left-10 w-72 h-72 bg-orange-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-96 h-96 bg-emerald-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
                    <div class="text-center">


                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4">
                            Live
                            <span class="bg-gradient-to-r from-orange-400 to-orange-500 bg-clip-text text-transparent">
                                Exchange Rates
                            </span>
                            <br />
                            <span class="bg-gradient-to-r from-yellow-400 to-amber-500 bg-clip-text text-transparent">
                                & Gold Prices
                            </span>
                        </h1>

                        <p class="text-slate-300 max-w-2xl mx-auto text-sm md:text-base">
                            Real-time foreign exchange rates and gold prices in Myanmar Kyat (MMK).
                            Updated every 30 minutes from local banks and global markets.
                        </p>

                        <div class="flex flex-wrap items-center justify-center gap-6 mt-8">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                <span class="text-xs text-slate-300">Live Updates</span>
                            </div>
                            <div class="w-px h-4 bg-slate-600"></div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs text-slate-300">Updated {{ currentTime }}</span>
                            </div>
                            <div class="w-px h-4 bg-slate-600"></div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="text-xs text-slate-300">Every 30 minutes</span>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-wrap items-center justify-center gap-4 mt-10">
                            <Link :href="route('rates.index')"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition-all hover:scale-105">
                                View Exchange Rates
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                            <Link :href="route('goldPage.index')"
                                class="inline-flex items-center gap-2 px-6 py-3 bg-transparent border-2 border-orange-600 text-orange-600 rounded-xl font-bold hover:bg-orange-600 hover:text-white transition-all">
                                View Gold Prices
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-0 left-0 right-0">
                    <svg class="w-full h-12 text-[#F7F7F5] dark:text-zinc-950" preserveAspectRatio="none"
                        viewBox="0 0 1440 54" fill="currentColor">
                        <path d="M0 22L120 16.7C240 11 480 0 720 0C960 0 1200 11 1320 16.7L1440 22V54H0V22Z" />
                    </svg>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-5">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">USD/MMK</span>
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ getUsdRate() }}</p>
                        <p class="text-[9px] text-slate-400 mt-1">US Dollar to Kyat</p>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SGD/MMK</span>
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ getSgdRate() }}</p>
                        <p class="text-[9px] text-slate-400 mt-1">Singapore Dollar to Kyat</p>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">EUR/MMK</span>
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ getEurRate() }}</p>
                        <p class="text-[9px] text-slate-400 mt-1">Euro to Kyat</p>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-lg hover:shadow-xl transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gold
                                (24K)</span>
                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ getGoldPrice() }}</p>
                        <p class="text-[9px] text-slate-400 mt-1">Per Kyatthar (16.329g)</p>
                    </div>
                </div>
            </div>

            <!-- Feature Cards -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Exchange Rates Card -->
                    <Link :href="route('rates.index')"
                        class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-8 hover:shadow-xl hover:border-orange-300 dark:hover:border-orange-700 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-100 to-orange-200 dark:from-orange-900/30 dark:to-orange-800/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2
                                    class="text-xl font-bold text-slate-800 dark:text-white group-hover:text-orange-600 transition-colors">
                                    Exchange Rates</h2>
                                <p class="text-sm text-slate-500">Live currency rates</p>
                            </div>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 mb-4">
                            Track real-time foreign exchange rates for USD, SGD, EUR, THB and more against Myanmar Kyat.
                        </p>
                        <div
                            class="flex items-center gap-2 text-orange-600 font-bold text-sm group-hover:gap-3 transition-all">
                            View all rates
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>

                    <!-- Gold Prices Card -->
                    <Link :href="route('goldPage.index')"
                        class="group bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-8 hover:shadow-xl hover:border-amber-300 dark:hover:border-amber-700 transition-all hover:-translate-y-1">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-100 to-yellow-200 dark:from-amber-900/30 dark:to-yellow-800/30 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2
                                    class="text-xl font-bold text-slate-800 dark:text-white group-hover:text-amber-600 transition-colors">
                                    Gold Prices</h2>
                                <p class="text-sm text-slate-500">Live precious metals</p>
                            </div>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 mb-4">
                            Track live gold prices in MMK, USD, and SGD. 24K, 22K, and 18K rates updated every 30
                            minutes.
                        </p>
                        <div
                            class="flex items-center gap-2 text-amber-600 font-bold text-sm group-hover:gap-3 transition-all">
                            View gold prices
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="border-t border-slate-200 dark:border-zinc-800 py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <p class="text-[9px] text-slate-400">
                        💰 Real-time exchange rates and gold prices. Updated every 30 minutes and 2 minutes accordingly.
                        Actual rates at local money changers may vary by 5-10%.
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, Head } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    rates: { type: Array, default: () => [] },
    gold: { type: Object, default: null }
})

const currentTime = computed(() => {
    return new Date().toLocaleTimeString()
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