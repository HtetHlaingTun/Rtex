<template>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>

                <div class="space-y-6 text-gray-700">
                    <p class="text-sm text-gray-500">Last updated: {{ formattedDate }}</p>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">1. Information We Collect</h2>
                        <p>We collect information you provide directly to us, such as when you create an account, add
                            assets to your portfolio, or set price alerts. This may include:</p>
                        <ul class="list-disc pl-6 mt-2 space-y-1">
                            <li>Name and email address</li>
                            <li>Account preferences</li>
                            <li>Asset portfolio information</li>
                            <li>Price alert settings</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">2. Automatically Collected Information</h2>
                        <p>When you visit our website, we automatically collect certain information about your device
                            and usage:</p>
                        <ul class="list-disc pl-6 mt-2 space-y-1">
                            <li>IP address and browser type</li>
                            <li>Pages visited and time spent</li>
                            <li>Referring website addresses</li>
                            <li>Device information (operating system, screen resolution)</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">3. Cookies and Tracking Technologies</h2>
                        <p>We use cookies and similar tracking technologies to:</p>
                        <ul class="list-disc pl-6 mt-2 space-y-1">
                            <li>Remember your login session</li>
                            <li>Store your preferences</li>
                            <li>Analyze site traffic and usage</li>
                            <li>Serve relevant advertisements (future)</li>
                        </ul>
                        <p class="mt-3">You can control cookies through your browser settings.</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">4. How We Use Your Information</h2>
                        <p>We use the collected information to:</p>
                        <ul class="list-disc pl-6 mt-2 space-y-1">
                            <li>Provide and maintain our exchange rate services</li>
                            <li>Process your asset portfolio and alerts</li>
                            <li>Improve user experience and site functionality</li>
                            <li>Send important account notifications</li>
                            <li>Comply with legal obligations</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">5. Data Sharing and Disclosure</h2>
                        <p>We do not sell your personal information. We may share your data with:</p>
                        <ul class="list-disc pl-6 mt-2 space-y-1">
                            <li><strong>Service Providers:</strong> Third-party vendors who help operate our website
                            </li>
                            <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                            <li><strong>Business Transfers:</strong> In connection with a merger or acquisition</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">6. Data Security</h2>
                        <p>We implement industry-standard security measures to protect your data, including SSL
                            encryption, secure databases, and regular security audits.</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">7. Your Rights</h2>
                        <p>Depending on your location, you may have the right to:</p>
                        <ul class="list-disc pl-6 mt-2 space-y-1">
                            <li>Access your personal data</li>
                            <li>Correct inaccurate data</li>
                            <li>Request deletion of your data</li>
                            <li>Opt-out of marketing communications</li>
                        </ul>
                        <p class="mt-3">To exercise these rights, contact us at privacy@luckeymm.online</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">8. Third-Party Links</h2>
                        <p>Our website may contain links to external sites. We are not responsible for their privacy
                            practices.</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">9. Children's Privacy</h2>
                        <p>Our services are not intended for users under 16. We do not knowingly collect data from
                            children.</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">10. Changes to This Policy</h2>
                        <p>We may update this privacy policy periodically. Changes will be posted on this page with an
                            updated revision date.</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-800 mb-3">11. Contact Us</h2>
                        <p>For privacy-related questions, contact us at:</p>
                        <p class="mt-2">Email: privacy@luckeymm.online</p>
                        <p>Address: Luckey</p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const formattedDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
})
</script>