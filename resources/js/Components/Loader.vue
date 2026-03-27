<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: 'Processing...'
    },
    overlay: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="show" :class="[
            overlay ? 'fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-sm' : 'relative py-4',
            'flex flex-col items-center justify-center'
        ]">

            <div class="bg-white p-6 rounded-2xl shadow-xl flex flex-col items-center border border-slate-100">
                <div class="relative w-12 h-12">
                    <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
                    <div
                        class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin">
                    </div>
                </div>

                <p v-if="message" class="mt-4 text-sm font-bold text-slate-700 tracking-wide uppercase">
                    {{ message }}
                </p>
            </div>
        </div>
    </Transition>
</template>