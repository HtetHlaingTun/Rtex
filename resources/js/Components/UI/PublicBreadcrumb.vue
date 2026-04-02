<template>
    <div class="flex flex-col sm:flex-row items-center justify-between w-full gap-3 sm:gap-4">

        <nav class="flex w-full sm:w-auto overflow-hidden" aria-label="Breadcrumb">
            <ol
                class="flex items-center space-x-1 md:space-x-2 whitespace-nowrap overflow-x-auto no-scrollbar pb-1 sm:pb-0">
                <li v-for="(item, index) in breadcrumbs" :key="index" class="inline-flex items-center flex-shrink-0">
                    <div class="flex items-center">
                        <svg v-if="index > 0"
                            class="rtl:rotate-180 w-2.5 h-2.5 text-slate-300 dark:text-zinc-600 mx-1.5"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 9 4-4-4-4" />
                        </svg>

                        <Link v-if="item.route" :href="route(item.route, item.params || {})"
                            class="inline-flex items-center text-[13px] sm:text-sm font-medium text-slate-600 hover:text-orange-600 dark:text-zinc-400 dark:hover:text-orange-400 transition-colors">
                            <span v-if="index === 0" class="inline-flex items-center gap-2">
                                <span class="text-xs">🏠</span>
                                <span class="hidden xs:inline">{{ item.label || 'Home' }}</span>
                            </span>
                            <span v-else>{{ item.label }}</span>
                        </Link>

                        <span v-else
                            class="text-[13px] sm:text-sm font-bold text-slate-900 dark:text-white truncate max-w-[120px] sm:max-w-none">
                            {{ item.label }}
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        <div
            class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-3 px-1 sm:px-0 border-t sm:border-none border-slate-100 dark:border-zinc-800 pt-2 sm:pt-0">

            <div class="flex items-center gap-4 text-[10px] sm:text-xs font-mono tracking-tight">
                <div
                    class="flex items-center gap-2 bg-slate-50 dark:bg-zinc-800/50 px-2 py-1 rounded-full border border-slate-100 dark:border-zinc-800">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping"
                            :class="isOnline ? 'bg-emerald-400' : 'bg-red-400'"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5"
                            :class="isOnline ? 'bg-emerald-500' : 'bg-red-500'"></span>
                    </span>
                    <span :class="isOnline ? 'text-emerald-600' : 'text-red-500'"
                        class="font-bold uppercase tracking-widest text-[9px]">
                        {{ isOnline ? 'Online' : 'Offline' }}
                    </span>
                </div>

                <div class="text-slate-400 dark:text-zinc-500 flex items-center gap-1.5">

                    <span class="tabular-nums font-bold text-slate-600 dark:text-zinc-300">
                        {{ currentTime }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
    breadcrumbs: {
        type: Array,
        required: true,
        default: () => []
    },
    separator: {
        type: String,
        default: '/'
    }
})

const isOnline = ref(navigator.onLine)
const lastSync = ref('—')

const currentTime = ref('—')

const updateTime = () => {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString()
}

const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine
}

const updateLastSync = () => {
    const now = new Date()
    lastSync.value = now.toLocaleTimeString()
}
onMounted(() => {
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)

    updateTime()

    const interval = setInterval(updateTime, 1000)

    // cleanup (important)
    onUnmounted(() => {
        clearInterval(interval)
        window.removeEventListener('online', updateOnlineStatus)
        window.removeEventListener('offline', updateOnlineStatus)
    })
})
</script>

<style scoped>
/* Mobile styles - apply margin-left only on screens 757px and lower */
@media (max-width: 757px) {
    nav {
        margin-left: 16px;
    }
}

/* Optional: Add padding for better touch targets on mobile */
@media (max-width: 757px) {
    .inline-flex.items-center.text-sm {
        padding: 4px 0;
    }

    /* Adjust icon size on mobile */
    svg.w-3.h-3 {
        width: 12px;
        height: 12px;
    }
}

/* Tablet styles (optional) */
@media (min-width: 758px) and (max-width: 1024px) {
    nav {
        margin-left: 12px;
    }
}
</style>