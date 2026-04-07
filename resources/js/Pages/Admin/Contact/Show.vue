<template>
    <AdminLayout>
        <div class="py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <div class="flex items-center gap-3">
                            <Link :href="route('admin.contacts.index')"
                                class="text-amber-600 hover:text-amber-700 flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Messages
                            </Link>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">Message Details</h1>
                    </div>
                    <div class="flex gap-3">
                        <button @click="deleteContact"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>

                <!-- Message Card -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg overflow-hidden">
                    <!-- Status Banner -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-900">
                        <div class="flex flex-wrap gap-3">
                            <span
                                :class="contact.is_read ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                class="px-3 py-1 rounded-full text-sm font-semibold">
                                {{ contact.is_read ? '✓ Read' : '● Unread' }}
                            </span>
                            <span v-if="contact.is_replied"
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                ✓ Replied
                            </span>
                            <span v-else class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-semibold">
                                ⏳ Not Replied Yet
                            </span>
                        </div>
                    </div>

                    <!-- Sender Information -->
                    <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    From
                                </label>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ contact.name }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Email
                                </label>
                                <div class="flex items-center gap-2">
                                    <a :href="`mailto:${contact.email}`"
                                        class="text-lg text-amber-600 hover:text-amber-800 dark:text-amber-400">
                                        {{ contact.email }}
                                    </a>
                                    <button @click="copyEmail" class="text-gray-400 hover:text-gray-600 transition"
                                        title="Copy email">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Subject
                                </label>
                                <p class="text-lg text-gray-900 dark:text-white">
                                    {{ contact.subject }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
                                    Received
                                </label>
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatFullDate(contact.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
                            Message
                        </label>
                        <div class="bg-gray-50 dark:bg-zinc-900 rounded-lg p-5">
                            <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap leading-relaxed">
                                {{ contact.message }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                        <div class="flex flex-wrap gap-3">
                            <a :href="`mailto:${contact.email}?subject=Re: ${contact.subject}`" target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Reply via Email
                            </a>

                            <button v-if="!contact.is_replied" @click="markAsReplied"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Mark as Replied
                            </button>
                        </div>
                    </div>

                    <!-- Reply Template -->
                    <div class="p-6 bg-gray-50 dark:bg-zinc-900">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Reply Template</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            Copy this template and customize it for your reply:
                        </p>
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-gray-200 dark:border-zinc-700">
                            <pre
                                class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-sans font-mono">{{ replyTemplate }}</pre>
                            <button @click="copyTemplate"
                                class="mt-3 text-sm text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                Copy Template
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>Message ID: {{ contact.id }}</p>
                    <p>Last updated: {{ formatFullDate(contact.updated_at) }}</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const contact = page.props.contact

const replyTemplate = computed(() => {
    return `Dear ${contact.name},

Thank you for contacting MMRatePro.

We have received your message regarding "${contact.subject}" and will get back to you within 24-48 hours.

Best regards,
MMRatePro Support Team
support@luckeymm.online`
})

const formatFullDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

// FIXED: Correct route parameter
const deleteContact = () => {
    if (confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        router.delete(route('admin.contacts.destroy', { contact: contact.id }), {
            onSuccess: () => {
                // Redirect to index after successful delete
                router.get(route('admin.contacts.index'))
            },
            onError: (errors) => {
                console.error('Delete failed:', errors)
                alert('Failed to delete message. Please try again.')
            }
        })
    }
}

// FIXED: Correct route parameter
const markAsReplied = () => {
    router.patch(route('admin.contacts.mark-replied', { contact: contact.id }), {
        onSuccess: () => {
            router.reload()
        },
        onError: (errors) => {
            console.error('Mark as replied failed:', errors)
            alert('Failed to update status. Please try again.')
        }
    })
}

const copyEmail = () => {
    navigator.clipboard.writeText(contact.email)
    alert('Email address copied to clipboard!')
}

const copyTemplate = () => {
    navigator.clipboard.writeText(replyTemplate.value)
    alert('Reply template copied to clipboard!')
}
</script>