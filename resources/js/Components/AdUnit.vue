<template>
    <div v-if="shouldShowAd" class="ad-container my-4 text-center">
        <div v-html="adCode"></div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
    zoneId: {
        type: String,
        required: true
    },
    type: {
        type: String,
        default: 'display' // display, popunder, native
    },
    showToLoggedIn: {
        type: Boolean,
        default: false
    }
})

const page = usePage()

// Don't show ads to logged-in users (better UX)
const shouldShowAd = computed(() => {
    if (!props.showToLoggedIn && page.props.auth?.user) {
        return false
    }
    return true
})

// PropellerAds example code structure
const adCode = computed(() => {
    return `<ins class="propellerads" data-id="${props.zoneId}"></ins>`
})
</script>

<style scoped>
.ad-container {
    min-height: 250px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>