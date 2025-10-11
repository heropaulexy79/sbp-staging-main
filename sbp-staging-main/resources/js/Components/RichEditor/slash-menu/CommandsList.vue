<script lang="ts" setup>
import { cn } from "@/lib/utils";
import { watch, ref } from "vue";
import { CommandItemProps } from "./editor-suggestions";

const props = defineProps<{
    items: CommandItemProps[];
    command: (...props: any) => void;
}>();
const selectedIndex = ref(0);
const container = ref<HTMLDivElement>();

defineExpose({
    onKeyDown,
});

watch(
    () => props.items,
    () => {
        selectedIndex.value = 0;
    },
);

function onKeyDown({ event }: { event: KeyboardEvent }) {
    if (event.key === "ArrowUp") {
        selectedIndex.value =
            (selectedIndex.value + props.items.length - 1) % props.items.length;
        return true;
    }

    if (event.key === "ArrowDown") {
        selectedIndex.value = (selectedIndex.value + 1) % props.items.length;
        return true;
    }

    if (event.key === "Enter") {
        selectItem(selectedIndex.value);
        event.stopPropagation();
        event.preventDefault();
        return true;
    }

    return false;
}

function selectItem(index: number) {
    const item = props.items[index];

    if (item) {
        props.command(item);
    }
}

function updateScrollView(container: HTMLElement, item: HTMLElement) {
    const containerHeight = container.offsetHeight;
    const itemHeight = item ? item.offsetHeight : 0;

    const top = item.offsetTop;
    const bottom = top + itemHeight;

    if (top < container.scrollTop) {
        container.scrollTop -= container.scrollTop - top + 5;
    } else if (bottom > containerHeight + container.scrollTop) {
        container.scrollTop +=
            bottom - containerHeight - container.scrollTop + 5;
    }
}

watch(selectedIndex, () => {
    const item = container.value?.children[selectedIndex.value] as HTMLElement;

    if (item && container.value) updateScrollView(container.value, item);
});
</script>

<template>
    <div
        ref="container"
        className="z-50 h-auto max-h-[330px] w-72 overflow-y-auto rounded-md border border-border bg-background px-1 py-2 shadow-md transition-all"
    >
        <template v-if="items.length">
            <button
                v-for="(item, index) in items"
                :key="index"
                :class="
                    cn(
                        `flex w-full items-center space-x-2 rounded-md px-2 py-1 text-left text-sm hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground`,
                        index === selectedIndex &&
                            'bg-accent text-accent-foreground',
                    )
                "
                @click="selectItem(index)"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-md border border-border bg-background"
                >
                    <!-- {{ item.icon }} -->
                    <component :is="() => item.icon" />
                </div>
                <div>
                    <p class="font-medium">{{ item.title }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ item.description }}
                    </p>
                </div>
            </button>
        </template>
        <div class="item" v-else>No result</div>
    </div>
</template>
