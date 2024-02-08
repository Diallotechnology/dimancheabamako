<template>
    <button
        type="button"
        @click.prevent="remove(props.url)"
        class="btn btn-sm btn-danger font-sm rounded"
    >
        <i class="material-icons md-delete_forever"></i>
        <!-- Edit -->
    </button>
</template>
<script setup>
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
const props = defineProps({
    url: {
        type: String,
        required: true,
    },
});
const remove = (url) => {
    Swal.fire({
        title: "supprimer ?",
        text: "Etes vous sur de vouloir supprimer cet element!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#088178",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, Supprimer!",
        cancelButtonText: "Non, Annuler!",
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .delete(url)
                .then((response) => {
                    console.log(response);
                    if (response.data.success) {
                        Swal.fire({
                            title: "Supprimé!",
                            text: "Votre element a été supprimé.",
                            icon: "success",
                        });
                        router.reload();
                    }
                })
                .catch(function (error) {
                    // handle error
                    if (error.response.status) {
                        Swal.fire({
                            title: "Suppression a échoué !",
                            text: "element non trouvé",
                            icon: "error",
                        });
                    }
                });
        }
    });
};
</script>
