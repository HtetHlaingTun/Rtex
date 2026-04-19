<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- TODAY -->
        <div v-if="todayData">
            <div
                class="px-4 sm:px-5 py-4 bg-gradient-to-r from-blue-50/50 to-white dark:from-blue-950/20 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                <div class="grid grid-cols-1 xs:grid-cols-[2fr_4fr] sm:grid-cols-[5fr_4fr] gap-4">

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

                    <div
                        class="grid grid-cols-[repeat(auto-fit,minmax(70px,auto))] gap-4 lg:gap-6 justify-start lg:justify-end items-center">

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

            <transition name="slide-down">
                <div v-if="showTodayHistory && todayEarlierRecords.length"
                    class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(r, idx) in todayEarlierRecords" :key="r.id"
                        class="px-4 sm:px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20 hover:bg-slate-100/60 dark:hover:bg-zinc-800/40 transition-colors duration-150">
                        <div
                            class="grid grid-cols-[auto_1fr_1fr] sm:grid-cols-[80px_1fr_1fr] gap-3 sm:gap-4 items-center">

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

        <!-- PREVIOUS DAYS -->
        <div v-if="sortedPreviousDays.length" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="(d, idx) in sortedPreviousDays" :key="d.date"
                class="group px-4 sm:px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/30 transition-colors duration-150">
                <div
                    class="grid grid-cols-[100px_1fr_1fr_50px] sm:grid-cols-[120px_1fr_1fr_80px] lg:grid-cols-[140px_1fr_1fr_100px] gap-3 sm:gap-4 items-center">

                    <div class="flex flex-col">
                        <span
                            class="text-[11px] sm:text-[12px] font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            {{ formatDateHeader(d.date) }}
                        </span>
                        <span class="text-[7px] sm:text-[8px] text-slate-400 dark:text-zinc-600 font-mono">
                            Final rate
                        </span>
                    </div>

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

// ─── Date helpers ────────────────────────────────────────────────────────────
// ALL three functions use the same local-time approach so SSR (UTC) and
// client (Myanmar UTC+6:30) are each internally consistent.

/** Returns "YYYY-MM-DD" in LOCAL time — used as the group key. */
const localDateKey = (d) => {
    if (!d) return ''
    const dt = new Date(d)
    return [
        dt.getFullYear(),
        String(dt.getMonth() + 1).padStart(2, '0'),
        String(dt.getDate()).padStart(2, '0'),
    ].join('-')
}

/** "YYYY-MM-DD" for right now in LOCAL time. */
const todayKey = () => localDateKey(new Date())

/** "YYYY-MM-DD" for yesterday in LOCAL time. */
const yesterdayKey = () => {
    const d = new Date()
    d.setDate(d.getDate() - 1)
    return localDateKey(d)
}

const isToday = (dateStr) => dateStr === todayKey()

const formatDate = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

/**
 * Display label for a YYYY-MM-DD key.
 * Compares keys (not Date objects) so it stays in sync with localDateKey.
 */
const formatDateHeader = (dateStr) => {
    if (!dateStr) return ''
    if (dateStr === todayKey()) return 'Today'
    if (dateStr === yesterdayKey()) return 'Yesterday'
    // Parse as local midnight — split avoids any UTC offset shift
    const [y, m, day] = dateStr.split('-').map(Number)
    return new Date(y, m - 1, day).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
}

const formatTime = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

// ─── Core grouping ───────────────────────────────────────────────────────────

const groupedByDate = computed(() => {
    if (!props.history?.data?.length) return []

    const groups = {}
    props.history.data.forEach(r => {
        const key = localDateKey(r.created_at)
        if (!groups[key]) groups[key] = []
        groups[key].push(r)
    })

    return Object.entries(groups)
        .map(([date, records]) => {
            const sorted = [...records].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            return {
                date,
                records: sorted,
                latestRate: sorted[0]?.sell_rate ?? 0,
                latestBuyRate: sorted[0]?.buy_rate ?? 0,
            }
        })
        .sort((a, b) => b.date.localeCompare(a.date)) // NEWEST first — string compare is safe for ISO dates
})

const todayData = computed(() =>
    groupedByDate.value.find(d => isToday(d.date)) ?? null
)

const todayEarlierRecords = computed(() =>
    (todayData.value?.records ?? []).slice(1)
)

const sortedPreviousDays = computed(() =>
    groupedByDate.value.filter(d => !isToday(d.date))
    // already sorted newest → oldest from groupedByDate
)

// ─── Trend helpers ───────────────────────────────────────────────────────────

// Today row: compare against most recent previous day
const getPreviousDaySellRate = () => sortedPreviousDays.value[0]?.latestRate ?? null
const getPreviousDayBuyRate = () => sortedPreviousDays.value[0]?.latestBuyRate ?? null

// Historical rows: compare each day against the next OLDER day (idx + 1)
// sortedPreviousDays is newest → oldest, so idx+1 is always the older entry
const getTrendPreviousSell = (idx) => sortedPreviousDays.value[idx + 1]?.latestRate ?? null
const getTrendPreviousBuy = (idx) => sortedPreviousDays.value[idx + 1]?.latestBuyRate ?? null

// Dropdown rows: compare each earlier record against the one above it
// idx 0 = second-latest → previous is the latest (records[0])
// idx 1 = third-latest  → previous is records[idx] in todayEarlierRecords (idx-1 ... +1 offset = idx in earlierRecords)
const getPreviousRateInDropdown = (idx, field) => {
    if (idx === 0) return todayData.value?.records[0]?.[field] ?? null
    return todayEarlierRecords.value[idx - 1]?.[field] ?? null
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