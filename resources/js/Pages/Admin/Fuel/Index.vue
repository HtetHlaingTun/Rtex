<template>
    <AdminLayout title="Fuel Price Management">
        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header - unchanged -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">⛽ Fuel Price Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Manage fuel prices and calibration settings
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex items-center gap-1">
                            <span
                                :class="['w-2 h-2 rounded-full', apiHealth.eia?.status ? 'bg-green-500' : 'bg-red-500']"></span>
                            <span class="text-xs text-gray-500 uppercase">EIA</span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span
                                :class="['w-2 h-2 rounded-full', apiHealth.commodity?.status ? 'bg-green-500' : 'bg-red-500']"></span>
                            <span class="text-xs text-gray-500 uppercase">Commodity</span>
                        </span>
                    </div>
                    <button @click="updateNow" :disabled="updating"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 transition">
                        <span v-if="updating" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                    fill="none" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            Updating...
                        </span>
                        <span v-else>🔄 Update Now</span>
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <div v-if="$page.props.flash?.success"
                class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-300">{{ $page.props.flash.success }}</p>
            </div>

            <!-- Stats Cards - unchanged -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">USD/MMK Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(usdRate) }} <span
                            class="text-sm font-normal text-gray-500">MMK</span></p>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Calibration Factor</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ currentFactor.toFixed(4) }}<span
                            class="text-sm font-normal text-gray-500">×</span></p>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Yangon 92 Price</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatNumber(prices.yangon?.octane_92
                        || 0) }} <span class="text-sm font-normal text-gray-500">MMK</span></p>
                </div>
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Weekly Trend</p>
                    <p class="text-2xl font-bold" :class="trendColor">
                        {{ trendIcon }} {{ stats.change }}
                        <span class="text-sm font-normal text-gray-500">MMK</span>
                    </p>
                </div>
            </div>

            <!-- Current Prices Table - unchanged -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">📍 Current Fuel Prices by Region</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th
                                    class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Region</th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    92 Octane</th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    95 Octane</th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Diesel</th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Premium Diesel</th>
                                <th
                                    class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    24h Change</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="region in regions" :key="region"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-5 py-3 font-medium text-gray-900 dark:text-white capitalize">{{ region }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{
                                    formatNumber(prices[region]?.octane_92) }}</td>
                                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{
                                    formatNumber(prices[region]?.octane_95) }}</td>
                                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{
                                    formatNumber(prices[region]?.diesel) }}</td>
                                <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{
                                    formatNumber(prices[region]?.premium_diesel) }}</td>
                                <td class="px-5 py-3 text-right">
                                    <span v-if="prices[region]?.change_92 !== 0"
                                        :class="prices[region]?.change_92 > 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ prices[region]?.change_92 > 0 ? '▲' : '▼' }} {{
                                            Math.abs(prices[region]?.change_92).toFixed(2) }}%
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500">
                    Last updated: {{ prices.yangon?.updated_at ? formatDate(prices.yangon.updated_at) : 'Never' }}
                </div>
            </div>

            <!-- Calibration Panel & Simulator -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Calibration Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">🎯 Calibration Settings</h2>
                    </div>
                    <div class="p-5">
                        <!-- Direct Factor Update -->
                        <form @submit.prevent="submitFactorForm" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Calibration Factor
                            </label>
                            <div class="flex gap-2">
                                <input v-model.number="factorInput" type="number" step="0.0001" min="0.5" max="5.0"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    required>
                                <span
                                    class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg">×</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Formula: Base Import × Factor = Calibrated Price
                            </p>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes
                                    (Optional)</label>
                                <input v-model="factorNotes" type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Reason for update...">
                            </div>
                            <button type="submit" :disabled="factorProcessing"
                                class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                                <span v-if="factorProcessing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" fill="none" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Updating...
                                </span>
                                <span v-else>Update Factor</span>
                            </button>
                        </form>

                        <hr class="my-6 border-gray-200 dark:border-gray-700">

                        <!-- Market Price Calibration -->
                        <form @submit.prevent="submitMarketForm">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Or Calibrate from Market Price
                            </label>
                            <div class="flex gap-2">
                                <input v-model.number="marketPriceInput" type="number" min="1000" max="10000"
                                    placeholder="e.g., 4735"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    required>
                                <span
                                    class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg">MMK</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Enter actual Yangon 92 market price
                            </p>
                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes
                                    (Optional)</label>
                                <input v-model="marketNotes" type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    placeholder="Market reference...">
                            </div>
                            <button type="submit" :disabled="marketProcessing"
                                class="mt-4 px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700 disabled:opacity-50">
                                <span v-if="marketProcessing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" fill="none" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    Calibrating...
                                </span>
                                <span v-else>Calibrate from Market Price</span>
                            </button>
                        </form>

                        <!-- Current Calibration Info -->
                        <div class="mt-6 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                <strong>Last calibration:</strong> {{ calibration.updated_at ?
                                    formatDate(calibration.updated_at) : 'Never' }}<br>
                                <strong>Reference price:</strong> {{ formatNumber(calibration.reference_price) }}
                                MMK<br>
                                <strong>Notes:</strong> {{ calibration.notes || '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Price Simulator -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">📊 Price Simulator</h2>
                    </div>
                    <div class="p-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Test Different Factor
                        </label>
                        <input v-model.number="simulatorFactor" type="range" min="1.0" max="2.5" step="0.01"
                            class="w-full" @input="updatePreview">
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>1.0×</span>
                            <span class="font-medium">{{ simulatorFactor.toFixed(2) }}×</span>
                            <span>2.5×</span>
                        </div>

                        <div class="mt-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="py-2 text-left text-gray-500 dark:text-gray-400">Region</th>
                                        <th class="py-2 text-right text-gray-500 dark:text-gray-400">92 Price</th>
                                        <th class="py-2 text-right text-gray-500 dark:text-gray-400">95 Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(price, region) in previewPrices" :key="region"
                                        class="border-b border-gray-100 dark:border-gray-700">
                                        <td class="py-2 capitalize text-gray-700 dark:text-gray-300">{{ region }}</td>
                                        <td class="py-2 text-right text-gray-900 dark:text-white">{{
                                            formatNumber(price?.octane_92) }}</td>
                                        <td class="py-2 text-right text-gray-900 dark:text-white">{{
                                            formatNumber(price?.octane_95) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="previewInputs" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                <strong>Inputs:</strong><br>
                                Global Price: ${{ previewInputs.global_price }}/gal<br>
                                USD Rate: {{ formatNumber(previewInputs.usd_rate) }} MMK<br>
                                Base Import: {{ formatNumber(previewInputs.base_import) }} MMK/L<br>
                                Calibrated: {{ formatNumber(previewInputs.calibrated_base) }} MMK/L
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    calibration: Object,
    prices: Object,
    usdRate: Number,
    stats: Object,
    history: Array,
    regions: Array,
})

const updating = ref(false)
const apiHealth = ref({ eia: { status: false }, commodity: { status: false } })

// Form state
const factorInput = ref(Number(props.calibration?.factor) || 1.4)
const factorNotes = ref('')
const factorProcessing = ref(false)

const marketPriceInput = ref('')
const marketNotes = ref('')
const marketProcessing = ref(false)

// Simulator state - initialize with current factor
const simulatorFactor = ref(Number(props.calibration?.factor) || 1.4)
const previewPrices = ref({})
const previewInputs = ref(null)

// Watch for calibration prop changes
watch(() => props.calibration?.factor, (newFactor) => {
    if (newFactor) {
        factorInput.value = Number(newFactor)
        simulatorFactor.value = Number(newFactor)
        updatePreview()
    }
})

// Current factor from props
const currentFactor = computed(() => Number(props.calibration?.factor) || 1.4)

const trendIcon = computed(() => {
    if (props.stats?.trend === 'up') return '▲'
    if (props.stats?.trend === 'down') return '▼'
    return '—'
})

const trendColor = computed(() => {
    if (props.stats?.trend === 'up') return 'text-green-600'
    if (props.stats?.trend === 'down') return 'text-red-600'
    return 'text-gray-600'
})

const formatNumber = (num) => {
    if (!num && num !== 0) return '—'
    return new Intl.NumberFormat().format(num)
}

const formatDate = (date) => {
    if (!date) return 'Never'
    return new Date(date).toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const submitFactorForm = async () => {
    factorProcessing.value = true

    try {
        const response = await fetch('/admin/fuel/calibration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                action: 'factor',
                calibration_factor: factorInput.value,
                notes: factorNotes.value,
            })
        })

        const data = await response.json()

        if (response.ok && data.success) {
            // Show success message then reload
            alert(data.message)
            window.location.reload()
        } else {
            alert(data.message || 'Update failed')
            factorProcessing.value = false
        }
    } catch (error) {
        console.error('Error:', error)
        alert('Network error. Please try again.')
        factorProcessing.value = false
    }
}

const submitMarketForm = async () => {
    marketProcessing.value = true

    try {
        // Step 1: Calibrate
        const calResponse = await fetch('/admin/fuel/calibration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                action: 'market_price',
                market_price: marketPriceInput.value,
                notes: marketNotes.value,
            })
        })

        const calData = await calResponse.json()

        if (!calResponse.ok || !calData.success) {
            alert(calData.message || 'Calibration failed')
            marketProcessing.value = false
            return
        }

        // Step 2: Auto-update prices
        const updateResponse = await fetch('/admin/fuel/update-now', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
        })

        const updateData = await updateResponse.json()

        if (updateResponse.ok && updateData.success) {
            alert(calData.message + '\n✅ Prices updated automatically!')
        } else {
            alert(calData.message + '\n⚠️ Click "Update Now" to apply prices.')
        }

        window.location.reload()

    } catch (error) {
        console.error('Error:', error)
        alert('Network error. Please try again.')
        marketProcessing.value = false
    }
}
const updateNow = async () => {
    updating.value = true

    try {
        const response = await fetch('/admin/fuel/update-now', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        const data = await response.json()

        if (response.ok && data.success) {
            alert(data.message)
            window.location.reload()
        } else {
            alert('Update failed: ' + (data.message || 'Unknown error'))
            updating.value = false
        }
    } catch (error) {
        alert('Error: ' + error.message)
        updating.value = false
    }
}


const updatePreview = async () => {
    const factor = Number(simulatorFactor.value) || 1.4

    try {
        const response = await fetch(`/admin/fuel/preview?factor=${factor}`)
        const data = await response.json()
        previewPrices.value = data.prices || {}
        previewInputs.value = data.inputs || null
    } catch (error) {
        console.error('Preview error:', error)
    }
}

const checkApiHealth = async () => {
    try {
        const response = await fetch('/admin/fuel/api-health')
        const data = await response.json()
        apiHealth.value = data || { eia: { status: false }, commodity: { status: false } }
    } catch (error) {
        console.error('API health check error:', error)
    }
}

onMounted(() => {
    checkApiHealth()
    updatePreview()
    setInterval(checkApiHealth, 60000)
})
</script>