<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2">
        <!-- HIGH Stat -->
        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">
                High
                <span v-if="!isWorldGold">({{ currencySymbol }})</span>
                <span v-else>
                    <span class="text-blue-600">($</span>
                    <span class="text-emerald-600">/S$</span>
                    <span class="text-slate-400">)</span>
                </span>
            </div>
            <div v-if="isWorldGold" class="space-y-1">
                <div class="text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                    {{ formatNumber(stats.high) }}
                </div>
                <div class="text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ formatNumber(stats.sgd?.high) }}
                </div>
            </div>
            <div v-else class="text-sm font-mono font-bold" :class="priceColor">
                {{ formatNumber(stats.high) }}
            </div>
        </div>

        <!-- LOW Stat -->
        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">
                Low
                <span v-if="!isWorldGold">({{ currencySymbol }})</span>
                <span v-else>
                    <span class="text-blue-600">($</span>
                    <span class="text-emerald-600">/S$</span>
                    <span class="text-slate-400">)</span>
                </span>
            </div>
            <div v-if="isWorldGold" class="space-y-1">
                <div class="text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                    {{ formatNumber(stats.low) }}
                </div>
                <div class="text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ formatNumber(stats.sgd?.low) }}
                </div>
            </div>
            <div v-else class="text-sm font-mono font-bold" :class="priceColor">
                {{ formatNumber(stats.low) }}
            </div>
        </div>

        <!-- AVG Stat -->
        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">
                Avg
                <span v-if="!isWorldGold">({{ currencySymbol }})</span>
                <span v-else>
                    <span class="text-blue-600">($</span>
                    <span class="text-emerald-600">/S$</span>
                    <span class="text-slate-400">)</span>
                </span>
            </div>
            <div v-if="isWorldGold" class="space-y-1">
                <div class="text-sm font-mono font-bold text-blue-600 dark:text-blue-400">
                    {{ formatNumber(stats.avg) }}
                </div>
                <div class="text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                    {{ formatNumber(stats.sgd?.avg) }}
                </div>
            </div>
            <div v-else class="text-sm font-mono font-bold" :class="priceColor">
                {{ formatNumber(stats.avg) }}
            </div>
        </div>

        <!-- DAYS Stat -->
        <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-zinc-800/30">
            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 mb-2">Days</div>
            <div class="text-sm font-mono font-bold text-slate-800 dark:text-white">
                {{ stats.count }}
            </div>
            <div class="text-[8px] text-slate-400 mt-1">
                Based on {{ stats.count }} {{ stats.count === 1 ? 'day' : 'days' }} of data
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            high: null,
            low: null,
            avg: null,
            count: 0,
            sgd: null
        })
    },
    type: {
        type: String,
        default: 'myanmar' // 'myanmar', 'world', 'new', 'traditional'
    },
    currencySymbol: {
        type: String,
        default: 'MMK'
    }
})

const isWorldGold = computed(() => props.type === 'world' || props.type === 'world_oz')
const isMyanmarGold = computed(() => props.type === 'myanmar' || props.type === 'new' || props.type === 'traditional')

const priceColor = computed(() => {
    if (props.type === 'new' || props.type === 'new_system') {
        return 'text-emerald-600 dark:text-emerald-400'
    }
    if (props.type === 'traditional' || props.type === 'old') {
        return 'text-amber-600 dark:text-amber-500'
    }
    return 'text-slate-800 dark:text-white'
})

const formatNumber = (value) => {
    if (value === null || value === undefined) return '—'
    return Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    })
}
</script>