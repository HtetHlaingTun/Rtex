<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <main class="min-h-screen selection:bg-orange-100 dark:selection:bg-orange-500/30">
            <div class="pt-[100px] flex flex-col justify-between max-w-md mx-auto px-6">

                <div class="text-center mb-10">

                    <h1 class="text-3xl font-monok tracking-tight text-slate-900 dark:text-white transition-colors">
                        Welcome <span class="text-orange-600">Back</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 mt-2">Access your RTEx dashboard and gold
                        markets</p>
                </div>

                <div v-if="status"
                    class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 text-sm font-mono text-emerald-600 dark:text-emerald-400 animate-in fade-in slide-in-from-top-2">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="group">
                        <InputLabel for="email" value="Email Address"
                            class="text-[10px] font-monok uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500 mb-2 block" />
                        <div class="relative">
                            <TextInput id="email" type="email"
                                class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all placeholder:text-slate-300 dark:placeholder:text-zinc-700"
                                v-model="form.email" required autofocus autocomplete="username"
                                placeholder="name@example.com" />
                        </div>
                        <InputError class="mt-2 text-xs font-mono text-rose-500" :message="form.errors.email" />
                    </div>

                    <div class="group">
                        <div class="flex justify-between items-center mb-2">
                            <InputLabel for="password" value="Password"
                                class="text-[10px] font-monok uppercase tracking-[0.15em] text-slate-400 dark:text-zinc-500" />


                        </div>

                        <TextInput id="password" type="password"
                            class="block w-full border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900/50 text-slate-900 dark:text-white focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl transition-all placeholder:text-slate-300 dark:placeholder:text-zinc-700"
                            v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />

                        <InputError class="mt-2 text-xs font-mono text-rose-500" :message="form.errors.password" />
                    </div>
                    <Link v-if="canResetPassword" :href="route('password.request')"
                        class="text-[11px] font-mono text-orange-600 hover:text-orange-700 dark:hover:text-orange-500 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500/20 rounded-md px-1">
                        Forgot Password?
                    </Link>

                    <div class="flex items-center justify-between py-1">
                        <label class="flex items-center cursor-pointer group">
                            <Checkbox name="remember" v-model:checked="form.remember"
                                class="rounded border-slate-300 dark:border-zinc-700 text-orange-600 focus:ring-orange-500/20 dark:bg-zinc-900" />
                            <span
                                class="ms-2 text-sm font-mono text-slate-500 dark:text-zinc-500 group-hover:text-slate-700 dark:group-hover:text-zinc-300 transition-colors">Remember
                                this device</span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <PrimaryButton
                            class="w-full justify-center py-4 bg-slate-900 dark:bg-white dark:text-slate-900 hover:bg-orange-600 dark:hover:bg-orange-500 hover:text-white font-monok uppercase tracking-[0.2em] rounded-xl transition-all duration-300 shadow-lg shadow-slate-200 dark:shadow-none active:scale-[0.97]"
                            :class="{ 'opacity-50 cursor-wait': form.processing }" :disabled="form.processing">
                            Sign In
                        </PrimaryButton>
                    </div>

                    <p class="text-center text-sm text-slate-400 dark:text-zinc-500 mt-10">
                        New to MMRatePro?
                        <Link :href="route('register')"
                            class="font-monok text-slate-900 dark:text-white underline decoration-orange-500/30 hover:decoration-orange-600 transition-all decoration-2 underline-offset-4">
                            Create Account
                        </Link>
                    </p>
                </form>
            </div>
        </main>
    </GuestLayout>
</template>