<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import GroupTable from "./Partials/GroupTable.vue";
import { Group, User } from "@/types";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { VisuallyHidden } from "radix-vue";
import { Button } from "@/Components/ui/button";
import CreateGroupForm from "./Partials/CreateGroupForm.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

defineProps<{ groups: Group[]; users: User[] }>();
</script>

<template>
    <Head title="Groups" />

    <AuthenticatedLayout>
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="container flex items-center justify-between py-6">
                <div>
                    <BaseBreadcrumb
                        :items="[
                            {
                                label: 'Groups',
                            },
                        ]"
                    />
                </div>
                <Dialog>
                    <DialogTrigger
                        v-if="
                            $page.props.auth.user.organisation_id &&
                            $page.props.auth.user.role === 'ADMIN'
                        "
                    >
                        <Button size="sm">Create Group</Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-[768px]">
                        <VisuallyHidden>
                            <DialogHeader
                                aria-hidden="true"
                                hidden
                                class="invisible h-0 w-0"
                            >
                                <DialogTitle> Create a group </DialogTitle>
                                <DialogDescription>
                                    <!-- Create a group -->
                                </DialogDescription>
                            </DialogHeader>
                        </VisuallyHidden>
                        <CreateGroupForm
                            :organisation_id="
                                $page.props.auth.user.organisation_id!
                            "
                            :users="users"
                        />
                    </DialogContent>
                </Dialog>
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <GroupTable
                        :groups="groups"
                        v-if="
                            $page.props.auth.user.organisation_id &&
                            $page.props.auth.user.role === 'ADMIN'
                        "
                    />

                    <div
                        v-if="groups.length === 0"
                        class="text-center py-8"
                    >
                        <p class="text-gray-500">No groups created yet</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
