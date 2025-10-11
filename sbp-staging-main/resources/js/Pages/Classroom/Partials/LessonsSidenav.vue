<script lang="ts" setup>
import { Course, Lesson } from "@/types";
import { WithUserLesson } from "./types";
import { Link } from "@inertiajs/vue3";
import { BookOpenText, Check, Star } from "lucide-vue-next";
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/Components/ui/sidebar";

defineProps<{
    course: Course;
    lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
}>();
</script>

<template>
    <!-- <div>
        <div class="px-4 py-6">
            <h2 class="text-sm font-bold">{{ course.title }}</h2>
        </div>
        <nav class="mb-6 px-0 py-0">
            <ul class="space-y-2">
                <li v-for="lesson in lessons">
                    <Link
                        :href="
                            route('classroom.lesson.show', {
                                course: course.slug,
                                lesson: lesson.slug,
                            })
                        "
                        :data-active="
                            route().current('classroom.lesson.show', {
                                course: course.slug,
                                lesson: lesson.slug,
                            })
                        "
                        class="group flex w-full items-center gap-2 border-l-[6px] border-l-transparent px-3 py-1 data-[active='true']:border-l-primary data-[active='true']:bg-accent data-[active='true']:text-accent-foreground"
                    >
                        <span
                            class="flex size-7 items-center justify-center rounded-full leading-none data-[completed='true']:bg-primary data-[completed='true']:text-primary-foreground group-data-[active='true']:bg-primary group-data-[active='true']:text-primary-foreground"
                            :data-completed="lesson.completed"
                        >
                            <Check v-if="lesson.completed" class="size-4" />
                            <template v-else>
                                <Star
                                    v-if="lesson.type === 'QUIZ'"
                                    class="size-4"
                                />
                                <BookOpenText v-else class="size-4" />
                            </template>
                        </span>
                        <span class="truncate">{{ lesson.title }}</span>
                    </Link>
                </li>
            </ul>
        </nav>
    </div> -->

    <SidebarGroup>
        <SidebarGroupLabel>Lessons</SidebarGroupLabel>
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="lesson in lessons" :key="lesson.id">
                    <SidebarMenuButton
                        :data-active="
                            route().current('classroom.lesson.show', {
                                course: course.slug,
                                lesson: lesson.slug,
                            })
                        "
                        class="group flex w-full items-center gap-2"
                        as-child
                    >
                        <Link
                            :href="
                                route('classroom.lesson.show', {
                                    course: course.slug,
                                    lesson: lesson.slug,
                                })
                            "
                            class="group flex w-full items-center gap-2"
                        >
                            <span
                                class="flex size-6 items-center justify-center rounded-full leading-none data-[completed='true']:text-primary group-data-[active='true']:bg-primary group-data-[active='true']:text-primary-foreground [&_svg]:size-4"
                                :data-completed="lesson.completed"
                            >
                                <Check v-if="lesson.completed" />
                                <template v-else>
                                    <Star v-if="lesson.type === 'QUIZ'" />
                                    <BookOpenText v-else />
                                </template>
                            </span>
                            <span class="truncate">{{ lesson.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
