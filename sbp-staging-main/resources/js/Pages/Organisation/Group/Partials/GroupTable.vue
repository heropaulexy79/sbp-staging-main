<script setup lang="ts">
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import { Group } from "@/types";
import { groupColumns } from "./group-column";

defineProps<{
    groups: Group[];
}>();
</script>

<template>
    <section>
        <BaseDataTable
            :columns="groupColumns"
            :data="groups"
            :meta="{
                user: $page.props.auth.user,
            }"
        />
    </section>
</template>
