<script setup>
import Layout from "@/Shared/Layout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import notify from "@/helper";
import InputError from "@/Components/InputError.vue";
defineProps({
    email_success: {
        type: String,
    },
});

const form = useForm({
    name: "",
    email: "",
    subject: "",
    message: "",
});

const submit = () => {
    form.post(route("contact.email"), {
        onFinish: () => form.reset(),
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <Head title="Contact" />
    <Layout>
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="/" rel="nofollow">Accueil</a>
                    <span></span> Pages <span></span> Contactez-nous
                </div>
            </div>
        </div>
        <section class="section-border pt-50 pb-50">
            <div class="container">
                <!-- <div id="map-panes" class="leaflet-map mb-50"></div> -->
                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <h4 class="mb-15 text-brand">Boutique</h4>
                        ACI 2 000<br />
                        <abbr title="Phone">Phone:</abbr> +223 66 03 51 54<br />
                        <abbr title="Email">Email: </abbr
                        >contact@dimancheabamako.com<br />
                        <a
                            class="btn btn-outline btn-sm btn-brand-outline font-weight-bold text-brand bg-white text-hover-white mt-20 border-radius-5 btn-shadow-brand hover-up"
                        >
                            <i class="fi-rs-marker mr-10"></i> Voir sur map</a
                        >
                    </div>
                </div>
            </div>
        </section>
        <section class="pt-50 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-10 m-auto">
                        <div
                            class="contact-from-area padding-20-row-col wow FadeInUp"
                        >
                            <h3 class="mb-10 text-center">Contactez-nous</h3>
                            <p class="text-muted mb-30 text-center font-sm">
                                Lorem ipsum dolor sit amet consectetur.
                            </p>

                            <div
                                v-if="email_success"
                                class="alert alert-primary text-white text-center"
                                role="alert"
                            >
                                {{ email_success }}
                            </div>

                            <form
                                class="contact-form-style text-center"
                                id="contact-form"
                                @submit.prevent="submit"
                                method="post"
                            >
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-style mb-20">
                                            <input
                                                v-model="form.name"
                                                placeholder="votre nom"
                                                type="text"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="form.errors.name"
                                            />
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-style mb-20">
                                            <input
                                                v-model="form.email"
                                                placeholder="Votre Email"
                                                type="email"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="form.errors.email"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="input-style mb-20">
                                            <input
                                                v-model="form.subject"
                                                placeholder="Objet"
                                                type="text"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="form.errors.subject"
                                            />
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-md-12">
                                        <div class="textarea-style mb-30">
                                            <textarea
                                                v-model="form.message"
                                                placeholder="Message"
                                            ></textarea>
                                            <InputError
                                                class="mt-2"
                                                :message="form.errors.message"
                                            />
                                        </div>

                                        <button
                                            class="submit submit-auto-width"
                                            type="submit"
                                            :class="{
                                                'opacity-25': form.processing,
                                            }"
                                            :disabled="form.processing"
                                        >
                                            Envoyé
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <p class="form-messege"></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>
