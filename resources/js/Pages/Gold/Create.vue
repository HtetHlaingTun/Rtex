<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';
import { useEscapeShortcut } from '@/Composables/useKeyboardShortcut';

const props = defineProps({
    goldTypes: Array,
    currencies: Array,
    previousPrices: Object,
});

const liveReferenceUSD = ref(null);
const isLoadingLive = ref(false);
const lastUpdatedTime = ref(null);
let priceInterval = null;

const showGoldDashboard = ref(false);
const dashboardData = ref(null);
const isLoadingDashboard = ref(false);

// Calculator State
const showCalculator = ref(false);
const spotPrice = ref(null);
const exchangeRate = ref(null);

// Math: (Spot * 1.02041 * Rate) / 1.875
const calculatedPrice = computed(() => {
    if (!spotPrice.value || !exchangeRate.value) return 0;
    return Math.round((spotPrice.value * 1.02041 * exchangeRate.value) / 1.875);
});

// Form
const form = useForm({
    gold_type_id: '',
    price_date: new Date().toISOString().substr(0, 10),
    price: '',
    currency_id: props.currencies?.find(c => c.code === 'MMK')?.id || '',
    source_type: 'manual_entry',
    market_notes: '',
});

const applyCalculatedPrice = () => {
    if (calculatedPrice.value > 0) {
        form.price = calculatedPrice.value;
        form.market_notes = `Calculated: Spot $${spotPrice.value} @ ${exchangeRate.value}`;
        showCalculator.value = false;
        spotPrice.value = null;
    }
};

// gold fetch =====================
const fetchGoldHistory = async (range = '1d') => {
    isLoadingDashboard.value = true;
    try {
        const response = await axios.get(route('api.gold-history', { range }));
        dashboardData.value = response.data;
    } catch (error) {
        console.error('Error fetching gold history:', error);
    } finally {
        isLoadingDashboard.value = false;
    }
};
const openGoldDashboard = () => {
    showGoldDashboard.value = true;
    fetchGoldHistory();
};

const selectedPreviousPrice = computed(() => {
    if (!form.gold_type_id) return null;
    const history = props.previousPrices[form.gold_type_id];
    return history ? history[0] : null;
});

const priceDifference = computed(() => {
    if (!form.price || !selectedPreviousPrice.value) return null;
    const diff = form.price - selectedPreviousPrice.value.price;
    const percentage = ((diff / selectedPreviousPrice.value.price) * 100).toFixed(2);
    return { amount: diff, percentage, isUp: diff > 0, isDown: diff < 0 };
});

const submit = () => {
    form.post(route('gold.store'), {
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs = [
    { label: 'Gold Rates', href: route('gold.index') },
    { label: 'New Entry' }
];

const fetchLiveRate = async () => {
    isLoadingLive.value = true;
    try {
        const response = await axios.get(route('api.live-gold'));

        if (response.data?.status === 'success' && response.data?.usd_price) {
            liveReferenceUSD.value = response.data.usd_price;
            lastUpdatedTime.value = new Date();
        } else {
            liveReferenceUSD.value = null;
        }
    } catch (error) {
        liveReferenceUSD.value = null;
    } finally {
        isLoadingLive.value = false;
    }
};

watch(showCalculator, (isOpen) => {
    if (isOpen && !liveReferenceUSD.value) {
        fetchLiveRate();
    } else if (isOpen) {
        spotPrice.value = liveReferenceUSD.value;
    }
});

onMounted(() => {
    fetchLiveRate();

    priceInterval = setInterval(() => {
        fetchLiveRate();
    }, 120000);

    const urlParams = new URLSearchParams(window.location.search);
    const typeId = urlParams.get('type_id');
    if (typeId) {
        form.gold_type_id = parseInt(typeId);
        const selectedType = props.goldTypes.find(t => t.id == typeId);
        if (selectedType) {
            form.market_notes = `Updating rates for ${selectedType.name}. `;
        }
    }
});

useEscapeShortcut(() => {
    if (showGoldDashboard.value) {
        showGoldDashboard.value = false;
        return;
    }
    if (showCalculator.value) {
        showCalculator.value = false;
    }
});

onUnmounted(() => {
    clearInterval(priceInterval);
});
</script>

<template>

    <Head title="New Gold Price" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8 pb-24">

            <!-- ===== LEFT: Main Form (2 cols) ===== -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Spot Price Assistant Banner -->
                <div
                    class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <div class="bg-indigo-600 p-2 rounded-lg text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span
                                class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest">Automation</span>
                            <span class="text-xs font-bold text-indigo-900">Spot Price Assistant</span>
                        </div>
                    </div>
                    <button @click="showCalculator = true" type="button"
                        class="px-4 py-2 bg-white border border-indigo-200 text-indigo-600 text-[10px] font-black uppercase rounded-lg hover:bg-indigo-600 hover:text-white transition-all shadow-sm active:scale-95">
                        Open Calculator
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit"
                    class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Gold Type -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Gold
                                Type</label>
                            <div class="flex items-center space-x-2">
                                <select v-model="form.gold_type_id"
                                    class="flex-1 bg-slate-50 border-slate-200 rounded-xl font-bold py-3 px-4 focus:ring-amber-500">
                                    <option value="" disabled>Select Type</option>
                                    <option v-for="type in goldTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                                <Link :href="route('gold-types.create')"
                                    class="p-3 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-900 hover:text-white transition-all"
                                    title="Register New Type">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- Pricing Date -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Pricing
                                Date</label>
                            <input type="date" v-model="form.price_date"
                                class="w-full bg-slate-50 border-slate-200 rounded-xl font-bold py-3 px-4 focus:ring-amber-500" />
                        </div>
                    </div>

                    <!-- Price Amount -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-end px-1">
                            <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                New Price Amount (MMK)
                            </label>
                            <div v-if="priceDifference"
                                :class="priceDifference.isUp ? 'text-emerald-600' : 'text-rose-600'"
                                class="text-[11px] font-black italic flex items-center space-x-1">
                                <span>{{ priceDifference.isUp ? '▲' : '▼' }}</span>
                                <span>{{ $formatNumber(Math.abs(priceDifference.amount)) }} ({{
                                    priceDifference.percentage
                                }}%)</span>
                            </div>
                        </div>
                        <div class="relative group">
                            <input type="number" v-model="form.price" step="0.01"
                                class="w-full bg-slate-50 border-slate-100 group-hover:bg-white border-2 rounded-2xl text-3xl font-mono font-black py-6 px-6 transition-all focus:border-amber-400 focus:ring-0"
                                :class="{ 'border-emerald-200': priceDifference?.isUp, 'border-rose-200': priceDifference?.isDown }"
                                placeholder="0.00" />
                            <div
                                class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 font-black text-xl italic">
                                MMK
                            </div>
                        </div>
                    </div>

                    <!-- Market Notes -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-slate-400 tracking-widest">
                            Market Notes / Source
                        </label>
                        <textarea v-model="form.market_notes" rows="3" placeholder="Explain price movements..."
                            class="w-full bg-slate-50 border-slate-200 rounded-xl font-medium focus:ring-amber-500" />
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-xl hover:bg-black transition-all active:scale-[0.98] disabled:opacity-50">
                        {{ form.processing ? 'Syncing...' : 'Submit Rate' }}
                    </button>
                </form>
            </div>

            <!-- ===== RIGHT: Sidebar (1 col) ===== -->
            <div class="space-y-6">






                <!-- Clickable Live Spot Price Card -->
                <div @click="openGoldDashboard"
                    class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3 cursor-pointer hover:border-amber-300 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Live Spot
                            Price</span>
                        <span
                            class="text-[10px] text-amber-500 font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                            View Dashboard →
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div v-if="liveReferenceUSD" class="flex items-center gap-2 text-emerald-600 font-bold">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-lg font-mono font-black">${{ $formatNumber(liveReferenceUSD) }}</span>
                        </div>
                        <div v-else class="flex items-center gap-2 text-rose-400 text-xs font-bold italic">
                            <span>⚠ Live price unavailable</span>
                        </div>
                        <button @click.stop="fetchLiveRate" :disabled="isLoadingLive"
                            class="p-2 hover:bg-amber-50 rounded-full transition-colors" title="Refresh Price">
                            <svg :class="{ 'animate-spin': isLoadingLive }" class="w-4 h-4 text-amber-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>

                    <div v-if="lastUpdatedTime" class="text-slate-400 text-[10px] font-medium italic">
                        Last checked: {{ $formatTimeOnly(lastUpdatedTime) }}
                    </div>
                </div>


                <!-- Gold Dashboard Modal -->
                <Teleport to="body">
                    <div v-if="showGoldDashboard"
                        class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm"
                        @click.self="showGoldDashboard = false">

                        <div
                            class="bg-white rounded-3xl w-full max-w-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                            <!-- Modal Header -->
                            <div class="bg-slate-900 px-8 py-5 flex items-center justify-between shrink-0">
                                <div>
                                    <h2 class="text-white font-black text-sm uppercase tracking-widest">World Gold
                                        Market</h2>
                                    <p class="text-slate-400 text-[10px] mt-0.5">COMEX Gold Futures (GC=F) · USD per
                                        Troy Oz</p>
                                </div>
                                <button @click="showGoldDashboard = false"
                                    class="text-slate-400 hover:text-white transition-colors text-xl leading-none">✕</button>
                            </div>

                            <!-- Loading State -->
                            <div v-if="isLoadingDashboard" class="flex-1 flex items-center justify-center py-20">
                                <div class="text-center space-y-3">
                                    <div
                                        class="w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full animate-spin mx-auto">
                                    </div>
                                    <p class="text-slate-400 text-xs font-medium">Fetching market data...</p>
                                </div>
                            </div>

                            <template v-else-if="dashboardData?.status === 'success'">

                                <!-- Price Header -->
                                <div class="px-8 pt-6 pb-4 shrink-0">
                                    <div class="flex items-end gap-4">
                                        <span class="text-4xl font-mono font-black text-slate-900">
                                            ${{ $formatNumber(dashboardData.current_price) }}
                                        </span>
                                        <span
                                            :class="dashboardData.change >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'"
                                            class="text-sm font-black px-3 py-1 rounded-full mb-1">
                                            {{ dashboardData.change >= 0 ? '▲' : '▼' }}
                                            ${{ Math.abs(dashboardData.change) }}
                                            ({{ Math.abs(dashboardData.change_percent) }}%)
                                        </span>
                                    </div>
                                    <p class="text-slate-400 text-[10px] mt-1 font-medium">
                                        Previous Close: ${{ $formatNumber(dashboardData.previous_close) }}
                                    </p>
                                </div>

                                <!-- Range Tabs -->
                                <div class="px-8 pb-4 flex gap-2 shrink-0">
                                    <button v-for="r in ['1d', '5d', '1mo', '3mo', '6mo', '1y']" :key="r"
                                        @click="fetchGoldHistory(r)" :class="dashboardData.range === r
                                            ? 'bg-slate-900 text-white'
                                            : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                        class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all">
                                        {{ r }}
                                    </button>
                                </div>

                                <!-- Chart -->
                                <div class="px-8 shrink-0">
                                    <div
                                        class="relative h-48 bg-slate-50 rounded-2xl overflow-hidden border border-slate-100">
                                        <svg v-if="dashboardData.points.length > 1" class="w-full h-full"
                                            viewBox="0 0 800 200" preserveAspectRatio="none">

                                            <defs>
                                                <linearGradient id="goldGrad" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%"
                                                        :stop-color="dashboardData.change >= 0 ? '#10b981' : '#f43f5e'"
                                                        stop-opacity="0.3" />
                                                    <stop offset="100%"
                                                        :stop-color="dashboardData.change >= 0 ? '#10b981' : '#f43f5e'"
                                                        stop-opacity="0" />
                                                </linearGradient>
                                            </defs>

                                            <!-- Compute points inline -->
                                            <template v-if="dashboardData.points.length">
                                                <path :d="(() => {
                                                    const pts = dashboardData.points;
                                                    const minP = Math.min(...pts.map(p => p.low));
                                                    const maxP = Math.max(...pts.map(p => p.high));
                                                    const range = maxP - minP || 1;
                                                    const toX = (i) => (i / (pts.length - 1)) * 800;
                                                    const toY = (v) => 190 - ((v - minP) / range) * 180;

                                                    const line = pts.map((p, i) =>
                                                        `${i === 0 ? 'M' : 'L'}${toX(i)},${toY(p.close)}`
                                                    ).join(' ');

                                                    const area = line +
                                                        ` L${toX(pts.length - 1)},200 L0,200 Z`;

                                                    return area;
                                                })()" fill="url(#goldGrad)" />
                                                <path :d="(() => {
                                                    const pts = dashboardData.points;
                                                    const minP = Math.min(...pts.map(p => p.low));
                                                    const maxP = Math.max(...pts.map(p => p.high));
                                                    const range = maxP - minP || 1;
                                                    const toX = (i) => (i / (pts.length - 1)) * 800;
                                                    const toY = (v) => 190 - ((v - minP) / range) * 180;

                                                    return pts.map((p, i) =>
                                                        `${i === 0 ? 'M' : 'L'}${toX(i)},${toY(p.close)}`
                                                    ).join(' ');
                                                })()" fill="none"
                                                    :stroke="dashboardData.change >= 0 ? '#10b981' : '#f43f5e'"
                                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </template>
                                        </svg>
                                        <div v-else
                                            class="flex items-center justify-center h-full text-slate-400 text-xs italic">
                                            No chart data available
                                        </div>
                                    </div>
                                </div>

                                <!-- Stats Grid -->
                                <div class="px-8 py-6 grid grid-cols-2 md:grid-cols-4 gap-4 shrink-0">
                                    <div v-for="stat in [
                                        { label: 'Day High', value: '$' + $formatNumber(dashboardData.day_high) },
                                        { label: 'Day Low', value: '$' + $formatNumber(dashboardData.day_low) },
                                        { label: '52W High', value: '$' + $formatNumber(dashboardData.fifty_two_week_high) },
                                        { label: '52W Low', value: '$' + $formatNumber(dashboardData.fifty_two_week_low) },
                                    ]" :key="stat.label" class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{
                                            stat.label }}</p>
                                        <p class="text-base font-mono font-black text-slate-900 mt-1">{{ stat.value }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="px-8 pb-6 shrink-0">
                                    <p class="text-[10px] text-slate-300 italic text-center">
                                        Source: Yahoo Finance · COMEX Gold Futures · Prices in USD per troy ounce
                                    </p>
                                </div>

                            </template>

                            <!-- Error State -->
                            <div v-else class="flex-1 flex items-center justify-center py-20">
                                <p class="text-rose-400 text-sm font-bold italic">⚠ Failed to load market data</p>
                            </div>

                        </div>
                    </div>
                </Teleport>












                <!-- Last Verified Price Card -->
                <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-2xl relative overflow-hidden group">
                    <div
                        class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500 rounded-full opacity-10 group-hover:scale-150 transition-transform" />

                    <h3 class="text-[10px] font-black uppercase text-slate-500 mb-6 tracking-widest relative z-10">
                        Last Verified Price
                    </h3>

                    <div v-if="selectedPreviousPrice" class="relative z-10">
                        <div class="text-3xl font-mono font-black text-amber-400 italic">
                            {{ $formatNumber(selectedPreviousPrice.price) }}
                        </div>
                        <p class="text-[10px] mt-2 text-slate-400 font-bold uppercase tracking-tight">
                            Updated: {{ selectedPreviousPrice.price_date }}
                        </p>
                        <div class="mt-6 pt-6 border-t border-slate-800">
                            <span class="text-[9px] font-black text-slate-600 uppercase italic">Trend Note</span>
                            <p class="text-[11px] text-slate-300 mt-1 italic">
                                "{{ selectedPreviousPrice.market_notes || 'No notes for this period.' }}"
                            </p>
                        </div>
                    </div>

                    <div v-else
                        class="py-10 text-center border border-dashed border-slate-700 rounded-2xl text-slate-500 text-xs italic">
                        Select a type to see history
                    </div>
                </div>

                <!-- Precision Check Notice -->
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                    <h4 class="text-[10px] font-black text-amber-900 uppercase mb-2 italic">Precision Check</h4>
                    <p class="text-[11px] text-amber-700 leading-relaxed">
                        Entries are staged in the <span class="font-bold">Verification Queue</span>. Avoid rapid
                        updates to maintain historical chart integrity.
                    </p>
                </div>
            </div>
        </div>

        <!-- ===== Calculator Modal ===== -->
        <div v-if="showCalculator"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div
                class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden animate-in zoom-in duration-200">
                <div class="p-8 space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="font-black text-slate-900 uppercase text-xs tracking-widest">Conversion Tool</h3>
                        <button @click="showCalculator = false"
                            class="text-slate-300 hover:text-slate-900 transition-colors">✕</button>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase">World Gold (USD/oz)</label>
                            <input type="number" v-model="spotPrice"
                                class="w-full bg-slate-50 border-slate-200 rounded-2xl text-xl font-mono font-black py-4 px-5 focus:ring-amber-500"
                                placeholder="0.00" />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase">Exchange Rate
                                (USD/MMK)</label>
                            <input type="number" v-model="exchangeRate"
                                class="w-full bg-slate-50 border-slate-200 rounded-2xl text-xl font-mono font-black py-4 px-5 focus:ring-amber-500"
                                placeholder="0.00" />
                        </div>
                    </div>

                    <div class="bg-amber-50 rounded-3xl p-6 text-center border border-amber-100">
                        <span class="block text-[10px] font-black text-amber-600 uppercase mb-2 italic tracking-widest">
                            Est. Local Price (16-Pe)
                        </span>
                        <div class="text-4xl font-mono font-black text-amber-900 tracking-tighter italic">
                            {{ calculatedPrice > 0 ? $formatNumber(calculatedPrice) : '---' }}
                        </div>
                    </div>

                    <button @click="applyCalculatedPrice" type="button"
                        class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-xl hover:bg-black transition-all active:scale-95">
                        Apply to Form
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>