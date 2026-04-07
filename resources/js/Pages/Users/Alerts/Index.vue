<template>
    <UserLayout title="Price Alerts">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Price Alerts</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Get notified when currency rates reach your target levels
                    </p>
                </div>
                <button @click="showCreateModal = true"
                    class="px-3 sm:px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Alert
                </button>
            </div>
        </template>

        <!-- Stats Summary - Responsive Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Total</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ alerts.length }}</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">alerts</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Active</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-1">{{ activeAlertsCount }}</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">monitoring</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Triggered</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600 mt-1">{{ triggeredCount }}</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">fired</p>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 sm:p-4 border border-slate-200 dark:border-zinc-800">
                <p class="text-[10px] sm:text-xs text-slate-400 uppercase tracking-wide">Currencies</p>
                <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ uniqueCurrenciesCount
                    }}</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1">watched</p>
            </div>
        </div>

        <!-- Create Alert Button (Top of Table) -->
        <div class="flex justify-end mb-3 sm:mb-4">
            <button @click="showCreateModal = true"
                class="px-3 sm:px-4 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs sm:text-sm font-bold transition flex items-center gap-2">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Alert
            </button>
        </div>

        <!-- Alerts Table - Desktop -->
        <div v-if="alerts.length > 0"
            class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px]">
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
                                class="px-3 sm:px-4 py-2 sm:py-3 text-left text-[10px] sm:text-xs font-bold text-slate-500">
                                Condition</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500">
                                Target</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500">
                                Current</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-center text-[10px] sm:text-xs font-bold text-slate-500">
                                Status</th>
                            <th
                                class="px-3 sm:px-4 py-2 sm:py-3 text-center text-[10px] sm:text-xs font-bold text-slate-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="alert in alerts" :key="alert.id"
                            class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-3 sm:px-4 py-2 sm:py-3">
                                <input type="checkbox" v-model="selectedItems" :value="alert.id"
                                    class="rounded w-3.5 h-3.5 sm:w-4 sm:h-4">
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{
                                        alert.currency?.code || 'Unknown' }}</p>
                                    <p class="text-[8px] sm:text-[10px] text-slate-400 hidden sm:block">{{
                                        alert.currency?.name
                                        || 'Currency not found' }}</p>
                                </div>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3">
                                <span :class="alert.type === 'above' ? 'text-emerald-600' : 'text-rose-600'"
                                    class="font-bold text-xs sm:text-sm">
                                    {{ alert.type === 'above' ? '↑ Above' : '↓ Below' }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-right dark:text-white">
                                <p class="font-mono font-bold text-[11px] sm:text-sm">{{ formatNumber(alert.target_rate)
                                    }}</p>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-right ">
                                <p class="font-mono text-[11px] sm:text-sm" :class="getRateStatusClass(alert)">
                                    {{ formatNumber(currentRates[alert.currency_id]?.mid_rate) }}
                                </p>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-center">
                                <span @click="toggleAlert(alert.id)"
                                    :class="alert.is_active ? 'bg-emerald-100 text-emerald-700 cursor-pointer' : 'bg-slate-100 text-slate-500 cursor-pointer'"
                                    class="px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full text-[8px] sm:text-[10px] font-bold inline-flex items-center gap-1 hover:opacity-80 transition">
                                    <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full"
                                        :class="alert.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                    {{ alert.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-center">
                                <div class="flex items-center justify-center gap-1 sm:gap-2">
                                    <button @click="editAlert(alert)"
                                        class="text-blue-500 hover:text-blue-600 transition p-1" title="Edit">
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteAlert(alert.id)"
                                        class="text-red-400 hover:text-red-600 transition p-1" title="Delete">
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
                    <span class="text-xs sm:text-sm text-slate-600">{{ selectedItems.length }} alert(s) selected</span>
                    <button @click="bulkDelete"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-bold transition">
                        Delete Selected
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
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-slate-500 font-medium text-sm sm:text-base">No alerts set</p>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-1">Create alerts to get notified when rates hit your
                targets</p>
            <button @click="showCreateModal = true"
                class="inline-block mt-3 sm:mt-4 px-3 sm:px-4 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs sm:text-sm font-bold transition">
                + Create Your First Alert
            </button>
        </div>

        <!-- Create/Edit Alert Modal - Responsive -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-3 sm:p-4"
            @click.self="showCreateModal = false">
            <div class="bg-white dark:bg-zinc-900 rounded-xl max-w-md w-full mx-3 sm:mx-0">
                <div
                    class="p-4 sm:p-5 border-b border-slate-200 dark:border-zinc-800 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                        {{ editingAlert ? 'Edit Alert' : 'Create New Alert' }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4 sm:p-5">
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2 dark:text-white">Currency</label>
                        <select v-model="form.currency_id" :disabled="!!editingAlert"
                            class="w-full px-3 sm:px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 text-sm">
                            <option value="">Select a currency</option>
                            <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                                {{ currency.code }} - {{ currency.name }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2 dark:text-white">Condition</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 dark:text-white">
                                <input type="radio" v-model="form.type" value="above" class="rounded ">
                                <span class="text-sm">↑ Above</span>
                            </label>
                            <label class="flex items-center gap-2 dark:text-white">
                                <input type="radio" v-model="form.type" value="below" class="rounded">
                                <span class="text-sm">↓ Below</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2 dark:text-white">Target Rate (MMK)</label>
                        <input type="number" v-model="form.target_rate" step="0.01"
                            class="w-full px-3 sm:px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-500 text-sm"
                            placeholder="Enter target rate">
                    </div>

                    <!-- Current rate preview -->
                    <div v-if="form.currency_id && getCurrentRate(form.currency_id)"
                        class="mt-3 p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-xs text-slate-500">Current rate:</p>
                        <p class="text-base sm:text-lg font-mono font-bold" :class="getPreviewClass(form)">
                            {{ formatNumber(getCurrentRate(form.currency_id)) }} MMK
                        </p>
                        <p class="text-[9px] sm:text-[10px] text-slate-400 mt-1">
                            Alert will trigger when rate goes {{ form.type === 'above' ? 'above' : 'below' }} {{
                                formatNumber(form.target_rate) }} MMK
                        </p>
                    </div>

                    <div class="flex gap-3 mt-4 sm:mt-5">
                        <button @click="saveAlert" :disabled="!isFormValid"
                            class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold transition disabled:opacity-50 text-sm">
                            {{ editingAlert ? 'Update Alert' : 'Create Alert' }}
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

const page = usePage()
const props = defineProps({
    alerts: { type: Array, default: () => [] },
    currentRates: { type: Object, default: () => ({}) },
    currencies: { type: Array, default: () => [] },
})

const showCreateModal = ref(false)
const editingAlert = ref(null)
const selectedItems = ref([])
const selectAll = ref(false)
const userId = computed(() => page.props.auth?.user?.id)

const form = ref({
    currency_id: '',
    type: 'above',
    target_rate: '',
})

const refreshData = () => {
    router.reload({
        only: ['alerts', 'currentRates'],
        preserveScroll: true,
        preserveState: true,
    })
}

let refreshInterval = null

const activeAlertsCount = computed(() => {
    return props.alerts?.filter(a => a.is_active).length || 0
})

const triggeredCount = computed(() => {
    return props.alerts?.filter(a => a.triggered_at).length || 0
})

const uniqueCurrenciesCount = computed(() => {
    return new Set(props.alerts?.map(a => a.currency_id) || []).size
})

const isFormValid = computed(() => {
    return form.value.currency_id && form.value.target_rate && form.value.target_rate > 0
})

const formatNumber = (value) => {
    if (!value) return '—'
    return new Intl.NumberFormat('en-US').format(value)
}

const getCurrentRate = (currencyId) => {
    return props.currentRates?.[currencyId]?.mid_rate
}

const getRateStatusClass = (alert) => {
    const currentRate = props.currentRates?.[alert.currency_id]?.mid_rate
    if (!currentRate) return 'text-slate-400'

    if (alert.type === 'above' && currentRate >= alert.target_rate) {
        return 'text-emerald-600 font-bold'
    }
    if (alert.type === 'below' && currentRate <= alert.target_rate) {
        return 'text-rose-600 font-bold'
    }
    return 'text-slate-500'
}

const getPreviewClass = (formData) => {
    const currentRate = getCurrentRate(formData.currency_id)
    if (!currentRate || !formData.target_rate) return 'text-slate-600'

    if (formData.type === 'above' && currentRate >= formData.target_rate) {
        return 'text-emerald-600'
    }
    if (formData.type === 'below' && currentRate <= formData.target_rate) {
        return 'text-rose-600'
    }
    return 'text-slate-600'
}

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedItems.value = props.alerts.map(a => a.id)
    } else {
        selectedItems.value = []
    }
}

const closeModal = () => {
    showCreateModal.value = false
    editingAlert.value = null
    form.value = {
        currency_id: '',
        type: 'above',
        target_rate: '',
    }
}

const editAlert = (alert) => {
    editingAlert.value = alert
    form.value = {
        currency_id: alert.currency_id,
        type: alert.type,
        target_rate: alert.target_rate,
    }
    showCreateModal.value = true
}

const saveAlert = () => {
    if (!isFormValid.value) return

    const data = {
        currency_id: form.value.currency_id,
        type: form.value.type,
        target_rate: form.value.target_rate,
    }

    if (editingAlert.value) {
        router.put(route('user.alerts.update', editingAlert.value.id), data, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal()
                alert('Alert updated successfully')
                refreshData()
            },
            onError: (errors) => {
                console.error('Update error:', errors)
                alert('Failed to update alert')
            }
        })
    } else {
        router.post(route('user.alerts.store'), data, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Alert created successfully')
                closeModal()
                alert('Alert created successfully')
                refreshData()
            },
            onError: (errors) => {
                console.error('Creation error:', errors)
                if (errors.response?.data?.message) {
                    alert(errors.response.data.message)
                } else {
                    alert('Failed to create alert. Please try again.')
                }
            }
        })
    }
}

const toggleAlert = (id) => {
    router.patch(route('user.alerts.toggle', id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Alert toggled successfully')
            refreshData()
        }
    })
}

const deleteAlert = (id) => {
    if (confirm('Delete this alert?')) {
        router.delete(route('user.alerts.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Alert deleted successfully')
                refreshData()
            }
        })
    }
}

const bulkDelete = () => {
    if (confirm(`Delete ${selectedItems.value.length} alert(s)?`)) {
        router.post(route('user.alerts.bulk-destroy'), {
            ids: selectedItems.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedItems.value = []
                selectAll.value = false
                alert('Selected alerts deleted successfully')
                refreshData()
            }
        })
    }
}

let unregister

onMounted(() => {
    unregister = registerEscapeHandler(closeModal)

    refreshInterval = setInterval(() => {
        refreshData()
    }, 30000)
})

onUnmounted(() => {
    if (unregister) unregister()
    if (refreshInterval) clearInterval(refreshInterval)
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