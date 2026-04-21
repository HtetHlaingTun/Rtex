<template>
    <nav class="sm:hidden fixed bottom-0 left-0 right-0 z-[100] bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl border-t border-slate-200/50 dark:border-zinc-800/50 shadow-lg"
        :style="{ paddingBottom: safeAreaBottom }">

        <div class="flex items-center justify-around h-16 px-2">

            <!-- Home -->
            <Link :href="route('welcome')"
                class="relative flex flex-col items-center justify-center gap-1 flex-1 py-1 transition-all duration-200 group"
                :class="[route().current('welcome') ? 'text-orange-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300']">

                <div v-if="route().current('welcome')" class="absolute -top-0.5 w-8 h-0.5 bg-orange-500 rounded-full">
                </div>

                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>

                <span class="text-[9px] font-bold uppercase tracking-wide">Home</span>
            </Link>

            <!-- Rates -->
            <Link :href="route('rates.index')"
                class="relative flex flex-col items-center justify-center gap-1 flex-1 py-1 transition-all duration-200 group"
                :class="[route().current('rates.index') ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300']">

                <div v-if="route().current('rates.index')" class="absolute -top-0.5 w-8 h-0.5 bg-blue-500 rounded-full">
                </div>

                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>

                <span class="text-[9px] font-bold uppercase tracking-wide">Rates</span>
            </Link>

            <!-- Fuel -->
            <Link :href="route('fuel-prices')"
                class="relative flex flex-col items-center justify-center gap-1 flex-1 py-1 transition-all duration-200 group"
                :class="[route().current('fuel-prices') ? 'text-amber-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300']">

                <div v-if="route().current('fuel-prices')"
                    class="absolute -top-0.5 w-8 h-0.5 bg-amber-500 rounded-full">
                </div>

                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>

                <span class="text-[9px] font-bold uppercase tracking-wide">Fuel</span>
            </Link>

            <!-- Gold -->
            <Link :href="route('goldPage.index')"
                class="relative flex flex-col items-center justify-center gap-1 flex-1 py-1 transition-all duration-200 group"
                :class="[route().current('goldPage.index') ? 'text-amber-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300']">

                <div v-if="route().current('goldPage.index')"
                    class="absolute -top-0.5 w-8 h-0.5 bg-amber-500 rounded-full">
                </div>

                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <span class="text-[9px] font-bold uppercase tracking-wide">Gold</span>
            </Link>

            <!-- Blog -->
            <Link :href="route('blog.index')"
                class="relative flex flex-col items-center justify-center gap-1 flex-1 py-1 transition-all duration-200 group"
                :class="[route().current('blog.index') ? 'text-purple-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300']">

                <div v-if="route().current('blog.index')"
                    class="absolute -top-0.5 w-8 h-0.5 bg-purple-500 rounded-full"></div>

                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>

                <span class="text-[9px] font-bold uppercase tracking-wide">Blog</span>
            </Link>

            <!-- Profile / Login -->
            <Link :href="isLoggedIn ? route('profile.edit') : route('login')"
                class="relative flex flex-col items-center justify-center gap-1 flex-1 py-1 transition-all duration-200 group"
                :class="[isLoggedIn ? 'text-emerald-600' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300']">

                <div v-if="isLoggedIn" class="absolute -top-0.5 w-8 h-0.5 bg-emerald-500 rounded-full"></div>

                <div class="relative">
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>

                    <span v-if="isLoggedIn"
                        class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-emerald-500 rounded-full border-2 border-white dark:border-zinc-950"></span>
                </div>

                <span class="text-[9px] font-bold uppercase tracking-wide">
                    {{ isLoggedIn ? 'Profile' : 'Login' }}
                </span>
            </Link>

        </div>
    </nav>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'

const page = usePage()
const isLoggedIn = computed(() => !!page.props.auth?.user)

// Safe area handling with web fallback
const safeAreaBottom = ref('env(safe-area-inset-bottom)')

onMounted(async () => {
    // Check if running in Capacitor native platform
    if (window.Capacitor && window.Capacitor.isNativePlatform()) {
        try {
            const { SafeArea } = await import('@capacitor-community/safe-area')
            const insets = await SafeArea.getSafeAreaInsets()
            safeAreaBottom.value = `${insets.bottom}px`

            // Listen for changes (orientation, keyboard)
            SafeArea.addListener('safeAreaChanged', (insets) => {
                safeAreaBottom.value = `${insets.bottom}px`
            })
        } catch (e) {
            console.log('Safe area plugin not available, using CSS env')
        }
    }
})
</script>
<style scoped>
/* No additional styles needed - using inline style binding */
</style>