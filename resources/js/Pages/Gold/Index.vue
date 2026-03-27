<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';
import { Link, Head } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    goldTypes: Array,
    pending_gold_count: Number,
    worldGoldSnapshot: Object,

});

// --- Reactive State ---
const snapshot = ref(props.worldGoldSnapshot);
const localGoldTypes = ref(props.goldTypes);
const now = ref(new Date());

let snapshotInterval = null;
let timer;

// --- Breadcrumbs ---
const currencyBreadcrumbs = [
    { label: 'Gold Prices', href: route('gold.index') },
    { label: 'Home' },
    { label: 'live' }
];

// --- Computed Properties (Reactive Filters) ---
const worldGold = computed(() => localGoldTypes.value.filter(t => t.category === 'world'));
const myanmarGoldNew = computed(() => localGoldTypes.value.filter(t => t.category === 'myanmar' && t.system === 'new'));
const myanmarGoldOld = computed(() => localGoldTypes.value.filter(t => t.category === 'myanmar' && t.system === 'old'));
const totalActiveTypes = computed(() => localGoldTypes.value.length);


const worldGoldPerGram = computed(() => {
    if (!snapshot.value?.usd_price) return 0;
    // 1 Troy Ounce = 31.1034768 grams
    return snapshot.value.usd_price / 31.1035;
});

// Add these to your computed section
const usdPerGram = computed(() => (snapshot.value?.usd_price || 0) / 31.1035);

// New System (16.329g)
const usdPerKyattharNew = computed(() => usdPerGram.value * 16.329);

// Old System (16.606g)
const usdPerKyattharOld = computed(() => usdPerGram.value * 16.606);

// --- Methods ---
const fetchLatestSnapshot = async () => {
    try {
        const response = await axios.get(route('api.live-gold'));

        if (response.data?.status === 'success') {
            // Update World Gold Top Card
            snapshot.value = response.data;

            // Update Myanmar Gold Grid Cards
            if (response.data.gold_types) {
                localGoldTypes.value = response.data.gold_types;
            }
        }
    } catch (e) {
        console.error('Snapshot refresh failed:', e);
    }
};

const isFresh = (timestamp) => {
    if (!timestamp) return false;
    const updatedTime = new Date(timestamp);
    const diff = now.value - updatedTime;
    return diff < 600000; // 10 minutes in ms
};

// --- Lifecycle ---
onMounted(() => {
    // Pulse timer
    timer = setInterval(() => {
        now.value = new Date();
    }, 60000);

    // Initial fetch and 1-minute interval
    fetchLatestSnapshot();
    snapshotInterval = setInterval(fetchLatestSnapshot, 60000);
});

onUnmounted(() => {
    clearInterval(snapshotInterval);
    clearInterval(timer);
});
</script>

<template>

    <Head title="Gold Market Dashboard" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="currencyBreadcrumbs" />
        </template>

        <template #header>

            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-2xl md:mx-[auto] lg:max-w-4xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight italic">Gold Market Overview</h1>
                        <p class="text-sm text-slate-500 font-medium">
                            Monitoring {{ totalActiveTypes }} bullion categories.
                        </p>
                        <p class="text-[12px] text-slate-400 mt-2 font-medium">Auto-syncing every two minutes</p>
                    </div>
                    <Link v-if="$page.props.auth.user.role !== 'viewer'" :href="route('gold-types.create')"
                        class="hidden md:flex inline-flex items-center justify-center px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-black transition shadow-lg active:scale-95">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New
                    </Link>

                    <Fab class="md:hidden" v-if="$page.props.auth.user.role !== 'viewer'"
                        :href="route('gold-types.create')" />
                </div>
            </div>

        </template>

        <div class="space-y-12 pb-16">





            <section>
                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-xl md:mx-[auto] lg:max-w-3xl">
                    <div class="flex items-center gap-3 mb-8 px-1 md:max-w-xl">
                        <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
                        <div>
                            <h2 class="text-md font-black uppercase tracking-widest text-slate-700">
                                World Gold Bullion
                            </h2>

                        </div>
                    </div>
                </div>

                <!-- world gold rate  -->
                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-md md:mx-[auto] lg:max-w-2xl">
                    <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-2xl border border-slate-800">
                        <div class="flex justify-between items-center mb-8">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                                <span class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">
                                    Global Spot Value (USD)
                                </span>
                            </div>
                            <div v-if="snapshot" :class="snapshot.change >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                                class="text-[11px] font-black px-3 py-1 bg-white/5 rounded-lg border border-white/10">
                                {{ snapshot.change >= 0 ? '▲' : '▼' }} {{ Math.abs(snapshot.change_percent) }}%
                            </div>
                        </div>

                        <div class="mb-10 text-center sm:text-left">
                            <p class="text-[10px] text-slate-500 font-bold uppercase mb-2 tracking-widest">Price Per
                                Troy Ounce</p>
                            <span class="text-5xl font-mono font-black text-white italic tracking-tighter">
                                ${{ $formatNumber(snapshot.usd_price) }}
                            </span>
                        </div>

                        <div v-if="worldGoldSnapshot" class="mb-8 p-4 bg-white/5 rounded-2xl border border-white/10">
                            <div class="flex justify-between items-center mb-3">
                                <span
                                    class="text-[10px] font-black text-amber-500 uppercase italic tracking-wider">Market
                                    Analysis</span>
                                <span class="text-[10px] font-bold text-slate-400">Premium: +2.04%</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase font-bold">Implied Gold-USD</p>
                                    <p class="text-sm font-mono font-black text-slate-200">
                                        {{ $formatMoney(worldGoldSnapshot.usd_mmk_rate * 1.02041) }} <span
                                            class="text-[10px] text-slate-500">MMK</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-slate-500 uppercase font-bold">Spread vs Mid-Rate</p>
                                    <p class="text-sm font-mono font-black text-amber-400">
                                        +{{ $formatMoney((worldGoldSnapshot.usd_mmk_rate * 1.02041) -
                                            worldGoldSnapshot.usd_mmk_rate) }} <span class="text-[10px]">MMK</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8 border-t border-slate-800/50">
                            <div class="bg-white/5 p-4 rounded-2xl">
                                <p class="text-[9px] text-slate-500 font-bold uppercase mb-2">Per Gram</p>
                                <span class="text-xl font-mono font-black text-slate-200">
                                    ${{ $formatNumber(usdPerGram) }}
                                </span>
                            </div>

                            <div class="bg-white/5 p-4 rounded-2xl border border-amber-500/20">
                                <p class="text-[9px] text-amber-500/70 font-bold uppercase mb-2 italic">Kyatthar (New)
                                </p>
                                <span class="text-xl font-mono font-black text-amber-400">
                                    ${{ $formatNumber(usdPerKyattharNew) }}
                                </span>
                                <p class="text-[8px] text-slate-600 font-bold mt-1">16.329g</p>
                            </div>

                            <div class="bg-white/5 p-4 rounded-2xl border border-orange-500/20">
                                <p class="text-[9px] text-orange-500/70 font-bold uppercase mb-2 italic">Kyatthar (Old)
                                </p>
                                <span class="text-xl font-mono font-black text-orange-400">
                                    ${{ $formatNumber(usdPerKyattharOld) }}
                                </span>
                                <p class="text-[8px] text-slate-600 font-bold mt-1">16.606g</p>
                            </div>
                        </div>

                        <div class="mt-8 pt-4 flex items-center justify-between opacity-30 border-t border-white/5">
                            <span class="text-[8px] font-bold uppercase">Updated: {{
                                $formatDateTime(snapshot.fetched_at) }}</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest">1oz = 31.1035g</span>
                        </div>
                    </div>
                </div>


            </section>






            <!---------------------- gold cards  ----------------------------------->
            <section v-if="myanmarGoldNew.length > 0">

                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-xl md:mx-[auto] lg:max-w-3xl">
                    <div class="flex items-center gap-3 mb-8 px-1 md:max-w-xl">
                        <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
                        <div>
                            <h2 class="text-md font-black uppercase tracking-widest text-slate-700">
                                Myanmar Bullion — New System
                            </h2>
                            <p class="text-[15px] text-slate-400 mt-0.5 font-medium">1 Kyatthar = 16.329g</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-md md:mx-[auto] lg:max-w-2xl">

                    <Link v-for="type in myanmarGoldNew" :key="type.id" :href="route('gold.history', type.id)"
                        class="bg-white border border-slate-200  p-8 shadow-sm shadow-inner-soft hover:border-amber-300 hover:shadow-xl transition-all duration-300 group relative block active:scale-[0.98]">

                        <div class="relative">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h3
                                        class="font-black text-xl text-slate-900 group-hover:text-amber-600 transition-colors">
                                        {{ type.name }}
                                    </h3>
                                    <p class="text-[12px] text-slate-400 font-bold uppercase mt-1.5 tracking-widest">
                                        {{ type.purity }} · {{ type.unit }}
                                    </p>
                                </div>
                                <span
                                    class="text-[12px] font-black text-amber-500 border border-amber-200 bg-amber-50 px-2.5 py-1 rounded-full uppercase">
                                    New
                                </span>
                            </div>

                            <div class="flex items-end justify-between">
                                <div>
                                    <span
                                        class="block text-[10px] text-slate-400 font-black uppercase mb-1.5 tracking-widest">
                                        Current Value
                                    </span>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl font-mono font-black text-slate-900 italic">
                                            {{ type.latest_verified_price ?
                                                $formatNumber(type.latest_verified_price.price) : '---' }}
                                        </span>
                                        <span class="text-[10px] font-black text-slate-400">MMK</span>
                                    </div>
                                </div>
                                <div v-if="type.latest_verified_price?.status === 'verified'"
                                    class="flex flex-col items-end gap-1">
                                    <div :class="type.latest_verified_price.trend === 'up' ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-rose-600 bg-rose-50 border-rose-100'"
                                        class="flex items-center gap-2 px-2.5 py-1 rounded-lg text-[10px] font-black border">
                                        <span>{{ type.latest_verified_price.trend === 'up' ? '▲' : '▼' }}</span>
                                        <span>{{ $formatNumber(type.latest_verified_price.change_percentage || '0')
                                            }}%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div v-if="isFresh(type.latest_verified_price?.created_at)"
                                        class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">
                                        {{ $formatDateTime(type.latest_verified_price?.created_at || 'N/A') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div v-if="type.latest_verified_price?.high_price"
                                        class="flex flex-col items-end leading-none">
                                        <span class="text-[9px] font-black text-emerald-500 mb-0.5">
                                            H: {{ $formatNumber(type.latest_verified_price.high_price) }}
                                        </span>
                                        <span class="text-[9px] font-black text-rose-500">
                                            L: {{ $formatNumber(type.latest_verified_price.low_price) }}
                                        </span>
                                    </div>

                                    <div class="hidden group-hover:flex items-center transition-all duration-300">
                                        <div class="h-6 w-px bg-slate-100 mx-1"></div>
                                        <span
                                            class="text-amber-500 font-black text-sm ml-1 animate-in fade-in slide-in-from-right-1">→</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </section>






            <!-- old gold section  -->
            <section v-if="myanmarGoldOld.length > 0">

                <div
                    class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-xl xs:max-w-md md:mx-[auto] lg:max-w-3xl">
                    <div class="flex items-center gap-3 mb-8 px-1">
                        <div class="w-1.5 h-8 bg-orange-400 rounded-full"></div>
                        <div>
                            <h2 class="text-md font-black uppercase tracking-widest text-slate-700">
                                Myanmar Bullion — Old System
                            </h2>
                            <p class="text-[15px] text-slate-400 mt-0.5 font-medium">1 Kyatthar = 16.606g</p>
                        </div>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-3 md:max-w-md md:mx-auto lg:max-w-2xl lg:mx-none">

                    <Link v-for="type in myanmarGoldOld" :key="type.id" :href="route('gold.history', type.id)"
                        class="bg-white border border-slate-200  p-8 shadow-sm hover:border-orange-300 hover:shadow-xl transition-all duration-300 group relative block active:scale-[0.98]">
                        <div class="relative">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h3
                                        class="font-black text-xl text-slate-900 group-hover:text-orange-500 transition-colors">
                                        {{ type.name }}
                                    </h3>
                                    <p class="text-[12px] text-slate-400 font-bold uppercase mt-1.5 tracking-widest">
                                        {{ type.purity }} · {{ type.unit }}
                                    </p>
                                </div>
                                <span
                                    class="text-[12px] font-black text-orange-500 border border-orange-200 bg-orange-50 px-2.5 py-1 rounded-full uppercase">
                                    Old
                                </span>
                            </div>

                            <div class="flex items-end justify-between">
                                <div>
                                    <span
                                        class="block text-[10px] text-slate-400 font-black uppercase mb-1.5 tracking-widest">
                                        Current Value
                                    </span>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl font-mono font-black text-slate-900 italic">
                                            {{ type.latest_verified_price ?
                                                $formatNumber(type.latest_verified_price.price) : '---' }}
                                        </span>
                                        <span class="text-[10px] font-black text-slate-400">MMK</span>
                                    </div>
                                </div>
                                <div v-if="type.latest_verified_price?.status === 'verified'"
                                    class="flex flex-col items-end gap-1">
                                    <div :class="type.latest_verified_price.trend === 'up' ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-rose-600 bg-rose-50 border-rose-100'"
                                        class="flex items-center gap-2 px-2.5 py-1 rounded-lg text-[10px] font-black border">
                                        <span>{{ type.latest_verified_price.trend === 'up' ? '▲' : '▼' }}</span>
                                        <span>{{ $formatNumber(type.latest_verified_price.change_percentage || '0')
                                            }}%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div v-if="isFresh(type.latest_verified_price?.created_at)"
                                        class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-tight">
                                        {{ $formatDateTime(type.latest_verified_price?.created_at || 'N/A') }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div v-if="type.latest_verified_price?.high_price"
                                        class="flex flex-col items-end leading-none">
                                        <span class="text-[9px] font-black text-emerald-500 mb-0.5">
                                            H: {{ $formatNumber(type.latest_verified_price.high_price) }}
                                        </span>
                                        <span class="text-[9px] font-black text-rose-500">
                                            L: {{ $formatNumber(type.latest_verified_price.low_price) }}
                                        </span>
                                    </div>

                                    <div class="hidden group-hover:flex items-center transition-all duration-300">
                                        <div class="h-6 w-px bg-slate-100 mx-1"></div>
                                        <span
                                            class="text-amber-500 font-black text-sm ml-1 animate-in fade-in slide-in-from-right-1">→</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </section>

            <section v-if="worldGold.length > 0">
                <div class="flex items-center gap-3 mb-8 px-1">
                    <div class="w-1.5 h-8 bg-slate-900 rounded-full"></div>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">International Markets
                        </h2>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-medium">Prices in USD</p>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-0 relative z-10 divide-y lg:divide-y-0 lg:divide-x divide-slate-800">
                        <Link v-for="type in worldGold" :key="type.id" :href="route('gold.history', type.id)"
                            class="group/item block px-8 py-6 hover:bg-slate-800/40 transition-all">
                            <span class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">{{
                                type.name
                                }}</span>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-3xl font-mono font-black italic group-hover/item:text-amber-400 transition-colors">
                                    ${{ type.latestVerifiedPrice ? $formatNumber(type.latestVerifiedPrice.price) :
                                        '0.00' }}
                                </span>
                                <span class="text-[10px] opacity-30 font-bold uppercase">/ {{ type.unit }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>