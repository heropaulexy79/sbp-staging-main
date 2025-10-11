<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Course } from "@/types";
import CreateLessonForm from "./Partials/CreateLessonForm.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

defineProps<{ course: Course }>();
</script>

<template>
    <Head :title="course.title + ' Lesson'" />

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
                            { label: 'Create course' },
                        ]"
                    />
                </div>
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <div>
                        <!-- Form -->
                        <CreateLessonForm :course="course" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
