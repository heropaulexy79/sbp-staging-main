import { BillingHistory } from "@/types";
import { createColumnHelper } from "@tanstack/vue-table";
import { h } from "vue";

const columnHelper = createColumnHelper<BillingHistory>();

export const billingHistoryColumns = [
    columnHelper.accessor("transaction_ref", {
        header: () => h("div", { class: "" }, "Transaction reference"),
        cell(props) {
            return h(
                "div",
                { class: "" },
                props.row.getValue("transaction_ref"),
            );
        },
    }),
    columnHelper.accessor("description", {
        header: () => h("div", { class: "" }, "Description"),
        cell(props) {
            const description = props.getValue();
            return h("div", { class: "" }, description);
        },
    }),
    columnHelper.accessor("amount", {
        header: () => h("div", { class: "text-right" }, "Amount"),
        cell(props) {
            const amount = props.getValue() ?? 0;
            return h(
                "div",
                { class: "text-right" },
                Intl.NumberFormat(undefined, {
                    style: "currency",
                    currency: props.row.original.currency ?? "NGN",
                }).format(amount),
            );
        },
    }),
    columnHelper.accessor("created_at", {
        header: () => h("div", { class: "" }, "Date"),
        cell(props) {
            const dv = props.getValue();
            const date = dv ? new Date(dv) : null;

            return h(
                "div",
                { class: "" },
                date ? Intl.DateTimeFormat(undefined, {}).format(date) : "-",
            );
        },
    }),

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
