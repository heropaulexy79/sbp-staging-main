<script setup lang="ts">
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import {
    Organisation,
    OrganisationInvite,
    OrganisationUser,
    User,
} from "@/types";
import { ref } from "vue";
import { employeeColumns, employeeInviteColumns } from "./employee-column";
import InviteToOrganisationForm from "./InviteToOrganisationForm.vue";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import DialogHeader from "@/Components/ui/dialog/DialogHeader.vue";
import { VisuallyHidden } from "radix-vue";

defineProps<{
    organisation: Organisation;
    employees: OrganisationUser[];
    invites: OrganisationInvite[];
}>();
</script>

<template>
    <section class="space-y-6">
        <header class="flex items-start justify-between gap-6">
            <div>
                <h2 class="text-lg font-medium">Team Settings</h2>

                <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Once your organisation is setup, you can invite other members to
                    join you!
                </p> -->
            </div>

            <Dialog>
                <DialogTrigger>
                    <Button>Add Student</Button>
                </DialogTrigger>
                <DialogContent>
                    <VisuallyHidden>
                        <DialogHeader
                            aria-hidden="true"
                            hidden
                            class="invisible h-0 w-0"
                        >
                            <DialogTitle>Invite to organisation</DialogTitle>
                            <DialogDescription>
                                Send an invite to join your organisation
                            </DialogDescription>
                        </DialogHeader>
                    </VisuallyHidden>

                    <InviteToOrganisationForm :organisation="organisation" />

                    <!-- <DialogFooter>
        Save changes
      </DialogFooter> -->
                </DialogContent>
            </Dialog>
        </header>

        <BaseDataTable :columns="employeeColumns" :data="employees" />

        <div class="pt-4">
            <h3 class="mb-4 font-medium">Invitations</h3>
            <BaseDataTable :columns="employeeInviteColumns" :data="invites" />
        </div>
    </section>
</template>
