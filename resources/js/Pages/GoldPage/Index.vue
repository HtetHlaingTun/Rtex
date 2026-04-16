<template>
    <GuestLayout>

        <Head :title="meta.title">
            <meta name="description" :content="meta.description">
            <meta name="keywords" :content="meta.keywords">
            <link rel="canonical" :href="route('goldPage.index')" />
        </Head>

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero Section -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-amber-900 via-amber-800 to-amber-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-20 left-10 w-72 h-72 bg-yellow-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-96 h-96 bg-amber-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20">
                    <div class="text-center">


                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-4">
                            Live
                            <span
                                class="bg-gradient-to-r from-yellow-400 to-amber-500 bg-clip-text text-transparent">Gold
                                Prices</span>
                        </h1>

                        <p class="text-amber-100 max-w-2xl mx-auto text-sm md:text-base">
                            Real-time gold prices in Myanmar Kyat (MMK), US Dollar (USD), and Singapore Dollar (SGD).
                            Track 24K, 22K, and 18K rates updated live.
                        </p>

                        <div class="flex flex-wrap items-center justify-center gap-6 mt-8">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></div>
                                <span class="text-xs text-amber-100">Live Updates</span>
                            </div>
                            <div class="w-px h-4 bg-amber-700"></div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs text-amber-100">Updated {{ lastUpdated }}</span>
                            </div>
                            <div class="w-px h-4 bg-amber-700"></div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="text-xs text-amber-100">Every 2 minutes</span>
                            </div>
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

            <!-- Main Content - SAME AS WELCOME.VUE -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16">

                <!-- Use the exact same WorldGoldMarket component as Welcome.vue -->
                <WorldGoldMarket :snapshot="snapshot" :worldGoldSnapshot="worldGoldSnapshot" :sgdRate="sgdRate"
                    :sgdGoldPrice="sgdGoldPrice" :sgdPerGram="sgdPerGram" :sgdKyattharNew="sgdKyattharNew"
                    :sgdKyattharOld="sgdKyattharOld" :usdPerGram="usdPerGram" :usdPerKyattharNew="usdPerKyattharNew"
                    :usdPerKyattharOld="usdPerKyattharOld" :priceFlash="priceFlash" :dayHigh="dayHigh" :dayLow="dayLow"
                    :momentum7d="momentum7d" :premium200d="premium200d" :previousDayUsdPrice="previousDayUsdPrice"
                    :previousDaySgdPrice="previousDaySgdPrice" :previousCloseDate="previousCloseDate" />

                <!-- Myanmar Bullion Cards -->
                <div class="space-y-12 mt-8">
                    <MyanmarGoldSystem :goldTypes="myanmarGoldNew" system="new" systemColor="amber"
                        systemLabel="New System" weight="16.329g" :isFresh="isFresh"
                        :previousDayPrice="previousDayMmkNew" :previousCloseDate="previousCloseDate"
                        :usdMmkRate="snapshot?.usd_mmk_rate || 4385" :sgdRate="sgdRate" />

                    <MyanmarGoldSystem :goldTypes="myanmarGoldOld" system="old" systemColor="orange"
                        systemLabel="Old System" weight="16.606g" :isFresh="isFresh"
                        :previousDayPrice="previousDayMmkOld" :previousCloseDate="previousCloseDate"
                        :usdMmkRate="snapshot?.usd_mmk_rate || 4385" :sgdRate="sgdRate" />
                </div>

                <!-- International Markets -->
                <InternationalMarkets v-if="worldGold.length > 0" :goldTypes="worldGold" />

                <!-- FAQ Section -->
                <!-- <div class="mt-12 pt-8 border-t border-slate-200 dark:border-zinc-800">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-6 text-center">
                        Frequently Asked Questions
                    </h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-2">What is the difference between New
                                and Old gold systems?</h3>
                            <p class="text-sm text-slate-500">The New System uses 16.329g per Kyatthar (premium purity),
                                while the Old System uses 16.606g per Kyatthar (standard purity).</p>
                        </div>
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-2">How often are gold prices updated?
                            </h3>
                            <p class="text-sm text-slate-500">Gold prices are automatically updated every 30 minutes
                                during market hours.</p>
                        </div>
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-2">Where do your gold prices come
                                from?</h3>
                            <p class="text-sm text-slate-500">We source prices from global gold markets (LBMA) and local
                                Myanmar gold markets.</p>
                        </div>
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-slate-200 dark:border-zinc-800">
                            <h3 class="font-bold text-slate-800 dark:text-white mb-2">What does 24K, 22K, 18K mean?</h3>
                            <p class="text-sm text-slate-500">These represent gold purity - 24K is pure gold (99.9%),
                                22K is 91.67% pure, and 18K is 75% pure gold.</p>
                        </div>
                    </div>
                </div> -->
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