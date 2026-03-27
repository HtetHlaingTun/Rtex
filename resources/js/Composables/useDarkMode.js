import { ref, onMounted } from "vue";

export function useDarkMode() {
    const isDark = ref(false);

    const applyTheme = (dark) => {
        isDark.value = dark;
        if (dark) {
            document.documentElement.classList.add("dark");
            localStorage.setItem("theme", "dark");
        } else {
            document.documentElement.classList.remove("dark");
            localStorage.setItem("theme", "light");
        }
    };

    const toggleDark = () => {
        applyTheme(!isDark.value);
    };

    // This initialization runs as soon as the composable is called (Setup phase)
    // Checking this before onMounted helps reduce the layout shift
    const initTheme = () => {
        const savedTheme = localStorage.getItem("theme");
        const systemPrefersDark = window.matchMedia(
            "(prefers-color-scheme: dark)",
        ).matches;

        if (savedTheme === "dark" || (!savedTheme && systemPrefersDark)) {
            isDark.value = true;
            document.documentElement.classList.add("dark");
        } else {
            isDark.value = false;
            document.documentElement.classList.remove("dark");
        }
    };

    initTheme();

    return { isDark, toggleDark };
}
