<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/notifications";
import { ref } from "vue";

const props = defineProps({
    product: {
        type: Object,
        default: () => ({}),
    },
    category: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    nom: "",
    reduction: "",
    debut: "",
    fin: "",
    product_id: "",
    categorie_id: "",
});

const test = ref();
const submit = () => {
    form.post(route("promotion.store"), {
        onSuccess: () => {
            form.reset();
            notify("Promo ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <AuthenticatedLayout>
        <div class="card">
            <h2 class="p-4 text-center">Formulaire de nouvelle promotion</h2>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <Input
                        input_type="text"
                        place="le nom de la promo"
                        label="Nom"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                    />
                    <Input
                        input_type="number"
                        place="le taux de la promo"
                        label="Reduction en %"
                        v-model="form.reduction"
                        :message="form.errors.reduction"
                        required
                    />
                    <InputDate
                        label="Debut de la promo"
                        v-model="form.debut"
                        :message="form.errors.debut"
                        required
                    />
                    <InputDate
                        label="Fin de la promo"
                        v-model="form.fin"
                        :message="form.errors.fin"
                        required
                    />

                    <Select
                        label="Categorie"
                        v-model="form.categorie_id"
                        :data="category"
                        :message="form.errors.categorie_id"
                    />
                    <Select
                        label="Produit concerne"
                        v-model="form.product_id"
                        :data="product"
                        :message="form.errors.product_id"
                    />
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-danger rounded"
                            data-bs-dismiss="modal"
                        >
                            Fermer
                        </button>
                        <button
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                            class="btn btn-primary rounded"
                        >
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
@/helper
