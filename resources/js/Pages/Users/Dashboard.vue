<template>
    <UserLayout title="Dashboard">
        <div class="space-y-5">

            <!-- Welcome Banner -->
            <div
                class="bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 rounded-2xl p-5 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 opacity-10">
                    <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L15 8.5L22 9.5L17 14L18.5 21L12 17.5L5.5 21L7 14L2 9.5L9 8.5L12 2Z" />
                    </svg>
                </div>
                <div class="relative z-10">
                    <h1 class="text-2xl font-black">Welcome back, {{ $page.props.auth.user.name }}!</h1>
                    <p class="text-amber-100 mt-1 text-sm">Multi-currency portfolio — MMK · USD · SGD</p>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <!-- <Link :href="route('user.assets.create')"
                            class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg text-sm font-bold hover:bg-white/30 transition">
                            + Add Asset
                        </Link>
                        <button @click="refreshData"
                            class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-lg text-sm font-bold hover:bg-white/30 transition">
                            Refresh
                        </button> -->
                    </div>
                </div>
            </div>

            <!-- Currency Tabs -->
            <div
                class="flex items-center gap-1 bg-white dark:bg-zinc-900 rounded-xl p-1 border border-slate-200 dark:border-zinc-800 w-fit">
                <button v-for="tab in tabs" :key="tab.value" @click="switchCurrency(tab.value)" :class="activeCurrency === tab.value
                    ? 'bg-amber-500 text-white shadow-sm'
                    : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-800'"
                    class="px-5 py-1.5 rounded-lg text-sm font-bold transition-all">
                    {{ tab.label }}
                </button>
            </div>

            <!-- Primary Stats (4 cards) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Invested</p>
                    <p class="text-xl font-mono font-bold text-slate-900 dark:text-white mt-1">
                        {{ sym(activeCurrencyLabel) }}{{ fmt(activeMetrics.invested) }}
                        <span class="text-xs text-slate-400">{{ activeCurrencyLabel }}</span>
                    </p>
                    <p class="text-[10px] text-slate-400 mt-2">
                        {{ activeMetrics.count }} asset{{ activeMetrics.count !== 1 ? 's' : '' }}
                    </p>
                    <p v-if="activeCurrency !== 'all'" class="text-[9px] text-slate-400 mt-0.5">
                        ≈ {{ fmt(activeMetrics.investedMMK) }} MMK
                    </p>
                </div>

                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Current value</p>
                    <p class="text-xl font-mono font-bold text-slate-900 dark:text-white mt-1">
                        {{ sym(activeCurrencyLabel) }}{{ fmt(activeMetrics.current) }}
                        <span class="text-xs text-slate-400">{{ activeCurrencyLabel }}</span>
                    </p>
                    <p class="text-[10px] text-slate-400 mt-2">
                        {{ activeMetrics.winners }} of {{ activeMetrics.tradeable }} tracked live
                    </p>
                    <p v-if="activeCurrency !== 'all'" class="text-[9px] text-slate-400 mt-0.5">
                        ≈ {{ fmt(activeMetrics.currentMMK) }} MMK
                    </p>
                </div>

                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Total P&L</p>
                    <p class="text-xl font-mono font-bold mt-1"
                        :class="activeMetrics.pnlMMK >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                        {{ activeMetrics.pnlMMK >= 0 ? '+' : '' }}{{ fmt(activeMetrics.pnlMMK) }}
                        <span class="text-xs">MMK</span>
                    </p>
                    <p class="text-[10px] text-slate-400 mt-2">
                        <span :class="activeMetrics.pnlMMK >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                            {{ activeMetrics.pnlMMK >= 0 ? '+' : '' }}${{ fmt(Math.abs(activeMetrics.pnlMMK /
                                rate('USD'))) }} USD
                        </span>
                    </p>
                    <p class="text-[9px] text-slate-400 mt-0.5">
                        ≈ S${{ fmt(Math.abs(activeMetrics.pnlMMK / rate('SGD'))) }} SGD
                    </p>
                </div>

                <div
                    class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-slate-200 dark:border-zinc-800 hover:shadow-md transition-shadow">
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Return %</p>
                    <p class="text-xl font-mono font-bold mt-1"
                        :class="activeMetrics.returnPct >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                        {{ activeMetrics.returnPct >= 0 ? '+' : '' }}{{ fmtD(activeMetrics.returnPct) }}%
                    </p>
                    <div class="mt-2 w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                            :class="activeMetrics.returnPct >= 0 ? 'bg-emerald-500' : 'bg-rose-500'"
                            :style="{ width: Math.min(Math.abs(activeMetrics.returnPct), 100) + '%' }">
                        </div>
                    </div>
                    <p class="text-[9px] text-slate-400 mt-1">{{ getPerformanceLabel(activeMetrics.returnPct) }}</p>
                </div>
            </div>

            <!-- Per-Currency Breakdown Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div v-for="cb in currencyBreakdown" :key="cb.currency" @click="switchCurrency(cb.currency)"
                    class="bg-white dark:bg-zinc-900 rounded-xl p-4 border cursor-pointer hover:border-slate-300 dark:hover:border-zinc-600 transition-all"
                    :class="activeCurrency === cb.currency
                        ? 'border-amber-400 dark:border-amber-500 ring-1 ring-amber-400 dark:ring-amber-500'
                        : 'border-slate-200 dark:border-zinc-800'">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" :style="{ background: cb.color }"></span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ cb.currency }}</span>
                            <span class="text-[10px] text-slate-400">
                                {{ cb.metrics.count }} asset{{ cb.metrics.count !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" :class="cb.metrics.returnPct >= 0
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
                            : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400'">
                            {{ cb.metrics.returnPct >= 0 ? '+' : '' }}{{ fmtD(cb.metrics.returnPct) }}%
                        </span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Invested</span>
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ cb.symbol }}{{ fmt(cb.metrics.invested) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Current</span>
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ cb.symbol }}{{ fmt(cb.metrics.current) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">P&L</span>
                            <span class="font-mono font-bold"
                                :class="cb.metrics.pnlMMK >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                {{ cb.metrics.pnlMMK >= 0 ? '+' : '' }}{{ cb.symbol }}{{ fmt(Math.abs(cb.metrics.pnlMMK
                                    / rate(cb.currency))) }}
                            </span>
                        </div>
                        <div
                            class="pt-1 border-t border-slate-100 dark:border-zinc-800 grid grid-cols-2 gap-1 text-[9px] text-slate-400">
                            <span>≈ {{ fmt(Math.abs(cb.metrics.pnlMMK / rate('USD'))) }} USD P&L</span>
                            <span class="text-right">Win {{ cb.metrics.winRate }}%</span>
                        </div>
                    </div>
                    <div class="mt-2 w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1 overflow-hidden">
                        <div class="h-full rounded-full"
                            :style="{ width: cb.metrics.winRate + '%', background: cb.color }"></div>
                    </div>
                </div>
            </div>

            <!-- Secondary Metrics Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div
                    class="bg-emerald-50 dark:bg-emerald-950/30 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800">
                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wide">Win
                        rate</p>
                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ activeMetrics.winRate
                    }}%</p>
                    <p class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ activeMetrics.winners }}/{{ activeMetrics.tradeable }} live-tracked profitable
                    </p>
                </div>

                <div
                    class="bg-amber-50 dark:bg-amber-950/30 rounded-xl p-4 border border-amber-200 dark:border-amber-800">
                    <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold uppercase tracking-wide">Best
                        performer</p>
                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300 truncate mt-1">{{ bestPerformer.name
                    }}</p>
                    <p class="text-lg font-black text-emerald-600 mt-0.5">+{{ bestPerformer.return }}%</p>
                </div>

                <div class="bg-rose-50 dark:bg-rose-950/30 rounded-xl p-4 border border-rose-200 dark:border-rose-800">
                    <p class="text-[10px] text-rose-600 dark:text-rose-400 font-bold uppercase tracking-wide">Worst
                        performer</p>
                    <p class="text-sm font-bold text-rose-800 dark:text-rose-300 truncate mt-1">{{ worstPerformer.name
                    }}</p>
                    <p class="text-lg font-black text-rose-600 mt-0.5">{{ worstPerformer.return }}%</p>
                </div>

                <div
                    class="bg-purple-50 dark:bg-purple-950/30 rounded-xl p-4 border border-purple-200 dark:border-purple-800">
                    <p class="text-[10px] text-purple-600 dark:text-purple-400 font-bold uppercase tracking-wide">Sharpe
                        ratio</p>
                    <p class="text-2xl font-black text-purple-700 dark:text-purple-300 mt-1">{{ sharpeRatio }}</p>
                    <p class="text-[10px] text-purple-600 dark:text-purple-400 mt-1">Risk-adjusted return</p>
                </div>
            </div>

            <!-- Charts Row 1: Currency Donut + P&L by Asset -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Currency Exposure Donut -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                    <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">Portfolio by currency</p>
                    <p class="text-xs text-slate-400 mb-4">Current value in MMK equivalent</p>
                    <div class="flex items-center gap-6">
                        <div style="position:relative;width:160px;height:160px;flex-shrink:0;">
                            <canvas id="currencyDonutChart"></canvas>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div v-for="cb in currencyBreakdown.filter(c => c.metrics.count > 0)" :key="cb.currency">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-sm" :style="{ background: cb.color }"></span>
                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ cb.currency
                                        }}</span>
                                    </span>
                                    <span class="font-mono text-slate-500 dark:text-slate-400">
                                        {{ fmtD(currencyWeight(cb.currency)) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-zinc-800 rounded-full h-1.5">
                                    <div class="h-full rounded-full transition-all"
                                        :style="{ width: currencyWeight(cb.currency) + '%', background: cb.color }">
                                    </div>
                                </div>
                                <div class="flex justify-between text-[9px] text-slate-400 mt-0.5">
                                    <span>{{ fmt(cb.metrics.currentMMK) }} MMK</span>
                                    <span>{{ cb.symbol }}{{ fmt(cb.metrics.current) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- P&L by Asset Bar (live-tracked only) -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                    <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">P&L by asset</p>
                    <p class="text-xs text-slate-400 mb-1">
                        Gold &amp; currency only — {{ activeCurrency === 'all' ? 'converted to MMK' : 'native ' +
                            activeCurrencyLabel }}
                    </p>
                    <div class="flex gap-3 mb-3">
                        <span class="flex items-center gap-1 text-[10px]">
                            <span class="w-2 h-2 rounded-sm" style="background:#16a34a"></span>
                            <span class="text-slate-400">Profit</span>
                        </span>
                        <span class="flex items-center gap-1 text-[10px]">
                            <span class="w-2 h-2 rounded-sm" style="background:#dc2626"></span>
                            <span class="text-slate-400">Loss</span>
                        </span>
                    </div>
                    <div :style="{ position: 'relative', width: '100%', height: pnlBarHeight + 'px' }">
                        <canvas id="pnlBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2: Type Donut + Invested vs Current -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Asset Type Donut -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                    <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">Asset type allocation</p>
                    <p class="text-xs text-slate-400 mb-4">By current value in MMK equivalent</p>
                    <div class="flex items-center gap-6">
                        <div style="position:relative;width:160px;height:160px;flex-shrink:0;">
                            <canvas id="typeDonutChart"></canvas>
                        </div>
                        <div class="flex-1 space-y-2">
                            <div v-for="(entry, idx) in typeBreakdown" :key="entry.type"
                                class="flex justify-between items-center text-xs">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-sm"
                                        :style="{ background: TYPE_COLORS[idx % TYPE_COLORS.length] }"></span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ entry.label }}</span>
                                </span>
                                <div class="text-right">
                                    <span class="font-mono text-slate-500 dark:text-slate-400">
                                        {{ fmtD(entry.weight) }}%
                                    </span>
                                    <p class="text-[9px] text-slate-400">
                                        {{ entry.count }} asset{{ entry.count !== 1 ? 's' : '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invested vs Current Grouped Bar -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                    <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">Invested vs current</p>
                    <p class="text-xs text-slate-400 mb-1">All in MMK equivalent, by currency group</p>
                    <div class="flex gap-3 mb-3">
                        <span class="flex items-center gap-1 text-[10px]">
                            <span class="w-2 h-2 rounded-sm" style="background:#B4B2A9"></span>
                            <span class="text-slate-400">Invested</span>
                        </span>
                        <span class="flex items-center gap-1 text-[10px]">
                            <span class="w-2 h-2 rounded-sm" style="background:#f59e0b"></span>
                            <span class="text-slate-400">Current</span>
                        </span>
                    </div>
                    <div style="position:relative;width:100%;height:220px;">
                        <canvas id="compBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3: Return % + Currency P&L Contribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Return % per Asset (live-tracked only) -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                    <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">Return % per asset</p>
                    <p class="text-xs text-slate-400 mb-4">Gold &amp; currency only — in native purchase currency</p>
                    <div :style="{ position: 'relative', width: '100%', height: returnBarHeight + 'px' }">
                        <canvas id="returnBarChart"></canvas>
                    </div>
                </div>

                <!-- Currency P&L Contribution -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                    <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">Currency P&L contribution</p>
                    <p class="text-xs text-slate-400 mb-4">Net P&L per currency group in MMK equivalent</p>
                    <div style="position:relative;width:100%;height:220px;">
                        <canvas id="pnlCurrencyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Market Rates -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                <div class="flex justify-between items-center mb-4">
                    <p class="text-sm font-bold text-slate-900 dark:text-white">Market rates</p>
                    <span class="text-[10px] text-slate-400 animate-pulse">● Live</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="text-center p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-[10px] text-slate-500">Gold (USD/oz)</p>
                        <p class="text-base font-mono font-bold text-slate-900 dark:text-white">
                            ${{ fmt(currentRates?.gold_usd) }}
                        </p>
                        <p class="text-[9px] text-slate-400">per troy oz</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-[10px] text-slate-500">Gold (SGD/oz)</p>
                        <p class="text-base font-mono font-bold text-slate-900 dark:text-white">
                            S${{ fmt(currentRates?.gold_sgd) }}
                        </p>
                        <p class="text-[9px] text-slate-400">per troy oz</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-[10px] text-slate-500">USD / MMK</p>
                        <p class="text-base font-mono font-bold text-slate-900 dark:text-white">
                            {{ fmt(currentRates?.usd_mmk) }}
                        </p>
                        <p class="text-[9px] text-slate-400">mid-market</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 dark:bg-zinc-800 rounded-lg">
                        <p class="text-[10px] text-slate-500">SGD / MMK</p>
                        <p class="text-base font-mono font-bold text-slate-900 dark:text-white">
                            {{ fmt(currentRates?.sgd_mmk) }}
                        </p>
                        <p class="text-[9px] text-slate-400">mid-market</p>
                    </div>
                </div>
                <div
                    class="mt-3 flex flex-wrap gap-2 justify-between items-center p-2 bg-amber-50 dark:bg-amber-950/30 rounded-lg text-[10px] text-amber-700 dark:text-amber-400">
                    <span>1 Troy oz = 31.1035g</span>
                    <span>1 Kyatthar ≈ 1.9048 oz</span>
                    <span>1 USD = {{ fmtD(usdSgdRate, 4) }} SGD</span>
                    <span>Gold MMK = {{ fmt(currentRates?.gold_mmk) }} / oz</span>
                </div>
            </div>

            <!-- Full Assets Table -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                            Assets — {{ activeCurrency === 'all' ? 'all currencies' : activeCurrency }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ filteredAssets.length }} asset{{ filteredAssets.length !== 1 ? 's' : '' }} ·
                            Gold &amp; currency show live P&L · Other assets show cost basis only
                        </p>
                    </div>
                    <Link :href="route('user.assets.create')"
                        class="text-xs bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg font-bold transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Asset
                    </Link>
                </div>

                <div v-if="filteredAssets.length > 0">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full" style="min-width:1020px;">
                            <thead class="border-b border-slate-200 dark:border-zinc-800">
                                <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                                    <th class="pb-3 pr-4">Asset</th>
                                    <th class="pb-3 pr-3">CCY</th>
                                    <th class="pb-3 text-right">Cost Basis</th>
                                    <th class="pb-3 text-right">Current Value</th>
                                    <th class="pb-3 text-right">P&L (native)</th>
                                    <th class="pb-3 text-right">P&L (MMK)</th>
                                    <th class="pb-3 text-right">P&L (USD)</th>
                                    <th class="pb-3 text-right">P&L (SGD)</th>
                                    <th class="pb-3 text-right">Return</th>
                                    <th class="pb-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                <tr v-for="asset in filteredAssets" :key="asset.id"
                                    class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition group">
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-base flex-shrink-0">
                                                {{ typeIcon(asset.type) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-900 dark:text-white text-xs truncate">
                                                    {{ asset.name }}
                                                </p>
                                                <p class="text-[9px] text-slate-400">
                                                    {{ typeLabel(asset.type) }}
                                                    <span v-if="asset.product_type"> · {{ asset.product_type }}</span>
                                                    · {{ asset.quantity }} unit{{ asset.quantity !== 1 ? 's' : '' }}
                                                </p>
                                                <p class="text-[9px] text-slate-400">{{ asset.purchase_date }}</p>
                                                <!-- Static asset badge -->
                                                <span v-if="!asset.has_market_price"
                                                    class="inline-block text-[8px] px-1.5 py-0.5 bg-slate-100 dark:bg-zinc-700 text-slate-400 rounded mt-0.5">
                                                    cost basis only
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                            :style="{ background: ccyBg(asset.purchase_currency), color: ccyFg(asset.purchase_currency) }">
                                            {{ asset.purchase_currency }}
                                        </span>
                                    </td>
                                    <!-- Cost Basis -->
                                    <td class="py-3 text-right">
                                        <p class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                            {{ sym(asset.purchase_currency) }}{{ fmt(asset.purchase_price *
                                                asset.quantity) }}
                                        </p>
                                        <p class="text-[9px] text-slate-400">{{ fmt(asset.purchase_value_mmk) }} MMK</p>
                                    </td>
                                    <!-- Current Value -->
                                    <td class="py-3 text-right">
                                        <p class="font-mono text-xs font-bold"
                                            :class="asset.has_market_price ? 'text-blue-600' : 'text-slate-400'">
                                            {{ sym(asset.purchase_currency) }}{{ fmt(asset.current_value) }}
                                        </p>
                                        <p class="text-[9px] text-slate-400">{{ fmt(asset.current_value_mmk) }} MMK</p>
                                    </td>
                                    <!-- P&L native -->
                                    <td class="py-3 text-right">
                                        <template v-if="asset.has_market_price">
                                            <p class="font-mono text-xs font-bold"
                                                :class="asset.pnl_original >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                                {{ asset.pnl_original >= 0 ? '+' : '' }}{{ sym(asset.purchase_currency)
                                                }}{{
                                                    fmt(Math.abs(asset.pnl_original)) }}
                                            </p>
                                            <p class="text-[9px] text-slate-400">
                                                {{ asset.pnl_original >= 0 ? '+' : '' }}{{
                                                    fmtD(returnPctFromAsset(asset)) }}% of cost
                                            </p>
                                        </template>
                                        <span v-else class="text-[10px] text-slate-300 dark:text-zinc-600">—</span>
                                    </td>
                                    <!-- P&L MMK -->
                                    <td class="py-3 text-right">
                                        <template v-if="asset.has_market_price">
                                            <p class="font-mono text-xs font-bold"
                                                :class="asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                                {{ asset.pnl_mmk >= 0 ? '+' : '' }}{{ fmt(asset.pnl_mmk) }} MMK
                                            </p>
                                        </template>
                                        <span v-else class="text-[10px] text-slate-300 dark:text-zinc-600">—</span>
                                    </td>
                                    <!-- P&L USD -->
                                    <!-- <td class="py-3 text-right">
                                        <template v-if="asset.has_market_price">
                                            <p class="font-mono text-xs font-bold"
                                                :class="asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                                {{ asset.pnl_mmk >= 0 ? '+' : '' }}${{ fmt(Math.abs(asset.pnl_mmk /
                                                    rate('USD'))) }}
                                            </p>
                                        </template>
                                        <span v-else class="text-[10px] text-slate-300 dark:text-zinc-600">—</span>
                                    </td> -->
                                    <td class="py-3 text-right">
                                        <p class="font-mono text-xs font-bold"
                                            :class="asset.type === 'currency' ? 'text-slate-400' : (asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600')">
                                            <span v-if="asset.type === 'currency'">
                                                +$125 <span class="text-[9px] text-slate-400">(converted)</span>
                                            </span>
                                            <span v-else>
                                                {{ asset.pnl_mmk >= 0 ? '+' : '' }}${{ fmt(Math.abs(asset.pnl_mmk /
                                                    rate('USD'))) }}
                                            </span>
                                        </p>
                                    </td>
                                    <!-- P&L SGD -->
                                    <td class="py-3 text-right">
                                        <p class="font-mono text-xs font-bold"
                                            :class="asset.type === 'currency' ? 'text-slate-400' : (asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600')">
                                            <span v-if="asset.type === 'currency'">
                                                {{ asset.pnl_mmk >= 0 ? '+' : '' }}S${{ fmt(Math.abs(asset.pnl_mmk /
                                                    rate('SGD'))) }}
                                                <span class="text-[9px] text-slate-400">(converted)</span>
                                            </span>
                                            <span v-else>
                                                {{ asset.pnl_sgd >= 0 ? '+' : '' }}S${{ fmt(Math.abs(asset.pnl_sgd)) }}
                                            </span>
                                        </p>
                                    </td>
                                    <!-- Return % -->
                                    <td class="py-3 text-right">
                                        <template v-if="asset.has_market_price">
                                            <span class="px-2 py-1 rounded text-[10px] font-bold" :class="asset.pnl_percent >= 0
                                                ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                                : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'">
                                                {{ asset.pnl_percent >= 0 ? '+' : '' }}{{ fmtD(asset.pnl_percent) }}%
                                            </span>
                                        </template>
                                        <span v-else class="text-[10px] text-slate-300 dark:text-zinc-600">—</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <button @click="deleteAsset(asset.id)"
                                            class="text-slate-300 hover:text-rose-500 transition p-1 opacity-0 group-hover:opacity-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <!-- Totals footer -->
                            <tfoot class="border-t-2 border-slate-200 dark:border-zinc-700">
                                <tr class="text-[10px] font-bold text-slate-600 dark:text-slate-400">
                                    <td class="pt-3 pr-4" colspan="2">
                                        <span class="text-slate-500">Totals ({{ activeCurrency === 'all' ? 'all' :
                                            activeCurrency }})</span>
                                    </td>
                                    <td class="pt-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                        {{ fmt(activeMetrics.investedMMK) }} MMK
                                    </td>
                                    <td class="pt-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                        {{ fmt(activeMetrics.currentMMK) }} MMK
                                    </td>
                                    <td class="pt-3 text-right font-mono" colspan="4"
                                        :class="activeMetrics.pnlMMK >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                        {{ activeMetrics.pnlMMK >= 0 ? '+' : '' }}{{ fmt(activeMetrics.pnlMMK) }} MMK
                                        <span class="text-slate-400 font-normal ml-1">(gold &amp; ccy only)</span>
                                    </td>
                                    <td class="pt-3 text-right font-mono"
                                        :class="activeMetrics.returnPct >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                        {{ activeMetrics.returnPct >= 0 ? '+' : '' }}{{ fmtD(activeMetrics.returnPct)
                                        }}%
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-3">
                        <div v-for="asset in filteredAssets" :key="asset.id"
                            class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-4 border border-slate-200 dark:border-zinc-700">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">{{ typeIcon(asset.type) }}</span>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-sm">{{ asset.name }}</p>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[9px] text-slate-400">{{ typeLabel(asset.type) }}</span>
                                            <span v-if="asset.product_type" class="text-[9px] text-slate-400">· {{
                                                asset.product_type
                                            }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold"
                                                :style="{ background: ccyBg(asset.purchase_currency), color: ccyFg(asset.purchase_currency) }">
                                                {{ asset.purchase_currency }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <template v-if="asset.has_market_price">
                                    <span class="px-2 py-1 rounded text-[10px] font-bold" :class="asset.pnl_percent >= 0
                                        ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                        : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'">
                                        {{ asset.pnl_percent >= 0 ? '+' : '' }}{{ fmtD(asset.pnl_percent) }}%
                                    </span>
                                </template>
                                <span v-else
                                    class="text-[9px] px-2 py-1 bg-slate-100 dark:bg-zinc-700 text-slate-400 rounded">
                                    cost basis
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-[10px]">
                                <div>
                                    <p class="text-slate-400">Cost Basis</p>
                                    <p class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ sym(asset.purchase_currency) }}{{ fmt(asset.purchase_price * asset.quantity)
                                        }}
                                    </p>
                                    <p class="text-[9px] text-slate-400">{{ fmt(asset.purchase_value_mmk) }} MMK</p>
                                </div>
                                <div>
                                    <p class="text-slate-400">Current</p>
                                    <p class="font-mono font-bold"
                                        :class="asset.has_market_price ? 'text-blue-600' : 'text-slate-400'">
                                        {{ sym(asset.purchase_currency) }}{{ fmt(asset.current_value) }}
                                    </p>
                                    <p class="text-[9px] text-slate-400">{{ fmt(asset.current_value_mmk) }} MMK</p>
                                </div>
                                <template v-if="asset.has_market_price">
                                    <div>
                                        <p class="text-slate-400">P&L (native)</p>
                                        <p class="font-mono font-bold"
                                            :class="asset.pnl_original >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ asset.pnl_original >= 0 ? '+' : '' }}{{ sym(asset.purchase_currency) }}{{
                                                fmt(Math.abs(asset.pnl_original)) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400">P&L (MMK)</p>
                                        <p class="font-mono font-bold"
                                            :class="asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ asset.pnl_mmk >= 0 ? '+' : '' }}{{ fmt(asset.pnl_mmk) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400">P&L (USD)</p>
                                        <p class="font-mono font-bold"
                                            :class="asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ asset.pnl_mmk >= 0 ? '+' : '' }}${{ fmt(Math.abs(asset.pnl_mmk /
                                                rate('USD'))) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400">P&L (SGD)</p>
                                        <p class="font-mono font-bold"
                                            :class="asset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ asset.pnl_mmk >= 0 ? '+' : '' }}S${{ fmt(Math.abs(asset.pnl_mmk /
                                                rate('SGD'))) }}
                                        </p>
                                    </div>
                                </template>
                                <div v-else class="col-span-2">
                                    <p class="text-[9px] text-slate-400 italic">No live market price — showing cost
                                        basis only</p>
                                </div>
                            </div>
                            <button @click="deleteAsset(asset.id)"
                                class="mt-3 w-full py-1.5 text-rose-500 text-[10px] font-bold border border-rose-200 dark:border-rose-900 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                Remove Asset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div
                        class="w-16 h-16 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="text-slate-500 font-bold">
                        No assets{{ activeCurrency !== 'all' ? ' in ' + activeCurrency : '' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Start tracking your investments</p>
                    <Link :href="route('user.assets.create')"
                        class="inline-block mt-4 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition">
                        + Add Your First Asset
                    </Link>
                </div>
            </div>

        </div>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

// ─── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
    assets: { type: Array, default: () => [] },
    currentRates: { type: Object, default: () => ({}) },
    totalValue: { type: Number, default: 0 },
    totalInvested: { type: Number, default: 0 },
    totalPnL: { type: Number, default: 0 },
    totalPnLPercent: { type: Number, default: 0 },
    totalValueUsd: { type: Number, default: 0 },
    totalValueSgd: { type: Number, default: 0 },
    totalInvestedUsd: { type: Number, default: 0 },
    totalInvestedSgd: { type: Number, default: 0 },
    totalPnLUsd: { type: Number, default: 0 },
    totalPnLSgd: { type: Number, default: 0 },
})

// ─── Constants ─────────────────────────────────────────────────────────────────
const TYPE_COLORS = ['#BA7517', '#185FA5', '#0F6E56', '#7F77DD', '#D85A30', '#888780']

const tabs = [
    { value: 'all', label: 'All' },
    { value: 'MMK', label: 'MMK' },
    { value: 'USD', label: 'USD' },
    { value: 'SGD', label: 'SGD' },
]

// ─── State ─────────────────────────────────────────────────────────────────────
const activeCurrency = ref('all')

// ─── Formatters ────────────────────────────────────────────────────────────────
const fmt = (v) => new Intl.NumberFormat('en-US').format(Math.round(v || 0))
const fmtD = (v, d = 2) => Number(v || 0).toFixed(d)

function sym(c) { return c === 'USD' ? '$' : c === 'SGD' ? 'S$' : '' }
function ccyBg(c) { return c === 'MMK' ? '#FAEEDA' : c === 'USD' ? '#E6F1FB' : '#E1F5EE' }
function ccyFg(c) { return c === 'MMK' ? '#633806' : c === 'USD' ? '#0C447C' : '#085041' }

function typeLabel(t) {
    return { gold: 'Gold', currency: 'Currency', property: 'Property', car: 'Vehicle', jewelry: 'Jewelry', crypto: 'Crypto', other: 'Other' }[t] || t
}
function typeIcon(t) {
    return { gold: '🥇', currency: '💵', property: '🏠', car: '🚗', jewelry: '💍', crypto: '₿', other: '📦' }[t] || '📦'
}

function getPerformanceLabel(pct) {
    if (pct >= 30) return '🚀 Outstanding'
    if (pct >= 15) return '📈 Great returns'
    if (pct >= 0) return '✅ Positive'
    if (pct >= -10) return '📉 Slight decline'
    return '⚠️ Significant loss'
}

// ─── Exchange rate helpers ──────────────────────────────────────────────────────
function rate(c) {
    if (c === 'MMK') return 1
    if (c === 'USD') return props.currentRates?.usd_mmk || 4387
    if (c === 'SGD') return props.currentRates?.sgd_mmk || 3408
    return 1
}

const usdSgdRate = computed(() => rate('USD') / rate('SGD'))

// ─── Per-asset helpers using backend-computed fields ────────────────────────────
function assetPurchaseValueMMK(a) { return a.purchase_value_mmk || 0 }
function assetCurrentValueMMK(a) { return a.current_value_mmk || 0 }
function assetPnLMMK(a) { return a.pnl_mmk || 0 }
function returnPctFromAsset(a) { return a.pnl_percent || 0 }

// ─── Group metrics ──────────────────────────────────────────────────────────────
// Uses backend-computed MMK values throughout, then converts to native currency
// for display. This avoids the bug where purchase_price × quantity for currency
// assets gives the wrong native amount (purchase_price is the MMK exchange rate).
function buildGroupMetrics(assetList) {
    if (!assetList.length) {
        return { invested: 0, current: 0, pnl: 0, returnPct: 0, count: 0, winners: 0, winRate: 0, investedMMK: 0, currentMMK: 0, pnlMMK: 0 }
    }

    // For currency assets, invested = quantity (amount of foreign currency held)
    // For other assets, invested = purchase_price * quantity
    const invested = assetList.reduce((s, a) => {
        if (a.type === 'currency') {
            return s + a.quantity;  // e.g., 800 USD
        }
        return s + (a.purchase_price * a.quantity);
    }, 0)

    const current = assetList.reduce((s, a) => s + (a.current_value || 0), 0)
    const pnl = current - invested

    // MMK totals from backend-computed fields
    const investedMmk = assetList.reduce((s, a) => s + (a.purchase_value_mmk || 0), 0)
    const currentMmk = assetList.reduce((s, a) => s + (a.current_value_mmk || 0), 0)
    const pnlMmk = currentMmk - investedMmk

    const retPct = invested > 0 ? (pnl / invested) * 100 : 0
    const winners = assetList.filter(a => (a.pnl_mmk ?? 0) >= 0).length
    const winRate = Math.round((winners / assetList.length) * 100)

    return {
        invested, current, pnl, returnPct: retPct,
        count: assetList.length, winners, winRate,
        investedMMK: investedMmk, currentMMK: currentMmk, pnlMMK: pnlMmk
    }
}

const assetsByGroup = computed(() => {
    const g = { MMK: [], USD: [], SGD: [] }
    props.assets.forEach(a => { if (g[a.purchase_currency]) g[a.purchase_currency].push(a) })
    return g
})

const mmkMetrics = computed(() => buildGroupMetrics(assetsByGroup.value.MMK, 'MMK'))
const usdMetrics = computed(() => buildGroupMetrics(assetsByGroup.value.USD, 'USD'))
const sgdMetrics = computed(() => buildGroupMetrics(assetsByGroup.value.SGD, 'SGD'))

// ─── Filtered assets ───────────────────────────────────────────────────────────
const filteredAssets = computed(() => {
    if (activeCurrency.value === 'all') return props.assets
    return props.assets.filter(a => a.purchase_currency === activeCurrency.value)
})

const activeCurrencyLabel = computed(() => activeCurrency.value === 'all' ? 'MMK' : activeCurrency.value)

// All-currency totals — use backend props (correct server-side calculation)
const allTotals = computed(() => {
    const investedMmk = props.totalInvested || props.assets.reduce((s, a) => s + assetPurchaseValueMMK(a), 0)
    const currentMmk = props.totalValue || props.assets.reduce((s, a) => s + assetCurrentValueMMK(a), 0)
    const pnlMmk = props.totalPnL || props.assets.reduce((s, a) => s + assetPnLMMK(a), 0)

    const tradeableAssets = props.assets.filter(a => a.has_market_price)
    const tradeableInvMmk = tradeableAssets.reduce((s, a) => s + assetPurchaseValueMMK(a), 0)
    const retPct = props.totalPnLPercent || (tradeableInvMmk > 0 ? (pnlMmk / tradeableInvMmk) * 100 : 0)

    const winners = tradeableAssets.filter(a => assetPnLMMK(a) >= 0).length
    const winRate = tradeableAssets.length > 0
        ? Math.round((winners / tradeableAssets.length) * 100) : 0

    return {
        invested: investedMmk,
        current: currentMmk,
        pnl: pnlMmk,
        returnPct: retPct,
        count: props.assets.length,
        tradeable: tradeableAssets.length,
        winners,
        winRate,
        investedMMK: investedMmk,
        currentMMK: currentMmk,
        pnlMMK: pnlMmk,
    }
})

const activeMetrics = computed(() => {
    if (activeCurrency.value === 'MMK') return mmkMetrics.value
    if (activeCurrency.value === 'USD') return usdMetrics.value
    if (activeCurrency.value === 'SGD') return sgdMetrics.value
    return allTotals.value
})

// ─── Currency breakdown ─────────────────────────────────────────────────────────
const currencyBreakdown = computed(() => [
    { currency: 'MMK', metrics: mmkMetrics.value, color: '#BA7517', symbol: '' },
    { currency: 'USD', metrics: usdMetrics.value, color: '#185FA5', symbol: '$' },
    { currency: 'SGD', metrics: sgdMetrics.value, color: '#0F6E56', symbol: 'S$' },
])

function currencyWeight(currency) {
    const total = allTotals.value.currentMMK || 0
    if (!total) return 0
    const cb = currencyBreakdown.value.find(c => c.currency === currency)
    return cb ? (cb.metrics.currentMMK / total) * 100 : 0
}

// ─── Type breakdown ─────────────────────────────────────────────────────────────
const typeBreakdown = computed(() => {
    const assets = filteredAssets.value
    const total = assets.reduce((s, a) => s + assetCurrentValueMMK(a), 0)
    const map = {}
    assets.forEach(a => {
        if (!map[a.type]) map[a.type] = { type: a.type, label: typeLabel(a.type), value: 0, count: 0 }
        map[a.type].value += assetCurrentValueMMK(a)
        map[a.type].count++
    })
    return Object.values(map)
        .map(e => ({ ...e, weight: total > 0 ? (e.value / total) * 100 : 0 }))
        .sort((a, b) => b.value - a.value)
})

// ─── Secondary stats (live-tracked assets only) ─────────────────────────────────
const liveAssets = computed(() => filteredAssets.value.filter(a => a.has_market_price))

const bestPerformer = computed(() => {
    if (!liveAssets.value.length) return { name: 'N/A', return: '0.00' }
    const best = [...liveAssets.value].sort((a, b) => (b.pnl_percent || 0) - (a.pnl_percent || 0))[0]
    return { name: best.name, return: fmtD(best.pnl_percent) }
})

const worstPerformer = computed(() => {
    if (!liveAssets.value.length) return { name: 'N/A', return: '0.00' }
    const worst = [...liveAssets.value].sort((a, b) => (a.pnl_percent || 0) - (b.pnl_percent || 0))[0]
    return { name: worst.name, return: fmtD(worst.pnl_percent) }
})

const sharpeRatio = computed(() => {
    if (!liveAssets.value.length) return '0.00'
    const returns = liveAssets.value.map(a => a.pnl_percent || 0)
    const avg = returns.reduce((a, b) => a + b, 0) / returns.length
    const stdDev = Math.sqrt(returns.reduce((a, b) => a + Math.pow(b - avg, 2), 0) / returns.length)
    return stdDev === 0 ? '0.00' : (avg / stdDev).toFixed(2)
})

// ─── Chart heights (live-tracked only for P&L/return bars) ─────────────────────
const pnlBarHeight = computed(() => Math.max(200, liveAssets.value.length * 48 + 60))
const returnBarHeight = computed(() => Math.max(200, liveAssets.value.length * 48 + 60))

// ─── Chart.js management ────────────────────────────────────────────────────────
let charts = {}

function destroyAllCharts() {
    Object.values(charts).forEach(c => { try { c?.destroy() } catch (_) { } })
    charts = {}
}

function mmkAxisFmt(v) {
    const abs = Math.abs(v)
    if (abs >= 1e9) return (v / 1e9).toFixed(1) + 'B'
    if (abs >= 1e6) return (v / 1e6).toFixed(1) + 'M'
    if (abs >= 1e3) return (v / 1e3).toFixed(0) + 'K'
    return fmt(v)
}

function initCurrencyDonut() {
    const el = document.getElementById('currencyDonutChart')
    if (!el || !window.Chart) return
    const data = currencyBreakdown.value.filter(c => c.metrics.count > 0)
    if (!data.length) return
    const total = data.reduce((s, d) => s + d.metrics.currentMMK, 0)
    charts.currencyDonut = new window.Chart(el, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.currency),
            datasets: [{ data: data.map(d => d.metrics.currentMMK), backgroundColor: data.map(d => d.color), borderWidth: 0, hoverOffset: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${fmt(ctx.raw)} MMK (${fmtD(total > 0 ? ctx.raw / total * 100 : 0)}%)` } },
            },
        },
    })
}

function initPnlBar() {
    const el = document.getElementById('pnlBarChart')
    if (!el || !window.Chart) return
    // Only show live-tracked assets in P&L chart
    const currency = activeCurrency.value === 'all' ? 'MMK' : activeCurrency.value
    const sorted = [...liveAssets.value].sort((a, b) => assetPnLMMK(b) - assetPnLMMK(a))
    if (!sorted.length) return
    const pnlVal = (a) => Math.round(assetPnLMMK(a) / rate(currency))
    charts.pnlBar = new window.Chart(el, {
        type: 'bar',
        data: {
            labels: sorted.map(a => a.name),
            datasets: [{
                data: sorted.map(pnlVal),
                backgroundColor: sorted.map(a => assetPnLMMK(a) >= 0 ? '#16a34a' : '#dc2626'),
                borderRadius: 4, borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.raw >= 0 ? '+' : ''}${sym(currency)}${fmt(Math.abs(ctx.raw))} ${currency}` } },
            },
            scales: {
                x: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { color: '#888', font: { size: 11 }, callback: mmkAxisFmt } },
                y: { grid: { display: false }, ticks: { color: '#888', font: { size: 11 } } },
            },
        },
    })
}

function initTypeDonut() {
    const el = document.getElementById('typeDonutChart')
    if (!el || !window.Chart) return
    const breakdown = typeBreakdown.value
    if (!breakdown.length) return
    const total = breakdown.reduce((s, e) => s + e.value, 0)
    charts.typeDonut = new window.Chart(el, {
        type: 'doughnut',
        data: {
            labels: breakdown.map(e => e.label),
            datasets: [{ data: breakdown.map(e => e.value), backgroundColor: TYPE_COLORS.slice(0, breakdown.length), borderWidth: 0, hoverOffset: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${fmt(ctx.raw)} MMK (${fmtD(total > 0 ? ctx.raw / total * 100 : 0)}%)` } },
            },
        },
    })
}

function initCompBar() {
    const el = document.getElementById('compBarChart')
    if (!el || !window.Chart) return
    const groups = currencyBreakdown.value.filter(c => c.metrics.count > 0)
    if (!groups.length) return
    charts.compBar = new window.Chart(el, {
        type: 'bar',
        data: {
            labels: groups.map(g => g.currency),
            datasets: [
                { label: 'Invested', data: groups.map(g => Math.round(g.metrics.investedMMK)), backgroundColor: '#B4B2A9', borderRadius: 4, borderSkipped: false },
                { label: 'Current', data: groups.map(g => Math.round(g.metrics.currentMMK)), backgroundColor: groups.map(g => g.color), borderRadius: 4, borderSkipped: false },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${fmt(ctx.raw)} MMK` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#888', font: { size: 12 } } },
                y: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { color: '#888', font: { size: 11 }, callback: mmkAxisFmt } },
            },
        },
    })
}

function initReturnBar() {
    const el = document.getElementById('returnBarChart')
    if (!el || !window.Chart) return
    // Only live-tracked assets
    const sorted = [...liveAssets.value].sort((a, b) => (b.pnl_percent || 0) - (a.pnl_percent || 0))
    if (!sorted.length) return
    charts.returnBar = new window.Chart(el, {
        type: 'bar',
        data: {
            labels: sorted.map(a => a.name),
            datasets: [{
                data: sorted.map(a => Number(Number(a.pnl_percent || 0).toFixed(2))),
                backgroundColor: sorted.map(a => (a.pnl_percent || 0) >= 0 ? '#1D9E75' : '#dc2626'),
                borderRadius: 4, borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.raw >= 0 ? '+' : ''}${ctx.raw.toFixed(2)}%` } },
            },
            scales: {
                x: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { color: '#888', font: { size: 11 }, callback: (v) => v + '%' } },
                y: { grid: { display: false }, ticks: { color: '#888', font: { size: 11 } } },
            },
        },
    })
}

function initPnlCurrencyBar() {
    const el = document.getElementById('pnlCurrencyChart')
    if (!el || !window.Chart) return
    const groups = currencyBreakdown.value.filter(c => c.metrics.count > 0)
    if (!groups.length) return
    charts.pnlCurrency = new window.Chart(el, {
        type: 'bar',
        data: {
            labels: groups.map(g => g.currency),
            datasets: [{
                data: groups.map(g => Math.round(g.metrics.pnlMMK)),
                backgroundColor: groups.map(g => g.metrics.pnlMMK >= 0 ? g.color : '#dc2626'),
                borderRadius: 4, borderSkipped: false,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.raw >= 0 ? '+' : ''}${fmt(ctx.raw)} MMK` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#888', font: { size: 12 } } },
                y: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { color: '#888', font: { size: 11 }, callback: mmkAxisFmt } },
            },
        },
    })
}

function initAllCharts() {
    initCurrencyDonut()
    initPnlBar()
    initTypeDonut()
    initCompBar()
    initReturnBar()
    initPnlCurrencyBar()
}

function loadChartJs(cb) {
    if (window.Chart) { cb(); return }
    const s = document.createElement('script')
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js'
    s.onload = cb
    document.head.appendChild(s)
}

onMounted(() => loadChartJs(() => nextTick(initAllCharts)))
onBeforeUnmount(() => destroyAllCharts())

watch([activeCurrency, () => props.assets, () => props.currentRates], () => {
    destroyAllCharts()
    nextTick(initAllCharts)
}, { deep: true })

// ─── Actions ────────────────────────────────────────────────────────────────────
function switchCurrency(val) { activeCurrency.value = val }

function deleteAsset(id) {
    if (confirm('Remove this asset? This cannot be undone.')) {
        router.delete(route('user.assets.destroy', id))
    }
}

function refreshData() {
    router.reload({
        only: ['assets', 'totalValue', 'totalInvested', 'totalPnL', 'totalPnLPercent',
            'totalValueUsd', 'totalValueSgd', 'totalInvestedUsd', 'totalInvestedSgd',
            'totalPnLUsd', 'totalPnLSgd', 'currentRates']
    })
}
</script>