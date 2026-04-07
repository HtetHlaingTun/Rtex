<template>
    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="blogBreadcrumbs" />
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700 flex justify-between items-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Blog Post</h1>
                        <div class="flex gap-2">
                            <Link :href="route('blog.show', post.slug)" target="_blank"
                                class="text-blue-600 hover:text-blue-800 text-sm">
                                View Public Post →
                            </Link>
                        </div>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title *
                            </label>
                            <input type="text" v-model="form.title" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                            <p class="text-xs text-gray-500 mt-1">
                                Slug will be: {{ generateSlug(form.title) }}
                            </p>
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Content * (HTML supported)
                            </label>
                            <textarea v-model="form.content" rows="15" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white font-mono text-sm"></textarea>
                            <div class="text-xs text-gray-500 mt-1 flex justify-between">
                                <span>HTML formatting supported (h1, h2, p, ul, li, etc.)</span>
                                <span class="text-gray-400">{{ form.content.length }} characters</span>
                            </div>
                        </div>

                        <!-- Excerpt -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Excerpt (optional)
                            </label>
                            <textarea v-model="form.excerpt" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                Leave empty to auto-generate from content (first 150 characters)
                            </p>
                        </div>

                        <!-- Meta Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Title (optional - for SEO)
                            </label>
                            <input type="text" v-model="form.meta_title" maxlength="60"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                            <p class="text-xs text-gray-500 mt-1">
                                {{ form.meta_title.length }}/60 characters. Leave empty to use title.
                            </p>
                        </div>

                        <!-- Meta Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Description (optional - for SEO)
                            </label>
                            <textarea v-model="form.meta_description" rows="2" maxlength="160"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white"></textarea>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ form.meta_description.length }}/160 characters. Leave empty to use excerpt.
                            </p>
                        </div>

                        <!-- Publish Status -->
                        <div class="flex items-center gap-3">
                            <input type="checkbox" v-model="form.is_published" id="published"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <label for="published" class="text-sm text-gray-700 dark:text-gray-300">
                                Published
                            </label>
                        </div>

                        <!-- Current Status Info -->
                        <div v-if="post.is_published" class="text-xs text-green-600 dark:text-green-400">
                            ✓ Published on: {{ formatDate(post.published_at) }}
                        </div>
                        <div v-else class="text-xs text-yellow-600 dark:text-yellow-400">
                            ⚠️ This post is currently a draft (not visible to public)
                        </div>

                        <!-- Preview Section -->
                        <div class="border-t border-gray-200 dark:border-zinc-700 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Preview</h3>
                            <div class="bg-gray-50 dark:bg-zinc-900 rounded-lg p-4">
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ form.title || 'Untitled' }}</h4>
                                <div class="text-sm text-gray-500 mt-2" v-html="form.excerpt || 'No excerpt'"></div>
                                <div class="text-xs text-gray-400 mt-2">
                                    Slug: /blog/{{ generateSlug(form.title) }}
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-zinc-700">
                            <button type="submit" :disabled="form.processing"
                                class="bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700 disabled:opacity-50 transition">
                                {{ form.processing ? 'Saving...' : 'Update Post' }}
                            </button>
                            <Link :href="route('admin.blog.index')"
                                class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                                Cancel
                            </Link>
                            <button type="button" @click="deletePost"
                                class="ml-auto bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                                Delete Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    post: Object
})

const form = useForm({
    title: props.post.title,
    content: props.post.content,
    excerpt: props.post.excerpt || '',
    meta_title: props.post.meta_title || '',
    meta_description: props.post.meta_description || '',
    is_published: props.post.is_published || false
})

const blogBreadcrumbs = [
    { label: 'Blog', href: route('admin.blog.index') },
    { label: 'Edit', },

];

const generateSlug = (title) => {
    if (!title) return ''
    return title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleString()
}

const submitForm = () => {
    form.put(route('admin.blog.update', props.post.id), {
        onSuccess: () => {
            // Redirect to blog index on success
            router.get(route('admin.blog.index'))
        }
    })
}

const deletePost = () => {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        router.delete(route('admin.blog.destroy', props.post.id), {
            onSuccess: () => {
                router.get(route('admin.blog.index'))
            }
        })
    }
}
</script>