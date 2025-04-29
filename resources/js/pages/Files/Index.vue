<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { type BreadcrumbItem, type File, type PaginatedData } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon } from 'lucide-vue-next';

interface Props {
    files: PaginatedData<File>;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
];

defineProps<Props>();

const columns = [
    { key: 'name', label: 'File Name' },
    { key: 'content', label: 'File Content' },
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
        <div class="rounded-xl border border-border p-6">
            <DataTable :data="files" :columns="columns">
                <!-- Custom cell template to clamp content text -->
                <template #cell-content="{ item }">
                    <p class="max-w-full sm:line-clamp-2  line-clamp-4 text-sm text-muted-foreground">
                        {{ item.content }}
                    </p>
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
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
