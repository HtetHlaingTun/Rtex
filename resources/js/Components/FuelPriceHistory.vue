<template>
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden">
        <!-- Header -->
        <div
            class="px-5 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-zinc-800/30 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Fuel Price History
                    </h2>
                </div>
                <select v-model="selectedRegion" @change="fetchHistory"
                    class="text-xs bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg px-2 py-1">
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
        </div>

        <!-- History Table -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-800">
                <thead class="bg-slate-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase text-slate-400">Date</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase text-slate-400">92 Octane</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase text-slate-400">95 Octane</th>
                        <th class="px-4 py-3 text-right text-[10px] font-black uppercase text-slate-400">Diesel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <tr v-for="record in history" :key="record.date"
                        class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 transition">
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ record.date
                                    }}</span>
                                <span class="text-[8px] text-slate-400">{{ record.time }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-amber-600">{{
                                    formatNumber(record.octane_92) }}</span>
                                <span v-if="record.trend_92" :class="record.trend_92.color" class="text-[9px]">
                                    {{ record.trend_92.icon }} {{ Math.abs(record.trend_92.percent) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-amber-600">{{
                                    formatNumber(record.octane_95) }}</span>
                                <span v-if="record.trend_95" :class="record.trend_95.color" class="text-[9px]">
                                    {{ record.trend_95.icon }} {{ Math.abs(record.trend_95.percent) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex flex-col items-end">
                                <span class="text-[13px] font-mono font-bold text-emerald-600">{{
                                    formatNumber(record.diesel) }}</span>
                                <span v-if="record.trend_diesel" :class="record.trend_diesel.color" class="text-[9px]">
                                    {{ record.trend_diesel.icon }} {{ Math.abs(record.trend_diesel.percent) }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const history = ref([])
const selectedRegion = ref('yangon')

const fetchHistory = async () => {
    loading.value = true
    try {
        const response = await axios.get(`/api/fuel-prices/history/${selectedRegion.value}`)
        if (response.data.success) {
            history.value = response.data.history
        }
    } catch (error) {
        console.error('Failed to fetch history:', error)
    } finally {
        loading.value = false
    }
}

const formatNumber = (value) => {
    if (!value) return '—'
    return new Intl.NumberFormat('en-US').format(value)
}

onMounted(() => {
    fetchHistory()
})
</script>