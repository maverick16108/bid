/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Fira Sans"', 'system-ui', 'sans-serif'],
                mono: ['"Fira Code"', 'monospace'],
            },
            colors: {
                // Semantic aliases for the Swiss Design System
                primary: '#0F172A',   // Navy
                secondary: '#334155', // Slate
                cta: '#0369A1',       // Sky Blue (Primary Action)
                'cta-hover': '#0284C7',
                surface: '#FFFFFF',
                background: '#F8FAFC',
            }
        },
    },
    plugins: [],
}
