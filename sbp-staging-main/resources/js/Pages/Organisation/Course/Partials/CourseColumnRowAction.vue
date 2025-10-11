<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Course } from "@/types";
import { router } from "@inertiajs/vue3";
import { EllipsisIcon } from "lucide-vue-next";
import { ref } from "vue";
import EnrollTeamInCourseForm from "../EnrollTeamInCourseForm.vue";
import { toast } from "vue-sonner";

const props = defineProps<{
    course: Course;
}>();
const enrollModal = ref(false);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon">
                <EllipsisIcon class="size-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-48" align="end">
            <DropdownMenuLabel>
                <div
                    class="text-sm font-medium text-gray-800 dark:text-gray-200"
                >
                    Actions
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                v-if="course.is_published"
                @select="
                    () => {
                        enrollModal = true;
                    }
                "
            >
                Enroll students
            </DropdownMenuItem>
            <DropdownMenuItem
                @select="
                    () =>
                        router.visit(
                            route('organisation.course.leaderboard', {
                                course,
                            }),
                        )
                "
            >
                View leaderboard
            </DropdownMenuItem>
            <DropdownMenuItem
                @select="() => router.visit(route('course.edit', { course }))"
            >
                Manage course
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <Dialog
        :open="enrollModal"
        @update:open="
            (v) => {
                enrollModal = v;
            }
        "
    >
        <DialogContent
            class="max-w-lg"
            @escape-key-down="(e) => e.preventDefault()"
        >
            <DialogHeader>
                <DialogTitle> Enroll students in course </DialogTitle>
                <DialogDescription>
                    Enroll students into
                    {{ course.title }}
                </DialogDescription>
            </DialogHeader>

            <EnrollTeamInCourseForm
                :course="course"
                :on-success="
                    () => {
                        enrollModal = false;
                        toast.success('Students enrolled');
                    }
                "
            />
        </DialogContent>
    </Dialog>
</template>
