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
                        Conversion Factors & Spreads
                    </h1>
                    <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-1 ">
                        Configure how CBM rates are converted to buy/sell rates
                    </p>
                </div>
                <div class="flex gap-2">
                    <div class="px-3 py-2 bg-slate-100 rounded-xl text-sm hidden md:block">
                        Formula: Working Rate = CBM Rate × Factor
                    </div>
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
                        <p class="text-xs text-slate-500 mt-1 hidden md:block">
                            Configure factors and spreads for each currency. Changes take effect immediately.
                        </p>
                    </div>

                    <Fab :href="route('currencies.settings')" icon="currency" />

                    <button @click="saveAll" :disabled="saving"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                        {{ saving ? 'Saving...' : 'Save All Changes' }}
                    </button>
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

                    <div class="p-4 border-b border-slate-100 bg-slate-50">
                        <div class="flex flex-col xs:flex-row items-start xs:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-indigo-700 font-bold text-sm">{{ currency.code }}</span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 leading-tight">{{ currency.name }}</h3>
                                    <p class="text-xs text-slate-500 uppercase">{{ currency.code }}</p>
                                </div>
                            </div>
                            <div class="text-left xs:text-right border-t xs:border-t-0 pt-2 xs:pt-0 w-full xs:w-auto">
                                <div class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">CBM Rate
                                </div>
                                <div class="font-mono font-bold text-slate-700">{{
                                    $formatMoney(currency.current_cbm_rate) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 space-y-5 flex-1">

                        <div class="bg-blue-50 p-3 sm:p-4 rounded-xl border border-blue-200">
                            <label class="block text-xs font-bold text-blue-800 mb-2 uppercase tracking-tight gap-2">
                                Enter Bank Rate
                            </label>

                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="number" v-model="bankRateInput[currency.id]" step="0.01"
                                    class="w-full px-3 py-3 border border-blue-300 rounded-lg font-mono text-base bg-white focus:ring-2 focus:ring-blue-500/20 shadow-sm"
                                    :placeholder="`${currency.code} Rate`">

                                <div class="flex gap-2 w-full sm:w-auto">
                                    <button @click="calculateFactorFromBankRate(currency)"
                                        class="flex-1 sm:flex-none px-4 py-3 bg-blue-600 text-white rounded-lg text-xs font-black uppercase tracking-wider hover:bg-blue-700 active:scale-[0.98] transition-all">
                                        Calculate
                                    </button>
                                    <button @click="resetToCBM(currency)"
                                        class="px-4 py-3 bg-white text-gray-500 border border-blue-200 rounded-lg text-xs font-bold hover:bg-gray-100">
                                        Reset
                                    </button>
                                </div>
                            </div>

                            <div v-if="calculatedMarketRates[currency.id]"
                                class="mt-3 p-3 bg-white rounded-lg border border-blue-100 shadow-sm">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div class="border-r border-slate-100">
                                        <div class="text-[10px] text-slate-400 uppercase font-bold">Market Buy</div>
                                        <div class="font-bold text-emerald-600 text-sm">
                                            {{ $formatMoney(calculatedMarketRates[currency.id].buy) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-400 uppercase font-bold">Market Sell</div>
                                        <div class="font-bold text-rose-600 text-sm">
                                            {{ $formatMoney(calculatedMarketRates[currency.id].sell) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-400 text-center mt-2 font-mono">
                                    {{ calculatedMarketRates[currency.id].factor.toFixed(6) }} factor applied
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-500 uppercase">Current Factor</label>
                                <button @click="setFactorTo1(currency)"
                                    class="text-[10px] text-indigo-600 font-bold underline">
                                    Set to 1.0
                                </button>
                            </div>
                            <input type="number" v-model="editing[currency.id].factor" step="0.000001"
                                class="w-full px-3 py-2.5 border border-slate-200 rounded-lg font-mono text-sm bg-slate-50 text-slate-500"
                                readonly>
                        </div>

                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
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
                                <input type="number" v-model="editing[currency.id].buy_spread"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/10">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black text-slate-400 uppercase">
                                    Sell {{ editing[currency.id].spread_type === 'percentage' ? '%' : 'MMK' }}
                                </label>
                                <input type="number" v-model="editing[currency.id].sell_spread"
                                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/10">
                            </div>
                        </div>

                        <div class="bg-slate-900 p-4 rounded-xl shadow-inner mt-auto">
                            <div class="grid grid-cols-2 gap-4 text-center">
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

                        <div class="flex flex-col gap-3 pt-2">
                            <label class="flex items-center gap-3 cursor-pointer group">
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
                                {{ saving ? 'Saving...' : 'Confirm & Save' }}
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
                    <p class="text-xs text-slate-500 mt-1">
                        Please add currencies in settings first.
                    </p>
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
import { ref, reactive, onMounted } from 'vue'
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
    { label: 'Factors Config' },
]

const editing = reactive({})
const bankRateInput = reactive({})
const calculatedMarketRates = reactive({})
const saving = ref(false)
const loading = ref(true)

// Fixed markup percentages for each currency
const MARKUP_PERCENTAGES = {
    USD: 17.3,   // 17.3% markup
    SGD: 14.5,   // 14.5% markup
    THB: 5.0,    // 5% markup
    EUR: 15.5,   // 15.5% markup
    GBP: 15.5,   // 15.5% markup
    CNY: 13.5,   // 13.5% markup
    MYR: 12.5,   // 12.5% markup
    JPY: 11.5,   // 11.5% markup
    VND: 8.0,    // 8% markup
    KRW: 10.0,   // 10% markup
}

onMounted(() => {
    if (props.currencies && Array.isArray(props.currencies) && props.currencies.length > 0) {
        props.currencies.forEach(currency => {
            editing[currency.id] = {
                factor: currency.cbm_conversion_factor || props.default_factor,
                spread_type: currency.spread_type || 'percentage',
                buy_spread: currency.buy_spread_percentage || 0.5,
                sell_spread: currency.sell_spread_percentage || 0.5,
                fixed_buy_margin: currency.fixed_buy_margin || 0,
                fixed_sell_margin: currency.fixed_sell_margin || 0,
                auto_fetch: currency.use_cbm_auto_fetch !== false,
            }
            bankRateInput[currency.id] = ''
            calculatedMarketRates[currency.id] = null
        })
    }
    loading.value = false
})

// Fixed formula: Bank Rate × (1 + Markup%) = Market Rate
const calculateFactorFromBankRate = (currency) => {
    const bankRate = bankRateInput[currency.id]
    if (!bankRate) {
        alert(`Please enter the bank rate for ${currency.code} (e.g., 3662 for USD)`)
        return
    }

    const markupPercent = MARKUP_PERCENTAGES[currency.code] || 15.0
    const markupMultiplier = 1 + (markupPercent / 100)

    // Calculate market rate from bank rate
    const marketRate = bankRate * markupMultiplier

    // Calculate factor to achieve this market rate from CBM
    const factor = marketRate / currency.current_cbm_rate

    // Store calculated rates
    calculatedMarketRates[currency.id] = {
        buy: marketRate * 0.995,  // 0.5% below market
        sell: marketRate * 1.005, // 0.5% above market
        working: marketRate,
        factor: factor,
        bankRate: bankRate,
        markup: markupPercent
    }

    // Update the factor in editing
    editing[currency.id].factor = factor

    alert(`✅ Calculated for ${currency.code}:\n\n` +
        `Bank Rate: ${bankRate}\n` +
        `Markup: ${markupPercent}%\n` +
        `Market Rate: ${marketRate.toFixed(2)}\n` +
        `CBM Rate: ${currency.current_cbm_rate}\n` +
        `Factor: ${factor.toFixed(6)}\n\n` +
        `Result: ${currency.current_cbm_rate} × ${factor.toFixed(6)} = ${marketRate.toFixed(2)}`)
}

// Reset to pure CBM (factor = 1)
const resetToCBM = (currency) => {
    if (confirm(`Reset ${currency.code} to use pure CBM rate?\n\nCurrent CBM rate: ${currency.current_cbm_rate}\nMarket rate will become: ${currency.current_cbm_rate}`)) {
        editing[currency.id].factor = 1
        calculatedMarketRates[currency.id] = {
            buy: currency.current_cbm_rate * 0.995,
            sell: currency.current_cbm_rate * 1.005,
            working: currency.current_cbm_rate,
            factor: 1,
            bankRate: null,
            markup: 0
        }
        alert(`✅ ${currency.code} reset to CBM base rate. Factor = 1`)
    }
}

// Set factor to 1 without confirmation
const setFactorTo1 = (currency) => {
    editing[currency.id].factor = 1
    calculatedMarketRates[currency.id] = {
        buy: currency.current_cbm_rate * 0.995,
        sell: currency.current_cbm_rate * 1.005,
        working: currency.current_cbm_rate,
        factor: 1,
        bankRate: null,
        markup: 0
    }
}

const previewBuyRate = (currency) => {
    if (!currency.current_cbm_rate) return 0
    const workingRate = currency.current_cbm_rate * editing[currency.id].factor
    if (editing[currency.id].spread_type === 'percentage') {
        return workingRate * (1 - (editing[currency.id].buy_spread / 100))
    } else {
        return workingRate - editing[currency.id].fixed_buy_margin
    }
}

const previewSellRate = (currency) => {
    if (!currency.current_cbm_rate) return 0
    const workingRate = currency.current_cbm_rate * editing[currency.id].factor
    if (editing[currency.id].spread_type === 'percentage') {
        return workingRate * (1 + (editing[currency.id].sell_spread / 100))
    } else {
        return workingRate + editing[currency.id].fixed_sell_margin
    }
}

const saveCurrency = async (currency) => {
    saving.value = true
    try {
        await axios.post(`/currencies/${currency.id}/factor`, {
            _method: 'PUT',
            cbm_conversion_factor: editing[currency.id].factor,
            spread_type: editing[currency.id].spread_type,
            buy_spread_percentage: editing[currency.id].buy_spread,
            sell_spread_percentage: editing[currency.id].sell_spread,
            fixed_buy_margin: editing[currency.id].fixed_buy_margin,
            fixed_sell_margin: editing[currency.id].fixed_sell_margin,
            use_cbm_auto_fetch: editing[currency.id].auto_fetch,
        })
        alert(`✅ Saved factor for ${currency.code}`)
    } catch (error) {
        console.error('Failed to save:', error)
        alert('Failed to save. Please try again.')
    } finally {
        saving.value = false
    }
}

const saveAll = async () => {
    if (!props.currencies || !Array.isArray(props.currencies) || props.currencies.length === 0) {
        alert('No currencies to save')
        return
    }

    saving.value = true
    try {
        for (const currency of props.currencies) {
            await axios.post(`/currencies/${currency.id}/factor`, {
                _method: 'PUT',
                cbm_conversion_factor: editing[currency.id].factor,
                spread_type: editing[currency.id].spread_type,
                buy_spread_percentage: editing[currency.id].buy_spread,
                sell_spread_percentage: editing[currency.id].sell_spread,
                fixed_buy_margin: editing[currency.id].fixed_buy_margin,
                fixed_sell_margin: editing[currency.id].fixed_sell_margin,
                use_cbm_auto_fetch: editing[currency.id].auto_fetch,
            })
        }
        alert('All factors saved successfully!')
        router.reload()
    } catch (error) {
        console.error('Failed to save all:', error)
        alert('Failed to save some factors. Please try again.')
    } finally {
        saving.value = false
    }
}
</script>