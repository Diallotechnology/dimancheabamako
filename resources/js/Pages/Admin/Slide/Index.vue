<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
import notify from "@/helper";

const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    nom: "",
});

const submit = () => {
    form.post(route("slide.store"), {
        onSuccess: () => {
            form.reset();
            notify("Slide ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>

<template>
    <Head title="Categorie" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des slides</h2>
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
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4" v-for="row in slide" :key="row.id">
                        <div class="card card-product-grid">
                            <a href="#" class="img-wrap">
                                <img src="" alt="Product" />
                            </a>
                            <div class="info-wrap">
                                <ButtonEdit
                                    :href="route('slide.edit', row.id)"
                                />
                                <ButtonDelete
                                    :url="route('slide.destroy', row.id)"
                                />
                            </div>
                        </div>
                        <!-- card-product  end// -->
                    </div>
                </div>
                <!-- row.// -->
            </div>
            <!--  card-body.// -->
        </div>
    </AuthenticatedLayout>
</template>
