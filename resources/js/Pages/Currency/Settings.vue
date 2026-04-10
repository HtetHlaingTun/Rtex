<script setup>
import { useForm, router, Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    currencies: Array,
    errors: Object
});

const page = usePage();
const showFlash = ref(false);

const breadcrumbItems = [
    { label: 'Live Rates', href: route('currencies.index') },
    { label: 'Currencies' }
];

// Watch for flash messages
watch(() => page.props.flash, (newVal) => {
    if (newVal?.success || newVal?.error) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 5000);
    }
}, { deep: true });

const form = useForm({
    code: '',
    name: '',
    symbol: '',
    decimal_places: 2,
    display_order: 0,
    source_mode: 'cross_usd',        // ADD THIS - Default to cross_usd for new currencies
    bank_markup_percentage: 2.0,     // ADD THIS - Default markup 2%
    buy_spread_percentage: 0.5,      // ADD THIS - Default buy spread
    sell_spread_percentage: 0.5,     // ADD THIS - Default sell spread
    spread_type: 'percentage',       // ADD THIS - Default spread type
    is_active: true                  // ADD THIS - Active by default
});

const autoUppercase = () => {
    form.code = form.code.toUpperCase();
};

const submit = () => {
    if (form.processing) return;

    form.transform((data) => ({
        ...data,
        _loader_message: 'Saving New Currency...',
    })).post(route('currencies.store-type'), {
        onSuccess: () => {
            form.reset();
            // Reset to defaults after successful save
            form.source_mode = 'cross_usd';
            form.bank_markup_percentage = 2.0;
            form.buy_spread_percentage = 0.5;
            form.sell_spread_percentage = 0.5;
            form.spread_type = 'percentage';
            form.is_active = true;
        },
        onError: () => {
            // Handle error
        }
    });
};

const deleteCurrency = (id) => {
    if (confirm('Move this currency to the trash? (Note: History will be preserved)')) {
        router.delete(route('currencies.destroy', id), {
            preserveScroll: true,
            data: { _loader_message: 'Deleting Currency...' }
        });
    }
};
</script>

<template>

    <Head title="Currency Management" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbItems" />
        </template>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            <!-- Register Form - Left Side -->
            <div class="xl:col-span-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800">Register Currency</h3>
                        <p class="text-xs text-slate-500">Define a new asset for exchange tracking.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-6 space-y-5">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-1">
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Code</label>
                                <input v-model="form.code" @input="autoUppercase"
                                    @keydown.esc="form.code = ''; $event.target.blur()" type="text" maxlength="3"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-mono text-center font-bold"
                                    :class="{ 'border-red-500': form.errors.code }" placeholder="USD" />
                                <p v-if="form.errors.code" class="text-[10px] text-red-500 mt-1 font-medium">{{
                                    form.errors.code }}</p>
                            </div>
                            <div class="col-span-2">
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Full
                                    Name</label>
                                <input v-model="form.name" @keydown.esc="form.name = ''; $event.target.blur()"
                                    type="text"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="{ 'border-red-500': form.errors.name }"
                                    placeholder="United States Dollar" />
                                <p v-if="form.errors.name" class="text-[10px] text-red-500 mt-1 font-medium">{{
                                    form.errors.name }}</p>
                            </div>
                        </div>

                        <!-- Symbol & Decimals -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Symbol</label>
                                <input v-model="form.symbol" @keydown.esc="form.symbol = ''; $event.target.blur()"
                                    type="text"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="$" />
                                <p v-if="form.errors.symbol" class="text-[10px] text-red-500 mt-1 font-medium">{{
                                    form.errors.symbol }}</p>
                            </div>

                            <div>
                                <label
                                    class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Decimals</label>
                                <input v-model="form.decimal_places" type="number"
                                    class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </div>

                        <!-- Source Mode Selection - ADD THIS SECTION -->
                        <div class="pt-2">
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Source
                                Mode</label>
                            <div class="grid grid-cols-3 gap-2 p-1 bg-slate-100 rounded-xl">
                                <button type="button" v-for="mode in ['bank_avg', 'cross_usd', 'cbm']" :key="mode"
                                    @click="form.source_mode = mode"
                                    class="py-2 rounded-lg text-[11px] font-bold transition-all"
                                    :class="form.source_mode === mode ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'">
                                    {{ mode === 'bank_avg' ? 'Bank Average' : mode === 'cross_usd' ? 'Cross Rate'
                                        : 'CBMRate'
                                    }}
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">
                                <span v-if="form.source_mode === 'bank_avg'">Uses average of 4 banks (KBZ, Yoma, CB,
                                    AYA)</span>
                                <span v-else-if="form.source_mode === 'cross_usd'">Calculates using USD/MMK ÷
                                    USD/Currency (from
                                    Yahoo Finance)</span>
                                <span v-else>Uses CBM rate with conversion factor</span>
                            </p>
                        </div>

                        <!-- Markup (for cross_usd and bank_avg) -->
                        <div v-if="form.source_mode !== 'cbm'" class="pt-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">
                                {{ form.source_mode === 'bank_avg' ? 'Bank Markup (%)' : 'Markup (%)' }}
                            </label>
                            <input v-model.number="form.bank_markup_percentage" type="number" step="0.1"
                                class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500" />
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ form.source_mode === 'bank_avg' ? 'Base Rate = Bank Average × (1 + Markup%)'
                                    : 'BaseRate = (USD / MMK ÷ USD / Currency) × (1 + Markup %)' }}
                            </p>
                        </div>

                        <!-- Spread Configuration -->
                        <div class="pt-2">
                            <label
                                class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-2">Spread
                                Configuration</label>
                            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl mb-3">
                                <button type="button" v-for="type in ['percentage', 'fixed']" :key="type"
                                    @click="form.spread_type = type"
                                    class="py-2 rounded-lg text-[11px] font-bold transition-all"
                                    :class="form.spread_type === type ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500'">
                                    {{ type === 'percentage' ? 'Percentage' : 'Fixed Margin' }}
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Buy {{
                                        form.spread_type ===
                                            'percentage' ? '%' : 'MMK' }}</label>
                                    <input v-model.number="form.buy_spread_percentage" type="number" step="0.01"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/10">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase">Sell {{
                                        form.spread_type ===
                                            'percentage' ? '%' : 'MMK' }}</label>
                                    <input v-model.number="form.sell_spread_percentage" type="number" step="0.01"
                                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500/10">
                                </div>
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="pt-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.is_active"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs font-bold text-slate-600">Active (show in live rates)</span>
                            </label>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex justify-center items-center disabled:opacity-50">
                            <svg v-if="!form.processing" class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ form.processing ? 'Saving...' : 'Save Asset' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Currencies List - Right Side -->
            <div class="xl:col-span-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800">Active Currencies</h3>
                        <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-xs font-bold">{{
                            currencies.length }} Available</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                        Asset</th>
                                    <th
                                        class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                        Full Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                        Source Mode</th>
                                    <th
                                        class="px-6 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-right text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="c in currencies" :key="c.id"
                                    class="group hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center font-bold shadow-sm ring-4 ring-white">
                                                {{ c.code.substring(0, 2) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-slate-900 uppercase font-mono">{{
                                                    c.code }}
                                                </div>
                                                <div class="text-[10px] text-slate-400">ID: {{
                                                    c.id.toString().padStart(4, '0')
                                                    }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-slate-600 font-medium">{{ c.name }}</div>
                                        <div class="text-xs text-slate-400">Symbol: <span
                                                class="text-indigo-600 font-bold">{{
                                                    c.symbol || '-' }}</span></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold"
                                            :class="c.source_mode === 'bank_avg' ? 'bg-emerald-100 text-emerald-700' : 'bg-purple-100 text-purple-700'">
                                            {{ c.source_mode === 'bank_avg' ? 'Bank Avg' : c.source_mode === 'cross_usd'
                                                ?
                                                'Cross Rate' : 'CBM' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                            <span
                                                class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                            {{ c.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button @click="deleteCurrency(c.id)"
                                            class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="currencies.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-slate-200 mb-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 12H4" />
                                            </svg>
                                            <p class="text-slate-400 text-sm font-medium italic">No assets registered
                                                yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>