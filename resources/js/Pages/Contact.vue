<template>
    <GuestLayout>


        <Head title="Live Market Rates" />
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">


                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-amber-600 px-6 py-8">
                        <h1 class="text-3xl font-bold text-white">Contact Us</h1>
                        <p class="text-amber-100 mt-2">We'd love to hear from you</p>
                    </div>

                    <div class="p-8">
                        <div class="grid md:grid-cols-2 gap-8">
                            <!-- Contact Form -->
                            <div>
                                <form @submit.prevent="submitForm" class="space-y-6">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name
                                            *</label>
                                        <input type="text" v-model="form.name" required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                                        <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name
                                        }}</p>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email
                                            *</label>
                                        <input type="email" v-model="form.email" required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                                        <p v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{
                                            form.errors.email }}</p>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject
                                            *</label>
                                        <input type="text" v-model="form.subject" required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white">
                                        <p v-if="form.errors.subject" class="text-red-500 text-sm mt-1">{{
                                            form.errors.subject }}</p>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message
                                            *</label>
                                        <textarea v-model="form.message" rows="5" required
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-amber-500 focus:border-amber-500 dark:bg-zinc-700 dark:text-white"></textarea>
                                        <p v-if="form.errors.message" class="text-red-500 text-sm mt-1">{{
                                            form.errors.message }}</p>
                                    </div>

                                    <button type="submit" :disabled="form.processing"
                                        class="w-full bg-amber-600 text-white py-2 px-4 rounded-lg hover:bg-amber-700 transition disabled:opacity-50">
                                        {{ form.processing ? 'Sending...' : 'Send Message' }}
                                    </button>
                                </form>
                            </div>

                            <!-- Contact Info -->
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Get in Touch
                                    </h3>
                                    <div class="space-y-3 text-gray-600 dark:text-gray-400">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            <span>mmratepro@gmail.com</span>
                                        </div>
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span>Response within 24-48 hours</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Business Hours
                                    </h3>
                                    <div class="space-y-2 text-gray-600 dark:text-gray-400">
                                        <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                        <p>Saturday: 10:00 AM - 2:00 PM</p>
                                        <p>Sunday: Closed</p>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Quick Links
                                    </h3>
                                    <div class="space-y-2">
                                        <a href="/privacy" class="block text-amber-600 hover:text-amber-800">Privacy
                                            Policy</a>
                                        <a href="/terms" class="block text-amber-600 hover:text-amber-800">Terms of
                                            Service</a>
                                        <a href="/blog" class="block text-amber-600 hover:text-amber-800">Blog</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { useForm, Head } from '@inertiajs/vue3'
import { ref } from 'vue'  // ← IMPORTANT: This was missing!

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: ''
})

const errorMessage = ref('')

const submitForm = () => {
    errorMessage.value = ''

    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (errors) => {
            if (errors.error) {
                errorMessage.value = errors.error
            }
        }
    })
}
</script>