<template>
    <div class="relative">
        <button @click="toggleDropdown"
            class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div v-if="isOpen"
            class="absolute right-0 mt-2 w-80 bg-white dark:bg-zinc-900 rounded-xl shadow-lg border border-slate-200 dark:border-zinc-700 z-50 overflow-hidden">
            <div class="p-3 border-b border-slate-200 dark:border-zinc-700 flex justify-between items-center">
                <h3 class="font-bold text-slate-900 dark:text-white">Notifications</h3>
                <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-blue-500 hover:text-blue-600">
                    Mark all read
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-4 text-center text-slate-400 text-sm">
                    No notifications
                </div>
                <div v-for="notification in notifications.slice(0, 10)" :key="notification.id"
                    @click="markAsRead(notification.id)"
                    class="p-3 border-b border-slate-100 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition cursor-pointer"
                    :class="{ 'bg-amber-50 dark:bg-amber-950/20': !notification.is_read }">
                    <div class="flex items-start gap-2">
                        <div class="flex-shrink-0">
                            <div
                                class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                                {{ notification.title }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2">
                                {{ notification.message }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ formatTime(notification.created_at) }}
                            </p>
                        </div>
                        <div v-if="!notification.is_read" class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0 mt-2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-2 border-t border-slate-200 dark:border-zinc-700 text-center">
                <Link :href="route('user.notifications.index')" class="text-xs text-amber-500 hover:text-amber-600">
                    View all notifications →
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
    notifications: { type: Array, default: () => [] },
    unreadCount: { type: Number, default: 0 },
})

const emit = defineEmits(['notification-read', 'mark-all-read'])

const isOpen = ref(false)

const formatTime = (timestamp) => {
    if (!timestamp) return ''
    const date = new Date(timestamp)
    const now = new Date()
    const diff = now - date

    if (diff < 60000) return 'Just now'
    if (diff < 3600000) return `${Math.floor(diff / 60000)} min ago`
    if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`
    return date.toLocaleDateString()
}

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
}

const markAsRead = (id) => {
    router.patch(route('user.notifications.mark-read', id), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('notification-read', id)
        }
    })
}

const markAllAsRead = () => {
    router.post(route('user.notifications.mark-all-read'), {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('mark-all-read')
        }
    })
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (isOpen.value && !event.target.closest('.relative')) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>