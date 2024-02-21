import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const notify = (message = "", type) => {
    if (type) {
        toast.success(message);
    } else {
        toast.error("la validation a echoué verifiez vos informations!");
    }
};
export const AddToCard = async (url) => {
    await axios
        .get(url)
        .then((response) => {
            cartnotify(response.data.message, response.data.type);
        })
        .catch(function (error) {
            // handle error
            console.log(error);
        });
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

export let Price_euro = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "EUR",
});

export default notify;
