<template>
    <Nav />
    <slot />
</template>

<script>
import { Link } from "@inertiajs/vue3";
import Nav from "../Shared/Nav.vue";
import Logo from "../Shared/Logo.vue";
import FlashMessages from "../Shared/FlashMessages.vue";
// import Icon from "@/Shared/Icon";
// import Dropdown from "@/Shared/Dropdown";

// export default {
//     components: {
//         Dropdown,
//         FlashMessages,
//         Icon,
//         Link,
//         Logo,
//         MainMenu,
//     },
//     props: {
//         auth: Object,
//     },
// };

export default {
    components: {
        Link,
        Nav,
        FlashMessages,
        Logo,
    },
    props: {
        auth: Object,
    },
};
</script>
