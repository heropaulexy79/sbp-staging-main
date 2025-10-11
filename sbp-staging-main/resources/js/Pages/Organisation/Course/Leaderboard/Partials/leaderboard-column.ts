import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { cn } from "@/lib/utils";
import { ColumnDef, createColumnHelper } from "@tanstack/vue-table";
import { h } from "vue";
import LeaderboardStudentName from "./LeaderboardStudentName.vue";
import LeaderboardStudentAction from "./LeaderboardStudentAction.vue";

export type Student = {
    user: {
        id: number;
        email: string;
        name: string;
    };
    score: number;
    scores: {
        score: string | null;
    }[];
};

const columnHelper = createColumnHelper<Student>();

export const leaderboardColumns = [
    columnHelper.display({
        id: "s/n",
        maxSize: 20,
        cell(props) {
            const n = props.row.index + 1;

            return h("div", n);
        },
    }),

    columnHelper.accessor("user.name", {
        header: () => h("div", { class: "" }, "Student"),
        cell(props) {
            return h(LeaderboardStudentName, {
                student: props.row.original,
            });
        },
    }),

    columnHelper.accessor("score", {
        header: () => h("div", { class: "text-right" }, ""),
        cell(props) {
            const n = props.row.index + 1;
            const score = props.row.original.score ?? 0;
            // console.log(score);
            return h(
                "div",
                {
                    class: cn(
                        "font-bold text-lg text-right",
                        n < 4 && "text-primary",
                    ),
                },
                score,
            );
        },
    }),

    columnHelper.display({
        id: "actions",
        maxSize: 50,
        cell(props) {
            return h(LeaderboardStudentAction, { student: props.row.original });
        },
    }),
] as any;
