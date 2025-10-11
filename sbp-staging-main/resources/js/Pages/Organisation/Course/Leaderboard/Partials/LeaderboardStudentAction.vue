<script setup lang="ts">
import { Student } from "./leaderboard-column";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/Components/ui/alert-dialog";
import { Button } from "@/Components/ui/button";
import { EllipsisIcon, EllipsisVerticalIcon } from "lucide-vue-next";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
    student: Student;
}>();

const page = usePage();

const resetCourseProgressAlert = ref(false);
const unenrollCourseProgressAlert = ref(false);

const resetCourseForm = useForm({});
const unenrollCourseForm = useForm({});

function resetCourseProgress() {
    resetCourseForm.delete(
        route("organisation.course.leaderboard.reset.student.progress", {
            // @ts-ignore
            course: page.props?.course?.slug as string,
            student: props.student.user.id,
        }),
    );
}
function unenrollCourseProgress() {
    unenrollCourseForm.delete(
        route("organisation.course.student.destroy", {
            // @ts-ignore
            course: page.props?.course?.slug as string,
            student: props.student.user.id,
        }),
        {
            preserveScroll: true,
        },
    );
}
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
            <DropdownMenuItem @select="resetCourseProgressAlert = true">
                Reset course progress
            </DropdownMenuItem>
            <DropdownMenuItem @select="unenrollCourseProgressAlert = true">
                Unenroll
            </DropdownMenuItem>
            <!-- <DropdownMenuItem 
                                            >
                                                Reset lesson progress
                                            </DropdownMenuItem> -->
        </DropdownMenuContent>
    </DropdownMenu>

    <AlertDialog v-model:open="resetCourseProgressAlert">
        <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will reset
                    {{ student.user.name }}
                    progress in this course
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="resetCourseProgress"
                    >Continue</AlertDialogAction
                >
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>

    <AlertDialog v-model:open="unenrollCourseProgressAlert">
        <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will unenroll
                    {{ student.user.name }}
                    and reset user's progress in this course
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="unenrollCourseProgress">
                    Continue
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
