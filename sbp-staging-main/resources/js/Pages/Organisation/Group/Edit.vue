<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Group, User } from "@/types";
import UpdateGroupForm from "./Partials/UpdateGroupForm.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

defineProps<{
    group: Group;
    users: User[];
}>();
</script>

<template>
    <Head title="Edit Group" />

    <AuthenticatedLayout>
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="container py-6">
                <BaseBreadcrumb
                    :items="[
                        {
                            label: 'Groups',
                            href: route('group.index'),
                        },
                        {
                            label: 'Edit Group',
                        },
                    ]"
                />
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <UpdateGroupForm :group="group" :users="users" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
