<script lang="ts" setup>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Button } from "@/Components/ui/button";
import GlobalLayout from "./GlobalLayout.vue";
import {
    Dialog,
    DialogTitle,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { VisuallyHidden } from "radix-vue";
import CreateCourseForm from "@/Pages/Organisation/Course/Partials/CreateCourseForm.vue";
import { getPublicProfileImage } from "@/lib/utils";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <GlobalLayout>
        <div>
            <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
                <nav
                    class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
                >
                    <!-- Primary Navigation Menu -->
                    <div class="container">
                        <div class="flex h-16 justify-between">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="flex shrink-0 items-center">
                                    <Link :href="route('dashboard')">
                                        <ApplicationLogo
                                            class="block h-6 max-h-9 w-auto fill-current text-primary dark:text-primary-foreground"
                                        />
                                    </Link>
                                </div>

                                <!-- Navigation Links -->
                                <div
                                    class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                >
                                    <NavLink
                                        :href="route('dashboard')"
                                        :active="
                                            route().current('dashboard') ||
                                            route().current('course.*') ||
                                            route().current('lesson.*') ||
                                            route().current('public.course.*')
                                        "
                                    >
                                        Dashboard
                                    </NavLink>
                                </div>
                            </div>

                            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                                <Dialog>
                                    <DialogTrigger
                                        v-if="
                                            $page.props.auth.user
                                                .account_type === 'TEACHER'
                                        "
                                    >
                                        <Button size="sm">New course</Button>
                                    </DialogTrigger>
                                    <DialogContent class="max-w-[768px]">
                                        <VisuallyHidden>
                                            <DialogHeader
                                                aria-hidden="true"
                                                hidden
                                                class="invisible h-0 w-0"
                                            >
                                                <DialogTitle>
                                                    Create a course
                                                </DialogTitle>
                                                <DialogDescription>
                                                    Create a course
                                                </DialogDescription>
                                            </DialogHeader>
                                        </VisuallyHidden>
                                        <CreateCourseForm />
                                    </DialogContent>
                                </Dialog>
                                <!-- Settings Dropdown -->
                                <div class="relative">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="-mr-4 hover:bg-transparent"
                                            >
                                                <Avatar>
                                                    <AvatarImage
                                                        :src="
                                                            getPublicProfileImage(
                                                                $page.props.auth
                                                                    .user.email,
                                                            )
                                                        "
                                                        :alt="
                                                            $page.props.auth
                                                                .user.name
                                                        "
                                                    />
                                                    <AvatarFallback>
                                                        {{
                                                            $page.props.auth
                                                                .user.name[0]
                                                        }}
                                                    </AvatarFallback>
                                                </Avatar>

                                                {{ $page.props.auth.user.name }}
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            class="w-48"
                                            align="end"
                                        >
                                            <DropdownMenuLabel>
                                                <!-- My Account -->
                                                <div
                                                    class="text-sm font-medium text-gray-800 dark:text-gray-200"
                                                >
                                                    {{
                                                        $page.props.auth.user
                                                            .name
                                                    }}
                                                </div>
                                                <div
                                                    class="truncate text-xs font-medium text-gray-500"
                                                >
                                                    {{
                                                        $page.props.auth.user
                                                            .email
                                                    }}
                                                </div>
                                            </DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="
                                                        route('profile.edit')
                                                    "
                                                >
                                                    Profile
                                                </Link>
                                            </DropdownMenuItem>

                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="route('logout')"
                                                    method="post"
                                                    as="button"
                                                    class="w-full"
                                                >
                                                    Logout
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>

                            <!-- Hamburger -->
                            <div class="-me-2 flex items-center sm:hidden">
                                <button
                                    @click="
                                        showingNavigationDropdown =
                                            !showingNavigationDropdown
                                    "
                                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{
                                                hidden: showingNavigationDropdown,
                                                'inline-flex':
                                                    !showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{
                                                hidden: !showingNavigationDropdown,
                                                'inline-flex':
                                                    showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Navigation Menu -->
                    <div
                        :class="{
                            block: showingNavigationDropdown,
                            hidden: !showingNavigationDropdown,
                        }"
                        class="sm:hidden"
                    >
                        <div class="space-y-1 pb-3 pt-2">
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="
                                    route().current('dashboard') ||
                                    route().current('course.*') ||
                                    route().current('lesson.*') ||
                                    route().current('public.course.*')
                                "
                            >
                                Dashboard
                            </ResponsiveNavLink>
                        </div>

                        <!-- Responsive Settings Options -->
                        <div
                            class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                        >
                            <div class="px-4">
                                <div
                                    class="text-base font-medium text-gray-800 dark:text-gray-200"
                                >
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink
                                    :href="route('profile.edit')"
                                >
                                    Profile
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header
                    class="bg-white shadow dark:bg-gray-800"
                    v-if="$slots.header"
                >
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </GlobalLayout>
</template>
