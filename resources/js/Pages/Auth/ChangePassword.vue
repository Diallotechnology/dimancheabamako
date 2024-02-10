<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("change_password"), {
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
    <GuestLayout>
        <Head title="ChangePassword" />

        <section class="content-main mt-80 mb-80">
            <div class="card mx-auto card-login">
                <div class="card-body">
                    <h4 class="card-title mb-4 h2">Se connecter</h4>
                    <h4 class="mb-2">Changement de Mot de Passe Requis 🔒</h4>
                    <h6>
                        NB: Utilisez au moins huit (8) caractères, mélangez
                        majuscules, minuscules, chiffres et caractères spéciaux.
                    </h6>
                    <div
                        v-if="status"
                        class="mb-4 font-medium text-sm text-green-600"
                    >
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit">
                        <TextInput
                            type="hidden"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autofocus
                        />

                        <div class="mt-4">
                            <InputLabel
                                for="password"
                                value="Nouveau mot de passe"
                            />

                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
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
                        <!-- form-group form-check .// -->
                        <div class="my-4">
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                >Valider
                            </PrimaryButton>
                        </div>
                        <!-- form-group// -->
                    </form>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
