<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/Components/ui/dialog";
import { VisuallyHidden } from "radix-vue";
import QuizGeneratorForm from "./QuizGeneratorForm.vue";

interface Course {
    id: number;
    title: string;
    description: string;
}

const props = defineProps<{
    course: Course;
    isOpen: boolean;
}>();

const emit = defineEmits<{
    'update:isOpen': [value: boolean];
    success: [];
}>();

function handleSuccess() {
    emit('success');
    emit('update:isOpen', false);
}

function handleClose() {
    emit('update:isOpen', false);
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="handleClose">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
            <VisuallyHidden>
                <DialogHeader aria-hidden="true" hidden class="invisible h-0 w-0">
                    <DialogTitle>Generate Quizzes with AI</DialogTitle>
                    <DialogDescription>
                        Create quiz questions automatically using AI based on your course content
                    </DialogDescription>
                </DialogHeader>
            </VisuallyHidden>
            
            <div class="space-y-4">
                <div class="text-center space-y-2">
                    <h2 class="text-2xl font-bold">Generate Quizzes with AI</h2>
                    <p class="text-muted-foreground">
                        Create quiz questions automatically using AI based on your course content
                    </p>
                </div>
                
                <QuizGeneratorForm 
                    :course="course" 
                    @success="handleSuccess"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>
