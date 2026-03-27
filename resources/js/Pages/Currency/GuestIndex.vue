<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'
import axios from 'axios'

const props = defineProps({
    rates: Array,
    worldGoldSnapshot: Object,
    cbm_available: Boolean
})

// --- Reactive State ---
// We initialize these with the props provided by the initial Inertia load
const snapshot = ref(props.worldGoldSnapshot);
const localRates = ref(props.rates);
let refreshInterval = null;

// --- Computed ---
const featuredRates = computed(() => {
    return localRates.value.filter(r => ['USD', 'SGD', 'THB', 'JPY'].includes(r.currency.code));
});

const otherRates = computed(() => {
    return localRates.value.filter(r => !['USD', 'SGD', 'THB', 'JPY'].includes(r.currency.code));
});

// --- API Sync ---
const fetchLatestSnapshot = async () => {
    try {
        const response = await axios.get('/api/market-pulse');

        if (response.data.status === 'success') {
            // Update the reactive refs so the UI updates automatically
            snapshot.value = response.data.data.gold;
            localRates.value = response.data.data.currencies;
        }
    } catch (e) {
        console.error('API Sync Error:', e);
    }
};

// --- Lifecycle ---
onMounted(() => {
    // Refresh every 60 seconds
    refreshInterval = setInterval(fetchLatestSnapshot, 60000);
});

onUnmounted(() => {
    // Clean up to prevent memory leaks
    if (refreshInterval) clearInterval(refreshInterval);
});
</script>


<template>

    <Head title="Live Exchange Rates" />

    <UserLayout>
        <div class="max-w-2xl mx-auto space-y-8">
            <div class="flex justify-between items-end px-2">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 italic tracking-tight">Market Rates</h1>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Live from Central Bank
                    </p>
                </div>
                <div v-if="snapshot" class="text-right">
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-tighter">Gold Premium</p>
                    <p class="text-sm font-mono font-black text-slate-700">+2.04%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div v-for="rate in localRates" :key="rate.id"
                    class="bg-white border border-slate-200 rounded-[2rem] p-6 shadow-sm hover:shadow-md transition-all active:scale-[0.98]">

                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black shadow-lg">
                                {{ rate.currency.code }}
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900">{{ rate.currency.name }}</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Official Mid:
                                    {{ $formatNumber(rate.cbm_rate) }}</p>
                            </div>
                        </div>
                        <div :class="rate.market_trend === 'up' ? 'text-emerald-500' : 'text-rose-500'"
                            class="text-xs font-black bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                            {{ rate.market_trend === 'up' ? '▲' : '▼' }} {{ rate.change_percentage }}%
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-4 text-center">
                            <span
                                class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1 block">Buying</span>
                            <span class="text-xl font-mono font-black text-slate-900">{{ $formatMoney(rate.buy_rate)
                                }}</span>
                        </div>
                        <div class="bg-rose-50/50 border border-rose-100 rounded-2xl p-4 text-center">
                            <span
                                class="text-[9px] font-black text-rose-600 uppercase tracking-widest mb-1 block">Selling</span>
                            <span class="text-xl font-mono font-black text-slate-900">{{ $formatMoney(rate.sell_rate)
                                }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-50 flex justify-between items-center px-1">
                        <span class="text-[9px] font-bold text-slate-400">Spread: {{ $formatNumber(rate.sell_rate -
                            rate.buy_rate) }} MMK</span>
                        <Link :href="route('currencies.history', rate.id)"
                            class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:underline">
                            View Trends →
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="snapshot"
                class="bg-slate-900 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/20 rounded-full blur-2xl"></div>

                <h4 class="text-[10px] font-black text-amber-500 uppercase tracking-[0.2em] mb-6 italic">Gold Market
                    Logic</h4>

                <div class="space-y-6 relative z-10">
                    <div class="flex justify-between items-end">
                        <div>
                            <p class="text-[9px] text-slate-500 font-bold uppercase mb-1">Effective Gold-USD Rate</p>
                            <p class="text-3xl font-mono font-black italic">
                                {{ $formatNumber(snapshot.usd_mmk_rate * 1.02041) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] text-slate-500 font-bold uppercase mb-1">Market Spread</p>
                            <p class="text-xl font-mono font-black text-amber-400">
                                +{{ $formatNumber((snapshot.usd_mmk_rate * 1.02041) - snapshot.usd_mmk_rate) }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-white/10 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[8px] text-slate-500 font-bold uppercase mb-1 tracking-tighter">Implied Price
                                (16.329g)</p>
                            <p class="text-sm font-black text-slate-200">{{ $formatMoney(snapshot.mmk_price_new) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[8px] text-slate-500 font-bold uppercase mb-1 tracking-tighter">Implied Price
                                (16.606g)</p>
                            <p class="text-sm font-black text-slate-200">{{ $formatMoney(snapshot.mmk_price_old) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>