<template>
    <GuestLayout>

        <Head :title="meta.title">
            <meta name="description" :content="meta.description">
            <meta name="keywords" :content="meta.keywords">
            <link rel="canonical" :href="route('blog.index')" />
        </Head>

        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 transition-colors duration-300">

            <!-- Hero Section - Responsive -->
            <div
                class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                <div class="absolute inset-0 opacity-10">
                    <div
                        class="absolute top-20 left-10 w-48 sm:w-72 h-48 sm:h-72 bg-amber-500 rounded-full blur-3xl animate-pulse">
                    </div>
                    <div
                        class="absolute bottom-20 right-10 w-64 sm:w-96 h-64 sm:h-96 bg-emerald-500 rounded-full blur-3xl animate-pulse delay-1000">
                    </div>
                </div>

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-20">
                    <div class="text-center">


                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white mb-3 sm:mb-4 px-2">
                            Exchange Rate
                            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">
                                Blog
                            </span>
                        </h1>

                        <p class="text-slate-300 max-w-2xl mx-auto text-xs sm:text-sm md:text-base px-4">
                            Latest updates, market insights, and expert analysis on exchange rates and gold prices in
                            Myanmar.
                        </p>

                        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 mt-6 sm:mt-8">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-amber-400 animate-pulse"></div>
                                <span class="text-[10px] sm:text-xs text-slate-300">Market News</span>
                            </div>
                            <div class="w-px h-3 sm:h-4 bg-slate-600"></div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-[10px] sm:text-xs text-slate-300">Updated Daily</span>
                            </div>
                            <div class="w-px h-3 sm:h-4 bg-slate-600"></div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                                <span class="text-[10px] sm:text-xs text-slate-300">Expert Analysis</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute bottom-0 left-0 right-0">
                    <svg class="w-full h-8 sm:h-12 text-[#F7F7F5] dark:text-zinc-950" preserveAspectRatio="none"
                        viewBox="0 0 1440 54" fill="currentColor">
                        <path d="M0 22L120 16.7C240 11 480 0 720 0C960 0 1200 11 1320 16.7L1440 22V54H0V22Z" />
                    </svg>
                </div>
            </div>

            <!-- Blog Posts Grid - Responsive -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 pb-16 sm:pb-20">
                <div v-if="posts.data && posts.data.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
                    <Link v-for="post in posts.data" :key="post.id" :href="route('blog.show', post.slug)"
                        class="group bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 dark:hover:border-amber-700">

                        <!-- Featured Image -->
                        <div v-if="post.featured_image" class="relative h-40 sm:h-48 overflow-hidden">
                            <img :src="post.featured_image" :alt="post.title"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <div class="absolute bottom-3 left-3">
                                <span
                                    class="text-[8px] sm:text-[10px] font-bold text-white bg-amber-600 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full">
                                    {{ post.category || 'Market Update' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            <!-- Date -->
                            <div
                                class="flex items-center gap-1.5 sm:gap-2 text-[9px] sm:text-[10px] text-slate-400 mb-2 sm:mb-3 flex-wrap">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ formatDate(post.published_at) }}</span>
                                <span class="text-slate-500">•</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ formatReadTime(post.content) }}</span>
                            </div>

                            <!-- Title -->
                            <h2
                                class="text-base sm:text-xl font-bold text-slate-900 dark:text-white mb-2 sm:mb-3 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                {{ post.title }}
                            </h2>

                            <!-- Excerpt -->
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-3 sm:mb-4 line-clamp-3">
                                {{ post.excerpt || stripHtml(post.content).substring(0, 120) + '...' }}
                            </p>

                            <!-- Author & Read More -->
                            <div
                                class="flex items-center justify-between pt-2 sm:pt-3 border-t border-slate-100 dark:border-zinc-800">
                                <div class="flex items-center gap-1.5 sm:gap-2">
                                    <div
                                        class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/50 dark:to-orange-900/50 flex items-center justify-center">
                                        <span
                                            class="text-[8px] sm:text-[9px] font-bold text-amber-600 dark:text-amber-400">
                                            {{ (post.author || 'A').charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <span
                                        class="text-[9px] sm:text-[10px] font-medium text-slate-500 dark:text-slate-400">
                                        {{ post.author || 'Admin' }}
                                    </span>
                                </div>
                                <span
                                    class="text-[10px] sm:text-[11px] font-bold text-amber-600 dark:text-amber-400 group-hover:underline flex items-center gap-1">
                                    Read More
                                    <svg class="w-3 h-3 transition-transform group-hover:translate-x-1" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State - Responsive -->
                <div v-else
                    class="text-center py-12 sm:py-16 bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-zinc-800">
                    <div
                        class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-slate-300 dark:text-slate-600" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <p class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-[0.2em] mb-1 sm:mb-2">No
                        blog posts yet</p>
                    <p class="text-[9px] sm:text-[10px] text-slate-400">Check back soon for updates and insights</p>
                </div>

                <!-- Pagination - Responsive -->
                <div v-if="posts.links && posts.links.length > 3" class="mt-8 sm:mt-12">
                    <div class="flex flex-wrap justify-center gap-1.5 sm:gap-2">
                        <Link v-for="link in posts.links" :key="link.label" :href="link.url || '#'"
                            class="px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-sm font-medium transition-all duration-200"
                            :class="link.active
                                ? 'bg-amber-600 text-white shadow-md'
                                : 'bg-white dark:bg-zinc-900 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-zinc-800 hover:bg-amber-50 dark:hover:bg-zinc-800 hover:border-amber-300 dark:hover:border-amber-700'"
                            v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, Head } from '@inertiajs/vue3'

defineProps({
    posts: Object,
    meta: Object
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatReadTime = (content) => {
    if (!content) return '2 min read'
    const wordCount = content.replace(/<[^>]*>/g, '').split(/\s+/).length
    const readTime = Math.ceil(wordCount / 200)
    return `${readTime} min read`
}

const stripHtml = (html) => {
    if (!html) return ''
    const tmp = document.createElement('DIV')
    tmp.innerHTML = html
    return tmp.textContent || tmp.innerText || ''
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 0.3;
        transform: scale(1);
    }

    50% {
        opacity: 0.6;
        transform: scale(1.05);
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-1000 {
    animation-delay: 1s;
}
</style>