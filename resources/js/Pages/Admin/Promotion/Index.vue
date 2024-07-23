<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, Link } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import { ref, watch } from "vue";
import ButtonShow from "@/Components/ButtonShow.vue";

const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
    filter: {
        type: Object,
        default: () => ({}),
    },
});

let search = ref(props.filter.search);
const Reset = () => {
    search.value = "";
};
watch(search, (value) => {
    setTimeout(() => {
        router.get(
            "/admin/promotion",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
</script>

<template>
    <Head title="Promotion" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des promo</h2>
            </div>
            <div>
                <Link
                    :href="route('promotion.create')"
                    class="btn btn-primary btn-sm rounded"
                    type="button"
                >
                    <i class="material-icons md-plus md-18"></i>

                    Nouveau</Link
                >
            </div>
        </div>
        <div class="card mb-4">
            <header class="card-header">
                <div class="row gx-3">
                    <div class="col-lg-4 col-md-6 me-auto">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Recherche..."
                            class="form-control"
                        />
                    </div>
                    <div class="col-auto">
                        <button @click="Reset" class="btn btn-danger rounded">
                            Reset<i
                                class="material-icons md-delete_forever md-18"
                            ></i>
                        </button>
                    </div>
                </div>
            </header>
            <Table :rows="rows">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th scope="col">Nom</th>
                        <th scope="col">reduction</th>
                        <th scope="col">Debut</th>
                        <th scope="col">fin</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.nom }}</td>
                        <td>{{ row.reduction }} %</td>
                        <td>{{ row.debut }}</td>
                        <td>{{ row.fin }}</td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonShow
                                :href="route('promotion.show', row.id)"
                            />
                            <ButtonEdit
                                :href="route('promotion.edit', row.id)"
                            />

                            <ButtonDelete
                                :url="route('promotion.destroy', row.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
    </AuthenticatedLayout>
</template>
