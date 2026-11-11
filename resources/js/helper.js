import { ref } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const notify = (message = "", type) => {
    if (type) {
        hidemodal();
        toast.success(message);
    } else {
        toast.error("la validation a echoué verifiez vos informations!");
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

// Pages non migrées : ferme la modale (Components/Modal.vue), Bootstrap n'étant plus chargé.
const hidemodal = () => {
    window.dispatchEvent(new Event("legacy-modal:close"));
};

export default notify;
