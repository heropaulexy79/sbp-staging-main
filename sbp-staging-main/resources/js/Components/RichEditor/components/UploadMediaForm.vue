<script lang="ts" setup>
import { ref, computed } from "vue";
import { Tabs, TabsList, TabsTrigger, TabsContent } from "@/Components/ui/tabs";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { ImagesIcon, XIcon } from "lucide-vue-next";
import { useDropZone } from "@vueuse/core";
import { useObjectURLs } from "@/lib/utils";

const props = defineProps<{
    onUpload: (urls: string[]) => void;
}>();

const linkForm = ref({
    url: "",
});
const pendingFiles = ref<File[]>([]);
const uploadedFiles = ref<{ url: string; name: string }[]>([]);
const dropZoneRef = ref<HTMLElement>();
const getObjectUrls = useObjectURLs();

const displayPendingFiles = computed(() => {
    return pendingFiles.value.filter(
        (file) =>
            !uploadedFiles.value.some(
                (uploadedFile) => uploadedFile.name === file.name,
            ),
    );
});

async function onDrop(files: File[] | null) {
    // called when files are dropped on zone
    if (!files) return;

    pendingFiles.value.push(...files);
    await handleUploadFiles(files);
}

const acceptedFiles = ["image/jpeg", "image/png", "image/webp", "image/gif"];

const { isOverDropZone } = useDropZone(dropZoneRef, {
    onDrop,

    dataTypes: acceptedFiles,
});

async function handleChangeFile(event: Event) {
    try {
        const target = event.target as HTMLInputElement;
        const files = target.files ? Array.from(target.files) : null;

        if (!files) return;

        pendingFiles.value.push(...files);
        await handleUploadFiles(files);
    } catch (error) {
        console.log(error);
    }
}

async function handleUploadFiles(files: File[]) {
    const ufiles = await uploadFiles(files);
    const ufiless = ufiles.map((r) => r?.data).filter(Boolean) as {
        url: string;
        name: string;
    }[];
    uploadedFiles.value.push(...ufiless);
}

async function uploadFile(file: File) {
    const formData = new FormData();
    formData.append("file", file);

    const response = await window.axios.post<{
        url: string;
        name: string;
    }>(route("upload"), formData);

    return response;
}

async function uploadFiles(files: File[]) {
    return Promise.all(files.map((file) => uploadFile(file)));
}

async function deleteFile(file: { url: string; name: string }) {
    // We remove it from the pending files in the onLoad below
    // But if they delete it before it loads we need to remove it here
    // Otherwise they'll see the pending image for a second
    const pendingFile = pendingFiles.value.find((f) => f.name === file.name);

    pendingFiles.value = pendingFiles.value.filter((p) => {
        return p !== pendingFile;
    });

    const response = await window.axios.delete<{
        message: {
            status: string;
            message: string;
        };
    }>(route("upload.delete", { url: file.url }), {
        data: {
            url: file.url,
        },
    });

    if (response.data.message.status !== "success") {
        //
        console.log(response.data.message.message);
        return;
    }

    uploadedFiles.value = uploadedFiles.value.filter((f) => f.url !== file.url);
}

function submitLink() {
    // Todo: Zod verify if url
    if (!linkForm.value.url) return;
    props.onUpload([linkForm.value.url]);
}

function submitUploaded() {
    // props.onUpload(pendingFiles.value.map((v) => getObjectUrls(v)));
    props.onUpload(uploadedFiles.value.map((v) => v.url));
}
</script>

<template>
    <!--  -->

    <Tabs default-value="url">
        <div class="mx-auto w-fit">
            <TabsList class="h-9 justify-center">
                <TabsTrigger value="url">Upload from URL</TabsTrigger>
                <TabsTrigger value="device">Upload from device</TabsTrigger>
            </TabsList>
        </div>
        <TabsContent value="url">
            <form @submit.prevent="submitLink" class="mt-6">
                <div>
                    <Label for="url" hidden>Url</Label>
                    <Input
                        name="url"
                        id="url"
                        v-model="linkForm.url"
                        placeholder="https://example.com/image.png"
                    />
                </div>
                <div className="flex items-center justify-end mt-4">
                    <Button type="submit" :disabled="!linkForm.url">
                        Upload
                    </Button>
                </div>
            </form>
        </TabsContent>
        <TabsContent value="device">
            <div class="mt-4">
                <div>
                    <label
                        ref="dropZoneRef"
                        htmlFor="image-input"
                        class="block"
                    >
                        <div
                            class="grid cursor-pointer place-items-center rounded-md border border-dashed border-input px-4 py-12 text-muted-foreground transition-colors hover:border-ring hover:bg-background/5 hover:text-primary"
                        >
                            <ImagesIcon class="h-8 w-8" />
                            <span> Drop image here </span>
                        </div>

                        <input
                            multiple
                            id="image-input"
                            name="file"
                            :accept="acceptedFiles.join(',')"
                            type="file"
                            class="sr-only block"
                            @change="handleChangeFile"
                        />
                    </label>
                </div>

                <div class="flex flex-row flex-wrap gap-2">
                    <div
                        v-for="file in uploadedFiles"
                        class="group relative mt-2"
                    >
                        <div
                            class="size-20 overflow-hidden rounded-lg border border-neutral-100"
                        >
                            <img
                                :src="file.url"
                                :alt="file.name"
                                class="size-full object-cover"
                            />
                        </div>
                        <!-- variant="" -->
                        <Button
                            type="button"
                            size="icon"
                            class="absolute -right-[0.625rem] -top-[0.125rem] hidden rounded-full group-hover:inline-flex"
                            @click="deleteFile(file)"
                        >
                            <XIcon className="block h-6 w-6" />
                        </Button>
                    </div>

                    <div
                        v-for="file in displayPendingFiles"
                        :key="file.name"
                        class="relative mt-2 size-20 overflow-hidden rounded-lg border border-neutral-100"
                    >
                        <img
                            :src="getObjectUrls(file)"
                            :alt="file.name"
                            class="size-full object-cover opacity-50"
                        />
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-end mt-4">
                <Button
                    type="button"
                    @click="submitUploaded"
                    :disabled="uploadedFiles.length < 1"
                >
                    Upload
                </Button>
            </div>
        </TabsContent>
    </Tabs>
</template>
