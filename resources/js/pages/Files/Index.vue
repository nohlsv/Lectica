<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { type BreadcrumbItem, type File, type PaginatedData } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, StarIcon, TrendingUpIcon, UsersIcon, TagsIcon, GraduationCapIcon } from 'lucide-vue-next';
import FileCard from '@/components/FileCard.vue';
import { useDateFormat } from '@vueuse/core';

interface RecommendationGroup {
    [key: string]: File[];
}

interface Props {
    files: PaginatedData<File>;
    recommendations: RecommendationGroup;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'content', label: 'Content' },
    { key: 'created_at', label: 'Uploaded At' },
];

// Map recommendation categories to icons and titles
const recommendationCategories = [
    {
        key: 'trending',
        title: 'Trending Files',
        icon: TrendingUpIcon,
        description: 'Popular files with the most stars this week'
    },
    {
        key: 'program',
        title: 'Files From Your Program',
        icon: GraduationCapIcon,
        description: 'Files shared by students in your program'
    },
    {
        key: 'collaborative',
        title: 'You Might Like These',
        icon: UsersIcon,
        description: 'Based on files you\'ve starred'
    },
    {
        key: 'contentBased',
        title: 'Similar Content',
        icon: TagsIcon,
        description: 'Files with similar tags to ones you\'ve viewed'
    }
];
</script>

<template>
    <Head title="File List" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-semibold">Files</h1>
                <Link href="/files/create" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    Upload New File
                </Link>
            </div>
        </div>
        <div class="rounded-xl border border-border p-6 mb-8">
            <DataTable :data="files" :columns="columns">
                <!-- Custom cell template to clamp content text -->
                <template #cell-content="{ item }">
                    <p class="max-w-full sm:line-clamp-2 line-clamp-4 text-sm text-muted-foreground">
                        {{ item.content }}
                    </p>
                </template>
                <template #cell-created_at="{ item }">
                    {{ useDateFormat(item.created_at, 'MMM D, YYYY').value }}
                </template>

                <template #actions="{ item }">
                    <div class="flex items-center gap-2">
                        <Link
                            :href="`/files/${item.id}`"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                            title="View file details"
                        >
                            <EyeIcon class="h-4 w-4" />
                        </Link>
                        <Link
                            :href="`/files/${item.id}/edit`"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                            title="Edit file"
                        >
                            <PencilIcon class="h-4 w-4" />
                        </Link>
                        <StarIcon class="h-4 w-4 text-amber-500" />
                        <span>{{ item.star_count || 0 }}</span>
                    </div>
                </template>
            </DataTable>
        </div>

        <!-- Recommendations Section -->
        <div class="px-4 mb-10">
            <h2 class="text-xl font-semibold mb-6">Recommended Files</h2>

            <div class="grid gap-8">
                <div v-for="category in recommendationCategories" :key="category.key" class="space-y-4">
                    <!-- Only show categories that have files -->
                    <div v-if="recommendations[category.key]?.length">
                        <div class="flex items-center gap-2 mb-3">
                            <component :is="category.icon" class="h-5 w-5 text-primary" />
                            <h3 class="text-lg font-medium">{{ category.title }}</h3>
                        </div>

                        <p class="text-sm text-muted-foreground mb-4">{{ category.description }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <FileCard
                                v-for="file in recommendations[category.key]"
                                :key="file.id"
                                :file="file"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link to view all recommendations -->
            <div class="mt-8 text-center">
                <Link
                    href="/recommendations"
                    class="inline-flex items-center justify-center rounded-md bg-primary/10 text-primary px-4 py-2 text-sm font-medium hover:bg-primary/20 transition-colors"
                >
                    View All Recommendations
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
