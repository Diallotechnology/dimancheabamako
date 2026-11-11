<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { promotion as promotionIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/PromotionController';
import Combobox from '@/Components/Admin/Combobox.vue';
import DateTimePicker from '@/Components/Admin/DateTimePicker.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Promotion } from '@/types';

const props = defineProps<{
    promotion: Promotion;
    /** Tous les produits : valeur = id (chaîne). */
    product: Option[];
}>();

const form = useForm<{ nom: string; reduction: string; debut: string; fin: string; product_id: string[] }>({
    nom: props.promotion.nom,
    reduction: String(props.promotion.reduction),
    debut: props.promotion.debut,
    fin: props.promotion.fin,
    product_id: (props.promotion.products ?? []).map((product) => String(product.id)),
});

const submit = (): void => {
    form.submit(update(props.promotion.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Promotion mise à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2
        :title="`Modifier ${promotion.nom}`"
        description="Changez la réduction, la période ou les produits concernés."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="promotionIndex()">
                    <ArrowLeft />
                    Retour aux promotions
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.nom"
                    placeholder="Nom de la promotion"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Réduction (%)" :error="form.errors.reduction" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.reduction"
                    type="number"
                    min="1"
                    max="100"
                    placeholder="Taux de la promotion"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField label="Début de la promotion" :error="form.errors.debut" required v-slot="field">
                    <DateTimePicker
                        :id="field.id"
                        v-model="form.debut"
                        :invalid="field.invalid"
                        :described-by="field.describedBy"
                    />
                </FormField>
                <FormField label="Fin de la promotion" :error="form.errors.fin" required v-slot="field">
                    <DateTimePicker
                        :id="field.id"
                        v-model="form.fin"
                        :invalid="field.invalid"
                        :described-by="field.describedBy"
                    />
                </FormField>
            </div>
            <FormField label="Produits concernés" :error="form.errors.product_id" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.product_id"
                    :options="product"
                    multiple
                    placeholder="Choisir des produits"
                    search-placeholder="Rechercher un produit…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="promotionIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>
