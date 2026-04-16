<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- ==================== TODAY SECTION ==================== -->
        <div v-if="todayData">
            <div
                class="px-5 py-4 bg-gradient-to-r from-blue-50/50 to-white dark:from-blue-950/20 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                            </span>
                            <span
                                class="text-[11px] font-black tracking-wider uppercase text-blue-600 dark:text-blue-400">
                                Today
                            </span>
                        </div>
                        <div class="text-[9px] text-slate-400 dark:text-zinc-500 font-mono mt-0.5">
                            {{ formatDate(todayData.records[0]?.created_at) }}
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <!-- USD Price -->
                        <div class="text-right">

                            <div class="text-base font-mono font-black text-blue-600 dark:text-blue-400">
                                ${{ formatMoney(todayData.latestPrice, 2) }}
                            </div>
                            <div class="flex justify-end mt-0.5">
                                <TrendIcon :current="todayData.latestPrice" :previous="getSecondLatestPrice('price')"
                                    :show-percentage="true" class="scale-75 origin-right" />
                            </div>
                        </div>

                        <!-- SGD Price -->
                        <div class="text-right">

                            <div class="text-base font-mono font-black text-blue-600 dark:text-blue-400">
                                S${{ formatMoney(todayData.latestSgdPrice, 2) }}
                            </div>
                            <div class="flex justify-end mt-0.5">
                                <TrendIcon :current="todayData.latestSgdPrice"
                                    :previous="getSecondLatestPrice('sgd_price')" :show-percentage="true"
                                    class="scale-75 origin-right" />
                            </div>
                        </div>

                        <!-- Show button for all records (no warning) -->
                        <button v-if="todayEarlierRecords.length > 0" @click="toggleToday" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[9px] font-black tracking-wider uppercase
           bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300
           hover:bg-blue-200 dark:hover:bg-blue-500/25 transition-all duration-200">
                            <svg :class="{ 'rotate-180': showTodayHistory }"
                                class="w-2.5 h-2.5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ todayEarlierRecords.length }} earlier
                        </button>
                    </div>
                </div>
            </div>

            <!-- TODAY DROPDOWN - Show all records (no limit) -->
            <transition name="slide-down">
                <div v-if="showTodayHistory" class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(r, idx) in todayEarlierRecords" :key="r.id"
                        class="grid grid-cols-[80px_1fr] gap-4 px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20 hover:bg-slate-100/60 dark:hover:bg-zinc-800/40 transition-colors duration-150">

                        <!-- Time column -->
                        <div class="flex items-center gap-1.5">
                            <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-mono font-bold text-slate-500 dark:text-zinc-400">
                                {{ formatTime(r.created_at) }}
                            </span>
                        </div>

                        <!-- Price columns -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- USD -->
                            <div class="flex flex-col items-end gap-1">
                                <div class="flex items-center gap-2">

                                    <span class="text-[13px] font-mono font-black text-blue-600 dark:text-blue-400">
                                        ${{ formatMoney(r.price, 2) }}
                                    </span>
                                </div>
                                <TrendIcon :current="r.price" :previous="getPreviousPrice(r, 'price')"
                                    :show-percentage="true" class="scale-75" />
                            </div>

                            <!-- SGD -->
                            <div v-if="r.sgd_price" class="flex flex-col items-end gap-1">
                                <div class="flex items-center gap-2">

                                    <span class="text-[13px] font-mono font-black text-blue-600 dark:text-blue-400">
                                        S${{ formatMoney(r.sgd_price, 2) }}
                                    </span>
                                </div>
                                <TrendIcon :current="r.sgd_price" :previous="getPreviousPrice(r, 'sgd_price')"
                                    :show-percentage="true" class="scale-75" />
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- ==================== OTHER RECORDS TABLE ==================== -->
        <div v-if="otherRecordsList.length > 0">
            <WorldOtherRecordsTable :records="otherRecordsList" />
        </div>


        <!-- DEBUG INFO -->
        <!-- <div v-if="hasData" class="px-6 py-2 text-center text-xs border-t border-slate-200 dark:border-zinc-800">
            <details>
                <summary class="text-slate-400 cursor-pointer hover:text-slate-600 transition-colors">Debug Info
                </summary>
                <div class="mt-2 text-left text-slate-500 dark:text-zinc-400 space-y-1">
                    <p>Total records: {{ totalRecords }}</p>
                    <p>Today records: {{ todayData?.records?.length || 0 }}</p>
                    <p>Other records: {{ otherRecordsList.length }}</p>
                    <p>Unique days: {{ uniqueDaysCount }}</p>
                    <p>Today date: {{ todayDateString }}</p>
                    <p>Needs consolidation: {{ needsConsolidation ? 'Yes' : 'No' }}</p>
                </div>
            </details>
        </div> -->

        <!-- EMPTY STATE -->
        <div v-if="!hasData" class="py-16 flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">No historical data</p>
            <p class="text-[10px] text-slate-400 dark:text-zinc-500">Data will appear after the first price sync</p>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TrendIcon from '@/Components/TrendIcon.vue'
import WorldOtherRecordsTable from '@/Components/WorldOtherRecordsTable.vue'

const props = defineProps({
    history: { type: Object, required: true }
})

const showTodayHistory = ref(false)
const toggleToday = () => showTodayHistory.value = !showTodayHistory.value

// Helper functions
const formatDateKey = (d) => {
    if (!d) return ''
    return new Date(d).toISOString().slice(0, 10)
}

const isToday = (dateStr) => {
    if (!dateStr) return false
    return dateStr === todayDateString.value
}

const formatDate = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatTime = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

const formatMoney = (value, decimals = 2) => {
    if (value === null || value === undefined) return '0.00'
    return Number(value).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    })
}

// Get today's date as YYYY-MM-DD
const todayDateString = computed(() => {
    const today = new Date()
    return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
})

// Get all records from history
const allRecords = computed(() => {
    return props.history?.data || []
})

// Group records by date
const groupedByDate = computed(() => {
    if (!allRecords.value.length) return []

    const groups = {}
    allRecords.value.forEach(r => {
        const key = formatDateKey(r.created_at)
        if (!groups[key]) groups[key] = []
        groups[key].push(r)
    })

    return Object.entries(groups)
        .map(([date, records]) => {
            // Sort by created_at descending (newest first)
            const sorted = [...records].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            return {
                date,
                records: sorted,
                latestPrice: sorted[0]?.price || 0,
                latestSgdPrice: sorted[0]?.sgd_price || 0,
                recordCount: sorted.length
            }
        })
        .sort((a, b) => b.date.localeCompare(a.date))
})

// Check if consolidation is needed (any day with more than 5 records)
const needsConsolidation = computed(() => {
    return groupedByDate.value.some(group => group.recordCount > 5)
})

// Unique days count
const uniqueDaysCount = computed(() => groupedByDate.value.length)

// Get today's data
const todayData = computed(() => groupedByDate.value.find(d => isToday(d.date)))

// Get earlier records from today (excluding the latest) - limit to reasonable number
const todayEarlierRecords = computed(() => {
    const records = todayData.value?.records || []
    // Return all but limit UI display later
    return records.slice(1)
})

// Get all records except today
const otherRecordsList = computed(() => {
    return allRecords.value.filter(r => !isToday(formatDateKey(r.created_at)))
})

// Check if there's any data
const hasData = computed(() => allRecords.value.length > 0)

// Get second latest price for trend calculation
const getSecondLatestPrice = (field) => {
    const records = todayData.value?.records || []
    return records.length >= 2 ? records[1]?.[field] : null
}

// Get previous price for a specific record (for dropdown trend icons)
const getPreviousPrice = (record, field) => {
    const records = todayData.value?.records || []
    const index = records.findIndex(r => r.id === record.id)
    return index + 1 < records.length ? records[index + 1][field] : null
}

// Statistics for debug
const totalRecords = computed(() => allRecords.value.length)

// Log for debugging
if (import.meta.env.DEV) {
    console.log('World Gold Component - Debug Info:', {
        totalRecords: totalRecords.value,
        todayRecords: todayData.value?.records?.length || 0,
        otherRecords: otherRecordsList.value.length,
        uniqueDays: uniqueDaysCount.value,
        needsConsolidation: needsConsolidation.value,
        groupedDates: groupedByDate.value.map(g => ({ date: g.date, count: g.recordCount }))
    })
}
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease-out;
}

.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>