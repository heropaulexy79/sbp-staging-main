<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import CourseTable from "./Partials/CourseTable.vue";
import { Course } from "@/types";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { VisuallyHidden } from "radix-vue";
import { Button } from "@/Components/ui/button";
import CreateCourseForm from "./Partials/CreateCourseForm.vue";
import UploadResourceForm from "./Partials/UploadResourceForm.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";
import CourseCard from "./Partials/CourseCard.vue";
import { UploadIcon } from "lucide-vue-next";

defineProps<{ courses: Course[] }>();
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
                                label: 'Courses',
                            },
                        ]"
                    />
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Upload Resource Button -->
                    <Dialog>
                        <DialogTrigger
                            v-if="
                                $page.props.auth.user.organisation_id &&
                                $page.props.auth.user.role === 'ADMIN'
                            "
                        >
                            <Button size="sm" variant="outline">
                                <UploadIcon class="size-4 mr-2" />
                                Upload Resource
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="max-w-[768px]">
                            <VisuallyHidden>
                                <DialogHeader
                                    aria-hidden="true"
                                    hidden
                                    class="invisible h-0 w-0"
                                >
                                    <DialogTitle>Upload Resource</DialogTitle>
                                    <DialogDescription>
                                        Upload documents or paste text to create searchable content
                                    </DialogDescription>
                                </DialogHeader>
                            </VisuallyHidden>
                            <UploadResourceForm
                                :courses="courses"
                                @success="() => {}"
                            />
                        </DialogContent>
                    </Dialog>


                    <!-- Create Course Button -->
                    <Dialog>
                        <DialogTrigger
                            v-if="
                                $page.props.auth.user.organisation_id &&
                                $page.props.auth.user.role === 'ADMIN'
                            "
                        >
                            <Button size="sm">Create course</Button>
                        </DialogTrigger>
                        <DialogContent class="max-w-[768px]">
                            <VisuallyHidden>
                                <DialogHeader
                                    aria-hidden="true"
                                    hidden
                                    class="invisible h-0 w-0"
                                >
                                    <DialogTitle> Create a course </DialogTitle>
                                    <DialogDescription>
                                        <!-- Create a course -->
                                    </DialogDescription>
                                </DialogHeader>
                            </VisuallyHidden>
                            <CreateCourseForm
                                :organisation_id="
                                    $page.props.auth.user.organisation_id!
                                "
                            />
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </header>
        <div class="py-12">
            <div class="container">
                <div class="space-y-6">
                    <CourseTable
                        :courses="courses"
                        v-if="
                            $page.props.auth.user.organisation_id &&
                            $page.props.auth.user.role === 'ADMIN'
                        "
                    />

                    <ul
                        class="space-y-4"
                        v-if="
                            $page.props.auth.user.organisation_id &&
                            $page.props.auth.user.role !== 'ADMIN'
                        "
                    >
                        <li v-for="course in courses">
                            <CourseCard :course="course" />
                        </li>
                        <li v-if="courses.length === 0" class="text-center">
                            No courses at the moment
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
