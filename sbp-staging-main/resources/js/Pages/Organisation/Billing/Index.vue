<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import UpdatePaymentMethod from "./Partials/UpdatePaymentMethod.vue";
import {
    BillingHistory as TBillingHistory,
    PaymentMethod,
    Subscription,
} from "@/types";
import BillingHistory from "./Partials/BillingHistory.vue";
import ManageSubscription from "./Partials/ManageSubscription.vue";

const props = defineProps<{
    payment_method: PaymentMethod | null;
    subscription: Subscription | null;
    history: TBillingHistory[];
}>();

// console.log(props);
</script>

<template>
    <Head title="Organisation Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Billing
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="grid gap-6 md:grid-cols-2">
                    <div
                        class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                    >
                        <UpdatePaymentMethod :payment_method="payment_method" />
                    </div>

                    <div
                        class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                    >
                        <ManageSubscription
                            :subscription="subscription"
                            :payment_method="payment_method"
                        />
                    </div>
                </div>

                <div
                    class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                >
                    <BillingHistory :billing_history="history" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
