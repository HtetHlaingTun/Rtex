<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Header -->
        <div
            class="px-5 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-zinc-800/30 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Fuel Price Estimates
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] text-slate-400">Live Estimate</span>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
            <div class="inline-block w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin">
            </div>
            <p class="text-sm text-slate-500 mt-2">Calculating fuel prices...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-6 text-center">
            <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm text-rose-600 dark:text-rose-400">{{ error }}</p>
            <button @click="fetchPrices"
                class="mt-3 px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600 transition">
                Retry
            </button>
        </div>

        <!-- Price Display -->
        <div v-else class="divide-y divide-slate-100 dark:divide-zinc-800">
            <!-- Global Reference -->
            <div class="px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500">Global Gas (RBOB)</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">
                        ${{ formatNumber(prices.global_gas_usd) }}/gallon
                    </span>
                </div>
                <div class="flex justify-between items-center text-xs mt-1">
                    <span class="text-slate-500">USD/MMK Rate</span>
                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">
                        {{ formatNumber(prices.usd_mmk_rate) }} MMK
                    </span>
                </div>
            </div>

            <!-- Regional Prices with Trends -->
            <div v-for="(regionPrices, region) in prices.prices" :key="region"
                class="px-5 py-4 hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 transition">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white capitalize">
                        {{ formatRegionName(region) }}
                    </h3>
                    <span class="text-[9px] text-slate-400">MMK per liter</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <!-- 92 Octane -->
                    <div class="text-center">
                        <div class="text-[10px] font-bold text-slate-400 mb-1">92 Octane</div>
                        <div class="text-base font-mono font-bold text-amber-600 dark:text-amber-400">
                            {{ formatNumber(regionPrices['92']) }}
                        </div>
                        <div v-if="regionPrices.trend_92" class="flex items-center justify-center gap-1 mt-1">
                            <span :class="regionPrices.trend_92.color" class="text-[10px] font-bold">
                                {{ regionPrices.trend_92.icon }}
                            </span>
                            <span :class="regionPrices.trend_92.color" class="text-[8px] font-mono">
                                {{ Math.abs(regionPrices.trend_92.percent) }}%
                            </span>
                        </div>
                    </div>

                    <!-- 95 Octane -->
                    <div class="text-center">
                        <div class="text-[10px] font-bold text-slate-400 mb-1">95 Octane</div>
                        <div class="text-base font-mono font-bold text-amber-600 dark:text-amber-400">
                            {{ formatNumber(regionPrices['95']) }}
                        </div>
                        <div v-if="regionPrices.trend_95" class="flex items-center justify-center gap-1 mt-1">
                            <span :class="regionPrices.trend_95.color" class="text-[10px] font-bold">
                                {{ regionPrices.trend_95.icon }}
                            </span>
                            <span :class="regionPrices.trend_95.color" class="text-[8px] font-mono">
                                {{ Math.abs(regionPrices.trend_95.percent) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Diesel -->
                    <div class="text-center">
                        <div class="text-[10px] font-bold text-slate-400 mb-1">Diesel</div>
                        <div class="text-base font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            {{ formatNumber(regionPrices.diesel) }}
                        </div>
                        <div v-if="regionPrices.trend_diesel" class="flex items-center justify-center gap-1 mt-1">
                            <span :class="regionPrices.trend_diesel.color" class="text-[10px] font-bold">
                                {{ regionPrices.trend_diesel.icon }}
                            </span>
                            <span :class="regionPrices.trend_diesel.color" class="text-[8px] font-mono">
                                {{ Math.abs(regionPrices.trend_diesel.percent) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="px-5 py-3 bg-slate-50/30 dark:bg-zinc-800/10">
                <p class="text-[8px] text-slate-400 text-center">
                    ⚡ {{ prices.disclaimer }}<br>
                    Last updated: {{ formatTime(prices.last_updated) }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const error = ref(null)
const prices = ref(null)

const fetchPrices = async () => {
    loading.value = true
    error.value = null

    try {
        const response = await axios.get('/api/fuel-prices')
        if (response.data.success) {
            prices.value = response.data.data
        } else {
            error.value = 'Unable to fetch fuel prices'
        }
    } catch (err) {
        console.error('Fuel price fetch error:', err)
        error.value = err.response?.data?.message || 'Failed to load fuel prices'
    } finally {
        loading.value = false
    }
}

const formatNumber = (value) => {
    if (!value && value !== 0) return '—'
    return new Intl.NumberFormat('en-US').format(Math.round(value))
}

const formatRegionName = (region) => {
    const names = {
        'yangon': 'Yangon',
        'mandalay': 'Mandalay',
        'naypyidaw': 'Nay Pyi Taw',
        'ayeyarwady': 'Ayeyarwady',
        'bago': 'Bago',
        'magway': 'Magway',
        'sagaing': 'Sagaing',
        'thanintharyi': 'Thanintharyi'
    }
    return names[region] || region.charAt(0).toUpperCase() + region.slice(1)
}

const formatTime = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleTimeString('en-GB', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(() => {
    fetchPrices()
    // Refresh every 30 minutes
    const interval = setInterval(fetchPrices, 30 * 60 * 1000)
    return () => clearInterval(interval)
})
</script>