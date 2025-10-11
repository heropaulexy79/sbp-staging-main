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
import { Course, Question } from "@/types";
import { useForm } from "@inertiajs/vue3";
import axios from 'axios';
import QuizBuilder from "./QuizBuilder.vue";
import { generateId } from "./utils";
import { RichEditor } from "@/Components/RichEditor";
import { slugify } from "@/lib/utils";
import { WandSparklesIcon, RefreshCwIcon, CheckIcon, BookOpenIcon } from "lucide-vue-next";
import { ref, onMounted, computed } from "vue";
import { Checkbox } from "@/Components/ui/checkbox";
import { Badge } from "@/Components/ui/badge";
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/Components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { Check, ChevronsUpDown } from "lucide-vue-next";

const props = defineProps<{ course: Course }>();

const form = useForm({
    title: "",
    slug: "",
    content: "",
    quiz: [
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
    type: "DEFAULT",
    is_published: "false",
});

// AI Generation state
const isGenerating = ref(false);
const generatedContent = ref("");
const showGeneratedContent = ref(false);

// Resource selection state
const courseResources = ref<Array<{
    id: string;
    title: string;
    content: string;
    metadata: any;
}>>([]);
const selectedResources = ref<string[]>([]);
const isLoadingResources = ref(false);
const isResourceDropdownOpen = ref(false);
const resourceSearchQuery = ref("");

function createLesson() {
    // console.log(form.content);
    form.transform((r) => {
        const slug = r.slug;

        return {
            ...r,
            ...(slug.trim().length > 0 ? { slug } : { slug: slugify(r.title) }),
        };
    }).post(route("lesson.store", { course: props.course.id }), {
        onSuccess() {},
        onError(error) {
            console.log(error);
        },
        preserveScroll: true,
    });

    // form.content = "zero";
    // form.content = "Zero";

    // console.log(form);
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
    
    // Debug logging
    console.log('Course object:', props.course);
    console.log('Course ID:', props.course.id);
    console.log('Title:', form.title);
    
    try {
        const requestData = {
            title: form.title,
            course_id: props.course.id,
            options: {
                type: form.type,
                include_examples: true,
                reference_resources: selectedResources.value.length > 0 ? selectedResources.value : null
            }
        };
        
        console.log('Sending request data:', requestData);
        
        // Use axios which automatically handles CSRF tokens in Laravel
        const response = await axios.post('/api/rag/generate-content', requestData, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        
        console.log('Response status:', response.status);
        console.log('Response data:', response.data);
        
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
            // Server responded with error status
            console.error('Error response:', error.response.data);
            alert('Failed to generate content: ' + (error.response.data.message || 'Server error'));
        } else if (error.request) {
            // Request was made but no response received
            alert('Failed to generate content. Please check your internet connection and try again.');
        } else {
            // Something else happened
            alert('Failed to generate content. Please try again.');
        }
    } finally {
        isGenerating.value = false;
    }
}

function useGeneratedContent() {
    // Ensure the content is properly formatted for the RichEditor
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

// Fetch course resources
async function fetchCourseResources() {
    if (!props.course.id) return;
    
    isLoadingResources.value = true;
    try {
        const response = await axios.get(`/api/resources/course/${props.course.id}`);
        if (response.data.success) {
            courseResources.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to fetch course resources:', error);
    } finally {
        isLoadingResources.value = false;
    }
}

// Toggle resource selection
function toggleResource(resourceId: string) {
    const index = selectedResources.value.indexOf(resourceId);
    if (index > -1) {
        selectedResources.value.splice(index, 1);
    } else {
        selectedResources.value.push(resourceId);
    }
}

// Remove selected resource
function removeSelectedResource(resourceId: string) {
    const index = selectedResources.value.indexOf(resourceId);
    if (index > -1) {
        selectedResources.value.splice(index, 1);
    }
}

// Get selected resource details
function getSelectedResourceDetails() {
    return courseResources.value.filter(resource => 
        selectedResources.value.includes(resource.id)
    );
}

// Filtered resources based on search query
const filteredResources = computed(() => {
    if (!resourceSearchQuery.value) {
        return courseResources.value;
    }
    
    const query = resourceSearchQuery.value.toLowerCase();
    return courseResources.value.filter(resource => 
        resource.title.toLowerCase().includes(query) ||
        resource.content.toLowerCase().includes(query)
    );
});

// Add resource to selection
function addResource(resourceId: string) {
    if (!selectedResources.value.includes(resourceId)) {
        selectedResources.value.push(resourceId);
    }
    resourceSearchQuery.value = "";
    isResourceDropdownOpen.value = false;
}

// Load resources on component mount
onMounted(() => {
    fetchCourseResources();
});
</script>

<template>
    <form @submit.prevent="createLesson">
        <div
            class="relative grid gap-6 md:grid-cols-[1fr_250px] md:gap-10 lg:grid-cols-[1fr_350px]"
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

                <!-- Resource Selection (only for DEFAULT type) -->
                <div v-if="form.type === 'DEFAULT'" class="md:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <Label>Reference Resources (Optional)</Label>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="fetchCourseResources"
                            :disabled="isLoadingResources"
                            class="text-xs"
                        >
                            <RefreshCwIcon class="size-3 mr-1" :class="{ 'animate-spin': isLoadingResources }" />
                            Refresh
                        </Button>
                    </div>
                    
                    <!-- Selected Resources Display -->
                    <div v-if="selectedResources.length > 0" class="mb-3">
                        <div class="flex flex-wrap gap-2">
                            <Badge 
                                v-for="resourceId in selectedResources" 
                                :key="resourceId"
                                variant="secondary"
                                class="flex items-center gap-1"
                            >
                                <BookOpenIcon class="size-3" />
                                {{ courseResources.find(r => r.id === resourceId)?.title || 'Unknown' }}
                                <button
                                    type="button"
                                    @click="removeSelectedResource(resourceId)"
                                    class="ml-1 hover:text-destructive"
                                >
                                    ×
                                </button>
                            </Badge>
                        </div>
                    </div>

                    <!-- Searchable Resource Dropdown -->
                    <div v-if="courseResources.length > 0">
                        <Popover v-model:open="isResourceDropdownOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="isResourceDropdownOpen"
                                    class="w-full justify-between"
                                >
                                    <span class="truncate">
                                        {{ resourceSearchQuery || "Search and select resources..." }}
                                    </span>
                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-full p-0" align="start">
                                <Command>
                                    <CommandInput 
                                        placeholder="Search resources..." 
                                        v-model="resourceSearchQuery"
                                    />
                                    <CommandList>
                                        <CommandEmpty>No resources found.</CommandEmpty>
                                        <CommandGroup>
                                            <CommandItem
                                                v-for="resource in filteredResources"
                                                :key="resource.id"
                                                :value="resource.id"
                                                @select="addResource(resource.id)"
                                                class="flex items-start space-x-2 p-3"
                                            >
                                                <Check 
                                                    :class="[
                                                        'mr-2 h-4 w-4',
                                                        selectedResources.includes(resource.id) ? 'opacity-100' : 'opacity-0'
                                                    ]" 
                                                />
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                        {{ resource.title }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                        {{ resource.content.substring(0, 80) }}{{ resource.content.length > 80 ? '...' : '' }}
                                                    </p>
                                                    <div v-if="resource.metadata" class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-400">
                                                            {{ resource.metadata.source_type?.toUpperCase() || 'TEXT' }}
                                                        </span>
                                                        <span v-if="resource.metadata.content_size" class="text-xs text-gray-400">
                                                            {{ Math.round(resource.metadata.content_size / 1000) }}KB
                                                        </span>
                                                    </div>
                                                </div>
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>
                    
                    <!-- No Resources Message -->
                    <div v-else-if="!isLoadingResources" class="text-center py-4 text-gray-500 dark:text-gray-400">
                        <BookOpenIcon class="size-8 mx-auto mb-2 opacity-50" />
                        <p class="text-sm">No resources available for this course</p>
                        <p class="text-xs">Upload some resources first to use them as references</p>
                    </div>
                    
                    <!-- Loading State -->
                    <div v-if="isLoadingResources" class="text-center py-4">
                        <RefreshCwIcon class="size-6 mx-auto mb-2 animate-spin" />
                        <p class="text-sm text-gray-500">Loading resources...</p>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="mt-2">
                        <div v-if="form.type === 'DEFAULT'">
                            <div class="flex items-center justify-between mb-2">
                                <Label for="content">Content</Label>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :disabled="!form.title || isGenerating"
                                    @click="generateWithAI"
                                    class="flex items-center gap-2"
                                >
                                    <WandSparklesIcon class="size-4" />
                                    {{ isGenerating ? 'Generating...' : 'Generate with AI' }}
                                </Button>
                            </div>
                            
                            <!-- Generated content preview -->
                            <div v-if="showGeneratedContent && generatedContent" class="mb-4 p-4 border rounded-lg bg-muted/50">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-sm">✨ AI Generated Content Preview</h4>
                                    <div class="flex gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            @click="regenerateContent"
                                            :disabled="isGenerating"
                                            class="flex items-center gap-1"
                                        >
                                            <RefreshCwIcon class="size-3" />
                                            Regenerate
                                        </Button>
                                        <Button
                                            type="button"
                                            size="sm"
                                            @click="useGeneratedContent"
                                            class="flex items-center gap-1"
                                        >
                                            <CheckIcon class="size-3" />
                                            Use This Content
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            @click="dismissGeneratedContent"
                                        >
                                            Dismiss
                                        </Button>
                                    </div>
                                </div>
                                <div 
                                    class="prose prose-sm max-w-none max-h-64 overflow-y-auto border rounded p-3 bg-background"
                                    v-html="generatedContent"
                                ></div>
                            </div>
                            
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
                </div>
            </div>

            <!-- Right -->
            <aside class="sticky top-4 space-y-6 self-start rounded-md px-4">
                <Button
                    type="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Create
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
