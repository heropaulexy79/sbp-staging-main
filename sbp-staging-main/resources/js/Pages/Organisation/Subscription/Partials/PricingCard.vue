<script setup lang="ts">
import Button from "@/Components/ui/button/Button.vue";
import { cn } from "@/lib/utils";
import { SubscriptionPlan } from "@/types";

const props = withDefaults(
    defineProps<{
        plan: SubscriptionPlan;
        variant: "default" | "alt";
    }>(),
    {
        variant: "default",
    },
);
const emit = defineEmits<{
    onSelect: [plan: SubscriptionPlan]; // named tuple syntax
}>();

let cls = "bg-background/60 text-foreground";

if (props.variant === "alt") {
    cls = "bg-gray-900 text-white shadow-2xl";
}

const id = "tier-" + props.plan.id;
</script>

<template>
    <div
        :class="
            cn(
                'rounded-3xl rounded-t-3xl p-8 ring-1 ring-gray-900/10 sm:p-10 lg:rounded-bl-3xl',
                cls,
            )
        "
    >
        <h3 :id="id" class="text-base/7 font-semibold text-primary">
            {{ plan.name }}
        </h3>
        <p class="mt-4 flex items-baseline gap-x-2">
            <span class="text-5xl font-semibold tracking-tight">
                {{
                    plan.price
                        ? new Intl.NumberFormat("en-NG", {
                              currency: plan.currency,
                              style: "currency",
                              currencyDisplay: "narrowSymbol",
                              maximumFractionDigits: 0,
                          }).format(plan.price / 100)
                        : "Custom"
                }}
            </span>

            <span v-if="plan.price" class="text-base text-muted-foreground"
                >/ user / month</span
            >
        </p>
        <p class="mt-6 text-base/7">
            {{ plan.description }}
        </p>
        <ul role="list" class="mt-8 space-y-3 text-sm/6 sm:mt-10">
            <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex gap-x-3"
            >
                <svg
                    class="h-6 w-5 flex-none text-primary"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                    aria-hidden="true"
                    data-slot="icon"
                >
                    <path
                        fill-rule="evenodd"
                        d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                        clip-rule="evenodd"
                    />
                </svg>
                <div>
                    {{ feature }}
                </div>
            </li>
        </ul>
        <Button
            :aria-describedby="id"
            class="mt-8 w-full"
            @click="
                () => {
                    emit('onSelect', plan);
                }
            "
        >
            {{ plan.price ? "Get started" : "Contact sales" }}
        </Button>
    </div>
</template>
