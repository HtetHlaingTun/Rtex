<template>
    <GuestLayout>

        <Head :title="meta.title">
            <meta name="description" :content="meta.description">
            <meta name="keywords" :content="meta.keywords">
            <link rel="canonical" :href="route('goldPage.index')" />
        </Head>

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero Section - Responsive -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-amber-900 via-amber-800 to-amber-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <div class="absolute inset-0 opacity-10">
                    <div
                        class="absolute top-20 left-10 w-48 sm:w-72 h-48 sm:h-72 bg-yellow-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-64 sm:w-96 h-64 sm:h-96 bg-amber-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-20">
                    <div class="text-center">


                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white mb-3 sm:mb-4 px-2">
                            Live
                            <span class="bg-gradient-to-r from-yellow-400 to-amber-500 bg-clip-text text-transparent">
                                Gold Prices
                            </span>
                        </h1>

                        <p class="text-amber-100 max-w-2xl mx-auto text-xs sm:text-sm md:text-base px-4">
                            Real-time gold prices in Myanmar Kyat (MMK), US Dollar (USD), and Singapore Dollar (SGD).
                            Track 24K, 22K, and 18K rates updated live.
                        </p>

                        <!-- Stats Row - Mobile Friendly -->
                        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 mt-6 sm:mt-8">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-yellow-400 animate-pulse"></div>
                                <span class="text-[10px] sm:text-xs text-amber-100">Live Updates</span>
                            </div>
                            <div class="w-px h-3 sm:h-4 bg-amber-700"></div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-amber-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-[10px] sm:text-xs text-amber-100">Updated {{ lastUpdated }}</span>
                            </div>
                            <div class="w-px h-3 sm:h-4 bg-amber-700"></div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-amber-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="text-[10px] sm:text-xs text-amber-100">Every 2 min</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Curved Bottom Edge - Responsive -->
                <div class="absolute bottom-0 left-0 right-0">
                    <svg class="w-full h-8 sm:h-12 text-[#F7F7F5] dark:text-zinc-950" preserveAspectRatio="none"
                        viewBox="0 0 1440 54" fill="currentColor">
                        <path d="M0 22L120 16.7C240 11 480 0 720 0C960 0 1200 11 1320 16.7L1440 22V54H0V22Z" />
                    </svg>
                </div>
            </div>

            <!-- Main Content - Responsive -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 pb-12 sm:pb-16">

                <!-- World Gold Market Component -->
                <div class="mb-6 sm:mb-8">
                    <WorldGoldMarket :snapshot="snapshot" :worldGoldSnapshot="worldGoldSnapshot" :sgdRate="sgdRate"
                        :sgdGoldPrice="sgdGoldPrice" :sgdPerGram="sgdPerGram" :sgdKyattharNew="sgdKyattharNew"
                        :sgdKyattharOld="sgdKyattharOld" :usdPerGram="usdPerGram" :usdPerKyattharNew="usdPerKyattharNew"
                        :usdPerKyattharOld="usdPerKyattharOld" :priceFlash="priceFlash" :dayHigh="dayHigh"
                        :dayLow="dayLow" :momentum7d="momentum7d" :premium200d="premium200d"
                        :previousDayUsdPrice="previousDayUsdPrice" :previousDaySgdPrice="previousDaySgdPrice"
                        :previousCloseDate="previousCloseDate" />
                </div>

                <!-- Myanmar Bullion Cards - Responsive Grid -->
                <div class="space-y-6 sm:space-y-8 mt-6 sm:mt-8">
                    <MyanmarGoldSystem :goldTypes="myanmarGoldNew" system="new" systemColor="amber"
                        systemLabel="New System" weight="16.329g" :isFresh="isFresh"
                        :previousDayPrice="previousDayMmkNew" :previousCloseDate="previousCloseDate"
                        :usdMmkRate="snapshot?.usd_mmk_rate || 4385" :sgdRate="sgdRate" />

                    <MyanmarGoldSystem :goldTypes="myanmarGoldOld" system="old" systemColor="orange"
                        systemLabel="Traditional System" weight="16.606g" :isFresh="isFresh"
                        :previousDayPrice="previousDayMmkOld" :previousCloseDate="previousCloseDate"
                        :usdMmkRate="snapshot?.usd_mmk_rate || 4385" :sgdRate="sgdRate" />
                </div>

                <!-- International Markets - Responsive -->
                <div v-if="worldGold.length > 0" class="mt-6 sm:mt-8">
                    <InternationalMarkets :goldTypes="worldGold" />
                </div>

                <!-- Quick Info Card - Mobile Friendly -->
                <div
                    class="mt-6 sm:mt-8 bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-950/20 dark:to-yellow-950/20 rounded-xl p-4 sm:p-6 border border-amber-100 dark:border-amber-900/30">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                        <div
                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600 dark:text-amber-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-white mb-1">Understanding
                                Gold Systems</h3>
                            <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">
                                <strong class="text-amber-600">New System:</strong> 16.329g per Kyatthar (Premium
                                Purity) •
                                <strong class="text-amber-600">Traditional System:</strong> 16.606g per Kyatthar
                                (Standard Purity)
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Disclaimer - Mobile Optimized -->
            <div class="border-t border-slate-200 dark:border-zinc-800 py-6 sm:py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <p class="text-[8px] sm:text-[9px] text-slate-400 leading-relaxed">
                        💰 Live gold prices updated every 2 minutes. Rates are for reference only and may vary by
                        2-3% at local gold shops. Always verify before trading.
                    </p>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import WorldGoldMarket from '@/Components/gold/user/WorldGoldMarket.vue';
import MyanmarGoldSystem from '@/Components/gold/user/MyanmarGoldSystem.vue';
import InternationalMarkets from '@/Components/gold/user/InternationalMarkets.vue';
import axios from 'axios';

const props = defineProps({
    worldGoldSnapshot: Object,
    goldTypes: Array,
    sgdRate: Object,
    previousDayUsdPrice: Number,
    previousDaySgdPrice: Number,
    previousCloseDate: String,
    myanmarGoldNew: Array,
    myanmarGoldOld: Array,
    previousDayMmkNew: Number,
    previousDayMmkOld: Number,
    meta: Object
});

const snapshot = ref(props.worldGoldSnapshot);
const localGoldTypes = ref(props.goldTypes);
const now = ref(new Date());
const priceFlash = ref(false);
let snapshotInterval = null;
let timer;

// Pass through previous day values from props
const previousDayUsdPrice = computed(() => props.previousDayUsdPrice);
const previousDaySgdPrice = computed(() => props.previousDaySgdPrice);
const previousCloseDate = computed(() => props.previousCloseDate);
const previousDayMmkNew = computed(() => props.previousDayMmkNew);
const previousDayMmkOld = computed(() => props.previousDayMmkOld);
const myanmarGoldNew = computed(() => props.myanmarGoldNew);
const myanmarGoldOld = computed(() => props.myanmarGoldOld);
const worldGold = computed(() => localGoldTypes.value?.filter(t => t.category === 'world') || []);

// USD Calculations
const usdPerGram = computed(() => (snapshot.value?.usd_price || 0) / 31.1035);
const usdPerKyattharNew = computed(() => usdPerGram.value * 16.329);
const usdPerKyattharOld = computed(() => usdPerGram.value * 16.606);

// SGD Calculations
const sgdGoldPrice = computed(() => {
    if (!snapshot.value?.usd_price || !props.sgdRate?.usd_sgd_rate) return 0;
    return snapshot.value.usd_price * props.sgdRate.usd_sgd_rate;
});

const sgdPerGram = computed(() => {
    if (!sgdGoldPrice.value) return 0;
    return sgdGoldPrice.value / 31.1035;
});

const sgdKyattharNew = computed(() => {
    if (!sgdGoldPrice.value) return 0;
    const perGram = sgdGoldPrice.value / 31.1035;
    return perGram * 16.329;
});

const sgdKyattharOld = computed(() => {
    if (!sgdGoldPrice.value) return 0;
    const perGram = sgdGoldPrice.value / 31.1035;
    return perGram * 16.606;
});

// Metrics
const dayHigh = computed(() => snapshot.value?.day_high || snapshot.value?.usd_price || 0);
const dayLow = computed(() => snapshot.value?.day_low || snapshot.value?.usd_price || 0);

const momentum7d = computed(() => {
    const currentPrice = snapshot.value?.usd_price || 0;
    const sevenDayOldPrice = snapshot.value?.seven_day_old_price || currentPrice;
    if (sevenDayOldPrice > 0 && currentPrice > 0) {
        const change7d = ((currentPrice - sevenDayOldPrice) / sevenDayOldPrice) * 100;
        return parseFloat(change7d.toFixed(1));
    }
    return 0;
});

const premium200d = computed(() => {
    if (snapshot.value?.usd_price && snapshot.value?.previous_close) {
        const premium = ((snapshot.value.usd_price - snapshot.value.previous_close) / snapshot.value.previous_close) * 100;
        return parseFloat(premium.toFixed(1));
    }
    return 0;
});

// Helper Functions
const fetchLatestSnapshot = async () => {
    try {
        const response = await axios.get(route('api.live-gold'));
        if (response.data?.status === 'success') {
            snapshot.value = response.data;
            if (response.data.gold_types) localGoldTypes.value = response.data.gold_types;
            priceFlash.value = true;
            setTimeout(() => { priceFlash.value = false }, 700);
        }
    } catch (e) {
        console.error('Snapshot refresh failed:', e);
    }
};

const isFresh = (timestamp) => {
    if (!timestamp) return false;
    return (now.value - new Date(timestamp)) < 600000;
};

const lastUpdated = computed(() => new Date().toLocaleTimeString());

onMounted(() => {
    timer = setInterval(() => { now.value = new Date() }, 60000);
    fetchLatestSnapshot();
    snapshotInterval = setInterval(fetchLatestSnapshot, 60000);
});

onUnmounted(() => {
    clearInterval(snapshotInterval);
    clearInterval(timer);
});
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
</style>