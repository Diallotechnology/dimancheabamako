<script setup lang="ts">
import { CurveType } from '@unovis/ts';
import { VisArea, VisAxis, VisCrosshair, VisLine, VisScatter, VisXYContainer } from '@unovis/vue';
import { computed } from 'vue';
import type { ChartConfig } from '@/Components/ui/chart';
import { ChartContainer, ChartTooltip, ChartTooltipContent, componentToString } from '@/Components/ui/chart';

/**
 * Courbe du nombre de commandes par jour (tableau de bord).
 * `labels` et `values` sont les deux listes parallèles envoyées par AdminController::dashboard
 * (dates `YYYY-MM-DD` triées, nombre de commandes du jour).
 */
interface OrdersPoint {
    label: string;
    count: number;
}

const props = defineProps<{
    labels: string[];
    values: number[];
}>();

const config = {
    count: { label: 'Commandes', color: 'var(--chart-1)' },
} satisfies ChartConfig;

const dayFormat = new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short' });
const fullFormat = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'long' });

const toDate = (iso: string): Date => new Date(`${iso}T00:00:00`);

const points = computed<OrdersPoint[]>(() =>
    props.labels.map((label, index) => ({ label, count: props.values[index] ?? 0 })),
);

/** Au plus 6 repères sur l'axe des dates, répartis régulièrement. */
const tickValues = computed<number[]>(() => {
    const last = points.value.length - 1;
    if (last < 0) return [];
    const step = Math.max(1, Math.ceil(last / 5));
    const ticks: number[] = [];
    for (let i = 0; i <= last; i += step) ticks.push(i);
    return ticks;
});

const x = (_: OrdersPoint, index: number) => index;
const y = (point: OrdersPoint) => point.count;

const tooltip = computed(() =>
    componentToString(config, ChartTooltipContent, {
        valueFormatter: (value: number) => `${value} commande${value > 1 ? 's' : ''}`,
        labelFormatter: (index: number | Date) => {
            const point = points.value[index as number];
            return point ? fullFormat.format(toDate(point.label)) : '';
        },
    }),
);
</script>

<template>
    <ChartContainer :config="config" class="h-64 w-full min-w-0 overflow-hidden md:h-72">
        <VisXYContainer :data="points" :padding="{ top: 8, right: 8 }">
            <VisArea :x="x" :y="y" :color="config.count.color" :opacity="0.15" :curve-type="CurveType.MonotoneX" />
            <VisLine :x="x" :y="y" :color="config.count.color" :line-width="2" :curve-type="CurveType.MonotoneX" />
            <VisScatter
                :x="x"
                :y="y"
                :color="config.count.color"
                :size="8"
                stroke-color="var(--background)"
                :stroke-width="2"
            />
            <VisAxis
                type="x"
                :tick-values="tickValues"
                :tick-format="(index: number) => (points[index] ? dayFormat.format(toDate(points[index].label)) : '')"
                :grid-line="false"
                :tick-line="false"
                :domain-line="false"
                :tick-text-font-size="12"
            />
            <VisAxis
                type="y"
                :tick-format="(value: number) => (Number.isInteger(value) ? String(value) : '')"
                :grid-line="true"
                :tick-line="false"
                :domain-line="false"
                :num-ticks="4"
                :tick-text-font-size="12"
            />
            <VisCrosshair :color="config.count.color" :template="tooltip" />
            <ChartTooltip />
        </VisXYContainer>
    </ChartContainer>
</template>
