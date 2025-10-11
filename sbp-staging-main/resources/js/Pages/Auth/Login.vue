<script setup lang="ts">
// import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Button, buttonVariants } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { cn } from "@/lib/utils";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthLayout from "./Partials/AuthLayout.vue";

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Log in" />

        <div class="mb-4 text-center">
            <h2 class="text-2xl font-semibold tracking-tight">Welcome back</h2>
            <p class="text-sm text-muted-foreground">
                Enter your your details below
            </p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <Label for="email"> Email </Label>

                <Input
                    id="email"
                    type="email"
                    class="mt-1"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <Label for="password"> Password </Label>

                <Input
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400"
                        >Remember me</span
                    >
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    :class="
                        cn(buttonVariants({ variant: 'link', class: 'px-0' }))
                    "
                >
                    Forgot your password?
                </Link>
            </div>
            <div class="mt-4 flex items-center justify-end">
                <Button
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign in
                </Button>
            </div>
            <div class="mt-4 text-center">
                Don't have an account
                <Link
                    :href="route('register')"
                    :class="
                        cn(
                            buttonVariants({
                                variant: 'link',
                                class: 'px-0 font-bold',
                            }),
                        )
                    "
                >
                    Sign Up?
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
