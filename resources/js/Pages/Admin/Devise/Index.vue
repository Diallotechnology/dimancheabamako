<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import Table from "@/Components/Table.vue";

const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Devise" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des devise</h2>
            </div>
        </div>
        <div class="card mb-4">
            <Table :rows="rows">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th scope="col">type</th>
                        <th scope="col">taux</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.type }}</td>
                        <td>{{ row.taux }}</td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonEdit :href="route('devise.edit', row.id)" />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
    </AuthenticatedLayout>
</template>
