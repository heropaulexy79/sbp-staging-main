<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Course, Lesson } from "@/types";
import { Head, Link } from "@inertiajs/vue3";
import LessonContent from "./Partials/LessonContent.vue";
import LessonsSidenav from "./Partials/LessonsSidenav.vue";
import { computed } from "vue";
import { WithUserLesson } from "./Partials/types";
import {
    Sidebar,
    SidebarContent,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuItem,
    SidebarProvider,
    SidebarRail,
    SidebarTrigger,
} from "@/Components/ui/sidebar";
import { ChevronLeft, ChevronLeftIcon } from "lucide-vue-next";
import { buttonVariants } from "@/Components/ui/button";
import { Separator } from "@/Components/ui/separator";

const props = defineProps<{
    course: Course;
    lessons: WithUserLesson<Omit<Lesson, "content" | "content_json">>[];
    lesson: WithUserLesson<Lesson>;
}>();

const nextLesson = computed(() => {
    const idx = props.lessons.findIndex((r) => r.id === props.lesson.id);

    return props.lessons[idx + 1] ?? null;
});
</script>

<template>
    <Head :title="lesson.title" />
    <AuthenticatedLayout :is-fullscreen="true">
        <div class="relative">
            <!-- class="min-h-[calc(100svh-65px)]" -->
            <SidebarProvider>
                <!-- class="top-[65px] h-[calc(100svh-65px)]" -->
                <Sidebar off-canvas-class="h-full">
                    <SidebarHeader>
                        <SidebarMenu>
                            <div class="-ml-4 mb-3 flex gap-4">
                                <Link
                                    :href="route('dashboard')"
                                    :class="
                                        buttonVariants({
                                            // size: 'icon',
                                            size: 'sm',
                                            variant: 'ghost',
                                            class: 'group/back-btn shrink-0',
                                        })
                                    "
                                >
                                    <ChevronLeft
                                        class="transition-all group-hover/back-btn:-translate-x-2"
                                    />
                                    Go to dashboard
                                </Link>
                            </div>
                            <SidebarMenuItem>
                                {{ course.title }}
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarHeader>

                    <SidebarContent>
                        <LessonsSidenav :course="course" :lessons="lessons" />
                    </SidebarContent>

                    <SidebarRail />
                </Sidebar>

                <!-- class="min-h-[calc(100svh-65px)] peer-data-[variant=inset]:min-h-[calc(100svh-65px)]" -->
                <SidebarInset>
                    <header
                        class="bg-sidebar sticky top-0 flex h-16 shrink-0 items-center gap-2 border-b px-4"
                    >
                        <SidebarTrigger class="-ml-1" />
                        <Separator orientation="vertical" class="mr-2 h-4" />
                        {{ lesson.title }}
                    </header>

                    <div class="bg-white">
                        <LessonContent
                            :lesson="lesson"
                            :course="course"
                            :next-lesson-id="nextLesson?.slug ?? null"
                        />
                    </div>
                </SidebarInset>
            </SidebarProvider>
        </div>
    </AuthenticatedLayout>
</template>
