<template>

    <Head title="Gold Market Dashboard" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="currencyBreadcrumbs" />
        </template>

        <!-- ═══ HEADER ═══════════════════════════════════════════ -->
        <template #header>
            <div class="max-w-3xl mx-auto px-1 w-full">
                <!-- Top bar -->
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <!-- Market status badge -->
                        <span :class="marketOpen
                            ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                            : 'text-rose-700 bg-rose-50 border-rose-200'"
                            class="inline-flex items-center gap-1.5 text-[10px] font-bold tracking-widest uppercase px-3 py-1 rounded-full border">
                            <span class="relative flex h-1.5 w-1.5">
                                <span v-if="marketOpen"
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75">
                                </span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5"
                                    :class="marketOpen ? 'bg-emerald-500' : 'bg-rose-500'">
                                </span>
                            </span>
                            Market {{ marketOpen ? 'Open' : 'Closed' }}
                        </span>

                        <!-- Sync indicator -->
                        <span class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
                            <svg :class="{ 'animate-spin': isRefreshing }" class="w-3 h-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Auto-syncing
                        </span>
                    </div>

                    <!-- Add button -->
                    <Link v-if="$page.props.auth.user.role !== 'viewer'" :href="route('gold-types.create')"
                        class="hidden md:inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-bold tracking-wide rounded-full transition-colors active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Type
                    </Link>
                </div>

                <!-- Title + mini ticker -->
                <div class="flex items-end justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-[10px] font-black tracking-[0.18em] uppercase text-amber-600 mb-1">
                            Real-time Bullion Monitor
                        </p>
                        <h1 class="text-[38px] font-black text-slate-900 tracking-tight leading-none mb-2">
                            Gold Market
                        </h1>
                        <p class="text-[13px] text-slate-400 font-medium">
                            Tracking
                            <span class="text-slate-700 font-semibold">{{ totalActiveTypes }}</span>
                            categories · Updated {{ timeAgo(lastUpdateTime) }}
                        </p>
                    </div>

                    <!-- Live mini ticker -->
                    <div v-if="snapshot" class="flex flex-col items-end gap-0.5 pb-1">
                        <span class="text-[9px] font-black tracking-[0.15em] uppercase text-slate-400">XAU / USD</span>
                        <span
                            class="font-mono text-2xl font-black text-slate-900 tracking-tight transition-colors duration-300"
                            :class="{ 'text-amber-500': priceFlash }">
                            ${{ $formatNumber(snapshot?.usd_price ?? 0) }}
                        </span>
                        <span class="font-mono text-[11px] font-bold"
                            :class="priceUp ? 'text-emerald-600' : 'text-rose-600'">
                            {{ priceUp ? '+' : '' }}{{ changeAbs }}
                            ({{ priceUp ? '+' : '' }}{{ changePct }}%)
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <!-- ═══ BODY ══════════════════════════════════════════════ -->
        <div class="max-w-3xl mx-auto space-y-14 pb-20">

            <!-- ── WORLD GOLD HERO ──────────────────────────────── -->
            <WorldGoldHero :snapshot="snapshot" :priceFlash="priceFlash" :priceUp="priceUp" :changeAbs="changeAbs"
                :changePct="changePct" :usdPerGram="usdPerGram" :usdPerKyattharNew="usdPerKyattharNew"
                :usdPerKyattharOld="usdPerKyattharOld" :worldGoldSnapshot="worldGoldSnapshot" :sgdRate="sgdRate" />

            <!-- ── SGD GOLD MARKET ──────────────────────────────── -->
            <SgdGoldMarket v-if="sgdRate && snapshot" :sgdRate="sgdRate" :snapshot="snapshot"
                :sgdGoldPrice="sgdGoldPrice" :sgdPerGram="sgdPerGram" :sgdKyattharNew="sgdKyattharNew"
                :sgdKyattharOld="sgdKyattharOld" :usdPerGram="usdPerGram" />

            <!-- ── MYANMAR NEW SYSTEM ─────────────────────────── -->
            <MyanmarGoldSystem :goldTypes="myanmarGoldNew" system="new" systemColor="amber" systemLabel="New System"
                weight="16.329g" :isFresh="isFresh" />

            <!-- ── MYANMAR OLD SYSTEM ─────────────────────────── -->
            <MyanmarGoldSystem :goldTypes="myanmarGoldOld" system="old" systemColor="orange" systemLabel="Old System"
                weight="16.606g" :isFresh="isFresh" />

            <!-- ── INTERNATIONAL MARKETS ──────────────────────── -->
            <InternationalMarkets v-if="worldGold.length > 0" :goldTypes="worldGold" />

        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';
import { Link, Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import axios from 'axios';

// Import component sections
import WorldGoldHero from '@/Components/gold/admin/WorldGoldHero.vue';
import SgdGoldMarket from '@/Components/gold/admin/SgdGoldMarket.vue';
import MyanmarGoldSystem from '@/Components/gold/admin/MyanmarGoldSystem.vue';
import InternationalMarkets from '@/Components/gold/admin/InternationalMarkets.vue';

const props = defineProps({
    goldTypes: Array,
    pending_gold_count: Number,
    worldGoldSnapshot: Object,
    sgdRate: {
        type: Object,
        default: null
    },
});

const snapshot = ref(props.worldGoldSnapshot);
const localGoldTypes = ref(props.goldTypes);
const now = ref(new Date());
const isRefreshing = ref(false);
const priceFlash = ref(false);
const lastUpdateTime = ref(new Date());

let snapshotInterval = null;
let timer;

const currencyBreadcrumbs = [
    { label: 'Gold Prices', href: route('gold.index') },
    { label: 'Home' },
    { label: 'live' },
];

const worldGold = computed(() => localGoldTypes.value.filter(t => t.category === 'world'));
const myanmarGoldNew = computed(() => localGoldTypes.value.filter(t => t.category === 'myanmar' && t.system === 'new'));
const myanmarGoldOld = computed(() => localGoldTypes.value.filter(t => t.category === 'myanmar' && t.system === 'old'));
const totalActiveTypes = computed(() => localGoldTypes.value.length);

// ============ USD CALCULATIONS ============
const usdPerGram = computed(() => (snapshot.value?.usd_price || 0) / 31.1035);
const usdPerKyattharNew = computed(() => usdPerGram.value * 16.329);
const usdPerKyattharOld = computed(() => usdPerGram.value * 16.606);

// ============ SGD CALCULATIONS ============
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

// ============ PRICE CHANGE ============
const priceUp = computed(() => (snapshot.value?.change ?? 0) >= 0);
const changePct = computed(() => Math.abs(snapshot.value?.change_percent ?? 0));
const changeAbs = computed(() => snapshot.value?.change ?? 0);

// ============ MARKET STATUS ============
const marketOpen = computed(() => {
    const h = now.value.getUTCHours();
    return h >= 22 || h < 21;
});

// ============ HELPER FUNCTIONS ============
const fetchLatestSnapshot = async () => {
    isRefreshing.value = true;
    try {
        const response = await axios.get(route('api.live-gold'));
        if (response.data?.status === 'success') {
            snapshot.value = response.data;
            if (response.data.gold_types) localGoldTypes.value = response.data.gold_types;
            lastUpdateTime.value = new Date();
            priceFlash.value = true;
            setTimeout(() => { priceFlash.value = false; }, 700);
        }
    } catch (e) {
        console.error('Snapshot refresh failed:', e);
    } finally {
        isRefreshing.value = false;
    }
};

const isFresh = (timestamp) => {
    if (!timestamp) return false;
    return (now.value - new Date(timestamp)) < 600000;
};

const timeAgo = (ts) => {
    if (!ts) return 'N/A';
    const diff = Math.floor((now.value - new Date(ts)) / 60000);
    if (diff < 1) return 'just now';
    if (diff < 60) return `${diff}m ago`;
    return `${Math.floor(diff / 60)}h ago`;
};

onMounted(() => {
    timer = setInterval(() => { now.value = new Date(); }, 60000);
    fetchLatestSnapshot();
    snapshotInterval = setInterval(fetchLatestSnapshot, 60000);
});

onUnmounted(() => {
    clearInterval(snapshotInterval);
    clearInterval(timer);
});
</script>