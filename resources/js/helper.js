import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
const notify = (message = "", type) => {
    if (type) {
        toast.success(message);
    } else {
        toast.error("la validation a echoué verifiez vos informations!");
    }
};
export const AddToCard = async (url) => {
    try {
        await axios.get(url).then((response) => {
            document.dispatchEvent(new CustomEvent("cart-updated", {}));
            cartnotify(response.data.message, response.data.type);
        });
    } catch (error) {
        console.error(error);
    }
};

export let cartnotify = (message = "", type) => {
    if (type) {
        toast.success(message);
    } else {
        toast.error(message);
    }
};

export let Price_format = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "XOF",
});

export const useCurrencyConverter = () => {
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
    };

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
    };

    return { convertToPrice };
};
export default notify;
