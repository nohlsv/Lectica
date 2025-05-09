<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { type BreadcrumbItem, type File, type PaginatedData } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, StarIcon } from 'lucide-vue-next';
import { useDateFormat } from '@vueuse/core';

interface Props {
    files: PaginatedData<File>;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'content', label: 'Content', class: 'hidden sm:table-cell' },
    { key: 'created_at', label: 'Upload Info', class: 'hidden md:table-cell' },
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
        <div class="rounded-xl border border-border p-4 sm:p-6 mb-8 overflow-hidden">
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <DataTable :data="files" :columns="columns" class="min-w-full">
                    <!-- Custom cell template to clamp content text -->
                    <template #cell-content="{ item }">
                        <p class="max-w-full sm:line-clamp-2 line-clamp-4 text-sm text-muted-foreground">
                            {{ item.content }}
                        </p>
                    </template>
                    <template #cell-created_at="{ item }">
                        <p class="max-w-full text-sm text-muted-foreground">
                            By {{item.user.last_name}}, {{item.user.first_name}}<br>
                            {{ useDateFormat(item.created_at, 'MMM D, YYYY').value }}
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
                                v-if="item.can_edit"
                                :href="`/files/${item.id}/edit`"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                                title="Edit file"
                            >
                                <PencilIcon class="h-4 w-4" />
                            </Link>
                            <div v-else class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-muted-foreground opacity-40" title="Only the uploader can edit this file">
                                <PencilIcon class="h-4 w-4" />
                            </div>
                            <StarIcon class="h-4 w-4 text-amber-500" />
                            <span>{{ item.star_count || 0 }}</span>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
