<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    rates: Array
});

const approveRate = (id) => {
    router.patch(route('currencies.verify', id), {}, {
        _loader_message: 'Publishing to Live Feed...',
        onSuccess: () => {
            // Success sound or notification handled by global flash
        }
    });
};

const rejectRate = (id) => {
    if (confirm('Are you sure you want to reject and delete this entry?')) {
        router.delete(route('currencies.destroy-rate', id));
    }
};
</script>
<template>
    <AdminLayout title="Pending Verifications">
        <template #header>
            <div class="px-2 sm:px-0">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Verification Queue</h1>
                <p class="text-[11px] sm:text-sm text-slate-500 font-medium mt-1">Review submitted market rates before
                    they go live.</p>
            </div>
        </template>

        <div class="space-y-4 px-2 sm:px-0 pb-safe">
            <div v-for="rate in rates" :key="rate.id"
                class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-6 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group">

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 sm:gap-6">

                    <div class="flex items-center space-x-4">
                        <div
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black shadow-lg shrink-0">
                            {{ rate.currency?.code }}
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm sm:text-base">{{ rate.currency?.name }}</h4>
                            <p class="text-[10px] sm:text-xs text-slate-400 font-medium">
                                By {{ rate.creator?.name || 'System' }} <span class="mx-1 opacity-30">•</span> {{
                                    $formatDateTime(rate.created_at)
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-3 sm:flex items-center gap-2 sm:gap-8 bg-slate-50 px-4 sm:px-6 py-3 rounded-2xl border border-slate-100">
                        <div class="text-center sm:text-left">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Buy</span>
                            <span class="text-xs sm:text-sm font-mono font-bold text-emerald-600">{{
                                $formatNumber(rate.buy_rate) }}</span>
                        </div>

                        <div class="hidden sm:block h-8 w-[1px] bg-slate-200"></div>

                        <div class="text-center sm:text-left">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Sell</span>
                            <span class="text-xs sm:text-sm font-mono font-bold text-rose-600">{{
                                $formatNumber(rate.sell_rate)
                                }}</span>
                        </div>

                        <div class="hidden sm:block h-8 w-[1px] bg-slate-200"></div>

                        <div class="text-center sm:text-left">
                            <span
                                class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Spread</span>
                            <span class="text-xs sm:text-sm font-mono font-bold text-slate-900">
                                {{ $formatNumber(rate.sell_rate - rate.buy_rate) }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="flex items-center space-x-3 w-full lg:w-auto border-t border-slate-50 pt-4 lg:pt-0 lg:border-none">
                        <button @click="rejectRate(rate.id)"
                            class="flex-1 lg:flex-none px-4 py-3 sm:py-2 text-xs font-bold text-slate-400 hover:text-rose-600 active:bg-rose-50 rounded-xl transition-colors">
                            Reject
                        </button>
                        <button @click="approveRate(rate.id)"
                            class="flex-[2] lg:flex-none px-6 py-3 sm:py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">
                            Approve & Publish
                        </button>
                    </div>
                </div>

                <div v-if="rate.market_analysis"
                    class="mt-4 p-3 bg-amber-50/50 rounded-xl border border-amber-100/50 text-[11px] text-amber-700 italic leading-relaxed">
                    <span class="font-bold uppercase text-[9px] not-italic mr-2 opacity-50">Note:</span>
                    "{{ rate.market_analysis }}"
                </div>
            </div>

            <div v-if="rates.length === 0"
                class="py-20 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                <div class="text-slate-200 mb-4">
                    <svg class="w-16 h-16 mx-auto opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Queue is clear</h3>
                <p class="text-xs text-slate-500 mt-1">All submitted rates have been processed.</p>
            </div>
        </div>
    </AdminLayout>
</template>