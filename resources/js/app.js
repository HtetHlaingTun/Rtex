import "../css/app.css";
import "./bootstrap";

import { createInertiaApp, Link, Head, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import Breadcrumbs from "./Components/Layouts/Breadcrumbs.vue";
import Navbar from "./Components/Layouts/Navbar.vue";
import Loader from "./Components/Loader.vue";
import PullToRefresh from "./Components/PullToRefresh.vue";
import TrendIcon from "./Components/TrendIcon.vue";
import Paginations from "./Components/Layouts/Paginations.vue";
import FlashMessages from "./Components/FlashMessages.vue";
import Chart from "chart.js/auto";
import GoldPriceChart from "./Components/Charts/GoldPriceChart.vue";
import Fab from "./Components/Layouts/Fab.vue";
import SlideButton from "./Components/UI/SlideButton.vue";
import UserNavbar from "./Components/Layouts/UserNavbar.vue";
import PublicBackButton from "./Components/UI/PublicBackButton.vue";
import PublicBackIcon from "./Components/UI/PublicBackIcon.vue";
import PublicBreadcrumb from "./Components/UI/PublicBreadcrumb.vue";
import UserGoldChart from "./Components/Charts/UserGoldChart.vue";
import MobileBottomNav from "./Components/UI/MobileBottomNav.vue";
import RateHistoryChart from "./Components/Charts/Rate/RateHistoryChart.vue";
import UserBreadcrumbs from "./Components/Layouts/UserBreadcrumbs.vue";
import NotificationsBell from "./Components/NotificationsBell.vue";
import GoogleAnalytics from "./Components/GoogleAnalytics.vue";
import PublicCurrencyRate from "./Components/PublicCurrencyRate.vue";
import MyanmarGoldHistoryList from "./Components/MyanmarGoldHistoryList.vue";
import WorldGoldHistoryList from "./Components/WorldGoldHistoryList.vue";
import MyanmarOtherRecordsTable from "./Components/MyanmarOtherRecordsTable.vue";
import WorldOtherRecordsTable from "./Components/WorldOtherRecordsTable.vue";
import FuelPriceWidget from "./Components/FuelPriceWidget.vue";
import FuelPriceHistory from "./Components/FuelPriceHistory.vue";
import AdUnit from "./Components/AdUnit.vue";

if (window.Capacitor && window.Capacitor.isNativePlatform()) {
    import("@capacitor-community/safe-area").then(({ SafeArea }) => {
        SafeArea.load().catch((err) => {
            console.log("SafeArea load error:", err);
        });
    });
}

window.Chart = Chart;
let logoutTimer;

const appName = import.meta.env.VITE_APP_NAME || "MMRatePro";

function resetTimer() {
    clearTimeout(logoutTimer);
    logoutTimer = setTimeout(() => {
        // Check if user is still active
        fetch("/check-session", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        }).then((response) => {
            if (response.status === 401) {
                window.location.href = "/login";
            }
        });
    }, 55 * 60 * 1000); // Check every 55 minutes
}

// Reset timer on user activity
window.onload = resetTimer;
window.onmousemove = resetTimer;
window.onkeypress = resetTimer;
window.onscroll = resetTimer;
window.onclick = resetTimer;

// Warn before closing if admin is logged in
window.onbeforeunload = function () {
    if (document.querySelector('meta[name="user-role"]')?.content === "admin") {
        return "You have unsaved changes. Are you sure you want to leave?";
    }
};

// Router event handlers
router.on("finish", (event) => {
    const status = event.detail.visit.cache;
});

// Add this inside the setup function or before createInertiaApp
router.on("invalid", (event) => {
    // If the server returns a 404 but we know it should exist,
    // it's often a stale manifest. Force a hard reload.
    if (
        event.detail.response.status === 404 ||
        event.detail.response.status === 419
    ) {
        event.preventDefault();
        window.location.reload();
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    // resolve: async (name) => {
    //     const pages = import.meta.glob("./Pages/**/*.vue");
    //     const page = await pages[`./Pages/${name}.vue`]();
    //     return page.default;
    // },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component("Link", Link)
            .component("Head", Head)
            .component("Breadcrumbs", Breadcrumbs)
            .component("Navbar", Navbar)
            .component("Loader", Loader)
            .component("PullToRefresh", PullToRefresh)
            .component("TrendIcon", TrendIcon)
            .component("FlashMessages", FlashMessages)
            .component("GoldPriceChart", GoldPriceChart)
            .component("Fab", Fab)
            .component("SlideButton", SlideButton)
            .component("UserNavbar", UserNavbar)
            .component("PublicBackButton", PublicBackButton)
            .component("PublicBackIcon", PublicBackIcon)
            .component("UserGoldChart", UserGoldChart)
            .component("MobileBottomNav", MobileBottomNav)
            .component("RateHistoryChart", RateHistoryChart)
            .component("UserBreadcrumbs", UserBreadcrumbs)
            .component("NotificationsBell", NotificationsBell)
            .component("GoogleAnalytics", GoogleAnalytics)
            .component("PublicCurrencyRate", PublicCurrencyRate)
            .component("MyanmarGoldHistoryList", MyanmarGoldHistoryList)
            .component("WorldGoldHistoryList", WorldGoldHistoryList)
            .component("MyanmarOtherRecordsTable", MyanmarOtherRecordsTable)
            .component("WorldOtherRecordsTable", WorldOtherRecordsTable)
            .component("FuelPriceWidget", FuelPriceWidget)
            .component("FuelPriceHistory", FuelPriceHistory)
            .component("AdUnit", AdUnit)

            .component("PublicBreadcrumb", PublicBreadcrumb)
            .component("Paginations", Paginations);

        // ============================================
        // GLOBAL FORMATTING HELPERS
        // ============================================

        // --- Currency & Money Formatting ---
        app.config.globalProperties.$calculateDailyChange = (
            current,
            previous
        ) => {
            if (!previous || !previous.sell_rate || previous.sell_rate == 0)
                return "0.00%";

            const diff =
                parseFloat(current.sell_rate) - parseFloat(previous.sell_rate);
            const percent = (diff / parseFloat(previous.sell_rate)) * 100;

            const formatted = percent.toFixed(2);
            return `${percent > 0 ? "+" : ""}${formatted}%`;
        };

        app.config.globalProperties.$formatYway = (kyatTharPrice) => {
            if (!kyatTharPrice) return "0";
            const price = parseFloat(kyatTharPrice) / 128;
            return new Intl.NumberFormat("en-US", {
                maximumFractionDigits: 0,
            }).format(price);
        };

        app.config.globalProperties.$formatLiveTime = (value) => {
            if (!value) return "—";

            // Convert "2026-03-31 08:10:27" to "2026-03-31T08:10:27"
            // This ensures Javascript sees it as a valid date object
            const dateString =
                typeof value === "string" ? value.replace(/\s+/g, "T") : value;
            const date = new Date(dateString);

            if (isNaN(date.getTime())) return "Invalid Date";

            return date.toLocaleTimeString("en-GB", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true,
            });
        };

        app.config.globalProperties.$calculateGoldPremium = (
            localMmk,
            worldUsd,
            usdMmkRate
        ) => {
            if (!localMmk || !worldUsd || !usdMmkRate) return 0;

            // 1.875 is the conversion factor for 1 Troy Oz to 1 Myanmar Kyat Thar (Traditional)
            const worldInMmk =
                (parseFloat(worldUsd) / 1.875) * parseFloat(usdMmkRate);
            const diff = parseFloat(localMmk) - worldInMmk;

            return (diff / worldInMmk) * 100;
        };

        app.config.globalProperties.$calculateUsdPrice = (
            mmkPrice,
            usdMmkRate
        ) => {
            if (!mmkPrice || !usdMmkRate || parseFloat(usdMmkRate) === 0)
                return 0;
            return parseFloat(mmkPrice) / parseFloat(usdMmkRate);
        };

        // --- New: Trend Direction Finder ---
        app.config.globalProperties.$getTrendDirection = (
            current,
            previous
        ) => {
            if (!previous) return "neutral";
            const curr = parseFloat(current.sell_rate);
            const prev = parseFloat(previous.sell_rate);

            if (curr > prev) return "up";
            if (curr < prev) return "down";
            return "neutral";
        };

        // --- New: Is Today Helper ---
        app.config.globalProperties.$isToday = (dateString) => {
            if (!dateString) return false;
            const d = new Date(dateString);
            const today = new Date();
            return d.toDateString() === today.toDateString();
        };

        // --- Trend Color Helper (Modified to be more robust) ---
        app.config.globalProperties.$getTrendColor = (trend) => {
            if (trend === "up") return "text-emerald-600";
            if (trend === "down") return "text-rose-600";
            return "text-slate-400";
        };

        // --- Money Formatting ---
        app.config.globalProperties.$formatMoney = (value, decimals = 2) => {
            if (!value && value !== 0) return "-";
            return new Intl.NumberFormat("en-US", {
                minimumFractionDigits: decimals,
                maximumFractionDigits: 4,
            }).format(value);
        };

        app.config.globalProperties.$formatCurrency = (
            value,
            symbol = "MMK"
        ) => {
            if (!value && value !== 0) return "-";
            return `${app.config.globalProperties.$formatMoney(
                value
            )} ${symbol}`;
        };

        // --- Factor Formatting (6 decimal places) ---
        app.config.globalProperties.$formatFactor = (factor) => {
            if (!factor && factor !== 0) return "1.000000";
            return Number(factor).toFixed(6);
        };

        // --- Percentage Formatting ---
        app.config.globalProperties.$formatPercentage = function (value) {
            const num = parseFloat(value);
            if (isNaN(num)) return "0.00%"; // Fallback if data is missing
            return num.toFixed(2) + "%";
        };
        // --- Spread Formatting ---
        app.config.globalProperties.$formatSpread = (rate) => {
            if (!rate) return "-";

            if (rate.spread_type === "percentage") {
                const spread = (
                    ((rate.sell_rate - rate.buy_rate) / rate.working_rate) *
                    100
                ).toFixed(2);
                return `${spread}%`;
            } else {
                const spread = rate.sell_rate - rate.buy_rate;
                return app.config.globalProperties.$formatMoney(spread);
            }
        };

        // --- Change Formatting with Trend ---
        app.config.globalProperties.$formatChange = (percentage) => {
            if (!percentage && percentage !== 0)
                return { text: "-", class: "text-slate-400", icon: null };

            const formatted = percentage.toFixed(2);
            const text = `${percentage > 0 ? "+" : ""}${formatted}%`;
            const class_name =
                percentage > 0
                    ? "text-green-600"
                    : percentage < 0
                    ? "text-red-600"
                    : "text-slate-400";
            const icon = percentage > 0 ? "▲" : percentage < 0 ? "▼" : null;

            return { text, class: class_name, icon };
        };

        // --- Trend Color Helper ---
        app.config.globalProperties.$getTrendColor = (trend) => {
            if (trend === "up") return "text-green-600";
            if (trend === "down") return "text-red-600";
            return "text-slate-400";
        };

        // --- Number Formatting ---
        app.config.globalProperties.$formatNumber = (value, decimals = 0) => {
            if (!value && value !== 0) return "-";
            return new Intl.NumberFormat("en-US", {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(value);
        };

        // --- Existing Date/Time Helpers (keeping yours) ---
        app.config.globalProperties.$formatDateTime = (dateString) => {
            if (!dateString) return "—";
            return new Date(dateString).toLocaleString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                hour12: true,
            });
        };

        app.config.globalProperties.$formatTime24 = (value) => {
            if (!value) return "";
            const date = new Date(value);
            return new Intl.DateTimeFormat("en-GB", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: false,
            }).format(date);
        };

        app.config.globalProperties.$formatShortDate = (date) => {
            return new Date(date).toLocaleDateString("en-GB", {
                day: "numeric",
                month: "numeric",
                year: "2-digit",
            });
        };

        app.config.globalProperties.$formatDate = (dateString) => {
            if (!dateString) return "—";
            return new Date(dateString).toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
            });
        };

        app.config.globalProperties.$formatTimeOnly = (value) => {
            if (!value) return "—";
            const date = new Date(value);
            if (isNaN(date.getTime())) return "—";
            return date.toLocaleTimeString("en-GB", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true,
            });
        };

        app.config.globalProperties.$formatDecimal = (value, decimals = 2) => {
            if (!value && value !== 0) return "0.00";
            return new Intl.NumberFormat("en-US", {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(value);
        };

        // --- Router error handling ---
        router.on("error", (event) => {
            if (
                event.detail.errors?.status === 419 ||
                event.detail.message?.includes("419")
            ) {
                window.location.reload();
            }
        });

        window.addEventListener("popstate", () => {
            router.reload({ preserveScroll: true });
        });

        return app.mount(el);
    },
    // progress: {
    //     // color: "#4f46e5",
    // },
});
