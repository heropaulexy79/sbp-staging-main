<script lang="ts" setup>
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/Components/ui/alert-dialog";
import { Button } from "@/Components/ui/button";
import { errorBagToString } from "@/lib/errors";
import { OrganisationUser } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { XIcon } from "lucide-vue-next";
import { toast } from "vue-sonner";

const props = defineProps<{
    employee: OrganisationUser;
}>();

const form = useForm({});

function deleteEmployee() {
    form.delete(
        route("organisation.employee.delete", {
            organisation: props.employee.organisation_id,
            employee: props.employee.user_id,
        }),
        {
            preserveScroll: true,
            onError(errors) {
                toast.error("Delete employee", {
                    description: errorBagToString(errors),
                });
                form.reset();
            },
        },
    );
}
</script>

<template>
    <AlertDialog>
        <AlertDialogTrigger as-child>
            <Button variant="outline" size="icon">
                <XIcon class="size-4" />
            </Button>
        </AlertDialogTrigger>
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete
                    this users account and remove your data from our servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="deleteEmployee">
                    Continue
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
