import "./bootstrap";
// import "../css/app.css";

// import { createSSRApp, h } from "vue";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

const appName = import.meta.env.VITE_APP_NAME;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});

// createServer((page) =>
//     createInertiaApp({
//         page,
//         render: renderToString,
//         resolve: (name) => {
//             const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
//             return pages[`./Pages/${name}.vue`];
//         },
//         setup({ App, props, plugin }) {
//             return createSSRApp({
//                 render: () => h(App, props),
//             }).use(plugin);
//         },
//     })
// );
