<script lang="ts" setup>
import { Course, OrganisationUser, User, Group } from "@/types";
import axios from "axios";
import { computed, onMounted, ref } from "vue";
import {
    ComboboxAnchor,
    ComboboxInput,
    ComboboxPortal,
    ComboboxRoot,
} from "radix-vue";
import {
    CommandEmpty,
    CommandGroup,
    CommandItem,
    CommandList,
} from "@/Components/ui/command";
import {
    TagsInput,
    TagsInputInput,
    TagsInputItem,
    TagsInputItemDelete,
    TagsInputItemText,
} from "@/Components/ui/tags-input";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Button } from "@/Components/ui/button";
import { useForm } from "@inertiajs/vue3";
import { getPublicProfileImage } from "@/lib/utils";
import { Users } from "lucide-vue-next";

type Student = OrganisationUser;

const props = defineProps<{
    course: Course;
    onSuccess: () => void;
}>();

const loading = ref(false);
const students = ref<Student[]>([]);
const groups = ref<Group[]>([]);
const error = ref(null);
const modelValue = ref<string[]>([]);
const open = ref(false);
const searchTerm = ref("");
// Create a unified list of selectable items (students and groups)
const selectableItems = computed(() => {
    const items: Array<{
        id: string;
        type: 'student' | 'group';
        name: string;
        subtitle?: string;
        data: Student | Group;
    }> = [];

    // Add students
    students.value?.forEach(student => {
        items.push({
            id: `student_${student.user_id}`,
            type: 'student',
            name: student.user.name,
            subtitle: student.user.email,
            data: student
        });
    });

    // Add groups
    groups.value?.forEach(group => {
        items.push({
            id: `group_${group.id}`,
            type: 'group',
            name: group.name,
            subtitle: `${group.users_count} members`,
            data: group
        });
    });

    return items;
});

// Get selected items for display
const selectedItems = computed(() =>
    selectableItems.value?.filter((item) => modelValue.value.includes(item.id))
);

// Get filtered items (not selected) for dropdown
const filteredItems = computed(() =>
    selectableItems.value?.filter((item) => !modelValue.value.includes(item.id))
);

const wrapperRef = ref<HTMLElement>();

const form = useForm({
    // students: [] as string[],
});

async function fetchStudents() {
    error.value = null;
    students.value = [];
    loading.value = true;

    try {
        // replace `getPost` with your data fetching util / API wrapper
        students.value = (
            await axios.get(route("organisation.employees"))
        ).data.students;
    } catch (err: any) {
        error.value = err.toString();
    } finally {
        loading.value = false;
    }
}

async function fetchGroups() {
    try {
        groups.value = (
            await axios.get(route("group.api"))
        ).data.groups;
    } catch (err: any) {
        console.error('Failed to fetch groups:', err);
        // Don't set error for groups as it's optional
    }
}

function enrollStudents() {
    // Separate students and groups from the selected items
    const studentIds = modelValue.value
        .filter(id => id.startsWith('student_'))
        .map(id => id.replace('student_', ''));
    
    const groupIds = modelValue.value
        .filter(id => id.startsWith('group_'))
        .map(id => id.replace('group_', ''));

    // If we have students, enroll them
    if (studentIds.length > 0) {
        form.transform(() => ({
            students: studentIds,
        })).post(route("course.enroll", { course: props.course.slug }), {
            onSuccess() {
                // If we also have groups, enroll them after students
                if (groupIds.length > 0) {
                    enrollGroups(groupIds);
                } else {
                    props.onSuccess?.();
                }
            },
        });
    } else if (groupIds.length > 0) {
        // Only groups selected
        enrollGroups(groupIds);
    }
}

function enrollGroups(groupIds: string[]) {
    form.transform(() => ({
        groups: groupIds,
    })).post(route("course.enroll.groups", { course: props.course.slug }), {
        onSuccess() {
            props.onSuccess?.();
        },
    });
}

onMounted(async () => {
    await Promise.all([
        fetchStudents(),
        fetchGroups()
    ]);
});
</script>

<template>
    <span v-if="error" class="my-5 text-destructive">{{ error }}</span>
    <div ref="wrapperRef">
        <form @submit.prevent="enrollStudents">
            <TagsInput class="gap-0 px-0" :model-value="modelValue">
                <div class="flex flex-wrap items-center gap-2 px-3">
                    <TagsInputItem
                        v-for="item in selectedItems"
                        :key="item.id"
                        :value="item.id"
                        class="h-7"
                    >
                        <TagsInputItemText class="sr-only" />
                        <span class="py-1 pl-2 leading-none">
                            <!-- Student Avatar -->
                            <Avatar v-if="item.type === 'student'" class="size-6 border">
                                <AvatarImage
                                    :src="
                                        getPublicProfileImage((item.data as Student).user.email)
                                    "
                                    class="leading-none"
                                />
                                <AvatarFallback>{{
                                    item.name[0]
                                }}</AvatarFallback>
                            </Avatar>
                            <!-- Group Icon -->
                            <div v-else class="size-6 border rounded-full bg-blue-100 flex items-center justify-center">
                                <Users class="h-3 w-3 text-blue-600" />
                            </div>
                        </span>
                        <span class="rounded bg-transparent px-2 py-1 text-sm">
                            {{ item.name }}
                            <span v-if="item.subtitle" class="text-xs text-gray-500 ml-1">
                                ({{ item.subtitle }})
                            </span>
                        </span>
                        <TagsInputItemDelete />
                    </TagsInputItem>
                </div>

                <ComboboxRoot
                    v-model="modelValue"
                    v-model:open="open"
                    v-model:searchTerm="searchTerm"
                    class="relative w-full"
                >
                    <ComboboxAnchor as-child>
                        <ComboboxInput placeholder="Students and Groups..." as-child>
                            <TagsInputInput
                                class="w-full border-none px-3 outline-none ring-0 focus-visible:ring-0"
                                :class="modelValue.length > 0 ? 'mt-2' : ''"
                                @keydown.enter.prevent
                            />
                        </ComboboxInput>
                    </ComboboxAnchor>

                    <ComboboxPortal :to="wrapperRef">
                        <CommandList
                            position="popper"
                            class="mt-2 w-[--radix-popper-anchor-width] max-h-60 rounded-md border bg-popover text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 overflow-y-auto"
                            dismissable
                        >
                            <CommandEmpty> No Students or Groups </CommandEmpty>
                            <CommandGroup>
                                <CommandItem disabled value="" v-if="loading">
                                    Loading...
                                </CommandItem>
                                <CommandItem
                                    v-for="item in filteredItems"
                                    :key="item.id"
                                    :value="item.name"
                                    @select.prevent="
                                        (ev) => {
                                            modelValue.push(item.id);

                                            if (filteredItems.length === 0) {
                                                open = false;
                                            }
                                        }
                                    "
                                    class="flex items-center gap-2"
                                >
                                    <!-- Student Avatar -->
                                    <Avatar v-if="item.type === 'student'" class="size-6">
                                        <AvatarImage
                                            :src="
                                                getPublicProfileImage(
                                                    (item.data as Student).user.email,
                                                )
                                            "
                                        />
                                        <AvatarFallback>{{
                                            item.name[0]
                                        }}</AvatarFallback>
                                    </Avatar>
                                    <!-- Group Icon -->
                                    <div v-else class="size-6 border rounded-full bg-blue-100 flex items-center justify-center">
                                        <Users class="h-3 w-3 text-blue-600" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span>{{ item.name }}</span>
                                        <span v-if="item.subtitle" class="text-xs text-gray-500">
                                            {{ item.subtitle }}
                                        </span>
                                    </div>
                                </CommandItem>
                            </CommandGroup>
                        </CommandList>
                    </ComboboxPortal>
                </ComboboxRoot>
            </TagsInput>

            <Button
                type="submit"
                class="mt-4 w-full"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Enroll selected ({{ modelValue.length }})
            </Button>
        </form>
    </div>
</template>
