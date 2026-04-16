<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- TODAY -->
        <div v-if="todayData">

            <!-- HEADER -->
            <div
                class="px-5 py-4 bg-gradient-to-r from-blue-50/50 to-white dark:from-blue-950/20 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                <div class="flex justify-between items-center">
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

                    <div class="flex gap-6">

                        <div class="text-right">
                            <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-1">Buy</div>
                            <div class="text-base font-mono font-black text-emerald-600 dark:text-emerald-400">
                                {{ $formatMoney(todayData.latestBuyRate) }}
                            </div>
                            <div class="flex justify-end mt-0.5">
                                <TrendIcon :current="todayData.latestBuyRate" :previous="getSecondLatestBuyRate()"
                                    :show-percentage="true" class="scale-75 origin-right" />
                            </div>
                        </div>

                        <div class="w-px h-10 bg-slate-200 dark:bg-zinc-700 rounded-full"></div>

                        <div class="text-right">
                            <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-1">Sell</div>
                            <div class="text-base font-mono font-black text-rose-600 dark:text-rose-400">
                                {{ $formatMoney(todayData.latestRate) }}
                            </div>
                            <div class="flex justify-end mt-0.5">
                                <TrendIcon :current="todayData.latestRate" :previous="getSecondLatestSellRate()"
                                    :show-percentage="true" class="scale-75 origin-right" />
                            </div>
                        </div>

                        <button v-if="todayData.records.length > 1" @click="toggleToday" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[9px] font-black tracking-wider uppercase
                                   bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300
                                   hover:bg-blue-200 dark:hover:bg-blue-500/25 transition-all duration-200">
                            <svg :class="{ 'rotate-180': showTodayHistory }"
                                class="w-2.5 h-2.5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ todayData.records.length - 1 }} earlier
                        </button>
                    </div>
                </div>
            </div>

            <!-- TODAY HISTORY DROPDOWN -->
            <transition name="slide-down">
                <div v-if="showTodayHistory" class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="r in todayEarlierRecords" :key="r.id"
                        class="grid grid-cols-[80px_1fr_1fr] gap-4 px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20 hover:bg-slate-100/60 dark:hover:bg-zinc-800/40 transition-colors duration-150">

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

                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400">Buy</span>
                            <div class="flex items-center gap-2 flex-col">
                                <span class="text-[13px] font-mono font-black text-emerald-600 dark:text-emerald-400">
                                    {{ $formatMoney(r.buy_rate) }}
                                </span>
                                <TrendIcon :current="r.buy_rate" :previous="getPreviousRate(r, 'buy_rate')"
                                    :show-percentage="true" class="scale-75" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-400">Sell</span>
                            <div class="flex items-center gap-2 flex-col">
                                <span class="text-[13px] font-mono font-black text-rose-600 dark:text-rose-400">
                                    {{ $formatMoney(r.sell_rate) }}
                                </span>
                                <TrendIcon :current="r.sell_rate" :previous="getPreviousRate(r, 'sell_rate')"
                                    :show-percentage="true" class="scale-75" />
                            </div>
                        </div>

                    </div>
                </div>
            </transition>

        </div>

        <!-- PREVIOUS DAYS -->
        <div v-if="previousDaysData.length" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="d in previousDaysData" :key="d.date"
                class="group grid grid-cols-[120px_1fr_1fr_100px] items-center gap-4 px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/30 transition-colors duration-150">

                <div class="flex flex-col">
                    <span class="text-[12px] font-bold text-slate-700 dark:text-slate-300">
                        {{ formatDateHeader(d.date) }}
                    </span>
                    <span class="text-[8px] text-slate-400 dark:text-zinc-600 font-mono">
                        Final rate
                    </span>
                </div>

                <div class="text-right">
                    <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-0.5">Buy</div>
                    <span class="text-[13px] font-mono font-black text-emerald-600 dark:text-emerald-400">
                        {{ $formatMoney(d.latestBuyRate) }}
                    </span>
                </div>

                <div class="text-right">
                    <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-0.5">Sell</div>
                    <span class="text-[13px] font-mono font-black text-rose-600 dark:text-rose-400">
                        {{ $formatMoney(d.latestRate) }}
                    </span>
                </div>

                <div class="flex justify-end">
                    <TrendIcon :current="d.latestRate" :previous="d.prevSellRate" :show-percentage="true"
                        class="scale-90" />
                </div>

            </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="!todayData && !previousDaysData.length" class="py-16 flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">No historical data</p>
            <p class="text-[10px] text-slate-400 dark:text-zinc-500">Data will appear after the first rate sync</p>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TrendIcon from '@/Components/TrendIcon.vue'

const props = defineProps({
    history: Object
})

const showTodayHistory = ref(false)
const toggleToday = () => showTodayHistory.value = !showTodayHistory.value

// Date helpers
const formatDateKey = (d) => {
    const date = new Date(d)
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const isToday = (dateStr) => {
    const today = new Date().toISOString().slice(0, 10)
    return dateStr === today
}

const formatDate = (d) => new Date(d).toLocaleDateString('en-GB', {
    day: '2-digit', month: 'short', year: 'numeric'
})

const formatDateHeader = (dateStr) => {
    const date = new Date(dateStr)
    const today = new Date()
    const yesterday = new Date(today)
    yesterday.setDate(yesterday.getDate() - 1)

    if (date.toDateString() === today.toDateString()) return 'Today'
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday'
    return date.toLocaleDateString('en-GB', { weekday: 'short', day: '2-digit', month: 'short' })
}

const formatTime = (d) => new Date(d).toLocaleTimeString('en-GB', {
    hour: '2-digit', minute: '2-digit', second: '2-digit'
})

// Core logic
const groupedByDate = computed(() => {
    const groups = {}

    props.history?.data?.forEach(r => {
        const key = formatDateKey(r.created_at)
        if (!groups[key]) groups[key] = []
        groups[key].push(r)
    })

    return Object.entries(groups).map(([date, records]) => {
        const unique = Array.from(
            new Map(records.map(r => [`${r.created_at}_${r.buy_rate}_${r.sell_rate}`, r])).values()
        )
        const sorted = unique.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        const clean = sorted.filter((r, i, arr) => {
            if (i === 0) return true
            return r.buy_rate !== arr[i - 1].buy_rate || r.sell_rate !== arr[i - 1].sell_rate
        })
        return {
            date,
            records: clean,
            latestRate: clean[0]?.sell_rate || 0,
            latestBuyRate: clean[0]?.buy_rate || 0
        }
    }).sort((a, b) => b.date.localeCompare(a.date))
})

const todayData = computed(() => groupedByDate.value.find(d => isToday(d.date)))
const todayEarlierRecords = computed(() => todayData.value?.records.slice(1) || [])

const previousDaysData = computed(() => {
    const days = groupedByDate.value.filter(d => !isToday(d.date))
    return days.map((d, i) => {
        const prev = days[i + 1]
        return {
            ...d,
            prevSellRate: prev?.latestRate ?? null
        }
    })
})

// Trend helpers
const getSecondLatestBuyRate = () => {
    const r = todayData.value?.records || []
    for (let i = 1; i < r.length; i++) {
        if (r[i].buy_rate !== r[0].buy_rate) return r[i].buy_rate
    }
    return null
}

const getSecondLatestSellRate = () => {
    const r = todayData.value?.records || []
    for (let i = 1; i < r.length; i++) {
        if (r[i].sell_rate !== r[0].sell_rate) return r[i].sell_rate
    }
    return null
}

const getPreviousRate = (record, field) => {
    const list = todayData.value?.records || []
    const index = list.findIndex(r => r.id === record.id)
    for (let i = index + 1; i < list.length; i++) {
        if (list[i][field] !== record[field]) {
            return list[i][field]
        }
    }
    return null
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