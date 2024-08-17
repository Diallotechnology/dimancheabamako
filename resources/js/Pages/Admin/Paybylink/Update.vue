<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";
const props = defineProps({
    paylink: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    name: props.paylink.name,
    contact: props.paylink.contact,
    montant: props.paylink.montant,
});

const submit = () => {
    form.patch(route("paylink.update", props.paylink.id), {
        onSuccess: () => {
            form.name = props.paylink.name;
            form.contact = props.paylink.contact;
            form.montant = props.paylink.montant;
            notify("paiement mise à jour avec success !", true);
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
                        place="le nom du client"
                        label="Nom"
                        v-model="form.name"
                        :message="form.errors.name"
                        required
                    />
                    <Input
                        input_type="text"
                        place="le contact du client"
                        label="contact"
                        v-model="form.contact"
                        :message="form.errors.contact"
                        required
                    />
                    <Input
                        input_type="number"
                        label="Montant en CFA"
                        place="le montant"
                        v-model="form.montant"
                        :message="form.errors.montant"
                        required
                    />
                    <div class="modal-footer">
                        <Link
                            :href="route('paylink')"
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
