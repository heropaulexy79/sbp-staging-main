<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef } from "@tanstack/vue-table";
import { FlexRender, getCoreRowModel, useVueTable } from "@tanstack/vue-table";
import { useDragAndDrop } from "@formkit/drag-and-drop/vue";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { animations } from "@formkit/drag-and-drop";
import { Lesson } from "@/types";
import axios from "axios";
import { ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { watchDebounced } from "@vueuse/core";

const props = defineProps<{
    columns: ColumnDef<Lesson, TValue>[];
    data: Lesson[];
    meta?: Record<string, any>;
}>();

const page = usePage();
const defaultItems = ref([...props.data]);

const [parent, data] = useDragAndDrop(defaultItems.value, {
    // handleEnd(data) {
    //     console.log({ newDaatea: data });
    // },
    // performSort(state, data) {
    //     console.log(state, data);
    // },
    draggingClass: "dragging",
    dragHandle: ".tb-drag-handle",
    plugins: [animations()],
});

const table = useVueTable({
    get data() {
        return data.value;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
    defaultColumn: {
        size: Number.MAX_SAFE_INTEGER,
        maxSize: Number.MAX_SAFE_INTEGER,
        minSize: 50,
    },
    meta: {
        ...(props.meta ?? {}),
    },
});

watchDebounced(
    data,
    (nam) => {
        // console.log(nam.map((t) => t.title));
        Promise.allSettled([
            nam.map((item, ind) => {
                return axios.patch(
                    route("lesson.update.position", {
                        course: Number(item.course_id),
                        lesson: item.id,
                    }),
                    { position: ind },
                    {
                        headers: {
                            Accept: "application/json",
                        },
                    },
                );
            }),
        ]).then(() => {
            // For some reason state becomes inconsistent
            router.visit(page.url, {
                preserveScroll: true,
            });
            // defaultItems.value = name
        });
    },
    { deep: true },
);
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow
                    v-for="headerGroup in table.getHeaderGroups()"
                    :key="headerGroup.id"
                >
                    <TableHead
                        v-for="header in headerGroup.headers"
                        :key="header.id"
                        :style="{
                            width:
                                header.getSize() === Number.MAX_SAFE_INTEGER
                                    ? 'auto'
                                    : header.getSize() + 'px',
                            colSpan: header.colSpan,
                        }"
                    >
                        <FlexRender
                            v-if="!header.isPlaceholder"
                            :render="header.column.columnDef.header"
                            :props="header.getContext()"
                        />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody ref="parent">
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        :data-state="
                            row.getIsSelected() ? 'selected' : undefined
                        "
                    >
                        <TableCell
                            v-for="cell in row.getVisibleCells()"
                            :key="cell.id"
                            :style="{
                                width:
                                    cell.column.getSize() ===
                                    Number.MAX_SAFE_INTEGER
                                        ? 'auto'
                                        : cell.column.getSize() + 'px',
                            }"
                        >
                            <FlexRender
                                :render="cell.column.columnDef.cell"
                                :props="cell.getContext()"
                            />
                        </TableCell>
                    </TableRow>
                </template>
                <template v-else>
                    <TableRow>
                        <TableCell
                            :colSpan="columns.length"
                            class="h-24 text-center"
                        >
                            No results.
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>
    </div>
</template>
