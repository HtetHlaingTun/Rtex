<template>
    <UserLayout title="My Assets Portfolio">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">My Assets Portfolio</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track your assets in MMK,
                        USD, and SGD with
                        real-time exchange rates</p>
                </div>
                <div class="flex gap-2 sm:gap-3">
                    <button @click="toggleView"
                        class="px-3 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 dark:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span class="hidden xs:inline">{{ viewMode === 'table' ? 'Card View' : 'Table View' }}</span>
                    </button>
                    <button @click="refreshData"
                        class="px-3 sm:px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 dark:text-white">
                        <svg class="w-4 h-4" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="xs:inline">Refresh</span>
                    </button>
                    <Link :href="route('user.assets.create')"
                        class="px-3 sm:px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl text-xs sm:text-sm font-bold transition shadow-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden xs:inline">Add Asset</span>
                    </Link>
                </div>
            </div>
        </template>

        <!-- Currency-Specific P&L Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6">
            <!-- MMK P&L Summary -->
            <div
                class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl sm:rounded-2xl p-4 sm:p-5 text-white shadow-lg">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] sm:text-xs opacity-80">Total P&L (MMK)</p>
                        <p class="text-lg sm:text-xl font-black mt-1">{{ currencyPnLSummary.totalPnlMmk >= 0 ? '+' : ''
                            }}{{ formatMoney(currencyPnLSummary.totalPnlMmk) }} MMK</p>
                    </div>
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">
                        🇲🇲
                    </div>
                </div>
                <div class="space-y-1 text-[9px] sm:text-[10px]">
                    <div class="flex justify-between">
                        <span>MMK Assets:</span>
                        <span class="font-mono">{{ currencyPnLSummary.mmkAssets.pnl >= 0 ? '+' : '' }}{{
                            formatMoney(currencyPnLSummary.mmkAssets.pnl) }} MMK</span>
                    </div>
                    <div class="flex justify-between">
                        <span>SGD Assets (converted):</span>
                        <span class="font-mono">{{ currencyPnLSummary.sgdAssets.pnlMmk >= 0 ? '+' : '' }}{{
                            formatMoney(currencyPnLSummary.sgdAssets.pnlMmk) }} MMK</span>
                    </div>
                    <div class="flex justify-between">
                        <span>USD Assets (converted):</span>
                        <span class="font-mono">{{ currencyPnLSummary.usdAssets.pnlMmk >= 0 ? '+' : '' }}{{
                            formatMoney(currencyPnLSummary.usdAssets.pnlMmk) }} MMK</span>
                    </div>
                </div>
            </div>

            <!-- USD P&L Summary -->
            <div
                class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl sm:rounded-2xl p-4 sm:p-5 text-white shadow-lg">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] sm:text-xs opacity-80">Total P&L (USD)</p>
                        <p class="text-lg sm:text-xl font-black mt-1">{{ currencyPnLSummary.totalPnlUsd >= 0 ? '+' : ''
                            }}${{ formatMoney(Math.abs(currencyPnLSummary.totalPnlUsd)) }} USD</p>
                    </div>
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">
                        🇺🇸
                    </div>
                </div>
                <div class="space-y-1 text-[9px] sm:text-[10px]">
                    <div class="flex justify-between">
                        <span>USD Assets:</span>
                        <span class="font-mono">{{ currencyPnLSummary.usdAssets.pnlUsd >= 0 ? '+' : '' }}${{
                            formatMoney(Math.abs(currencyPnLSummary.usdAssets.pnlUsd)) }} USD</span>
                    </div>
                    <div class="flex justify-between">
                        <span>MMK Assets (converted):</span>
                        <span class="font-mono">{{ currencyPnLSummary.mmkAssets.pnlUsd >= 0 ? '+' : '' }}${{
                            formatMoney(Math.abs(currencyPnLSummary.mmkAssets.pnlUsd)) }} USD</span>
                    </div>
                    <div class="flex justify-between">
                        <span>SGD Assets (converted):</span>
                        <span class="font-mono">{{ currencyPnLSummary.sgdAssets.pnlUsd >= 0 ? '+' : '' }}${{
                            formatMoney(Math.abs(currencyPnLSummary.sgdAssets.pnlUsd)) }} USD</span>
                    </div>
                </div>
            </div>

            <!-- SGD P&L Summary -->
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl sm:rounded-2xl p-4 sm:p-5 text-white shadow-lg">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] sm:text-xs opacity-80">Total P&L (SGD)</p>
                        <p class="text-lg sm:text-xl font-black mt-1">{{ currencyPnLSummary.totalPnlSgd >= 0 ? '+' : ''
                            }}S${{ formatMoney(Math.abs(currencyPnLSummary.totalPnlSgd)) }} SGD</p>
                    </div>
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">
                        🇸🇬
                    </div>
                </div>
                <div class="space-y-1 text-[9px] sm:text-[10px]">
                    <div class="flex justify-between">
                        <span>SGD Assets:</span>
                        <span class="font-mono">{{ currencyPnLSummary.sgdAssets.pnlSgd >= 0 ? '+' : '' }}S${{
                            formatMoney(Math.abs(currencyPnLSummary.sgdAssets.pnlSgd)) }} SGD</span>
                    </div>
                    <div class="flex justify-between">
                        <span>MMK Assets (converted):</span>
                        <span class="font-mono">{{ currencyPnLSummary.mmkAssets.pnlSgd >= 0 ? '+' : '' }}S${{
                            formatMoney(Math.abs(currencyPnLSummary.mmkAssets.pnlSgd)) }} SGD</span>
                    </div>
                    <div class="flex justify-between">
                        <span>USD Assets (converted):</span>
                        <span class="font-mono">{{ currencyPnLSummary.usdAssets.pnlSgd >= 0 ? '+' : '' }}S${{
                            formatMoney(Math.abs(currencyPnLSummary.usdAssets.pnlSgd)) }} SGD</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">
            <!-- Total Portfolio Value -->
            <div
                class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl sm:rounded-2xl p-4 sm:p-5 text-white shadow-lg hover:shadow-xl transition-all hover:scale-[1.02]">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-[10px] sm:text-xs opacity-80">Total Portfolio Value</p>
                        <p class="text-base sm:text-lg font-black mt-1">{{ formatMoney(totalPortfolioValue.mmk) }} MMK
                        </p>
                    </div>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-2 text-[9px] sm:text-[10px]">
                    <span class="bg-white/20 px-2 py-0.5 rounded">${{ formatMoney(totalPortfolioValue.usd) }} USD</span>
                    <span class="bg-white/20 px-2 py-0.5 rounded">S${{ formatMoney(totalPortfolioValue.sgd) }}
                        SGD</span>
                </div>
            </div>

            <!-- Total Invested Breakdown -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-all">
                <p class="text-[10px] sm:text-xs text-slate-400 dark:text-slate-500 mb-2">💰 Total Invested</p>
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-[11px] sm:text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-300">🇲🇲 MMK:</span>
                        <span class="font-mono font-bold text-emerald-600">{{ formatMoney(totalInvestedByCurrency.mmk)
                        }} MMK</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] sm:text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-300">🇸🇬 SGD:</span>
                        <span class="font-mono font-bold text-blue-600">{{ formatMoney(totalInvestedByCurrency.sgd) }}
                            SGD</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px] sm:text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-300">🇺🇸 USD:</span>
                        <span class="font-mono font-bold text-purple-600">{{ formatMoney(totalInvestedByCurrency.usd) }}
                            USD</span>
                    </div>
                    <div class="border-t border-slate-200 dark:border-zinc-700 pt-2 mt-1">
                        <div class="flex justify-between items-center text-[10px] sm:text-[11px]">
                            <span class="font-bold text-slate-700 dark:text-slate-300">Total (MMK):</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{
                                formatMoney(totalInvestedByCurrency.totalMmk) }}
                                MMK</span>
                        </div>
                        <div
                            class="flex justify-between text-[8px] sm:text-[9px] text-slate-400 dark:text-slate-500 mt-0.5">
                            <span>≈ ${{ formatMoney(totalInvestedByCurrency.totalUsd) }} USD</span>
                            <span>≈ S${{ formatMoney(totalInvestedByCurrency.totalSgd) }} SGD</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Currency Distribution -->
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-zinc-800 shadow-sm">
                <p class="text-[10px] sm:text-xs text-slate-400 dark:text-slate-500 mb-2">Currency Distribution</p>
                <div class="space-y-2">
                    <div class="flex justify-between text-[10px] sm:text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-300">🇲🇲 MMK</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300">{{ currencyDistribution.mmk
                            }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full"
                            :style="{ width: currencyDistribution.mmk + '%' }">
                        </div>
                    </div>
                    <div class="flex justify-between text-[10px] sm:text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-300">🇸🇬 SGD</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300">{{ currencyDistribution.sgd
                            }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" :style="{ width: currencyDistribution.sgd + '%' }">
                        </div>
                    </div>
                    <div class="flex justify-between text-[10px] sm:text-xs">
                        <span class="font-medium text-slate-600 dark:text-slate-300">🇺🇸 USD</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300">{{ currencyDistribution.usd
                            }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full"
                            :style="{ width: currencyDistribution.usd + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="flex flex-col sm:flex-row gap-3 mb-4 sm:mb-6">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" v-model="searchQuery" placeholder="Search assets..."
                        class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white dark:bg-zinc-900 text-slate-900 dark:text-white">
                </div>
            </div>
            <select v-model="filterType"
                class="px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white dark:bg-zinc-900 text-slate-900 dark:text-white">
                <option value="all">All Types</option>
                <option value="gold">🥇 Gold</option>
                <option value="currency">💵 Currency</option>

            </select>
            <select v-model="filterCurrency"
                class="px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white dark:bg-zinc-900 text-slate-900 dark:text-white">
                <option value="all">All Currencies</option>
                <option value="MMK">🇲🇲 MMK Only</option>
                <option value="USD">🇺🇸 USD Only</option>
                <option value="SGD">🇸🇬 SGD Only</option>
            </select>
            <select v-model="sortBy"
                class="px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white dark:bg-zinc-900 text-slate-900 dark:text-white">
                <option value="date">Sort by Date</option>
                <option value="name">Sort by Name</option>
                <option value="value">Sort by Value</option>
                <option value="return">Sort by Return</option>
            </select>
            <button @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
                class="px-3 py-2 border border-slate-200 dark:border-zinc-700 rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-zinc-800 transition bg-white dark:bg-zinc-900 text-slate-700 dark:text-white">
                {{ sortOrder === 'asc' ? '↑ Ascending' : '↓ Descending' }}
            </button>
            <button @click="clearFilters"
                class="px-3 py-2 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 rounded-xl text-sm hover:bg-red-100 dark:hover:bg-red-900/50 transition">
                Clear Filters
            </button>
        </div>

        <!-- Results Count -->
        <div v-if="assets.length > 0" class="mb-3 text-xs text-slate-400 dark:text-slate-500">
            Showing {{ filteredAndSortedAssets.length }} of {{ assets.length }} assets
        </div>

        <!-- Table View -->
        <div v-if="viewMode === 'table' && filteredAndSortedAssets.length > 0"
            class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1200px]">
                    <thead class="bg-slate-50 dark:bg-zinc-800 border-b border-slate-200 dark:border-zinc-700">
                        <tr>
                            <th
                                class="px-4 sm:px-6 py-3 text-left text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                Asset</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                Purchase Currency</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                Purchase Value</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                Current Value</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                P&L (Original)</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                P&L (MMK)</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                P&L (USD)</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                P&L (SGD)</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-right text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                Return %</th>
                            <th
                                class="px-4 sm:px-6 py-3 text-center text-[10px] sm:text-xs font-bold text-slate-500 dark:text-slate-400">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr v-for="asset in filteredAndSortedAssets" :key="asset.id"
                            class="hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 sm:px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center text-lg">
                                        {{ getAssetIcon(asset.type) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-sm">{{ asset.name }}</p>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500">{{
                                            getAssetTypeLabel(asset.type) }} · {{
                                                asset.quantity }} units</p>
                                        <p class="text-[9px] text-slate-400 dark:text-slate-500">{{
                                            formatDate(asset.purchase_date) }}</p>
                                        <p v-if="asset.type === 'currency'"
                                            class="text-[8px] text-blue-600 dark:text-blue-400 mt-0.5">
                                            Rate: {{ formatMoney(asset.purchase_price) }} MMK/{{ asset.purchase_currency
                                            }}
                                        </p>
                                        <p v-if="asset.type === 'gold' && asset.troy_ounces"
                                            class="text-[8px] text-amber-600 dark:text-amber-400 mt-0.5">
                                            {{ asset.troy_ounces.toFixed(4) }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold"
                                    :class="getCurrencyBadgeClass(asset.purchase_currency)">
                                    {{ asset.purchase_currency }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <p class="font-mono font-bold text-sm"
                                    :class="asset.type === 'currency' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-900 dark:text-white'">
                                    <span v-if="asset.type === 'currency'">
                                        {{ formatMoney(asset.purchase_value_mmk) }} MMK
                                    </span>
                                    <span v-else>
                                        {{ formatMoney(asset.purchase_price) }} {{ asset.purchase_currency }}
                                    </span>
                                </p>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500">
                                    <span v-if="asset.type === 'currency'">
                                        @ {{ formatMoney(asset.purchase_price) }} MMK/{{ asset.purchase_currency }}
                                    </span>
                                    <span v-else>
                                        ≈ {{ formatMoney(asset.purchase_value_mmk) }} MMK
                                    </span>
                                </p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <p class="font-mono font-bold text-sm"
                                    :class="asset.type === 'currency' ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400'">
                                    <span v-if="asset.type === 'currency'">
                                        {{ formatMoney(asset.current_value_mmk) }} MMK
                                    </span>
                                    <span v-else>
                                        {{ formatMoney(asset.current_value) }} {{ asset.purchase_currency }}
                                    </span>
                                </p>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500">
                                    <span v-if="asset.type === 'currency'">
                                        {{ asset.quantity }} {{ asset.purchase_currency }} × {{
                                            asset.purchase_currency === 'USD'
                                                ? formatMoney(currentRates?.usd_mmk)
                                                : asset.purchase_currency === 'SGD'
                                                    ? formatMoney(currentRates?.sgd_mmk)
                                                    : formatMoney(1)
                                        }} MMK/{{ asset.purchase_currency }}
                                    </span>
                                    <span v-else>
                                        ≈ {{ formatMoney(asset.current_value_mmk) }} MMK
                                    </span>
                                </p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <p :class="asset.pnl_original >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                    class="font-mono font-bold text-sm">
                                    <span v-if="asset.type === 'currency'">
                                        {{ asset.pnl_original >= 0 ? '+' : '' }}{{
                                            formatMoney(Math.abs(asset.pnl_original)) }}
                                        {{ asset.purchase_currency }}
                                    </span>
                                    <span v-else>
                                        {{ asset.pnl_original >= 0 ? '+' : '' }}{{
                                            formatMoney(Math.abs(asset.pnl_original)) }}
                                        {{ asset.purchase_currency }}
                                    </span>
                                </p>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500">
                                    <span v-if="asset.type === 'currency'">
                                        Exchange rate change
                                    </span>
                                    <span v-else>
                                        {{ ((asset.pnl_original / (asset.purchase_price * asset.quantity)) *
                                            100).toFixed(2) }}%
                                        of purchase
                                    </span>
                                </p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <p :class="asset.pnl_mmk >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                    class="font-mono font-bold text-sm">
                                    {{ asset.pnl_mmk >= 0 ? '+' : '' }}{{ formatMoney(asset.pnl_mmk) }}
                                </p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <p :class="asset.pnl_usd >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                    class="font-mono font-bold text-sm">
                                    {{ asset.pnl_usd >= 0 ? '+' : '' }}${{ formatMoney(Math.abs(asset.pnl_usd)) }}
                                </p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <p :class="asset.pnl_sgd >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                    class="font-mono font-bold text-sm">
                                    {{ asset.pnl_sgd >= 0 ? '+' : '' }}S${{ formatMoney(Math.abs(asset.pnl_sgd)) }}
                                </p>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-right">
                                <span
                                    :class="asset.is_profit ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                                    class="px-2 py-1 rounded-lg text-xs font-bold">
                                    {{ asset.pnl_percent >= 0 ? '+' : '' }}{{ asset.pnl_percent.toFixed(2) }}%
                                </span>
                                <div class="w-16 h-1 bg-slate-100 dark:bg-zinc-800 rounded-full mt-1 overflow-hidden">
                                    <div class="h-full rounded-full"
                                        :class="asset.is_profit ? 'bg-emerald-500' : 'bg-rose-500'"
                                        :style="{ width: Math.min(Math.abs(asset.pnl_percent), 100) + '%' }"></div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-3 text-center">
                                <button @click="deleteAsset(asset.id)"
                                    class="text-red-400 hover:text-red-600 dark:text-red-500 dark:hover:text-red-400 transition p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card View -->
        <div v-else-if="viewMode === 'card' && filteredAndSortedAssets.length > 0"
            class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5">
            <div v-for="asset in filteredAndSortedAssets" :key="asset.id"
                class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                <div class="p-4 sm:p-5">
                    <div class="flex justify-between items-start mb-3 sm:mb-4">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-zinc-800 dark:to-zinc-700 flex items-center justify-center text-xl sm:text-2xl">
                                {{ getAssetIcon(asset.type) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">{{ asset.name
                                    }}</h3>
                                <p class="text-[8px] sm:text-[10px] text-slate-400 dark:text-slate-500">{{
                                    getAssetTypeLabel(asset.type) }}
                                </p>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold"
                                    :class="getCurrencyBadgeClass(asset.purchase_currency)">
                                    {{ asset.purchase_currency }}
                                </span>
                            </div>
                        </div>
                        <span
                            :class="asset.is_profit ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'"
                            class="px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-lg text-[9px] sm:text-xs font-bold">
                            {{ asset.pnl_percent >= 0 ? '+' : '' }}{{ asset.pnl_percent.toFixed(2) }}%
                        </span>
                    </div>

                    <div class="space-y-2 sm:space-y-3 text-xs sm:text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Purchase:</span>
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">
                                <span v-if="asset.type === 'currency'">
                                    {{ formatMoney(asset.purchase_value_mmk) }} MMK
                                </span>
                                <span v-else>
                                    {{ formatMoney(asset.purchase_price) }} {{ asset.purchase_currency }}
                                </span>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Current:</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400">
                                <span v-if="asset.type === 'currency'">
                                    {{ formatMoney(asset.current_value_mmk) }} MMK
                                </span>
                                <span v-else>
                                    {{ formatMoney(asset.current_value) }} {{ asset.purchase_currency }}
                                </span>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">P&L (MMK):</span>
                            <span
                                :class="asset.is_profit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                class="font-mono font-bold">
                                {{ asset.is_profit ? '+' : '' }}{{ formatMoney(asset.pnl_mmk) }} MMK
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">P&L (USD):</span>
                            <span
                                :class="asset.is_profit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                class="font-mono font-bold">
                                {{ asset.is_profit ? '+' : '' }}${{ formatMoney(Math.abs(asset.pnl_usd)) }} USD
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">P&L (SGD):</span>
                            <span
                                :class="asset.is_profit ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                class="font-mono font-bold">
                                {{ asset.is_profit ? '+' : '' }}S${{ formatMoney(Math.abs(asset.pnl_sgd)) }} SGD
                            </span>
                        </div>
                        <button @click="showCalculation(asset)"
                            class="w-full text-center text-[8px] sm:text-[10px] text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline">
                            📊 View calculation details
                        </button>
                        <div class="pt-2 border-t border-slate-100 dark:border-zinc-800">
                            <div
                                class="flex justify-between text-[8px] sm:text-[10px] text-slate-400 dark:text-slate-500">
                                <span>📅 {{ formatDate(asset.purchase_date) }}</span>
                                <span>📦 {{ asset.quantity }} units</span>
                            </div>
                        </div>
                    </div>

                    <button @click="deleteAsset(asset.id)"
                        class="mt-3 sm:mt-4 w-full py-1.5 sm:py-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-[10px] sm:text-xs font-bold border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                        Remove Asset
                    </button>
                </div>
            </div>
        </div>

        <!-- No Results State -->
        <div v-else-if="filteredAndSortedAssets.length === 0 && assets.length > 0"
            class="text-center py-12 sm:py-16 bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800">
            <div
                class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm sm:text-base">No matching assets found</p>
            <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 mt-1">Try adjusting your search or filters
            </p>
            <button @click="clearFilters"
                class="inline-block mt-3 sm:mt-4 px-4 sm:px-6 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition">
                Clear Filters
            </button>
        </div>

        <!-- Empty State -->
        <div v-else-if="assets.length === 0"
            class="text-center py-12 sm:py-16 bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800">
            <div
                class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm sm:text-base">No assets yet</p>
            <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 mt-1">Start tracking your investments by
                adding your
                first asset
            </p>
            <Link :href="route('user.assets.create')"
                class="inline-block mt-3 sm:mt-4 px-4 sm:px-6 py-1.5 sm:py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs sm:text-sm font-bold transition">
                + Add Your First Asset
            </Link>
        </div>

        <!-- Exchange Rate Info Footer -->
        <div
            class="mt-4 sm:mt-6 p-3 sm:p-4 bg-gradient-to-r from-slate-50 to-white dark:from-zinc-900 dark:to-zinc-800 rounded-xl border border-slate-200 dark:border-zinc-700">
            <div class="flex flex-wrap justify-between items-center gap-2 sm:gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-[8px] sm:text-[10px] text-slate-500 dark:text-slate-400">Live exchange rates from
                        market
                        data</span>
                </div>
                <div
                    class="flex flex-wrap gap-2 sm:gap-4 text-[7px] sm:text-[9px] font-mono text-slate-500 dark:text-slate-400">
                    <span>1 USD = {{ formatMoney(currentRates?.usd_mmk) }} MMK</span>
                    <span>1 SGD = {{ formatMoney(currentRates?.sgd_mmk) }} MMK</span>
                    <span>1 USD = {{ formatNumber(currentRates?.usd_sgd, 4) }} SGD</span>
                </div>
            </div>
        </div>

        <!-- Calculation Modal -->
        <div v-if="selectedAsset" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="closeModal">
            <div
                class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl max-w-2xl w-full max-h-[85vh] sm:max-h-[80vh] overflow-y-auto">
                <div
                    class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 p-4 sm:p-5 flex justify-between items-center">
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">Calculation Details
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ selectedAsset.name }}</p>
                    </div>
                    <button @click="closeModal"
                        class="text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-400">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <!-- Purchase Information -->
                    <div class="bg-slate-50 dark:bg-zinc-800 rounded-xl p-3 sm:p-4">
                        <h4
                            class="text-xs sm:text-sm font-black text-slate-900 dark:text-white mb-2 sm:mb-3 flex items-center gap-2">
                            <span class="w-1 h-3 sm:h-4 bg-blue-500 rounded-full"></span>
                            Purchase Information
                        </h4>
                        <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Purchase Price:</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">
                                    <span v-if="selectedAsset.type === 'currency'">
                                        {{ formatMoney(selectedAsset.purchase_price) }} MMK per {{
                                            selectedAsset.purchase_currency }}
                                    </span>
                                    <span v-else>
                                        {{ formatMoney(selectedAsset.purchase_price) }} {{
                                            selectedAsset.purchase_currency }}
                                    </span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Quantity:</span>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{
                                    selectedAsset.quantity }}
                                    units</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-400">Purchase Date:</span>
                                <span class="font-mono text-slate-900 dark:text-white">{{ selectedAsset.purchase_date
                                    }}</span>
                            </div>
                            <div class="border-t border-slate-200 dark:border-zinc-700 my-2"></div>
                            <div class="flex justify-between font-bold">
                                <span class="text-slate-700 dark:text-slate-300">Total Purchase Value (MMK):</span>
                                <span class="font-mono text-blue-600 dark:text-blue-400">{{
                                    formatMoney(selectedAsset.purchase_value_mmk) }} MMK</span>
                            </div>
                        </div>
                    </div>

                    <!-- P&L Summary -->
                    <div
                        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 rounded-xl p-3 sm:p-4 border border-indigo-200 dark:border-indigo-800">
                        <h4
                            class="text-xs sm:text-sm font-black text-indigo-800 dark:text-indigo-300 mb-2 sm:mb-3 flex items-center gap-2">
                            <span class="w-1 h-3 sm:h-4 bg-indigo-500 rounded-full"></span>
                            📈 Profit & Loss Summary
                        </h4>
                        <div class="space-y-2 text-xs sm:text-sm">
                            <div class="flex justify-between">
                                <span class="text-indigo-700 dark:text-indigo-300">Return %:</span>
                                <span :class="selectedAsset.pnl_percent >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                    class="font-mono font-bold">
                                    {{ selectedAsset.pnl_percent >= 0 ? '+' : '' }}{{
                                        selectedAsset.pnl_percent.toFixed(2) }}%
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-indigo-700 dark:text-indigo-300">P&L (MMK):</span>
                                <span :class="selectedAsset.pnl_mmk >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                    class="font-mono">
                                    {{ selectedAsset.pnl_mmk >= 0 ? '+' : '' }}{{ formatMoney(selectedAsset.pnl_mmk) }}
                                    MMK
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-indigo-700 dark:text-indigo-300">P&L (USD):</span>
                                <span :class="selectedAsset.pnl_usd >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                    class="font-mono">
                                    {{ selectedAsset.pnl_usd >= 0 ? '+' : '' }}${{
                                        formatMoney(Math.abs(selectedAsset.pnl_usd))
                                    }} USD
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-indigo-700 dark:text-indigo-300">P&L (SGD):</span>
                                <span :class="selectedAsset.pnl_sgd >= 0 ? 'text-emerald-600' : 'text-rose-600'"
                                    class="font-mono">
                                    {{ selectedAsset.pnl_sgd >= 0 ? '+' : '' }}S${{
                                        formatMoney(Math.abs(selectedAsset.pnl_sgd))
                                    }} SGD
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Exchange Rates Used -->
                    <div class="bg-slate-100 dark:bg-zinc-800 rounded-xl p-3">
                        <h4 class="text-[9px] sm:text-[10px] font-black text-slate-500 dark:text-slate-400 mb-2">📊
                            Exchange
                            Rates Used</h4>
                        <div class="grid grid-cols-2 gap-1 sm:gap-2 text-[8px] sm:text-[9px]">
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">USD/MMK:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-300">{{
                                    formatMoney(currentRates?.usd_mmk)
                                    }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">SGD/MMK:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-300">{{
                                    formatMoney(currentRates?.sgd_mmk)
                                    }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 dark:text-slate-400">USD/SGD:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-300">{{
                                    formatNumber(currentRates?.usd_sgd, 4) }}</span>
                            </div>
                            <div class="flex justify-between col-span-2">
                                <span class="text-slate-500 dark:text-slate-400">Updated:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-300">{{ new
                                    Date().toLocaleTimeString()
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>


<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { registerEscapeHandler } from '@/Composables/useGlobalEscape'

const props = defineProps({
    assets: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({}) },
    currentRates: { type: Object, default: () => ({}) }
})

const viewMode = ref('table')
const refreshing = ref(false)
const selectedAsset = ref(null)

// Filter and sort state
const sortBy = ref('date')
const sortOrder = ref('desc')
const filterType = ref('all')
const searchQuery = ref('')
const filterCurrency = ref('all')

const formatMoney = (value) => {
    if (!value && value !== 0) return '0'
    return new Intl.NumberFormat('en-US').format(Math.round(Math.abs(value)))
}

const formatNumber = (value, decimals = 2) => {
    if (!value && value !== 0) return '0'
    return value.toFixed(decimals)
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

const getAssetTypeLabel = (type) => {
    const labels = { gold: 'Gold', currency: 'Foreign Currency', property: 'Property', car: 'Vehicle', jewelry: 'Jewelry', crypto: 'Crypto', other: 'Other' }
    return labels[type] || type
}

const getAssetIcon = (type) => {
    const icons = { gold: '🥇', currency: '💵', property: '🏠', car: '🚗', jewelry: '💍', crypto: '₿', other: '📦' }
    return icons[type] || '📦'
}

const getCurrencyBadgeClass = (currency) => {
    const classes = {
        MMK: 'bg-emerald-100 text-emerald-700',
        USD: 'bg-purple-100 text-purple-700',
        SGD: 'bg-blue-100 text-blue-700'
    }
    return classes[currency] || 'bg-slate-100 text-slate-700'
}

// Filtered and sorted assets
const filteredAndSortedAssets = computed(() => {
    let filtered = [...props.assets]

    if (filterType.value !== 'all') {
        filtered = filtered.filter(a => a.type === filterType.value)
    }

    if (filterCurrency.value !== 'all') {
        filtered = filtered.filter(a => a.purchase_currency === filterCurrency.value)
    }

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(a =>
            a.name.toLowerCase().includes(query) ||
            getAssetTypeLabel(a.type).toLowerCase().includes(query)
        )
    }

    filtered.sort((a, b) => {
        let aVal, bVal
        switch (sortBy.value) {
            case 'name':
                aVal = a.name
                bVal = b.name
                break
            case 'value':
                aVal = a.current_value_mmk
                bVal = b.current_value_mmk
                break
            case 'return':
                aVal = a.pnl_percent
                bVal = b.pnl_percent
                break
            case 'date':
                aVal = new Date(a.purchase_date)
                bVal = new Date(b.purchase_date)
                break
            default:
                aVal = a.purchase_date
                bVal = b.purchase_date
        }

        if (sortOrder.value === 'asc') {
            return aVal > bVal ? 1 : -1
        } else {
            return aVal < bVal ? 1 : -1
        }
    })

    return filtered
})

const clearFilters = () => {
    searchQuery.value = ''
    filterType.value = 'all'
    filterCurrency.value = 'all'
    sortBy.value = 'date'
    sortOrder.value = 'desc'
}

// Computed properties for summary
const totalInvestedByCurrency = computed(() => {

    let mmk = 0
    let usd = 0
    let sgd = 0

    props.assets.forEach(a => {

        if (a.purchase_currency === 'MMK') {
            mmk += a.purchase_value_mmk
        }

        if (a.purchase_currency === 'USD') {
            usd += a.quantity // ✅ FIX HERE
        }

        if (a.purchase_currency === 'SGD') {
            sgd += (a.purchase_price * a.quantity)
        }
    })

    const totalMmk =
        mmk +
        (usd * (props.currentRates?.usd_mmk || 1)) +
        (sgd * (props.currentRates?.sgd_mmk || 1))

    return {
        mmk: mmk,
        usd: usd,
        sgd: sgd,
        totalMmk: totalMmk,
        totalUsd: totalMmk / (props.currentRates?.usd_mmk || 1),
        totalSgd: totalMmk / (props.currentRates?.sgd_mmk || 1)
    }
})

const totalPortfolioValue = computed(() => {
    const mmkValue = props.assets.reduce((sum, a) => sum + (a.current_value_mmk || 0), 0)
    const usdValue = mmkValue / (props.currentRates?.usd_mmk || 1)
    const sgdValue = mmkValue / (props.currentRates?.sgd_mmk || 1)
    return { mmk: mmkValue, usd: usdValue, sgd: sgdValue }
})

const currencyDistribution = computed(() => {
    const total = totalPortfolioValue.value.mmk || 1
    const mmkValue = props.assets
        .filter(a => a.purchase_currency === 'MMK')
        .reduce((sum, a) => sum + (a.current_value_mmk || 0), 0)
    const usdValue = props.assets
        .filter(a => a.purchase_currency === 'USD')
        .reduce((sum, a) => sum + (a.current_value_mmk || 0), 0)
    const sgdValue = props.assets
        .filter(a => a.purchase_currency === 'SGD')
        .reduce((sum, a) => sum + (a.current_value_mmk || 0), 0)

    return {
        mmk: Math.round((mmkValue / total) * 100),
        usd: Math.round((usdValue / total) * 100),
        sgd: Math.round((sgdValue / total) * 100)
    }
})

// Currency P&L Summary
const currencyPnLSummary = computed(() => {
    const mmkAssets = props.assets.filter(a => a.purchase_currency === 'MMK')
    const usdAssets = props.assets.filter(a => a.purchase_currency === 'USD')
    const sgdAssets = props.assets.filter(a => a.purchase_currency === 'SGD')

    const mmkPnlMmk = mmkAssets.reduce((sum, a) => sum + (a.pnl_mmk || 0), 0)
    const sgdPnlMmk = sgdAssets.reduce((sum, a) => sum + (a.pnl_mmk || 0), 0)
    const usdPnlMmk = usdAssets.reduce((sum, a) => sum + (a.pnl_mmk || 0), 0)

    const mmkPnlUsd = mmkPnlMmk / (props.currentRates?.usd_mmk || 1)
    const sgdPnlUsd = sgdPnlMmk / (props.currentRates?.usd_mmk || 1)
    const usdPnlUsd = usdPnlMmk / (props.currentRates?.usd_mmk || 1)

    const mmkPnlSgd = mmkPnlMmk / (props.currentRates?.sgd_mmk || 1)
    const sgdPnlSgd = sgdPnlMmk / (props.currentRates?.sgd_mmk || 1)
    const usdPnlSgd = usdPnlMmk / (props.currentRates?.sgd_mmk || 1)

    return {
        totalPnlMmk: mmkPnlMmk + sgdPnlMmk + usdPnlMmk,
        totalPnlUsd: mmkPnlUsd + sgdPnlUsd + usdPnlUsd,
        totalPnlSgd: mmkPnlSgd + sgdPnlSgd + usdPnlSgd,
        mmkAssets: { pnl: mmkPnlMmk, pnlUsd: mmkPnlUsd, pnlSgd: mmkPnlSgd },
        sgdAssets: { pnlMmk: sgdPnlMmk, pnlUsd: sgdPnlUsd, pnlSgd: sgdPnlSgd },
        usdAssets: { pnlMmk: usdPnlMmk, pnlUsd: usdPnlUsd, pnlSgd: usdPnlSgd }
    }
})

const showCalculation = (asset) => {
    selectedAsset.value = asset
}

const toggleView = () => {
    viewMode.value = viewMode.value === 'table' ? 'card' : 'table'
}

const refreshData = () => {
    refreshing.value = true
    router.reload({ only: ['assets', 'summary', 'currentRates'] })
    setTimeout(() => { refreshing.value = false }, 1000)
}

const deleteAsset = (id) => {
    if (confirm('Are you sure you want to remove this asset? This action cannot be undone.')) {
        router.delete(route('user.assets.destroy', id))
    }
}

let unregister
const closeModal = () => {
    selectedAsset.value = null
}

onMounted(() => {
    unregister = registerEscapeHandler(closeModal)
})

onUnmounted(() => {
    if (unregister) unregister()
})
</script>

<style scoped>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.group:hover .group-hover\:translate-x-0\.5 {
    transform: translateX(0.125rem);
}

.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@media (max-width: 480px) {
    .xs\:inline {
        display: inline;
    }
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>