// composables/useRealtimeAlerts.js

import { onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";

export function useRealtimeAlerts(userId, onAlertReceived = null) {
    let echoChannel = null;
    let pollingInterval = null;

    const showToast = (title, message) => {
        const toast = document.createElement("div");
        toast.className =
            "fixed bottom-4 right-4 bg-amber-500 text-white p-4 rounded-lg shadow-lg z-50 animate-slide-up max-w-md";
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <div class="flex-1">
                    <div class="font-bold">${title}</div>
                    <div class="text-sm">${message}</div>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            if (toast.parentElement) toast.remove();
        }, 5000);
    };

    const playSound = () => {
        const audio = new Audio("/sounds/notification.mp3");
        audio.play().catch((e) => console.log("Sound play failed:", e));
    };

    const playNotification = () => {
        playSound();
        if (Notification.permission === "granted") {
            new Notification("Price Alert Triggered", {
                body: "A currency rate has reached your target level",
                icon: "/favicon.ico",
            });
        }
    };

    // Fallback polling function
    const startPolling = () => {
        pollingInterval = setInterval(() => {
            router.reload({
                only: ["alerts", "currentRates", "watchlist"],
                preserveScroll: true,
                preserveState: true,
            });
        }, 30000); // Refresh every 30 seconds
    };

    const stopPolling = () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    };

    onMounted(() => {
        // Request notification permission
        if (Notification.permission === "default") {
            Notification.requestPermission();
        }

        // Try WebSocket first, fallback to polling
        if (window.Echo && userId) {
            try {
                echoChannel = window.Echo.channel(`user.${userId}`);
                echoChannel.listen(".alert.triggered", (data) => {
                    playNotification();
                    showToast(data.title, data.message);
                    if (onAlertReceived) {
                        onAlertReceived(data);
                    }
                    router.reload({
                        only: ["alerts", "currentRates", "watchlist"],
                        preserveScroll: true,
                        preserveState: true,
                    });
                });
                console.log("✅ WebSocket connected for real-time alerts");
            } catch (error) {
                console.warn(
                    "WebSocket failed, falling back to polling:",
                    error,
                );
                startPolling();
            }
        } else {
            console.log("Echo not available, using polling for alerts");
            startPolling();
        }
    });

    onUnmounted(() => {
        // Clean up WebSocket connection
        if (echoChannel) {
            try {
                // Try to leave the channel properly
                if (typeof echoChannel.stopListening === "function") {
                    echoChannel.stopListening(".alert.triggered");
                }
                if (typeof echoChannel.unsubscribe === "function") {
                    echoChannel.unsubscribe();
                }
            } catch (e) {
                console.log("Error cleaning up channel:", e);
            }
            echoChannel = null;
        }
        // Clean up polling
        stopPolling();
    });
}
