<script setup lang="ts">
import LaravelPagination from "@/Components/ui/LaravelPagination.vue";
import TeacherLayout from "@/Layouts/TeacherLayout.vue";
import { Course, Paginated } from "@/types";
import { Head, usePage } from "@inertiajs/vue3";
import CourseTable from "../Organisation/Course/Partials/CourseTable.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

const page = usePage();

const props = defineProps<{
    courses: Paginated<Course>;
}>();
// console.log(props);
</script>

<template>
    <Head title="Dashboard" />

    <TeacherLayout v-if="$page.props.auth.user.account_type === 'TEACHER'">
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="container flex items-center justify-between py-6">
                <div>
                    <BaseBreadcrumb
                        :items="[
                            {
                                label: 'Courses',
                            },
                        ]"
                    />
                </div>
            </div>
        </header>
        <div class="py-10">
            <div class="container">
                <CourseTable :courses="courses.data" />

                <LaravelPagination
                    v-if="courses.total > courses.data.length"
                    :items="courses"
                    class="flex justify-center py-6"
                />
            </div>
        </div>
    </TeacherLayout>
</template>
