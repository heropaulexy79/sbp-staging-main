<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { Question, Lesson } from "@/types";
import { useForm } from "@inertiajs/vue3";
import QuizBuilder from "./QuizBuilder.vue";
import { generateId } from "./utils";
import { RichEditor } from "@/Components/RichEditor";
import { slugify } from "@/lib/utils";
import { WandSparklesIcon, RefreshCwIcon, CheckIcon } from "lucide-vue-next";
import axios from 'axios';
import { ref } from "vue";

const props = defineProps<{ lesson: Lesson }>();

const form = useForm({
    title: props.lesson.title,
    slug: props.lesson.slug,
    content: props.lesson.content,
    quiz:
        props.lesson.content_json &&
        typeof props.lesson.content_json !== "string"
            ? // TODO: PARSE WITH ZOD before hand
              props.lesson.content_json
            : [
                  {
                      id: generateId(),
                      text: "",
                      type: "single_choice",
                      options: [
                          { id: generateId(), text: "" },
                          { id: generateId(), text: "" },
                      ],
                  },
              ],
    type: props.lesson.type ?? "DEFAULT",
    is_published: props.lesson.is_published
        ? props.lesson.is_published + ""
        : "false",
});

// AI Generation state
const isGenerating = ref(false);
const generatedContent = ref("");
const showGeneratedContent = ref(false);

function submit() {
    form.patch(
        route("lesson.update", {
            course: props.lesson.course_id,
            lesson: props.lesson.id,
        }),
        {
            onSuccess() {},
            onError(error) {},
            preserveScroll: true,
        },
    );
}

function updateType(value: string) {
    value = value.toUpperCase();
    // form.content = "";

    if (value === "DEFAULT") {
        form.quiz = [];
    } else {
        form.content = "";
    }
}

function generateSlug() {
    form.slug = slugify(form.title);
}

async function generateWithAI() {
    if (!form.title.trim()) {
        alert('Please enter a lesson title first');
        return;
    }
    
    isGenerating.value = true;
    showGeneratedContent.value = false;
    
    try {
        const requestData = {
            title: form.title,
            course_id: props.lesson.course_id,
            options: {
                type: form.type,
                include_examples: true
            }
        };
        
        const response = await axios.post('/api/rag/generate-content', requestData);
        
        if (response.data.success) {
            console.log('Generated content:', response.data.content);
            generatedContent.value = response.data.content;
            showGeneratedContent.value = true;
        } else {
            alert('Failed to generate content: ' + response.data.message);
        }
    } catch (error) {
        console.error('Error generating content:', error);
        if (error.response) {
            alert('Failed to generate content: ' + (error.response.data.message || 'Server error'));
        } else {
            alert('Failed to generate content. Please try again.');
        }
    } finally {
        isGenerating.value = false;
    }
}

function useGeneratedContent() {
    if (generatedContent.value) {
        form.content = generatedContent.value;
        showGeneratedContent.value = false;
        generatedContent.value = "";
    }
}

function regenerateContent() {
    generateWithAI();
}

function dismissGeneratedContent() {
    showGeneratedContent.value = false;
    generatedContent.value = "";
}
</script>

<template>
    <form @submit.prevent="submit">
        <div
            class="relative grid gap-6 md:grid-cols-[1fr_200px] md:gap-10 lg:grid-cols-[1fr_250px]"
        >
            <!-- Left -->
            <div
                class="grid gap-6 rounded-md bg-background px-4 py-4 md:grid-cols-2"
            >
                <div>
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        placeholder="Enter the title of this lesson"
                        v-model="form.title"
                        class="mt-2"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div>
                    <Label for="type">Type</Label>
                    <Select
                        id="type"
                        v-model:model-value="form.type"
                        @update:model-value="updateType"
                        disabled
                    >
                        <SelectTrigger class="mt-2">
                            <SelectValue placeholder="Select lesson type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="DEFAULT"> Default </SelectItem>
                            <SelectItem value="QUIZ"> Quiz </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="md:col-span-2">
                    <div class="mt-2">
                        <div v-if="form.type === 'DEFAULT'">
                            <Label for="content">Content</Label>
                            <RichEditor
                                id="content"
                                v-model="form.content"
                                class="mt-2"
                            />
                        </div>

                        <div v-if="form.type === 'QUIZ'" class="">
                            <QuizBuilder
                                :errors="form.errors"
                                v-model:model-value="form.quiz as Question[]"
                            />
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.content" />
                    
                    <!-- AI Content Generation -->
                    <div v-if="form.type === 'DEFAULT'" class="mt-4">
                        <div class="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="generateWithAI"
                                :disabled="isGenerating"
                                class="flex items-center gap-2"
                            >
                                <WandSparklesIcon class="h-4 w-4" />
                                {{ isGenerating ? 'Generating...' : 'Generate with AI' }}
                            </Button>
                            
                            <Button
                                v-if="isGenerating"
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="regenerateContent"
                                class="flex items-center gap-2"
                            >
                                <RefreshCwIcon class="h-4 w-4" />
                                Regenerate
                            </Button>
                        </div>
                        
                        <!-- Generated Content Preview -->
                        <div v-if="showGeneratedContent" class="mt-4 p-4 border rounded-lg bg-muted/50">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium">Generated Content Preview</h4>
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="useGeneratedContent"
                                        class="flex items-center gap-1"
                                    >
                                        <CheckIcon class="h-3 w-3" />
                                        Use This Content
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="dismissGeneratedContent"
                                    >
                                        Dismiss
                                    </Button>
                                </div>
                            </div>
                            <div 
                                class="prose prose-sm max-w-none"
                                v-html="generatedContent"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right -->
            <aside class="sticky top-4 space-y-6 self-start rounded-md px-4">
                <Button
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Save
                </Button>

                <div>
                    <Label for="slug">Slug</Label>
                    <div class="mt-2 flex items-center justify-center">
                        <Input id="slug" v-model="form.slug" />
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            class="flex-shrink-0"
                            :disabled="!form.title"
                            @click="generateSlug"
                        >
                            <WandSparklesIcon class="size-4" />
                        </Button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.slug" />
                </div>

                <div>
                    <Label for="type">Status</Label>
                    <Select id="type" v-model:model-value="form.is_published">
                        <SelectTrigger class="mt-2">
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="true"> Published </SelectItem>
                            <SelectItem value="false"> Draft </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError
                        class="mt-2"
                        :message="form.errors.is_published"
                    />
                </div>
            </aside>
        </div>
    </form>
</template>
