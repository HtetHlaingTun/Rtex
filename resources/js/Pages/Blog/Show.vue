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
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link :href="route('blog.index')"
                    class="inline-flex items-center text-amber-600 hover:text-amber-700 mb-6">
                    ← Back to Blog
                </Link>

                <!-- Blog Post -->
                <article class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg overflow-hidden">
                    <!-- Featured Image -->
                    <img v-if="post.featured_image" :src="featuredImageUrl" :alt="post.title"
                        class="w-full h-64 md:h-96 object-cover">

                    <div class="p-6 md:p-8">
                        <!-- Meta -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-500 dark:text-gray-400 mb-4">
                            <div class="flex flex-wrap gap-3">
                                <span>{{ formatDate(post.published_at) }}</span>
                                <span>By {{ post.author || 'Admin' }}</span>
                                <span>{{ post.views || 0 }} views</span>
                            </div>
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                            {{ post.title }}
                        </h1>

                        <!-- Content -->
                        <div class="prose prose-lg dark:prose-invert max-w-none dark:text-white" v-html="post.content">
                        </div>

                        <!-- Share Section -->
                        <div class="border-t border-gray-200 dark:border-zinc-700 mt-8 pt-8">
                            <h3 class="text-lg font-semibold mb-4 dark:text-white">Share this article</h3>
                            <div class="flex flex-wrap gap-3">
                                <!-- Facebook Share -->
                                <button @click="shareOnFacebook"
                                    class="flex items-center gap-2 bg-[#1877f2] text-white px-4 py-2 rounded-lg hover:bg-[#1877f2]/90 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                    Facebook
                                </button>

                                <!-- Twitter/X Share -->
                                <button @click="shareOnTwitter"
                                    class="flex items-center gap-2 bg-[#1da1f2] text-white px-4 py-2 rounded-lg hover:bg-[#1da1f2]/90 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                    </svg>
                                    Twitter
                                </button>

                                <!-- LinkedIn Share -->
                                <button @click="shareOnLinkedIn"
                                    class="flex items-center gap-2 bg-[#0a66c2] text-white px-4 py-2 rounded-lg hover:bg-[#0a66c2]/90 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451c.979 0 1.771-.773 1.771-1.729V1.729C24 .774 23.227 0 22.225 0z" />
                                    </svg>
                                    LinkedIn
                                </button>

                                <!-- WhatsApp Share -->
                                <button @click="shareOnWhatsApp"
                                    class="flex items-center gap-2 bg-[#25d366] text-white px-4 py-2 rounded-lg hover:bg-[#25d366]/90 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                    </svg>
                                    WhatsApp
                                </button>

                                <!-- Copy Link Button -->
                                <button @click="copyLink"
                                    class="flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                    {{ copied ? 'Copied!' : 'Copy Link' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Related Posts -->
                <div v-if="relatedPosts && relatedPosts.length > 0" class="mt-12">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Related Articles
                    </h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <Link v-for="related in relatedPosts" :key="related.id" :href="route('blog.show', related.slug)"
                            class="bg-white dark:bg-zinc-800 rounded-lg p-4 hover:shadow-lg transition">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                                {{ related.title }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ formatDate(related.published_at) }}
                            </p>
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
        // Fallback for older browsers
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

/* Prose styling for blog content */
.prose {
    max-width: none;
}

.prose h1 {
    font-size: 2em;
    margin-top: 0.5em;
    margin-bottom: 0.5em;
}

.prose h2 {
    font-size: 1.5em;
    margin-top: 1em;
    margin-bottom: 0.5em;
}

.prose h3 {
    font-size: 1.25em;
    margin-top: 0.8em;
    margin-bottom: 0.4em;
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

.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1em 0;
}
</style>