<script lang="ts" setup>
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";
import { buttonVariants } from "@/Components/ui/button";
import { cn } from "@/lib/utils";
import { Course } from "@/types";
import { Link } from "@inertiajs/vue3";

defineProps<{ course: Course }>();
</script>

<template>
    <header class="bg-white shadow dark:bg-gray-800">
        <div class="container flex items-center justify-between py-6">
            <div>
                <BaseBreadcrumb
                    :items="[
                        {
                            href: route('course.index'),
                            label: 'Courses',
                        },
                        { label: course.title },
                    ]"
                />
            </div>

            <div class="inline-flex items-center justify-center gap-4">
                <!-- <Link
                    :href="
                        route('public.course.show', {
                            course: course.slug,
                        })
                    "
                    :class="
                        cn(buttonVariants({ size: 'sm', variant: 'outline' }))
                    "
                    v-if="course.is_published"
                >
                    View course page
                </Link> -->
                <Link
                    :href="
                        route('course.edit', {
                            course: course.id,
                        })
                    "
                    :class="cn(buttonVariants({ size: 'sm' }))"
                >
                    Settings
                </Link>
            </div>
        </div>
    </header>

    <div class="mt-6">
        <slot />
    </div>
</template>
