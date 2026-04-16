<template>

    <Head :title="`${goldType.name} History`" />

    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="breadcrumbs" />
        </template>

        <div class="max-w-6xl mx-auto space-y-8 pb-16">
            <!-- ===== HEADER ===== -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 md:max-w-6xl md:mx-auto">
                <div class="flex items-center gap-4">
                    <!-- System badge with enhanced animation -->
                    <div :class="goldType.system === 'old'
                        ? 'from-orange-400 to-orange-600 border-orange-300'
                        : 'from-amber-300 to-yellow-500 border-amber-200'"
                        class="w-14 h-14 rounded-2xl border-2 flex flex-col items-center justify-center shrink-0 relative overflow-hidden bg-gradient-to-br shadow-lg group">
                        <div
                            class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/40 to-transparent -translate-x-full animate-shine group-hover:pause">
                        </div>
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-9 h-9 text-white/90 drop-shadow-md mb-1">
                            <path d="M3 17l2-3h6l2 3H3z" opacity="0.5" />
                            <path d="M11 17l2-3h6l2 3h-10z" opacity="0.7" />
                            <path d="M6 13l2-4h8l2 4H6z" />
                            <path d="M8 10h8l.5 1h-9l.5-1z" fill="white" opacity="0.3" />
                        </svg>
                        <div
                            class="absolute bottom-0 w-full bg-black/20 backdrop-blur-[1px] py-0.5 flex justify-center">
                            <span class="text-[8px] font-black uppercase text-white tracking-widest leading-none">
                                {{ goldType.system }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h1 class="text-2xl font-black text-slate-900 italic tracking-tight">
                            {{ goldType.name }}
                        </h1>
                        <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-2">
                            <span class="w-1 h-1 bg-amber-500 rounded-full"></span>
                            {{ goldType.system === 'old' ? 'Traditional System (16.606g)' : 'Premium System (16.329g)'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Live world reference with enhanced styling -->
                <div v-if="latestSnapshot"
                    class="flex items-center gap-3 px-5 py-3 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl text-white shadow-lg border border-amber-500/20">
                    <div class="relative">
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        <div class="absolute inset-0 w-2 h-2 bg-emerald-400 rounded-full animate-ping opacity-75"></div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">Global Spot</span>
                        <span class="text-sm font-mono font-black text-amber-400">
                            ${{ $formatNumber(latestSnapshot.usd_price) }}
                        </span>
                    </div>
                    <div class="h-8 w-px bg-white/10 mx-1"></div>
                    <div class="flex flex-col">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider">USD/MMK</span>
                        <span class="text-sm font-mono font-black text-white">
                            {{ $formatNumber(latestSnapshot.usd_mmk_rate) }}
                        </span>
                    </div>
                </div>


            </div>

            <!-- Market Status Indicator -->
            <div class="md:max-w-full text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-full">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[9px] font-black text-slate-600 uppercase tracking-wider">
                        Live Market Monitoring · Real-time Updates
                    </span>
                    <span class="text-[8px] text-slate-400">{{ goldType.purity || goldType.unit }}</span>
                </div>
            </div>

            <!-- ===== TODAY'S PRICE + STATS ===== -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:max-w-6xl md:mx-auto">
                <!-- Today's Price Card - Enhanced -->
                <div :class="goldType.system === 'old'
                    ? 'bg-gradient-to-br from-orange-500 via-orange-600 to-red-600 border-orange-400/30 shadow-orange-900/20'
                    : 'bg-gradient-to-br from-amber-400 via-yellow-500 to-amber-600 border-amber-300/30 shadow-amber-900/20'"
                    class="lg:col-span-2 p-7 rounded-[2rem] text-white relative overflow-hidden shadow-2xl transition-all duration-500 hover:scale-[1.01] group">

                    <!-- Animated background -->
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/20 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full border border-white/10">
                    </div>

                    <div class="relative z-10">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-4 bg-black/40 rounded-full"></span>
                                    <span class="text-[10px] font-black text-black/80 uppercase tracking-[0.2em]">
                                        Local Market Price
                                    </span>
                                </div>

                                <!-- USD Rate Reference -->
                                <div v-if="usdRate"
                                    class="bg-black/10 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[7px] font-black text-white/70 uppercase tracking-tighter">USD
                                            Reference</span>
                                        <div v-if="$isToday(usdRate.rate_date)"
                                            class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-lg font-mono font-black text-black">
                                            {{ $formatNumber(usdRate.mid_rate) }}
                                        </span>
                                        <span class="text-[9px] font-bold text-black">MMK</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[6px] text-black font-sans font-bold">Buy: {{
                                            $formatNumber(usdRate.buy_rate)
                                        }}</span>
                                        <span class="w-px h-2 bg-white/20"></span>
                                        <span class="text-[6px] text-black font-sans font-bold">Sell: {{
                                            $formatNumber(usdRate.sell_rate)
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <span v-if="todayPrice?.source_type === 'auto'"
                                class="text-[8px] font-black text-black bg-white/30 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 uppercase tracking-tighter flex items-center gap-1">
                                <div class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></div>
                                Live Sync
                            </span>
                        </div>

                        <!-- Current Price -->
                        <div v-if="todayPrice" class="flex flex-col gap-2">
                            <div class="flex items-baseline gap-3">
                                <span
                                    class="text-4xl sm:text-5xl text-black font-mono font-black italic tracking-tighter tabular-nums drop-shadow-sm">
                                    {{ $formatNumber(todayPrice.price) }}
                                </span>
                                <span class="text-sm font-black text-black/60 font-mono">MMK</span>
                            </div>

                            <!-- Price Change Indicator -->
                            <div v-if="priceChange" class="flex items-center gap-2">
                                <div :class="priceChange.isUp ? 'bg-emerald-500/30' : 'bg-rose-500/30'"
                                    class="px-2 py-1 rounded-lg">
                                    <span :class="priceChange.isUp ? 'text-emerald-200' : 'text-rose-200'"
                                        class="text-xs font-black">
                                        {{ priceChange.isUp ? '▲' : '▼' }}
                                        {{ Math.abs(priceChange.percent) }}%
                                    </span>
                                </div>
                                <span class="text-[10px] text-black/50">
                                    vs yesterday ({{ $formatNumber(yesterdayPrice?.price) }} MMK)
                                </span>
                            </div>
                        </div>

                        <div v-else class="py-4">
                            <div class="flex flex-col">
                                <span class="text-3xl text-black/20 font-mono font-black italic tracking-tighter">
                                    --- , ---
                                </span>
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="w-2 h-2 bg-black/20 rounded-full"></div>
                                    <span class="text-xs font-black text-black/40 uppercase tracking-widest italic">
                                        Awaiting Market Data
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards - Enhanced -->
                <div class="space-y-4">
                    <!-- All Time High -->
                    <div
                        class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-2xl p-5 hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[9px] font-black text-emerald-700 uppercase tracking-wider">All-Time High</p>
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <p class="text-2xl font-mono font-black text-emerald-900">
                            {{ stats.highest ? $formatNumber(stats.highest) : '—' }}
                        </p>
                        <p class="text-[9px] text-emerald-600 mt-1 font-bold">MMK per Kyatthar</p>
                    </div>

                    <!-- All Time Low -->
                    <div
                        class="bg-gradient-to-br from-rose-50 to-rose-100 border border-rose-200 rounded-2xl p-5 hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[9px] font-black text-rose-700 uppercase tracking-wider">All-Time Low</p>
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <p class="text-2xl font-mono font-black text-rose-900">
                            {{ stats.lowest ? $formatNumber(stats.lowest) : '—' }}
                        </p>
                        <p class="text-[9px] text-rose-600 mt-1 font-bold">MMK per Kyatthar</p>
                    </div>

                    <!-- Average Price -->
                    <!-- Add this as a third card in your stats grid -->
                    <div
                        class="bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-2xl p-5 hover:shadow-lg transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[9px] font-black text-slate-600 uppercase tracking-wider">30-Day Avg</p>
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <p class="text-2xl font-mono font-black text-slate-800">
                            {{ stats.average ? $formatNumber(stats.average) : '—' }}
                        </p>
                        <p class="text-[9px] text-slate-500 mt-1 font-bold">MMK per Kyatthar</p>
                    </div>
                </div>
            </div>

            <!-- ===== 30-DAY CHART ===== -->
            <div
                class="bg-white border border-slate-200 rounded-[2rem] p-6 shadow-xl shadow-slate-200/50 relative overflow-hidden group">
                <div class="absolute -top-32 -right-32 w-72 h-72 rounded-full blur-[110px] opacity-[0.15] pointer-events-none"
                    :class="priceChange?.isUp !== false ? 'bg-emerald-400' : 'bg-rose-400'">
                </div>

                <div
                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 relative z-10">
                    <div>
                        <h2
                            class="text-[11px] font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-slate-900 rounded-full"></span>
                            {{ chartData.length > 0 && chartData[0]?.date?.includes(':') ? 'Intraday Movement'
                                : 'Price History Chart' }}
                        </h2>
                        <p class="text-[10px] text-slate-400 font-bold mt-1">
                            {{ chartData.length }} snapshots over 30 days
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div :class="priceChange?.isUp !== false ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-[9px] font-black uppercase tracking-tighter shadow-sm">
                            <span class="relative flex h-1.5 w-1.5">
                                <span :class="priceChange?.isUp !== false ? 'bg-emerald-400' : 'bg-rose-400'"
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                                <span :class="priceChange?.isUp !== false ? 'bg-emerald-500' : 'bg-rose-500'"
                                    class="relative inline-flex rounded-full h-1.5 w-1.5"></span>
                            </span>
                            {{ priceChange?.isUp !== false ? 'Bullish Trend' : 'Bearish Trend' }}
                        </div>

                        <div class="text-[9px] text-slate-400">
                            Volatility: {{ stats.volatility || '0' }}%
                        </div>
                    </div>
                </div>

                <!-- Chart Container -->
                <div v-if="chartData.length > 1"
                    class="relative h-64 bg-gradient-to-b from-slate-50/50 to-white rounded-2xl overflow-hidden border border-slate-100/80">
                    <GoldPriceChart :chart-data="chartData" :is-up="priceChange?.isUp !== false" />
                </div>

                <div v-else
                    class="h-64 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Insufficient Data for
                        Chart</p>
                </div>
            </div>

            <!-- ===== HISTORY TABLE ===== -->
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">
                            Price History
                        </h2>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            {{ stats.total_entries }} total records · Last 30 days
                        </p>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-4 text-[9px] font-black uppercase text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            Auto-sync
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-indigo-400 rounded-full"></div>
                            Manual
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                            Verified
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th
                                    class="text-left px-6 py-3 font-black text-slate-400 uppercase tracking-wider text-[10px]">
                                    Date & Time</th>
                                <th
                                    class="text-right px-6 py-3 font-black text-slate-400 uppercase tracking-wider text-[10px]">
                                    Price (MMK)</th>
                                <th
                                    class="text-right px-6 py-3 font-black text-slate-400 uppercase tracking-wider text-[10px]">
                                    24h Change</th>
                                <th
                                    class="text-right px-6 py-3 font-black text-slate-400 uppercase tracking-wider text-[10px] hidden md:table-cell">
                                    World Gold</th>
                                <th
                                    class="text-center px-6 py-3 font-black text-slate-400 uppercase tracking-wider text-[10px] hidden sm:table-cell">
                                    Source</th>
                                <th
                                    class="text-center px-6 py-3 font-black text-slate-400 uppercase tracking-wider text-[10px] hidden lg:table-cell">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="record in history.data" :key="record.id"
                                class="hover:bg-slate-50 transition-colors group">

                                <!-- Date & Time -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-xs">
                                            {{ $formatDate(record.price_date) }}
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-medium">
                                            {{ record.created_at ? $formatTime24(record.created_at) : '—' }}
                                        </span>
                                    </div>
                                    <span v-if="$isToday(record.price_date)"
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-emerald-500/10 text-emerald-600 rounded text-[7px] font-black uppercase mt-1">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span
                                                class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                        </span>
                                        Live
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-mono font-black text-slate-900 text-sm">
                                            {{ $formatNumber(record.price) }}
                                        </span>
                                        <span class="text-[9px] text-slate-400">MMK</span>
                                    </div>
                                </td>

                                <!-- Change -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">

                                        <div class="flex flex-col items-end">

                                            <TrendIcon :current="record.price" :previous="record.previous_price" />
                                            <span v-if="record.change_percentage"
                                                :class="record.trend === 'up' ? 'text-emerald-500' : 'text-rose-500'"
                                                class="text-[8px]">
                                                ({{ Math.abs(parseFloat(record.change_percentage)).toFixed(2) }}%)
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- World Gold USD -->
                                <td class="px-6 py-4 text-right hidden md:table-cell">
                                    <div class="flex flex-col items-end">
                                        <span class="text-slate-600 font-mono font-bold text-xs">
                                            {{ record.world_gold_usd ? '$' + $formatNumber(record.world_gold_usd) : '—'
                                            }}
                                        </span>

                                        <div v-if="record.world_gold_change !== null"
                                            class="flex items-center gap-1 mt-0.5">
                                            <!-- Trend Icon with proper current and previous values -->
                                            <TrendIcon :current="record.world_gold_change" :previous="0"
                                                class="scale-75" />



                                            <!-- Percent change with parentheses -->
                                            <!-- <span v-if="record.world_gold_change_percent"
                                                class="text-[8px] text-slate-400">
                                                ({{ Math.abs(record.world_gold_change_percent).toFixed(2) }}%)
                                            </span> -->
                                        </div>
                                    </div>
                                </td>

                                <!-- Source -->
                                <td class="px-6 py-4 text-center hidden sm:table-cell">
                                    <span v-if="record.source_type === 'auto'"
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-full text-[8px] font-black uppercase">
                                        <div class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></div>
                                        Auto
                                    </span>
                                    <span v-else
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-full text-[8px] font-black uppercase">
                                        <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        Manual
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 text-center hidden lg:table-cell">
                                    <span :class="record.status === 'verified'
                                        ? 'bg-emerald-50 border-emerald-100 text-emerald-700'
                                        : 'bg-amber-50 border-amber-100 text-amber-700'"
                                        class="inline-block px-2 py-1 border rounded-full text-[8px] font-black uppercase">
                                        {{ record.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="!history.data?.length" class="py-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">No price history available yet</p>
                    <p class="text-[10px] text-slate-300 mt-1">Data will appear after first price sync</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="history.links && history.links.length > 3" class="border-t border-slate-100 pt-6">
                <Paginations :links="history.links" />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';


const props = defineProps({
    goldType: Object,
    history: Object,
    chartData: Array,
    todayPrice: Object,
    yesterdayPrice: Object,
    stats: Object,
    latestSnapshot: Object,
    usdRate: Object,
});

const breadcrumbs = [
    { label: 'Gold Prices', href: route('gold.index') },
    { label: props.goldType?.name || 'Gold History' }
];

const priceChange = computed(() => {
    if (!props.todayPrice || !props.yesterdayPrice) return null;
    const diff = parseFloat(props.todayPrice.price) - parseFloat(props.yesterdayPrice.price);
    const pct = ((diff / parseFloat(props.yesterdayPrice.price)) * 100).toFixed(2);
    return { amount: diff, percent: pct, isUp: diff >= 0 };
});

const floatVal = (v) => parseFloat(v) || 0;

onMounted(() => {
    if (window.Echo) {
        window.Echo.channel('gold-markets')
            .listen('.GoldPriceUpdated', (e) => {
                console.log('Gold price updated:', e);
                router.reload({ only: ['todayPrice', 'history', 'stats', 'chartData'] });
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('gold-markets');
    }
});
</script>

<style scoped>
@keyframes shine {
    100% {
        transform: translateX(100%);
    }
}

.animate-shine {
    animation: shine 3s infinite;
}

.group:hover .pause {
    animation-play-state: paused;
}
</style>