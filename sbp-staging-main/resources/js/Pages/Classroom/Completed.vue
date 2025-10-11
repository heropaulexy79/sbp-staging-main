<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head } from "@inertiajs/vue3";
import LessonsSidenav from "./Partials/LessonsSidenav.vue";
import { WithUserLesson } from "./Partials/types";
import CourseCompleted from "./Partials/CourseCompleted.vue";

const props = defineProps<{
    course: Course;
    lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
    progress: number;
    completed_lessons: number;
    total_score: number;
}>();
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <div class="container">
            <div
                class="relative grid min-h-[calc(100svh-65px)] bg-background md:grid-cols-[225px_1fr]"
            >
                <LessonsSidenav
                    class="fixed top-0 h-[100svh] self-start overflow-auto border-r bg-background md:sticky"
                    :course="course"
                    :lessons="lessons"
                />
                <div class="bg-white">
                    <div class="px-12 py-12">
                        <CourseCompleted
                            :course="course"
                            :lessons="lessons"
                            :progress="progress"
                            :completed_lessons="completed_lessons"
                            :total_score="total_score"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
