<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Header -->
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-200 dark:border-zinc-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-yellow-200 dark:from-amber-900/30 dark:to-yellow-800/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 5V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V10L15 5H5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5L9 5" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 10H21C22.1046 10 23 10.8954 23 12V16C23 17.1046 22.1046 18 21 18H19" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12H14" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H12" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white">Fuel Price Estimates
                        </h2>
                        <p class="text-xs text-slate-500">Live Estimate</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs text-slate-500">Live</span>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
            <p class="mt-2 text-slate-500">Loading fuel prices...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-red-500">{{ error }}</p>
            <button @click="fetchFuelPrices"
                class="mt-3 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm hover:bg-amber-700 transition">
                Retry
            </button>
        </div>

        <!-- Prices Grid -->
        <div v-else class="p-4 sm:p-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- 92 Octane -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">92 Octane</span>
                        <div
                            class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">
                        {{ formatPrice(octane92) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">per liter</p>
                    <div v-if="trend92" class="mt-1.5 flex items-center gap-1">
                        <span :class="trend92.color" class="text-[10px] font-bold">
                            {{ trend92.icon }} {{ formatNumber(trend92.diff) }}
                        </span>
                        <span class="text-[9px] text-slate-400">vs yesterday</span>
                    </div>
                </div>

                <!-- 95 Octane -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">95 Octane</span>
                        <div
                            class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">
                        {{ formatPrice(octane95) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">per liter</p>
                    <div v-if="trend95" class="mt-1.5 flex items-center gap-1">
                        <span :class="trend95.color" class="text-[10px] font-bold">
                            {{ trend95.icon }} {{ formatNumber(trend95.diff) }}
                        </span>
                        <span class="text-[9px] text-slate-400">vs yesterday</span>
                    </div>
                </div>

                <!-- Diesel -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Diesel</span>
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">
                        {{ formatPrice(diesel) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">per liter</p>
                    <div v-if="trendDiesel" class="mt-1.5 flex items-center gap-1">
                        <span :class="trendDiesel.color" class="text-[10px] font-bold">
                            {{ trendDiesel.icon }} {{ formatNumber(trendDiesel.diff) }}
                        </span>
                        <span class="text-[9px] text-slate-400">vs yesterday</span>
                    </div>
                </div>

                <!-- Premium Diesel -->
                <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Premium Diesel</span>
                        <div
                            class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xl sm:text-2xl font-bold text-slate-800 dark:text-white">
                        {{ formatPrice(premiumDiesel) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">per liter</p>
                    <div v-if="trendPremium" class="mt-1.5 flex items-center gap-1">
                        <span :class="trendPremium.color" class="text-[10px] font-bold">
                            {{ trendPremium.icon }} {{ formatNumber(trendPremium.diff) }}
                        </span>
                        <span class="text-[9px] text-slate-400">vs yesterday</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                <p class="text-xs text-amber-700 dark:text-amber-300 text-center">
                    * Prices are indicative and may vary by location and station. Updated daily.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useFuelTrends } from '@/Composables/useFuelTrends'

const { calculateTrend } = useFuelTrends()

const loading = ref(true)
const error = ref(null)
const octane92 = ref(0)
const octane95 = ref(0)
const diesel = ref(0)
const premiumDiesel = ref(0)
const trend92 = ref(null)
const trend95 = ref(null)
const trendDiesel = ref(null)
const trendPremium = ref(null)
let interval = null

const fetchFuelPrices = async () => {
    loading.value = true
    error.value = null

    try {
        // ✅ USE HISTORY API - It has ALL records including the latest
        const historyRes = await fetch('/api/fuel-prices/history/yangon')

        if (!historyRes.ok) {
            throw new Error(`HTTP ${historyRes.status}`)
        }

        const historyData = await historyRes.json()

        if (historyData.success && historyData.history?.length) {
            // ✅ Sort by date AND time to get absolute latest record
            const sortedHistory = [...historyData.history].sort((a, b) => {
                const dateA = new Date(a.date + ' ' + a.time)
                const dateB = new Date(b.date + ' ' + b.time)
                return dateB - dateA  // Newest first
            })

            const latestRecord = sortedHistory[0]

            // ✅ Set current prices from the absolute latest record
            octane92.value = latestRecord.octane_92 || 0
            octane95.value = latestRecord.octane_95 || 0
            diesel.value = latestRecord.diesel || 0
            premiumDiesel.value = latestRecord.premium_diesel || 0

            // Calculate trend vs yesterday
            const today = new Date().toISOString().split('T')[0]
            const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0]

            const todayRecords = historyData.history.filter(r => r.date === today)
            const yesterdayRecords = historyData.history.filter(r => r.date === yesterday)

            // Get latest record for today
            const todayLatest = todayRecords.sort((a, b) =>
                new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time)
            )[0]

            // Get latest record for yesterday
            const yesterdayLatest = yesterdayRecords.sort((a, b) =>
                new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time)
            )[0]

            if (todayLatest && yesterdayLatest) {
                trend92.value = calculateTrend(todayLatest.octane_92, yesterdayLatest.octane_92)
                trend95.value = calculateTrend(todayLatest.octane_95, yesterdayLatest.octane_95)
                trendDiesel.value = calculateTrend(todayLatest.diesel, yesterdayLatest.diesel)
                trendPremium.value = calculateTrend(todayLatest.premium_diesel, yesterdayLatest.premium_diesel)
            }
        } else {
            error.value = 'No fuel price data available'
        }
    } catch (err) {
        console.error('Fuel API Error:', err)
        error.value = 'Unable to fetch fuel prices'
    } finally {
        loading.value = false
    }
}

const formatPrice = (v) => v ? `MMK ${v.toLocaleString()}` : 'MMK —'
const formatNumber = (v) => v != null ? v.toLocaleString() : '—'

onMounted(() => {
    fetchFuelPrices()
    interval = setInterval(fetchFuelPrices, 300000) // Refresh every 5 minutes
})

onUnmounted(() => {
    if (interval) clearInterval(interval)
})
</script>