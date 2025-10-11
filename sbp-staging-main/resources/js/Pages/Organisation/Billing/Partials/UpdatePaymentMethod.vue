<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { errorBagToString } from "@/lib/errors";
import { PaymentMethod } from "@/types";
import { useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue-sonner";

const page = usePage();

const props = defineProps<{
    payment_method: PaymentMethod | null;
}>();

const payForm = useForm({
    email: page.props.auth.user.email,
    amount: 100 * 100,
    currency: "NGN",
    channels: ["card"],
    // callback_url: `${window.location.origin ?? "https://int-sbp.test"}/payments/paystack/callback`,
    metadata: {
        type: "ADD_PAYMENT_METHOD",
        organisation_id: page.props.auth.user.organisation_id,
    },
});

function addMethod() {
    payForm.post(route("paystack.pay"), {
        onSuccess() {
            console.log("success");
        },
        onError(errors) {
            console.log(errors);
            toast.error("Add payment method", {
                description: errorBagToString(errors),
            });
        },
    });
}
</script>

<template>
    <header>
        <h2 class="text-lg font-medium">Payment methods</h2>

        <p class="mt-1 text-sm text-muted-foreground">
            Manage your payment methods to pay your subscription securely.
        </p>
    </header>

    <div className="flex flex-col gap-2 mt-2" v-if="payment_method">
        <div className="grid grid-cols-2 items-center text-sm">
            <div>Debit card ({{ payment_method.card_type }})</div>
            <div className="text-right">
                **** **** **** {{ payment_method.last_four }}
            </div>
        </div>
        <div className="grid grid-cols-2 items-center text-sm">
            <div>Expires</div>
            <div className="text-right">
                {{ payment_method.exp_month }} / {{ payment_method.exp_year }}
            </div>
        </div>

        <form class="mt-2" @submit.prevent="addMethod">
            <Button
                class="mt-3"
                :class="{ 'opacity-25': payForm.processing }"
                :disabled="payForm.processing"
            >
                Update payment method
            </Button>
        </form>
    </div>
    <form v-else class="mt-2" @submit.prevent="addMethod">
        <div class="text-sm text-muted-foreground">
            No payment methods! Setup one bellow
        </div>
        <Button
            class="mt-3"
            :class="{ 'opacity-25': payForm.processing }"
            :disabled="payForm.processing"
        >
            Add payment method
        </Button>
    </form>
</template>
