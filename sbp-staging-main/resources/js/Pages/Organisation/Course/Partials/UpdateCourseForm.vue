<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { RichEditor } from "@/Components/RichEditor";
import UploadMediaForm from "@/Components/UploadMediaForm.vue";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { Course } from "@/types";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { toast } from "vue-sonner";

const props = defineProps<{
    course: Course;
}>();
const page = usePage();
const isBannerPopupOpen = ref(false);

const form = useForm({
    title: props.course.title ?? "",
    description: props.course.description ?? "",
    banner_image: props.course.banner_image ?? "",
    is_published: props.course.is_published
        ? props.course.is_published + ""
        : "false",
});

function submit() {
    form.patch(route("course.update", { course: props.course.id }), {
        onSuccess() {},
        onError(error) {},
        preserveScroll: true,
    });
}

watch(
    () => page.props.flash["message"],
    (flash) => {
        if (!flash) return;

        const message = flash.message;
        const action = flash.action;
        const type = flash.status;

        const toastOptions: any = {
            ...(action && {}),
        };

        switch (type) {
            case "success":
                toast.success(message, { ...toastOptions });
                break;

            case "error":
                toast.error(message, { ...toastOptions });
                break;

            default:
                toast(message, { ...toastOptions });
                break;
        }
    },
);
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Course information</h2>

            <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your organisation is setup, you can invite other members to
                join you!
            </p> -->
        </header>

        <form @submit.prevent="submit" class="mt-6">
            <div class="space-y-6">
                <div>
                    <Label for="banner_image">Banner Image</Label>
                    <div>
                        <Popover
                            :open="isBannerPopupOpen"
                            @update:open="(v) => (isBannerPopupOpen = v)"
                        >
                            <PopoverTrigger>
                                <div
                                    class="mt-2 size-20 rounded-md bg-gray-400 bg-cover"
                                    :style="{
                                        backgroundImage: form.banner_image
                                            ? `url(${form.banner_image})`
                                            : 'unset',
                                    }"
                                ></div>
                            </PopoverTrigger>
                            <PopoverContent class="w-fit">
                                <div class="">
                                    <UploadMediaForm
                                        :onUpload="
                                            (d) => {
                                                form.banner_image = d[0];
                                                isBannerPopupOpen = false;
                                            }
                                        "
                                    />
                                </div>
                            </PopoverContent>
                        </Popover>
                    </div>
                </div>

                <div>
                    <Label for="title">Title</Label>
                    <Input
                        id="title"
                        placeholder="Enter the title of your course"
                        v-model="form.title"
                        class="mt-2"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
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

                <div>
                    <Label for="description">Description</Label>
                    <RichEditor
                        v-model="form.description"
                        id="description"
                        placeholder="Enter the description of your course"
                        class="mt-2"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.description"
                    />
                </div>

                <div>
                    <Button
                        type="submit"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Save
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>
