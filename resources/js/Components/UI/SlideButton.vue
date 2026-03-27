<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    label: { type: String, default: 'Slide to Save' },
    successLabel: { type: String, default: 'Authenticating...' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false }, // Tied to form.processing
});

const emit = defineEmits(['success']);

const container = ref(null);
const slider = ref(null);
const isDragging = ref(false);
const startX = ref(0);
const translateX = ref(0);
const isComplete = ref(false);

// --- NEW: Reset Logic ---
// If the parent stops loading (form.processing = false) 
// but we didn't actually finish (e.g., validation error), snap back.
watch(() => props.loading, (newVal) => {
    if (!newVal && isComplete.value) {
        // Optional: Only reset if there are errors. 
        // For now, let's reset to allow re-submission.
        setTimeout(() => {
            isComplete.value = false;
            translateX.value = 0;
        }, 500);
    }
});

const textOpacity = computed(() => {
    if (isComplete.value || props.loading) return 0;
    const threshold = 100;
    const opacity = 1 - (translateX.value / threshold);
    return opacity < 0 ? 0 : opacity;
});

const startDrag = (e) => {
    if (props.disabled || props.loading || isComplete.value) return;
    isDragging.value = true;
    startX.value = e.type.includes('mouse') ? e.pageX : e.touches[0].pageX;
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    const x = e.type.includes('mouse') ? e.pageX : e.touches[0].pageX;
    const walk = x - startX.value;
    const maxTranslate = container.value.offsetWidth - slider.value.offsetWidth - 12;

    if (walk > 0 && walk <= maxTranslate) {
        translateX.value = walk;
    } else if (walk > maxTranslate) {
        translateX.value = maxTranslate;
        isComplete.value = true;
        isDragging.value = false;
        emit('success');
    }
};

const stopDrag = () => {
    if (!isDragging.value) return;
    isDragging.value = false;
    if (!isComplete.value) translateX.value = 0;
};

onMounted(() => {
    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
    window.addEventListener('touchmove', onDrag, { passive: false });
    window.addEventListener('touchend', stopDrag);
});

onUnmounted(() => {
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
    window.removeEventListener('touchmove', onDrag);
    window.removeEventListener('touchend', stopDrag);
});
</script>

<template>
    <div ref="container"
        class="relative w-full h-16 sm:h-20 bg-slate-100 rounded-[2rem] p-1.5 flex items-center overflow-hidden border border-slate-200 transition-colors duration-500"
        :class="[
            disabled ? 'opacity-50 pointer-events-none' : '',
            isComplete && !loading ? 'bg-emerald-50 border-emerald-200' : ''
        ]">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none transition-opacity duration-150"
            :style="{ opacity: textOpacity }">
            <span
                class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 select-none whitespace-nowrap">
                {{ label }}
            </span>
        </div>

        <div v-if="isComplete || loading"
            class="absolute inset-0 flex items-center justify-center pointer-events-none animate-in fade-in zoom-in duration-300">
            <span class="text-[10px] font-black uppercase tracking-[0.2em]"
                :class="loading ? 'text-amber-600' : 'text-emerald-600'">
                {{ loading ? successLabel : 'Done' }}
            </span>
        </div>

        <div ref="slider" @mousedown="startDrag" @touchstart.prevent="startDrag"
            class="relative z-10 h-full aspect-square bg-slate-900 rounded-[1.6rem] flex items-center justify-center cursor-grab active:cursor-grabbing shadow-xl"
            :style="{
                transform: `translateX(${translateX}px)`,
                transition: isDragging ? 'none' : 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.3s'
            }" :class="{ 'bg-emerald-500': isComplete && !loading, 'bg-amber-500': loading }">
            <svg v-if="!loading && !isComplete" class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>

            <svg v-else-if="isComplete && !loading" class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>

            <svg v-else class="w-5 h-5 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
    </div>
</template>