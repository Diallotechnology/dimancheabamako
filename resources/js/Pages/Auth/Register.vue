<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import notify from "@/helper";
defineProps({
    status: {
        type: String,
    },
    pays: {
        type: Object,
        default: () => ({}),
    },
});
const form = useForm({
    email: "",
    password: "",
    password_confirmation: "",
    prenom: "",
    nom: "",
    contact: "",
    pays: "",
});

const submit = () => {
    form.post(route("register"), {
        onSuccess: () => {
            form.reset();
            notify("Compte crée avec success !", true);
        },
        onError: () => {
            notify(false);
        },
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="S'inscrire" />

        <h4 class="card-title mb-4 h2">S'inscrire</h4>
        <div v-if="status" class="mb-4 font-medium text-sm text-success">
            {{ status }}
        </div>
        <form @submit.prevent="submit">
            <div class="row">
                <div class="col-md-6">
                    <InputLabel for="prenom" value="Prénom" />

                    <TextInput
                        id="prenom"
                        type="text"
                        class="mt-1 block w-100"
                        v-model="form.prenom"
                        required
                        autocomplete="prenom"
                    />

                    <InputError class="mt-2" :message="form.errors.prenom" />
                </div>
                <div class="col-md-6">
                    <InputLabel for="nom" value="Nom" />

                    <TextInput
                        id="nom"
                        type="text"
                        class="mt-1 block w-100"
                        v-model="form.nom"
                        required
                        autocomplete="nom"
                    />

                    <InputError class="mt-2" :message="form.errors.nom" />
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-100"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mt-3">
                        <InputLabel for="contact" value="Contact" />

                        <vue-tel-input v-model="form.contact"></vue-tel-input>
                        <InputError
                            class="mt-2"
                            :message="form.errors.contact"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mt-3">
                        <Select2 label="Pays" :message="form.errors.pays">
                            <VueSelect
                                placeholder="selectionner"
                                v-model="form.pays"
                                :options="pays"
                            />
                        </Select2>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <InputLabel for="password" value="Mot de passe" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-100"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirmé le mot de passe"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-100"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="d-flex justify-content-center my-4">
                <p class="me-2">Vous avez dejà un compte?</p>
                <div>
                    <Link
                        :href="route('login')"
                        class="underline text-end text-sm"
                    >
                        Se connecter
                    </Link>
                </div>
            </div>
            <PrimaryButton
                class="ms-4"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Valider
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
