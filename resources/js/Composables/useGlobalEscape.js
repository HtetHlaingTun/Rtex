import { onMounted, onUnmounted } from "vue";

const escapeHandlers = new Set();

const handleGlobalEscape = (event) => {
    if (event.key === "Escape") {
        // Call all registered handlers (last registered gets priority)
        const handlers = Array.from(escapeHandlers);
        if (handlers.length > 0) {
            handlers[handlers.length - 1](); // Close top-most modal only
            // OR use this to close all:
            // handlers.forEach(handler => handler())
        }
    }
};

export function registerEscapeHandler(handler) {
    escapeHandlers.add(handler);

    // Start listening if this is the first handler
    if (escapeHandlers.size === 1) {
        window.addEventListener("keydown", handleGlobalEscape);
    }

    return () => {
        escapeHandlers.delete(handler);
        // Remove listener when no handlers remain
        if (escapeHandlers.size === 0) {
            window.removeEventListener("keydown", handleGlobalEscape);
        }
    };
}
