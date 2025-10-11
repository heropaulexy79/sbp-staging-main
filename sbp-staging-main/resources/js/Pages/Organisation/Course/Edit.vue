<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course } from "@/types";
import ManageCourseLayout from "./Partials/ManageCourseLayout.vue";
import UpdateCourseForm from "./Partials/UpdateCourseForm.vue";

defineProps<{ course: Course }>();
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <ManageCourseLayout :course="course">
            <div class="py-4">
                <div class="container">
                    <div
                        class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8"
                    >
                        <UpdateCourseForm :course="course" />
                    </div>
                </div>
            </div>
        </ManageCourseLayout>
    </AuthenticatedLayout>
</template>
