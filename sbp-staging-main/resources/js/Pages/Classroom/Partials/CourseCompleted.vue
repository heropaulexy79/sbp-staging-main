<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Progress } from "@/Components/ui/progress";
import { Course, Lesson } from "@/types";
import { AwardIcon } from "lucide-vue-next";
import { WithUserLesson } from "./types";

defineProps<{
    course: Course;
    lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
    progress: number;
    completed_lessons: number;
    total_score: number;
}>();
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <Card
            class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800"
        >
            <div
                class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-center"
            >
                <AwardIcon class="mx-auto mb-4 h-16 w-16 text-white" />
                <h1 class="mb-2 text-3xl font-bold text-white">
                    Congratulations!
                </h1>
                <p class="text-xl text-white">You've completed the course!</p>
            </div>
            <CardContent class="p-6">
                <div class="mb-6">
                    <h2 class="mb-2 text-2xl font-semibold dark:text-white">
                        Course: {{ course.title }}
                    </h2>
                    <Progress :model-value="progress" class="mb-2 h-2" />
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        {{ progress }}% Complete
                    </p>
                </div>
                <div class="mb-6 grid gap-4 md:grid-cols-2">
                    <div class="text-center">
                        <p class="text-4xl font-bold text-primary">
                            {{ completed_lessons }}
                        </p>
                        <p class="text-muted-foreground">Lessons Completed</p>
                    </div>
                    <div class="text-center">
                        <p class="text-4xl font-bold text-purple-600">
                            {{ total_score }}
                        </p>
                        <p class="text-gray-600 dark:text-gray-300">
                            Points Won
                        </p>
                    </div>
                </div>
                <div
                    class="mb-6 border-t border-gray-200 pt-6 dark:border-gray-700"
                >
                    <h3 class="mb-4 text-lg font-semibold dark:text-white">
                        Lessons covered:
                    </h3>
                    <ul
                        class="list-inside list-disc text-gray-600 dark:text-gray-300"
                    >
                        <li v-for="lesson in lessons" :key="lesson.id">
                            {{ lesson.title }}
                        </li>
                    </ul>
                </div>
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <Button class="w-full sm:w-auto"> Go to dashboard </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
