<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';

import { router, Head } from '@inertiajs/vue3';

const props = defineProps({
    pendingPrices: Object,
});

const breadcrumbs = [
    { label: 'Gold Market', href: route('gold.index') },
    { label: 'Verification Queue' }
];

const approvePrice = (id) => {
    router.patch(route('gold.approve', id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Verification Queue" />
    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="max-w-5xl mx-auto pb-20">
            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900 italic">Verification Queue godl</h1>
                <p class="text-slate-500 font-medium">Review and approve staged gold market rates.</p>
            </div>

            <div v-if="pendingPrices.data.length > 0" class="space-y-4">
                <div v-for="price in pendingPrices.data" :key="price.id"
                    class="bg-white border border-slate-200 rounded-[2rem] p-6 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">

                    <div class="flex items-center space-x-6">
                        <div
                            class="w-16 h-16 bg-slate-900 rounded-2xl flex flex-col items-center justify-center text-amber-400 shadow-inner">
                            <span class="text-[10px] font-black uppercase leading-none">{{ price.gold_type.code
                                }}</span>
                            <div class="h-0.5 w-4 bg-slate-700 my-1"></div>
                            <span class="text-[8px] font-bold text-slate-500 uppercase">{{ price.gold_type.unit
                                }}</span>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">{{ price.gold_type.name }}</h3>
                            <div
                                class="flex items-center space-x-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <span>{{ price.price_date }}</span>
                                <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                <span>By {{ price.creator?.name || 'System' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between md:justify-end flex-1 gap-8">
                        <div class="text-right">
                            <span
                                class="block text-[10px] font-black text-slate-400 uppercase mb-1 tracking-tighter">Proposed
                                Rate</span>
                            <div class="text-2xl font-mono font-black text-slate-900 italic">
                                {{ $formatNumber(price.price) }} <span class="text-xs not-italic text-slate-400">{{
                                    price.currency?.code || 'MMK' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <button
                                class="p-3 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <button @click="approvePrice(price.id)"
                                class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg shadow-emerald-200 transition-all active:scale-95 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            </button>
                        </div>
                    </div>
                </div>

                <Paginations :links="pendingPrices.links" />
            </div>

            <div v-else class="py-32 text-center bg-white border-2 border-dashed border-slate-100 rounded-[3rem]">
                <div
                    class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-black text-slate-900">Queue is Clear</h3>
                <p class="text-slate-400 text-sm mt-2 font-medium">All gold prices are verified and live.</p>
                <Link :href="route('gold.index')"
                    class="mt-8 inline-block text-xs font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800">
                Return to Dashboard →
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>