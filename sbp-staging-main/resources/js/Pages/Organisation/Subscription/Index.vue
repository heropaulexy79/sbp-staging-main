<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { PaymentMethod, SubscriptionPlan } from "@/types";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import PricingCard from "./Partials/PricingCard.vue";
import { toast } from "vue-sonner";
import { errorBagToString } from "@/lib/errors";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { AlertTriangle } from "lucide-vue-next";
import { Button } from "@/Components/ui/button";

const page = usePage();
const props = defineProps<{
    plans: {
        [k: string]: SubscriptionPlan;
    };
    payment_method: PaymentMethod | null;
    is_admin: false;
    // subscription: Subscription | null;
    // history: TBillingHistory[];
}>();

const payForm = useForm({
    email: page.props.auth.user.email,
    amount: 50 * 100,
    currency: "NGN",
    channels: ["card"],
    callback_url: `${window.location.origin}/payment/paystack/callback`,
    metadata: {
        type: "ADD_PAYMENT_METHOD",
        organisation_id: page.props.auth.user.organisation_id,
        selected_plan: "",
    },
});

const createSubsForm = useForm({
    plan: "",
});

function addMethod() {
    payForm.post(route("paystack.pay"), {
        onError(errors) {
            console.log(errors);
            toast.error("Add payment method", {
                description: errorBagToString(errors),
            });
        },
    });
}

function createSub() {
    createSubsForm.post(route("subscriptions.store"), {
        onError(errors) {
            console.log(errors);
            toast.error("Add payment method", {
                description: errorBagToString(errors),
            });
        },
    });
}

const plansArray = Object.entries(props.plans).map(([key, v]) => v);

function onSelect(plan: SubscriptionPlan) {
    if (plan.price) {
        if (!props.payment_method) {
            payForm.metadata.selected_plan = plan.id;
            addMethod();
        } else {
            createSubsForm.plan = plan.id;
            createSub();
        }
    } else {
    }
}
</script>

<template>
    <Head title="Choose Subscription" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Choose Subscription
            </h2>
        </template>

        <div class="py-0">
            <!-- <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8"> -->
            <div class="" v-if="is_admin">
                <div
                    class="relative isolate bg-white px-6 py-4 sm:py-4 lg:px-8"
                >
                    <div
                        class="absolute inset-x-0 -top-3 -z-10 transform-gpu overflow-hidden px-36 blur-3xl"
                        aria-hidden="true"
                    >
                        <div
                            class="mx-auto aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-[#ff80b5] to-primary opacity-30"
                            style="
                                clip-path: polygon(
                                    74.1% 44.1%,
                                    100% 61.6%,
                                    97.5% 26.9%,
                                    85.5% 0.1%,
                                    80.7% 2%,
                                    72.5% 32.5%,
                                    60.2% 62.4%,
                                    52.4% 68.1%,
                                    47.5% 58.3%,
                                    45.2% 34.5%,
                                    27.5% 76.7%,
                                    0.1% 64.9%,
                                    17.9% 100%,
                                    27.6% 76.8%,
                                    76.1% 97.7%,
                                    74.1% 44.1%
                                );
                            "
                        ></div>
                    </div>
                    <div class="mx-auto max-w-4xl text-center">
                        <h2 class="text-base/7 font-semibold text-primary">
                            Pricing
                        </h2>
                        <p
                            class="mt-2 text-balance text-5xl font-semibold tracking-tight text-gray-900 sm:text-6xl"
                        >
                            Choose the right plan for you
                        </p>
                    </div>
                    <!-- <p
                        class="mx-auto mt-6 max-w-2xl text-pretty text-center text-lg font-medium text-muted-foreground sm:text-xl/8"
                    >
                        Choose an affordable plan that's packed with the best
                        features for boosting .
                    </p> -->
                    <div
                        class="mx-auto mt-16 grid max-w-lg grid-cols-1 items-center gap-6 sm:mt-20 lg:max-w-4xl lg:grid-cols-2 lg:gap-6"
                    >
                        <PricingCard
                            v-for="plan in plansArray"
                            :key="plan.id"
                            :plan="plan"
                            :variant="
                                plan.id === 'enterprise' ? 'alt' : 'default'
                            "
                            @on-select="onSelect"
                        />
                    </div>
                </div>
            </div>

            <div v-else>
                <div
                    class="flex min-h-screen flex-col items-center justify-center bg-gray-100 p-4"
                >
                    <Card class="w-full max-w-md">
                        <CardHeader class="text-center">
                            <div
                                class="mx-auto mb-4 w-fit rounded-full bg-yellow-100 p-3"
                            >
                                <AlertTriangle
                                    class="h-8 w-8 text-yellow-600"
                                />
                            </div>
                            <CardTitle class="text-2xl font-bold"
                                >Subscription Expired</CardTitle
                            >
                            <CardDescription
                                >Your access has been temporarily
                                suspended</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="text-center">
                            <p class="mb-4">
                                We're sorry, but your subscription has expired.
                                To regain access to your account and continue
                                using our services, please contact your
                                administrator to renew the subscription.
                            </p>
                            <p class="font-semibold">
                                What should you do next?
                            </p>
                            <ul class="mt-2 list-inside list-disc text-left">
                                <li>
                                    Contact your company's IT department or
                                    account administrator
                                </li>
                                <li>Request a subscription renewal</li>
                                <li>
                                    Once renewed, you can log back in to access
                                    your account
                                </li>
                            </ul>
                        </CardContent>
                        <CardFooter class="flex flex-col space-y-2">
                            <!-- <Button class="w-full" asChild>
            <Link href="mailto:admin@yourcompany.com">
              <Mail class="mr-2 h-4 w-4" /> Contact Admin
            </Link>
          </Button> -->
                            <Button variant="outline" class="w-full" asChild>
                                <Link href="/">Return to Homepage</Link>
                            </Button>
                        </CardFooter>
                    </Card>
                    <p class="mt-8 text-center text-sm text-gray-500">
                        If you believe this is an error, please contact our
                        <Link
                            href="mailto:info@imageandtime.com"
                            class="font-medium text-blue-600 hover:underline"
                        >
                            support team
                        </Link>
                        .
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
