<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { UploadIcon, FileTextIcon, LoaderIcon } from 'lucide-vue-next';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';

const props = defineProps<{
    courses: Array<{
        id: number;
        title: string;
    }>;
}>();

const emit = defineEmits<{
    success: [];
}>();

// Form state
const form = useForm({
    title: '',
    course_id: null as number | null,
    file: null as File | null,
    text: '',
});

// UI state
const isUploading = ref(false);
const uploadProgress = ref(0);
const selectedFile = ref<File | null>(null);
const inputType = ref<'file' | 'text'>('file');

// Computed
const hasContent = computed(() => {
    return (selectedFile.value !== null) || (form.text.trim().length > 0);
});

const canSubmit = computed(() => {
    return form.title.trim().length > 0 && 
           form.course_id !== null && 
           hasContent.value && 
           !isUploading.value;
});

// Methods
function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedFile.value = target.files[0];
        form.file = target.files[0];
        
        // Auto-fill title if not set
        if (!form.title) {
            form.title = selectedFile.value.name.replace(/\.[^/.]+$/, ''); // Remove extension
        }
    }
}

function clearFile() {
    selectedFile.value = null;
    form.file = null;
}

function switchInputType(type: 'file' | 'text') {
    inputType.value = type;
    if (type === 'file') {
        form.text = '';
    } else {
        clearFile();
    }
}

async function submitForm() {
    if (!canSubmit.value) return;

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const formData = new FormData();
        formData.append('title', form.title);
        formData.append('course_id', form.course_id!.toString());

        if (inputType.value === 'file' && selectedFile.value) {
            formData.append('file', selectedFile.value);
        } else if (inputType.value === 'text' && form.text.trim()) {
            formData.append('text', form.text);
        }

        const response = await axios.post('/api/resources/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            },
        });

        if (response.data.success) {
            // Reset form
            form.reset();
            selectedFile.value = null;
            inputType.value = 'file';
            uploadProgress.value = 0;
            
            // Emit success event
            emit('success');
            
            // Show success message (you can add a toast notification here)
            alert(`Resource "${form.title}" uploaded and processed successfully!`);
        } else {
            throw new Error(response.data.message || 'Upload failed');
        }

    } catch (error: any) {
        console.error('Upload error:', error);
        
        let errorMessage = 'Failed to upload resource';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        alert(errorMessage);
    } finally {
        isUploading.value = false;
        uploadProgress.value = 0;
    }
}

function formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<template>
    <div class="max-h-[80vh] overflow-y-auto pr-2">
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Upload Resource
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Upload a document or paste text to create searchable content for your courses.
                </p>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Title -->
            <div>
                <Label for="title">Title *</Label>
                <Input
                    id="title"
                    v-model="form.title"
                    placeholder="Enter a title for this resource"
                    class="mt-2"
                    :disabled="isUploading"
                />
                <InputError class="mt-2" :message="form.errors.title" />
            </div>

            <!-- Course Selection -->
            <div>
                <Label for="course_id">Course *</Label>
                <Select v-model:model-value="form.course_id" :disabled="isUploading">
                    <SelectTrigger class="mt-2">
                        <SelectValue placeholder="Select a course" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem 
                            v-for="course in courses" 
                            :key="course.id" 
                            :value="course.id"
                        >
                            {{ course.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError class="mt-2" :message="form.errors.course_id" />
            </div>

            <!-- Input Type Toggle -->
            <div>
                <Label>Content Source</Label>
                <div class="mt-2 flex space-x-4">
                    <Button
                        type="button"
                        variant="outline"
                        :class="{ 'bg-primary text-primary-foreground': inputType === 'file' }"
                        @click="switchInputType('file')"
                        :disabled="isUploading"
                    >
                        <UploadIcon class="size-4 mr-2" />
                        Upload File
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        :class="{ 'bg-primary text-primary-foreground': inputType === 'text' }"
                        @click="switchInputType('text')"
                        :disabled="isUploading"
                    >
                        <FileTextIcon class="size-4 mr-2" />
                        Paste Text
                    </Button>
                </div>
            </div>

            <!-- File Upload -->
            <div v-if="inputType === 'file'">
                <Label for="file">Document *</Label>
                <div class="mt-2">
                    <Input
                        id="file"
                        type="file"
                        accept=".pdf,.docx,.txt"
                        @change="handleFileSelect"
                        :disabled="isUploading"
                        class="cursor-pointer"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        Supported formats: PDF, DOCX, TXT (Max 10MB)
                    </p>
                </div>
                
                <!-- Selected File Display -->
                <div v-if="selectedFile" class="mt-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <FileTextIcon class="size-4 text-gray-500" />
                            <span class="text-sm font-medium">{{ selectedFile.name }}</span>
                            <span class="text-xs text-gray-500">({{ formatFileSize(selectedFile.size) }})</span>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="clearFile"
                            :disabled="isUploading"
                        >
                            Remove
                        </Button>
                    </div>
                </div>
                
                <InputError class="mt-2" :message="form.errors.file" />
            </div>

            <!-- Text Input -->
            <div v-if="inputType === 'text'">
                <Label for="text">Text Content *</Label>
                <Textarea
                    id="text"
                    v-model="form.text"
                    placeholder="Paste or type your text content here..."
                    class="mt-2 min-h-[200px]"
                    :disabled="isUploading"
                />
                <p class="text-xs text-gray-500 mt-1">
                    {{ form.text.length }} characters
                </p>
                <InputError class="mt-2" :message="form.errors.text" />
            </div>

            <!-- Upload Progress -->
            <div v-if="isUploading" class="space-y-2">
                <div class="flex items-center space-x-2">
                    <LoaderIcon class="size-4 animate-spin" />
                    <span class="text-sm">Processing resource...</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="bg-primary h-2 rounded-full transition-all duration-300"
                        :style="{ width: uploadProgress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <Button
                    type="submit"
                    :disabled="!canSubmit"
                    :class="{ 'opacity-50 cursor-not-allowed': !canSubmit }"
                >
                    <UploadIcon class="size-4 mr-2" />
                    {{ isUploading ? 'Processing...' : 'Upload Resource' }}
                </Button>
            </div>
            </form>
        </div>
    </div>
</template>

