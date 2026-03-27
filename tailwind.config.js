import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
const plugin = require("tailwindcss/plugin");

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],
    darkMode: "class",
    theme: {
        extend: {
            fontFamily: {
                // sans: ["Figtree", ...defaultTheme.fontFamily.sans],
                // Set Inter as the primary UI font
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
                // Set JetBrains Mono for prices and numbers
                mono: ["JetBrains Mono", ...defaultTheme.fontFamily.mono],
            },
            // Safe Area Spacing merged here
            spacing: {
                "safe-top": "env(safe-area-inset-top)",
                "safe-bottom": "env(safe-area-inset-bottom)",
                "safe-left": "env(safe-area-inset-left)",
                "safe-right": "env(safe-area-inset-right)",
            },
            screens: {
                // This creates a custom breakpoint for your 471px requirement
                xs: "471px",
                sm: "758px",

                md: "1000px",
            },

            animation: {
                shine: "shine 3s infinite linear",
                float: "float 6s ease-in-out infinite",
            },
            keyframes: {
                shine: {
                    "0%": { transform: "translateX(-100%) skewX(-15deg)" },
                    "50%": { transform: "translateX(100%) skewX(-15deg)" },
                    "100%": { transform: "translateX(100%) skewX(-15deg)" },
                },
                float: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-10px)" },
                },
            },

            letterSpacing: {
                widest: ".25em",
            },
            borderRadius: {
                "4xl": "2rem",
            },
            boxShadow: {
                gold: "0 20px 50px rgba(245, 158, 11, 0.2)",
                "inner-soft": "inset 0 4px 12px 0 rgba(0, 0, 0, 0.03)",
            },
        },
    },

    plugins: [
        forms,
        // Safe Area Utilities merged here
        plugin(function ({ addUtilities }) {
            addUtilities({
                ".p-safe": {
                    paddingTop: "env(safe-area-inset-top)",
                    paddingBottom: "env(safe-area-inset-bottom)",
                    paddingLeft: "env(safe-area-inset-left)",
                    paddingRight: "env(safe-area-inset-right)",
                },
                ".pb-safe": { paddingBottom: "env(safe-area-inset-bottom)" },
                ".pt-safe": { paddingTop: "env(safe-area-inset-top)" },
            });
        }),
    ],
};
