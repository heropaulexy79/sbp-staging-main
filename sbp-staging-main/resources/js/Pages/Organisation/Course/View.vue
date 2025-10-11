<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Course, Lesson } from "@/types";
import ManageCourseLayout from "./Partials/ManageCourseLayout.vue";
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import { lessonColumns } from "./Lesson/Partials/lesson-column";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/Components/ui/button";
import LessonDataTable from "./Lesson/Partials/LessonDataTable.vue";
import QuizGeneratorModal from "./Partials/QuizGeneratorModal.vue";
import { BrainIcon } from "lucide-vue-next";
import { ref } from "vue";

const props = defineProps<{ course: Course; lessons: Lesson[] }>();

// Quiz generator modal state
const isQuizGeneratorOpen = ref(false);

function handleQuizGeneratorSuccess() {
    // Optionally refresh the page or show a success message
    console.log('Quiz generation completed successfully');
}
</script>

<template>
    <Head :title="course.title" />

    <AuthenticatedLayout>
        <ManageCourseLayout :course="course">
            <div class="py-4">
                <div class="container">
                    <div class="space-y-6">
                        <header class="flex items-start justify-between gap-6">
                            <div>
                                <h2 class="text-lg font-medium">Lessons</h2>

                                <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
Update your account's profile information and email address.
</p> -->
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    @click="isQuizGeneratorOpen = true"
                                    :class="cn(buttonVariants({ variant: 'outline' }))"
                                >
                                    <BrainIcon class="size-4 mr-2" />
                                    Generate Quiz
                                </button>
                                
                                <Link
                                    :href="
                                        route('lesson.create', {
                                            course: course.id,
                                        })
                                    "
                                    :class="cn(buttonVariants())"
                                >
                                    Create lesson
                                </Link>
                            </div>
                        </header>

                        <LessonDataTable
                            :columns="lessonColumns"
                            :data="lessons"
                        />
                    </div>
                </div>
            </div>
        </ManageCourseLayout>

        <!-- Quiz Generator Modal -->
        <QuizGeneratorModal
            :course="course"
            v-model:is-open="isQuizGeneratorOpen"
            @success="handleQuizGeneratorSuccess"
        />
    </AuthenticatedLayout>
</template>
