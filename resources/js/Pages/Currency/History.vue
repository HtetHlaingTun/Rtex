<template>
    <AdminLayout :title="`${currency.code} Performance History`">
        <template #breadcrumbs>
            <div class="px-2 sm:px-0">
                <Breadcrumbs :items="breadcrumbs" />
            </div>
        </template>

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-12 h-12 sm:w-14 sm:h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-white text-lg sm:text-xl font-black shadow-xl shrink-0">
                        {{ currency.code }}
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                            {{ currency.name }} History
                        </h1>
                        <p class="text-[11px] sm:text-sm text-slate-500 font-medium italic">
                            Historical market data and rate trends
                        </p>
                    </div>
                </div>


            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Current Rate (Sell)</span>
                <div class="text-2xl font-black text-slate-900 mt-2">
                    {{ currentRate }}
                    <span class="text-xs font-medium text-slate-400 ml-1">MMK</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">All-Time High (Sell)</span>
                <div class="text-2xl font-black text-emerald-600 mt-2">
                    {{ allTimeHigh }}
                    <span class="text-xs font-medium text-slate-400 ml-1">MMK</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">All-Time Low (Sell)</span>
                <div class="text-2xl font-black text-rose-600 mt-2">
                    {{ allTimeLow }}
                    <span class="text-xs font-medium text-slate-400 ml-1">MMK</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Average Rate (30d)</span>
                <div class="text-2xl font-black text-slate-900 mt-2">
                    {{ averageRate }}
                    <span class="text-xs font-medium text-slate-400 ml-1">MMK</span>
                </div>
            </div>
        </div>

        <!-- Historical Rates Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100">
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                Effective Date
                            </th>
                            <th
                                class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">
                                Buy Rate
                            </th>
                            <th
                                class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">
                                Sell Rate
                            </th>
                            <th
                                class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">
                                Spread
                            </th>
                            <th
                                class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">
                                Change
                            </th>
                            <th class="px-6 py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                Source
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="(record, index) in history.data" :key="record.id"
                            class="hover:bg-slate-50/50 transition group">


                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-bold text-slate-900">
                                        {{ $formatDate(record.rate_date || record.created_at) }}
                                    </div>
                                    <span v-if="$isToday(record.rate_date || record.created_at)"
                                        class="px-1.5 py-0.5 bg-indigo-100 text-indigo-600 text-[9px] font-black rounded uppercase animate-pulse">
                                        Live
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $formatTimeOnly(record.created_at) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 flex flex-col">
                                    <span class="text-sm font-mono font-bold text-emerald-600">
                                        {{ $formatMoney(record.buy_rate) }}
                                    </span>
                                    <TrendIcon :current="record.buy_rate" :previous="history.data[index + 1]?.buy_rate"
                                        type="buy" />
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 flex flex-col">
                                    <span class="text-sm font-mono font-bold text-rose-600">
                                        {{ $formatMoney(record.sell_rate) }}
                                    </span>
                                    <TrendIcon :current="record.sell_rate"
                                        :previous="history.data[index + 1]?.sell_rate" type="sell" />
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 rounded-md bg-slate-100 text-xs font-mono">
                                    {{ formatSpread(record) }}
                                </span>
                            </td>

                            <div :class="$getTrendColor($getTrendDirection(record, history.data[index + 1]))"
                                class="flex items-center justify-end gap-1 mt-5">
                                <span class="text-sm font-bold">
                                    {{ $calculateDailyChange(record, history.data[index + 1]) }}
                                </span>
                                <span class="flex items-center justify-end gap-1"
                                    v-if="$getTrendDirection(record, history.data[index + 1]) === 'up'">▲</span>
                                <span v-if="$getTrendDirection(record, history.data[index + 1]) === 'down'">▼</span>
                            </div>

                            <td class="px-6 py-4">
                                <span class="text-xs text-slate-500">
                                    {{ record.source_name || 'Manual Entry' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden divide-y divide-slate-100">
                <div v-for="(record, index) in history.data" :key="'mob-' + record.id"
                    class="p-4 space-y-3 active:bg-slate-50 transition">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm font-bold text-slate-900">
                                {{ $formatDate(record.rate_date || record.created_at) }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ $formatTimeOnly(record.created_at) }}
                            </div>
                        </div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                            {{ record.source_name || 'Manual' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100/50">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-[9px] font-bold text-emerald-600 uppercase">Buy</div>
                                <TrendIcon :current="record.buy_rate" :previous="history.data[index + 1]?.buy_rate"
                                    type="buy" size="small" />
                            </div>
                            <div class="text-sm font-mono font-bold text-emerald-700">
                                {{ $formatMoney(record.buy_rate) }}
                            </div>
                            <div class="flex justify-between text-[10px] mt-1">
                                <span class="text-slate-500">Spread:</span>
                                <span class="font-mono">{{ getSpread(record) }}</span>
                            </div>
                        </div>
                        <div class="bg-rose-50/50 p-3 rounded-xl border border-rose-100/50">
                            <div class="flex items-center justify-between mb-1">
                                <div class="text-[9px] font-bold text-rose-600 uppercase">Sell</div>
                                <TrendIcon :current="record.sell_rate" :previous="history.data[index + 1]?.sell_rate"
                                    type="sell" size="small" />
                            </div>
                            <div class="text-sm font-mono font-bold text-rose-700">
                                {{ $formatMoney(record.sell_rate) }}
                            </div>
                            <div v-if="record.change_percentage" :class="getChangeColor(record.market_trend)"
                                class="text-[9px] mt-1">
                                {{ $formatPercentage(record.change_percentage) }}
                                {{ record.market_trend === 'up' ? '↑' : (record.market_trend === 'down' ? '↓' : '') }}
                            </div>
                        </div>
                    </div>

                    <!-- CBM Reference (if available) -->
                    <!-- <div v-if="record.cbm_rate" class="bg-slate-50 p-2 rounded-lg">
                        <div class="flex justify-between text-[10px]">
                            <span class="text-slate-500">CBM Reference:</span>
                            <span class="font-mono">{{ $formatMoney(record.cbm_rate) }}</span>
                        </div>
                        <div v-if="record.factors" class="flex justify-between text-[10px] mt-1">
                            <span class="text-slate-500">Factor:</span>
                            <span class="font-mono">{{ $formatFactor(record.factors?.cbm_conversion_factor) }}</span>
                        </div>
                    </div> -->
                    <div v-if="record.factors" class="bg-slate-50 p-2 rounded-lg">
                        <div class="flex justify-between text-[10px]">
                            <span class="text-slate-500">CBM Reference:</span>
                            <span class="font-mono">{{ getCbmReference(record) }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] mt-1">
                            <span class="text-slate-500">Factor:</span>
                            <span class="font-mono">{{ getConversionFactor(record) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8 border-t border-slate-100">
                <Paginations :links="history.links" />
            </div>

            <!-- Empty State -->
            <div v-if="history.data.length === 0" class="px-6 py-20 text-center">
                <div class="flex flex-col items-center">
                    <div class="p-4 bg-slate-50 rounded-full mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">No historical data available</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        No rate history found for {{ currency.code }}
                    </p>
                    <Link :href="route('currencies.create', { currency_id: currency.id })"
                        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                        Add First Rate
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, getCurrentInstance } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue'
import TrendIcon from '@/Components/TrendIcon.vue'

const props = defineProps({
    currency: {
        type: Object,
        required: true
    },
    history: {
        type: Object,
        required: true
    }
})

// Get the global instance to access global helpers
const instance = getCurrentInstance()
const global = instance.appContext.config.globalProperties

const breadcrumbs = [
    { label: 'Live Rates', href: route('currencies.index') },
    { label: `${props.currency.code} History` }
]

// Computed statistics - using global helpers correctly
const currentRate = computed(() => {
    if (!props.history.data || props.history.data.length === 0) return '0.00'
    return global.$formatMoney(props.history.data[0]?.sell_rate || 0)
})

const allTimeHigh = computed(() => {
    if (!props.history.data || props.history.data.length === 0) return '0.00'
    const maxSell = Math.max(...props.history.data.map(r => parseFloat(r.sell_rate) || 0))
    return global.$formatMoney(maxSell)
})

const allTimeLow = computed(() => {
    if (!props.history.data || props.history.data.length === 0) return '0.00'
    const minSell = Math.min(...props.history.data.map(r => parseFloat(r.sell_rate) || 0))
    return global.$formatMoney(minSell)
})

const averageRate = computed(() => {
    if (!props.history.data || props.history.data.length === 0) return '0.00'
    // Get last 30 days or all if less than 30
    const last30 = props.history.data.slice(0, 30)
    const sum = last30.reduce((acc, r) => acc + (parseFloat(r.sell_rate) || 0), 0)
    const avg = sum / last30.length
    return global.$formatMoney(avg)
})

// Helper functions
const formatSpread = (record) => {
    const spread = record.sell_rate - record.buy_rate
    if (record.spread_type_applied === 'percentage') {
        const percentage = (spread / record.buy_rate * 100).toFixed(2)
        return `${percentage}%`
    }
    return global.$formatMoney(spread)
}

const getChangeColor = (trend) => {
    if (trend === 'up') return 'text-green-600'
    if (trend === 'down') return 'text-red-600'
    return 'text-slate-400'
}

const getConversionFactor = (record) => {
    if (!record.factors) return '—'

    try {
        // Parse factors if it's a string
        let factors = record.factors
        if (typeof factors === 'string') {
            factors = JSON.parse(factors)
        }

        // Look for the factor in different possible fields
        const factor = factors.cbm_conversion_factor ||
            factors.conversion_factor ||
            factors.cbm_factor ||
            factors.factor

        if (factor !== undefined && factor !== null) {
            // Format to 6 decimal places
            return parseFloat(factor).toFixed(6)
        }

        return '—'
    } catch (e) {
        console.error('Error parsing factors:', e)
        return '—'
    }
}
const formatNumber = (value) => {
    if (!value) return '0'
    return new Intl.NumberFormat('en-US').format(Math.round(value))
}

const getCbmReference = (record) => {
    if (!record.factors) return '—'

    try {
        let factors = record.factors
        if (typeof factors === 'string') {
            factors = JSON.parse(factors)
        }

        // Check for working_rate
        if (factors.working_rate) {
            return formatNumber(factors.working_rate)
        }

        // Check for cbm_rate on the record
        if (record.cbm_rate) {
            return formatNumber(record.cbm_rate)
        }

        return '—'
    } catch (e) {
        return '—'
    }
}

const getSpread = (record) => {
    if (!record.buy_rate || !record.sell_rate) return '—'

    const buy = parseFloat(record.buy_rate)
    const sell = parseFloat(record.sell_rate)
    const spread = ((sell - buy) / buy * 100).toFixed(2)
    return `${spread}%`
}

</script>

<style scoped>
/* Add any component-specific styles here */
</style>