<script lang="ts" setup>
import { Badge } from "@/Components/ui/badge";
import { Button, buttonVariants } from "@/Components/ui/button";
import { errorBagToString } from "@/lib/errors";
import { PaymentMethod, Subscription } from "@/types";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";

const page = usePage();

const props = defineProps<{
    subscription: Subscription | null;
    payment_method: PaymentMethod | null;
}>();

const payForm = useForm({});
const cancelSubForm = useForm({});

function upgradeToEntreprise() {
    // payForm.post(route("paystack.pay"), {
    //     onSuccess() {
    //         console.log("success");
    //     },
    //     onError(errors) {
    //         console.log(errors);
    //         toast.error("Add payment method", {
    //             description: errorBagToString(errors),
    //         });
    //     },
    // });
}

function cancelSubscription() {
    cancelSubForm.delete(route("subscriptions.destroy"), {
        onError(errors) {
            console.log(errors);
            toast.error("Cancel Subscription", {
                description: errorBagToString(errors),
            });
        },
    });
}
</script>

<template>
    <header>
        <h2 class="text-lg font-medium">Subscription</h2>

        <p class="mt-1 text-sm text-muted-foreground">
            Manage your subscription and billing.
        </p>
    </header>

    <div className="space-y-4 mt-6">
        <div v-if="subscription">
            <p className="font-medium">
                Current Plan:
                <Badge class="text-sm uppercase">{{ subscription.plan }}</Badge>
            </p>
            <p className="text-sm text-gray-500">
                {{
                    new Intl.NumberFormat("en-NG", {
                        currency: subscription.currency,
                        style: "currency",
                        currencyDisplay: "narrowSymbol",
                        maximumFractionDigits: 0,
                    }).format(subscription.amount / 100)
                }}
                /user /month, billed monthly
            </p>
        </div>

        <form
            v-if="subscription"
            class="mt-2"
            @submit.prevent="cancelSubscription"
        >
            <Button
                class="mt-3"
                variant="outline"
                :class="{ 'opacity-25': cancelSubForm.processing }"
                :disabled="cancelSubForm.processing"
            >
                Cancel subscription
            </Button>
        </form>
        <Link
            v-else="subscription"
            :href="route('subscriptions.show')"
            class="mt-2"
            :class="buttonVariants({})"
        >
            Subscribe now
        </Link>
        <!-- <form
            v-if="subscription?.plan === 'starter'"
            class="mt-2"
            @submit.prevent="upgradeToEntreprise"
        >
            <div class="text-sm text-muted-foreground">
                Upgrage to enterprise
            </div>
            <Button
                class="mt-3"
                :class="{ 'opacity-25': payForm.processing }"
                :disabled="payForm.processing"
            >
                Upgrade
            </Button>
        </form> -->
    </div>
</template>
