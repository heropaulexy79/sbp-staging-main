<script setup lang="ts">
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";
import BaseDataTable from "@/Components/ui/BaseDataTable.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
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
import { Course } from "@/types";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { Student, leaderboardColumns } from "./Partials/leaderboard-column";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import EnrollTeamInCourseForm from "../EnrollTeamInCourseForm.vue";

const props = defineProps<{
    students: Student[];
    course: Course;
}>();

const resetCourseProgressAlert = ref(false);
const enrollModal = ref(false);

const resetCourseForm = useForm({
    students: props.students.map((r) => r.user.id),
});

function resetCourseProgress() {
    resetCourseForm.delete(
        route("organisation.course.leaderboard.reset.students.progress", {
            course: props.course.slug,
            // student: props.students.map((r) => r.user.id),
        }),
    );
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="container flex items-center justify-between py-6">
                <div>
                    <BaseBreadcrumb
                        :items="[
                            {
                                href: route('dashboard'),
                                label: 'Courses',
                            },
                            {
                                href: route('course.show', { course }),
                                label: course.title,
                            },
                            { label: 'Leaderboard' },
                        ]"
                    />
                </div>
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="mx-auto max-w-2xl">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-2xl font-semibold tracking-tight">
                            Leaderboard
                        </h2>
                        <div
                            class="flex items-center justify-between gap-4"
                            v-if="$page.props.auth.user.role === 'ADMIN'"
                        >
                            <Dialog
                                :open="enrollModal"
                                @update:open="
                                    (v) => {
                                        enrollModal = v;
                                    }
                                "
                            >
                                <DialogTrigger as-child>
                                    <Button size="sm"> Enroll students </Button>
                                </DialogTrigger>
                                <DialogContent
                                    class="max-w-lg"
                                    @escape-key-down="(e) => e.preventDefault()"
                                >
                                    <DialogHeader>
                                        <DialogTitle>
                                            Enroll students in course
                                        </DialogTitle>
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
                                            }
                                        "
                                    />
                                </DialogContent>
                            </Dialog>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="resetCourseProgressAlert = true"
                            >
                                Reset all progress
                            </Button>
                        </div>
                    </div>
                    <!-- @vue-ignore -->
                    <BaseDataTable
                        :columns="leaderboardColumns"
                        :data="students"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <AlertDialog v-model:open="resetCourseProgressAlert">
        <!-- <AlertDialogTrigger>Open</AlertDialogTrigger> -->
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will reset selected
                    students progress in this course
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="resetCourseProgress">
                    Continue
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
