<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Paginated } from "@/types";
import { Head, Link } from "@inertiajs/vue3";
import PublicCourseCard from "./Partials/PublicCourseCard.vue";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import LaravelPagination from "@/Components/ui/LaravelPagination.vue";
import { Button } from "@/Components/ui/button";
import { SearchIcon } from "lucide-vue-next";

const props = defineProps<{
    courses: Paginated<Course>;
}>();
</script>

<template>
    <Head title="Courses" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="container">
                <div class="mb-6">
                    <form :action="route('public.course.index')">
                        <div class="relative max-w-sm">
                            <div>
                                <Label for="search" class="sr-only"
                                    >Search</Label
                                >
                                <Input
                                    id="search"
                                    name="search"
                                    :default-value="
                                        $page.props.query.search ?? ''
                                    "
                                    placeholder="Search for a course"
                                    class="mt-2 h-14 pr-14"
                                />
                                <!-- <InputError class="mt-2" :message="form.errors.title" /> -->
                            </div>
                            <Button
                                size="icon"
                                class="absolute right-2 top-1/2 -translate-y-1/2"
                            >
                                <SearchIcon class="size-4" />
                                <span class="sr-only">Search</span>
                            </Button>
                        </div>
                    </form>
                </div>
                <ul
                    class="grid grid-cols-[repeat(auto-fill,minmax(min(100%,300px),1fr))] gap-4"
                >
                    <li v-for="course in courses.data">
                        <Link
                            :href="
                                route('public.course.show', {
                                    course: course.slug,
                                })
                            "
                            class="block h-full ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
                        >
                            <PublicCourseCard :course="course" class="h-full" />
                        </Link>
                    </li>
                    <li v-if="courses.data.length === 0" class="text-center">
                        No courses at the moment
                    </li>
                </ul>

                <LaravelPagination
                    v-if="courses.total > courses.data.length"
                    :items="courses"
                    class="flex justify-center py-6"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
