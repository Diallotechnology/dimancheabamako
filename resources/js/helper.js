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

const hidemodal = () => {
    // Using vanilla JavaScript to trigger the modal
    const modal = document.getElementById("exampleModal");
    if (modal) {
        // Ensure the modal is hidden initially
        modal.style.display = "none";
        // Use Bootstrap's modal method to show the modal
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modalInstance.hide();
            modal.focus();
        }
    }
};

export default notify;
