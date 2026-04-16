<template>

    <Head>
        <title>{{ meta.title }} | MMRatePro</title>
        <meta name="description" :content="meta.description">
        <meta property="og:title" :content="meta.title">
        <meta property="og:description" :content="meta.description">
        <meta property="og:image" :content="meta.image">
        <meta property="og:url" :content="meta.url">
    </Head>

    <GuestLayout>
        <div class="min-h-screen bg-[#F7F7F5] dark:bg-zinc-950 py-8 sm:py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Back Button - Responsive -->
                <Link :href="route('blog.index')"
                    class="inline-flex items-center gap-1 text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 mb-4 sm:mb-6 text-sm sm:text-base transition-colors group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Blog
                </Link>

                <!-- Blog Post Card -->
                <article
                    class="bg-white dark:bg-zinc-900 rounded-xl sm:rounded-2xl shadow-lg overflow-hidden border border-slate-200 dark:border-zinc-800">

                    <!-- Featured Image - Responsive -->
                    <div v-if="post.featured_image" class="relative overflow-hidden bg-slate-100 dark:bg-zinc-800">
                        <img :src="featuredImageUrl" :alt="post.title"
                            class="w-full h-48 sm:h-64 md:h-96 object-cover transition-transform duration-500 hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>

                    <div class="p-5 sm:p-6 md:p-8">
                        <!-- Meta Info - Responsive -->
                        <div
                            class="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-sm text-slate-500 dark:text-slate-400 mb-4 sm:mb-6">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ formatDate(post.published_at) }}</span>
                            </div>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>By {{ post.author || 'Admin' }}</span>
                            </div>
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>{{ post.views || 0 }} views</span>
                            </div>
                        </div>

                        <!-- Title - Responsive -->
                        <h1
                            class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 dark:text-white mb-4 sm:mb-6 leading-tight">
                            {{ post.title }}
                        </h1>

                        <!-- Category Badge (if exists) -->
                        <div v-if="post.category" class="mb-4 sm:mb-6">
                            <span
                                class="inline-block px-2.5 py-1 text-[10px] sm:text-xs font-bold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                {{ post.category }}
                            </span>
                        </div>

                        <!-- Content - Responsive Typography -->
                        <div class="prose prose-sm sm:prose-base md:prose-lg dark:prose-invert max-w-none"
                            v-html="post.content">
                        </div>

                        <!-- Share Section - Responsive -->
                        <div class="border-t border-slate-200 dark:border-zinc-800 mt-8 pt-8">
                            <h3
                                class="text-sm sm:text-base font-bold text-slate-800 dark:text-white mb-3 sm:mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                Share this article
                            </h3>
                            <div class="flex flex-wrap gap-2 sm:gap-3">
                                <!-- Facebook Share -->
                                <button @click="shareOnFacebook"
                                    class="flex items-center gap-1.5 sm:gap-2 bg-[#1877f2] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-sm font-medium hover:bg-[#1877f2]/90 transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                    <span class="hidden xs:inline">Facebook</span>
                                </button>

                                <!-- Twitter/X Share -->
                                <button @click="shareOnTwitter"
                                    class="flex items-center gap-1.5 sm:gap-2 bg-[#1da1f2] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-sm font-medium hover:bg-[#1da1f2]/90 transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                    </svg>
                                    <span class="hidden xs:inline">Twitter</span>
                                </button>

                                <!-- LinkedIn Share -->
                                <button @click="shareOnLinkedIn"
                                    class="flex items-center gap-1.5 sm:gap-2 bg-[#0a66c2] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-sm font-medium hover:bg-[#0a66c2]/90 transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451c.979 0 1.771-.773 1.771-1.729V1.729C24 .774 23.227 0 22.225 0z" />
                                    </svg>
                                    <span class="hidden xs:inline">LinkedIn</span>
                                </button>

                                <!-- WhatsApp Share -->
                                <button @click="shareOnWhatsApp"
                                    class="flex items-center gap-1.5 sm:gap-2 bg-[#25d366] text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-sm font-medium hover:bg-[#25d366]/90 transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                    </svg>
                                    <span class="hidden xs:inline">WhatsApp</span>
                                </button>

                                <!-- Copy Link Button -->
                                <button @click="copyLink"
                                    class="flex items-center gap-1.5 sm:gap-2 bg-slate-600 text-white px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-[11px] sm:text-sm font-medium hover:bg-slate-700 transition-all active:scale-95">
                                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                    <span>{{ copied ? 'Copied!' : 'Copy Link' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Related Posts - Responsive -->
                <div v-if="relatedPosts && relatedPosts.length > 0" class="mt-10 sm:mt-12">
                    <h2
                        class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-white mb-4 sm:mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        Related Articles
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                        <Link v-for="related in relatedPosts" :key="related.id" :href="route('blog.show', related.slug)"
                            class="group bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4 sm:p-5 hover:shadow-lg transition-all hover:-translate-y-1 hover:border-amber-300 dark:hover:border-amber-700">
                            <h3
                                class="text-sm sm:text-base font-bold text-slate-800 dark:text-white mb-2 line-clamp-2 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                {{ related.title }}
                            </h3>
                            <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400">
                                {{ formatDate(related.published_at) }}
                            </p>
                            <div
                                class="mt-3 flex items-center gap-1 text-amber-600 text-[10px] sm:text-xs font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                                Read more
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Link, Head } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    post: Object,
    relatedPosts: Array,
    meta: Object
})

const copied = ref(false)

// Page title and description for meta tags
const pageTitle = computed(() => props.post?.meta_title || props.post?.title || 'MMRatePro')
const pageDescription = computed(() => {
    const desc = props.post?.meta_description || props.post?.excerpt
    if (desc && desc.length > 160) {
        return desc.substring(0, 157) + '...'
    }
    return desc || 'Real-time exchange rates and gold prices for Myanmar'
})

const getBaseUrl = () => {
    if (window.location.hostname === 'localhost') {
        return 'http://localhost:8000'
    }
    return 'https://luckeymm.online'
}

const featuredImageUrl = computed(() => {
    const baseUrl = getBaseUrl()
    if (props.post?.featured_image) {
        if (props.post.featured_image.startsWith('http')) {
            return props.post.featured_image
        }
        return `${baseUrl}/${props.post.featured_image.replace(/^\/+/, '')}`
    }
    return `${baseUrl}/default-og-image.jpg`
})

// Current URL for sharing
const currentUrl = computed(() => {
    return `https://luckeymm.online/blog/${props.post?.slug}`
})

// Encoded values for sharing
const encodedUrl = computed(() => encodeURIComponent(currentUrl.value))
const encodedTitle = computed(() => encodeURIComponent(pageTitle.value))

// Share functions
const shareOnFacebook = () => {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl.value}`, '_blank', 'width=600,height=400,noopener,noreferrer')
}

const shareOnTwitter = () => {
    window.open(`https://twitter.com/intent/tweet?url=${encodedUrl.value}&text=${encodedTitle.value}`, '_blank', 'width=600,height=400,noopener,noreferrer')
}

const shareOnLinkedIn = () => {
    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl.value}`, '_blank', 'width=600,height=400,noopener,noreferrer')
}

const shareOnWhatsApp = () => {
    window.open(`https://wa.me/?text=${encodedTitle.value}%20${encodedUrl.value}`, '_blank', 'width=600,height=400,noopener,noreferrer')
}

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(currentUrl.value)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy:', err)
        const textarea = document.createElement('textarea')
        textarea.value = currentUrl.value
        document.body.appendChild(textarea)
        textarea.select()
        document.execCommand('copy')
        document.body.removeChild(textarea)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    }
}

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Prose styling for blog content - Responsive */
.prose {
    max-width: none;
    color: #334155;
}

.dark .prose {
    color: #e2e8f0;
}

.prose h1 {
    font-size: 1.75em;
    margin-top: 0.5em;
    margin-bottom: 0.5em;
    font-weight: 800;
}

.prose h2 {
    font-size: 1.5em;
    margin-top: 1em;
    margin-bottom: 0.5em;
    font-weight: 700;
}

.prose h3 {
    font-size: 1.25em;
    margin-top: 0.8em;
    margin-bottom: 0.4em;
    font-weight: 600;
}

.prose p {
    margin-bottom: 1em;
    line-height: 1.6;
}

.prose ul,
.prose ol {
    margin: 0.5em 0 1em 1.5em;
}

.prose li {
    margin-bottom: 0.25em;
}

.prose a {
    color: #f59e0b;
    text-decoration: underline;
}

.prose a:hover {
    color: #d97706;
}

.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1em 0;
}

.prose blockquote {
    border-left: 4px solid #f59e0b;
    padding-left: 1em;
    margin: 1em 0;
    font-style: italic;
    color: #64748b;
}

.dark .prose blockquote {
    color: #94a3b8;
}

@media (max-width: 640px) {
    .prose h1 {
        font-size: 1.5em;
    }

    .prose h2 {
        font-size: 1.3em;
    }

    .prose h3 {
        font-size: 1.1em;
    }

    .prose p {
        font-size: 0.9em;
    }
}
</style>