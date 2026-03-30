<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import { computed } from 'vue';

const { isDark, toggleDark } = useDarkMode()
// Navigation for public users only
const publicNav = [
    // { name: 'Live Rates', href: route('c'), component: 'Currency/Index' },
    // { name: 'Gold Prices', href: route('gold.index'), component: 'Gold/Index' },


];

const page = usePage();
const activeComponent = computed(() => page.component);
</script>
<template>
    <nav class="bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-slate-100 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-10">
                    <h1 class="flex items-center gap-3">
                        <!-- <img src="/favicon.ico" class="h-10 w-10"> -->
                        <span
                            class="text-xl font-black tracking-tighter text-orange-600 transition-colors duration-500">
                            MM<span class="text-slate-900 dark:text-white">RatePro</span>
                        </span>
                    </h1>

                    <Link :href="route('welcome')" class="hidden sm:flex flex-shrink-0 flex items-center">
                        <span class="text-md font-mono tracking-tighter text-orange-600">
                            Home<span class="text-slate-900"></span>
                        </span>
                    </Link>






                    <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                        <Link v-for="item in publicNav" :key="item.name" :href="item.href"
                            class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold transition-colors"
                            :class="activeComponent === item.component
                                ? 'border-orange-500 text-slate-900'
                                : 'border-transparent text-slate-400 hover:text-slate-600 hover:border-slate-200'">
                            {{ item.name }}
                        </Link>

                    </div>



                </div>


                <div class="flex items-center space-x-4 ">

                    <Link :href="route('login')" class="hidden sm:flex flex-shrink-0 flex items-center">
                        <span class="text-xl font-mono tracking-tighter text-orange-600">
                            login<span class="text-slate-900"></span>
                        </span>
                    </Link>

                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="p-2 text-slate-400 hover:text-orange-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </Link>


                    <button @click="toggleDark"
                        class="relative inline-flex h-8 w-14 items-center rounded-full transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-orange-500/20"
                        :class="isDark ? 'bg-zinc-700' : 'bg-slate-200'">
                        <div class="absolute inset-0 flex items-center justify-between px-1.5 pointer-events-none">
                            <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <svg class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.728 0A9 9 0 115.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>

                        <span
                            class="inline-block h-6 w-6 transform rounded-full bg-white shadow-lg ring-0 transition duration-300 ease-in-out flex items-center justify-center"
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