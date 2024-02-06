<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import { Head } from "@inertiajs/vue3";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Modal from "@/Components/Modal.vue";
import Form from "@/Components/Form.vue";
</script>

<template>
    <Head title="Produit" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Liste des produits</h2>
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
            <header class="card-header">
                <div class="row gx-3">
                    <div class="col-lg-4 col-md-6 me-auto">
                        <input
                            type="text"
                            placeholder="Search..."
                            class="form-control"
                        />
                    </div>
                    <div class="col-lg-2 col-6 col-md-3">
                        <select class="form-select">
                            <option>All category</option>
                            <option>Electronics</option>
                            <option>Clothings</option>
                            <option>Something else</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-6 col-md-3">
                        <select class="form-select">
                            <option>Latest added</option>
                            <option>Cheap first</option>
                            <option>Most viewed</option>
                        </select>
                    </div>
                </div>
            </header>
            <Table> </Table>
            <!-- card-header end// -->
            <div class="card-body">
                <div
                    class="row gx-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 row-cols-xxl-5"
                >
                    <div class="col">
                        <div class="card card-product-grid">
                            <a href="#" class="img-wrap">
                                <img
                                    v-bind:src="'assets/imgs/items/1.jpg'"
                                    alt="Product"
                                />
                            </a>
                            <div class="info-wrap">
                                <a href="#" class="title text-truncate"
                                    >Just another product name</a
                                >
                                <div class="price mb-2">$179.00</div>
                                <!-- price.// -->
                                <ButtonEdit href="" />
                                <ButtonDelete href="" />
                            </div>
                        </div>
                        <!-- card-product  end// -->
                    </div>
                </div>
                <!-- row.// -->
            </div>
            <!-- card-body end// -->
        </div>
        <!-- card end// -->
        <div class="pagination-area mt-30 mb-50">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-start">
                    <li class="page-item active">
                        <a class="page-link" href="#">01</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">02</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">03</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link dot" href="#">...</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">16</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#"
                            ><i class="material-icons md-chevron_right"></i
                        ></a>
                    </li>
                </ul>
            </nav>
        </div>
        <Modal name="Formulaire de nouveau produit">
            <Form> </Form>
        </Modal>
    </AuthenticatedLayout>
</template>
