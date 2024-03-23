import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import axios from "axios";

const showModal = () => {
    // Using vanilla JavaScript to trigger the modal
    const modal = document.getElementById("quickViewModal");
    if (modal) {
        // Ensure the modal is hidden initially
        modal.style.display = "none";
        // Use Bootstrap's modal method to show the modal
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    }
};

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

export const handleModalHidden = () => {
    // Gérer le focus après la fermeture du modal
    const actionButton = document.getElementById("quickViewModal");
    const modalInstance = bootstrap.Modal.getInstance(actionButton);
    if (modalInstance) {
        modalInstance.hide();
        actionButton.focus();
    }
};

const notify = (message = "", type) => {
    if (type) {
        hidemodal();
        toast.success(message);
    } else {
        toast.error("la validation a echoué verifiez vos informations!");
    }
};

export const AddToCard = async (url) => {
    try {
        await axios.get(url).then((response) => {
            document.dispatchEvent(new CustomEvent("cart-updated", {}));

            if (response.data.type) {
                showModal();
            } else {
                cartnotify(response.data.message, response.data.type);
            }
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

export default notify;
