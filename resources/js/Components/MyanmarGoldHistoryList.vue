<template>
    <div
        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-800 shadow-sm bg-white dark:bg-zinc-900">

        <!-- ==================== TODAY SECTION ==================== -->
        <div v-if="todayRecords.length > 0">
            <div
                class="px-5 py-4 bg-gradient-to-r from-slate-50 to-white dark:from-zinc-800/30 dark:to-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                <div class="flex justify-between items-center">
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

                    <div class="flex gap-6">
                        <div class="text-right">
                            <div class="text-[8px] font-black tracking-wider uppercase text-slate-400 mb-1">Price (MMK)
                            </div>
                            <div class="text-base font-mono font-black" :class="priceColor">
                                {{ $formatMoney(todayRecords[0]?.price, 0, true) }}
                            </div>
                            <div class="flex justify-end mt-0.5">
                                <TrendIcon :current="todayRecords[0]?.price" :previous="todayRecords[1]?.price"
                                    :show-percentage="true" class="scale-75 origin-right" />
                            </div>
                        </div>

                        <button v-if="todayRecords.length > 1" @click="toggleToday"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[9px] font-black tracking-wider uppercase bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all duration-200">
                            <svg :class="{ 'rotate-180': showTodayHistory }"
                                class="w-2.5 h-2.5 transition-transform duration-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                            {{ todayRecords.length - 1 }} earlier
                        </button>
                    </div>
                </div>
            </div>

            <!-- TODAY DROPDOWN (Earlier records today) -->
            <transition name="slide-down">
                <div v-if="showTodayHistory" class="divide-y divide-slate-100 dark:divide-zinc-800">
                    <div v-for="(r, idx) in todayRecords.slice(1)" :key="r.id"
                        class="grid grid-cols-[80px_1fr] gap-4 px-5 py-3 bg-slate-50/50 hover:bg-slate-100/60 transition-colors duration-150">

                        <div class="flex items-center gap-1.5">
                            <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] font-mono font-bold text-slate-500">{{ formatTime(r.created_at)
                            }}</span>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400">MMK</span>
                                <span class="text-[13px] font-mono font-black" :class="priceColor">
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

        <!-- DEBUG INFO (remove after fixing) -->
        <!-- <div v-if="allRecords.length > 0" class="px-6 py-2 text-center text-xs border-t border-slate-200">
            <details>
                <summary class="text-slate-400 cursor-pointer">Debug Info</summary>
                <div class="mt-2 text-left text-slate-500">
                    <p>Total records: {{ allRecords.length }}</p>
                    <p>Today records: {{ todayRecords.length }}</p>
                    <p>Other records: {{ otherRecords.length }}</p>
                    <p>Today date: {{ todayDateString }}</p>
                    <p>Sample record date: {{ allRecords[0]?.created_at }}</p>
                </div>
            </details>
        </div> -->

        <!-- EMPTY STATE -->
        <div v-if="allRecords.length === 0" class="py-16 flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-500">No historical data</p>
            <p class="text-[10px] text-slate-400">Data will appear after the first price sync</p>
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
    // Try price_date first, fallback to created_at
    return record.price_date || record.created_at
}

// Get all records and transform to use correct date
const allRecords = computed(() => {
    return (props.history?.data || []).map(record => ({
        ...record,
        // Ensure we have a proper date field
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

// Log for debugging
console.log('Total records:', allRecords.value.length)
console.log('Today records:', todayRecords.value.length)
console.log('Other records:', otherRecords.value.length)
if (allRecords.value[0]) {
    console.log('Sample record dates:', {
        created_at: allRecords.value[0].created_at,
        price_date: allRecords.value[0].price_date,
        effective_date: allRecords.value[0].effective_date
    })
}

// Helper functions
const formatDate = (d) => {
    if (!d) return ''
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
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