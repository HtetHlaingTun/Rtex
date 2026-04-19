<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- TODAY -->
        <div v-if="todayData">

            <!-- HEADER - Grid with Auto-Fit -->
            <div
                class="px-4 sm:px-5 py-4 bg-gradient-to-r from-blue-50/50 to-white dark:from-blue-950/20 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">

                <!-- Responsive Grid: 1 column on mobile, 2 columns on desktop -->
                <div class="grid grid-cols-1 xs:grid-cols-[2fr_4fr] sm:grid-cols-[5fr_4fr] gap-4 ">

                    <!-- Left: Date info -->
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
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

                    <!-- Right: Auto-fit grid for rates and button -->
                    <div
                        class="grid grid-cols-[repeat(auto-fit,minmax(70px,auto))] gap-4 lg:gap-6 justify-start lg:justify-end items-center">

                        <!-- Buy Rate -->
                        <div class="text-left lg:text-right">
                            <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-1">Buy</div>
                            <div
                                class="text-sm sm:text-base font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ $formatMoney(todayData.latestBuyRate) }}
                            </div>
                            <div class="flex justify-start lg:justify-end mt-0.5">
                                <TrendIcon :current="todayData.latestBuyRate" :previous="getPreviousDayBuyRate()"
                                    :show-percentage="true" class="scale-75 origin-left lg:origin-right" />
                            </div>
                        </div>

                        <!-- Sell Rate -->
                        <div class="text-left lg:text-right">
                            <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-1">Sell</div>
                            <div
                                class="text-sm sm:text-base font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                {{ $formatMoney(todayData.latestRate) }}
                            </div>
                            <div class="flex justify-start lg:justify-end mt-0.5">
                                <TrendIcon :current="todayData.latestRate" :previous="getPreviousDaySellRate()"
                                    :show-percentage="true" class="scale-75 origin-left lg:origin-right" />
                            </div>
                        </div>

                        <!-- Button -->
                        <button v-if="todayData.records.length > 1" @click="toggleToday"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[9px] font-black tracking-wider uppercase
                                   bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300
                                   hover:bg-blue-200 dark:hover:bg-blue-500/25 transition-all duration-200 whitespace-nowrap">
                            <svg :class="{ 'rotate-180': showTodayHistory }"
                                class="w-2.5 h-2.5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                            <span>{{ todayData.records.length - 1 }} earlier</span>
                        </button>

                    </div>
                </div>
            </div>

            <!-- TODAY HISTORY DROPDOWN - Responsive Grid -->
            <transition name="slide-down">
                <div v-if="showTodayHistory && todayEarlierRecords.length"
                    class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(r, idx) in todayEarlierRecords" :key="r.id"
                        class="px-4 sm:px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20 hover:bg-slate-100/60 dark:hover:bg-zinc-800/40 transition-colors duration-150">

                        <!-- Auto-fit grid for history items -->
                        <div
                            class="grid grid-cols-[auto_1fr_1fr] sm:grid-cols-[80px_1fr_1fr] gap-3 sm:gap-4 items-center">

                            <!-- Time column -->
                            <div class="flex items-center gap-1.5">
                                <svg class="w-2.5 h-2.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span
                                    class="text-[9px] sm:text-[10px] font-mono font-bold text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                                    {{ formatTime(r.created_at) }}
                                </span>
                            </div>

                            <!-- Buy Rate -->
                            <div class="flex items-center justify-end gap-2 sm:gap-3">
                                <div class="flex flex-col items-end gap-1">
                                    <span
                                        class="text-[11px] sm:text-[13px] font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                        {{ $formatMoney(r.buy_rate) }}
                                    </span>
                                    <TrendIcon :current="r.buy_rate"
                                        :previous="getPreviousRateInDropdown(idx, 'buy_rate')" :show-percentage="true"
                                        class="scale-75" />
                                </div>
                            </div>

                            <!-- Sell Rate -->
                            <div class="flex items-center justify-end gap-2 sm:gap-3">
                                <div class="flex flex-col items-end gap-1">
                                    <span
                                        class="text-[11px] sm:text-[13px] font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                        {{ $formatMoney(r.sell_rate) }}
                                    </span>
                                    <TrendIcon :current="r.sell_rate"
                                        :previous="getPreviousRateInDropdown(idx, 'sell_rate')" :show-percentage="true"
                                        class="scale-75" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </transition>

        </div>

        <!-- PREVIOUS DAYS - Responsive Grid Table -->
        <div v-if="sortedPreviousDays.length" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="(d, idx) in sortedPreviousDays" :key="d.date"
                class="group px-4 sm:px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/30 transition-colors duration-150">

                <!-- Auto-fit grid for historical data -->
                <div
                    class="grid grid-cols-[100px_1fr_1fr_50px] sm:grid-cols-[120px_1fr_1fr_80px] lg:grid-cols-[140px_1fr_1fr_100px] gap-3 sm:gap-4 items-center">

                    <!-- Date column -->
                    <div class="flex flex-col">
                        <span
                            class="text-[11px] sm:text-[12px] font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            {{ formatDateHeader(d.date) }}
                        </span>
                        <span class="text-[7px] sm:text-[8px] text-slate-400 dark:text-zinc-600 font-mono">
                            Final rate
                        </span>
                    </div>

                    <!-- Buy Rate with Trend - Compare with NEXT newer day -->
                    <div class="text-right">
                        <div class="text-[7px] sm:text-[8px] font-black tracking-wider uppercase text-slate-400 mb-0.5">
                            Buy</div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="text-[11px] sm:text-[13px] font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ $formatMoney(d.latestBuyRate) }}
                            </span>
                            <TrendIcon :current="d.latestBuyRate" :previous="getTrendPreviousBuy(idx)"
                                :show-percentage="true" class="scale-75" />
                        </div>
                    </div>

                    <!-- Sell Rate with Trend - Compare with NEXT newer day -->
                    <div class="text-right">
                        <div class="text-[7px] sm:text-[8px] font-black tracking-wider uppercase text-slate-400 mb-0.5">
                            Sell</div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="text-[11px] sm:text-[13px] font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                {{ $formatMoney(d.latestRate) }}
                            </span>
                            <TrendIcon :current="d.latestRate" :previous="getTrendPreviousSell(idx)"
                                :show-percentage="true" class="scale-75" />
                        </div>
                    </div>

                    <!-- Empty column for spacing -->
                    <div></div>

                </div>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="!todayData && !sortedPreviousDays.length"
            class="py-12 sm:py-16 flex flex-col items-center gap-3 text-center">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-zinc-400">No historical data</p>
            <p class="text-[9px] sm:text-[10px] text-slate-400 dark:text-zinc-500">Data will appear after the first rate
                sync</p>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TrendIcon from '@/Components/TrendIcon.vue'

const props = defineProps({
    history: {
        type: Object,
        default: () => ({ data: [] })
    }
})

const showTodayHistory = ref(false)
const toggleToday = () => showTodayHistory.value = !showTodayHistory.value

// Date helpers
const formatDateKey = (d) => {
    if (!d) return ''
    const date = new Date(d)
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const isToday = (dateStr) => {
    const today = new Date().toISOString().slice(0, 10)
    return dateStr === today
}

const formatDate = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric'
    })
}

const formatDateHeader = (dateStr) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    if (date.toDateString() === today.toDateString()) return 'Today'
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday'
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
}

const formatTime = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleTimeString('en-GB', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    })
}

// Core logic - Group by date and sort NEWEST to OLDEST
const groupedByDate = computed(() => {
    if (!props.history?.data?.length) return []

    const groups = {}

    props.history.data.forEach(r => {
        const key = formatDateKey(r.created_at)
        if (!groups[key]) groups[key] = []
        groups[key].push(r)
    })

    return Object.entries(groups).map(([date, records]) => {
        const sorted = [...records].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))

        return {
            date,
            records: sorted,
            latestRate: sorted[0]?.sell_rate || 0,
            latestBuyRate: sorted[0]?.buy_rate || 0
        }
    }).sort((a, b) => new Date(b.date) - new Date(a.date)) // NEWEST first
})

const todayData = computed(() => groupedByDate.value.find(d => isToday(d.date)))
const todayEarlierRecords = computed(() => {
    const records = todayData.value?.records || []
    return records.slice(1)
})

// Previous days - already sorted NEWEST to OLDEST
const previousDaysData = computed(() => {
    return groupedByDate.value.filter(d => !isToday(d.date))
})

// Sorted previous days (already newest to oldest, but ensure correct order)
const sortedPreviousDays = computed(() => {
    return [...previousDaysData.value].sort((a, b) => new Date(b.date) - new Date(a.date))
})

// Today's trends (compare with the most recent previous day)
const getPreviousDaySellRate = () =>
    sortedPreviousDays.value[0]?.latestRate ?? null

const getPreviousDayBuyRate = () =>
    sortedPreviousDays.value[0]?.latestBuyRate ?? null

// HISTORICAL trends — compare each day with the NEXT OLDER day (index + 1)
// sortedPreviousDays is NEWEST → OLDEST, so:
//   idx 0 = 17 Apr → compare with idx 1 = 16 Apr
//   idx 1 = 16 Apr → compare with idx 2 = 15 Apr
//   idx 2 = 15 Apr → no older day → null
const getTrendPreviousSell = (currentIndex) =>
    sortedPreviousDays.value[currentIndex + 1]?.latestRate ?? null

const getTrendPreviousBuy = (currentIndex) =>
    sortedPreviousDays.value[currentIndex + 1]?.latestBuyRate ?? null

// Dropdown trends
const getPreviousRateInDropdown = (index, field) => {
    const records = todayEarlierRecords.value
    if (index === 0) {
        return todayData.value?.records[0]?.[field] || null
    }
    return records[index - 1]?.[field] || null
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