<script setup>
import Layout from "@/Shared/Layout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>
<template>
    <Head title="Validation order" />
    <Layout>
        <section class="section-padding">
            <div class="container pt-25">
                <div class="row">
                    <div class="col-md-6 m-auto">
                        <p class="mb-50"></p>
                        <h1 class="mb-4">Opération terminée.</h1>
                        <h4 class="mb-4">
                            Félicitations, votre paiement a été effectué avec
                            succès.
                        </h4>
                        <h4 class="mb-4">
                            Vous allez bientot recevoir un email de confirmation
                            avec la facture.
                        </h4>

                        <Link
                            class="btn btn-default submit-auto-width font-xs hover-up"
                            :href="route('home')"
                            >Retour à la page d'accueil</Link
                        >
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>
