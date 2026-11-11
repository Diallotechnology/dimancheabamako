<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { user as userIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/UserController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, User } from '@/types';

const props = defineProps<{
    user: User;
    roles: Option[];
}>();

const form = useForm<{ name: string; email: string; role: string | null }>({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
});

const submit = (): void => {
    form.submit(update(props.user.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Utilisateur mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier « ${user.name} »`" description="Mettez à jour l'identité et le rôle de l'utilisateur.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="userIndex()">
                    <ArrowLeft />
                    Retour aux utilisateurs
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nom d'utilisateur" :error="form.errors.name" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.name"
                    placeholder="Le nom de l'utilisateur"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="E-mail" :error="form.errors.email" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.email"
                    type="email"
                    placeholder="adresse@exemple.com"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Rôle (privilège)" :error="form.errors.role" v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.role"
                    :options="roles"
                    placeholder="Choisir un rôle"
                    search-placeholder="Rechercher un rôle…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="userIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>
