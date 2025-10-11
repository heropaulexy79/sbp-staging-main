<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button, buttonVariants } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthLayout from "./Partials/AuthLayout.vue";

const page = usePage();

// console.log(page.props.query);

const props = defineProps<{
    prefilled?: {
        email?: string;
    };
}>();

const form = useForm({
    name: "",
    email:
        (props?.prefilled?.email?.trim().length ?? 0) > 0
            ? props.prefilled?.email?.toLowerCase()
            : page.props.query.email?.toLowerCase() ?? "",
    password: "",
    password_confirmation: "",
    invitation_token: page.props.query["tk"] ?? "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => {
            form.reset("password", "password_confirmation");
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Register" />

        <div class="mb-4 text-center">
            <h2 class="text-2xl font-semibold tracking-tight">
                Create account
            </h2>
            <p class="text-sm text-muted-foreground">
                Enter your your details below to get started
            </p>
        </div>

        <form @submit.prevent="submit">
            <input
                id="invitation_token"
                v-model="form.invitation_token"
                hidden
                disabled
            />

            <div>
                <Label for="name"> Name </Label>

                <Input
                    id="name"
                    type="text"
                    class="mt-1"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <Label for="email"> Email </Label>

                <Input
                    id="email"
                    type="email"
                    class="mt-1"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    :disabled="
                        (props?.prefilled?.email?.trim?.()?.length ?? 0) > 1 &&
                        (form.invitation_token?.trim().length ?? 0) > 1
                    "
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <Label for="password"> Password </Label>

                <Input
                    id="password"
                    type="password"
                    class="mt-1"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <Label for="password_confirmation"> Confirm Password </Label>

                <Input
                    id="password_confirmation"
                    type="password"
                    class="mt-1"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-8">
                <Button
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </Button>
            </div>

            <div class="mt-4 text-center">
                <Link
                    :href="route('login')"
                    :class="
                        cn(buttonVariants({ variant: 'link', class: 'px-0' }))
                    "
                >
                    Already have an account?
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
