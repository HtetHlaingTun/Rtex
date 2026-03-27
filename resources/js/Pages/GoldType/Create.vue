<script setup>
import { useForm, Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumbs from '@/Components/Layouts/Breadcrumbs.vue';

const props = defineProps({
    goldTypes: Array, // Received from the controller
});

const form = useForm({
    name: '',
    code: '',
    category: 'myanmar',
    purity: '',
    system: 'new',
    unit: 'Kyatthar',
    gram_conversion: '',
    color_code: '#F59E0B',
    is_active: true,
});

const breadcrumbs = [
    { label: 'Gold Rates', href: route('gold.index') },
    { label: 'Register Category' }
];

const submit = () => {
    form.post(route('gold-types.store'), {
        onSuccess: () => form.reset(),
    });
};

const deleteType = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        router.delete(route('gold-types.destroy', id));
    }
};
</script>

<template>

    <Head title="Register Gold Type" />
    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <div class="lg:col-span-8 space-y-6">
                    <div
                        class="bg-white border border-slate-200  p-6 sm:p-10 shadow-sm transition-all duration-300 hover:shadow-md">
                        <header class="mb-10">

                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tighter italic">Register
                                Gold
                                Category</h1>
                            <p class="text-sm text-slate-400 font-medium mt-1">Define a new purity or origin for the
                                market
                                index.</p>
                        </header>

                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">Display
                                        Name</label>
                                    <input v-model="form.name" type="text" placeholder="e.g. Academy Gold"
                                        class="w-full bg-slate-50 border-slate-200 rounded-2xl font-bold py-4 px-5 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:bg-white transition-all outline-none" />
                                    <p v-if="form.errors.name"
                                        class="text-[10px] text-rose-500 font-bold px-1 uppercase italic">{{
                                            form.errors.name }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">System
                                        Code</label>
                                    <input v-model="form.code" type="text" placeholder="AC-24K"
                                        class="w-full bg-slate-50 border-slate-200 rounded-2xl font-bold uppercase py-4 px-5 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:bg-white transition-all outline-none" />
                                    <p v-if="form.errors.code"
                                        class="text-[10px] text-rose-500 font-bold px-1 uppercase italic">{{
                                            form.errors.code }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="form.category === 'myanmar'" class="space-y-3">
                                <label
                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">Weight
                                    System</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button type="button" @click="form.system = 'new'"
                                        :class="form.system === 'new' ? 'bg-slate-900 text-white border-slate-900 shadow-lg scale-[1.02]' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-300'"
                                        class="border-2 rounded-[1.5rem] p-5 text-left transition-all duration-300 relative overflow-hidden group">
                                        <span
                                            class="block text-[10px] font-black uppercase tracking-widest mb-1 opacity-60">New
                                            System</span>
                                        <span class="block text-sm font-black italic">1 Kyatthar = 16.329g</span>
                                    </button>

                                    <button type="button" @click="form.system = 'old'"
                                        :class="form.system === 'old' ? 'bg-orange-500 text-white border-orange-500 shadow-lg scale-[1.02]' : 'bg-white text-slate-500 border-slate-200 hover:border-orange-300'"
                                        class="border-2 rounded-[1.5rem] p-5 text-left transition-all duration-300 group">
                                        <span
                                            class="block text-[10px] font-black uppercase tracking-widest mb-1 opacity-60">Old
                                            System</span>
                                        <span class="block text-sm font-black italic">1 Kyatthar = 16.606g</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">Category</label>
                                    <select v-model="form.category"
                                        class="w-full bg-slate-50 border-slate-200 rounded-2xl font-bold py-4 px-5 appearance-none focus:ring-4 focus:ring-amber-500/10">
                                        <option value="myanmar">Myanmar Local</option>
                                        <option value="world">World Spot</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">Trading
                                        Unit</label>
                                    <input v-model="form.unit" type="text" placeholder="e.g. Tical"
                                        class="w-full bg-slate-50 border-slate-200 rounded-2xl font-bold py-4 px-5 focus:ring-4 focus:ring-amber-500/10" />
                                </div>
                            </div>

                            <div class="space-y-2 mb-10">
                                <label
                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 px-1">Purity
                                    Description</label>
                                <input v-model="form.purity" type="text" placeholder="e.g. 99.99% or 16-Pe"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl font-bold py-4 px-5 focus:ring-4 focus:ring-amber-500/10" />
                            </div>

                            <button type="submit" :disabled="form.processing"
                                class="hidden xl:block group relative w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.3em] hover:bg-black transition-all shadow-[0_20px_40px_-15px_rgba(0,0,0,0.3)] active:scale-[0.98] disabled:opacity-50 overflow-hidden">
                                <span class="relative z-10">{{ form.processing ? 'Syncing...' : 'Save Gold Category'
                                    }}</span>
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-amber-500 to-orange-500 translate-y-full group-hover:translate-y-0 transition-transform duration-300 opacity-20">
                                </div>
                            </button>

                            <SlideButton class="xl:hidden " :loading="form.processing" :disabled="form.processing"
                                label="Slide to Register" successLabel="Authenticating..." @success="submit" />
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-4 lg:sticky lg:top-6">
                    <div
                        class="bg-slate-50 border border-slate-200 rounded-[2.5rem] p-6 shadow-sm overflow-hidden flex flex-col max-h-[calc(100vh-100px)]">
                        <div class="flex items-center justify-between mb-6 px-2">
                            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Registry</h3>
                            <span
                                class="bg-white border border-slate-200 text-slate-600 px-3 py-1 rounded-full text-[10px] font-black shadow-sm">
                                {{ goldTypes.length }} TOTAL
                            </span>
                        </div>

                        <div class="space-y-3 overflow-y-auto custom-scrollbar pr-1 flex-grow">
                            <div v-for="type in goldTypes" :key="type.id"
                                class="group flex items-center justify-between p-4 rounded-2xl border transition-all duration-300"
                                :class="type.deleted_at ? 'bg-slate-100/50 border-dashed border-slate-300 opacity-60' : 'bg-white border-slate-100 shadow-sm hover:border-amber-200 hover:shadow-md'">

                                <div class="flex flex-col gap-0.5">
                                    <span class="text-xs font-black uppercase tracking-tight"
                                        :class="type.deleted_at ? 'text-slate-400' : 'text-slate-900'">
                                        {{ type.name }}
                                    </span>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="text-[8px] font-black uppercase text-slate-400">{{ type.category
                                            }}</span>
                                        <span v-if="type.category === 'myanmar'"
                                            :class="type.system === 'old' ? 'bg-orange-50 text-orange-600 border-orange-100' : 'bg-slate-100 text-slate-500 border-slate-200'"
                                            class="text-[7px] font-black uppercase border px-1.5 py-0.5 rounded-md">
                                            {{ type.system === 'old' ? '16.606g' : '16.329g' }}
                                        </span>
                                        <span v-if="type.deleted_at"
                                            class="text-[7px] font-black text-rose-500 bg-rose-50 px-1.5 py-0.5 rounded-md uppercase">Trashed</span>
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    <button v-if="!type.deleted_at" @click="deleteType(type.id)"
                                        class="p-2 text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    <button v-else @click="router.post(route('gold-types.restore', type.id))"
                                        class="p-2 text-emerald-500 hover:bg-emerald-50 rounded-xl transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="goldTypes.length === 0" class="py-20 text-center">
                                <div class="inline-flex p-4 rounded-full bg-slate-100 mb-4">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No active
                                    categories
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>