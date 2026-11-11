<script setup lang="ts">
import { computed, useId } from 'vue';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

/**
 * Champ de formulaire : libellé, aide et erreur serveur (form.errors.x) reliés au contrôle.
 * Le slot reçoit `id`, `invalid` et `describedBy` à poser sur l'input :
 *
 *   <FormField label="Nom" :error="form.errors.nom" v-slot="field">
 *       <Input :id="field.id" v-model="form.nom" :aria-invalid="field.invalid" :aria-describedby="field.describedBy" />
 *   </FormField>
 */
const props = defineProps<{
    label: string;
    error?: string;
    hint?: string;
    required?: boolean;
    class?: string;
}>();

const id = useId();
const hintId = `${id}-hint`;
const errorId = `${id}-error`;

const describedBy = computed(
    () => [props.error ? errorId : null, props.hint ? hintId : null].filter(Boolean).join(' ') || undefined,
);
</script>

<template>
    <div :class="cn('grid gap-1.5', props.class)">
        <Label :for="id" class="text-sm font-medium">
            {{ label }}
            <span v-if="required" class="text-destructive" aria-hidden="true">*</span>
        </Label>
        <slot :id="id" :invalid="Boolean(error)" :described-by="describedBy" />
        <p v-if="hint && !error" :id="hintId" class="text-xs text-muted-foreground">{{ hint }}</p>
        <p v-if="error" :id="errorId" class="text-xs font-medium text-destructive" role="alert">{{ error }}</p>
    </div>
</template>
