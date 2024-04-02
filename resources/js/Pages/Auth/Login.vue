<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import notify from "@/helper";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
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
        <Head title="Connexion" />
        <div class="card mx-auto card-login">
            <div class="card-body">
                <h4 class="card-title mb-4 h2">Se connecter</h4>
                <div
                    v-if="status"
                    class="mb-4 font-medium text-sm text-success"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Email" />

                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password" value="Password" />

                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />

                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>
                    <div class="mb-3">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="float-end font-sm text-muted"
                        >
                            Mot de passe oublié ?
                        </Link>
                        <Checkbox
                            name="remember"
                            v-model:checked="form.remember"
                        />
                    </div>
                    <div class="d-flex justify-content-center my-4">
                        <p class="me-2">Vous n'avez de un compte?</p>
                        <div>
                            <Link
                                :href="route('register')"
                                class="underline text-end text-sm"
                            >
                                S'inscrire
                            </Link>
                        </div>
                    </div>
                    <!-- form-group form-check .// -->
                    <div class="mb-4">
                        <PrimaryButton
                            :class="{
                                'opacity-25': form.processing,
                            }"
                            :disabled="form.processing"
                            >Valider
                        </PrimaryButton>
                    </div>
                    <!-- form-group// -->
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
