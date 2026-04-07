<template>
    <!-- Empty component - just loads analytics -->
</template>

<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

onMounted(() => {
    const gaId = 'G-ZSY1190X65'

    // Don't load in admin area or local development
    if (window.location.hostname === 'localhost' ||
        window.location.hostname === '127.0.0.1' ||
        window.location.pathname.startsWith('/admin')) {
        console.log('Analytics skipped: Admin or local environment')
        return
    }

    // Load Google Analytics script
    const script1 = document.createElement('script')
    script1.async = true
    script1.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`
    document.head.appendChild(script1)

    // Initialize gtag
    const script2 = document.createElement('script')
    script2.innerHTML = `
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '${gaId}');
    `
    document.head.appendChild(script2)

    // Track SPA page views for Inertia.js
    router.on('navigate', (event) => {
        if (typeof window.gtag !== 'undefined') {
            window.gtag('config', gaId, {
                page_path: event.detail.page.url
            })
        }
    })

    console.log('Google Analytics loaded for:', gaId)
})
</script>