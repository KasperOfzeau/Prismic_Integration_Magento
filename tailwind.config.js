/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./view/frontend/**/*.{xml,phtml}"],
    theme: {
        fontFamily: {
            sans: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"'
        },
        container: {
            center: true,
        },
        extend: {
            colors: {
                'orange': '#E7B239',
                'green': '#999A36',
                'green-light': '#e3e5cb',
                'brown': '#57272b',
            },
            maxWidth: {
                '350': '350px',
            },
            minWidth: {
                '350': '350px',
            },
            boxShadow: {
                'card': '0 2px 4px rgba(0,0,0,.1)',
            },
            backgroundImage: {
                'hero': "linear-gradient(180deg,transparent 50%,rgba(0,0,0,.3))",
            },
            screens: {
                'tablet': '1180px',
            },
        },
    },
    plugins: [],
}
