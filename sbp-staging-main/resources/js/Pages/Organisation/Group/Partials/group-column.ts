// import Button from "@/Components/ui/button/Button.vue";
import { Group } from "@/types";
import { createColumnHelper } from "@tanstack/vue-table";
import { h } from "vue";
import GroupColumnRowAction from "./GroupColumnRowAction.vue";

const columnHelper = createColumnHelper<Group>();

export const groupColumns = [
    columnHelper.accessor("name", {
        header: () => h("div", { class: "" }, "Group Name"),
        cell(props) {
            return h(
                "div",
                { class: "font-medium" },
                props.row.original.name
            );
        },
    }),
    columnHelper.display({
        id: "users_count",
        header: () => h("div", { class: "" }, "Number of People"),
        cell(props) {
            const group = props.row.original;
            console.log('Group data:', group); // Debug log
            const count = group.users_count || group.users?.length || 0;
            return h(
                "div",
                { class: "" },
                `${count} member${count !== 1 ? 's' : ''}`
            );
        },
    }),
    columnHelper.display({
        id: "actions",
        maxSize: 50,
        cell(props) {
            return h(GroupColumnRowAction, { group: props.row.original });
        },
    }),
];
