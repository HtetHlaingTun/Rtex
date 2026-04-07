<template>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-blue-600 px-6 py-8">
                    <h1 class="text-3xl font-bold text-white">Contact Us</h1>
                    <p class="text-blue-100 mt-2">We'd love to hear from you</p>
                </div>

                <div class="p-8">
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Contact Form -->
                        <div>
                            <form @submit.prevent="submitForm" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                                    <input type="text" v-model="form.name" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input type="email" v-model="form.email" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                                    <input type="text" v-model="form.subject" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                                    <textarea v-model="form.message" rows="5" required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>

                                <button type="submit" :disabled="submitting"
                                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                                    {{ submitting ? 'Sending...' : 'Send Message' }}
                                </button>
                            </form>

                            <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg">
                                {{ successMessage }}
                            </div>
                            <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg">
                                {{ errorMessage }}
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Get in Touch</h3>
                                <div class="space-y-3 text-gray-600">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span>support@luckeymm.online</span>
                                    </div>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span>Response within 24-48 hours</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Business Hours</h3>
                                <div class="space-y-2 text-gray-600">
                                    <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                                    <p>Saturday: 10:00 AM - 2:00 PM</p>
                                    <p>Sunday: Closed</p>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Quick Links</h3>
                                <div class="space-y-2">
                                    <a href="/faq" class="block text-blue-600 hover:text-blue-800">FAQ</a>
                                    <a href="/privacy" class="block text-blue-600 hover:text-blue-800">Privacy
                                        Policy</a>
                                    <a href="/terms" class="block text-blue-600 hover:text-blue-800">Terms of
                                        Service</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: ''
})

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const submitForm = async () => {
    submitting.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
        await router.post('/contact', form.value)
        successMessage.value = 'Thank you! Your message has been sent.'
        form.value = { name: '', email: '', subject: '', message: '' }
    } catch (error) {
        errorMessage.value = 'Failed to send message. Please try again.'
    } finally {
        submitting.value = false
    }
}
</script>