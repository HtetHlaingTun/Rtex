<template>
    <UserLayout title="My Profile">
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Profile Header -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-6">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center overflow-hidden">
                            <img v-if="user.profile_photo" :src="user.profile_photo" class="w-full h-full object-cover">
                            <span v-else class="text-2xl font-bold text-amber-600">{{ user.name.charAt(0) }}</span>
                        </div>
                        <button @click="triggerFileInput"
                            class="absolute bottom-0 right-0 p-1 bg-amber-500 rounded-full text-white hover:bg-amber-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        <input type="file" ref="photoInput" @change="uploadPhoto" accept="image/*" class="hidden">
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ user.name }}</h2>
                        <p class="text-sm text-slate-500">{{ user.role_label || user.role }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div :class="user.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                class="px-2 py-0.5 rounded-full text-xs font-bold">
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <form @submit.prevent="updateProfile"
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-6">
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-4">Personal Information</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
                        <input type="text" v-model="form.name"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Email
                            Address</label>
                        <input type="email" v-model="form.email"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                        <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Phone
                            Number</label>
                        <input type="tel" v-model="form.phone"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                        <input type="text" v-model="form.department"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Notification
                            Preferences</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.notify_on_verification" class="rounded">
                                <span class="text-sm">Notify when my submissions are verified</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.notify_on_new_entry" class="rounded">
                                <span class="text-sm">Notify about new entries</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" v-model="form.notify_on_rejection" class="rounded">
                                <span class="text-sm">Notify when my submissions are rejected</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit" :disabled="processing"
                            class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg transition-all disabled:opacity-50">
                            {{ processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Password Change Form -->
            <form @submit.prevent="updatePassword"
                class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 p-6">
                <h3 class="text-lg font-black text-slate-900 dark:text-white mb-4">Change Password</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Current
                            Password</label>
                        <input type="password" v-model="passwordForm.current_password"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                        <p v-if="errors.current_password" class="text-xs text-red-500 mt-1">{{ errors.current_password
                        }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">New
                            Password</label>
                        <input type="password" v-model="passwordForm.password"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Confirm New
                            Password</label>
                        <input type="password" v-model="passwordForm.password_confirmation"
                            class="w-full px-4 py-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800">
                    </div>

                    <div>
                        <button type="submit" :disabled="passwordProcessing"
                            class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg transition-all disabled:opacity-50">
                            {{ passwordProcessing ? 'Updating...' : 'Update Password' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    user: Object
})

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    department: props.user.department || '',
    notify_on_verification: props.user.notify_on_verification,
    notify_on_new_entry: props.user.notify_on_new_entry,
    notify_on_rejection: props.user.notify_on_rejection,
})

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const processing = ref(false)
const passwordProcessing = ref(false)
const photoInput = ref(null)

const updateProfile = () => {
    processing.value = true
    form.put(route('user.profile.update'), {
        onFinish: () => { processing.value = false }
    })
}

const updatePassword = () => {
    passwordProcessing.value = true
    passwordForm.put(route('user.profile.password'), {
        onFinish: () => {
            passwordProcessing.value = false
            passwordForm.reset()
        }
    })
}

const triggerFileInput = () => {
    photoInput.value.click()
}

const uploadPhoto = (event) => {
    const file = event.target.files[0]
    if (file) {
        const formData = new FormData()
        formData.append('profile_photo', file)

        axios.post(route('user.profile.photo'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        }).then(() => {
            router.reload()
        }).catch(error => {
            alert('Failed to upload photo')
        })
    }
}
</script>