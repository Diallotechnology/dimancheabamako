import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const notify = (message, type) => {
    if (type === "ok") {
        toast.success(message);
    } else if (type === "fail") {
        toast.error("la validation a echoué verifiez vos informations!");
    }
};

export default notify;
