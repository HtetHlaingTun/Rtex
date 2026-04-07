<template>
    <AdminLayout>

        <template #breadcrumbs>
            <Breadcrumbs :items="blogBreadcrumbs" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Blog Posts</h1>
                    <Link :href="route('admin.blog.create')"
                        class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700">
                        + New Post
                    </Link>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
                    <!-- Desktop Table View (hidden on mobile) -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-900">
                                <tr>
                                    <th
                                        class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Title</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Published</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Views</th>
                                    <th
                                        class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                <tr v-for="post in posts.data" :key="post.id"
                                    :class="{ 'bg-yellow-50 dark:bg-yellow-900/10': !post.is_published }">
                                    <td class="px-4 sm:px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ post.title }}
                                        </div>
                                        <div class="text-xs text-gray-500 truncate max-w-[200px]">
                                            {{ post.slug }}
                                        </div>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4">
                                        <span :class="post.is_published
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'"
                                            class="px-2 py-1 rounded-full text-xs font-semibold">
                                            {{ post.is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(post.published_at) || '—' }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ post.views || 0 }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 text-sm space-x-2">
                                        <Link :href="route('admin.blog.edit', post.id)"
                                            class="text-amber-600 hover:text-amber-900 transition">
                                            Edit
                                        </Link>
                                        <button @click="togglePublish(post)"
                                            class="text-blue-600 hover:text-blue-900 transition">
                                            {{ post.is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                        <button @click="deletePost(post.id)"
                                            class="text-red-600 hover:text-red-900 transition">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="posts.data && posts.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No blog posts found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View (visible on small screens) -->
                    <div class="md:hidden divide-y divide-gray-200 dark:divide-zinc-700">
                        <div v-for="post in posts.data" :key="post.id" class="p-4 space-y-3"
                            :class="{ 'bg-yellow-50 dark:bg-yellow-900/10': !post.is_published }">

                            <!-- Title and Status Row -->
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ post.title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1 break-all">
                                        {{ post.slug }}
                                    </p>
                                </div>
                                <span :class="post.is_published
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'"
                                    class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap ml-2">
                                    {{ post.is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>

                            <!-- Metadata Row -->
                            <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ formatDate(post.published_at) || 'Not published' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>{{ post.views || 0 }} views</span>
                                </div>
                            </div>

                            <!-- Action Buttons Row -->
                            <div class="flex flex-wrap gap-3 pt-2">
                                <Link :href="route('admin.blog.edit', post.id)"
                                    class="text-amber-600 hover:text-amber-900 text-sm font-medium transition flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </Link>
                                <button @click="togglePublish(post)"
                                    class="text-blue-600 hover:text-blue-900 text-sm font-medium transition flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ post.is_published ? 'Unpublish' : 'Publish' }}
                                </button>
                                <button @click="deletePost(post.id)"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium transition flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Empty State for Mobile -->
                        <div v-if="posts.data && posts.data.length === 0" class="p-8 text-center text-gray-500">
                            No blog posts found
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <Paginations :links="posts.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    posts: Object
})

const blogBreadcrumbs = [
    { label: 'Blog', href: route('admin.blog.index') },

];

const formatDate = (date) => {
    if (!date) return null
    return new Date(date).toLocaleDateString()
}

const togglePublish = (post) => {
    router.patch(route('admin.blog.toggle-publish', post.id), {
        preserveScroll: true
    })
}

const deletePost = (id) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('admin.blog.destroy', id))
    }
}
</script>