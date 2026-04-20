<template>
    <GuestLayout>

        <Head title="MMRatePro - Fuel Prices in Myanmar" />

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <div class="absolute inset-0 opacity-10">
                    <div
                        class="absolute top-20 left-10 w-48 sm:w-72 h-48 sm:h-72 bg-amber-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-64 sm:w-96 h-64 sm:h-96 bg-yellow-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-24">
                    <div class="text-center">


                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white mb-3 sm:mb-4 px-2">
                            Live
                            <span class="bg-gradient-to-r from-yellow-100 to-orange-300 bg-clip-text text-transparent">
                                Fuel
                            </span>


                            <span class="bg-gradient-to-r from-yellow-100 to-orange-300 bg-clip-text text-transparent">
                                Rates
                            </span>

                        </h1>

                        <p class="text-slate-300 max-w-2xl mx-auto text-xs sm:text-sm md:text-base px-4">
                            Real-time fuel prices across Myanmar. Updated daily with the latest rates from local
                            stations.
                        </p>
                    </div>
                </div>

                <div class="absolute bottom-0 left-0 right-0">
                    <svg class="w-full h-8 sm:h-12 text-[#F7F7F5] dark:text-zinc-950" preserveAspectRatio="none"
                        viewBox="0 0 1440 54" fill="currentColor">
                        <path d="M0 22L120 16.7C240 11 480 0 720 0C960 0 1200 11 1320 16.7L1440 22V54H0V22Z" />
                    </svg>
                </div>
            </div>

            <!-- Widget -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <FuelPriceWidget />
            </div>

            <!-- History -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
                <FuelPriceHistory />
            </div>

        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head } from '@inertiajs/vue3'
import FuelPriceWidget from '@/Components/FuelPriceWidget.vue'
import FuelPriceHistory from '@/Components/FuelPriceHistory.vue'
</script>