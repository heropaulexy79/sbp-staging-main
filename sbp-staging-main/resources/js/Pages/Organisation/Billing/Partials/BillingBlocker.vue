<script lang="ts" setup>
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from "@/Components/ui/alert-dialog";
// import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";

defineProps<{ canUpdateMethod: boolean }>();
const vmodel = defineModel("open", { default: false });
</script>

<template>
    <AlertDialog v-model:open="vmodel">
        <!-- <AlertDialogTrigger asChild>
            
        </AlertDialogTrigger> -->
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Subscription Expired</AlertDialogTitle>
                <AlertDialogDescription>
                    Your subscription has expired. To continue using our
                    services,{{ " " }}
                    <span v-if="!canUpdateMethod">
                        contact an admin to update billing information to renewy
                        your subscription
                    </span>
                    <span v-else>
                        please update your billing information to renew your
                        subscription.
                    </span>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <!-- <AlertDialogCancel>Close</AlertDialogCancel> -->
                <AlertDialogAction v-if="canUpdateMethod" as-child>
                    <Link :href="route('subscriptions.show')">
                        Renew Subscription
                    </Link>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
