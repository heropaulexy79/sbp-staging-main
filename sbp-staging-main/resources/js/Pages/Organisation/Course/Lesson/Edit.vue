<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course, Lesson } from "@/types";
import UpdateLessonForm from "./Partials/UpdateLessonForm.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

defineProps<{ course: Course; lesson: Lesson }>();
</script>

<template>
    <Head :title="lesson.title" />

    <AuthenticatedLayout>
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="container flex items-center justify-between py-6">
                <div>
                    <BaseBreadcrumb
                        :items="[
                            {
                                href: route('course.index'),
                                label: 'Courses',
                            },
                            {
                                href: route('course.show', {
                                    course: course.id,
                                }),
                                label: course.title,
                            },
                            { label: lesson.title },
                        ]"
                    />
                </div>
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <!-- Form -->
                <UpdateLessonForm :lesson="lesson" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
