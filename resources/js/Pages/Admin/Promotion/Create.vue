<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    product: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    nom: "",
    reduction: "",
    debut: "",
    fin: "",
    product_id: [],
});
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
                    <div class="row">
                        <div class="col-md-6">
                            <InputDate
                                label="Debut de la promo"
                                v-model="form.debut"
                                :message="form.errors.debut"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <InputDate
                                label="Fin de la promo"
                                v-model="form.fin"
                                :message="form.errors.fin"
                                required
                            />
                        </div>
                    </div>
                    <Select2
                        label="Produit concerné"
                        :message="form.errors.product_id"
                    >
                        <VueSelect
                            :is-multi="true"
                            placeholder="selectionner"
                            v-model="form.product_id"
                            :options="product"
                        />
                    </Select2>

                    <div class="modal-footer">
                        <Link
                            :href="route('promotion')"
                            role="button"
                            class="btn btn-danger rounded"
                            data-bs-dismiss="modal"
                        >
                            Fermer
                        </Link>
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
