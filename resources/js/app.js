import { createApp, h } from "vue";
import "./bootstrap";
import { createInertiaApp } from "@inertiajs/vue3";
import { InertiaProgress } from "@inertiajs/progress";

InertiaProgress.init();

// createInertiaApp({
//     resolve: (name) => require(`./Pages/${name}`),
//     title: (title) => (title ? `${title} - Ping CRM` : "Ping CRM"),
//     setup({ el, App, props, plugin }) {
//         createApp({ render: () => h(App, props) })
//             .use(plugin)
//             .mount(el);
//     },
// });

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
