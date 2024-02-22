<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";
import { ref, onMounted } from "vue";

const props = defineProps({
    shipping: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    transport: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const pays = ref([]);
const getpays = async (url) => {
    await axios
        .get(url)
        .then((response) => {
            pays.value = response.data;
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
onMounted(() => {
    getpays(route("transport.country", props.shipping.transport_id));
});
const form = useForm({
    poids: props.shipping.poids,
    temps: props.shipping.temps,
    montant: props.shipping.montant,
    transport_id: props.shipping.transport_id,
    country_id: props.shipping.country_id,
});

const submit = () => {
    form.patch(route("shipping.update", props.shipping.id), {
        onSuccess: () => {
            form.poids = props.shipping.poids;
            form.temps = props.shipping.temps;
            form.montant = props.shipping.montant;
            form.transport_id = props.shipping.transport_id;
            form.country_id = props.shipping.country_id;
            notify("livraison mise à jour avec success !", true);
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
                    <Select
                        v-model="form.transport_id"
                        :message="form.errors.transport_id"
                        label="Nom du transporteur"
                        @change="
                            getpays(
                                route('transport.country', form.transport_id)
                            )
                        "
                    >
                        <option
                            v-for="row in transport"
                            :key="row.id"
                            :value="row.id"
                        >
                            {{ row.nom }}
                        </option>
                    </Select>

                    <Select
                        v-model="form.country_id"
                        :message="form.errors.country_id"
                        label="Nom du pays"
                    >
                        <option
                            v-for="row in pays"
                            :key="row.id"
                            :value="row.id"
                        >
                            {{ row.nom }}
                        </option>
                    </Select>
                    <Input
                        input_type="text"
                        place="le temps du transport"
                        label="Temps"
                        v-model="form.temps"
                        :message="form.errors.temps"
                        required
                    />
                    <Input
                        input_type="text"
                        place="le poids du transport"
                        label="Poids"
                        v-model="form.poids"
                        :message="form.errors.poids"
                        required
                    />
                    <Input
                        input_type="number"
                        place="le prix du transport"
                        label="montant"
                        v-model="form.montant"
                        :message="form.errors.montant"
                        required
                    />
                    <div class="modal-footer">
                        <Link
                            :href="route('pays')"
                            class="btn btn-danger rounded"
                            data-bs-dismiss="modal"
                        >
                            Annuler
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
