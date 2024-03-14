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
    poids: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const zone = ref([]);
const getzone = async (url) => {
    await axios
        .get(url)
        .then((response) => {
            zone.value = response.data;
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
onMounted(() => {
    getzone(route("transport.zone", props.shipping.transport_id));
});
const form = useForm({
    temps: props.shipping.temps,
    montant: props.shipping.montant,
    poids_id: `${props.shipping.poids_id}`,
    transport_id: props.shipping.transport_id,
    zone_id: `${props.shipping.zone_id}`,
});

const submit = () => {
    form.patch(route("shipping.update", props.shipping.id), {
        onSuccess: () => {
            form.poids = props.shipping.poids;
            form.temps = props.shipping.temps;
            form.montant = props.shipping.montant;
            form.poids_id = `${props.shipping.poids_id}`;
            form.transport_id = props.shipping.transport_id;
            form.zone_id = `${props.shipping.zone_id}`;
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
                            getzone(route('transport.zone', form.transport_id))
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
                    <Select2
                        label="Nom de la zone"
                        :message="form.errors.zone_id"
                    >
                        <VueSelect
                            placeholder="selectionner"
                            v-model="form.zone_id"
                            :options="zone"
                        />
                    </Select2>

                    <Select2 label="Poids" :message="form.errors.poids_id">
                        <VueSelect
                            placeholder="selectionner"
                            v-model="form.poids_id"
                            :options="poids"
                        />
                    </Select2>

                    <Input
                        input_type="text"
                        place="le temps du transport"
                        label="Temps"
                        v-model="form.temps"
                        :message="form.errors.temps"
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
                            :href="route('shipping')"
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
