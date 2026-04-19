<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- TODAY -->
        <div v-if="todayData">
            <div
                class="px-4 sm:px-5 py-4 bg-gradient-to-r from-blue-50/50 to-white dark:from-blue-950/20 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                <div class="grid grid-cols-1 xs:grid-cols-[2fr_4fr] sm:grid-cols-[5fr_4fr] gap-4">

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

                    <!-- Right: Rates + button -->
                    <div
                        class="grid grid-cols-[repeat(auto-fit,minmax(70px,auto))] gap-4 lg:gap-6 justify-start lg:justify-end items-center">

                        <!-- Buy -->
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

                        <!-- Sell -->
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

                        <!-- Expand button -->
                        <button v-if="todayData.records.length > 1" @click="toggleToday" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[9px] font-black tracking-wider uppercase
                                    text-blue-700  dark:text-blue-300
                                   transition-all duration-200 whitespace-nowrap">
                            <svg :class="{ 'rotate-180': showTodayHistory }"
                                class="w-2.5 h-2.5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                            <span>{{ todayData.records.length - 1 }} </span>
                        </button>

                    </div>
                </div>
            </div>

            <!-- TODAY DROPDOWN -->
            <transition name="slide-down">
                <div v-if="showTodayHistory && todayEarlierRecords.length"
                    class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(r, idx) in todayEarlierRecords" :key="r.id"
                        class="px-4 sm:px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20 hover:bg-slate-100/60 dark:hover:bg-zinc-800/40 transition-colors duration-150">
                        <div
                            class="grid grid-cols-[auto_1fr_1fr] sm:grid-cols-[80px_1fr_1fr] gap-3 sm:gap-4 items-center">

                            <!-- Time -->
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

                            <!-- Buy -->
                            <div class="flex items-center justify-end">
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

                            <!-- Sell -->
                            <div class="flex items-center justify-end">
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
        <div v-if="previousDays.length" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="(d, idx) in previousDays" :key="d.date"
                class="group px-4 sm:px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-zinc-800/30 transition-colors duration-150">
                <div
                    class="grid grid-cols-[100px_1fr_1fr] sm:grid-cols-[120px_1fr_1fr] lg:grid-cols-[140px_1fr_1fr] gap-3 sm:gap-4 items-center">

                    <!-- Date -->
                    <div class="flex flex-col">
                        <span
                            class="text-[11px] sm:text-[12px] font-bold text-slate-700 dark:text-slate-300 whitespace-nowrap">
                            {{ formatDateHeader(d.date) }}
                        </span>
                        <span class="text-[7px] sm:text-[8px] text-slate-400 dark:text-zinc-600 font-mono">
                            Final rate
                        </span>
                    </div>

                    <!-- Buy -->
                    <div class="text-right">
                        <div class="text-[7px] sm:text-[8px] font-black tracking-wider uppercase text-slate-400 mb-0.5">
                            Buy</div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="text-[11px] sm:text-[13px] font-mono font-black text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                {{ $formatMoney(d.latestBuyRate) }}
                            </span>
                            <TrendIcon :current="d.latestBuyRate"
                                :previous="previousDays[idx + 1]?.latestBuyRate ?? null" :show-percentage="true"
                                class="scale-75" />
                        </div>
                    </div>

                    <!-- Sell -->
                    <div class="text-right">
                        <div class="text-[7px] sm:text-[8px] font-black tracking-wider uppercase text-slate-400 mb-0.5">
                            Sell</div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                class="text-[11px] sm:text-[13px] font-mono font-black text-rose-600 dark:text-rose-400 whitespace-nowrap">
                                {{ $formatMoney(d.latestRate) }}
                            </span>
                            <TrendIcon :current="d.latestRate" :previous="previousDays[idx + 1]?.latestRate ?? null"
                                :show-percentage="true" class="scale-75" />
                        </div>
                    </div>



                </div>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="!todayData && !previousDays.length"
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
    // Pre-grouped from Laravel using Asia/Singapore timezone
    grouped: { type: Array, default: () => [] },
    todayKey: { type: String, default: '' },
})

const showTodayHistory = ref(false)
const toggleToday = () => showTodayHistory.value = !showTodayHistory.value

// ─── Derived from server-grouped data — zero date logic here ─────────────────

const todayData = computed(() =>
    props.grouped.find(d => d.date === props.todayKey) ?? null
)

const previousDays = computed(() =>
    props.grouped.filter(d => d.date !== props.todayKey)
    // already newest → oldest from Laravel sortKeysDesc()
)

const todayEarlierRecords = computed(() =>
    (todayData.value?.records ?? []).slice(1)
)

// ─── Trend helpers ────────────────────────────────────────────────────────────

// Today vs most recent previous day
const getPreviousDaySellRate = () => previousDays.value[0]?.latestRate ?? null
const getPreviousDayBuyRate = () => previousDays.value[0]?.latestBuyRate ?? null

// Dropdown: each earlier record vs the one above it
// idx 0 → compare with records[0] (the latest shown in header)
// idx 1 → compare with todayEarlierRecords[0]
const getPreviousRateInDropdown = (idx, field) => {
    if (idx === 0) return todayData.value?.records[0]?.[field] ?? null
    return todayEarlierRecords.value[idx - 1]?.[field] ?? null
}

// ─── Display helpers — no grouping, pure formatting ──────────────────────────

const formatDate = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatDateHeader = (dateStr) => {
    if (!dateStr) return ''
    if (dateStr === props.todayKey) return 'Today'

    // Compare date strings — safe, no timezone issues
    const [ty, tm, td] = props.todayKey.split('-').map(Number)
    const [y, m, day] = dateStr.split('-').map(Number)
    const diffDays = Math.round(
        (Date.UTC(ty, tm - 1, td) - Date.UTC(y, m - 1, day)) / 86_400_000
    )
    if (diffDays === 1) return 'Yesterday'

    return new Date(Date.UTC(y, m - 1, day))
        .toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
}

const formatTime = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
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