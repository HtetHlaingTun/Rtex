<template>
    <GuestLayout>

        <Head :title="`${systemLabel} Gold History`" />

        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="mb-6">
                <Link :href="route('welcome')" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Back to Home
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h1 class="text-2xl font-black text-slate-900 mb-2">{{ systemLabel }} Gold</h1>
                <p class="text-sm text-slate-500">{{ weight }} per Kyatthar</p>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div v-for="type in goldTypes" :key="type.id" class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">{{ type.name }}</h2>
                            <p class="text-sm text-slate-500">{{ type.purity }} · {{ type.unit }}</p>
                        </div>
                        <!-- FIX: Change this route -->
                        <Link :href="route('gold.public-history', type.id)"
                            class="text-amber-600 hover:text-amber-700 font-bold">
                            View Details →
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <span class="text-xs font-black text-slate-600">ကျပ်သား</span>
                            <div class="text-lg font-mono font-black text-slate-800">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price) : '—' }}
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <span class="text-xs font-black text-slate-600">ပဲ</span>
                            <div class="text-lg font-mono font-black text-slate-800">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 16) :
                                    '—' }}
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <span class="text-xs font-black text-slate-600">မတ်</span>
                            <div class="text-lg font-mono font-black text-slate-800">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 4) :
                                    '—' }}
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <span class="text-xs font-black text-slate-600">ရွေး</span>
                            <div class="text-lg font-mono font-black text-slate-800">
                                {{ type.latest_verified_price ? $formatNumber(type.latest_verified_price.price / 128) :
                                    '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    goldTypes: Array,
    systemLabel: String,
    weight: String,
    type: String,
});
</script>