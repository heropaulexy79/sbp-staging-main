<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Group } from "@/types";
import { Link, router, useForm } from "@inertiajs/vue3";
import { MoreHorizontal, Edit, Trash2 } from "lucide-vue-next";

defineProps<{
    group: Group;
}>();

const form = useForm({});

function deleteGroup(group: Group) {
    if (confirm("Are you sure you want to delete this group?")) {
        form.delete(route("group.destroy", { group: group.id }));
    }
}
</script>

<template>
    <div class="relative">
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 p-0">
                    <span class="sr-only">Open menu</span>
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem as-child>
                    <Link
                        :href="route('group.edit', { group: group.id })"
                        class="flex items-center"
                    >
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem
                    @click="deleteGroup(group)"
                    class="text-red-600"
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
