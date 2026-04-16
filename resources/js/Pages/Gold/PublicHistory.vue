<template>
    <GuestLayout>

        <Head :title="pageInfo.title" />

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-slate-900 dark:text-zinc-100 transition-colors duration-300">

            <!-- Sticky Header - Responsive -->
            <header
                class="sticky top-[150px] sm:top-[100px] z-40 w-full bg-white/95 dark:bg-zinc-900/95 border-b border-slate-200 dark:border-zinc-800 backdrop-blur-md shadow-sm">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-5">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4">

                        <!-- Page Title & Description -->
                        <div class="flex items-start gap-2 sm:gap-3">
                            <div :class="`w-0.5 sm:w-1 h-8 sm:h-10 rounded-full mt-0.5 ${selectedType === 'world_oz' ? 'bg-blue-500' :
                                selectedType === 'new_system' ? 'bg-emerald-500' : 'bg-amber-500'
                                }`"></div>
                            <div>
                                <h1
                                    class="text-base sm:text-xl md:text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                                    {{ pageInfo.title }}
                                </h1>
                                <div class="flex items-center gap-1.5 sm:gap-2 mt-0.5 sm:mt-1">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span
                                            class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                    </span>
                                    <p
                                        class="text-[8px] sm:text-[10px] md:text-xs text-slate-500 dark:text-zinc-500 font-bold uppercase tracking-wider">
                                        {{ pageInfo.description }}
                                    </p>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </header>

            <main
                class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-8 pb-12 sm:pb-16 flex flex-col gap-4 sm:gap-6">

                <!-- Tab Navigation - Responsive Horizontal Scroll -->
                <div class="overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0">
                    <div class="flex gap-2 min-w-max">
                        <Link v-for="tab in tabs" :key="tab.value"
                            :href="route('user.gold.history', { type: tab.value })"
                            class="px-3 sm:px-5 py-1.5 sm:py-2 text-xs sm:text-sm font-bold rounded-xl transition-all duration-200 whitespace-nowrap"
                            :class="selectedType === tab.value
                                ? `bg-${tab.value === 'world_oz' ? 'blue' : tab.value === 'new_system' ? 'emerald' : 'amber'}-500 text-white shadow-lg shadow-${tab.value === 'world_oz' ? 'blue' : tab.value === 'new_system' ? 'emerald' : 'amber'}-500/20`
                                : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-400 border border-slate-200 dark:border-zinc-800 hover:border-slate-300 dark:hover:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800'">
                            {{ tab.label }}
                        </Link>
                    </div>
                </div>

                <!-- Chart Card - Responsive -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl sm:rounded-2xl p-3 sm:p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div
                        class="flex items-center justify-between mb-3 sm:mb-4 pb-2 border-b border-slate-100 dark:border-zinc-800">
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <span
                                class="text-[8px] sm:text-[10px] font-black uppercase tracking-wider text-slate-400">Price
                                History
                                Chart</span>
                        </div>
                    </div>
                    <UserGoldChart :key="selectedType" :id="history.data[0]?.gold_type_id || 'world'"
                        :type="selectedType" :chartColor="pageInfo.chartColor"
                        :currency="selectedType === 'world_oz' ? 'USD' : 'MMK'"
                        :symbol="selectedType === 'world_oz' ? '$' : ''" />
                </div>

                <!-- History Components - Responsive -->
                <div class="mt-2">
                    <!-- For World Gold -->
                    <WorldGoldHistoryList v-if="selectedType === 'world_oz'" :history="history" />

                    <!-- For Myanmar Gold - New System -->
                    <MyanmarGoldHistoryList v-if="selectedType === 'new_system'" :history="history" system-type="new" />

                    <!-- For Myanmar Gold - Traditional -->
                    <MyanmarGoldHistoryList v-else-if="selectedType === 'traditional'" :history="history"
                        system-type="old" />
                </div>

            </main>
        </div>
    </GuestLayout>
</template>

<script setup>
import UserGoldChart from '@/Components/Charts/UserGoldChart.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import WorldGoldHistoryList from '@/Components/WorldGoldHistoryList.vue'
import MyanmarGoldHistoryList from '@/Components/MyanmarGoldHistoryList.vue'

const props = defineProps({
    history: { type: Object, required: true },
    selectedType: { type: String, required: true },
    breadcrumbs: { type: Array, default: () => [] },
    stats: Object,
    latestPrice: Number,
    latestSgdPrice: { type: Number, default: 0 },
})

// ── Config ───────────────────────────────────────────
const tabs = [
    { value: 'new_system', label: 'New System', color: 'emerald' },
    { value: 'traditional', label: 'Traditional', color: 'amber' },
    { value: 'world_oz', label: 'World Spot', color: 'blue' },
]

const typeMap = {
    world_oz: {
        title: 'World Gold Spot',
        description: 'USD & SGD / Troy oz · 31.1 g',
        currency: 'USD',
        symbol: '$',
        secondaryCurrency: 'SGD',
        secondarySymbol: 'S$',
        colorClass: 'blue',
        chartColor: '#2563EB',
    },
    new_system: {
        title: 'New System Gold',
        description: 'MMK / Kyatthar · 16.329 g',
        currency: 'MMK',
        symbol: '',
        colorClass: 'emerald',
        chartColor: '#10B981'
    },
    traditional: {
        title: 'Traditional Gold',
        description: 'MMK / Kyatthar · 16.606 g',
        currency: 'MMK',
        symbol: '',
        colorClass: 'amber',
        chartColor: '#F59E0B'
    },
}

const pageInfo = computed(() => typeMap[props.selectedType] ?? { title: 'Gold History', description: '', currency: '', symbol: '', colorClass: '', chartColor: '#888' })

const getTextColor = (color) => {
    const map = {
        emerald: 'text-emerald-600 dark:text-emerald-400',
        blue: 'text-blue-600 dark:text-blue-400',
        amber: 'text-amber-600 dark:text-amber-500'
    }
    return map[color] || 'text-slate-900 dark:text-white'
}

onMounted(() => { })
onUnmounted(() => { })
</script>

<style scoped>
/* Touch-friendly tap highlights */
@media (max-width: 640px) {
    .hover\:shadow-md:active {
        transform: scale(0.98);
    }
}
</style>