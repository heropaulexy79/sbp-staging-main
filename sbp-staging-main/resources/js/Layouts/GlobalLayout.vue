<script lang="ts" setup>
import { Toaster } from "@/Components/ui/sonner";
import { router, usePage } from "@inertiajs/vue3";
import { watch } from "vue";
import { toast } from "vue-sonner";

const page = usePage();

type ToastActions = Parameters<typeof toast>[1];

watch(
    () => page.props.flash["global:message"],
    (flash) => {
        if (!flash) return;

        // TODO: FIX RUN 2WICE
        // console.log("runn");
        const message = flash.message;
        const action = flash.action;
        const type = flash.status;

        const toastOptions: ToastActions = {
            ...(action && {
                action: {
                    label: action?.["cta:text"],
                    onClick(event) {
                        action?.["cta:link"] &&
                            router.visit(action?.["cta:link"]);
                    },
                },
            }),
        };

        switch (type) {
            case "success":
                toast.success(message, { ...toastOptions });
                break;

            case "error":
                toast.error(message, { ...toastOptions });
                break;

            default:
                toast(message, { ...toastOptions });
                break;
        }

        // toast("Event has been created", {
        //     // description: 'Sunday, December 03, 2023 at 9:00 AM',
        //     action: {
        //         label: "Undo",
        //         onClick: () => console.log("Undo"),
        //     },
        // });
    },
);
</script>

<template>
    <slot />
    <Toaster />
</template>
