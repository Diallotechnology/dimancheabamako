import "./bootstrap";
// import '../css/app.css';

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

import Select from "@/Components/Select.vue";
import Input from "@/Components/Input.vue";
import TextArea from "@/Components/TextArea.vue";
import InputDate from "@/Components/InputDate.vue";

// import Multiselect from "vue-multiselect";

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
                // .component("multiselect", Multiselect)
                .component("InputDate", InputDate)
                .component("Input", Input)
                .component("TextArea", TextArea)
                .mount(el)
        );
    },
    progress: {
        color: "#4B5563",
    },
});
