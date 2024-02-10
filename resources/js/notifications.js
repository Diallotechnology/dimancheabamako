import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const notify = (message = "", type) => {
    if (type) {
        toast.success(message);
    } else {
        toast.error("la validation a echoué verifiez vos informations!");
    }
};
export let Price_format = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "XOF",
});

export default notify;
