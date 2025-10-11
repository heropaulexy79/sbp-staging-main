<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdateOrganisationForm from "@/Pages/Organisation/Partials/UpdateOrganisationForm.vue";
import {
    Organisation,
    OrganisationInvite,
    OrganisationUser,
    User,
} from "@/types";
import { Head } from "@inertiajs/vue3";
import TeamSettings from "./Partials/TeamSettings.vue";

const props = defineProps<{
    organisation: Organisation;
    employees: OrganisationUser[];
    invites: OrganisationInvite[];
}>();
</script>

<template>
    <Head title="Organisation Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            ></h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                >
                    <UpdateOrganisationForm
                        :organisation="props.organisation"
                        class="max-w-xl"
                    />
                </div>

                <div
                    class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                >
                    <!-- class="max-w-xl"  -->
                    <TeamSettings
                        :organisation="organisation"
                        :employees="employees"
                        :invites="invites"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
