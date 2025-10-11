<script setup lang="ts">
import InputError from "@/Components/InputError.vue";
import { RichEditor } from "@/Components/RichEditor";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import { slugify } from "@/lib/utils";
import { useForm } from "@inertiajs/vue3";
import UploadMediaForm from "@/Components/UploadMediaForm.vue";
import { ref } from "vue";

const isBannerPopupOpen = ref(false);

const form = useForm({
    title: "",
    description: "",
    slug: "",
    banner_image: "",
});

function createCourse() {
    form.transform((d) => {
        return {
            ...d,
            slug: slugify(d.title),
        };
    }).post(route("course.store"), {
        onSuccess() {},
        onError(error) {
            console.log(error);
        },
    });
}
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium">Create course</h2>

            <!-- <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Once your organisation is setup, you can invite other members to
                join you!
            </p> -->
        </header>

        <form @submit.prevent="createCourse">
            <div class="space-y-6">
                <div>
                    <Label for="banner_image">Banner Image</Label>
                    <div>
                        <Popover
                            :open="isBannerPopupOpen"
                            @update:open="(v) => (isBannerPopupOpen = v)"
                        >
                            <PopoverTrigger>
                                <!-- <Button type="button" variant="secondary">
                                    Banner Image
                                </Button> -->

                                <div
                                    class="mt-2 size-20 rounded-md bg-gray-400 bg-cover"
                                    :style="{
                                        backgroundImage: form.banner_image
                                            ? `url(${form.banner_image})`
                                            : 'unset',
                                    }"
                                ></div>
                            </PopoverTrigger>
                            <!-- class="max-h-[90dvh] w-fit grid-rows-[auto_minmax(0,1fr)_auto]" -->
                            <PopoverContent class="w-fit">
                                <!-- <div class="mb-6">
                                    <h3 class="font-medium leading-none">
                                        Upload Image
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Upload image from your computer or from
                                        a link
                                    </p>
                                </div> -->
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
                        autofocus
                        class="mt-2"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                    <InputError class="mt-2" :message="form.errors.slug" />
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
                        Create
                    </Button>
                </div>
            </div>
        </form>
    </section>
</template>
