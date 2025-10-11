<script lang="ts" setup>
import { Link } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/Components/ui/button";
</script>

<template>
    <div class="bg-primary px-4 py-3 text-center text-primary-foreground">
        <div class="container mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-5 w-5 text-primary-foreground"
                >
                    <path
                        d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"
                    ></path>
                    <line x1="12" x2="12" y1="8" y2="12"></line>
                    <line x1="12" x2="12.01" y1="16" y2="16"></line>
                </svg>
                <p class="text-sm font-medium">
                    To continue using this service, please add a payment method.
                </p>
            </div>
            <Link
                :class="cn(buttonVariants({ variant: 'secondary' }))"
                :href="route('organisation.billing.index')"
            >
                Update Billing
            </Link>
        </div>
    </div>
</template>
