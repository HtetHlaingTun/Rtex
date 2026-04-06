<template>
    <UserLayout title="Notifications">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900">Notifications</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Stay updated with your alerts and system announcements
                    </p>
                </div>
                <div class="flex gap-2">
                    <button v-if="unreadCount > 0" @click="markAllAsRead"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs sm:text-sm font-bold transition">
                        Mark All Read ({{ unreadCount }})
                    </button>
                    <button @click="clearAll" v-if="notificationsData.length > 0"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs sm:text-sm font-bold transition">
                        Clear All
                    </button>
                </div>
            </div>
        </template>


        <!-- Filter Tabs - Responsive -->
        <div class="flex gap-1.5 sm:gap-2 mb-4">
            <button @click="setFilter('all')"
                :class="filter === 'all' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400'"
                class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-bold transition">
                All
                <span class="ml-1 text-[10px] opacity-75">({{ totalCount }})</span>
            </button>
            <button @click="setFilter('unread')"
                :class="filter === 'unread' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400'"
                class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-bold transition">
                Unread
                <span v-if="unreadCount > 0"
                    class="ml-1 px-1.5 py-0.5 bg-red-500 text-white rounded-full text-[10px] sm:text-xs">
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </button>
            <button @click="setFilter('read')"
                :class="filter === 'read' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-400'"
                class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-bold transition">
                Read
                <span class="ml-1 text-[10px] opacity-75">({{ readCount }})</span>
            </button>
        </div>

        <!-- Notifications List - Responsive -->
        <div v-if="notificationsData.length > 0" class="space-y-2 sm:space-y-3">
            <div v-for="notification in notificationsData" :key="notification.id"
                class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-3 sm:p-4 hover:shadow-md transition"
                :class="{ 'border-l-2 sm:border-l-4 border-l-amber-500': !notification.is_read }">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex gap-2 sm:gap-3 flex-1 min-w-0">
                        <!-- Icon - Responsive size -->
                        <div class="flex-shrink-0">
                            <div v-if="notification.type === 'alert'"
                                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <div v-else
                                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content - Responsive text -->
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base truncate">{{
                                notification.title }}</p>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-0.5 sm:mt-1 break-words">
                                {{ notification.message }}</p>
                            <p class="text-[10px] sm:text-xs text-slate-400 mt-1 sm:mt-2">{{
                                formatDate(notification.created_at)
                                }}</p>
                        </div>
                    </div>

                    <!-- Action buttons - Responsive size -->
                    <div class="flex gap-1 sm:gap-2 flex-shrink-0">
                        <button v-if="!notification.is_read" @click="markAsRead(notification.id)"
                            class="text-blue-500 hover:text-blue-600 transition p-1 sm:p-1.5" title="Mark as read">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <button @click="deleteNotification(notification.id)"
                            class="text-red-400 hover:text-red-600 transition p-1 sm:p-1.5" title="Delete">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State - Responsive -->
        <div v-else
            class="text-center py-8 sm:py-12 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800">
            <div
                class="w-12 h-12 sm:w-16 sm:h-16 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <p class="text-slate-500 font-medium text-sm sm:text-base">No notifications</p>
            <p class="text-[10px] sm:text-xs text-slate-400 mt-1">When alerts trigger, they will appear here</p>
        </div>

        <!-- Pagination - Responsive -->
        <div v-if="notificationsData.length > 0 && notifications.last_page > 1"
            class="mt-4 flex justify-center gap-1 sm:gap-2">
            <Link v-if="notifications.prev_page_url" :href="notifications.prev_page_url"
                class="px-2 sm:px-3 py-1 bg-slate-100 dark:bg-zinc-800 rounded-lg text-xs sm:text-sm text-slate-600 hover:bg-slate-200 transition">
                ← Prev
            </Link>
            <span class="px-2 sm:px-3 py-1 text-xs sm:text-sm text-slate-500">
                {{ notifications.current_page }} / {{ notifications.last_page }}
            </span>
            <Link v-if="notifications.next_page_url" :href="notifications.next_page_url"
                class="px-2 sm:px-3 py-1 bg-slate-100 dark:bg-zinc-800 rounded-lg text-xs sm:text-sm text-slate-600 hover:bg-slate-200 transition">
                Next →
            </Link>
        </div>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'

const props = defineProps({
    notifications: { type: Object, default: () => ({ data: [] }) },
    unreadCount: { type: Number, default: 0 },
    filter: { type: String, default: 'all' },
})

// Debug: Log props on mount
onMounted(() => {

})

// Extract the data array from the paginator object
const notificationsData = computed(() => props.notifications?.data || [])

// Total count from paginator
const totalCount = computed(() => props.notifications?.total || 0)

// Calculate read count
const readCount = computed(() => totalCount.value - props.unreadCount)

const formatDate = (timestamp) => {
    if (!timestamp) return ''
    const date = new Date(timestamp)
    return date.toLocaleString()
}

const setFilter = (newFilter) => {
    router.get(route('user.notifications.index', { filter: newFilter }), {}, {
        preserveScroll: true,
    })
}

const markAsRead = (id) => {
    router.patch(route('user.notifications.mark-read', id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            console.log('Notification marked as read')
        }
    })
}

const markAllAsRead = () => {
    if (confirm(`Mark all ${props.unreadCount} unread notifications as read?`)) {
        router.post(route('user.notifications.mark-all-read'), {}, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('All notifications marked as read')
            }
        })
    }
}

const deleteNotification = (id) => {
    if (confirm('Delete this notification?')) {
        router.delete(route('user.notifications.destroy', id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('Notification deleted')
            }
        })
    }
}

const clearAll = () => {
    if (confirm('⚠️ WARNING: This will permanently delete ALL your notifications. This action cannot be undone. Are you sure?')) {
        const url = route('user.notifications.clear-all')
        console.log('DELETE URL:', url)

        router.delete(url, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('All notifications cleared')
                alert('All notifications have been cleared.')
                // Reload the page to refresh the list
                window.location.reload()
            },
            onError: (errors) => {
                console.error('Failed to clear notifications:', errors)
                alert('Failed to clear notifications. Please try again.')
            }
        })
    }
}
</script>

<style scoped>
/* Mobile optimizations */
@media (max-width: 640px) {
    .break-words {
        word-break: break-word;
    }
}
</style>