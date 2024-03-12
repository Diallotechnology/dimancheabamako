<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    promotion: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    product: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});

const test = [{ label: "24", value: "24" }];
// const test = props.promotion.products.map((row) => row.id);
console.log(test);
const form = useForm({
    nom: props.promotion.nom,
    reduction: props.promotion.reduction,
    debut: props.promotion.debut,
    fin: props.promotion.fin,
    product_id: [],
});
// `${props.product.categorie_id}`
const submit = () => {
    form.post(route("promotion.update"), {
        onSuccess: () => {
            form.nom = props.promotion.nom;
            form.reduction = props.promotion.reduction;
            form.debut = props.promotion.debut;
            form.fin = props.promotion.fin;
            form.product_id = props.promotion.products.map((row) => row.id);
            notify("Promo mise à jour avec success !", true);
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
            <h2 class="p-4 text-center">Formulaire de mise à jour</h2>
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
                            :options="
                                product.map((p) => ({
                                    label: p.label,
                                    value: p.value,
                                }))
                            "
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
