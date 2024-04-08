import "./bootstrap";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

import Select from "@/Components/Select.vue";
import Select2 from "@/Components/Select2.vue";
import Input from "@/Components/Input.vue";
import TextArea from "@/Components/TextArea.vue";
import InputDate from "@/Components/InputDate.vue";
import VueSelect from "vue3-select-component";
import VueTelInput from "vue-tel-input";
import "vue3-select-component/dist/style.css";
import "vue-tel-input/vue-tel-input.css";
const globalOptions = {
    mode: "international",
};
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
            .component("Select", Select)
            .component("Select2", Select2)
            .component("VueSelect", VueSelect)
            .component("InputDate", InputDate)
            .component("Input", Input)
            .component("TextArea", TextArea)
            .component("VueTelInput", VueTelInput)
            .use(VueTelInput, globalOptions)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
