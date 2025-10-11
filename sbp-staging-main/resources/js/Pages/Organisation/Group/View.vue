<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Group } from "@/types";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";

defineProps<{
    group: Group;
}>();
</script>

<template>
    <Head title="View Group" />

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
                            label: 'View Group',
                        },
                    ]"
                />
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ group.name }}</h1>
                                <p class="text-gray-600 mt-1">{{ group.users_count }} member{{ group.users_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <Link
                                :href="route('group.edit', { group: group.id })"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90"
                            >
                                Edit Group
                            </Link>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Members</h3>
                            <div v-if="group.users.length === 0" class="text-gray-500">
                                No members in this group
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="user in group.users"
                                    :key="user.id"
                                    class="flex items-center space-x-3 p-3 border rounded-lg"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
