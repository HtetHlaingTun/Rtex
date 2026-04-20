<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue'

// Main navigation items
const navItems = [
    // { name: 'Dashboard', href: route('dashboard'), component: 'Dashboard' },
    { name: 'Gold Prices', href: route('gold.index'), component: 'Gold/Index' },
    { name: 'Feedback', href: route('admin.contacts.index'), component: 'Admin/Contact/Index' },
    { name: 'Blog', href: route('admin.blog.index'), component: 'Admin/Blog/Index' },
    { name: 'Fuel', href: route('admin.fuel.index'), component: 'Admin/Fuel/Index' },
];

const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');
</script>

<template>
    <nav class="bg-white border-b border-slate-200 shadow-sm shadow-inner-soft  z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">

                <div class="flex items-center gap-10">
                    <h1 class="flex-shrink-0 flex items-center gap-3">
                        <!-- <img src="../../../../public/favicon.ico" class="h-10 w-10 "> -->
                        <span class="text-xl font-black tracking-tighter text-orange-600">
                            MM<span class="text-slate-900">RatePro</span>
                        </span>

                    </h1>

                    <div class="hidden md:flex items-center space-x-1">
                        <Link v-for="item in navItems" :key="item.name" :href="item.href"
                            class="px-3 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="$page.component === item.component ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50'">
                            {{ item.name }}
                        </Link>

                        <Link :href="route('currencies.index')"
                            class="px-3 py-2 rounded-lg text-sm font-bold transition-all"
                            :class="$page.component === 'Currency/Index' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-indigo-600 hover:bg-slate-50'">
                            Live Rates
                        </Link>


                    </div>
                </div>

                <div
                    class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-t border-slate-200 pb-safe">
                    <div class="flex items-center justify-around h-16 px-2">

                        <Link v-for="item in navItems" :key="item.name" :href="item.href"
                            class="flex flex-col items-center justify-center flex-1 py-1"
                            :class="$page.component === item.component ? 'text-indigo-600' : 'text-slate-400'">
                            <span class="text-[10px] font-bold mt-1">{{ item.name }}</span>
                        </Link>

                        <Link :href="route('currencies.index')"
                            class="flex flex-col items-center justify-center flex-1 py-1"
                            :class="$page.component === 'Currency/Index' ? 'text-indigo-600' : 'text-slate-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <span class="text-[10px] font-bold mt-1">Live Rates</span>
                        </Link>




                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block border-r pr-4 border-slate-200">
                        <p class="text-sm font-bold text-slate-800 leading-tight">{{ $page.props.auth.user?.name ||
                            'Guest' }}</p>
                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{
                            $page.props.auth.user?.role || 'Viewer' }}</p>
                    </div>

                    <Link :href="route('logout')" method="post" as="button"
                        class="px-4 py-2 text-xs font-bold text-white bg-slate-900 rounded-xl hover:bg-black transition shadow-md shadow-slate-200">
                        Sign Out
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>