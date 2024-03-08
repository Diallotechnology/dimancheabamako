<template>
    <!-- Vous n'avez pas besoin de template dans ce cas -->
</template>

<script>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

export {

        const page = usePage();
        const local = page.props.locale;
        const taux = ref();

        const getDevise = async () => {
            try {
                const response = await axios.get(route("devise.taux"));
                taux.value = response.data;
            } catch (error) {
                console.error(error);
            }
        },

        const convertToPrice = (prixXOF) => {
            const tauxConversion = taux.value;
            const prixEUR = prixXOF / tauxConversion;

            if (local == "fr") {
                return new Intl.NumberFormat("fr-FR", {
                    style: "currency",
                    currency: "EUR",
                }).format(prixEUR.toFixed(2));
            } else if (local == "en") {
                return new Intl.NumberFormat("en-US", {
                    style: "currency",
                    currency: "USD",
                }).format(prixEUR.toFixed(2));
            }
        },

        return { convertToPrice, getDevise }

};
</script>
