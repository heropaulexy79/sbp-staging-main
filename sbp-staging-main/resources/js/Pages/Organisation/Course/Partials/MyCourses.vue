<script setup lang="ts">
import CourseTable from "./CourseTable.vue";
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
import CreateCourseForm from "./CreateCourseForm.vue";
import BaseBreadcrumb from "@/Components/ui/BaseBreadcrumb.vue";

defineProps<{ courses: Course[] }>();
</script>

<template>
    <div class="container">
        <div class="space-y-6">
            <CourseTable :courses="courses" />
        </div>
    </div>
</template>
