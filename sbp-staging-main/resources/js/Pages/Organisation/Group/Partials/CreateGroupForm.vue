<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted } from "vue";
import { User } from "@/types";
import { X, ChevronDown, Search } from "lucide-vue-next";

const props = defineProps<{
    organisation_id: number;
    users: User[];
}>();

const selectedUserIds = ref<number[]>([]);
const searchQuery = ref("");
const isDropdownOpen = ref(false);

const form = useForm({
    name: "",
    user_ids: [] as number[],
});

// Filter users based on search query
const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    return props.users.filter(user => 
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        user.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Get selected users for display
const selectedUsers = computed(() => {
    return props.users.filter(user => selectedUserIds.value.includes(user.id));
});

function createGroup() {
    form.user_ids = selectedUserIds.value;
    form.post(route("group.store"), {
        onSuccess() {
            form.reset();
            selectedUserIds.value = [];
            searchQuery.value = "";
            isDropdownOpen.value = false;
        },
        onError(error) {
            console.log(error);
        },
    });
}

function toggleUser(userId: number) {
    const index = selectedUserIds.value.indexOf(userId);
    if (index > -1) {
        selectedUserIds.value.splice(index, 1);
    } else {
        selectedUserIds.value.push(userId);
    }
}

function removeUser(userId: number) {
    const index = selectedUserIds.value.indexOf(userId);
    if (index > -1) {
        selectedUserIds.value.splice(index, 1);
    }
}

function toggleDropdown() {
    isDropdownOpen.value = !isDropdownOpen.value;
    if (!isDropdownOpen.value) {
        searchQuery.value = "";
    }
}

function selectUser(user: User) {
    toggleUser(user.id);
    searchQuery.value = "";
}

function selectAllUsers() {
    // Add all filtered users to selection (don't replace existing selections)
    const newSelections = filteredUsers.value
        .filter(user => !selectedUserIds.value.includes(user.id))
        .map(user => user.id);
    selectedUserIds.value = [...selectedUserIds.value, ...newSelections];
}

function selectAllAvailableUsers() {
    // Select all users from the main users list
    selectedUserIds.value = props.users.map(user => user.id);
}

function clearAllUsers() {
    selectedUserIds.value = [];
}

// Check if all filtered users are selected
const allFilteredUsersSelected = computed(() => {
    if (filteredUsers.value.length === 0) return false;
    return filteredUsers.value.every(user => selectedUserIds.value.includes(user.id));
});

// Check if some (but not all) filtered users are selected
const someFilteredUsersSelected = computed(() => {
    if (filteredUsers.value.length === 0) return false;
    const selectedCount = filteredUsers.value.filter(user => selectedUserIds.value.includes(user.id)).length;
    return selectedCount > 0 && selectedCount < filteredUsers.value.length;
});

// Click outside handler
function handleClickOutside(event: Event) {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
        isDropdownOpen.value = false;
        searchQuery.value = "";
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Create Group</h2>
        </header>

        <form @submit.prevent="createGroup">
            <div class="space-y-6">
                <div>
                    <Label for="name">Group Name</Label>
                    <Input
                        id="name"
                        placeholder="Enter the name of your group"
                        v-model="form.name"
                        autofocus
                        class="mt-2"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Label for="users">Select Members</Label>
                    <div class="mt-2 relative dropdown-container">
                        <!-- Selected Users Display -->
                        <div v-if="selectedUsers.length > 0" class="mb-2 flex flex-wrap gap-2">
                            <div
                                v-for="user in selectedUsers"
                                :key="user.id"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-primary/10 text-primary rounded-md text-sm"
                            >
                                <span>{{ user.name }}</span>
                                <button
                                    type="button"
                                    @click="removeUser(user.id)"
                                    class="hover:bg-primary/20 rounded-full p-0.5"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div v-if="users.length > 5 && selectedUsers.length === 0" class="mb-2 flex gap-2">
                            <button
                                type="button"
                                @click="selectAllAvailableUsers"
                                class="text-xs px-2 py-1 bg-primary/10 text-primary rounded hover:bg-primary/20 transition-colors"
                            >
                                Select All Users
                            </button>
                        </div>

                        <!-- Dropdown Trigger -->
                        <div class="relative">
                            <button
                                type="button"
                                @click="toggleDropdown"
                                class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-left focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                                <span class="text-gray-500">
                                    {{ selectedUsers.length > 0 ? `${selectedUsers.length} user(s) selected` : 'Select users...' }}
                                </span>
                                <ChevronDown 
                                    class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': isDropdownOpen }"
                                />
                            </button>

                            <!-- Dropdown Content -->
                            <div
                                v-if="isDropdownOpen"
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-hidden"
                            >
                                <!-- Search Input -->
                                <div class="p-2 border-b border-gray-200">
                                    <div class="relative">
                                        <Search class="absolute left-2 top-2.5 h-4 w-4 text-gray-400" />
                                        <Input
                                            v-model="searchQuery"
                                            placeholder="Search users..."
                                            class="pl-8"
                                            @click.stop
                                        />
                                    </div>
                                    
                                    <!-- Select All / Clear All Buttons -->
                                    <div v-if="filteredUsers.length > 0" class="mt-2 flex gap-2">
                                        <button
                                            type="button"
                                            @click="selectAllUsers"
                                            class="text-xs px-2 py-1 bg-primary/10 text-primary rounded hover:bg-primary/20 transition-colors"
                                            :disabled="allFilteredUsersSelected"
                                        >
                                            {{ allFilteredUsersSelected ? 'All Selected' : 'Select All' }}
                                        </button>
                                        <button
                                            v-if="selectedUserIds.length > 0"
                                            type="button"
                                            @click="clearAllUsers"
                                            class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition-colors"
                                        >
                                            Clear All
                                        </button>
                                    </div>
                                </div>

                                <!-- Users List -->
                                <div class="max-h-48 overflow-y-auto">
                                    <div v-if="filteredUsers.length === 0" class="p-3 text-sm text-gray-500">
                                        {{ searchQuery ? 'No users found' : 'No users available' }}
                                    </div>
                                    <div v-else>
                                        <button
                                            v-for="user in filteredUsers"
                                            :key="user.id"
                                            type="button"
                                            @click="selectUser(user)"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                                            :class="{ 'bg-primary/10 text-primary': selectedUserIds.includes(user.id) }"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="font-medium">{{ user.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                                </div>
                                                <div v-if="selectedUserIds.includes(user.id)" class="text-primary">
                                                    ✓
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.user_ids" />
                </div>

                <div>
                    <Button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Create Group
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>
