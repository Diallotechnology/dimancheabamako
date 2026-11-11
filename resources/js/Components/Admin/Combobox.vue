<script setup lang="ts">
import { Check, ChevronsUpDown, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/Components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/Components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';
import type { Option } from '@/types';

/**
 * Liste déroulante avec recherche (remplace Select2 + VueSelect).
 * Options au format `{ label, value }` produit par App\Support\Options::from() : valeurs en chaîne.
 * `multiple` : le modèle devient un tableau de valeurs et la sélection reste ouverte.
 */
const props = withDefaults(
    defineProps<{
        options: Option[];
        multiple?: boolean;
        placeholder?: string;
        searchPlaceholder?: string;
        emptyText?: string;
        disabled?: boolean;
        id?: string;
        invalid?: boolean;
        describedBy?: string;
    }>(),
    {
        multiple: false,
        placeholder: 'Choisir…',
        searchPlaceholder: 'Rechercher…',
        emptyText: 'Aucun résultat.',
        disabled: false,
        id: undefined,
        invalid: false,
        describedBy: undefined,
    },
);

const model = defineModel<string | string[] | null>({ required: true });
const open = ref(false);

const selectedValues = computed<string[]>(() => {
    if (Array.isArray(model.value)) {
        return model.value.map(String);
    }

    return model.value === null || model.value === '' ? [] : [String(model.value)];
});

const selectedOptions = computed(() =>
    selectedValues.value
        .map((value) => props.options.find((option) => option.value === value))
        .filter((option): option is Option => option !== undefined),
);

const isSelected = (value: string): boolean => selectedValues.value.includes(value);

const choose = (value: string): void => {
    if (!props.multiple) {
        model.value = value;
        open.value = false;

        return;
    }

    model.value = isSelected(value)
        ? selectedValues.value.filter((selected) => selected !== value)
        : [...selectedValues.value, value];
};

const unselect = (value: string): void => {
    model.value = selectedValues.value.filter((selected) => selected !== value);
};

/** Au-delà, le déclencheur résume la sélection pour ne pas grandir sans fin. */
const MAX_CHIPS = 3;
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :disabled="disabled"
                :class="
                    cn(
                        'h-auto min-h-9 w-full justify-between gap-2 border-input bg-card px-3 py-1.5 font-normal hover:bg-card',
                        invalid && 'border-destructive',
                    )
                "
            >
                <span v-if="selectedOptions.length === 0" class="truncate text-muted-foreground">
                    {{ placeholder }}
                </span>
                <span v-else-if="!multiple" class="truncate">{{ selectedOptions[0]?.label }}</span>
                <span v-else class="flex flex-wrap gap-1">
                    <span
                        v-for="option in selectedOptions.slice(0, MAX_CHIPS)"
                        :key="option.value"
                        class="inline-flex items-center gap-1 rounded-sm bg-accent py-0.5 pr-1 pl-2 text-xs text-accent-foreground"
                    >
                        {{ option.label }}
                        <span
                            role="button"
                            tabindex="-1"
                            class="rounded-sm p-0.5 hover:bg-accent-foreground/15"
                            :aria-label="`Retirer ${option.label}`"
                            @click.stop="unselect(option.value)"
                            @pointerdown.stop
                        >
                            <X class="size-3" />
                        </span>
                    </span>
                    <span v-if="selectedOptions.length > MAX_CHIPS" class="px-1 py-0.5 text-xs text-muted-foreground">
                        +{{ selectedOptions.length - MAX_CHIPS }}
                    </span>
                </span>
                <ChevronsUpDown class="ml-auto size-4 shrink-0 text-muted-foreground" aria-hidden="true" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-(--reka-popper-anchor-width) min-w-56 p-0" align="start">
            <Command>
                <CommandInput :placeholder="searchPlaceholder" />
                <CommandList>
                    <CommandEmpty>{{ emptyText }}</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="option.value"
                            :value="option.label"
                            @select="choose(option.value)"
                        >
                            <span class="truncate">{{ option.label }}</span>
                            <Check
                                :class="cn('ml-auto size-4 text-accent-foreground', isSelected(option.value) ? 'opacity-100' : 'opacity-0')"
                            />
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
