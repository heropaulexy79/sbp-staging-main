<script lang="ts" setup>
import { Link } from "@inertiajs/vue3";
import { Paginated } from "@/types";
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationList,
    PaginationListItem,
    PaginationNext,
    PaginationPrev,
} from "@/Components/ui/pagination";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/Components/ui/button";
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from "lucide-vue-next";

defineProps<{
    items: Paginated<any>;
}>();
</script>

<template>
    <Pagination
        v-model:page="items.current_page"
        :total="items.total"
        show-edges
    >
        <PaginationList class="flex items-center gap-1">
            <PaginationFirst as-child>
                <Link
                    preserve-scroll
                    :href="items.first_page_url"
                    :class="
                        cn(
                            buttonVariants({
                                variant: 'outline',
                                class: 'h-10 w-10 p-0',
                            }),
                        )
                    "
                >
                    <ChevronsLeft class="h-4 w-4" />
                </Link>
            </PaginationFirst>
            <PaginationPrev>
                <Link
                    preserve-scroll
                    :href="items.prev_page_url ?? '#'"
                    :class="
                        cn(
                            buttonVariants({
                                variant: 'outline',
                                class: 'h-10 w-10 p-0',
                            }),
                        )
                    "
                >
                    <ChevronLeft class="h-4 w-4" />
                </Link>
            </PaginationPrev>

            <template v-for="(item, index) in items.links.slice(1, -1)">
                <PaginationListItem :value="Number(item.label)" as-child>
                    <Link
                        preserve-scroll
                        :href="item.url ?? '#'"
                        :class="
                            cn(
                                buttonVariants({
                                    variant: item.active
                                        ? 'default'
                                        : 'outline',
                                    class: 'h-10 w-10 p-0',
                                }),
                            )
                        "
                    >
                        {{ item.label }}
                    </Link>
                </PaginationListItem>
                <!-- <PaginationEllipsis
                                    v-else
                                    :key="item.type"
                                    :index="index"
                                /> -->
            </template>

            <PaginationNext as-child>
                <Link
                    preserve-scroll
                    :href="items.next_page_url ?? '#'"
                    :class="
                        cn(
                            buttonVariants({
                                variant: 'outline',
                                class: 'h-10 w-10 p-0',
                            }),
                        )
                    "
                >
                    <ChevronRight class="h-4 w-4" />
                </Link>
            </PaginationNext>
            <PaginationLast as-child>
                <Link
                    preserve-scroll
                    :href="items.last_page_url"
                    :class="
                        cn(
                            buttonVariants({
                                variant: 'outline',
                                class: 'h-10 w-10 p-0',
                            }),
                        )
                    "
                >
                    <ChevronsRight class="h-4 w-4" />
                </Link>
            </PaginationLast>
        </PaginationList>
    </Pagination>
</template>
