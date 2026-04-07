<template>
    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="contactBreadcrumbs" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Contact Messages
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manage customer inquiries and support messages
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="refreshMessages"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                            Refresh
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Messages</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ contacts.total || 0 }}
                                </p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Unread</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ unreadCount || 0 }}</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Replied</p>
                                <p class="text-2xl font-bold text-green-600">{{ repliedCount || 0 }}</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="mb-6">
                    <input type="text" v-model="searchTerm" @input="searchMessages"
                        placeholder="Search by name, email, or subject..."
                        class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white" />
                </div>

                <!-- Messages Table -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subject</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Received</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                <tr v-for="contact in contacts.data" :key="contact.id"
                                    :class="{ 'bg-yellow-50 dark:bg-yellow-900/10': !contact.is_read }">
                                    <td class="px-6 py-4">
                                        <span v-if="!contact.is_read"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            New
                                        </span>
                                        <span v-else-if="contact.is_replied"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Replied
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Read
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ contact.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ contact.email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate">
                                            {{ contact.subject }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">
                                            {{ formatDate(contact.created_at) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-3">
                                            <Link :href="route('admin.contacts.show', contact.id)"
                                                class="text-amber-600 hover:text-amber-900 transition">
                                                View
                                            </Link>
                                            <button @click="deleteContact(contact.id)"
                                                class="text-red-600 hover:text-red-900 transition">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="contacts.data && contacts.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No contact messages found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Showing {{ contacts.from || 0 }} to {{ contacts.to || 0 }} of {{ contacts.total || 0 }}
                            results
                        </div>
                        <div class="flex space-x-2">
                            <button @click="goToPage(contacts.current_page - 1)" :disabled="!contacts.prev_page_url"
                                class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-zinc-700">
                                Previous
                            </button>
                            <span class="px-4 py-2 bg-amber-600 text-white rounded-lg">
                                {{ contacts.current_page }}
                            </span>
                            <button @click="goToPage(contacts.current_page + 1)" :disabled="!contacts.next_page_url"
                                class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 dark:hover:bg-zinc-700">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    contacts: Object,
    unreadCount: Number
})
const contactBreadcrumbs = [
    { label: 'Feedback', href: route('admin.contacts.index') },

];

const searchTerm = ref('')
let searchTimeout = null

const repliedCount = computed(() => {
    if (!props.contacts.data) return 0
    return props.contacts.data.filter(c => c.is_replied).length
})
const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleString() // Shows full date and time
}

// FIXED: Correct route parameter
const deleteContact = (id) => {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        router.delete(route('admin.contacts.destroy', { contact: id }), {
            preserveScroll: true,
            onSuccess: () => {
                // Optionally show success message
                console.log('Contact deleted successfully')
            },
            onError: (errors) => {
                console.error('Delete failed:', errors)
                alert('Failed to delete message. Please try again.')
            }
        })
    }
}

const refreshMessages = () => {
    router.reload({ only: ['contacts', 'unreadCount'] })
}

const searchMessages = () => {
    if (searchTimeout) clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        router.get(route('admin.contacts.index'), { search: searchTerm.value }, {
            preserveState: true,
            preserveScroll: true
        })
    }, 300)
}

const goToPage = (page) => {
    if (page < 1) return
    router.get(route('admin.contacts.index'), { page, search: searchTerm.value }, {
        preserveState: true,
        preserveScroll: true
    })
}
</script>