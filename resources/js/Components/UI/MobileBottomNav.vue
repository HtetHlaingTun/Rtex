<template>
    <nav
        class="sm:hidden fixed bottom-0 left-0 right-0 z-[100] bg-white/80 dark:bg-zinc-950/80 backdrop-blur-lg border-t border-slate-200 dark:border-zinc-800 pb-safe">
        <div class="flex items-center justify-around h-16 px-2">

            <Link :href="route('welcome')" class="flex flex-col items-center gap-1 flex-1">
            <div :class="[route().current('welcome') ? 'text-orange-600' : 'text-slate-400']" class="transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-tight"
                :class="[route().current('welcome') ? 'text-orange-600' : 'text-slate-400']">Home</span>
            </Link>

            <!-- <Link :href="route('gold.history', 'new_system')" class="flex flex-col items-center gap-1 flex-1">
            <div :class="[route().current('gold.history') ? 'text-orange-600' : 'text-slate-400']">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-tight"
                :class="[route().current('gold.history') ? 'text-orange-600' : 'text-slate-400']">Rates</span>
            </Link> -->

            <Link :href="route('login')" class="flex flex-col items-center gap-1 flex-1">
            <div class="p-2 bg-slate-100 dark:bg-zinc-800 rounded-full text-slate-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-tight text-slate-400">Account</span>
            </Link>

        </div>
    </nav>
</template>