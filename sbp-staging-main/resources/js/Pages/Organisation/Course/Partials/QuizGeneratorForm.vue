<script setup lang="ts">
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
import { Checkbox } from "@/Components/ui/checkbox";
import { Badge } from "@/Components/ui/badge";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";
import { Progress } from "@/Components/ui/progress";
import { 
    WandSparklesIcon, 
    RefreshCwIcon, 
    CheckIcon, 
    XIcon, 
    BookOpenIcon,
    TrashIcon,
    PlusIcon,
    EyeIcon
} from "lucide-vue-next";
import { ref, onMounted, computed } from "vue";
import axios from 'axios';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from "@/Components/ui/command";
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover";
import { Check, ChevronsUpDown } from "lucide-vue-next";

interface Course {
    id: number;
    title: string;
    description: string;
}

interface Resource {
    id: string;
    title: string;
    content: string;
    metadata: any;
}

interface GeneratedQuiz {
    id: number;
    question: string;
    type: string;
    options?: Array<{
        id: number;
        option_text: string;
        is_correct: boolean;
        position: number;
    }>;
    metadata: {
        difficulty: string;
        explanation: string;
        course_id: number;
        generated_at: string;
    };
}

const props = defineProps<{ 
    course: Course;
    onSuccess?: () => void;
}>();

const emit = defineEmits<{
    success: [];
}>();

// Form state
const quizTitle = ref<string>('');
const quizTypes = ref<string[]>(['MULTIPLE_CHOICE']);
const quizCount = ref<number>(10);
const difficulty = ref<string>('medium');
const isPublished = ref<boolean>(false);
const selectedResources = ref<string[]>([]);

// Available quiz types
const availableQuizTypes = ref([
    { value: 'MULTIPLE_CHOICE', label: 'Multiple Choice' },
    { value: 'MULTIPLE_SELECT', label: 'Multiple Select' },
    { value: 'TRUE_FALSE', label: 'True/False' },
    { value: 'TYPE_ANSWER', label: 'Type Answer' },
    { value: 'PUZZLE', label: 'Puzzle' }
]);

// Generation state
const isGenerating = ref(false);
const generationProgress = ref(0);
const generatedQuizzes = ref<GeneratedQuiz[]>([]);
const showGeneratedQuizzes = ref(false);

// Resource selection state
const courseResources = ref<Resource[]>([]);
const isLoadingResources = ref(false);
const isResourceDropdownOpen = ref(false);
const resourceSearchQuery = ref("");

// Quiz management state
const unassignedQuizzes = ref<GeneratedQuiz[]>([]);
const isLoadingUnassigned = ref(false);

// Computed properties
const selectedCourse = computed(() => props.course);

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

const selectedResourceDetails = computed(() => {
    return courseResources.value.filter(resource => 
        selectedResources.value.includes(resource.id)
    );
});

// Methods
async function generateQuizzes() {
    if (!quizTitle.value.trim()) {
        alert('Please enter a quiz title');
        return;
    }

    if (quizTypes.value.length === 0) {
        alert('Please select at least one quiz type');
        return;
    }

    if (quizCount.value < 1 || quizCount.value > 50) {
        alert('Quiz count must be between 1 and 50');
        return;
    }

    isGenerating.value = true;
    generationProgress.value = 0;
    showGeneratedQuizzes.value = false;

    try {
        const requestData = {
            course_id: props.course.id,
            quiz_title: quizTitle.value.trim(),
            quiz_types: quizTypes.value,
            quiz_count: quizCount.value,
            difficulty: difficulty.value,
            is_published: isPublished.value,
            reference_resources: selectedResources.value.length > 0 ? selectedResources.value : undefined
        };

        // Simulate progress
        const progressInterval = setInterval(() => {
            if (generationProgress.value < 90) {
                generationProgress.value += Math.random() * 20;
            }
        }, 500);

        const response = await axios.post('/api/quizzes/generate', requestData, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        clearInterval(progressInterval);
        generationProgress.value = 100;

        if (response.data.success) {
            generatedQuizzes.value = response.data.data.quizzes;
            showGeneratedQuizzes.value = true;
            
            // Show success message with lesson info
            const lesson = response.data.data.lesson;
            const status = lesson.is_published ? 'Published' : 'Draft';
            alert(`Successfully created ${status.toLowerCase()} quiz lesson "${lesson.title}" with ${response.data.data.generated_count} questions!`);
            
            // Refresh unassigned quizzes
            await fetchUnassignedQuizzes();
            
            emit('success');
        } else {
            console.error('Quiz generation failed:', response.data);
            alert('Failed to generate quizzes: ' + response.data.message);
        }
    } catch (error) {
        console.error('Error generating quizzes:', error);
        if (error.response) {
            console.error('Error response:', error.response.data);
            console.error('Error status:', error.response.status);
            alert('Failed to generate quizzes: ' + (error.response.data.message || 'Server error'));
        } else {
            console.error('Request error:', error.message);
            alert('Failed to generate quizzes. Please try again.');
        }
    } finally {
        isGenerating.value = false;
        generationProgress.value = 0;
    }
}

async function fetchCourseResources() {
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

async function fetchUnassignedQuizzes() {
    isLoadingUnassigned.value = true;
    try {
        const response = await axios.get(`/api/quizzes/generated/${props.course.id}`);
        if (response.data.success) {
            unassignedQuizzes.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to fetch unassigned quizzes:', error);
    } finally {
        isLoadingUnassigned.value = false;
    }
}

function toggleQuizType(type: string) {
    const index = quizTypes.value.indexOf(type);
    if (index > -1) {
        quizTypes.value.splice(index, 1);
    } else {
        quizTypes.value.push(type);
    }
}

function addResource(resourceId: string) {
    if (!selectedResources.value.includes(resourceId)) {
        selectedResources.value.push(resourceId);
    }
    resourceSearchQuery.value = "";
    isResourceDropdownOpen.value = false;
}

function removeSelectedResource(resourceId: string) {
    const index = selectedResources.value.indexOf(resourceId);
    if (index > -1) {
        selectedResources.value.splice(index, 1);
    }
}

async function deleteQuizQuestion(questionId: number) {
    if (!confirm('Are you sure you want to delete this quiz question?')) {
        return;
    }

    try {
        const response = await axios.delete(`/api/quizzes/question/${questionId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        if (response.data.success) {
            // Remove from both lists
            generatedQuizzes.value = generatedQuizzes.value.filter(q => q.id !== questionId);
            unassignedQuizzes.value = unassignedQuizzes.value.filter(q => q.id !== questionId);
        } else {
            alert('Failed to delete quiz question: ' + response.data.message);
        }
    } catch (error) {
        console.error('Error deleting quiz question:', error);
        alert('Failed to delete quiz question. Please try again.');
    }
}

function dismissGeneratedQuizzes() {
    showGeneratedQuizzes.value = false;
    generatedQuizzes.value = [];
}

// Course content check
const courseContentInfo = ref<{
    has_lessons: boolean;
    lesson_count: number;
    has_course_description: boolean;
    can_generate_quizzes: boolean;
} | null>(null);

const isLoadingContentCheck = ref(false);

// Check course content
async function checkCourseContent() {
    isLoadingContentCheck.value = true;
    try {
        const response = await axios.get(`/api/quizzes/check-content/${props.course.id}`);
        if (response.data.success) {
            courseContentInfo.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to check course content:', error);
    } finally {
        isLoadingContentCheck.value = false;
    }
}

// Load data on mount
onMounted(() => {
    checkCourseContent();
    fetchCourseResources();
    fetchUnassignedQuizzes();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Course Info -->
        <div class="p-4 bg-muted rounded-lg">
            <h3 class="font-medium text-lg">{{ selectedCourse.title }}</h3>
            <p class="text-sm text-muted-foreground mt-1">{{ selectedCourse.description }}</p>
            
            <!-- Content Check Status -->
            <div v-if="courseContentInfo" class="mt-3 p-3 rounded-lg" :class="{
                'bg-green-50 border border-green-200': courseContentInfo.can_generate_quizzes,
                'bg-yellow-50 border border-yellow-200': !courseContentInfo.can_generate_quizzes
            }">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full" :class="{
                        'bg-green-500': courseContentInfo.can_generate_quizzes,
                        'bg-yellow-500': !courseContentInfo.can_generate_quizzes
                    }"></div>
                    <span class="text-sm font-medium">
                        {{ courseContentInfo.can_generate_quizzes ? 'Ready for quiz generation' : 'Limited content available' }}
                    </span>
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                    <span v-if="courseContentInfo.has_lessons">
                        {{ courseContentInfo.lesson_count }} lesson{{ courseContentInfo.lesson_count !== 1 ? 's' : '' }} available
                    </span>
                    <span v-else-if="courseContentInfo.has_course_description">
                        Using course description only
                    </span>
                    <span v-else>
                        No content available - please add lessons or course description
                    </span>
                </div>
            </div>
            
            <!-- Loading Content Check -->
            <div v-else-if="isLoadingContentCheck" class="mt-3 p-3 rounded-lg bg-gray-50">
                <div class="flex items-center gap-2">
                    <RefreshCwIcon class="size-4 animate-spin" />
                    <span class="text-sm">Checking course content...</span>
                </div>
            </div>
        </div>

        <!-- Quiz Title -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <BookOpenIcon class="size-5" />
                    Quiz Details
                </CardTitle>
                <CardDescription>
                    Enter a title for your quiz lesson
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div>
                    <Label for="quizTitle">Quiz Title</Label>
                    <Input
                        id="quizTitle"
                        v-model="quizTitle"
                        placeholder="e.g., JavaScript Basics Quiz, Midterm Assessment, etc."
                        class="mt-2"
                        required
                    />
                    <p class="text-xs text-muted-foreground mt-1">
                        This will be the title of the quiz lesson that gets created
                    </p>
                </div>
                
                <div class="mt-4">
                    <Label for="status">Status</Label>
                    <Select v-model:model-value="isPublished">
                        <SelectTrigger class="mt-2">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="false">Draft</SelectItem>
                            <SelectItem :value="true">Published</SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground mt-1">
                        Draft: Only you can see it. Published: Students can take the quiz.
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Quiz Generation Parameters -->
        <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <WandSparklesIcon class="size-5" />
                        Quiz Generation Parameters
                    </CardTitle>
                    <CardDescription>
                        Configure how you want the AI to generate quizzes for this course
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Quiz Types -->
                    <div>
                        <Label class="text-base font-medium">Quiz Types</Label>
                        <p class="text-sm text-muted-foreground mb-3">
                            Select the types of questions you want to generate
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <div 
                                v-for="type in availableQuizTypes" 
                                :key="type.value"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox 
                                    :id="type.value"
                                    :checked="quizTypes.includes(type.value)"
                                    @update:checked="toggleQuizType(type.value)"
                                />
                                <Label :for="type.value" class="text-sm font-normal">
                                    {{ type.label }}
                                </Label>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Quiz Count and Difficulty -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="quizCount">Number of Questions</Label>
                            <Input
                                id="quizCount"
                                type="number"
                                v-model="quizCount"
                                min="1"
                                max="50"
                                class="mt-2"
                            />
                            <p class="text-xs text-muted-foreground mt-1">
                                Between 1 and 50 questions
                            </p>
                        </div>

                        <div>
                            <Label for="difficulty">Difficulty Level</Label>
                            <Select v-model:model-value="difficulty">
                                <SelectTrigger class="mt-2">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="easy">Easy</SelectItem>
                                    <SelectItem value="medium">Medium</SelectItem>
                                    <SelectItem value="hard">Hard</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <Separator />

                    <!-- Reference Resources -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <Label class="text-base font-medium">Reference Resources (Optional)</Label>
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
                        <p class="text-sm text-muted-foreground mb-3">
                            Select specific resources to use as reference material for quiz generation
                        </p>
                        
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

                        <!-- Resource Selection Dropdown -->
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
                        <div v-else-if="!isLoadingResources" class="text-center py-4 text-muted-foreground">
                            <BookOpenIcon class="size-8 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No resources available for this course</p>
                            <p class="text-xs">Upload some resources first to use them as references</p>
                        </div>
                        
                        <!-- Loading State -->
                        <div v-if="isLoadingResources" class="text-center py-4">
                            <RefreshCwIcon class="size-6 mx-auto mb-2 animate-spin" />
                            <p class="text-sm text-muted-foreground">Loading resources...</p>
                        </div>
                    </div>

                    <!-- Generate Button -->
                    <div class="pt-4">
                        <Button
                            @click="generateQuizzes"
                            :disabled="isGenerating || !quizTitle.trim() || quizTypes.length === 0 || (courseContentInfo && !courseContentInfo.can_generate_quizzes)"
                            class="w-full"
                            size="lg"
                        >
                            <WandSparklesIcon class="size-4 mr-2" />
                            {{ isGenerating ? 'Generating Quizzes...' : 'Generate Quizzes' }}
                        </Button>
                        
                        <!-- Warning message when no content -->
                        <div v-if="courseContentInfo && !courseContentInfo.can_generate_quizzes" class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                <strong>Cannot generate quizzes:</strong> This course has no lessons or description. 
                                Please add some content first or upload reference resources.
                            </p>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div v-if="isGenerating" class="mt-4">
                            <div class="flex items-center justify-between text-sm text-muted-foreground mb-2">
                                <span>Generating quizzes...</span>
                                <span>{{ Math.round(generationProgress) }}%</span>
                            </div>
                            <Progress :value="generationProgress" class="w-full" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Generated Quizzes Preview -->
            <Card v-if="showGeneratedQuizzes && generatedQuizzes.length > 0">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <CheckIcon class="size-5 text-green-500" />
                                Generated Quizzes ({{ generatedQuizzes.length }})
                            </CardTitle>
                            <CardDescription>
                                Quiz lesson created successfully! The questions are now ready for students to take.
                            </CardDescription>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="dismissGeneratedQuizzes"
                        >
                            <XIcon class="size-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <div 
                            v-for="(quiz, index) in generatedQuizzes" 
                            :key="quiz.id"
                            class="border rounded-lg p-4 space-y-3"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <Badge variant="outline">{{ index + 1 }}</Badge>
                                        <Badge variant="secondary">{{ quiz.type.replace('_', ' ') }}</Badge>
                                        <Badge 
                                            :variant="quiz.metadata.difficulty === 'easy' ? 'default' : 
                                                     quiz.metadata.difficulty === 'medium' ? 'secondary' : 'destructive'"
                                        >
                                            {{ quiz.metadata.difficulty }}
                                        </Badge>
                                    </div>
                                    <p class="font-medium">{{ quiz.question }}</p>
                                    
                                    <!-- Options for multiple choice/select -->
                                    <div v-if="quiz.options && quiz.options.length > 0" class="mt-2 space-y-1">
                                        <div 
                                            v-for="option in quiz.options" 
                                            :key="option.id"
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <span 
                                                :class="option.is_correct ? 'text-green-600 font-medium' : 'text-muted-foreground'"
                                            >
                                                {{ option.option_text }}
                                            </span>
                                            <CheckIcon 
                                                v-if="option.is_correct" 
                                                class="size-3 text-green-600" 
                                            />
                                        </div>
                                    </div>
                                    
                                    <!-- Explanation -->
                                    <div v-if="quiz.metadata.explanation" class="mt-2 p-2 bg-muted rounded text-sm">
                                        <strong>Explanation:</strong> {{ quiz.metadata.explanation }}
                                    </div>
                                </div>
                                
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="deleteQuizQuestion(quiz.id)"
                                    class="text-destructive hover:text-destructive"
                                >
                                    <TrashIcon class="size-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Unassigned Quizzes Management -->
            <Card v-if="unassignedQuizzes.length > 0">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <EyeIcon class="size-5" />
                                Unassigned Quizzes ({{ unassignedQuizzes.length }})
                            </CardTitle>
                            <CardDescription>
                                Previously generated quizzes that haven't been assigned to lessons yet
                            </CardDescription>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="fetchUnassignedQuizzes"
                            :disabled="isLoadingUnassigned"
                        >
                            <RefreshCwIcon class="size-4" :class="{ 'animate-spin': isLoadingUnassigned }" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <div 
                            v-for="quiz in unassignedQuizzes" 
                            :key="quiz.id"
                            class="flex items-center justify-between p-3 border rounded-lg"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <Badge variant="secondary">{{ quiz.type.replace('_', ' ') }}</Badge>
                                    <Badge 
                                        :variant="quiz.metadata.difficulty === 'easy' ? 'default' : 
                                                 quiz.metadata.difficulty === 'medium' ? 'secondary' : 'destructive'"
                                    >
                                        {{ quiz.metadata.difficulty }}
                                    </Badge>
                                </div>
                                <p class="text-sm font-medium">{{ quiz.question }}</p>
                            </div>
                            
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="deleteQuizQuestion(quiz.id)"
                                class="text-destructive hover:text-destructive"
                            >
                                <TrashIcon class="size-4" />
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
    </div>
</template>
