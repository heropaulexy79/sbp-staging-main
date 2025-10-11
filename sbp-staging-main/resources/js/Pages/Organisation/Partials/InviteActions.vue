<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { errorBagToString } from "@/lib/errors";
import { Organisation, OrganisationInvite } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { X } from "lucide-vue-next";
import { toast } from "vue-sonner";

const props = defineProps<{
    organisation_id: Organisation["id"];
    id: OrganisationInvite["id"];
}>();

const form = useForm({});

function uninviteUser() {
    form.delete(
        route("organisation.uninvite", {
            organisation: props.organisation_id,
            invitation: props.id,
        }),
        {
            preserveScroll: true,
            onError(errors) {
                toast.error("Cancel invite", {
                    description: errorBagToString(errors),
                });
            },
        },
    );
}
</script>

<template>
    <Button type="button" variant="ghost" size="icon" @click="uninviteUser">
        <X :size="16" />
    </Button>
</template>
