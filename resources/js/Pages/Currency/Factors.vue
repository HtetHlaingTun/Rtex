<!-- resources/js/Pages/Currency/Factors.vue -->
<template>
    <AdminLayout title="Currency Factors Configuration">
        <template #breadcrumbs>
            <div class="px-2 sm:px-0">
                <Breadcrumbs :items="currencyBreadcrumbs" />
            </div>
        </template>

        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Currency Rate Configuration
                    </h1>
                    <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-1">
                        Configure source mode, bank markup, and spreads for each currency
                    </p>
                </div>
                <div class="flex gap-2">
                    <button @click="saveAll" :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                        {{ saving ? 'Saving...' : 'Save All Changes' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- CBM Status -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <div :class="cbm_available ? 'bg-green-500' : 'bg-red-500'"
                                class="w-2 h-2 rounded-full animate-pulse"></div>
                            <span class="font-medium">CBM API Status:</span>
                            <span :class="cbm_available ? 'text-green-600' : 'text-red-600'">
                                {{ cbm_available ? 'Connected' : 'Disconnected' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Current CBM rates are used as reference for 'cbm' mode only
                        </p>
                    </div>
                    <Fab :href="route('currencies.settings')" icon="currency" />
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12 bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-slate-500">Loading currencies...</p>
            </div>

            <!-- Currencies Grid -->
            <div v-else-if="currencies && currencies.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <div v-for="currency in currencies" :key="currency.id"
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">

                    <!-- Header -->
                    <div class="p-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex flex-col xs:flex-row items-start xs:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-indigo-700 font-bold text-sm">{{ currency.code }}</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ currency.name }}</h3>
                                    <p class="text-xs text-slate-500">{{ currency.code }}</p>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Last sync: {{ currency.banks_last_synced_at ? new
                                            Date(currency.banks_last_synced_at).toLocaleString() : 'Never' }}
                                        <span v-if="currency.avg_bank_rate" class="ml-2">
                                            (Avg: {{ $formatMoney(currency.avg_bank_rate) }})
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left xs:text-right">
                                <div class="text-[10px] uppercase text-slate-400 font-bold">Current CBM Rate</div>
                                <div class="font-mono font-bold text-slate-700">
                                    {{ $formatMoney(currency.current_cbm_rate) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Settings Badges -->
                    <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                        <div class="flex flex-wrap gap-2 text-[10px]">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full">
                                Mode: {{ editing[currency.id]?.source_mode || currency.source_mode || 'bank_avg' }}
                            </span>
                            <span v-if="editing[currency.id]?.source_mode === 'bank_avg'"
                                class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full">
                                Markup: {{ Number(editing[currency.id]?.bank_markup ?? currency.bank_markup_percentage
                                    ?? 0).toFixed(1) }}%
                            </span>
                            <span v-if="editing[currency.id]?.source_mode === 'cross_usd'"
                                class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full">
                                Cross Rate (USD Bridge)
                            </span>
                            <span v-if="editing[currency.id]?.source_mode === 'cbm'"
                                class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full">
                                Factor: {{ Number(editing[currency.id]?.factor || currency.cbm_conversion_factor ||
                                    1).toFixed(4) }}
                            </span>
                            <span class="px-2 py-1 bg-slate-200 text-slate-600 rounded-full">
                                Spread: {{ Number(editing[currency.id]?.buy_spread ?? currency.buy_spread_percentage ??
                                    0.5).toFixed(1) }}% /
                                {{ Number(editing[currency.id]?.sell_spread ?? currency.sell_spread_percentage ??
                                    0.5).toFixed(1) }}%
                            </span>
                        </div>
                    </div>

                    <div class="p-4 space-y-5 flex-1">
                        <!-- Source Mode Selection -->
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Source Mode</label>
                            <div class="grid grid-cols-4 gap-2 p-1 bg-slate-100 rounded-xl">
                                <button v-for="mode in ['bank_avg', 'cross_usd', 'cbm', 'manual']" :key="mode"
                                    @click="editing[currency.id].source_mode = mode"
                                    class="py-2 rounded-lg text-[11px] font-bold transition-all"
                                    :class="editing[currency.id].source_mode === mode ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'">
                                    {{ mode === 'bank_avg' ? 'Bank Average' : mode === 'cross_usd' ? 'Cross Rate' : mode
                                        ===
                                        'cbm' ? 'CBM Rate' : 'Manual' }}
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">
                                <span v-if="editing[currency.id].source_mode === 'bank_avg'">Uses average of 4 banks
                                    (KBZ, Yoma, CB, AYA)</span>
                                <span v-else-if="editing[currency.id].source_mode === 'cross_usd'">Calculates using
                                    USD/MMK ÷ USD/Currency (from Yahoo Finance)</span>
                                <span v-else-if="editing[currency.id].source_mode === 'cbm'">Uses CBM rate × conversion
                                    factor</span>
                                <span v-else>Manually entered rate</span>
                            </p>
                        </div>

                        <!-- Bank Markup (for bank_avg mode) -->
                        <div v-if="editing[currency.id].source_mode === 'bank_avg'" class="pt-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">Bank Markup (%)</label>
                                <button @click="setMarkupToZero(currency)"
                                    class="text-[10px] text-indigo-600 font-bold underline">
                                    Set to 0%
                                </button>
                            </div>
                            <input type="number" v-model="editing[currency.id].bank_markup" step="0.1"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg font-mono text-sm bg-white focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Markup percentage (e.g., 0 for no markup)">
                            <p class="text-[10px] text-slate-400 mt-1">
                                Formula: Base Rate = Bank Average Rate × (1 + Markup%)
                            </p>
                        </div>

                        <!-- Cross Rate Markup (for cross_usd mode) -->
                        <div v-if="editing[currency.id].source_mode === 'cross_usd'" class="pt-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">Bank Markup (%)</label>
                                <button @click="setMarkupToZero(currency)"
                                    class="text-[10px] text-indigo-600 font-bold underline">
                                    Set to 0%
                                </button>
                            </div>
                            <input type="number" v-model="editing[currency.id].bank_markup" step="0.1"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg font-mono text-sm bg-white focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Markup percentage (e.g., 2 for 2%)">
                            <p class="text-[10px] text-slate-400 mt-1">
                                Formula: Base Rate = (USD/MMK ÷ USD/Currency) × (1 + Markup%)
                            </p>
                        </div>

                        <!-- Manual Rate (for manual mode) -->
                        <div v-if="editing[currency.id].source_mode === 'manual'" class="pt-2">
                            <label class="text-xs font-bold text-slate-500 uppercase mb-1 block">Manual Base
                                Rate</label>
                            <input type="number" v-model="editing[currency.id].manual_base_rate" step="0.01"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg font-mono text-sm bg-white focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Enter manual rate">
                        </div>

                        <!-- CBM Factor (for cbm mode) -->
                        <div v-if="editing[currency.id].source_mode === 'cbm'" class="pt-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">CBM Conversion Factor</label>
                                <button @click="setFactorTo1(currency)"
                                    class="text-[10px] text-indigo-600 font-bold underline">
                                    Set to 1.0
                                </button>
                            </div>
                            <input type="number" v-model="editing[currency.id].factor" step="0.000001"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg font-mono text-sm bg-white focus:ring-2 focus:ring-indigo-500/20">
                            <p class="text-[10px] text-slate-400 mt-1">
                                Formula: Base Rate = CBM Rate × Factor
                            </p>
                        </div>

                        <!-- Spread Configuration -->
                        <div class="pt-2">
                            <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Spread
                                Configuration</label>
                            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl mb-3">
                                <button v-for="type in ['percentage', 'fixed']" :key="type"
                                    @click="editing[currency.id].spread_type = type"
                                    class="py-2 rounded-lg text-[11px] font-bold transition-all"
                                    :class="editing[currency.id].spread_type === type ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'">
                                    {{ type === 'percentage' ? 'Percentage' : 'Fixed Margin' }}
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">
                                        Buy {{ editing[currency.id].spread_type === 'percentage' ? '%' : 'MMK' }}
                                    </label>
                                    <input type="number" v-model="editing[currency.id].buy_spread" step="0.01"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/10">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase">
                                        Sell {{ editing[currency.id].spread_type === 'percentage' ? '%' : 'MMK' }}
                                    </label>
                                    <input type="number" v-model="editing[currency.id].sell_spread" step="0.01"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/10">
                                </div>
                            </div>
                        </div>

                        <!-- Cross Rate Preview (for cross_usd mode) -->
                        <div v-if="editing[currency.id].source_mode === 'cross_usd' && crossRatePreviews[currency.code]"
                            class="bg-purple-50 border border-purple-200 rounded-xl p-4 mt-2">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[10px] font-black text-purple-600 uppercase">Cross Rate
                                    Calculation</span>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-purple-700">USD/MMK (Current):</span>
                                    <span class="font-mono font-bold text-purple-900">{{
                                        crossRatePreviews[currency.code].usd_mmk_rate }} MMK</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-purple-700">USD/{{ currency.code }}:</span>
                                    <span class="font-mono font-bold text-purple-900">{{
                                        crossRatePreviews[currency.code].usd_to_target }}</span>
                                </div>
                                <div class="flex justify-between border-t border-purple-200 pt-2 mt-1">
                                    <span class="text-purple-700 font-bold">Calculated Base Rate:</span>
                                    <span class="font-mono font-bold text-purple-900">{{
                                        crossRatePreviews[currency.code].calculated_rate }} MMK</span>
                                </div>
                                <div class="flex justify-between text-[10px] text-purple-500">
                                    <span>Formula:</span>
                                    <span class="font-mono">{{ crossRatePreviews[currency.code].formula }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="bg-slate-900 p-4 rounded-xl shadow-inner mt-auto">
                            <div class="text-center mb-2">
                                <div class="text-[10px] text-slate-500 font-bold uppercase">Base Rate</div>
                                <div class="font-mono font-bold text-white text-lg">
                                    {{ $formatMoney(previewBaseRate(currency)) }}
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-center border-t border-slate-800 pt-3">
                                <div class="border-r border-slate-800">
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Final Buy</div>
                                    <div class="text-base font-black font-mono text-emerald-400">
                                        {{ $formatMoney(previewBuyRate(currency)) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 font-bold uppercase mb-1">Final Sell</div>
                                    <div class="text-base font-black font-mono text-rose-400">
                                        {{ $formatMoney(previewSellRate(currency)) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Auto-fetch & Save -->
                        <div class="flex flex-col gap-3 pt-2">
                            <label v-if="editing[currency.id].source_mode !== 'cross_usd'"
                                class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" v-model="editing[currency.id].auto_fetch"
                                        class="sr-only peer">
                                    <div
                                        class="w-10 h-5 bg-slate-200 rounded-full peer-checked:bg-indigo-600 transition-all">
                                    </div>
                                    <div
                                        class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-all peer-checked:left-6">
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-slate-600">Auto-fetch from CBM</span>
                            </label>
                            <button @click="saveCurrency(currency)" :disabled="saving"
                                class="w-full py-4 bg-indigo-600 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-lg shadow-indigo-200 disabled:opacity-50 active:translate-y-px transition-all">
                                {{ saving ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12 bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="flex flex-col items-center">
                    <div class="p-4 bg-slate-50 rounded-full mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">No currencies found</h4>
                    <p class="text-xs text-slate-500 mt-1">Please add currencies in settings first.</p>
                    <Link :href="route('currencies.settings')"
                        class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                        Add Currencies
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import axios from 'axios'
import Fab from '@/Components/Layouts/Fab.vue'

const props = defineProps({
    currencies: {
        type: Array,
        default: () => []
    },
    cbm_available: {
        type: Boolean,
        default: false
    },
    default_factor: {
        type: Number,
        default: 1
    }
})

const currencyBreadcrumbs = [
    { label: 'Live Rates', href: route('currencies.index') },
    { label: 'Rate Configuration' },
]

const editing = reactive({})
const crossRatePreviews = reactive({})
const saving = ref(false)
const loading = ref(true)

// Fetch cross rate preview for a currency
const fetchCrossRatePreview = async (currency) => {
    if (!currency.code) return

    try {
        const response = await axios.get(`/api/cross-rate-preview/${currency.code}`)
        crossRatePreviews[currency.code] = response.data
    } catch (error) {
        console.error(`Failed to fetch cross rate for ${currency.code}:`, error)
    }
}

onMounted(() => {
    if (props.currencies && Array.isArray(props.currencies) && props.currencies.length > 0) {
        props.currencies.forEach(currency => {
            editing[currency.id] = {
                source_mode: currency.source_mode || 'bank_avg',
                factor: currency.cbm_conversion_factor || props.default_factor,
                bank_markup: currency.bank_markup_percentage ?? 2.0,
                manual_base_rate: currency.manual_base_rate || 0,
                spread_type: currency.spread_type || 'percentage',
                buy_spread: currency.buy_spread_percentage ?? 0.5,
                sell_spread: currency.sell_spread_percentage ?? 0.5,
                fixed_buy_margin: currency.fixed_buy_margin || 0,
                fixed_sell_margin: currency.fixed_sell_margin || 0,
                auto_fetch: currency.use_cbm_auto_fetch !== false,
            }

            // Fetch cross rate preview for ALL cross_usd currencies
            if (currency.source_mode === 'cross_usd') {
                fetchCrossRatePreview(currency)
            }
        })
    }
    loading.value = false
})

// Watch for source_mode changes to fetch cross rate preview
watch(() => props.currencies, (newCurrencies) => {
    if (newCurrencies) {
        newCurrencies.forEach(currency => {
            if (editing[currency.id]?.source_mode === 'cross_usd') {
                fetchCrossRatePreview(currency)
            }
        })
    }
}, { deep: true })

const previewBaseRate = (currency) => {
    const mode = editing[currency.id].source_mode

    if (mode === 'bank_avg') {
        const bankAvg = Number(currency.avg_bank_rate) || 0
        const markup = Number(editing[currency.id].bank_markup) || 0
        return bankAvg * (1 + markup / 100)
    } else if (mode === 'cross_usd') {
        // Calculate using USD rate from props
        const usdCurrency = props.currencies?.find(c => c.code === 'USD')
        if (!usdCurrency) return 0

        // Get latest USD/MMK rate from exchange_rates or avg_bank_rate
        const usdMmk = usdCurrency.avg_bank_rate || 0
        if (usdMmk === 0) return 0

        // Hardcoded USD/JPY for now (you can make dynamic)
        const usdToTargetRates = {
            'JPY': 158.94,
            'CNY': 6.84,
            'MYR': 3.98,
            'INR': 92.63,
            'KRW': 1450.00,
            'HKD': 7.82,
            'NZD': 1.63,
            'AUD': 1.52,
            'CAD': 1.38,
            'CHF': 0.91,
        }

        const usdToTarget = usdToTargetRates[currency.code] || 1
        const baseRate = usdMmk / usdToTarget
        const markup = Number(editing[currency.id].bank_markup) || 2.0
        return baseRate * (1 + markup / 100)
    } else if (mode === 'cbm') {
        const cbmRate = Number(currency.current_cbm_rate) || 0
        const factor = Number(editing[currency.id].factor) || 1
        return cbmRate * factor
    } else {
        return Number(editing[currency.id].manual_base_rate) || 0
    }
}

const previewBuyRate = (currency) => {
    const baseRate = previewBaseRate(currency)
    if (!baseRate) return 0

    const spread = Number(editing[currency.id].buy_spread) || 0
    if (editing[currency.id].spread_type === 'percentage') {
        return baseRate * (1 - spread / 100)
    } else {
        return baseRate - spread
    }
}

const previewSellRate = (currency) => {
    const baseRate = previewBaseRate(currency)
    if (!baseRate) return 0

    const spread = Number(editing[currency.id].sell_spread) || 0
    if (editing[currency.id].spread_type === 'percentage') {
        return baseRate * (1 + spread / 100)
    } else {
        return baseRate + spread
    }
}

const setMarkupToZero = (currency) => {
    editing[currency.id].bank_markup = 0
}

const setFactorTo1 = (currency) => {
    editing[currency.id].factor = 1
}

const saveCurrency = async (currency) => {
    saving.value = true
    try {
        await axios.post(`/currencies/${currency.id}/factor`, {
            _method: 'PUT',
            source_mode: editing[currency.id].source_mode,
            cbm_conversion_factor: editing[currency.id].factor,
            bank_markup_percentage: editing[currency.id].bank_markup,
            manual_base_rate: editing[currency.id].manual_base_rate,
            spread_type: editing[currency.id].spread_type,
            buy_spread_percentage: editing[currency.id].buy_spread,
            sell_spread_percentage: editing[currency.id].sell_spread,
            fixed_buy_margin: editing[currency.id].fixed_buy_margin,
            fixed_sell_margin: editing[currency.id].fixed_sell_margin,
            use_cbm_auto_fetch: editing[currency.id].auto_fetch,
        })
        alert(`✅ Saved settings for ${currency.code}`)
        // Refresh cross rate preview after save
        if (editing[currency.id].source_mode === 'cross_usd') {
            await fetchCrossRatePreview(currency)
        }
    } catch (error) {
        console.error('Failed to save:', error)
        alert('Failed to save. Please try again.')
    } finally {
        saving.value = false
    }
}

const saveAll = async () => {
    if (!props.currencies || props.currencies.length === 0) {
        alert('No currencies to save')
        return
    }

    saving.value = true
    try {
        for (const currency of props.currencies) {
            await axios.post(`/currencies/${currency.id}/factor`, {
                _method: 'PUT',
                source_mode: editing[currency.id].source_mode,
                cbm_conversion_factor: editing[currency.id].factor,
                bank_markup_percentage: editing[currency.id].bank_markup,
                manual_base_rate: editing[currency.id].manual_base_rate,
                spread_type: editing[currency.id].spread_type,
                buy_spread_percentage: editing[currency.id].buy_spread,
                sell_spread_percentage: editing[currency.id].sell_spread,
                fixed_buy_margin: editing[currency.id].fixed_buy_margin,
                fixed_sell_margin: editing[currency.id].fixed_sell_margin,
                use_cbm_auto_fetch: editing[currency.id].auto_fetch,
            })
        }
        alert('All settings saved successfully!')
        router.reload()
    } catch (error) {
        console.error('Failed to save all:', error)
        alert('Failed to save some settings. Please try again.')
    } finally {
        saving.value = false
    }
}
</script>