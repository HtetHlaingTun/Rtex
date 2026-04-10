<template>
    <GuestLayout>

        <Head title="Live Market Rates" />
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-lg p-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Terms of Service</h1>

                    <div class="space-y-6 text-gray-700 dark:text-gray-300">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last updated: {{ formattedDate }}</p>

                        <!-- Executive Summary -->
                        <div
                            class="bg-amber-50 dark:bg-amber-950/30 p-4 rounded-lg border border-amber-200 dark:border-amber-800">
                            <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-400 mb-2">📋 Agreement
                                Overview</h2>
                            <p class="text-sm">By using MMRatePro ("we", "us", "our"), you agree to these Terms of
                                Service. MMRatePro provides real-time currency exchange rates, gold price tracking, and
                                portfolio management tools. These terms govern your use of our website and services,
                                including account registration, asset tracking, and price alerts.</p>
                        </div>

                        <!-- 1. Acceptance of Terms -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">1. Acceptance of Terms
                            </h2>
                            <p>By accessing or using MMRatePro (luckeymm.online), you agree to be bound by these Terms
                                of Service and our Privacy Policy. If you do not agree to these terms, please do not use
                                our services.</p>
                            <p class="mt-2">We reserve the right to update or modify these terms at any time without
                                prior notice. Your continued use of the service constitutes acceptance of any changes.
                            </p>
                        </section>

                        <!-- 2. Description of Services -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">2. Description of
                                Services</h2>
                            <p>MMRatePro provides the following services:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li><strong>Real-time Exchange Rates:</strong> Live USD, SGD, EUR, THB to MMK rates from
                                    multiple Myanmar banks</li>
                                <li><strong>Gold Price Tracking:</strong> World gold spot prices (USD/SGD) and Myanmar
                                    gold prices (New System & Traditional)</li>
                                <li><strong>Portfolio Management:</strong> Track your gold and currency assets with
                                    real-time P&L calculations</li>
                                <li><strong>Price Alerts:</strong> Set custom alerts for rate thresholds</li>
                                <li><strong>Watchlist:</strong> Favorite and monitor specific currencies and gold types
                                </li>
                                <li><strong>Historical Data:</strong> Access to historical exchange rates and gold
                                    prices</li>
                            </ul>
                        </section>

                        <!-- 3. Account Registration -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">3. Account Registration
                            </h2>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">3.1 Account
                                Requirements</h3>
                            <ul class="list-disc pl-6 space-y-1">
                                <li>You must be at least 16 years old to register</li>
                                <li>You must provide accurate and complete information</li>
                                <li>You are responsible for maintaining account security</li>
                                <li>You are responsible for all activities under your account</li>
                            </ul>

                            <h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mt-3 mb-2">3.2 Account
                                Termination</h3>
                            <p>We reserve the right to suspend or terminate accounts that:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Violate these Terms of Service</li>
                                <li>Provide false or misleading information</li>
                                <li>Engage in fraudulent or illegal activities</li>
                                <li>Attempt to manipulate rate data or system integrity</li>
                                <li>Remain inactive for more than 12 months</li>
                            </ul>
                        </section>

                        <!-- 4. User Conduct -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">4. User Conduct and
                                Restrictions</h2>
                            <p>You agree NOT to:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Use our data for unauthorized commercial purposes (e.g., reselling rates without
                                    permission)</li>
                                <li>Scrape, crawl, or extract data using automated tools without written consent</li>
                                <li>Attempt to bypass rate limits or security measures</li>
                                <li>Interfere with or disrupt our services or servers</li>
                                <li>Upload malicious code, viruses, or harmful content</li>
                                <li>Impersonate another user or entity</li>
                                <li>Share your account credentials with others</li>
                                <li>Use the service for any illegal purpose</li>
                            </ul>
                        </section>

                        <!-- 5. Rate Data and Accuracy -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">5. Rate Data and
                                Accuracy</h2>
                            <div class="bg-yellow-50 dark:bg-zinc-900 p-4 rounded-lg mb-3">
                                <p class="text-sm text-yellow-800 dark:text-yellow-400">⚠️ <strong>Important
                                        Disclaimer:</strong> Exchange rates and gold prices are provided for
                                    informational purposes only. They may not reflect actual market rates due to delays,
                                    bank-specific markups, or technical issues.</p>
                            </div>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Rates are sourced from bank APIs, CBM reference rates, and financial data providers
                                </li>
                                <li>Rates may be delayed by up to 5 minutes during peak hours</li>
                                <li>We are not responsible for financial decisions made based on our data</li>
                                <li>Bank markups and spreads are configurable and may not reflect actual transaction
                                    costs</li>
                                <li>Historical data is consolidated periodically for performance</li>
                            </ul>
                        </section>

                        <!-- 6. Portfolio and Asset Tracking -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">6. Portfolio and Asset
                                Tracking</h2>
                            <p>Our portfolio management features allow you to track your gold and currency assets. By
                                using these features, you acknowledge:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>We do not provide financial advice or investment recommendations</li>
                                <li>P&L calculations are estimates based on current rates</li>
                                <li>We are not responsible for any financial losses or gains</li>
                                <li>You are solely responsible for the accuracy of your asset data</li>
                                <li>Portfolio data is stored securely but you should maintain your own records</li>
                            </ul>
                        </section>

                        <!-- 7. Intellectual Property -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">7. Intellectual
                                Property</h2>
                            <p>All content on MMRatePro, including but not limited to:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Website design, layout, and graphics</li>
                                <li>Software code and algorithms</li>
                                <li>Rate calculation methodologies</li>
                                <li>Original blog content and articles</li>
                                <li>Brand names, logos, and trademarks</li>
                            </ul>
                            <p class="mt-2">is owned by MMRatePro and protected by copyright laws. You may not
                                reproduce, distribute, or create derivative works without our express written
                                permission.</p>
                        </section>

                        <!-- 8. User-Generated Content -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">8. User-Generated
                                Content</h2>
                            <p>You retain ownership of content you submit (e.g., portfolio data, watchlist items, alert
                                settings). By submitting content, you grant us a license to:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Store and display your content as part of our services</li>
                                <li>Use aggregated anonymous data for analytics and improvements</li>
                                <li>Backup and restore your data as needed</li>
                            </ul>
                            <p class="mt-2">You are responsible for ensuring your content does not violate any laws or
                                third-party rights.</p>
                        </section>

                        <!-- 9. Limitation of Liability -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">9. Limitation of
                                Liability</h2>
                            <div class="bg-red-50 dark:bg-zinc-900 dark:text-red-500 p-4 rounded-lg">
                                <p class="text-sm text-red-800 dark:text-red-400">⚠️ <strong> Limitation of
                                        Liability:</strong></p>
                                <ul class="list-disc pl-6 mt-2 space-y-1 text-sm">
                                    <li>We are not liable for any financial losses, trading losses, or investment
                                        decisions made using our data</li>
                                    <li>We are not responsible for service interruptions, data delays, or inaccuracies
                                    </li>
                                    <li>Our maximum liability is limited to the amount paid by you (if any) for our
                                        services</li>
                                    <li>We are not liable for indirect, incidental, or consequential damages</li>
                                    <li>We do not guarantee uninterrupted or error-free service</li>
                                </ul>
                            </div>
                        </section>

                        <!-- 10. Disclaimer of Warranties -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">10. Disclaimer of
                                Warranties</h2>
                            <p>Our services are provided "AS IS" and "AS AVAILABLE" without warranties of any kind:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>No warranty of merchantability or fitness for a particular purpose</li>
                                <li>No warranty that rates are accurate, complete, or current</li>
                                <li>No warranty that service will be uninterrupted or error-free</li>
                                <li>No warranty regarding third-party data sources (banks, CBM, Yahoo Finance)</li>
                            </ul>
                        </section>

                        <!-- 11. Subscription and Payments -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">11. Subscription and
                                Payments</h2>
                            <p>Currently, MMRatePro offers free basic services. If we introduce paid features in the
                                future:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Pricing will be clearly displayed before purchase</li>
                                <li>Subscriptions will auto-renew unless canceled</li>
                                <li>Refunds are provided at our discretion</li>
                                <li>We reserve the right to change pricing with 30 days notice</li>
                            </ul>
                        </section>

                        <!-- 12. Third-Party Links -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">12. Third-Party Links
                            </h2>
                            <p>Our website may contain links to third-party websites or services (e.g., bank websites,
                                financial news). We are not responsible for:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>The content or accuracy of third-party sites</li>
                                <li>Their privacy practices or terms of service</li>
                                <li>Any damages arising from your use of third-party sites</li>
                            </ul>
                        </section>

                        <!-- 13. Indemnification -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">13. Indemnification
                            </h2>
                            <p>You agree to indemnify and hold harmless MMRatePro, its officers, employees, and
                                affiliates from any claims, damages, or expenses arising from:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Your violation of these Terms of Service</li>
                                <li>Your use of our services</li>
                                <li>Your violation of any laws or third-party rights</li>
                            </ul>
                        </section>

                        <!-- 14. Termination -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">14. Termination</h2>
                            <p>We may terminate or suspend your account immediately, without prior notice, for conduct
                                that violates these Terms. Upon termination:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Your right to use our services will immediately cease</li>
                                <li>We may delete your account and associated data</li>
                                <li>You may request data export before termination</li>
                            </ul>
                        </section>

                        <!-- 15. Governing Law -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">15. Governing Law</h2>
                            <p>These Terms shall be governed by the laws of Myanmar. Any disputes arising from these
                                Terms or your use of our services shall be resolved in the courts of Myanmar.</p>
                        </section>

                        <!-- 16. Data Retention and Deletion -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">16. Data Retention and
                                Deletion</h2>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>You may delete your account at any time via Profile settings</li>
                                <li>Upon deletion, your personal data and portfolio will be permanently removed</li>
                                <li>Anonymous rate history may be retained for analytics</li>
                                <li>Inactive accounts may be deleted after 12 months of inactivity</li>
                            </ul>
                        </section>

                        <!-- 17. Force Majeure -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">17. Force Majeure</h2>
                            <p>We are not liable for delays or failures in performance resulting from causes beyond our
                                reasonable control, including but not limited to:</p>
                            <ul class="list-disc pl-6 mt-2 space-y-1">
                                <li>Natural disasters, pandemics, or emergencies</li>
                                <li>Internet outages or infrastructure failures</li>
                                <li>Bank API failures or data provider interruptions</li>
                                <li>Government actions or legal restrictions</li>
                            </ul>
                        </section>

                        <!-- 18. Entire Agreement -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">18. Entire Agreement
                            </h2>
                            <p>These Terms constitute the entire agreement between you and MMRatePro regarding our
                                services, superseding any prior agreements. If any provision is found to be
                                unenforceable, the remaining provisions remain in effect.</p>
                        </section>

                        <!-- 19. Contact Information -->
                        <section>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-3">19. Contact Information
                            </h2>
                            <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg mt-3">
                                <p><strong>For legal or terms-related inquiries:</strong></p>
                                <p>Email: <a href="mailto:support@luckeymm.online"
                                        class="text-amber-600 hover:text-amber-700">support@luckeymm.online</a></p>
                                <p class="mt-2"><strong>For support or technical issues:</strong></p>
                                <p>Email: <a href="mailto:support@luckeymm.online"
                                        class="text-amber-600 hover:text-amber-700">support@luckeymm.online</a></p>
                                <p class="mt-2"><strong>Address:</strong> Yangon, Myanmar</p>
                            </div>
                        </section>

                        <!-- Acceptance Footer -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">By using MMRatePro, you acknowledge that
                                you have read, understood, and agree to be bound by these Terms of Service.</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Last updated: {{ formattedDate }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3';

const formattedDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
})
</script>