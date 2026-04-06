<template>
    <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
            :class="{ '-translate-x-full': !sidebarOpen }">

            <!-- Logo -->
            <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-zinc-800">
                <Link :href="route('user.dashboard')" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">M</span>
                    </div>
                    <span class="text-xl font-black text-slate-900 dark:text-white">MMRatePro</span>
                </Link>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1">
                <Link :href="route('user.dashboard')"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                    :class="route().current('user.dashboard') ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-zinc-800'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-sm font-bold">Dashboard</span>
                </Link>

                <Link :href="route('user.assets.index')"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                    :class="route().current('user.assets.*') ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-zinc-800'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-sm font-bold">My Assets</span>
                </Link>

                <Link :href="route('user.watchlist.index')"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                    :class="route().current('user.watchlist.*') ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-zinc-800'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <span class="text-sm font-bold">Watchlist</span>
                </Link>

                <Link :href="route('user.alerts.index')"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                    :class="route().current('user.alerts.*') ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-zinc-800'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="text-sm font-bold">Alerts</span>
                </Link>

                <Link :href="route('user.notifications.index')"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all"
                    :class="route().current('user.notifications.*') ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-zinc-800'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="text-sm font-bold">Notifications</span>
                    <span v-if="localUnreadCount > 0"
                        class="ml-auto bg-red-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">
                        {{ localUnreadCount > 9 ? '9+' : localUnreadCount }}
                    </span>
                </Link>
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t border-slate-200 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <span class="text-amber-600 font-bold">{{ user.name.charAt(0) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ user.name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
                    </div>
                    <button @click="logout" class="text-red-500 hover:text-red-700 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area with Fixed Header -->
        <div class="lg:pl-64">
            <!-- Fixed Header -->
            <header
                class="fixed top-0 right-0 left-0 lg:left-64 z-20 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border-b border-slate-200 dark:border-zinc-800">
                <div class="flex items-center justify-between px-3 sm:px-4 py-2 sm:py-3 gap-2">
                    <!-- Left section: Menu button + Breadcrumbs -->
                    <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                        <button @click="sidebarOpen = true" class="lg:hidden text-slate-500 flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Breadcrumbs - hidden on very small screens, show on sm and up -->
                        <div class="hidden sm:block flex-1 min-w-0">
                            <UserBreadcrumbs :items="breadcrumbs" />
                        </div>
                    </div>

                    <!-- Right section: Icons + Add Asset + Dark Mode Toggle -->
                    <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                        <!-- Dark Mode Toggle Button -->
                        <button @click="toggleDarkMode"
                            class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition"
                            :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                            <svg v-if="isDarkMode" class="w-4 h-4 sm:w-5 sm:h-5 text-slate-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="w-4 h-4 sm:w-5 sm:h-5 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <button @click="refreshData"
                            class="p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-500" :class="{ 'animate-spin': refreshing }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>

                        <!-- Notification Bell -->
                        <NotificationsBell :notifications="localNotifications" :unreadCount="localUnreadCount"
                            @notification-read="handleNotificationRead" @mark-all-read="handleMarkAllRead" />

                        <Link :href="route('user.watchlist.index')"
                            class="relative p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </Link>

                        <Link :href="route('user.assets.create')"
                            class="text-xs sm:text-sm text-amber-500 hover:text-amber-600 font-bold whitespace-nowrap">
                            + Add Asset
                        </Link>
                    </div>
                </div>

                <!-- Mobile Breadcrumbs - show only on very small screens -->
                <div class="sm:hidden px-3 pb-2 pt-0 overflow-x-auto">
                    <UserBreadcrumbs :items="breadcrumbs" />
                </div>
            </header>

            <!-- Spacer to offset fixed header -->
            <div class="h-[57px] sm:h-[65px] lg:h-[73px]"></div>

            <!-- Main Content -->
            <main class="p-3 sm:p-4 md:p-6 xs:mt-[40px]">
                <div v-if="$slots.header" class="mb-6">
                    <slot name="header" />
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const sidebarOpen = ref(false)
const refreshing = ref(false)

// Dark Mode State
const isDarkMode = ref(false)

// Initialize dark mode from localStorage or system preference
const initDarkMode = () => {
    const savedMode = localStorage.getItem('darkMode')
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches

    if (savedMode !== null) {
        isDarkMode.value = savedMode === 'true'
    } else {
        isDarkMode.value = systemPrefersDark
    }

    applyDarkMode()
}

// Apply dark mode to document
const applyDarkMode = () => {
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

// Toggle dark mode
const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value
    localStorage.setItem('darkMode', isDarkMode.value)
    applyDarkMode()
}

// Local reactive state for notifications
const localNotifications = ref([])
const localUnreadCount = ref(0)

// Initialize from props
const initNotifications = () => {
    localNotifications.value = page.props.notifications?.items || []
    localUnreadCount.value = page.props.notifications?.unread_count || 0
}

initNotifications()

watch(() => page.props.notifications, (newVal) => {
    if (newVal) {
        localNotifications.value = newVal.items || []
        localUnreadCount.value = newVal.unread_count || 0
    }
}, { deep: true })

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => []
    },
})

const breadcrumbs = computed(() => {
    let crumbs = [];

    if (page.props.breadcrumbs && page.props.breadcrumbs.length > 0) {
        crumbs = page.props.breadcrumbs;
    } else {
        const currentUrl = page.url;
        if (currentUrl === '/') {
            crumbs = [{ label: 'Home', route: 'welcome' }];
        } else {
            crumbs = [{ label: 'Home', route: 'welcome' }, { label: 'Current Page' }];
        }
    }

    return crumbs.filter(crumb => !crumb.label.toLowerCase().includes('user'));
});

const refreshData = () => {
    refreshing.value = true
    router.reload({ only: ['assets', 'watchlist', 'notifications'] })
    setTimeout(() => { refreshing.value = false }, 1000)
}

const logout = () => {
    router.post(route('logout'))
}

// Handle notification events from child component
const handleNotificationRead = (notificationId) => {
    const notification = localNotifications.value.find(n => n.id === notificationId)
    if (notification && !notification.is_read) {
        notification.is_read = true
        localUnreadCount.value = Math.max(0, localUnreadCount.value - 1)
    }
}

const handleMarkAllRead = () => {
    localNotifications.value.forEach(n => {
        if (!n.is_read) {
            n.is_read = true
        }
    })
    localUnreadCount.value = 0
}

// WebSocket connection - SINGLE instance
let echoChannel = null
let isListenerSetup = false

const setupRealtimeListener = () => {
    const userId = user.value?.id
    if (!userId || isListenerSetup) return

    if (!window.Echo) {
        setTimeout(setupRealtimeListener, 500)
        return
    }

    try {
        if (echoChannel) {
            try { echoChannel.stopListening() } catch (e) { }
        }

        echoChannel = window.Echo.channel(`user.${userId}`)
        echoChannel.listen('.alert.triggered', (data) => {
            const newNotification = {
                id: data.id || Date.now(),
                title: data.title || 'Price Alert',
                message: data.message || 'Rate alert triggered',
                type: data.type || 'alert',
                is_read: false,
                created_at: data.created_at || new Date().toISOString(),
                data: data.data || data
            }

            const exists = localNotifications.value.some(n => n.id === newNotification.id)
            if (!exists) {
                localNotifications.value.unshift(newNotification)
                localUnreadCount.value++

                if (Notification.permission === 'granted') {
                    new Notification(newNotification.title, {
                        body: newNotification.message,
                        icon: '/favicon.ico'
                    })
                }
            }
        })

        isListenerSetup = true
    } catch (error) {
        console.error('WebSocket setup error:', error)
    }
}

// Polling fallback (every 30 seconds)
let pollInterval = null

const startPolling = () => {
    if (pollInterval) clearInterval(pollInterval)
    pollInterval = setInterval(() => {
        router.reload({
            only: ['notifications'],
            preserveScroll: true,
            preserveState: true,
        })
    }, 30000)
}

onMounted(() => {
    // Initialize dark mode
    initDarkMode()

    if (Notification.permission === 'default') {
        Notification.requestPermission()
    }

    if (typeof window !== 'undefined' && !window.Echo) {
        import('laravel-echo').then(({ default: Echo }) => {
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST,
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
                forceTLS: false,
                enabledTransports: ['ws'],
            })
            setTimeout(setupRealtimeListener, 2000)
        })
    } else if (window.Echo) {
        setupRealtimeListener()
    }

    startPolling()
})

onUnmounted(() => {
    if (echoChannel) {
        try {
            echoChannel.stopListening()
            echoChannel = null
        } catch (e) { }
    }
    if (pollInterval) {
        clearInterval(pollInterval)
        pollInterval = null
    }
    isListenerSetup = false
})
</script>

<style scoped>
/* Mobile optimizations */
@media (max-width: 640px) {
    main {
        padding: 0.75rem;
    }
}

/* Dark mode transition */
* {
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
</style>