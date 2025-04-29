<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File } from '@/types';
import { ArrowLeftIcon, PencilIcon, DownloadIcon } from 'lucide-vue-next';

interface Props {
    file: File;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: props.file.name,
        href: `/files/${props.file.id}`,
    },
];
</script>

<template>
    <Head :title="`File: ${file.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        href="/files"
                        class="inline-flex items-center gap-1 text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Files
                    </Link>
                    <h1 class="text-2xl font-bold">File Details</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="`/files/${file.id}/edit`"
                        class="inline-flex items-center justify-center gap-1 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <PencilIcon class="h-4 w-4" />
                        Edit
                    </Link>
                    <Link
                        :href="`/files/${file.id}/download`"
                        class="inline-flex items-center justify-center gap-1 rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-accent"
                    >
                        <DownloadIcon class="h-4 w-4" />
                        Download
                    </Link>
                </div>
            </div>

            <div class="rounded-lg border border-border p-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold">File Information</h2>
                        <div class="rounded-md bg-accent/50 p-4">
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="font-medium text-muted-foreground">Name:</dt>
                                    <dd>{{ file.name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-muted-foreground">Path:</dt>
                                    <dd class="text-right">{{ file.path }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-muted-foreground">ID:</dt>
                                    <dd>{{ file.id }}</dd>
                                </div>
                                <div class="flex justify-between items-start">
                                    <dt class="font-medium text-muted-foreground">Tags:</dt>
                                    <dd class="flex flex-wrap gap-1 justify-end">
                                        <span 
                                            v-for="tag in file.tags" 
                                            :key="tag.id"
                                            class="inline-flex px-2 py-1 text-xs rounded-md bg-primary/10 text-primary"
                                        >
                                            {{ tag.name }}
                                        </span>
                                        <span v-if="!file.tags || file.tags.length === 0" class="text-muted-foreground text-sm">
                                            No tags
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold">File Content</h2>
                        <div class="max-h-60 overflow-auto rounded-md bg-accent/50 p-4">
                            <pre class="text-sm">{{ file.content }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

