import { Badge } from "@/Components/ui/badge";
import { Course, Lesson } from "@/types";
import { Link } from "@inertiajs/vue3";
import { createColumnHelper } from "@tanstack/vue-table";
import { GripVertical } from "lucide-vue-next";
import { h } from "vue";

const columnHelper = createColumnHelper<Lesson>();

export const lessonColumns = [
    columnHelper.display({
        id: "drag_handle",
        // header: () => h("div", { class: "" }, "Status"),
        cell(props) {
            return h(
                "div",
                { class: "tb-drag-handle cursor-grab" },
                h(GripVertical, { class: "size-4" }),
            );
        },
    }),
    columnHelper.accessor("title", {
        header: () => h("div", { class: "" }, "Title"),
        cell(props) {
            return h(
                "div",
                { class: "" },
                h(
                    Link,
                    {
                        class: "block",
                        href: route("lesson.edit", {
                            lesson: props.row.original.id,
                            course: props.row.original.course_id,
                        }),
                    },
                    () => props.row.getValue("title"),
                ),
            );
        },
    }),
    columnHelper.accessor("type", {
        header: () => h("div", { class: "" }, "Type"),
        cell(props) {
            return h(
                "div",
                { class: "capitalize" },
                (props.row.getValue("type") as string)?.toLowerCase(),
            );
        },
    }),
    columnHelper.display({
        id: "is_published",
        header: () => h("div", { class: "" }, "Status"),
        cell(props) {
            const isPublished = props.row.original.is_published;
            return h(
                "div",
                { class: "" },
                h(
                    Badge,
                    {
                        variant: isPublished ? "default" : "outline",
                        class: "uppercase",
                    },
                    () => (isPublished ? "Published" : "Draft"),
                ),
            );
        },
    }),

    // columnHelper.accessor("role", {
    //     header: () => h("div", { class: "" }, "Role"),
    //     cell(props) {
    //         return h(
    //             "div",
    //             { class: "" },
    //             h(SelectTeamRole, {
    //                 organisation_id: props.row.original.organisation_id!,
    //                 role: props.row.getValue("role") as User["role"],
    //                 user_id: props.row.original.id!,
    //             })
    //         );
    //     },
    //     size: 200,
    // }),

    // TODO: DELETE MEMBERSHIP
    // columnHelper.display({
    //     id: 'actions',
    //     enableHiding: false,
    //     cell: ({ row }) => {
    //       const payment = row.original

    //       return h('div', { class: 'relative' }, h(DropdownAction, {
    //         payment,
    //       }))
    //     },
    //   }),
];
