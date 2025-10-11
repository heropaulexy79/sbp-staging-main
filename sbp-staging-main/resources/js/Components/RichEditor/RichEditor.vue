<script lang="ts" setup>
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import Image from "@tiptap/extension-image";
import Link from "@tiptap/extension-link";
import { Placeholder } from "@tiptap/extension-placeholder";
import StarterKit from "@tiptap/starter-kit";
import { EditorContent, useEditor, type Content } from "@tiptap/vue-3";
import AutoJoiner from "tiptap-extension-auto-joiner";
import GlobalDragHandle from "tiptap-extension-global-drag-handle";
import { useAttrs, watch } from "vue";
import UploadMediaForm from "./components/UploadMediaForm.vue";
import {
    EditorCommandMenu,
    editorSuggestions,
} from "./slash-menu/editor-suggestions";
import { useEditorStore } from "./use-editor-store";

const attrs = useAttrs();

const props = withDefaults(
    defineProps<{
        id?: string;
        class?: string;
        disabled?: boolean;
        placeholder?: string;
    }>(),
    {
        disabled: false,
        // placeholder: "",
    },
);

const modelValue = defineModel<Content>();

const editor = useEditor({
    extensions: [
        StarterKit,
        Placeholder.configure({
            placeholder:
                props.placeholder ??
                "Write something or press / for commands...",
            emptyEditorClass: "is-editor-empty",
        }),
        // BubbleMenu.con,
        EditorCommandMenu.configure({
            // @ts-ignore
            suggestion: editorSuggestions,
        }),
        Link.configure({
            protocols: ["https", "mailto", "tel"],
        }),
        Image.configure({
            //   HTMLAttributes: {
            //     class: 'my-custom-class',
            //   },
        }),
        GlobalDragHandle.configure({
            dragHandleWidth: 20, // default

            // The scrollTreshold specifies how close the user must drag an element to the edge of the lower/upper screen for automatic
            // scrolling to take place. For example, scrollTreshold = 100 means that scrolling starts automatically when the user drags an
            // element to a position that is max. 99px away from the edge of the screen
            // You can set this to 0 to prevent auto scrolling caused by this extension
            scrollTreshold: 100, // default
        }),
        AutoJoiner.configure({
            elementsToJoin: ["bulletList", "orderedList"], // default
        }),
    ],
    content: modelValue.value,
    onUpdate: ({ editor, transaction }) => {
        // HTML
        // this.$emit('update:modelValue', this.editor.getHTML())
        modelValue.value = editor.getHTML();

        // JSON
        // this.$emit('update:modelValue', this.editor.getJSON())
    },
    editable: !props.disabled,
    editorProps: {
        attributes: {
            class: "prose max-w-[unset] w-full rounded-md  bg-background px-4 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
        },
    },
});

const editorStore = useEditorStore();

watch(
    modelValue,
    (value) => {
        const edt = editor.value;
        if (!edt || !value) return;

        const isSame = edt.getHTML() === value;

        // JSON
        // const isSame = JSON.stringify(editor.getJSON()) === JSON.stringify(value)

        if (isSame) {
            return;
        }

        edt.commands.setContent(value, false);
    },
    { deep: true },
);

function onUploadImage(urls: string[]) {
    urls.forEach((r) => {
        // editor.commands.setImage({ src: 'https://example.com/foobar.png' })
        editor.value?.commands.setImage({ src: r });
    });
    editorStore.updateImageUploadModal(false);
}
</script>

<template>
    <editor-content v-bind="$attrs" :editor="editor" />

    <div>
        <Dialog
            :open="editorStore.isImageUploadModalOpen.value"
            @update:open="(v) => editorStore.updateImageUploadModal(v)"
        >
            <!-- <DialogTrigger> Edit Profile </DialogTrigger> -->
            <DialogContent
                class="max-h-[90dvh] grid-rows-[auto_minmax(0,1fr)_auto] px-0"
            >
                <DialogHeader class="p-6 pb-0">
                    <DialogTitle>Upload Image</DialogTitle>
                    <DialogDescription>
                        Upload image from your computer or from a link
                    </DialogDescription>
                </DialogHeader>
                <div class="overflow-y-auto px-6 py-4">
                    <UploadMediaForm :onUpload="onUploadImage" />
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style>
.tiptap p.is-editor-empty:first-child::before {
    color: hsl(var(--muted-foreground));
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

/* Control spacing in the editor */
.tiptap p {
    margin: 0.75rem 0;
}

.tiptap h1, .tiptap h2, .tiptap h3, .tiptap h4, .tiptap h5, .tiptap h6 {
    margin: 1rem 0 0.5rem 0;
    line-height: 1.2;
}

.tiptap ul, .tiptap ol {
    margin: 0.75rem 0;
    padding-left: 1.5rem;
}

.tiptap li {
    margin: 0.25rem 0;
}

.tiptap p:first-child {
    margin-top: 0;
}

.tiptap p:last-child {
    margin-bottom: 0;
}
.drag-handle {
    position: fixed;
    opacity: 1;
    transition: opacity ease-in 0.2s;
    border-radius: 0.25rem;

    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 10' style='fill: rgba(0, 0, 0, 0.5)'%3E%3Cpath d='M3,2 C2.44771525,2 2,1.55228475 2,1 C2,0.44771525 2.44771525,0 3,0 C3.55228475,0 4,0.44771525 4,1 C4,1.55228475 3.55228475,2 3,2 Z M3,6 C2.44771525,6 2,5.55228475 2,5 C2,4.44771525 2.44771525,4 3,4 C3.55228475,4 4,4.44771525 4,5 C4,5.55228475 3.55228475,6 3,6 Z M3,10 C2.44771525,10 2,9.55228475 2,9 C2,8.44771525 2.44771525,8 3,8 C3.55228475,8 4,8.44771525 4,9 C4,9.55228475 3.55228475,10 3,10 Z M7,2 C6.44771525,2 6,1.55228475 6,1 C6,0.44771525 6.44771525,0 7,0 C7.55228475,0 8,0.44771525 8,1 C8,1.55228475 7.55228475,2 7,2 Z M7,6 C6.44771525,6 6,5.55228475 6,5 C6,4.44771525 6.44771525,4 7,4 C7.55228475,4 8,4.44771525 8,5 C8,5.55228475 7.55228475,6 7,6 Z M7,10 C6.44771525,10 6,9.55228475 6,9 C6,8.44771525 6.44771525,8 7,8 C7.55228475,8 8,8.44771525 8,9 C8,9.55228475 7.55228475,10 7,10 Z'%3E%3C/path%3E%3C/svg%3E");
    background-size: calc(0.5em + 0.375rem) calc(0.5em + 0.375rem);
    background-repeat: no-repeat;
    background-position: center;
    width: 1.2rem;
    height: 1.5rem;
    z-index: 50;
    cursor: grab;

    &:hover {
        background-color: var(--novel-stone-100);
        transition: background-color 0.2s;
    }

    &:active {
        background-color: var(--novel-stone-200);
        transition: background-color 0.2s;
        cursor: grabbing;
    }

    &.hide {
        opacity: 0;
        pointer-events: none;
    }

    @media screen and (max-width: 600px) {
        display: none;
        pointer-events: none;
    }
}

.dark .drag-handle {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 10' style='fill: rgba(255, 255, 255, 0.5)'%3E%3Cpath d='M3,2 C2.44771525,2 2,1.55228475 2,1 C2,0.44771525 2.44771525,0 3,0 C3.55228475,0 4,0.44771525 4,1 C4,1.55228475 3.55228475,2 3,2 Z M3,6 C2.44771525,6 2,5.55228475 2,5 C2,4.44771525 2.44771525,4 3,4 C3.55228475,4 4,4.44771525 4,5 C4,5.55228475 3.55228475,6 3,6 Z M3,10 C2.44771525,10 2,9.55228475 2,9 C2,8.44771525 2.44771525,8 3,8 C3.55228475,8 4,8.44771525 4,9 C4,9.55228475 3.55228475,10 3,10 Z M7,2 C6.44771525,2 6,1.55228475 6,1 C6,0.44771525 6.44771525,0 7,0 C7.55228475,0 8,0.44771525 8,1 C8,1.55228475 7.55228475,2 7,2 Z M7,6 C6.44771525,6 6,5.55228475 6,5 C6,4.44771525 6.44771525,4 7,4 C7.55228475,4 8,4.44771525 8,5 C8,5.55228475 7.55228475,6 7,6 Z M7,10 C6.44771525,10 6,9.55228475 6,9 C6,8.44771525 6.44771525,8 7,8 C7.55228475,8 8,8.44771525 8,9 C8,9.55228475 7.55228475,10 7,10 Z'%3E%3C/path%3E%3C/svg%3E");
}
</style>
