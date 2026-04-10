<template>
    <GuestLayout>

        <Head title="Live Market Rates" />
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Privacy Policy</h1>

                    <div class="space-y-6 text-gray-700 dark:text-gray-300">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last updated: {{ formattedDate }}</p>

                        <!-- Executive Summary -->
                        <div
                            class="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-lg border border-amber-200 dark:border-amber-800">
                            <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-400 mb-2">📋 Executive
                                Summary</h2>
                            <p class="text-sm">MMRatePro is a currency exchange and gold price tracking platform. We
                                collect minimal personal data (name, email) for user accounts. Your asset portfolio data
                                is stored securely and only accessible to you. We do not sell your personal information.
                                This policy explains how we handle your data when you use our real-time exchange rate
                                services, gold price tracking, and portfolio management features.</p>
                        </div>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">1. Information We
                                Collect</h2>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">1.1 Account
                                Information</h3>
                            <p>When you register for an account, we collect:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Name and email address</li>
                                <li>Password (encrypted, never stored in plain text)</li>
                                <li>Account preferences and notification settings</li>
                                <li>Role and permissions (admin/editor/viewer for staff)</li>
                            </ul>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">1.2 Asset
                                Portfolio Information</h3>
                            <p>When you use our portfolio tracking features, we store:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Asset types (gold, currency, etc.)</li>
                                <li>Quantity and purchase price</li>
                                <li>Purchase date and currency</li>
                                <li>Weight and purity specifications (for gold assets)</li>
                                <li>Watchlist items and price alerts</li>
                            </ul>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">1.3
                                Automatically Collected Information</h3>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>IP address and geographic location (for rate personalization)</li>
                                <li>Browser type and version</li>
                                <li>Pages visited and time spent on each page</li>
                                <li>Referring website addresses</li>
                                <li>Device information (operating system, screen resolution)</li>
                                <li>Session data for authentication</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">2. How We Use Your
                                Information</h2>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">2.1 Core
                                Services</h3>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li><strong>Exchange Rate Display:</strong> Show real-time USD, SGD, EUR, THB rates to
                                    MMK</li>
                                <li><strong>Gold Price Tracking:</strong> Display world gold spot prices (USD/SGD) and
                                    Myanmar gold prices (MMK)</li>
                                <li><strong>Portfolio Management:</strong> Calculate current value and P&L of your
                                    assets</li>
                                <li><strong>Price Alerts:</strong> Send notifications when rates hit your target levels
                                </li>
                                <li><strong>Watchlist:</strong> Track favorite currencies and gold types</li>
                            </ul>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">2.2 Analytics &
                                Improvement</h3>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Analyze user behavior to improve website functionality</li>
                                <li>Track feature usage (portfolio, alerts, watchlist)</li>
                                <li>Monitor system performance and error rates</li>
                                <li>Optimize rate fetching frequency based on user activity</li>
                            </ul>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">2.3
                                Communications</h3>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Send price alert notifications (email or in-app)</li>
                                <li>Account verification and security notices</li>
                                <li>Important service updates and maintenance notifications</li>
                                <li>Newsletter (only if you subscribe)</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">3. Data Security
                                Measures</h2>

                            <div class="grid md:grid-cols-2 gap-4 mt-3">
                                <div class="bg-green-50 dark:bg-green-950/30 p-3 rounded-lg">
                                    <h3 class="font-semibold text-green-800 dark:text-green-400 mb-2">🔒 Encryption</h3>
                                    <ul class="list-disc pl-5 text-sm space-y-1">
                                        <li>SSL/TLS encryption for all data transmission</li>
                                        <li>Passwords hashed using bcrypt</li>
                                        <li>Database encryption for sensitive fields</li>
                                        <li>API keys stored in environment variables</li>
                                    </ul>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-950/30 p-3 rounded-lg">
                                    <h3 class="font-semibold text-blue-800 dark:text-blue-400 mb-2">🛡️ Authentication
                                    </h3>
                                    <ul class="list-disc pl-5 text-sm space-y-1">
                                        <li>Session-based authentication with Laravel Sanctum</li>
                                        <li>CSRF protection on all forms</li>
                                        <li>Rate limiting on API endpoints</li>
                                        <li>Automatic session timeout (inactivity logout)</li>
                                    </ul>
                                </div>

                                <div class="bg-purple-50 dark:bg-purple-950/30 p-3 rounded-lg">
                                    <h3 class="font-semibold text-purple-800 dark:text-purple-400 mb-2">📊 Data Backup
                                    </h3>
                                    <ul class="list-disc pl-5 text-sm space-y-1">
                                        <li>Automated daily database backups</li>
                                        <li>Redundant storage for critical data</li>
                                        <li>Disaster recovery procedures</li>
                                    </ul>
                                </div>

                                <div class="bg-amber-50 dark:bg-amber-950/30 p-3 rounded-lg">
                                    <h3 class="font-semibold text-amber-800 dark:text-amber-400 mb-2">🔐 Access Control
                                    </h3>
                                    <ul class="list-disc pl-5 text-sm space-y-1">
                                        <li>Role-based access control (admin, editor, viewer)</li>
                                        <li>Principle of least privilege</li>
                                        <li>Activity logging for sensitive actions</li>
                                        <li>Two-factor authentication available for admins</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">4. Third-Party Services
                            </h2>
                            <p>We use the following third-party services to enhance our platform:</p>

                            <div class="overflow-x-auto mt-3">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Service
                                            </th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Purpose
                                            </th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Data
                                                Shared</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr>
                                            <td class="px-4 py-2 text-sm">Google Analytics</td>
                                            <td class="px-4 py-2 text-sm">Website analytics and user behavior</td>
                                            <td class="px-4 py-2 text-sm">Anonymous usage data, page views</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm">Yahoo Finance API</td>
                                            <td class="px-4 py-2 text-sm">World gold price data</td>
                                            <td class="px-4 py-2 text-sm">None (public API)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm">Bank Rate APIs</td>
                                            <td class="px-4 py-2 text-sm">USD, SGD, EUR, THB exchange rates</td>
                                            <td class="px-4 py-2 text-sm">None (public endpoints)</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 text-sm">Monetag/Adsterra</td>
                                            <td class="px-4 py-2 text-sm">Ad serving and monetization</td>
                                            <td class="px-4 py-2 text-sm">Anonymous ad impressions</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">5. Your Data Rights
                            </h2>
                            <p>As a user of MMRatePro, you have the following rights:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li><strong>Access:</strong> Request a copy of all personal data we hold about you</li>
                                <li><strong>Rectification:</strong> Correct inaccurate or incomplete data</li>
                                <li><strong>Deletion:</strong> Request deletion of your account and associated data</li>
                                <li><strong>Portability:</strong> Export your portfolio data in JSON format</li>
                                <li><strong>Restriction:</strong> Limit how we process your data</li>
                                <li><strong>Objection:</strong> Opt-out of marketing communications</li>
                            </ul>
                            <p class="mt-3">To exercise these rights, email us at <a href="mailto:mmratepro@gmail.com"
                                    class="text-amber-600 hover:text-amber-700">mmratepro@gmail.com</a></p>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">6. Data Retention</h2>
                            <p>We retain your data for the following periods:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li><strong>Account Data:</strong> Until you delete your account</li>
                                <li><strong>Portfolio Assets:</strong> Until you remove them or delete your account</li>
                                <li><strong>Price Alerts:</strong> Until you delete them (inactive alerts auto-delete
                                    after 90 days)</li>
                                <li><strong>Session Data:</strong> 24 hours after last activity</li>
                                <li><strong>Rate History:</strong> Consolidated monthly, permanent records kept for 2
                                    years</li>
                                <li><strong>Analytics Data:</strong> 26 months (Google Analytics retention)</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">7. Children's Privacy
                            </h2>
                            <p>Our services are not intended for users under 16 years of age. We do not knowingly
                                collect personal information from children under 16. If you believe a child has provided
                                us with personal information, please contact us immediately.</p>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">8. International Data
                                Transfers</h2>
                            <p>Your information may be transferred to and processed in countries where our servers are
                                located. By using our service, you consent to such transfers. We ensure appropriate
                                safeguards are in place for international data transfers.</p>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">9. Security Breach
                                Procedures</h2>
                            <p>In the event of a data breach, we will:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Immediately contain and investigate the breach</li>
                                <li>Notify affected users within 72 hours of discovery</li>
                                <li>Report to relevant authorities as required by law</li>
                                <li>Implement measures to prevent future breaches</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">10. Cookies & Tracking
                            </h2>
                            <p>We use the following types of cookies:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li><strong>Essential Cookies:</strong> Required for authentication and core
                                    functionality</li>
                                <li><strong>Preference Cookies:</strong> Remember your settings (dark mode, currency
                                    preferences)</li>
                                <li><strong>Analytics Cookies:</strong> Google Analytics for usage tracking</li>
                                <li><strong>Session Cookies:</strong> Maintain your login session</li>
                            </ul>
                            <p class="mt-3">You can manage cookie preferences through your browser settings. Disabling
                                essential cookies may affect website functionality.</p>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">11. Changes to This
                                Policy</h2>
                            <p>We may update this privacy policy periodically. Changes will be posted on this page with
                                an updated revision date. For significant changes, we will notify you via email or
                                website notification.</p>
                        </section>

                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">12. Contact Information
                            </h2>
                            <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg mt-3">
                                <p><strong>Data Controller:</strong> MMRatePro</p>
                                <p><strong>Email:</strong> <a href="mailto:mmratepro@gmail.com"
                                        class="text-amber-600 hover:text-amber-700">mmratepro@gmail.com</a></p>
                                <p><strong>Support:</strong> <a href="mailto:mmratepro@gmail.com"
                                        class="text-amber-600 hover:text-amber-700">mmratepro@gmail.com</a></p>
                                <p><strong>Address:</strong> Yangon, Myanmar</p>
                                <p><strong>Data Protection Officer:</strong> Available at mmratepro@gmail.com</p>
                            </div>
                        </section>

                        <!-- Acceptance Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">By using MMRatePro, you acknowledge that
                                you have read and understood this Privacy Policy and agree to its terms.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue'

const formattedDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
})
</script>