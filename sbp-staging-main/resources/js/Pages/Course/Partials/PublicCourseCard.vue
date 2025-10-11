<script lang="ts" setup>
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";

defineProps<{
    course: Pick<
        Course,
        "id" | "title" | "slug" | "description" | "banner_image"
    >;
}>();
</script>

<template>
    <div class="rounded-lg bg-background p-4 shadow transition-all">
        <div class="">
            <div
                class="h-[200px] w-full rounded-md bg-gray-400 bg-cover"
                :style="{
                    backgroundImage: course.banner_image
                        ? `url(${course.banner_image})`
                        : 'unset',
                }"
            ></div>

            <h3 class="my-4 text-lg font-medium">{{ course.title }}</h3>
            <p
                class="line-clamp-3 text-sm text-muted-foreground"
                v-html="course.description"
            ></p>
        </div>
    </div>
</template>
