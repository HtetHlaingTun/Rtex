<template>
    <AdminLayout>
        <template #breadcrumbs>
            <Breadcrumbs :items="blogBreadcrumbs" />
        </template>
        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Blog Post</h1>
                    </div>

                    <form @submit.prevent="submitForm" class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title
                                *</label>
                            <input type="text" v-model="form.title" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content
                                *</label>
                            <textarea v-model="form.content" rows="15" required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white font-mono"></textarea>
                            <p class="text-xs text-gray-500 mt-1">HTML formatting supported</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Excerpt
                                (optional)</label>
                            <textarea v-model="form.excerpt" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate from content</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta Title
                                (optional)</label>
                            <input type="text" v-model="form.meta_title"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Meta
                                Description (optional)</label>
                            <textarea v-model="form.meta_description" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white"></textarea>
                        </div>

                        <div class="flex items-center gap-3">
                            <input type="checkbox" v-model="form.is_published" id="published"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <label for="published" class="text-sm text-gray-700 dark:text-gray-300">
                                Publish immediately
                            </label>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" :disabled="form.processing"
                                class="bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700 disabled:opacity-50">
                                {{ form.processing ? 'Creating...' : 'Create Post' }}
                            </button>
                            <Link :href="route('admin.blog.index')"
                                class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                                Cancel
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    title: '',
    content: '',
    excerpt: '',
    meta_title: '',
    meta_description: '',
    is_published: false
})
const blogBreadcrumbs = [
    { label: 'Blog', href: route('admin.blog.index') },
    { label: 'New Post' },

];
const submitForm = () => {
    form.post(route('admin.blog.store'))
}
</script>