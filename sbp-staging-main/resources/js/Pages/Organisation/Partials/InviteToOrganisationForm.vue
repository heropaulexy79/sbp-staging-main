<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from "@/Components/ui/tags-input";
import { Organisation } from "@/types";
import { useForm } from "@inertiajs/vue3";

const props = defineProps<{
    organisation: Organisation;
}>();

const form = useForm({
    invites: [] as string[],
});

const inviteToOrganisation = () => {
    form.transform((d) => {
        return {
            invites: d.invites.map((e) => ({ email: e.toLowerCase() })),
        };
    }).post(route("organisation.invite", { id: props.organisation.id }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
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
            <h2 class="text-lg font-medium">Invite to organisation</h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Invite other members to join your organisation
            </p>
        </header>

        <form @submit.prevent="inviteToOrganisation">
            <div class="space-y-6">
                <div>
                    <Label for="email">Emails</Label>
                    <!-- <Input
                        id="email"
                        type="email"
                        placeholder="Enter the email address"
                        v-model="form.email"
                        class="mt-2"
                        required
                    /> -->
                    <TagsInput
                        v-model="form.invites"
                        class="mt-2 focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
                        add-on-paste
                    >
                        <TagsInputItem
                            v-for="item in form.invites"
                            :key="item"
                            :value="item"
                        >
                            <TagsInputItemText />
                            <TagsInputItemDelete />
                        </TagsInputItem>

                        <TagsInputInput
                            id="email"
                            type="email"
                            placeholder="Enter the email addresses... press enter after each email"
                            class="border-none focus:ring-0"
                        />
                    </TagsInput>
                    <InputError class="mt-2" :message="form.errors.invites" />
                </div>

                <div>
                    <div v-for="(_, k) of form.invites">
                        <!-- @vue-ignore -->
                        <InputError
                            class="mt-2"
                            :message="form.errors?.[`invites.${k}.email`]"
                        />
                    </div>
                </div>

                <div>
                    <Button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Send invite
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>
