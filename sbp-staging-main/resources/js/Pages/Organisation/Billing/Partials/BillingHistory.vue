<script lang="ts" setup>
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import { billingHistoryColumns } from "./billing-history-column";
import { BillingHistory } from "@/types";

const props = defineProps<{
    billing_history: BillingHistory[];
}>();
</script>

<template>
    <header>
        <h2 class="text-lg font-medium">Billing history</h2>

        <p class="mt-1 text-sm text-muted-foreground">
            View your past payments.
        </p>
    </header>

    <div className="mt-6">
        <!-- @vue-ignore -->
        <BaseDataTable
            :columns="billingHistoryColumns"
            :data="billing_history"
        />
    </div>
</template>
