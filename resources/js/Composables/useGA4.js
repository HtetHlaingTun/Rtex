// Composables/useGA4.js
export function useGA4() {
    const isGtagAvailable = () => {
        return typeof window !== "undefined" && window.gtag;
    };

    const sendEvent = (eventName, eventParams = {}) => {
        if (isGtagAvailable()) {
            window.gtag("event", eventName, eventParams);
            if (process.env.NODE_ENV === "development") {
                console.log(`GA4 Event: ${eventName}`, eventParams);
            }
        } else if (process.env.NODE_ENV === "development") {
            console.warn("GTag not available - event not sent:", eventName);
        }
    };

    // Key events for MMRatePro
    const trackPageView = (pageTitle, pageLocation) => {
        sendEvent("page_view", {
            page_title: pageTitle,
            page_location: pageLocation,
        });
    };

    const trackFormStart = (formName, formId = null) => {
        sendEvent("form_start", {
            form_name: formName,
            form_id: formId,
            engagement_time_msec: 100,
        });
    };

    const trackFormSubmit = (formName, success = true, errorMessage = null) => {
        const eventParams = {
            form_name: formName,
            success: success,
        };

        if (errorMessage) {
            eventParams.error_message = errorMessage;
        }

        if (success) {
            // generate_lead is a recommended GA4 event for conversions
            sendEvent("generate_lead", eventParams);
        } else {
            sendEvent("form_submit_error", eventParams);
        }
    };

    const trackScroll = (depth, pageTitle) => {
        sendEvent("scroll", {
            scroll_depth: depth,
            page_title: pageTitle,
        });
    };

    const trackClick = (elementText, elementId, elementClass, url = null) => {
        const eventParams = {
            link_text: elementText || "unknown",
            link_url: url || window.location.href,
        };

        if (elementId) eventParams.link_id = elementId;
        if (elementClass) eventParams.link_class = elementClass;

        sendEvent("click", eventParams);
    };

    const trackSubscribe = (success = true) => {
        sendEvent("subscribe", {
            success: success,
            source: "newsletter_footer",
            method: "email",
        });
    };

    const trackRateView = (currency, rateType) => {
        sendEvent("view_item", {
            currency: currency,
            rate_type: rateType,
            engagement_time_msec: 500,
        });
    };

    const trackSearch = (searchTerm, resultsCount = null) => {
        const eventParams = {
            search_term: searchTerm,
        };

        if (resultsCount !== null) {
            eventParams.results_count = resultsCount;
        }

        sendEvent("search", eventParams);
    };

    const trackShare = (method, contentType, itemId = null) => {
        const eventParams = {
            share_method: method,
            content_type: contentType,
        };

        if (itemId) eventParams.item_id = itemId;

        sendEvent("share", eventParams);
    };

    return {
        sendEvent,
        trackPageView,
        trackFormStart,
        trackFormSubmit,
        trackScroll,
        trackClick,
        trackSubscribe,
        trackRateView,
        trackSearch,
        trackShare,
    };
}
