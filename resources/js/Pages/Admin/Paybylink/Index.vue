<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import notify from "@/helper";

const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    name: "",
    contact: "",
    montant: "",
});

const submit = () => {
    form.post(route("paylink.store"), {
        onSuccess: () => {
            form.reset();

            notify("lien generer avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
function copyMe(link) {
    navigator.clipboard.writeText(link);
    navigator.share({
        title: "Lien de paiement",
        text: "Expire dans 5 minutes",
        url: link,
    });

    notify("lien copié dans le presse papier !", true);
}
</script>
<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des paiements</h2>
            </div>

            <div>
                <a
                    href="#"
                    class="btn btn-primary btn-sm rounded"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#exampleModal"
                >
                    <i class="material-icons md-plus md-18"></i>

                    Nouveau</a
                >
                <Link
                    :href="route('dashboard')"
                    class="btn btn-sm font-sm rounded btn-brand m-1"
                >
                    dashboard
                </Link>
            </div>
        </div>
        <Table :rows="rows">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th scope="col">Transaction</th>
                    <th scope="col">Nom</th>
                    <th scope="col">contact</th>
                    <th scope="col">montant</th>
                    <th scope="col">lien</th>
                    <th scope="col">etat</th>
                    <th scope="col">Date</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows.data" :key="row.id">
                    <td>{{ row.id }}</td>
                    <td>
                        {{ row.trans_ref }} <br />
                        {{ row.trans_state }}
                    </td>
                    <td>{{ row.name }}</td>
                    <td>{{ row.contact }}</td>
                    <td>{{ row.montant }}</td>
                    <td>
                        <a @click="copyMe(row.lien)"> {{ row.lien }} </a>
                    </td>
                    <td>{{ row.etat }}</td>
                    <td>{{ row.created_at }}</td>
                    <td>
                        <Link
                            :href="route('paylink.regenerate', row.id)"
                            class="btn btn-sm font-sm rounded btn-brand m-1"
                        >
                            <i class="material-icons md-change_circle"></i>
                        </Link>
                        <ButtonEdit :href="route('paylink.edit', row.id)" />
                        <ButtonDelete :url="route('paylink.destroy', row.id)" />
                    </td>
                </tr>
            </tbody>
        </Table>
        <Modal name="Formulaire de nouveaux paiement">
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
        </Modal>
    </AuthenticatedLayout>
</template>
