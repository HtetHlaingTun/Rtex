<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden">

        <!-- Header -->
        <div
            class="px-5 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-zinc-800/30 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 sm:w-7 sm:h-7 text-amber-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 21v-8a2 2 0 012-2h2a2 2 0 012 2v8m-6 0h6m-6 0v2h6v-2m6-10h3v8h-3m0-8V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v3m8 0v8m0 0v3m-8-3v3m4-7h.01" />
                    </svg>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Fuel Price History
                    </h2>
                </div>
                <select v-model="selectedRegion" @change="fetchHistory"
                    class="text-xs bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg px-2 py-1.5 font-medium text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="yangon">Yangon</option>
                    <option value="mandalay">Mandalay</option>
                    <option value="naypyidaw">Nay Pyi Taw</option>
                    <option value="ayeyarwady">Ayeyarwady</option>
                    <option value="bago">Bago</option>
                    <option value="magway">Magway</option>
                    <option value="sagaing">Sagaing</option>
                    <option value="thanintharyi">Thanintharyi</option>
                </select>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="p-8 text-center">
            <div class="inline-block w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin">
            </div>
            <p class="mt-2 text-sm text-slate-500">Loading history...</p>
        </div>

        <!-- Table -->
        <div v-else-if="displayHistory.length" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800">
                <thead class="bg-slate-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">
                            Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">
                            92 Octane</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">
                            95 Octane</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">
                            Diesel</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">
                            Premium Diesel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <tr v-for="record in displayHistory" :key="record.date + record.time"
                        class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 transition-colors duration-150">

                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ record.date
                                    }}</span>
                                <span class="text-[8px] text-slate-400">{{ record.time }}</span>
                            </div>
                        </td>

                        <!-- 92 Octane -->
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-amber-600 dark:text-amber-400">
                                    {{ formatNumber(record.octane_92) }}
                                </span>
                                <span v-if="record.trend_92" :class="record.trend_92.color"
                                    class="text-[9px] font-bold">
                                    {{ record.trend_92.icon }} {{ formatNumber(record.trend_92.diff) }}
                                </span>
                            </div>
                        </td>

                        <!-- 95 Octane -->
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-amber-600 dark:text-amber-400">
                                    {{ formatNumber(record.octane_95) }}
                                </span>
                                <span v-if="record.trend_95" :class="record.trend_95.color"
                                    class="text-[9px] font-bold">
                                    {{ record.trend_95.icon }} {{ formatNumber(record.trend_95.diff) }}
                                </span>
                            </div>
                        </td>

                        <!-- Diesel -->
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ formatNumber(record.diesel) }}
                                </span>
                                <span v-if="record.trend_diesel" :class="record.trend_diesel.color"
                                    class="text-[9px] font-bold">
                                    {{ record.trend_diesel.icon }} {{ formatNumber(record.trend_diesel.diff) }}
                                </span>
                            </div>
                        </td>

                        <!-- Premium Diesel -->
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-purple-600 dark:text-purple-400">
                                    {{ formatNumber(record.premium_diesel) }}
                                </span>
                                <span v-if="record.trend_premium_diesel" :class="record.trend_premium_diesel.color"
                                    class="text-[9px] font-bold">
                                    {{ record.trend_premium_diesel.icon }} {{
                                        formatNumber(record.trend_premium_diesel.diff) }}
                                </span>
                            </div>
                        </td>

                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty -->
        <div v-else class="py-12 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-2 text-slate-500 dark:text-zinc-400">No historical data available</p>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { useFuelTrends } from '@/Composables/useFuelTrends'

const { addTrendsToHistory } = useFuelTrends()
const loading = ref(true)
const history = ref([])
const selectedRegion = ref('yangon')

// Add trends to history (API returns newest first)
const historyWithTrends = computed(() => {
    return addTrendsToHistory(history.value)
})

// Reverse to show oldest first in table
const displayHistory = computed(() => {
    return [...historyWithTrends.value].reverse()
})

const fetchHistory = async () => {
    loading.value = true
    try {
        const { data } = await axios.get(`/api/fuel-prices/history/${selectedRegion.value}`)
        if (data.success) {
            history.value = data.history
        }
    } catch (e) {
        console.error('Failed to fetch fuel history:', e)
        history.value = []
    } finally {
        loading.value = false
    }
}

const formatNumber = (v) => (v != null ? v.toLocaleString() : '—')

onMounted(() => {
    fetchHistory()
})

watch(selectedRegion, () => {
    fetchHistory()
})
</script>