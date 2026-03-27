<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    message: { type: String, default: 'Refreshing data...' }
});

const isPulling = ref(false);
const pullDistance = ref(0);
const threshold = 80; // Distance in px to trigger refresh
const startY = ref(0);

const getScrollTop = () => {
    // Check the actual scrolling container first
    const scroller = document.scrollingElement  // ✅ Most reliable cross-browser
        || document.documentElement
        || document.body;
    return scroller.scrollTop;
};

const handleTouchStart = (e) => {
    const scrollTop = getScrollTop();
    const touchY = e.touches[0].clientY; // position on screen, not page

    // Guard 1: Must be scrolled to top
    // Guard 2: Touch must start in the top ~20% of screen (prevents mid-page triggers)
    const screenThreshold = window.innerHeight * 0.2;

    if (scrollTop <= 5 && touchY < screenThreshold) {
        startY.value = e.touches[0].pageY;
    } else {
        startY.value = 0;
    }
};

const handleTouchMove = (e) => {
    const scrollTop = getScrollTop(); // ← use the same helper

    if (startY.value === 0 || scrollTop > 5) {
        pullDistance.value = 0;
        isPulling.value = false;
        return;
    }

    const currentY = e.touches[0].pageY;
    const diff = currentY - startY.value;

    if (diff > 0) {
        // Only prevent default if we are pulling down to avoid breaking normal scroll
        if (e.cancelable) e.preventDefault();
        pullDistance.value = Math.min(diff * 0.4, threshold + 20);
    } else {
        // User is swiping up (scrolling down the content)
        startY.value = 0;
        pullDistance.value = 0;
        isPulling.value = false;
    }
};

const handleTouchEnd = () => {
    if (pullDistance.value >= threshold) {
        refresh();
    }

    // Reset state
    isPulling.value = false;
    pullDistance.value = 0;
    startY.value = 0;
};

const refresh = () => {
    // This triggers router.on('start') in AdminLayout
    router.reload({
        data: { _loader_message: props.message },
        onFinish: () => {
            // Logic to hide the pull indicator is already handled by handleTouchEnd
        }
    });
};

onMounted(() => {
    window.addEventListener('touchstart', handleTouchStart, { passive: false });
    window.addEventListener('touchmove', handleTouchMove, { passive: false });
    window.addEventListener('touchend', handleTouchEnd);
});

onUnmounted(() => {
    window.removeEventListener('touchstart', handleTouchStart);
    window.removeEventListener('touchmove', handleTouchMove);
    window.removeEventListener('touchend', handleTouchEnd);
});
</script>

<template>
    <!-- This div expands with the pull, pushing slot content down -->
    <div class="w-full flex items-center justify-center overflow-hidden"
        :style="{ height: `${pullDistance}px`, opacity: Math.min(pullDistance / threshold, 1) }">
        <div v-show="pullDistance > 0" class="bg-white shadow-xl rounded-full p-3 border border-slate-200">
            <div v-if="pullDistance >= threshold"
                class="w-6 h-6 border-2 border-indigo-600 border-t-transparent animate-spin rounded-full" />
            <svg v-else class="w-6 h-6 text-indigo-600" :style="{ transform: `rotate(${pullDistance * 3}deg)` }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>
</template>
<style scoped>
.pull-container {
    overscroll-behavior-y: contain;
}
</style>