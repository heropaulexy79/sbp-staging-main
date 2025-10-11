import { h, type VNode } from "vue";
import { Editor, Range, VueRenderer } from "@tiptap/vue-3";
import { Extension } from "@tiptap/core";
import Suggestion, { type SuggestionOptions } from "@tiptap/suggestion";
import tippy from "tippy.js";

import CommandsList from "./CommandsList.vue";
import {
    Code2Icon,
    Heading1Icon,
    Heading2Icon,
    Heading3Icon,
    ImagesIcon,
    ListIcon,
    ListOrderedIcon,
    TextIcon,
    TextQuoteIcon,
} from "lucide-vue-next";
import { useEditorStore } from "../use-editor-store";

export interface CommandProps {
    editor: Editor;
    range: Range;
}

export interface CommandItemProps {
    title: string;
    description: string;
    icon?: VNode;
}

export const EditorCommandMenu = Extension.create<{
    suggestion: Partial<SuggestionOptions>;
}>({
    name: "editor-commands",
    addOptions() {
        return {
            suggestion: {
                char: "/",
                command: ({ editor, range, props }) => {
                    props.command({ editor, range });
                },
            },
        };
    },
    addProseMirrorPlugins() {
        return [
            Suggestion({
                editor: this.editor,
                ...this.options.suggestion,
            }),
        ];
    },
});

export const editorSuggestions = {
    items: ({ query }: { query: string }) => {
        return [
            {
                title: "Text",
                description: "Just start typing with plain text.",
                searchTerms: ["p", "paragraph"],
                icon: h(TextIcon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleNode("paragraph", "paragraph")
                        .run();
                },
            },
            //   {
            //     title: "To-do List",
            //     description: "Track tasks with a to-do list.",
            //     searchTerms: ["todo", "task", "list", "check", "checkbox"],
            //     // icon: <CheckSquareIcon size={18} />,
            //     command: ({ editor, range }: CommandProps) => {
            //       editor.chain().focus().deleteRange(range).toggleTaskList().run();
            //     },
            //   },
            {
                title: "Heading 1",
                description: "Big section heading.",
                searchTerms: ["title", "big", "large"],
                icon: h(Heading1Icon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode("heading", { level: 1 })
                        .run();
                },
            },
            {
                title: "Heading 2",
                description: "Medium section heading.",
                searchTerms: ["subtitle", "medium"],
                icon: h(Heading2Icon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode("heading", { level: 2 })
                        .run();
                },
            },
            {
                title: "Heading 3",
                description: "Small section heading.",
                searchTerms: ["subtitle", "small"],
                icon: h(Heading3Icon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .setNode("heading", { level: 3 })
                        .run();
                },
            },
            {
                title: "Bullet List",
                description: "Create a simple bullet list.",
                searchTerms: ["unordered", "point"],
                icon: h(ListIcon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleBulletList()
                        .run();
                },
            },
            {
                title: "Numbered List",
                description: "Create a list with numbering.",
                searchTerms: ["ordered"],
                icon: h(ListOrderedIcon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleOrderedList()
                        .run();
                },
            },
            {
                title: "Quote",
                description: "Capture a quote.",
                searchTerms: ["blockquote"],
                icon: h(TextQuoteIcon, { size: 18 }),
                command: ({ editor, range }: CommandProps) =>
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleNode("paragraph", "paragraph")
                        .toggleBlockquote()
                        .run(),
            },
            {
                title: "Code",
                description: "Capture a code snippet.",
                searchTerms: ["codeblock"],
                icon: h(Code2Icon, { size: 18 }),
                command: ({ editor, range }: CommandProps) =>
                    editor
                        .chain()
                        .focus()
                        .deleteRange(range)
                        .toggleCodeBlock()
                        .run(),
            },
            {
                title: "Image",
                description: "Upload image from url or device",
                searchTerms: ["image", "upload"],
                icon: h(ImagesIcon, { size: 18 }),
                command: ({ editor, range }: CommandProps) => {
                    const editorStore = useEditorStore();
                    editor.chain().focus().deleteRange(range).run();

                    editorStore.updateImageUploadModal(true);
                },
            },
        ].filter((item) => {
            if (typeof query === "string" && query.length > 0) {
                const search = query.toLowerCase();
                return (
                    item.title.toLowerCase().includes(search) ||
                    item.description.toLowerCase().includes(search) ||
                    (item.searchTerms &&
                        item.searchTerms.some((term: string) =>
                            term.includes(search),
                        ))
                );
            }
            return true;
        });
    },

    render: () => {
        let component: VueRenderer | null = null;
        let popup: any | null = null;

        return {
            onStart: (props: { editor: Editor; clientRect: DOMRect }) => {
                component = new VueRenderer(CommandsList, {
                    // using vue 2:
                    // parent: this,
                    // propsData: props,
                    props,
                    editor: props.editor,
                });

                if (!props.clientRect) {
                    return;
                }

                popup = tippy("body", {
                    getReferenceClientRect: props.clientRect as any,
                    appendTo: () => document.body,
                    content: component.element,
                    showOnCreate: true,
                    interactive: true,
                    trigger: "manual",
                    placement: "bottom-start",
                });
            },

            onUpdate(props: { editor: Editor; clientRect: DOMRect }) {
                component?.updateProps(props);

                if (!props.clientRect) {
                    return;
                }

                popup[0].setProps({
                    getReferenceClientRect: props.clientRect,
                });
            },

            onKeyDown(props: { event: KeyboardEvent }) {
                if (props.event.key === "Escape") {
                    popup[0].hide();

                    return true;
                }

                return component?.ref?.onKeyDown(props);
            },

            onExit() {
                popup[0].destroy();
                component?.destroy();
            },
        };
    },
};
