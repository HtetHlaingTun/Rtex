<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- ==================== TODAY SECTION ==================== -->
        <div v-if="todayRecords.length > 0">
            <div
                class="px-4 sm:px-5 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-zinc-800/30 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full"
                                    :class="pingColor"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2" :class="dotColor"></span>
                            </span>
                            <span class="text-[11px] font-black tracking-wider uppercase"
                                :class="textColor">Today</span>
                        </div>
                        <div class="text-[9px] text-slate-400 font-mono mt-0.5">
                            {{ formatDate(todayRecords[0]?.created_at) }}
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 sm:gap-6 w-full sm:w-auto justify-end">
                        <div class="text-right flex-1 sm:flex-none min-w-[120px]">
                            <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-1">Price (MMK)
                            </div>
                            <div class="text-base sm:text-base font-mono font-black" :class="priceColor">
                                {{ $formatMoney(todayRecords[0]?.price, 0, true) }}
                            </div>
                            <div class="flex justify-end mt-0.5">
                                <TrendIcon :current="todayRecords[0]?.price" :previous="todayRecords[1]?.price"
                                    :show-percentage="true" class="scale-75 origin-right" />
                            </div>
                        </div>

                        <button v-if="todayRecords.length > 1" @click="toggleToday" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[9px] font-black tracking-wider uppercase 
                                   bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300 
                                   hover:bg-slate-200 dark:hover:bg-zinc-700 transition-all duration-200 shrink-0">
                            <svg :class="{ 'rotate-180': showTodayHistory }"
                                class="w-2.5 h-2.5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>


                        </button>
                    </div>
                </div>
            </div>

            <!-- TODAY DROPDOWN (Earlier records today) - Responsive -->
            <transition name="slide-down">
                <div v-if="showTodayHistory" class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(r, idx) in todayRecords.slice(1)" :key="r.id"
                        class="grid grid-cols-[70px_1fr] sm:grid-cols-[80px_1fr] gap-3 sm:gap-4 px-4 sm:px-5 py-3 bg-slate-50/50 dark:bg-zinc-800/20 hover:bg-slate-100/60 dark:hover:bg-zinc-800/40 transition-colors duration-150">

                        <div class="flex items-center gap-1.5">
                            <svg class="w-2.5 h-2.5 text-slate-400 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span
                                class="text-[9px] sm:text-[10px] font-mono font-bold text-slate-500 dark:text-zinc-400">
                                {{ formatTime(r.created_at) }}
                            </span>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[9px] sm:text-[10px] font-bold text-slate-400 hidden sm:inline">MMK</span>
                                <span class="text-[11px] sm:text-[13px] font-mono font-black" :class="priceColor">
                                    {{ $formatMoney(r.price, 0, true) }}
                                </span>
                            </div>
                            <TrendIcon :current="r.price" :previous="todayRecords[idx + 2]?.price"
                                :show-percentage="true" class="scale-75" />
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- ==================== OTHER RECORDS TABLE ==================== -->
        <div v-if="otherRecords.length > 0">
            <MyanmarOtherRecordsTable :records="otherRecords" :systemType="systemType" />
        </div>

        <!-- EMPTY STATE - Responsive -->
        <div v-if="allRecords.length === 0" class="py-12 sm:py-16 flex flex-col items-center gap-3 text-center">
            <div
                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-zinc-400">No historical data</p>
            <p class="text-[9px] sm:text-[10px] text-slate-400 dark:text-zinc-500">Data will appear after the first
                price sync</p>
        </div>

    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TrendIcon from '@/Components/TrendIcon.vue'
import MyanmarOtherRecordsTable from '@/Components/MyanmarOtherRecordsTable.vue'

const props = defineProps({
    history: { type: Object, required: true },
    systemType: { type: String, required: true }
})

// Color configuration
const isNew = computed(() => props.systemType === 'new')
const pingColor = computed(() => isNew.value ? 'bg-emerald-400' : 'bg-amber-400')
const dotColor = computed(() => isNew.value ? 'bg-emerald-500' : 'bg-amber-500')
const textColor = computed(() => isNew.value ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400')
const priceColor = computed(() => isNew.value ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-500')

const showTodayHistory = ref(false)
const toggleToday = () => showTodayHistory.value = !showTodayHistory.value

// Helper to get the actual date from record (use price_date or created_at)
const getRecordDate = (record) => {
    return record.price_date || record.created_at
}

// Get all records and transform to use correct date
const allRecords = computed(() => {
    return (props.history?.data || []).map(record => ({
        ...record,
        effective_date: getRecordDate(record)
    }))
})

// Get today's date as YYYY-MM-DD
const todayDateString = computed(() => {
    const today = new Date()
    return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
})

// Check if a record is from today using its effective date
const isRecordFromToday = (record) => {
    if (!record?.effective_date) return false
    const recordDate = new Date(record.effective_date)
    const recordDateString = `${recordDate.getFullYear()}-${String(recordDate.getMonth() + 1).padStart(2, '0')}-${String(recordDate.getDate()).padStart(2, '0')}`
    return recordDateString === todayDateString.value
}

// Separate records
const todayRecords = computed(() => {
    return [...allRecords.value]
        .filter(r => isRecordFromToday(r))
        .sort((a, b) => new Date(b.effective_date) - new Date(a.effective_date))
})

const otherRecords = computed(() => {
    return [...allRecords.value]
        .filter(r => !isRecordFromToday(r))
        .sort((a, b) => new Date(b.effective_date) - new Date(a.effective_date))
})

// Helper functions
const formatDate = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatTime = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

// Debug logs (remove in production)
if (import.meta.env.DEV) {
    console.log('Myanmar Gold Component - Debug:', {
        totalRecords: allRecords.value.length,
        todayRecords: todayRecords.value.length,
        otherRecords: otherRecords.value.length,
        systemType: props.systemType
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

/* Touch-friendly tap highlights */
@media (max-width: 640px) {
    button:active {
        transform: scale(0.96);
    }
}
</style>