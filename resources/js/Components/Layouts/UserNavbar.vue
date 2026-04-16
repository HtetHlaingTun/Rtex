<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import { computed } from 'vue';

const { isDark, toggleDark } = useDarkMode()
const page = usePage();

// Get current URL path
const currentUrl = computed(() => page.url);

// Navigation items with their paths for active highlighting
const navItems = [
    { name: 'Home', href: '/', path: '/' },
    { name: 'Rates', href: '/rates', path: '/rates' },
    { name: 'Gold', href: '/gold/index', path: '/gold/index' },
    { name: 'Blog', href: '/blog', path: '/blog' },
];

// Check if current route matches the nav item
const isActive = (item) => {
    const current = currentUrl.value;

    // Exact match for home
    if (item.path === '/' && current === '/') return true;

    // Check if current URL starts with the item path
    if (item.path !== '/' && current.startsWith(item.path)) return true;

    // Also check for history pages (so Rates stays active when viewing history)
    if (item.path === '/rates' && current.startsWith('/history')) return true;
    if (item.path === '/gold/index' && current.startsWith('/gold')) return true;

    return false;
};
</script>

<template>
    <nav
        class="bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-slate-100 dark:border-zinc-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <!-- Logo -->
                <div class="flex items-center gap-8">
                    <Link :href="route('welcome')" class="flex items-center gap-2 group">

                        <span class="text-xl font-black tracking-tight">
                            <span class="text-orange-600 dark:text-orange-500">MM</span>
                            <span class="text-slate-800 dark:text-white">RatePro</span>
                        </span>
                    </Link>

                    <!-- Desktop Navigation with Active Highlight -->
                    <div class="hidden md:flex items-center gap-1">
                        <Link v-for="item in navItems" :key="item.name" :href="item.href"
                            class="relative px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200"
                            :class="isActive(item)
                                ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30'
                                : 'text-slate-600 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50/50 dark:hover:bg-orange-950/20'">

                            {{ item.name }}

                            <!-- Active Indicator Underline -->
                            <span v-if="isActive(item)"
                                class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-6 h-0.5 bg-orange-500 rounded-full">
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2">

                    <!-- Login Button -->
                    <Link v-if="!$page.props.auth.user" :href="route('login')" class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                               text-slate-600 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400
                               hover:bg-orange-50/50 dark:hover:bg-orange-950/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Login</span>
                    </Link>

                    <!-- Dashboard Link (when logged in) -->
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium
                               text-slate-600 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400
                               hover:bg-orange-50/50 dark:hover:bg-orange-950/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="hidden sm:inline">Dashboard</span>
                    </Link>

                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDark"
                        class="relative inline-flex h-8 w-14 items-center rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-orange-500/30"
                        :class="isDark ? 'bg-zinc-700' : 'bg-slate-200'">

                        <div class="absolute inset-0 flex items-center justify-between px-1.5 pointer-events-none">
                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.728 0A9 9 0 115.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>

                        <span
                            class="inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition-all duration-300 ease-in-out flex items-center justify-center"
                            :class="isDark ? 'translate-x-7' : 'translate-x-1'">
                            <svg v-if="isDark" class="w-3.5 h-3.5 text-zinc-700" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                            </svg>
                            <svg v-else class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </nav>
</template>