<script lang="ts" setup>
import {
    Select,
    SelectContent,
    // SelectGroup,
    SelectItem,
    // SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { errorBagToString } from "@/lib/errors";
import { Organisation, OrganisationUser, User } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";

const props = defineProps<{
    organisation_id: Organisation["id"];
    user_id: User["id"];
    role: OrganisationUser["role"];
}>();

const form = useForm({
    role: props.role!,
});

function updateRole(value: string) {
    form.transform(() => {
        return {
            role: value,
        };
    }).patch(
        route("organisation.updateEmployee", {
            organisation: props.organisation_id,
            employee: props.user_id,
        }),
        {
            preserveScroll: true,
            onError(errors) {
                toast.error("Updating employee", {
                    description: errorBagToString(errors),
                });
                form.reset();
            },
        },
    );
}
</script>

<template>
    <Select
        :default-value="form.role"
        :disabled="$page.props.auth.user.id === user_id"
        @update:model-value="updateRole"
    >
        <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Select a role" />
        </SelectTrigger>
        <SelectContent>
            <SelectItem value="ADMIN"> Administrator </SelectItem>
            <SelectItem value="STUDENT"> Student </SelectItem>
        </SelectContent>
    </Select>
</template>
