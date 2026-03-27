import { onMounted, onUnmounted } from "vue";

export function useEscapeShortcut(callback) {
    const handleKeyDown = (e) => {
        if (e.key === "Escape") {
            callback();
        }
    };

    onMounted(() => {
        window.addEventListener("keydown", handleKeyDown);
    });

    onUnmounted(() => {
        window.removeEventListener("keydown", handleKeyDown);
    });
}
