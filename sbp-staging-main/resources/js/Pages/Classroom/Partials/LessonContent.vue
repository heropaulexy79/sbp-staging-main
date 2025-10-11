<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import { Course, Lesson } from "@/types";
import { useForm } from "@inertiajs/vue3";
import { WithUserLesson } from "./types";
import QuizRenderer from "./QuizRenderer.vue";

const props = defineProps<{
    course: Course;
    lesson: WithUserLesson<Lesson>;
    nextLessonId: Lesson["slug"] | null;
}>();

const completionForm = useForm({});

function markAsComplete() {
    completionForm.patch(
        route("classroom.lesson.markComplete", {
            course: props.course.slug,
            lesson: props.lesson.slug,
            next: props.nextLessonId,
        }),
        {
            onSuccess() {},
            onError() {},
        },
    );
}
</script>

<template>
    <div>
        <div
            v-if="lesson.type === 'DEFAULT'"
            class="prose px-12 py-12 lg:prose-lg"
        >
            <article v-html="lesson.content" />

            <form
                @submit.prevent="markAsComplete"
                class="mt-10 flex items-center justify-center"
            >
                <Button type="submit" size="sm" v-if="!lesson.completed">
                    Mark as complete
                </Button>
            </form>
        </div>
        <div v-else-if="lesson.type === 'QUIZ'">
            <QuizRenderer
                :course="course"
                :lesson="lesson"
                :next-lesson-id="nextLessonId"
            />
        </div>
    </div>
</template>
