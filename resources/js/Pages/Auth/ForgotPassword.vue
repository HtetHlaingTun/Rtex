<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>

        <Head title="Reset Password" />

        <main class="min-h-screen selection:bg-orange-100 dark:selection:bg-orange-500/30">
            <div class="pt-[100px] flex flex-col justify-between max-w-md mx-auto px-6">

                <div class="text-center mb-10">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-orange-50 dark:bg-orange-500/10 mb-4 transition-colors duration-500">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white transition-colors">
                        Forgot <span class="text-orange-600">Password?</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 mt-3 leading-relaxed">
                        No problem. Enter your email and we'll send you a link to get back into your account.
                    </p>
                </div>

                <div v-if="status"
                    class="mb-8 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 text-sm font-bold text-emerald-600 dark:text-emerald-400 animate-in fade-in zoom-in duration-300">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="group">
                        <InputLabel for="email" value="Email Address"
                            class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 mb-2 block" />
                        <TextInput id="email" type="email"
                            class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all"
                            v-model="form.email" required autofocus autocomplete="username"
                            placeholder="your@email.com" />
                        <InputError class="mt-2 text-xs font-bold text-rose-500" :message="form.errors.email" />
                    </div>

                    <div class="pt-2">
                        <PrimaryButton
                            class="w-full justify-center py-4 bg-slate-900 dark:bg-white dark:text-slate-900 hover:bg-orange-600 dark:hover:bg-orange-500 hover:text-white font-black uppercase tracking-[0.2em] rounded-xl transition-all duration-300 shadow-lg shadow-slate-200 dark:shadow-none active:scale-[0.97]"
                            :class="{ 'opacity-50 cursor-wait': form.processing }" :disabled="form.processing">
                            {{ form.processing ? 'Sending...' : 'Send Reset Link' }}
                        </PrimaryButton>
                    </div>

                    <div class="text-center mt-10">
                        <Link :href="route('login')"
                            class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-orange-600 dark:text-zinc-500 dark:hover:text-orange-500 transition-colors group">
                        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Login
                        </Link>
                    </div>
                </form>
            </div>
        </main>
    </GuestLayout>
</template>