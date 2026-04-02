<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Create Account" />

        <main class="min-h-screen selection:bg-orange-100 dark:selection:bg-orange-500/30">
            <div class="pt-[80px] pb-20 flex flex-col justify-between max-w-lg mx-auto px-6">

                <div class="text-center mb-10">

                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white transition-colors">
                        Join <span class="text-orange-600">RTEx</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 mt-2">Start monitoring gold markets and currency
                        rates</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="group">
                        <InputLabel for="name" value="Full Name"
                            class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 mb-2 block" />
                        <TextInput id="name" type="text"
                            class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all"
                            v-model="form.name" required autofocus autocomplete="name" placeholder="Htet Hlaing Tun" />
                        <InputError class="mt-2 text-xs font-bold text-rose-500" :message="form.errors.name" />
                    </div>

                    <div class="group">
                        <InputLabel for="email" value="Email Address"
                            class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 mb-2 block" />
                        <TextInput id="email" type="email"
                            class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all"
                            v-model="form.email" required autocomplete="username" placeholder="name@example.com" />
                        <InputError class="mt-2 text-xs font-bold text-rose-500" :message="form.errors.email" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="group">
                            <InputLabel for="password" value="Password"
                                class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 mb-2 block" />
                            <TextInput id="password" type="password"
                                class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all"
                                v-model="form.password" required autocomplete="new-password" placeholder="••••••••" />
                            <InputError class="mt-2 text-xs font-bold text-rose-500" :message="form.errors.password" />
                        </div>

                        <div class="group">
                            <InputLabel for="password_confirmation" value="Confirm"
                                class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 mb-2 block" />
                            <TextInput id="password_confirmation" type="password"
                                class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all"
                                v-model="form.password_confirmation" required autocomplete="new-password"
                                placeholder="••••••••" />
                            <InputError class="mt-2 text-xs font-bold text-rose-500"
                                :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 text-center px-4">
                        By registering, you agree to our
                        <span class="text-slate-600 dark:text-zinc-300 font-bold">Terms of Service</span> and
                        <span class="text-slate-600 dark:text-zinc-300 font-bold">Privacy Policy</span>.
                    </p>

                    <div class="pt-4">
                        <PrimaryButton
                            class="w-full justify-center py-4 bg-slate-900 dark:bg-white dark:text-slate-900 hover:bg-orange-600 dark:hover:bg-orange-500 hover:text-white font-black uppercase tracking-[0.2em] rounded-xl transition-all duration-300 shadow-lg shadow-slate-200 dark:shadow-none active:scale-[0.97]"
                            :class="{ 'opacity-50 cursor-wait': form.processing }" :disabled="form.processing">
                            Create Account
                        </PrimaryButton>
                    </div>

                    <p class="text-center text-sm text-slate-400 dark:text-zinc-500 mt-8">
                        Already have an account?
                        <Link :href="route('login')"
                            class="font-black text-slate-900 dark:text-white underline decoration-orange-500/30 hover:decoration-orange-600 transition-all decoration-2 underline-offset-4">
                            Sign In Instead
                        </Link>
                    </p>
                </form>
            </div>
        </main>
    </GuestLayout>
</template>