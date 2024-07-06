<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import ButtonShow from "@/Components/ButtonShow.vue";
import Table from "@/Components/Table.vue";

const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
</script>
<template>
    <AuthenticatedLayout>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <h4 class="text-center">
                        Infos du produit N° {{ product.reference }}
                    </h4>

                    <!--  col.// -->
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <p>Nom: {{ product.nom }}</p>
                        <p>Categorie: {{ product.categorie.nom }}</p>
                        <p>Réference: {{ product.reference }}</p>
                    </div>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <p>Couleur: {{ product.color }}</p>
                        <p>Taille: {{ product.taille }}</p>
                        <p>Poids: {{ product.poids }} Kg</p>
                    </div>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <p>prix: {{ product.prix }}</p>
                        <p>stock: {{ product.taille }}</p>
                    </div>
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <p>Description: {{ product.description }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Produit Image</h5>
                <div class="row">
                    <div
                        class="col-xl-2 col-lg-3 col-md-6"
                        v-for="row in product.images"
                        :key="row"
                    >
                        <div class="card card-product-grid">
                            <a href="#" class="img-wrap">
                                <img v-bind:src="row.chemin" alt="Product" />
                            </a>
                            <div class="info-wrap">
                                <ButtonEdit
                                    :href="route('image.edit', row.id)"
                                />
                                <ButtonDelete
                                    :url="route('image.destroy', row.id)"
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
        <div class="row">
            <div class="col-md-12">
                <header class="card-header">
                    <h4 class="card-title">Liste des Ventes</h4>
                </header>
                <Table>
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th scope="col">Reference</th>
                            <th scope="col">Adresse</th>
                            <th scope="col">Postal</th>
                            <th scope="col">Pays</th>
                            <th scope="col">Ville</th>
                            <th scope="col">Payment</th>
                            <th scope="col">Etat</th>
                            <th scope="col">Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in product.orders" :key="row.id">
                            <td>{{ row.id }}</td>
                            <td class="fw-bold">{{ row.reference }}</td>
                            <td>{{ row.adresse }}</td>
                            <td>{{ row.postal }}</td>
                            <td>{{ row.pays }}</td>
                            <td>{{ row.ville }}</td>
                            <td>
                                {{ row.trans_ref }} <br />{{ row.trans_state }}
                            </td>
                            <td>{{ row.etat }}</td>
                            <td>{{ row.created_at }}</td>
                            <td>
                                <ButtonShow
                                    :href="route('order.show', row.id)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </Table>
            </div>
        </div>

        <!-- content-main end// -->
    </AuthenticatedLayout>
</template>
