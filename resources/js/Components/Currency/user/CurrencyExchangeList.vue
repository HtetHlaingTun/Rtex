<template>
    <section class="mt-12">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 px-2">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                    <h2 class="text-[11px] font-bold tracking-wider uppercase text-slate-400">
                        Live Market Data
                    </h2>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white">
                    Exchange Rates
                    <span class="text-sm font-normal text-slate-400 ml-1">MMK per unit</span>
                </h3>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-slate-400">Auto-refreshes every 30 minutes</p>
            </div>
        </div>

        <!-- Exchange Rates Table -->
        <div v-if="rates && rates.length > 0">
            <!-- Desktop Table -->
            <div
                class="hidden md:block bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-800/50">
                                <th class="text-left px-6 py-4 text-[11px] font-bold text-slate-500">Currency</th>
                                <th class="text-right px-6 py-4 text-[11px] font-bold text-slate-500">Buy (MMK)</th>
                                <th class="text-right px-6 py-4 text-[11px] font-bold text-slate-500">Sell (MMK)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr v-for="rate in rates" :key="rate.id" @click="goToHistory(rate)"
                                class="hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors cursor-pointer group">

                                <!-- Currency - NOW WITH FULL NAME -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                                            <span class="text-sm font-bold text-slate-600 dark:text-slate-300">
                                                {{ getCurrencyInitial(rate) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div
                                                class="font-bold text-slate-800 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ rate.currency?.code }}
                                            </div>
                                            <!-- ADDED: Full currency name -->
                                            <div class="text-[9px] text-slate-400 font-medium">
                                                {{ getCurrencyName(rate) }}
                                            </div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">
                                                {{ $formatTime24(rate.created_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Buy Rate -->
                                <td class="px-6 py-4 text-right">
                                    <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-lg">
                                        {{ $formatMoney(rate.buy_rate) }}
                                    </div>
                                    <div class="mt-0.5">
                                        <TrendIcon :current="rate.buy_rate" :previous="rate.prev_buy_rate"
                                            class="justify-end" />
                                    </div>
                                </td>

                                <!-- Sell Rate -->
                                <td class="px-6 py-4 text-right">
                                    <div class="font-mono font-bold text-rose-600 dark:text-rose-400 text-lg">
                                        {{ $formatMoney(rate.sell_rate) }}
                                    </div>
                                    <div class="mt-0.5">
                                        <TrendIcon :current="rate.sell_rate" :previous="rate.prev_sell_rate"
                                            class="justify-end" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards - NOW WITH FULL NAME -->
            <div class="md:hidden space-y-3">
                <div v-for="rate in rates" :key="rate.id" @click="goToHistory(rate)"
                    class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4 active:bg-slate-50 dark:active:bg-zinc-800 transition-all cursor-pointer hover:border-emerald-300 dark:hover:border-emerald-700">

                    <!-- Currency Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center">
                                <span class="text-sm font-bold text-slate-600 dark:text-slate-300">
                                    {{ getCurrencyInitial(rate) }}
                                </span>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-white text-base">{{ rate.currency?.code
                                    }}</div>
                                <!-- ADDED: Full currency name on mobile -->
                                <div class="text-[8px] text-slate-400 font-medium">{{ getCurrencyName(rate) }}</div>
                                <div class="text-[8px] text-slate-400">{{ $formatTime24(rate.created_at) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Buy & Sell Rates -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="text-center p-3 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/20">
                            <div class="text-[9px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Buy(MMK)
                            </div>
                            <div class="font-mono font-bold text-emerald-700 dark:text-emerald-400 text-lg">
                                {{ $formatMoney(rate.buy_rate) }}
                            </div>
                            <TrendIcon :current="rate.buy_rate" :previous="rate.prev_buy_rate"
                                class="justify-center mt-1" />
                        </div>
                        <div class="text-center p-3 rounded-xl bg-rose-50/40 dark:bg-rose-950/20">
                            <div class="text-[9px] font-bold text-rose-600 uppercase tracking-wider mb-1">Sell(MMK)
                            </div>
                            <div class="font-mono font-bold text-rose-700 dark:text-rose-400 text-lg">
                                {{ $formatMoney(rate.sell_rate) }}
                            </div>
                            <TrendIcon :current="rate.sell_rate" :previous="rate.prev_sell_rate"
                                class="justify-center mt-1" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-else-if="loading" class="space-y-3">
            <div v-for="i in 5" :key="i"
                class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4 animate-pulse">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>
                    <div class="flex-1">
                        <div class="h-4 bg-slate-200 dark:bg-zinc-700 rounded w-20 mb-2"></div>
                        <div class="h-3 bg-slate-200 dark:bg-zinc-700 rounded w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else
            class="text-center py-12 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800">
            <div
                class="w-16 h-16 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm text-slate-500">Loading exchange rates...</p>
        </div>


    </section>
</template>

<script setup>
import TrendIcon from '@/Components/TrendIcon.vue';

const props = defineProps({
    rates: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    }
});

// Helper function to get currency initial (first letter)
const getCurrencyInitial = (rate) => {
    return rate.currency?.code?.charAt(0) || '?';
};

// Helper function to get full currency name
const getCurrencyName = (rate) => {
    const currencyNames = {
        'USD': 'US Dollar',
        'EUR': 'Euro',
        'SGD': 'Singapore Dollar',
        'THB': 'Thai Baht',
        'JPY': 'Japanese Yen',
        'CNY': 'Chinese Yuan',
        'MYR': 'Malaysian Ringgit',
        'INR': 'Indian Rupee',
        'KRW': 'South Korean Won',
        'HKD': 'Hong Kong Dollar',
        'NZD': 'New Zealand Dollar',
        'AUD': 'Australian Dollar',
        'CAD': 'Canadian Dollar',
        'CHF': 'Swiss Franc',
        'GBP': 'British Pound',
        'VND': 'Vietnamese Dong',
        'PHP': 'Philippine Peso',
        'IDR': 'Indonesian Rupiah',
    };

    const code = rate.currency?.code;
    return currencyNames[code] || code || 'Currency';
};

const goToHistory = (rate) => {
    if (rate.currency?.id || rate.id) {
        window.location.href = route('history', rate.currency?.id || rate.id);
    }
};
</script>