import { OrganisationInvite, OrganisationUser, User } from "@/types";
import { createColumnHelper } from "@tanstack/vue-table";
import { h } from "vue";
import SelectTeamRole from "./SelectEmployeeRole.vue";
import InviteActions from "./InviteActions.vue";
import EmployeeActions from "./EmployeeActions.vue";

const columnHelper = createColumnHelper<OrganisationUser>();

export const employeeColumns = [
    columnHelper.accessor("user.name", {
        header: () => h("div", { class: "" }, "Name"),
        cell(props) {
            return h("div", { class: "" }, props.getValue());
        },
    }),

    columnHelper.accessor("role", {
        header: () => h("div", { class: "" }, "Role"),
        cell(props) {
            return h(
                "div",
                { class: "" },
                h(SelectTeamRole, {
                    organisation_id: props.row.original.organisation_id!,
                    role: props.row.getValue(
                        "role",
                    ) as OrganisationUser["role"],
                    user_id: props.row.original.user_id,
                }),
            );
        },
        size: 200,
    }),

    columnHelper.display({
        id: "actions",
        size: 100,
        // enableHiding: false,
        cell: ({ row }) => {
            const payment = row.original;

            return h(
                "div",
                { class: "" },
                h(EmployeeActions, {
                    employee: row.original,
                }),
            );
        },
    }),
];

const inviteColumnHelper2 = createColumnHelper<OrganisationInvite>();

export const employeeInviteColumns = [
    inviteColumnHelper2.accessor("email", {
        header: () => h("div", { class: "" }, "Email"),
        cell(props) {
            return h("div", { class: "" }, props.row.getValue("email"));
        },
    }),

    inviteColumnHelper2.accessor("role", {
        header: () => h("div", { class: "" }, "Role"),
        cell(props) {
            const role = {
                MEMBER: "Student",
                ADMIN: "Administrator",
            };
            return h(
                "div",
                { class: "" },
                role[props.row.getValue("role") as keyof typeof role] ??
                    role.MEMBER,
            );
        },
    }),

    inviteColumnHelper2.display({
        id: "actions",
        enableHiding: false,
        size: 100,
        cell: ({ row }) => {
            const payment = row.original;

            return h(
                "div",
                { class: "relative" },
                h(InviteActions, {
                    id: row.original.id,
                    organisation_id: row.original.organisation_id!,
                }),
            );
        },
    }),
];
