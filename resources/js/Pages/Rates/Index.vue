<template>
    <GuestLayout>

        <Head :title="meta.title">
            <meta name="description" :content="meta.description">
            <meta name="keywords"
                content="exchange rates, foreign exchange, USD to MMK, SGD to MMK, EUR to MMK, Myanmar currency, live forex rates">
            <link rel="canonical" :href="route('rates.index')" />
        </Head>

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero Section for Rates Page -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <!-- Animated Background -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-20 left-10 w-72 h-72 bg-orange-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-96 h-96 bg-emerald-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
                    <div class="text-center">


                        <!-- Title -->
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4">
                            Live
                            <span class="bg-gradient-to-r from-orange-400 to-orange-500 bg-clip-text text-transparent">
                                Exchange Rates
                            </span>
                        </h1>

                        <!-- Description -->
                        <p class="text-slate-300 max-w-2xl mx-auto text-sm md:text-base">
                            Real-time foreign exchange rates updated every 30 minutes.
                            Track major currencies against Myanmar Kyat (MMK) with live market data.
                        </p>

                        <!-- Stats Row -->
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
                                <span class="text-xs text-slate-300">Updated {{ lastUpdated }}</span>
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
                    </div>
                </div>

                <!-- Curved Bottom Edge -->
                <div class="absolute bottom-0 left-0 right-0">
                    <svg class="w-full h-12 text-[#F7F7F5] dark:text-zinc-950" preserveAspectRatio="none"
                        viewBox="0 0 1440 54" fill="currentColor">
                        <path d="M0 22L120 16.7C240 11 480 0 720 0C960 0 1200 11 1320 16.7L1440 22V54H0V22Z" />
                    </svg>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Currencies</span>
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ rates.length }}</p>
                        <p class="text-[9px] text-slate-400 mt-1">Active currencies</p>
                    </div>

                    <div
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow">
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
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow">
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
                        class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow">
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
                </div>
            </div>

            <!-- Exchange Rates Component -->
            <CurrencyExchangeList :rates="rates" :loading="false" />

            <!-- FAQ Section for SEO -->
            <!-- <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-6 text-center">
                    Frequently Asked Questions
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-bold text-slate-800 dark:text-white mb-2">How are exchange rates calculated?
                        </h3>
                        <p class="text-sm text-slate-500">Our rates use real-time USD/MMK from local banks combined with
                            global USD/Currency pairs from Yahoo Finance.</p>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-bold text-slate-800 dark:text-white mb-2">How often are rates updated?</h3>
                        <p class="text-sm text-slate-500">Exchange rates are automatically updated every 30 minutes
                            during market hours.</p>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-bold text-slate-800 dark:text-white mb-2">Where do you get your data?</h3>
                        <p class="text-sm text-slate-500">We source rates from major Myanmar banks (KBZ, CB, Yoma, AYA)
                            and global forex markets via Yahoo Finance.</p>
                    </div>
                    <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                        <h3 class="font-bold text-slate-800 dark:text-white mb-2">Are these rates accurate for trading?
                        </h3>
                        <p class="text-sm text-slate-500">Rates are for reference only. Actual exchange rates at local
                            money changers may vary by 5-10%.</p>
                    </div>
                </div>
            </div> -->
        </div>
    </GuestLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CurrencyExchangeList from '@/Components/Currency/user/CurrencyExchangeList.vue';

const props = defineProps({
    rates: {
        type: Array,
        default: () => []
    },
    meta: {
        type: Object,
        default: () => ({
            title: 'Live Exchange Rates - USD, SGD, EUR to MMK | MMRatePro',
            description: 'Real-time foreign exchange rates in Myanmar Kyat (MMK). Track USD, SGD, EUR, THB and other major currencies with live updates every 30 minutes.'
        })
    }
});

const lastUpdated = computed(() => {
    return new Date().toLocaleTimeString();
});

const getUsdRate = () => {
    const usd = props.rates.find(r => r.currency?.code === 'USD');
    return usd ? `≈ ${Math.round((usd.buy_rate + usd.sell_rate) / 2).toLocaleString()}` : '—';
};

const getSgdRate = () => {
    const sgd = props.rates.find(r => r.currency?.code === 'SGD');
    return sgd ? `≈ ${Math.round((sgd.buy_rate + sgd.sell_rate) / 2).toLocaleString()}` : '—';
};

const getEurRate = () => {
    const eur = props.rates.find(r => r.currency?.code === 'EUR');
    return eur ? `≈ ${Math.round((eur.buy_rate + eur.sell_rate) / 2).toLocaleString()}` : '—';
};
</script>