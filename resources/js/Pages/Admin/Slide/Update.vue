<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    slide: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    text_one: props.slide.text_one,
    text_two: props.slide.text_two,
    paragraph: props.slide.paragraph,
    image: null,
    _method: "PUT",
});

const submit = () => {
    form.post(route("slide.update", props.slide.id), {
        forceFormData: true,
        onSuccess: () => {
            form.text_one = props.slide.text_one;
            form.text_two = props.slide.text_two;
            form.paragraph = props.slide.paragraph;
            notify("slide mise à jour avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <AuthenticatedLayout>
        <div class="card">
            <h2 class="p-4 text-center">Formulaire de mise à jour</h2>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <Input
                        input_type="text"
                        label="text 1"
                        place="le text du slide"
                        v-model="form.text_one"
                        :message="form.errors.text_one"
                        required
                    />
                    <Input
                        input_type="text"
                        label="text 2"
                        place="le text du slide"
                        v-model="form.text_two"
                        :message="form.errors.text_two"
                        required
                    />
                    <Input
                        input_type="text"
                        label="text 3"
                        place="le text du slide"
                        v-model="form.paragraph"
                        :message="form.errors.paragraph"
                        required
                    />
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="text-uppercase form-label"
                                >Images du slide</label
                            >
                            <input
                                class="form-control"
                                name="image"
                                @input="form.image = $event.target.files[0]"
                                type="file"
                            />
                            <div v-show="form.errors.image">
                                <p class="text-sm text-danger">
                                    {{ form.errors.image }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <Link
                            :href="route('slide')"
                            class="btn btn-danger rounded"
                            data-bs-dismiss="modal"
                        >
                            Annuler
                        </Link>
                        <button
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                            class="btn btn-primary rounded"
                        >
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
