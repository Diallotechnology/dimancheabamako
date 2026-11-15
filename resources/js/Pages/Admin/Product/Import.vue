<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, TriangleAlert, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import { toast } from 'vue-sonner';
import { product as productIndex } from '@/actions/App/Http/Controllers/AdminController';
import { review, show, store } from '@/actions/App/Http/Controllers/ProductImportController';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Progress } from '@/Components/ui/progress';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';

/** Limites alignées sur `ProductImportRequest` : le serveur reste l'autorité. */
const MAX_IMAGES = 20;
const MAX_SIZE = 5 * 1024 * 1024;
const MAX_SIZE_MB = 5;
const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];
const POLL_MS = 2000;

/** `ProductImportController::show` : avancement du lot de jobs. */
interface BatchStatus {
    total: number;
    processed: number;
    failed: number;
    finished: boolean;
    progress: number;
}

interface Preview {
    file: File;
    url: string;
}

const previews = ref<Preview[]>([]);
const errors = ref<string[]>([]);
const sending = ref(false);
const batch = ref<BatchStatus | null>(null);
const batchId = ref<string | null>(null);
const pollError = ref<string | null>(null);
/** Lot introuvable côté serveur : le suivi est abandonné. */
const lost = ref(false);

let timer: ReturnType<typeof setTimeout> | null = null;
let stopped = false;

/** `processed` = réussites, `failed` = échecs ; `finished` et `progress` sont calculés côté serveur. */
const handled = (status: BatchStatus): number => status.processed + status.failed;

const done = computed(() => batch.value?.finished ?? false);
const succeeded = computed(() => batch.value?.processed ?? 0);
const percent = computed(() => batch.value?.progress ?? 0);

/** Une URL d'aperçu vit autant que sa vignette : on la libère dès que la vignette disparaît. */
const revokeAll = (): void => {
    previews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
    previews.value = [];
};

const onPick = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const picked = Array.from(input.files ?? []);
    // Le champ ne sert que de sélecteur : les fichiers retenus vivent dans `previews`.
    input.value = '';
    errors.value = [];

    for (const file of picked) {
        if (previews.value.length >= MAX_IMAGES) {
            errors.value.push(`Maximum ${MAX_IMAGES} photos par import.`);
            break;
        }
        if (!ACCEPTED.includes(file.type)) {
            errors.value.push(`${file.name} : format non accepté (jpg, png, webp).`);
        } else if (file.size > MAX_SIZE) {
            errors.value.push(`${file.name} : dépasse ${MAX_SIZE_MB} Mo.`);
        } else {
            previews.value.push({ file, url: URL.createObjectURL(file) });
        }
    }
};

const remove = (index: number): void => {
    const [removed] = previews.value.splice(index, 1);

    if (removed) {
        URL.revokeObjectURL(removed.url);
    }
};

const stopPolling = (): void => {
    stopped = true;

    if (timer !== null) {
        clearTimeout(timer);
        timer = null;
    }
};

const poll = async (id: string): Promise<void> => {
    if (stopped) {
        return;
    }

    try {
        const { data } = await window.axios.get<BatchStatus>(show(id).url);

        // La réponse peut arriver après le démontage de la page : on ne reprogramme alors rien.
        if (stopped) {
            return;
        }

        batch.value = data;
        pollError.value = null;

        if (data.finished) {
            return;
        }
    } catch (error) {
        if (stopped) {
            return;
        }

        // Lot introuvable (404) : inutile de réessayer. Autre erreur : on réessaie au prochain tour.
        const status = (error as { response?: { status?: number } }).response?.status;

        if (status === 404) {
            pollError.value = "Ce lot d'import est introuvable : relancez un import.";
            lost.value = true;

            return;
        }

        pollError.value = 'Suivi interrompu momentanément, nouvelle tentative…';
    }

    timer = setTimeout(() => void poll(id), POLL_MS);
};

const start = async (): Promise<void> => {
    if (previews.value.length === 0 || sending.value) {
        return;
    }

    errors.value = [];
    sending.value = true;

    const form = new FormData();
    previews.value.forEach((preview) => form.append('images[]', preview.file));

    try {
        const { data } = await window.axios.post<{ batch_id: string; total: number }>(store().url, form);

        batch.value = { total: data.total, processed: 0, failed: 0, finished: false, progress: 0 };
        batchId.value = data.batch_id;
        revokeAll();
        stopped = false;
        void poll(data.batch_id);
    } catch (error) {
        const messages = (error as { response?: { data?: { errors?: Record<string, string[]> } } }).response?.data?.errors;

        errors.value = messages ? Object.values(messages).flat() : ["L'envoi a échoué, réessayez."];
        toast.error('Import impossible.');
    } finally {
        sending.value = false;
    }
};

const reset = (): void => {
    stopPolling();
    batch.value = null;
    batchId.value = null;
    pollError.value = null;
    lost.value = false;
    revokeAll();
};

onBeforeUnmount(() => {
    stopPolling();
    revokeAll();
});
</script>

<template>
    <AdminLayoutV2
        title="Import de produits par photos"
        description="Déposez des photos : l'IA prépare une fiche produit par photo."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="productIndex()">
                    <ArrowLeft />
                    Retour aux produits
                </Link>
            </Button>
        </template>

        <div class="grid max-w-4xl gap-5">
            <Alert variant="warning">
                <TriangleAlert />
                <AlertDescription>
                    <p>
                        L'IA crée des <strong>brouillons</strong> invisibles de la boutique. Prix, poids et stock sont à 0 :
                        à la fin de l'import, une page de revue vous permet de tout compléter et de publier d'un coup.
                    </p>
                </AlertDescription>
            </Alert>

            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <!-- Sélection -->
                <div v-if="!batch" class="grid gap-4">
                    <div class="grid gap-1.5">
                        <Label for="import-photos" class="text-sm font-medium">Photos</Label>
                        <Input
                            id="import-photos"
                            type="file"
                            multiple
                            :accept="ACCEPTED.join(',')"
                            :disabled="sending"
                            aria-describedby="import-photos-hint"
                            @change="onPick"
                        />
                        <p id="import-photos-hint" class="text-xs text-muted-foreground">
                            JPG, PNG ou WebP, {{ MAX_IMAGES }} photos maximum, {{ MAX_SIZE_MB }} Mo chacune.
                        </p>
                    </div>

                    <ul v-if="errors.length" class="grid gap-1 text-sm font-medium text-destructive" role="alert">
                        <li v-for="message in errors" :key="message">{{ message }}</li>
                    </ul>

                    <ul v-if="previews.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                        <li
                            v-for="(preview, index) in previews"
                            :key="preview.url"
                            class="relative overflow-hidden rounded-md border bg-muted"
                        >
                            <img :src="preview.url" :alt="preview.file.name" class="aspect-square w-full object-cover" />
                            <Button
                                type="button"
                                variant="secondary"
                                size="icon-xs"
                                class="absolute top-1.5 right-1.5 shadow"
                                :disabled="sending"
                                :aria-label="`Retirer ${preview.file.name}`"
                                @click="remove(index)"
                            >
                                <X />
                            </Button>
                        </li>
                    </ul>

                    <div>
                        <Button type="button" :disabled="previews.length === 0 || sending" @click="start">
                            <Loader2 v-if="sending" class="animate-spin" />
                            {{ sending ? 'Envoi…' : `Lancer l'import (${previews.length})` }}
                        </Button>
                    </div>
                </div>

                <!-- Progression -->
                <div v-else class="grid gap-4" aria-live="polite">
                    <Progress :model-value="percent" class="h-3" aria-label="Avancement de l'import" />
                    <p class="text-sm">
                        {{ percent }} % — {{ handled(batch) }} / {{ batch.total }} traitées
                        <span v-if="batch.failed" class="font-medium text-destructive">
                            — {{ batch.failed }} en échec
                        </span>
                    </p>

                    <p v-if="pollError" class="text-sm font-medium text-destructive" role="alert">{{ pollError }}</p>

                    <div v-if="done" class="grid gap-3">
                        <p v-if="batch.failed" class="text-sm text-destructive">
                            Les photos en échec ne sont pas importées : renvoyez-les dans un nouvel import (les fiches déjà
                            générées sont en cache, l'IA n'est pas rappelée pour elles). Détails dans les logs.
                        </p>
                        <p v-if="succeeded > 0" class="text-sm">{{ succeeded }} brouillon(s) créé(s).</p>
                        <div class="flex flex-wrap gap-2">
                            <Button v-if="succeeded > 0 && batchId" as-child>
                                <Link :href="review(batchId)">Relire et publier</Link>
                            </Button>
                            <Button type="button" variant="outline" @click="reset">Nouvel import</Button>
                        </div>
                    </div>
                    <p v-else-if="!lost" class="text-sm text-muted-foreground">
                        Traitement en cours, vous pouvez rester sur cette page.
                    </p>
                    <div v-else>
                        <Button type="button" variant="outline" @click="reset">Nouvel import</Button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayoutV2>
</template>
