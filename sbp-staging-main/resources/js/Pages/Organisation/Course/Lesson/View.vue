<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Lesson } from "@/types";

defineProps<{ lesson: Lesson }>();
</script>

<template>
    <Head :title="lesson.title" />

    <AuthenticatedLayout> </AuthenticatedLayout>
</template>
