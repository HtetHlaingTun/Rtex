<template>
    <GuestLayout>

        <Head title="Live Market Rates" />

        <div
            class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 font-mono text-[#111] dark:text-zinc-100 transition-colors duration-300">


            <main class="max-w-[960px] mx-auto px-6 pb-20 pt-8 flex flex-col gap-12">
                <!-- Combined Gold Market Card (USD + SGD together) -->
                <WorldGoldMarket :snapshot="snapshot" :worldGoldSnapshot="worldGoldSnapshot" :sgdRate="sgdRate"
                    :sgdGoldPrice="sgdGoldPrice" :sgdPerGram="sgdPerGram" :sgdKyattharNew="sgdKyattharNew"
                    :sgdKyattharOld="sgdKyattharOld" :usdPerGram="usdPerGram" :usdPerKyattharNew="usdPerKyattharNew"
                    :usdPerKyattharOld="usdPerKyattharOld" :priceFlash="priceFlash" :priceUp="priceUp"
                    :changeAbs="changeAbs" :changePct="changePct" :dayHigh="dayHigh" :dayLow="dayLow"
                    :momentum7d="momentum7d" :premium200d="premium200d" />

                <!-- Myanmar Bullion Cards (New & Old Systems) -->
                <div class="space-y-12">
                    <MyanmarGoldSystem :goldTypes="myanmarGoldNew" system="new" systemColor="amber"
                        systemLabel="New System" weight="16.329g" :isFresh="isFresh" />

                    <MyanmarGoldSystem :goldTypes="myanmarGoldOld" system="old" systemColor="orange"
                        systemLabel="Old System" weight="16.606g" :isFresh="isFresh" />
                </div>

                <!-- International Markets -->
                <InternationalMarkets v-if="worldGold.length > 0" :goldTypes="worldGold" />

                <!-- Exchange Rates Section -->
                <CurrencyExchangeList :rates="rates" />
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 dark:border-zinc-800 py-8 mt-8">
                <div class="max-w-[960px] mx-auto px-6">
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center gap-4 text-[10px] text-slate-400">
                        <div class="flex items-center gap-4">
                            <span>© {{ new Date().getFullYear() }} MMRatePro Engine</span>
                            <span>•</span>
                            <span>Data reference from Yahoo Finance</span>

                        </div>
                        <div class="flex items-center gap-4">
                            <span>Powered by Yahoo Finance</span>
                            <span>•</span>
                            <span>v2.0</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref, computed } from 'vue'
import WorldGoldMarket from '@/Components/gold/user/WorldGoldMarket.vue'
import MyanmarGoldSystem from '@/Components/gold/user/MyanmarGoldSystem.vue'
import InternationalMarkets from '@/Components/gold/user/InternationalMarkets.vue'
import CurrencyExchangeList from '@/Components/Currency/user/CurrencyExchangeList.vue'
import axios from 'axios'

const props = defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    laravelVersion: { type: String, default: '' },
    phpVersion: { type: String, default: '' },
    rates: { type: Array, default: () => [] },
    gold: { type: Object, default: null },
    goldTypes: Array,
    pending_gold_count: Number,
    worldGoldSnapshot: Object,
    sgdRate: { type: Object, default: null },
    system_info: {
        type: Object,
        default: () => ({
            cbm_available: false,
            last_sync: 'Never',
            active_currencies: 0,
            calculation_method: 'Bank Average with Markup',
            markup_summary: '0%',
            active_banks: 4
        })
    }
})

const snapshot = ref(props.worldGoldSnapshot)
const localGoldTypes = ref(props.goldTypes)
const now = ref(new Date())
const isRefreshing = ref(false)
const priceFlash = ref(false)
const lastUpdateTime = ref(new Date())

let snapshotInterval = null
let timer

const worldGold = computed(() => localGoldTypes.value.filter(t => t.category === 'world'))
const myanmarGoldNew = computed(() => localGoldTypes.value.filter(t => t.category === 'myanmar' && t.system === 'new'))
const myanmarGoldOld = computed(() => localGoldTypes.value.filter(t => t.category === 'myanmar' && t.system === 'old'))

// USD Calculations
const usdPerGram = computed(() => (snapshot.value?.usd_price || 0) / 31.1035)
const usdPerKyattharNew = computed(() => usdPerGram.value * 16.329)
const usdPerKyattharOld = computed(() => usdPerGram.value * 16.606)

// SGD Calculations
const sgdGoldPrice = computed(() => {
    if (!snapshot.value?.usd_price || !props.sgdRate?.usd_sgd_rate) return 0
    return snapshot.value.usd_price * props.sgdRate.usd_sgd_rate
})

const sgdPerGram = computed(() => {
    if (!sgdGoldPrice.value) return 0
    return sgdGoldPrice.value / 31.1035
})

const sgdKyattharNew = computed(() => {
    if (!sgdGoldPrice.value) return 0
    const perGram = sgdGoldPrice.value / 31.1035
    return perGram * 16.329
})

const sgdKyattharOld = computed(() => {
    if (!sgdGoldPrice.value) return 0
    const perGram = sgdGoldPrice.value / 31.1035
    return perGram * 16.606
})

// Price Change
const priceUp = computed(() => (snapshot.value?.change ?? 0) >= 0)
const changePct = computed(() => Math.abs(snapshot.value?.change_percent ?? 0))
const changeAbs = computed(() => snapshot.value?.change ?? 0)

// ============ NEW METRICS ============
// 24h High/Low (from snapshot if available)
const dayHigh = computed(() => snapshot.value?.day_high || snapshot.value?.usd_price || 0)
const dayLow = computed(() => snapshot.value?.day_low || snapshot.value?.usd_price || 0)

// 7-day momentum - Using 7-day old price from controller
const momentum7d = computed(() => {
    const currentPrice = snapshot.value?.usd_price || 0
    const sevenDayOldPrice = props.gold?.seven_day_old_price || currentPrice

    if (sevenDayOldPrice > 0 && currentPrice > 0) {
        const change7d = ((currentPrice - sevenDayOldPrice) / sevenDayOldPrice) * 100
        return parseFloat(change7d.toFixed(1))
    }
    return 0
})

// Premium vs previous close - Return as NUMBER
const premium200d = computed(() => {
    if (snapshot.value?.usd_price && snapshot.value?.previous_close) {
        const premium = ((snapshot.value.usd_price - snapshot.value.previous_close) / snapshot.value.previous_close) * 100
        return parseFloat(premium.toFixed(1))
    }
    return 0
})

// ============ END NEW METRICS ============

// Helper Functions
const fetchLatestSnapshot = async () => {
    isRefreshing.value = true
    try {
        const response = await axios.get(route('api.live-gold'))
        if (response.data?.status === 'success') {
            snapshot.value = response.data
            if (response.data.gold_types) localGoldTypes.value = response.data.gold_types
            lastUpdateTime.value = new Date()
            priceFlash.value = true
            setTimeout(() => { priceFlash.value = false }, 700)
        }
    } catch (e) {
        console.error('Snapshot refresh failed:', e)
    } finally {
        isRefreshing.value = false
    }
}

const isFresh = (timestamp) => {
    if (!timestamp) return false
    return (now.value - new Date(timestamp)) < 600000
}

const timeAgo = (ts) => {
    if (!ts) return 'N/A'
    const diff = Math.floor((now.value - new Date(ts)) / 60000)
    if (diff < 1) return 'just now'
    if (diff < 60) return `${diff}m ago`
    return `${Math.floor(diff / 60)}h ago`
}

onMounted(() => {
    timer = setInterval(() => { now.value = new Date() }, 60000)
    fetchLatestSnapshot()
    snapshotInterval = setInterval(fetchLatestSnapshot, 60000)
})

onUnmounted(() => {
    clearInterval(snapshotInterval)
    clearInterval(timer)
})
</script>