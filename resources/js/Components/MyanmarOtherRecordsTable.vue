<template>
    <div>
        <!-- Table Header - Responsive -->
        <div
            class="grid grid-cols-3 px-4 sm:px-6 py-3 bg-slate-50 dark:bg-zinc-800/50 border-b border-slate-200 dark:border-zinc-800">
            <span
                class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-400">Date
                & Time</span>
            <span
                class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-400 text-right">Price
                (MMK)</span>
            <span
                class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-400 text-right">Change</span>
        </div>

        <!-- Table Body - Responsive -->
        <div v-if="paginatedRecords.length > 0" class="divide-y divide-slate-100 dark:divide-zinc-800">
            <div v-for="(record, index) in paginatedRecords" :key="record.id"
                class="group grid grid-cols-3 items-center px-4 sm:px-6 py-3 sm:py-4 hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition-all duration-200">

                <div class="flex flex-col">
                    <span class="text-[11px] sm:text-[13px] font-bold text-slate-800 dark:text-white">
                        {{ formatDate(record.effective_date || record.price_date || record.created_at) }}
                    </span>
                    <span class="text-[7px] sm:text-[8px] text-slate-400">
                        {{ formatTime(record.effective_date || record.created_at) }}
                    </span>
                </div>

                <div class="text-right">
                    <div class="flex flex-col items-end">
                        <span class="text-[11px] sm:text-[13px] font-mono font-black" :class="priceColor">
                            {{ $formatMoney(record.price, 0, true) }}
                        </span>
                        <div class="mt-0.5">
                            <TrendIcon :current="record.price" :previous="getGlobalPrevious(index)"
                                :show-percentage="true" class="scale-70 sm:scale-75" />
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <span v-if="getChangePercent(index)"
                        :class="getChangePercent(index).dir === 'up' ? 'text-emerald-600' : 'text-rose-600'"
                        class="text-[9px] sm:text-[10px] font-bold inline-flex items-center gap-1">
                        <span>{{ getChangePercent(index).dir === 'up' ? '▲' : '▼' }}</span>
                        {{ Math.abs(getChangePercent(index).value).toFixed(2) }}%
                    </span>
                    <span v-else class="text-slate-400 text-[9px] sm:text-[10px]">—</span>
                </div>
            </div>
        </div>

        <!-- Pagination - Responsive -->
        <div v-if="totalRecords > itemsPerPage"
            class="flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3 sm:py-4 bg-slate-50/50 dark:bg-zinc-800/20 border-t border-slate-200 dark:border-zinc-800">
            <span class="text-[9px] sm:text-[10px] font-bold text-slate-400">
                {{ totalRecords }} records · Page {{ currentPage }} of {{ totalPages }}
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
    records: { type: Array, required: true },
    systemType: { type: String, required: true },
})

const currentPage = ref(1)
const itemsPerPage = 10

const isNew = computed(() => props.systemType === 'new')
const priceColor = computed(() => isNew.value ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-500')

const allRecords = computed(() => props.records || [])
const totalRecords = computed(() => allRecords.value.length)
const totalPages = computed(() => Math.ceil(totalRecords.value / itemsPerPage))

const paginatedRecords = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return allRecords.value.slice(start, start + itemsPerPage)
})

const globalIndex = (paginatedIndex) => (currentPage.value - 1) * itemsPerPage + paginatedIndex

const getGlobalPrevious = (paginatedIndex) => {
    const gi = globalIndex(paginatedIndex)
    return allRecords.value[gi + 1]?.price ?? null
}

const getChangePercent = (paginatedIndex) => {
    const gi = globalIndex(paginatedIndex)
    const next = allRecords.value[gi + 1]
    if (!next || !next.price) return null
    const current = allRecords.value[gi].price
    const pct = ((current - next.price) / next.price) * 100
    if (Math.abs(pct) < 0.01) return null
    return { value: pct, dir: pct > 0 ? 'up' : 'down' }
}

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
    if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
const formatTime = (d) => d ? new Date(d).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : ''
</script>