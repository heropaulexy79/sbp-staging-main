<script lang="ts" setup>
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import { Checkbox } from "@/Components/ui/checkbox";
import { Input } from "@/Components/ui/input";
import { RadioGroup, RadioGroupItem } from "@/Components/ui/radio-group";
import { Course, Lesson } from "@/types";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { toast } from "vue-sonner";
import { WithUserLesson } from "./types";
import { useQuizAnswerManager } from "./use-quiz-answer-manager";
import { computed } from "vue";
import { cn } from "@/lib/utils";
import { AngryIcon, ArrowLeft, ArrowRight, SmileIcon } from "lucide-vue-next";
import { Progress } from "@/Components/ui/progress";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";

const page = usePage();

const props = defineProps<{
    course: Course;
    lesson: WithUserLesson<Lesson>;
    nextLessonId: Lesson["slug"] | null;
}>();

const {
    answerQuestion,
    nextQuestion,
    previousQuestion,
    currentQuesion,
    currentQuestionIdx,
    currentAnswer,
    hasNextQuesion,
    hasPreviousQuesion,
    answers,
} = useQuizAnswerManager(
    `${props.course.id}:${props.lesson.id}`,
    props.lesson.content_json || [],
    props.lesson.answers ?? null,
);

const successDialog = ref(false);
const typeAnswer = ref<string>("");
const retakeMode = ref(false);

// Debug: Log lesson data to understand the structure
console.log('Lesson data:', props.lesson);
console.log('Content JSON:', props.lesson.content_json);

const completionForm = useForm({
    answers: [],
});

function submit() {
    completionForm
        .transform((d) => {
            const ans = Array.from(answers.value.values());
            return {
                // When console.log ing it shows as a proxy
                // answers: ans.map(({ ...rest }) => ({
                //     ...rest,
                // })),
                answers: ans,
            };
        })
        .patch(
            route("classroom.lesson.answerQuiz", {
                course: props.course.slug,
                lesson: props.lesson.slug,
            }),
            {
                // preserveState: true,
                onSuccess(page) {
                    console.log('Quiz submission success:', page.props.flash);
                    // Show results dialog immediately after successful submission
                        successDialog.value = true;
                },
                onError(error) {
                    toast.error(JSON.stringify(error));
                },
            },
        );
}

const isCompleted = props.lesson.completed;
const isQuizLocked = computed(() => isCompleted && !retakeMode.value);

const correctOption = computed(() => {
    if (!isQuizLocked.value) return null;

    const n = currentQuesion.value;

    const r = n.options.find(
        // @ts-ignore
        (r) => r.id === n.correct_option,
    );

    return r;
});

// Helpers for multiple select
const multiSelected = computed<string[]>(() => {
    const ans = currentAnswer.value as unknown as string[] | undefined;
    return Array.isArray(ans) ? ans : [];
});

function toggleMultiSelect(optionId: string) {
    const current = new Set(multiSelected.value);
    if (current.has(optionId)) {
        current.delete(optionId);
    } else {
        current.add(optionId);
    }
    answerQuestion(currentQuesion.value.id, Array.from(current));
}

// Get Kahoot-style colors for any number of options
function getOptionColor(index: number) {
    const colors = [
        'bg-red-500 hover:bg-red-600',      // A - Red
        'bg-blue-500 hover:bg-blue-600',    // B - Blue  
        'bg-yellow-500 hover:bg-yellow-600', // C - Yellow
        'bg-green-500 hover:bg-green-600',   // D - Green
        'bg-purple-500 hover:bg-purple-600', // E - Purple
        'bg-pink-500 hover:bg-pink-600',     // F - Pink
        'bg-indigo-500 hover:bg-indigo-600', // G - Indigo
        'bg-orange-500 hover:bg-orange-600', // H - Orange
    ];
    return colors[index % colors.length] || 'bg-gray-500 hover:bg-gray-600';
}

function onContinue() {
    if (props.nextLessonId) {
        router.visit(
            route("classroom.lesson.show", {
                lesson: props.nextLessonId,
                course: props.course.slug,
            }),
        );

        return;
    }
    router.visit(
        route("classroom.course.completed.show", {
            course: props.course.slug,
        }),
    );
    // successDialog.value = false;
    // router.reload();
}

function retakeQuiz() {
    // Enter retake mode: unlock UI and clear previous answers and last question
    retakeMode.value = true;
    successDialog.value = false;
    // Clear persisted answers and last question for this quiz session
    try {
        localStorage.removeItem(`lesson:answers:${props.course.id}:${props.lesson.id}`);
        localStorage.removeItem(`lesson:question:${props.course.id}:${props.lesson.id}`);
    } catch {}
    // Reload the page state to re-initialize the answer manager with empty state
    router.reload();
}

function showPreviousResults() {
    successDialog.value = true;
}

const score = computed(() => {
    if (!props.lesson.content_json || !Array.isArray(props.lesson.content_json)) {
        return 0;
    }
    
    return props.lesson.content_json.reduce((prev, curr) => {
        const answr = answers.value.get(curr.id);

        if (answr?.selected_option === curr.correct_option) {
            return prev + 1;
        }

        return prev;
    }, 0);
});

const totalQuestions = computed(() => props.lesson.content_json?.length || 0);

const scoreInPercent = computed(() => {
    // Use flash message score if available (from backend), otherwise calculate
    const flashScore = page.props.flash?.message?.score;
    if (flashScore !== undefined) {
        return flashScore;
    }
    return totalQuestions.value > 0 ? (score.value / totalQuestions.value) * 100 : 0;
});

const correctCount = computed(() => {
    const flashScore = page.props.flash?.message?.score;
    if (flashScore !== undefined) {
        // Calculate correct count from percentage
        return Math.round((flashScore / 100) * totalQuestions.value);
    }
    return score.value;
});

const incorrectCount = computed(() => totalQuestions.value - correctCount.value);
</script>

<template>
    <div
        class="flex min-h-[calc(100svh-65px)] flex-col items-center justify-center bg-gradient-to-b from-rose-100 to-teal-100 p-4 dark:from-gray-900 dark:to-gray-800"
    >
        <Card class="mx-auto mb-6 w-full max-w-screen-md rounded-md">
            <CardHeader>
                <div class="flex items-center justify-end gap-4">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="successDialog = true"
                        v-if="isQuizLocked"
                    >
                        Show Previous Results
                    </Button>

                    <Progress
                        :model-value="
                            lesson.content_json && lesson.content_json.length > 0
                                ? ((currentQuestionIdx + 1) / lesson.content_json.length) * 100
                                : 0
                        "
                        class="h-3"
                    />
                    <span class="flex-shrink-0">
                        {{ currentQuestionIdx + 1 }} /
                        {{ lesson.content_json?.length || 0 }}
                    </span>
                </div>
            </CardHeader>
        </Card>
        <Card class="mx-auto w-full max-w-screen-md rounded-md">
            <CardHeader>
                <!-- <div class="mb-6 flex items-center justify-end gap-4">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="successDialog = true"
                        v-if="isCompleted"
                    >
                        Show Previous Results
                    </Button>

                    <Progress
                        :model-value="
                            lesson.content_json && lesson.content_json.length > 0
                                ? ((currentQuestionIdx + 1) / lesson.content_json.length) * 100
                                : 0
                        "
                        class="h-3"
                    />
                    <span class="flex-shrink-0">
                        {{ currentQuestionIdx + 1 }} /
                        {{ lesson.content_json?.length || 0 }}
                    </span>
                </div> -->
                <CardTitle class="mb-6 max-w-screen-sm text-lg font-bold">
                    {{ currentQuesion.text }}
                </CardTitle>
            </CardHeader>
            <CardContent class="">
                <div class="relative">
                    <!-- <div class="my-6">
            You scored {{ score }} / {{ lesson.content_json.length }}
        </div> -->

                    <!-- <CardTitle class="mb-6 max-w-screen-sm text-lg font-bold">
                        {{ currentQuesion.text }}
                    </CardTitle> -->

                    <div class="">
                        <!-- Kahoot-style colored tiles for single choice -->
                        <div 
                            v-if="currentQuesion.type === 'single_choice'"
                            :class="cn(
                                'gap-4 max-w-4xl mx-auto',
                                currentQuesion.options.length <= 2 ? 'grid grid-cols-2' : '',
                                currentQuesion.options.length === 3 ? 'grid grid-cols-3' : '',
                                currentQuesion.options.length === 4 ? 'grid grid-cols-2' : '',
                                currentQuesion.options.length === 5 ? 'grid grid-cols-3' : '',
                                currentQuesion.options.length === 6 ? 'grid grid-cols-3' : '',
                                currentQuesion.options.length >= 7 ? 'grid grid-cols-4' : ''
                            )"
                        >
                            <div
                                v-for="(option, index) in currentQuesion.options"
                                :key="option.id"
                                class="relative group cursor-pointer"
                                @click="!isQuizLocked && answerQuestion(currentQuesion.id, option.id)"
                            >
                                <!-- Kahoot-style colored tile -->
                                <div
                                    :class="cn(
                                        'h-32 rounded-xl flex items-center justify-center text-white font-bold text-lg transition-all duration-200 transform',
                                        'hover:scale-105 hover:shadow-lg active:scale-95',
                                        isQuizLocked ? 'cursor-not-allowed opacity-75' : 'cursor-pointer',
                                        // Dynamic Kahoot colors
                                        getOptionColor(index),
                                        // Selection state
                                        currentAnswer === option.id ? 'ring-4 ring-white ring-opacity-50 scale-105' : '',
                                        // Correct/incorrect feedback
                                        isQuizLocked && currentAnswer === option.id
                                            ? correctOption?.id === option.id
                                                ? 'ring-4 ring-green-300 bg-green-500'
                                                : 'ring-4 ring-red-300 bg-red-500'
                                            : '',
                                        isQuizLocked && correctOption?.id === option.id && currentAnswer !== option.id
                                            ? 'ring-4 ring-green-300 bg-green-500'
                                            : ''
                                    )"
                                >
                                    <!-- Option letter (A, B, C, D) -->
                                    <div class="absolute top-2 left-2 w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold">
                                        {{ String.fromCharCode(65 + index) }}
                                    </div>
                                    
                                    <!-- Option text -->
                                    <div class="text-center px-4">
                                        <div class="text-sm opacity-90 mb-1">Option {{ String.fromCharCode(65 + index) }}</div>
                                        <div class="font-semibold leading-tight">{{ option.text }}</div>
                                    </div>
                                    
                                    <!-- Selection indicator -->
                                    <div 
                                        v-if="currentAnswer === option.id"
                                        class="absolute top-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center"
                                    >
                                        <div class="w-3 h-3 bg-current rounded-full"></div>
                                    </div>
                                    
                                    <!-- Correct answer indicator -->
                                    <div 
                                        v-if="isQuizLocked && correctOption?.id === option.id"
                                        class="absolute bottom-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TRUE/FALSE as Kahoot-style big buttons -->
                        <div v-else-if="currentQuesion.type === 'true_false'" class="grid grid-cols-2 gap-4">
                            <Button
                                :disabled="isCompleted"
                                class="h-20 text-lg font-semibold bg-emerald-600 hover:bg-emerald-700"
                                @click="answerQuestion(currentQuesion.id, currentQuesion.options?.find(o => o.text?.toLowerCase?.() === 'true')?.id as string)"
                            >
                                True
                            </Button>
                            <Button
                                :disabled="isCompleted"
                                class="h-20 text-lg font-semibold bg-rose-600 hover:bg-rose-700"
                                @click="answerQuestion(currentQuesion.id, currentQuesion.options?.find(o => o.text?.toLowerCase?.() === 'false')?.id as string)"
                            >
                                False
                            </Button>
                        </div>

                        <!-- MULTIPLE SELECT with Kahoot-style colored tiles -->
                        <div 
                            v-else-if="currentQuesion.type === 'multiple_select'" 
                            :class="cn(
                                'gap-4 max-w-4xl mx-auto',
                                currentQuesion.options.length <= 2 ? 'grid grid-cols-2' : '',
                                currentQuesion.options.length === 3 ? 'grid grid-cols-3' : '',
                                currentQuesion.options.length === 4 ? 'grid grid-cols-2' : '',
                                currentQuesion.options.length === 5 ? 'grid grid-cols-3' : '',
                                currentQuesion.options.length === 6 ? 'grid grid-cols-3' : '',
                                currentQuesion.options.length >= 7 ? 'grid grid-cols-4' : ''
                            )"
                        >
                            <div
                                v-for="(option, index) in currentQuesion.options"
                                :key="option.id"
                                class="relative group cursor-pointer"
                                @click="!isQuizLocked && toggleMultiSelect(option.id as string)"
                            >
                                <!-- Kahoot-style colored tile with checkbox -->
                                <div
                                    :class="cn(
                                        'h-32 rounded-xl flex items-center justify-center text-white font-bold text-lg transition-all duration-200 transform relative',
                                        'hover:scale-105 hover:shadow-lg active:scale-95',
                                        isQuizLocked ? 'cursor-not-allowed opacity-75' : 'cursor-pointer',
                                        // Dynamic Kahoot colors
                                        getOptionColor(index),
                                        // Selection state - multiple can be selected
                                        multiSelected.includes(option.id as string) ? 'ring-4 ring-white ring-opacity-50 scale-105' : '',
                                        // Correct/incorrect feedback
                                        isQuizLocked && multiSelected.includes(option.id as string)
                                            ? option.is_correct
                                                ? 'ring-4 ring-green-300 bg-green-500'
                                                : 'ring-4 ring-red-300 bg-red-500'
                                            : '',
                                        isQuizLocked && option.is_correct && !multiSelected.includes(option.id as string)
                                            ? 'ring-4 ring-green-300 bg-green-500'
                                            : ''
                                    )"
                                >
                                    <!-- Option letter (A, B, C, D) -->
                                    <div class="absolute top-2 left-2 w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold">
                                        {{ String.fromCharCode(65 + index) }}
                                    </div>
                                    
                                    <!-- Option text -->
                                    <div class="text-center px-4">
                                        
                                        <div class="font-semibold text-sm mt-2 leading-tight">{{ option.text }}</div>
                                    </div>
                                    
                                    <!-- Multiple selection checkbox indicator -->
                                    <div 
                                        v-if="multiSelected.includes(option.id as string)"
                                        class="absolute top-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4 text-current" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Correct answer indicator -->
                                    <div 
                                        v-if="isQuizLocked && option.is_correct"
                                        class="absolute bottom-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Selection count for multiple select -->
                        <div v-if="currentQuesion.type === 'multiple_select' && multiSelected.length > 0" class="text-center mt-4">
                            <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ multiSelected.length }} option{{ multiSelected.length !== 1 ? 's' : '' }} selected
                            </div>
                        </div>

                        <!-- TYPE ANSWER free text -->
                        <div v-else-if="currentQuesion.type === 'type_answer'" class="space-y-3">
                            <Label for="type-answer">Your Answer</Label>
                            <Input
                                id="type-answer"
                            :disabled="isQuizLocked"
                                v-model="typeAnswer"
                                @keyup.enter="answerQuestion(currentQuesion.id, typeAnswer)"
                                placeholder="Type your answer"
                            />
                            <div>
                                <Button
                                    size="sm"
                                    :disabled="isQuizLocked || !typeAnswer.trim()"
                                    @click="answerQuestion(currentQuesion.id, typeAnswer)"
                                >
                                    Save Answer
                                </Button>
                            </div>
                        </div>

                        <div v-if="isQuizLocked" class="my-4">
                            The correct answer is {{ correctOption?.text }}
                        </div>
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between gap-4 [&_button]:min-w-20"
                    >
                        <Button
                            class="group"
                            variant="secondary"
                            size="sm"
                            @click="previousQuestion"
                            :disabled="!hasPreviousQuesion"
                        >
                            <ArrowLeft
                                :size="16"
                                class="transition-all group-hover:-translate-x-2"
                            />

                            Previous
                        </Button>
                        <Button
                            class="group"
                            size="sm"
                            @click="nextQuestion"
                            :disabled="!hasNextQuesion"
                            v-if="hasNextQuesion || isQuizLocked"
                        >
                            Next

                            <ArrowRight
                                :size="16"
                                class="transition-all group-hover:translate-x-2"
                            />
                        </Button>

                        <Button
                            class="group"
                            size="sm"
                            @click="submit"
                            v-if="!hasNextQuesion && !isQuizLocked"
                        >
                            Submit
                            <ArrowRight
                                :size="16"
                                class="transition-all group-hover:translate-x-2"
                            />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <div>
            <Dialog v-model:open="successDialog">
                <!-- <DialogTrigger as-child>
      <Button variant="outline">
        Edit Profile
      </Button>
    </DialogTrigger> -->
                <DialogContent class="sm:max-w-[575px]">
                    <DialogHeader>
                        <DialogTitle class="text-center">
                            {{ isCompleted ? 'Quiz Results' : 'Quiz Completed!' }}
                        </DialogTitle>
                        <!-- <DialogDescription>
                        Make changes to your profile here. Click save when
                        you're done.
                    </DialogDescription> -->
                    </DialogHeader>
                    <div class="text-center">
                        <div
                            class="radial-progress mx-auto size-32 rounded-full text-4xl font-bold"
                            :style="{
                                '--progress': Number(scoreInPercent),
                            }"
                        >
                            <span
                                class="flex size-28 items-center justify-center rounded-full bg-background"
                            >
                                {{ scoreInPercent ?? 0 }} %
                            </span>
                        </div>

                        <!-- <div class="mt-4">
                        {{ $page.props.flash.message?.message }}
                    </div> -->

                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col items-center space-y-2">
                                <SmileIcon class="size-8 text-green-600" />
                                <span class="text-sm font-medium">
                                    Correct Answers
                                </span>
                                <span class="text-2xl font-bold">{{
                                    correctCount
                                }}</span>
                            </div>
                            <div class="flex flex-col items-center space-y-2">
                                <AngryIcon class="size-8 text-destructive" />
                                <span class="text-sm font-medium">
                                    Incorrect Answers
                                </span>
                                <span class="text-2xl font-bold">
                                    {{ incorrectCount }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Show total questions -->
                        <div class="mt-4 text-sm text-muted-foreground">
                            Total Questions: {{ totalQuestions }}
                        </div>
                        
                        <!-- Show previous score message for completed quizzes -->
                        <div v-if="isCompleted && props.lesson.score" class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <div class="text-sm text-blue-800">
                                <strong>Previous Score:</strong> {{ Math.round(props.lesson.score) }}%
                            </div>
                        </div>
                    </div>
                    <DialogFooter
                        class="mt-4 items-center justify-center sm:justify-center gap-2"
                    >
                        <Button 
                            variant="outline" 
                            @click="retakeQuiz"
                            v-if="isQuizLocked"
                        >
                            Retake Quiz
                        </Button>
                        <Button @click="onContinue"> Continue </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
