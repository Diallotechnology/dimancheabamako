<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import notify from "@/helper";
import { Head, Link } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref } from "vue";

const MAX_IMAGES = 20;
const MAX_SIZE = 5 * 1024 * 1024;
const POLL_MS = 2000;

const files = ref([]);
const errors = ref([]);
const sending = ref(false);
const batch = ref(null);
const batchId = ref(null);
let timer = null;

const previews = computed(() =>
    files.value.map((file) => ({
        name: file.name,
        url: URL.createObjectURL(file),
    }))
);

const done = computed(() => batch.value?.finished === true);
const succeeded = computed(() =>
    batch.value ? batch.value.processed - batch.value.failed : 0
);

const onPick = (event) => {
    errors.value = [];
    const picked = Array.from(event.target.files);
    event.target.value = "";

    for (const file of picked) {
        if (files.value.length >= MAX_IMAGES) {
            errors.value.push(`Maximum ${MAX_IMAGES} photos par import.`);
            break;
        }
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            errors.value.push(`${file.name} : format non accepté (jpg, png, webp).`);
        } else if (file.size > MAX_SIZE) {
            errors.value.push(`${file.name} : dépasse 5 Mo.`);
        } else {
            files.value.push(file);
        }
    }
};

const remove = (index) => files.value.splice(index, 1);

const poll = async (batchId) => {
    try {
        const { data } = await axios.get(route("product.import.show", batchId));
        batch.value = data;
        if (data.finished) {
            return;
        }
    } catch (error) {
        console.log(error);
    }
    timer = setTimeout(() => poll(batchId), POLL_MS);
};

const start = async () => {
    if (files.value.length === 0 || sending.value) {
        return;
    }
    errors.value = [];
    sending.value = true;

    const form = new FormData();
    files.value.forEach((file) => form.append("images[]", file));

    try {
        const { data } = await axios.post(route("product.import.store"), form);
        batch.value = {
            total: data.total,
            processed: 0,
            failed: 0,
            finished: false,
            progress: 0,
        };
        files.value = [];
        batchId.value = data.batch_id;
        poll(data.batch_id);
    } catch (error) {
        const messages = error.response?.data?.errors;
        errors.value = messages
            ? Object.values(messages).flat()
            : ["L'envoi a échoué, réessayez."];
        notify("Import impossible.", false);
    } finally {
        sending.value = false;
    }
};

const reset = () => {
    batch.value = null;
    batchId.value = null;
    files.value = [];
};

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <Head title="Import par photos" />

    <AuthenticatedLayout>
        <div class="content-header">
            <h2 class="content-title card-title">
                Import de produits par photos
            </h2>
            <Link :href="route('product')" class="btn btn-light btn-sm rounded">
                Retour aux produits
            </Link>
        </div>

        <div class="alert alert-warning">
            L'IA crée des <strong>brouillons</strong> invisibles de la boutique.
            Prix, poids et stock sont à 0 : à la fin de l'import, une page de
            revue vous permet de tout compléter et de publier d'un coup.
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <!-- Sélection -->
                <div v-if="!batch">
                    <label class="form-label">
                        Photos (jpg, png, webp — {{ MAX_IMAGES }} max, 5 Mo
                        chacune)
                    </label>
                    <input
                        type="file"
                        class="form-control mb-3"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                        :disabled="sending"
                        @change="onPick"
                    />

                    <ul v-if="errors.length" class="text-danger">
                        <li v-for="message in errors" :key="message">
                            {{ message }}
                        </li>
                    </ul>

                    <div class="row g-2 mb-3">
                        <div
                            v-for="(preview, index) in previews"
                            :key="preview.url"
                            class="col-6 col-md-3 col-xl-2 text-center"
                        >
                            <img
                                :src="preview.url"
                                :alt="preview.name"
                                class="img-fluid rounded mb-1"
                            />
                            <button
                                type="button"
                                class="btn btn-sm btn-danger"
                                @click="remove(index)"
                            >
                                Retirer
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary rounded"
                        :disabled="files.length === 0 || sending"
                        @click="start"
                    >
                        {{
                            sending
                                ? "Envoi..."
                                : `Lancer l'import (${files.length})`
                        }}
                    </button>
                </div>

                <!-- Progression -->
                <div v-else>
                    <div class="progress mb-3" style="height: 1.5rem">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            :style="{ width: batch.progress + '%' }"
                            :aria-valuenow="batch.progress"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        >
                            {{ batch.progress }}%
                        </div>
                    </div>
                    <p>
                        {{ batch.processed }} / {{ batch.total }} traitées
                        <span v-if="batch.failed" class="text-danger">
                            — {{ batch.failed }} en échec
                        </span>
                    </p>

                    <div v-if="done">
                        <p v-if="batch.failed" class="text-danger">
                            Les photos en échec ne sont pas importées :
                            renvoyez-les dans un nouvel import (les fiches déjà
                            générées sont en cache, l'IA n'est pas rappelée
                            pour elles). Détails dans les logs.
                        </p>
                        <p v-if="succeeded > 0">
                            {{ succeeded }} brouillon(s) créé(s).
                        </p>
                        <Link
                            v-if="succeeded > 0"
                            :href="route('product.import.review', batchId)"
                            class="btn btn-primary rounded me-2"
                        >
                            Relire et publier
                        </Link>
                        <button
                            type="button"
                            class="btn btn-light rounded"
                            @click="reset"
                        >
                            Nouvel import
                        </button>
                    </div>
                    <p v-else class="text-muted">
                        Traitement en cours, vous pouvez rester sur cette page.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
