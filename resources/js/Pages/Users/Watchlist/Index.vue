<template>
    <UserLayout title="My Watchlist">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900">My Watchlist</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Track your favorite currencies and get real-time updates
                    </p>
                </div>
                <button @click="showAddModal = true"
                    class="px-3 sm:px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Currency
                </button>
            </div>
        </template>

        <!-- Stats Summary - Responsive Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Watching</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ watchlist.length }}</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">currencies</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Last Updated</p>
                <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white mt-1">{{ lastUpdated }}</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">live data</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Alerts Active</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ activeAlertsCount }}
                </p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">price alerts</p>
            </div>
        </div>

        <!-- Add Currency Button (Top of Table) -->
        <div class="flex justify-end mb-3 sm:mb-4">
            <button @click="showAddModal = true"
                class="px-3 sm:px-4 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs sm:text-sm font-bold transition flex items-center gap-2">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                + Add Currency
            </button>
        </div>

        <!-- Watchlist Table - Desktop -->
        <div v-if="watchlist.length > 0"
            class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px]">
                    <thead class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700">
                        <tr>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-bold text-slate-500">
                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll"
                                    class="rounded w-3.5 h-3.5 sm:w-4 sm:h-4">
                            </th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-bold text-slate-500">
                                Currency</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500">
                                Buy
                                Rate</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500">
                                Sell Rate</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500">
                                Mid
                                Rate</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-center text-[10px] sm:text-xs font-bold text-slate-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="item in watchlist" :key="item.id"
                            class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-3 sm:px-4 py-2 sm:py-3">
                                <input type="checkbox" v-model="selectedItems" :value="item.id"
                                    class="rounded w-3.5 h-3.5 sm:w-4 sm:h-4">
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{
                                        item.currency?.code }}</p>
                                    <p class="text-[8px] sm:text-[10px] text-slate-400 hidden sm:block">{{
                                        item.currency?.name
                                        }}</p>
                                </div>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-right">
                                <p class="font-mono font-bold text-[11px] sm:text-sm">{{
                                    formatNumber(currentRates[item.currency_id]?.buy_rate) }}</p>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-right">
                                <p class="font-mono font-bold text-[11px] sm:text-sm">{{
                                    formatNumber(currentRates[item.currency_id]?.sell_rate) }}</p>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-right">
                                <p class="font-mono font-bold text-[11px] sm:text-sm">{{
                                    formatNumber(currentRates[item.currency_id]?.mid_rate) }}</p>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-center">
                                <div class="flex items-center justify-center gap-1 sm:gap-2">
                                    <button @click="setAlert(item)"
                                        class="text-blue-500 hover:text-blue-600 transition p-1" title="Set Alert">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </button>
                                    <button @click="removeFromWatchlist(item.id)"
                                        class="text-red-400 hover:text-red-600 transition p-1" title="Remove">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedItems.length > 0"
                class="p-3 sm:p-4 bg-slate-50 dark:bg-zinc-800 border-t border-slate-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <span class="text-xs sm:text-sm text-slate-600">{{ selectedItems.length }} item(s) selected</span>
                    <button @click="bulkRemove"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-bold transition">
                        Remove Selected
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else
            class="text-center py-8 sm:py-12 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800">
            <div
                class="w-12 h-12 sm:w-16 sm:h-16 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
            <p class="text-slate-500 font-medium text-sm sm:text-base">Your watchlist is empty</p>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-1">Add currencies to track their rates in real-time</p>
            <button @click="showAddModal = true"
                class="inline-block mt-3 sm:mt-4 px-3 sm:px-4 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs sm:text-sm font-bold transition">
                + Add Your First Currency
            </button>
        </div>

        <!-- Add Currency Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-3 sm:p-4"
            @click.self="showAddModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl max-w-md w-full mx-3 sm:mx-0">
                <div
                    class="p-4 sm:p-5 border-b border-slate-200 dark:border-zinc-800 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">Add to Watchlist</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5">
                    <select v-model="selectedCurrencyId"
                        class="w-full px-3 sm:px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 text-sm">
                        <option value="">Select a currency</option>
                        <option v-for="currency in availableCurrencies" :key="currency.id" :value="currency.id">
                            {{ currency.code }} - {{ currency.name }}
                        </option>
                    </select>

                    <div v-if="selectedCurrencyId && getPreviewRate(selectedCurrencyId)"
                        class="mt-3 p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-[10px] sm:text-xs text-slate-500">Current mid rate:</p>
                        <p class="text-base sm:text-lg font-mono font-bold text-amber-600">{{
                            formatNumber(getPreviewRate(selectedCurrencyId)) }} MMK</p>
                    </div>

                    <div class="flex gap-3 mt-4 sm:mt-5">
                        <button @click="addToWatchlist" :disabled="!selectedCurrencyId"
                            class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold transition disabled:opacity-50 text-sm">
                            Add to Watchlist
                        </button>
                        <button @click="showAddModal = false"
                            class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Set Alert Modal - Responsive -->
        <div v-if="showAlertModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-3 sm:p-4"
            @click.self="closeModal">
            <div class="bg-white dark:bg-zinc-900 rounded-xl max-w-md w-full mx-3 sm:mx-0">
                <div
                    class="p-4 sm:p-5 border-b border-slate-200 dark:border-zinc-800 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        {{ editingAlertId ? 'Edit Price Alert' : 'Set Price Alert' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 sm:p-5">
                    <p class="text-sm text-slate-600 mb-4">
                        Set alert for <span class="font-bold">{{ selectedCurrency?.code }} - {{ selectedCurrency?.name
                            }}</span>
                    </p>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Condition</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" v-model="alertCondition" value="above" class="rounded">
                                <span class="text-sm">↑ Above</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" v-model="alertCondition" value="below" class="rounded">
                                <span class="text-sm">↓ Below</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Threshold Value (MMK)</label>
                        <input type="number" v-model="alertThreshold" step="0.01"
                            class="w-full px-3 sm:px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 text-sm"
                            :placeholder="editingAlertId ? 'Update threshold rate' : 'Enter threshold rate'">
                    </div>

                    <div v-if="selectedCurrency && currentRateForCurrency"
                        class="mb-4 p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-xs text-slate-500">Current rate:</p>
                        <p class="text-base sm:text-lg font-mono font-bold" :class="getRateStatusClass()">
                            {{ formatNumber(currentRateForCurrency) }} MMK
                        </p>
                        <p class="text-[9px] sm:text-[10px] text-slate-400 mt-1">
                            Alert will trigger when rate goes {{ alertCondition }} {{ formatNumber(alertThreshold) }}
                            MMK
                        </p>
                    </div>

                    <div v-if="activeAlertsCount > 0"
                        class="mb-4 flex justify-between items-center p-2 bg-amber-50 dark:bg-amber-950/30 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Active Alerts:</span>
                            <span class="bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full text-xs font-bold">
                                {{ activeAlertsCount }}
                            </span>
                        </div>
                        <Link :href="route('user.alerts.index')" class="text-xs text-amber-600 hover:text-amber-700">
                        View All →
                        </Link>
                    </div>

                    <div class="flex gap-3 mt-4 sm:mt-5">
                        <button @click="createAlert" :disabled="!alertThreshold || !selectedCurrency"
                            class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold transition disabled:opacity-50 text-sm">
                            {{ editingAlertId ? 'Update Alert' : 'Create Alert' }}
                        </button>
                        <button @click="closeModal"
                            class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold transition text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { registerEscapeHandler } from '@/Composables/useGlobalEscape'
import { useRealtimeAlerts } from '@/Composables/useRealtimeAlerts'

const props = defineProps({
    watchlist: { type: Array, default: () => [] },
    currentRates: { type: Object, default: () => ({}) },
    availableCurrencies: { type: Array, default: () => [] },
    alerts: { type: Array, default: () => [] },
})

const activeAlertsCount = computed(() => props.alerts?.length || 0)

const showAddModal = ref(false)
const showAlertModal = ref(false)
const selectedCurrencyId = ref('')
const selectedCurrency = ref(null)
const selectedItems = ref([])
const selectAll = ref(false)
const alertCondition = ref('above')
const alertThreshold = ref('')
const editingAlertId = ref(null)

const page = usePage()
const userId = computed(() => page.props.auth?.user?.id)

const lastUpdated = computed(() => {
    const rates = Object.values(props.currentRates)
    if (rates.length === 0) return 'Never'
    const latest = Math.max(...rates.map(r => new Date(r.updated_at).getTime()))
    return new Date(latest).toLocaleString()
})

const formatNumber = (value) => {
    if (!value) return '—'
    return new Intl.NumberFormat('en-US').format(value)
}

const getPreviewRate = (currencyId) => {
    return props.currentRates[currencyId]?.mid_rate
}

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedItems.value = props.watchlist.map(item => item.id)
    } else {
        selectedItems.value = []
    }
}

const addToWatchlist = () => {
    if (!selectedCurrencyId.value) return
    router.post(route('user.watchlist.store'), {
        currency_id: selectedCurrencyId.value
    }, {
        onSuccess: () => {
            showAddModal.value = false
            selectedCurrencyId.value = ''
        }
    })
}

const removeFromWatchlist = (id) => {
    if (confirm('Remove this currency from your watchlist?')) {
        router.delete(route('user.watchlist.destroy', id))
    }
}

const bulkRemove = () => {
    if (confirm(`Remove ${selectedItems.value.length} currency(s) from watchlist?`)) {
        router.post(route('user.watchlist.bulk-destroy'), {
            ids: selectedItems.value
        }, {
            onSuccess: () => {
                selectedItems.value = []
                selectAll.value = false
            }
        })
    }
}

const setAlert = (item) => {
    const existingAlert = props.alerts?.find(alert =>
        alert.currency_id === item.currency_id && alert.is_active === 1
    )

    if (existingAlert) {
        editingAlertId.value = existingAlert.id
        selectedCurrency.value = item.currency
        alertCondition.value = existingAlert.type
        alertThreshold.value = existingAlert.target_rate
    } else {
        editingAlertId.value = null
        selectedCurrency.value = item.currency
        alertCondition.value = 'above'
        alertThreshold.value = ''
    }
    showAlertModal.value = true
}

const currentRateForCurrency = computed(() => {
    if (!selectedCurrency.value) return null
    return props.currentRates[selectedCurrency.value.id]?.mid_rate
})

const getRateStatusClass = () => {
    if (!currentRateForCurrency.value || !alertThreshold.value) return 'text-slate-600'
    if (alertCondition.value === 'above' && currentRateForCurrency.value >= alertThreshold.value) {
        return 'text-emerald-600'
    }
    if (alertCondition.value === 'below' && currentRateForCurrency.value <= alertThreshold.value) {
        return 'text-rose-600'
    }
    return 'text-slate-600'
}

const createAlert = () => {
    if (!alertThreshold.value) {
        alert('Please enter a target rate')
        return
    }
    if (!selectedCurrency.value) {
        alert('No currency selected')
        return
    }

    const newTargetRate = parseFloat(alertThreshold.value)
    const currencyCode = selectedCurrency.value.code
    const isEditing = editingAlertId.value

    const formData = {
        currency_id: selectedCurrency.value.id,
        type: alertCondition.value,
        target_rate: newTargetRate,
    }

    if (isEditing) {
        router.put(route('user.alerts.update', editingAlertId.value), formData, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeModal()
                alert(`Alert updated for ${currencyCode}`)
            }
        })
    } else {
        router.post(route('user.alerts.store'), formData, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                closeModal()
                alert(`Alert created for ${currencyCode}`)
            }
        })
    }
}

useRealtimeAlerts(userId.value, (alertData) => {
    console.log('New alert received:', alertData)
})

let unregister

const closeModal = () => {
    showAlertModal.value = false
    editingAlertId.value = null
    alertCondition.value = 'above'
    alertThreshold.value = ''
    setTimeout(() => {
        selectedCurrency.value = null
    }, 100)
}

onMounted(() => {
    unregister = registerEscapeHandler(closeModal)
})

onUnmounted(() => {
    if (unregister) unregister()
})
</script>

<style scoped>
/* Mobile optimizations */
@media (max-width: 640px) {
    .table-responsive {
        margin: 0 -0.75rem;
    }
}
</style>