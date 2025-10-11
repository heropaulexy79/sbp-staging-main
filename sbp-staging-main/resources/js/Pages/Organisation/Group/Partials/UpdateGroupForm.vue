<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import { Group, User } from "@/types";

const props = defineProps<{
    group: Group;
    users: User[];
}>();

const selectedUserIds = ref<number[]>([]);

const form = useForm({
    name: props.group.name,
    user_ids: [] as number[],
});

// Initialize selected users
onMounted(() => {
    selectedUserIds.value = props.group.users.map(user => user.id);
    form.user_ids = selectedUserIds.value;
});

function updateGroup() {
    form.user_ids = selectedUserIds.value;
    form.patch(route("group.update", { group: props.group.id }), {
        onSuccess() {
            // Handle success
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
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Edit Group</h2>
        </header>

        <form @submit.prevent="updateGroup">
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
                    <div class="mt-2 space-y-2 max-h-48 overflow-y-auto border rounded-md p-3">
                        <div v-if="users.length === 0" class="text-sm text-gray-500">
                            No users available
                        </div>
                        <div v-else>
                            <div
                                v-for="user in users"
                                :key="user.id"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    type="checkbox"
                                    :id="`user-${user.id}`"
                                    :value="user.id"
                                    :checked="selectedUserIds.includes(user.id)"
                                    @change="toggleUser(user.id)"
                                    class="rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <label
                                    :for="`user-${user.id}`"
                                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </label>
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
                        Update Group
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>
