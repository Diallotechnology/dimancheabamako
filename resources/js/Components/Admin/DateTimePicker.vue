<script setup lang="ts">
import { CalendarDate } from '@internationalized/date';
import { CalendarIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Calendar } from '@/Components/ui/calendar';
import { Input } from '@/Components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';

/**
 * Date + heure (remplace `<input type="datetime-local">`).
 * Le modèle garde le format de l'input natif, `YYYY-MM-DDTHH:mm` : la valeur envoyée au serveur ne change pas.
 * Une valeur de base de données (`YYYY-MM-DD HH:mm:ss`) est acceptée en entrée.
 */
const props = withDefaults(
    defineProps<{
        placeholder?: string;
        disabled?: boolean;
        id?: string;
        invalid?: boolean;
        describedBy?: string;
    }>(),
    {
        placeholder: 'Choisir une date',
        disabled: false,
        id: undefined,
        invalid: false,
        describedBy: undefined,
    },
);

const model = defineModel<string>({ required: true });
const open = ref(false);

const PATTERN = /^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2}))?/;

const parts = computed(() => {
    const match = PATTERN.exec(model.value ?? '');

    if (match === null) {
        return null;
    }

    return {
        year: Number(match[1]),
        month: Number(match[2]),
        day: Number(match[3]),
        time: `${match[4] ?? '00'}:${match[5] ?? '00'}`,
    };
});

const pad = (value: number): string => String(value).padStart(2, '0');

const compose = (date: CalendarDate, time: string): string =>
    `${date.year}-${pad(date.month)}-${pad(date.day)}T${time}`;

const calendarValue = computed(() =>
    parts.value === null ? undefined : new CalendarDate(parts.value.year, parts.value.month, parts.value.day),
);

const time = computed(() => parts.value?.time ?? '00:00');

const label = computed(() =>
    parts.value === null
        ? null
        : `${pad(parts.value.day)}/${pad(parts.value.month)}/${parts.value.year} à ${parts.value.time}`,
);

const pickDate = (value: unknown): void => {
    if (value instanceof CalendarDate) {
        model.value = compose(value, time.value);
    }
};

const pickTime = (value: string | number): void => {
    const next = String(value);

    if (parts.value !== null && next !== '') {
        model.value = compose(new CalendarDate(parts.value.year, parts.value.month, parts.value.day), next);
    }
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                :aria-invalid="invalid || undefined"
                :aria-describedby="describedBy"
                :disabled="disabled"
                :class="
                    cn(
                        'h-9 w-full justify-start gap-2 border-input bg-card px-3 font-normal hover:bg-card',
                        invalid && 'border-destructive',
                    )
                "
            >
                <CalendarIcon class="size-4 shrink-0 text-muted-foreground" aria-hidden="true" />
                <span v-if="label === null" class="truncate text-muted-foreground">{{ placeholder }}</span>
                <span v-else class="truncate">{{ label }}</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <Calendar :model-value="calendarValue" locale="fr-FR" initial-focus @update:model-value="pickDate" />
            <div class="flex items-center gap-2 border-t p-3">
                <label :for="`${id ?? 'dtp'}-time`" class="text-sm text-muted-foreground">Heure</label>
                <Input
                    :id="`${id ?? 'dtp'}-time`"
                    type="time"
                    class="w-auto"
                    :model-value="time"
                    :disabled="parts === null"
                    @update:model-value="pickTime"
                />
            </div>
        </PopoverContent>
    </Popover>
</template>
