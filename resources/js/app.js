import "./bootstrap";
// import '../css/app.css';

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

import Select from "@/Components/Select.vue";
import Input from "@/Components/Input.vue";
import InputDate from "@/Components/InputDate.vue";
// import Multiselect from "vue-multiselect";
// import VueMultiselect from "vue-multiselect";

const appName = import.meta.env.VITE_APP_NAME;

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return (
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue)
                .component("Select", Select)
                // .component("VueMultiselect", VueMultiselect)
                .component("InputDate", InputDate)
                .component("Input", Input)
                .mount(el)
        );
    },
    progress: {
        color: "#4B5563",
    },
});
