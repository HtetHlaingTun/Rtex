<template>
    <div>
        <!-- Table Header -->
        <div
            class="grid grid-cols-[100px_1fr_1fr_70px_70px] sm:grid-cols-[120px_1fr_1fr_80px_80px] gap-2 sm:gap-4 px-4 sm:px-5 py-3 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800 text-[10px] font-black uppercase tracking-wider text-slate-400">
            <div>Date</div>
            <div class="text-right">USD</div>
            <div class="text-right">SGD</div>
            <div class="text-right">USD Trend</div>
            <div class="text-right">SGD Trend</div>
        </div>

        <!-- Table Body - Paginated -->
        <div v-if="paginatedGroups.length > 0" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="group in paginatedGroups" :key="group.date"
                class="hover:bg-slate-50/50 dark:hover:bg-zinc-800/20 transition-colors duration-150">

                <!-- Main row -->
                <div
                    class="grid grid-cols-[100px_1fr_1fr_70px_70px] sm:grid-cols-[120px_1fr_1fr_80px_80px] gap-2 sm:gap-4 px-4 sm:px-5 py-3 items-center">

                    <!-- Date column -->
                    <div class="flex flex-col">
                        <span class="text-[11px] sm:text-[12px] font-bold text-slate-700 dark:text-slate-300">
                            {{ formatDisplayDate(group.date) }}
                        </span>
                        <span v-if="group.records.length > 1" class="text-[8px] sm:text-[9px] text-slate-400">
                            {{ group.records.length }} entries
                        </span>
                    </div>

                    <!-- USD Price -->
                    <div class="text-right">
                        <span
                            class="text-[11px] sm:text-[13px] font-mono font-medium text-slate-600 dark:text-zinc-400">
                            ${{ formatMoney(group.latestPrice, 2) }}
                        </span>
                    </div>

                    <!-- SGD Price -->
                    <div class="text-right">
                        <span v-if="group.latestSgdPrice"
                            class="text-[11px] sm:text-[13px] font-mono font-medium text-slate-600 dark:text-zinc-400">
                            S${{ formatMoney(group.latestSgdPrice, 2) }}
                        </span>
                        <span v-else class="text-[10px] text-slate-400">—</span>
                    </div>

                    <!-- USD Trend -->
                    <div class="flex justify-end">
                        <TrendIcon :current="group.latestPrice" :previous="getPreviousDayPrice(group.date, 'price')"
                            :show-percentage="true" class="scale-75 sm:scale-90" />
                    </div>

                    <!-- SGD Trend -->
                    <div class="flex justify-end">
                        <TrendIcon v-if="group.latestSgdPrice" :current="group.latestSgdPrice"
                            :previous="getPreviousDayPrice(group.date, 'sgd_price')" :show-percentage="true"
                            class="scale-75 sm:scale-90" />
                        <span v-else class="text-[10px] text-slate-400">—</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-8 text-center">
            <p class="text-sm text-slate-400">No historical records found</p>
        </div>

        <!-- Pagination -->
        <div v-if="totalGroups > itemsPerPage"
            class="flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 bg-slate-50/50 dark:bg-zinc-800/20 border-t border-slate-200 dark:border-zinc-800">
            <span class="text-[9px] sm:text-[10px] font-bold text-slate-400">
                {{ totalGroups }} records · Page {{ currentPage }} of {{ totalPages }}
            </span>
            <div class="flex gap-1.5 flex-wrap justify-center">
                <button @click="goToPage(1)" :disabled="currentPage === 1"
                    class="min-w-[28px] sm:min-w-[32px] h-6 sm:h-7 flex items-center justify-center px-1.5 sm:px-2 text-[9px] sm:text-[10px] font-black rounded-lg transition-all"
                    :class="currentPage === 1 ? 'opacity-30 cursor-not-allowed bg-white border border-slate-200' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400'">«</button>
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                    class="min-w-[28px] sm:min-w-[32px] h-6 sm:h-7 flex items-center justify-center px-1.5 sm:px-2 text-[9px] sm:text-[10px] font-black rounded-lg transition-all"
                    :class="currentPage === 1 ? 'opacity-30 cursor-not-allowed bg-white border border-slate-200' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400'">‹</button>

                <button v-for="page in visiblePages" :key="page" @click="goToPage(page)"
                    class="min-w-[28px] sm:min-w-[32px] h-6 sm:h-7 flex items-center justify-center px-1.5 sm:px-2 text-[9px] sm:text-[10px] font-black rounded-lg transition-all"
                    :class="page === currentPage ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400'">
                    {{ page }}
                </button>

                <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                    class="min-w-[28px] sm:min-w-[32px] h-6 sm:h-7 flex items-center justify-center px-1.5 sm:px-2 text-[9px] sm:text-[10px] font-black rounded-lg transition-all"
                    :class="currentPage === totalPages ? 'opacity-30 cursor-not-allowed bg-white border border-slate-200' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400'">›</button>
                <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages"
                    class="min-w-[28px] sm:min-w-[32px] h-6 sm:h-7 flex items-center justify-center px-1.5 sm:px-2 text-[9px] sm:text-[10px] font-black rounded-lg transition-all"
                    :class="currentPage === totalPages ? 'opacity-30 cursor-not-allowed bg-white border border-slate-200' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-400'">»</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import TrendIcon from '@/Components/TrendIcon.vue'

const props = defineProps({
    records: { type: Array, required: true, default: () => [] }
})

// Pagination state
const currentPage = ref(1)
const itemsPerPage = 10

const formatDisplayDate = (dateStr) => {
    if (!dateStr) return ''
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatMoney = (value, decimals = 2) => {
    if (value === null || value === undefined) return '0.00'
    return Number(value).toLocaleString('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    })
}

// Group records by date
const groupedOtherRecords = computed(() => {
    if (!props.records || !props.records.length) return []

    const groups = {}
    props.records.forEach(record => {
        const dateKey = new Date(record.created_at).toISOString().slice(0, 10)
        if (!groups[dateKey]) groups[dateKey] = []
        groups[dateKey].push(record)
    })

    return Object.entries(groups)
        .map(([date, records]) => {
            const sorted = [...records].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            return {
                date,
                records: sorted,
                latestPrice: sorted[0]?.price || 0,
                latestSgdPrice: sorted[0]?.sgd_price || 0
            }
        })
        .sort((a, b) => b.date.localeCompare(a.date))
})

// Pagination computed properties
const totalGroups = computed(() => groupedOtherRecords.value.length)
const totalPages = computed(() => Math.ceil(totalGroups.value / itemsPerPage))

const paginatedGroups = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return groupedOtherRecords.value.slice(start, start + itemsPerPage)
})

const visiblePages = computed(() => {
    const maxVisible = 5
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
    let end = Math.min(totalPages.value, start + maxVisible - 1)
    if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1)
    const pages = []
    for (let i = start; i <= end; i++) pages.push(i)
    return pages
})

const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

// Get previous day's price for trend (supports both price and sgd_price)
const getPreviousDayPrice = (currentDate, field) => {
    // Use the full grouped list for trend calculation (not paginated)
    const currentIndex = groupedOtherRecords.value.findIndex(g => g.date === currentDate)
    if (currentIndex === -1) return null
    const previousGroup = groupedOtherRecords.value[currentIndex + 1]
    return previousGroup ? (previousGroup[field === 'price' ? 'latestPrice' : 'latestSgdPrice'] || null) : null
}
</script>