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
});
const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
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
                        class="mt-1 block w-full"
                        v-model="form.prenom"
                        required
                        autofocus
                        autocomplete="prenom"
                    />

                    <InputError class="mt-2" :message="form.errors.prenom" />
                </div>
                <div class="col-md-6">
                    <InputLabel for="nom" value="Nom" />

                    <TextInput
                        id="nom"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.nom"
                        required
                        autofocus
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
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div class="row">
                <div class="col-md-6">
                    <InputLabel for="contact" value="Contact" />

                    <TextInput
                        id="contact"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.contact"
                        required
                        autofocus
                        autocomplete="contact"
                    />

                    <InputError class="mt-2" :message="form.errors.contact" />
                </div>
                <div class="col-md-6">
                    <InputLabel for="pays" value="Pays" />

                    <TextInput
                        id="pays"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.pays"
                        required
                        autofocus
                        autocomplete="pays"
                    />

                    <InputError class="mt-2" :message="form.errors.pays" />
                </div>
            </div>
            <div class="mt-4">
                <InputLabel for="password" value="Mot de passe" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
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
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
