<template>
    <GuestLayout>

        <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Exchange Rate Blog
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400">
                        Latest updates, market insights, and expert analysis
                    </p>
                </div>

                <!-- Blog Posts Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="post in posts.data" :key="post.id" :href="route('blog.show', post.slug)"
                        class="bg-white dark:bg-zinc-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">
                        <div class="p-6">
                            <div class="text-sm text-amber-600 dark:text-amber-400 mb-2">
                                {{ formatDate(post.published_at) }}
                            </div>
                            <h2
                                class="text-xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-amber-600 transition">
                                {{ post.title }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                {{ post.excerpt }}
                            </p>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-500">
                                    By {{ post.author ? post.author : 'Admin' }}
                                </span>
                                <span class="text-amber-600 group-hover:underline">
                                    Read More →
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Pagination -->
                <div class="mt-12" v-if="posts.links && posts.links.length > 3">
                    <div class="flex justify-center space-x-2">
                        <Link v-for="link in posts.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
                            class="px-4 py-2 rounded-lg transition"
                            :class="link.active
                                ? 'bg-amber-600 text-white'
                                : 'bg-white dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-amber-100 dark:hover:bg-zinc-700'"
                            :disabled="!link.url" />
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    posts: Object,
    meta: Object
})



const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>