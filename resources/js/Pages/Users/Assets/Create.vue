<template>
    <UserLayout title="Add New Asset">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h1 class="text-2xl font-black text-slate-900 mb-6">Add New Asset</h1>

                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Asset Name</label>
                            <input type="text" v-model="form.name"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                :class="{ 'border-red-500': form.errors.name }" :placeholder="getNamePlaceholder()">
                            <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Asset Type</label>
                            <select v-model="form.type" @change="onTypeChange"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="gold">🥇 Gold</option>
                                <option value="currency">💵 Foreign Currency</option>

                            </select>
                        </div>

                        <!-- Help text for different asset types -->
                        <div v-if="form.type !== 'gold'" class="bg-blue-50 p-3 rounded-lg text-xs">
                            <div class="flex items-start gap-2">
                                <span class="text-lg">💡</span>
                                <div>
                                    <p class="font-bold text-blue-800">How this asset will be tracked:</p>
                                    <p class="text-blue-700 mt-1">
                                        <span v-if="form.type === 'currency'">
                                            • You hold foreign currency (USD/SGD)<br>
                                            • P&L comes from exchange rate changes against MMK<br>
                                            • Example: Buy $100 USD at 3,500 MMK, sell when rate is 4,500 MMK = +100,000
                                            MMK profit
                                        </span>
                                        <span v-else>
                                            • Purchase price in {{ form.purchase_currency }} will be recorded<br>
                                            • Current value calculated using live exchange rates<br>
                                            • P&L reflects currency value changes + potential appreciation
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Gold-specific fields -->
                        <div v-if="form.type === 'gold'" class="space-y-4 bg-amber-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Product Type</label>
                                <select v-model="form.product_type" @change="updateProductType"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                    <option value="1oz">🥇 1 Troy Ounce (31.1035g)</option>
                                    <option value="50g">🥇 50 Gram Gold Bar</option>
                                    <option value="100g">🥇 100 Gram Gold Bar</option>
                                    <option value="1kyatthar">🥇 1 Kyatthar (16.329g)</option>
                                    <option value="10kyatthar">🥇 10 Kyatthar (163.29g)</option>
                                    <option value="custom">⚖️ Custom Weight (grams)</option>
                                </select>
                            </div>

                            <div v-if="form.product_type === 'custom'">
                                <label class="block text-sm font-bold text-slate-700 mb-1">Weight (grams)</label>
                                <input type="number" step="0.01" v-model="form.custom_grams" @input="updateCustomWeight"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
                                    placeholder="e.g., 20, 50.5, 100">
                            </div>

                            <!-- Conversion Info -->
                            <div class="bg-white p-3 rounded-lg text-xs">
                                <p class="font-bold text-amber-700 mb-1">📐 Weight Conversion:</p>
                                <p class="text-slate-600">{{ conversionInfo }}</p>
                                <p v-if="calculatedTroyOz > 0" class="text-slate-600 mt-1">
                                    = {{ calculatedTroyOz.toFixed(4) }} Troy Ounces
                                </p>
                            </div>
                        </div>

                        <!-- Currency-specific example -->
                        <div v-if="form.type === 'currency'" class="bg-green-50 p-3 rounded-lg text-xs">
                            <div class="flex items-start gap-2">
                                <span class="text-lg">📝</span>
                                <div>
                                    <p class="font-bold text-green-800">Example:</p>
                                    <p class="text-green-700">
                                        Buy $800 USD at 3,500 MMK = 2,800,000 MMK<br>
                                        If USD/MMK rate rises to 4,500, your $800 is worth 3,600,000 MMK<br>
                                        Profit = +800,000 MMK (+28.57%)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Quantity (units)</label>
                                <input type="number" v-model="form.quantity" step="0.0001" min="0.0001"
                                    class="w-full px-4 py-2 border rounded-lg">
                                <p class="text-xs text-slate-400 mt-1">
                                    <span v-if="form.type === 'currency'">Amount of foreign currency (e.g., 800 for $800
                                        USD)</span>
                                    <span v-else>Number of units purchased</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Purchase Currency</label>
                                <select v-model="form.purchase_currency" class="w-full px-4 py-2 border rounded-lg">
                                    <option value="MMK">🇲🇲 MMK (Myanmar Kyat)</option>
                                    <option value="USD">🇺🇸 USD (US Dollar)</option>
                                    <option value="SGD">🇸🇬 SGD (Singapore Dollar)</option>
                                </select>
                                <p v-if="form.type === 'currency'" class="text-xs text-blue-600 mt-1">
                                    This is the currency you are buying/holding
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Purchase Price</label>
                                <input type="number" v-model="form.purchase_price" step="0.01"
                                    class="w-full px-4 py-2 border rounded-lg" :placeholder="getPricePlaceholder()">
                                <p class="text-xs text-slate-400 mt-1">
                                    <span v-if="form.type === 'currency'">Price per unit in {{ form.purchase_currency
                                        }}</span>
                                    <span v-else>Price per unit</span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Purchase Date</label>
                                <input type="date" v-model="form.purchase_date"
                                    class="w-full px-4 py-2 border rounded-lg">
                            </div>
                        </div>

                        <!-- Show total invested preview -->
                        <div v-if="form.purchase_price && form.quantity" class="bg-slate-50 p-3 rounded-lg">
                            <p class="text-xs font-bold text-slate-600">💰 Total Invested:</p>
                            <p class="text-sm font-mono font-bold">
                                {{ formatMoney(form.purchase_price * form.quantity) }} {{ form.purchase_currency }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                ≈ {{ formatMoney((form.purchase_price * form.quantity) * getCurrentRate()) }} MMK at
                                current rate
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Description (Optional)</label>
                            <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border rounded-lg"
                                :placeholder="getDescriptionPlaceholder()"></textarea>
                        </div>

                        <!-- Exchange Rate Info -->
                        <div class="bg-slate-50 rounded-lg p-4 text-sm">
                            <p class="font-bold mb-2">📊 Exchange Rates at Purchase:</p>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>1 USD = <span class="font-mono">{{ formatMoney(currentRates?.usd_mmk) }}</span> MMK
                                </div>
                                <div>1 SGD = <span class="font-mono">{{ formatMoney(currentRates?.sgd_mmk) }}</span> MMK
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">
                                * These rates will be recorded at time of purchase for future P&L calculation
                            </p>
                        </div>

                        <!-- Example calculation for currency -->
                        <div v-if="form.type === 'currency' && form.purchase_price && form.quantity"
                            class="bg-purple-50 rounded-lg p-3 text-xs">
                            <p class="font-bold text-purple-800 mb-1">📈 Example P&L Calculation:</p>
                            <div class="text-purple-700 space-y-1">
                                <p>Purchase: {{ formatMoney(form.purchase_price * form.quantity) }} {{
                                    form.purchase_currency }}</p>
                                <p>At current rate: ≈ {{ formatMoney((form.purchase_price * form.quantity) *
                                    getCurrentRate()) }} MMK</p>
                                <p v-if="getRateChange() !== 0"
                                    :class="getRateChange() > 0 ? 'text-green-600' : 'text-red-600'">
                                    Rate change: {{ getRateChange() > 0 ? '+' : '' }}{{ getRateChange().toFixed(2) }}%
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl disabled:opacity-50">
                                <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Adding...
                                </span>
                                <span v-else>Add Asset</span>
                            </button>
                            <Link :href="route('user.assets.index')"
                                class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-center">
                                Cancel
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    currentRates: {
        type: Object,
        default: () => ({})
    }
})

const form = useForm({
    name: '',
    type: 'gold',
    quantity: 1,
    purchase_price: '',
    purchase_currency: 'MMK',
    purchase_date: new Date().toISOString().split('T')[0],
    description: '',
    // Gold-specific
    product_type: '1oz',
    custom_grams: null
})

const calculatedTroyOz = ref(0)

const formatMoney = (value) => {
    if (!value && value !== 0) return '0'
    return new Intl.NumberFormat('en-US').format(Math.round(value))
}

const getNamePlaceholder = () => {
    const placeholders = {
        gold: 'e.g., 1 oz Gold Bar, 50g Gold, 10 Kyatthar',
        currency: 'e.g., $800 USD Savings, S$5000 SGD Account',
        property: 'e.g., Condo Unit 5B, Land Plot 123',
        car: 'e.g., Toyota Camry 2020, Honda CR-V',
        jewelry: 'e.g., Diamond Ring, Gold Necklace',
        crypto: 'e.g., 0.5 Bitcoin, 10 Ethereum',
        other: 'e.g., Art Collection, Vintage Watch'
    }
    return placeholders[form.type] || 'Enter asset name'
}

const getPricePlaceholder = () => {
    if (form.type === 'currency') {
        return `Price per ${form.purchase_currency} (e.g., 1 USD = ?)`
    }
    return `Amount in ${form.purchase_currency}`
}

const getDescriptionPlaceholder = () => {
    if (form.type === 'currency') {
        return 'e.g., USD savings account, Foreign currency held in wallet'
    }
    if (form.type === 'property') {
        return 'e.g., Location: Yangon, Size: 2000 sqft, Year built: 2020'
    }
    return 'Additional details about this asset'
}

const getCurrentRate = () => {
    if (form.purchase_currency === 'USD') {
        return props.currentRates?.usd_mmk || 4385
    } else if (form.purchase_currency === 'SGD') {
        return props.currentRates?.sgd_mmk || 3408
    }
    return 1
}

const getRateChange = () => {
    // This is just a preview - actual calculation will be done in backend
    const currentRate = getCurrentRate()
    const baseRate = form.purchase_currency === 'USD' ? 4385 : 3408
    return ((currentRate - baseRate) / baseRate) * 100
}

const onTypeChange = () => {
    if (form.type !== 'gold') {
        form.product_type = null
        form.custom_grams = null
    }
    // Auto-set purchase currency based on type
    if (form.type === 'currency' && form.purchase_currency === 'MMK') {
        form.purchase_currency = 'USD'
    }
}

const updateProductType = () => {
    updateWeightCalculation()
}

const updateCustomWeight = () => {
    updateWeightCalculation()
}

const updateWeightCalculation = () => {
    const quantity = parseFloat(form.quantity) || 1
    const productType = form.product_type
    const customGrams = parseFloat(form.custom_grams) || 0

    if (productType === '1oz') {
        calculatedTroyOz.value = quantity
    } else if (productType === '50g') {
        calculatedTroyOz.value = quantity * (50 / 31.1035)
    } else if (productType === '100g') {
        calculatedTroyOz.value = quantity * (100 / 31.1035)
    } else if (productType === '1kyatthar') {
        calculatedTroyOz.value = quantity * 0.525
    } else if (productType === '10kyatthar') {
        calculatedTroyOz.value = quantity * 5.25
    } else if (productType === 'custom' && customGrams > 0) {
        calculatedTroyOz.value = quantity * (customGrams / 31.1035)
    } else {
        calculatedTroyOz.value = 0
    }
}

const conversionInfo = computed(() => {
    const productType = form.product_type
    const customGrams = form.custom_grams

    switch (productType) {
        case '1oz':
            return '1 Troy Ounce = 31.1035 grams'
        case '50g':
            return '50 grams = 1.6075 Troy Ounces'
        case '100g':
            return '100 grams = 3.215 Troy Ounces'
        case '1kyatthar':
            return '1 Kyatthar = 16.329 grams = 0.525 Troy Ounces'
        case '10kyatthar':
            return '10 Kyatthar = 163.29 grams = 5.25 Troy Ounces'
        case 'custom':
            if (customGrams) {
                const troyOz = customGrams / 31.1035
                return `${customGrams} grams = ${troyOz.toFixed(4)} Troy Ounces`
            }
            return 'Enter weight in grams'
        default:
            return ''
    }
})

const submit = () => {
    // Auto-generate name if not provided
    if (!form.name || form.name === '') {
        if (form.type === 'gold') {
            const productLabels = {
                '1oz': '1 oz Gold Bar',
                '50g': '50g Gold Bar',
                '100g': '100g Gold Bar',
                '1kyatthar': '1 Kyatthar Gold',
                '10kyatthar': '10 Kyatthar Gold',
                'custom': `${form.custom_grams}g Gold Bar`
            }
            form.name = productLabels[form.product_type] || 'Gold Asset'
        } else if (form.type === 'currency') {
            // Fix: Create a proper name, not just the currency code
            const amount = parseFloat(form.quantity) || 0
            const currencySymbol = form.purchase_currency === 'USD' ? '$' : 'S$'
            form.name = `${currencySymbol}${amount.toLocaleString()} ${form.purchase_currency} (Foreign Currency Holding)`
        } else if (form.type === 'property') {
            form.name = 'Property Investment'
        } else if (form.type === 'car') {
            form.name = 'Vehicle Asset'
        } else {
            form.name = `${form.type.charAt(0).toUpperCase() + form.type.slice(1)} Asset`
        }
    }

    // Validate required fields
    if (!form.purchase_price || form.purchase_price <= 0) {
        alert('Please enter a valid purchase price')
        return
    }

    if (!form.quantity || form.quantity <= 0) {
        alert('Please enter a valid quantity')
        return
    }

    // Create a copy of form data to submit
    const submitData = {
        name: form.name,
        type: form.type,
        quantity: form.quantity,
        purchase_price: form.purchase_price,
        purchase_currency: form.purchase_currency,
        purchase_date: form.purchase_date,
        description: form.description || null,
    }

    // Only send gold-specific fields if type is gold
    if (form.type === 'gold') {
        submitData.product_type = form.product_type
        if (form.product_type === 'custom') {
            submitData.custom_grams = form.custom_grams
        }
    }



    form.post(route('user.assets.store'), submitData)
}
</script>