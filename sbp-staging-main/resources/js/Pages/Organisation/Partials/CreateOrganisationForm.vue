<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["onSuccess"]);

const form = useForm({
    name: "",
});

const createOrganisation = () => {
    form.post(route("organisation.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit("onSuccess");
        },
        onError: () => {
            // if (form.errors.password) {
            //     form.reset("password", "password_confirmation");
            //     passwordInput.value?.focus();
            // }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium">Setup organisation</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your organisation is setup, you can invite other members to
                join you!
            </p>
        </header>

        <form @submit.prevent="createOrganisation">
            <div class="space-y-6">
                <div>
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        class="mt-2"
                        placeholder="Enter the name of your organisation"
                        v-model="form.name"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Create organisation
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>
