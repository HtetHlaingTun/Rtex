<template>
    <AdminLayout title="Live Exchange Rates">
        <template #breadcrumbs>
            <div class="px-2 sm:px-0">
                <Breadcrumbs :items="currencyBreadcrumbs" />
            </div>
        </template>

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4  ">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Live Exchange Rates
                    </h1>
                    <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-1">
                        Real-time rates calculated from CBM reference with applied factors
                    </p>
                </div>

                <div class="flex gap-2">
                    <!-- Status Badge -->
                    <div class="flex items-center gap-2 px-3 py-2 bg-slate-100 rounded-xl">
                        <div :class="cbm_available ? 'bg-green-500' : 'bg-red-500'"
                            class="w-2 h-2 rounded-full animate-pulse"></div>
                        <span class="text-xs font-medium">
                            {{ cbm_available ? 'CBM API Connected' : 'CBM API Offline' }}
                        </span>
                    </div>



                    <!-- Factor Settings (Admin only) -->
                    <Link v-if="$page.props.auth.user.role === 'admin'" :href="route('currencies.factors')"
                        class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-200 transition">
                        ⚙️ Configure Factors
                    </Link>
                </div>
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-xs font-medium text-slate-400">Active Currencies</div>
                <div class="text-2xl font-bold text-slate-900 mt-1">{{ rates.length }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-xs font-medium text-slate-400">Last Update</div>
                <div class="text-sm font-bold text-slate-900 mt-1">{{ $formatTime24(last_fetch_time) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-xs font-medium text-slate-400">Reference</div>
                <div class="text-sm font-bold text-slate-900 mt-1">CBM Official Rates</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <div class="text-xs font-medium text-slate-400">Spread Type</div>
                <div class="text-sm font-bold text-slate-900 mt-1">{{ spreadTypeSummary }}</div>
            </div>
        </div>

        <!-- Rates Table -->
        <div v-if="rates && rates.length > 0"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-left">Currency</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Buy Rate</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Sell Rate</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Spread</th>

                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right hidden md:table-cell">
                                Change
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right hidden md:table-cell">
                                CBM Reference
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right hidden md:table-cell">
                                Factor
                            </th>

                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="rate in rates" :key="rate.id"
                            @click="router.visit(route('currencies.history', rate.id))"
                            class="hover:bg-slate-50/50 transition-colors group cursor-pointer">

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="hidden sm:flex w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center">
                                        <span class="text-indigo-600 font-bold text-sm">{{ rate.currency.code }}</span>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 hidden sm:flex">{{ rate.currency.name }}
                                        </div>
                                        <div class="font-bold text-slate-900 sm:hidden">{{ rate.currency.code }}</div>
                                        <div class="text-[10px] text-slate-400 hidden sm:flex">{{ rate.currency.code }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-emerald-600 font-mono">
                                    {{ $formatMoney(rate.buy_rate) }}
                                </span>
                                <div class="text-[10px] text-slate-400">MMK</div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-rose-600 font-mono">
                                    {{ $formatMoney(rate.sell_rate) }}
                                </span>
                                <div class="text-[10px] text-slate-400">MMK</div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="px-2 py-1 rounded-md bg-slate-100 text-xs font-mono">
                                    {{ formatSpread(rate) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right hidden md:table-cell">
                                <div :class="getChangeColor(rate.market_trend)"
                                    class="flex items-center justify-end gap-1">
                                    <span v-if="rate.change_percentage" class="text-sm font-medium">
                                        {{ $formatPercentage(rate.change_percentage) }}
                                    </span>
                                    <span v-else class="text-sm text-slate-400">-</span>
                                    <span v-if="rate.market_trend === 'up'" class="text-green-600">▲</span>
                                    <span v-if="rate.market_trend === 'down'" class="text-red-600">▼</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 hidden md:table-cell">
                                <div class="text-sm font-mono text-slate-60 ">
                                    {{ $formatMoney(rate.cbm_rate) }}
                                </div>
                                <div class="text-[10px] text-slate-400">MMK per {{ rate.currency.code }}</div>
                            </td>

                            <td class="px-6 py-4 hidden md:table-cell">
                                <div class="text-sm font-mono">
                                    {{ $formatFactor(rate.cbm_conversion_factor) }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ rate.spread_type === 'percentage' ? `${rate.buy_spread_applied}% /
                                    ${rate.sell_spread_applied}%` : `±${$formatMoney(rate.buy_spread_applied)}` }}
                                </div>
                            </td>
                            <!-- 
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('currencies.history', rate.id)"
                                    class="text-slate-300 group-hover:text-indigo-600 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </td> -->
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="flex flex-col items-center">
                <div class="p-4 bg-slate-50 rounded-full mb-4">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-900">No rates available</h4>
                <p class="text-xs text-slate-500 mt-1">
                    {{ cbm_available ? 'CBM API is connected but no currencies configured with rates'
                        : 'CBM API is currently unavailable' }}
                </p>
                <Link v-if="$page.props.auth.user.role === 'admin'" :href="route('currencies.factors')"
                    class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                    Configure Factors
                </Link>
            </div>
        </div>

        <!-- Information Note -->
        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
            <div class="flex gap-2 text-xs text-blue-800">
                <span>ℹ️</span>
                <span>
                    Rates are calculated from Central Bank of Myanmar (CBM) reference rates using configured conversion
                    factors and spreads.
                    <Link v-if="$page.props.auth.user.role === 'admin'" :href="route('currencies.factors')"
                        class="font-bold underline">
                        Configure factors
                    </Link>
                </span>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, getCurrentInstance } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue'
import axios from 'axios'

const props = defineProps({
    rates: {
        type: Array,
        default: () => []
    },
    cbm_available: {
        type: Boolean,
        default: false
    },
    last_fetch_time: {
        type: String,
        default: null
    },
    userRole: {
        type: String,
        default: 'viewer'
    },
})

const refreshing = ref(false)
const instance = getCurrentInstance()
const global = instance.appContext.config.globalProperties

const currencyBreadcrumbs = [
    { label: 'Live Rates', href: route('currencies.index') },
    { label: 'Live' },
]

const spreadTypeSummary = computed(() => {
    if (!props.rates || !Array.isArray(props.rates) || props.rates.length === 0) return '-'
    const spreadType = props.rates[0]?.spread_type
    return spreadType === 'percentage' ? 'Percentage Spread' : 'Fixed Margin'
})

const formatSpread = (rate) => {
    if (!rate) return '-'
    const spread = rate.sell_rate - rate.buy_rate
    if (rate.spread_type === 'percentage') {
        const percentage = (spread / rate.working_rate * 100).toFixed(2)
        return `${percentage}%`
    }
    return global.$formatMoney(spread)
}

const getChangeColor = (trend) => {
    if (trend === 'up') return 'text-green-600'
    if (trend === 'down') return 'text-red-600'
    return 'text-slate-400'
}

const refreshRates = async () => {
    refreshing.value = true
    try {
        const response = await axios.get('/currencies/refresh')
        if (response.data.success) {
            router.reload({ preserveState: false })
        }
    } catch (error) {
        console.error('Failed to refresh rates:', error)
        alert('Failed to refresh rates. Please try again.')
    } finally {
        refreshing.value = false
    }
}
</script>