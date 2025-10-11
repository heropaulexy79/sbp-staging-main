<script lang="ts" setup>
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";
import { ArrowRight } from "lucide-vue-next";

withDefaults(
    defineProps<{
        course: Pick<Course, "id" | "title" | "slug" | "banner_image">;
        canViewLesson: boolean;
    }>(),
    {
        canViewLesson: true,
    },
);
</script>

<template>
    <div class="@container">
        <div class="rounded-lg bg-background p-2 shadow transition-all @md:p-4">
            <div class="flex flex-col gap-5 @md:items-center @lg:flex-row">
                <div
                    class="aspect-video w-full rounded-md bg-gray-400 bg-cover @lg:size-32"
                    :style="{
                        backgroundImage: course.banner_image
                            ? `url(${course.banner_image})`
                            : 'unset',
                    }"
                ></div>
                <div
                    class="flex flex-1 flex-col gap-6 @md:justify-between @lg:flex-row"
                >
                    <Link
                        :href="
                            canViewLesson
                                ? route('classroom.lesson.index', {
                                      course: course.slug,
                                  })
                                : route('public.course.show', {
                                      course: course.slug,
                                  })
                        "
                    >
                        <h3 class="text-lg font-medium">{{ course.title }}</h3>
                    </Link>

                    <div
                        class="mt-4 flex flex-col @xs:flex-row @md:mt-0 @md:max-w-52 @lg:flex-col"
                    >
                        <Link
                            :href="
                                canViewLesson
                                    ? route('classroom.lesson.index', {
                                          course: course.slug,
                                      })
                                    : route('public.course.show', {
                                          course: course.slug,
                                      })
                            "
                            class="group"
                            :class="cn(buttonVariants({ size: 'lg' }))"
                        >
                            <span>Go to course</span>
                            <ArrowRight
                                :size="16"
                                class="transition-all group-hover:translate-x-2"
                            />
                        </Link>
                        <Link
                            :href="
                                route('organisation.course.leaderboard', {
                                    course: course.slug,
                                })
                            "
                            :class="
                                cn(
                                    buttonVariants({
                                        size: 'lg',
                                        variant: 'link',
                                    }),
                                )
                            "
                        >
                            <span>View leaderboard</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
