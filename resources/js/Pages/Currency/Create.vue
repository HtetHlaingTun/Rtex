<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';
import { useEscapeShortcut } from '@/Composables/useKeyboardShortcut';



const props = defineProps({
    currencies: Array, // Passed from CurrencyController@create
    selectedCurrencyId: [String, Number], // Add this prop
    previousRate: Object, // Add this
});

const breadcrumbItems = [
    { label: 'Live Rates', href: route('currencies.index') },
    { label: 'Add New Rate', href: route('currencies.create') }
];

const form = useForm({
    currency_id: props.selectedCurrencyId ?? '',
    rate_date: new Date().toISOString().substr(0, 10), // Default to today
    buy_rate: '',
    sell_rate: '',
    cbm_rate: '',
    source_name: 'Market Actual',
    market_analysis: '',
});

const submit = () => {
    form.post(route('currencies.store'), {
        onSuccess: () => form.reset(),
    });
};
const goToSettings = () => {
    router.visit(route('currencies.settings'))
}
const fillFromPrevious = () => {
    if (props.previousRate) {
        form.buy_rate = Number(props.previousRate.buy_rate);
        form.sell_rate = Number(props.previousRate.sell_rate);
    }
};

useEscapeShortcut(fillFromPrevious);
</script>

<template>
    <AdminLayout title="Add Exchange Rate">
        <template #breadcrumbs>
            <div class="px-2 sm:px-0">
                <Breadcrumbs :items="breadcrumbItems" />
            </div>
        </template>

        <template #header>
            <div class="flex items-center space-x-3 px-2 sm:px-0">
                <div class="bg-indigo-600 p-2 rounded-lg shadow-lg shadow-indigo-200 shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Submit Exchange Rate
                    </h1>
                    <p class="text-[11px] sm:text-sm text-slate-500 font-medium">Drafting market data for administrative
                        verification.</p>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

            <div class="lg:col-span-2 order-2 lg:order-1">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50/50 px-6 sm:px-8 py-4 border-b border-slate-100">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Rate Entry Details
                        </h3>
                    </div>

                    <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                            <div>
                                <label
                                    class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Target
                                    Asset</label>
                                <select v-model="form.currency_id"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl py-3 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': form.errors.currency_id }">
                                    <option value="" disabled>Select Asset...</option>
                                    <option v-for="c in currencies" :key="c.id" :value="c.id">{{ c.code }} — {{ c.name
                                        }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Effective
                                    Date</label>
                                <input type="date" v-model="form.rate_date"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl py-3" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="relative">
                                <label
                                    class="block text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2">Buy
                                    Rate</label>
                                <input type="number" step="0.01" v-model="form.buy_rate"
                                    @keydown.esc.stop="form.buy_rate = ''; $event.target.blur()" placeholder="0.00"
                                    class="w-full border-slate-200 rounded-xl py-3 pr-12 focus:ring-emerald-500" />
                                <span
                                    class="absolute right-4 bottom-3.5 text-[10px] font-bold text-slate-400">MMK</span>
                            </div>

                            <div class="relative">
                                <label
                                    class="block text-[10px] font-bold text-rose-600 uppercase tracking-wider mb-2">Sell
                                    Rate</label>
                                <input type="number" step="0.01" v-model="form.sell_rate"
                                    @keydown.esc.stop="form.sell_rate = ''; $event.target.blur()" placeholder="0.00"
                                    class="w-full border-slate-200 rounded-xl py-3 pr-12 focus:ring-rose-500" />
                                <span
                                    class="absolute right-4 bottom-3.5 text-[10px] font-bold text-slate-400">MMK</span>
                            </div>

                            <div class="relative">
                                <label
                                    class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">CBM
                                    Official</label>
                                <input type="number" step="0.01" v-model="form.cbm_rate" placeholder="Optional"
                                    class="w-full bg-slate-50 border-slate-100 border-dashed rounded-xl py-3" />
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">Market
                                Analysis</label>
                            <textarea v-model="form.market_analysis" rows="3"
                                placeholder="Optional: Describe market volatility..."
                                class="w-full border-slate-200 rounded-xl py-3 bg-slate-50/30 focus:bg-white transition-all"></textarea>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-slate-100 gap-4">
                            <button type="button" @click="goToSettings"
                                class="text-[11px] font-bold text-indigo-600 hover:text-indigo-700 active:opacity-60 transition">
                                + Register New Currency
                            </button>

                            <div class="flex items-center space-x-4 w-full sm:w-auto">
                                <Link :href="route('currencies.index')"
                                    class="flex-1 sm:flex-none text-center text-xs font-bold text-slate-400 hover:text-slate-600">
                                Cancel</Link>
                                <button type="submit" :disabled="form.processing"
                                    class="flex-[2] sm:flex-none px-8 py-3 bg-slate-900 text-white text-sm font-bold rounded-xl shadow-lg active:scale-95 transition-all disabled:opacity-50">
                                    {{ form.processing ? '...' : 'Submit Rates' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-4 sm:space-y-6 order-1 lg:order-2">

                <div v-if="previousRate" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[9px] font-bold uppercase tracking-widest text-slate-400">Previous</h4>
                        <span
                            class="text-[8px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full uppercase">
                            {{ $formatDateTime(previousRate.created_at) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <span class="block text-[8px] text-slate-400 uppercase font-bold mb-1">Buy</span>
                            <span class="text-sm font-mono font-bold text-slate-700">{{
                                $formatNumber(previousRate.buy_rate)
                                }}</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <span class="block text-[8px] text-slate-400 uppercase font-bold mb-1">Sell</span>
                            <span class="text-sm font-mono font-bold text-slate-700">{{
                                $formatNumber(previousRate.sell_rate)
                                }}</span>
                        </div>
                    </div>

                    <button type="button" @click="fillFromPrevious"
                        class="mt-4 w-full py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[10px] font-bold text-slate-600 hover:bg-slate-100 active:scale-[0.98] transition uppercase">
                        Sync Previous Values
                    </button>
                </div>

                <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-xl">
                    <h4 class="text-[10px] font-bold uppercase tracking-widest opacity-60 mb-3">Calculated Spread</h4>
                    <div class="flex justify-between items-end border-b border-indigo-800 pb-3">
                        <span class="text-xs font-medium opacity-80">Margin</span>
                        <span class="text-2xl font-mono font-bold tracking-tighter text-indigo-300">
                            {{ $formatNumber(form.sell_rate && form.buy_rate ? (form.sell_rate - form.buy_rate) :
                                '0.00') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>