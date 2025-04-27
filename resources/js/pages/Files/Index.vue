<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { type File } from '@/types';

interface Props {
    files: {
        data: File[];
        current_page: number;
        last_page: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        from: number;
        to: number;
        total: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
];

defineProps<Props>();
</script>

<template>
    <Head title="File List" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <h1 class="text-2xl font-bold">Files</h1>
            <p class="text-gray-600">List of files uploaded by the user.</p>
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Your Files</h2>
                <button class="btn btn-primary">Upload New File</button>
            </div>
        </div>
        <div class="rounded-xl border p-6">
            <table class="table-auto w-full divide-x divide-gray-200">
                <thead class="border-b">
                    <tr>
                        <th class="text-left p-4 pb-4">File Name</th>
                        <th class="text-left p-4 pb-4">File Content</th>
                        <th class="text-left p-4 pb-4">Path</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="file in files.data" :key="file.id">
                        <td class="p-4">{{ file.name }}</td>
                        <td class="p-4">{{ file.content }}</td>
                        <td class="p-4">{{ file.path }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
                <div class="flex flex-1 justify-between sm:hidden">
                    <Link
                        v-if="files.links[0].url"
                        :href="files.links[0].url"
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="files.links[files.links.length - 1].url"
                        :href="files.links[files.links.length - 1].url ?? ''"
                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Next
                    </Link>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm">
                            Showing
                            <span class="font-medium">{{ files.from }}</span>
                            to
                            <span class="font-medium">{{ files.to }}</span>
                            of
                            <span class="font-medium">{{ files.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <Link
                                v-for="link in files.links"
                                :key="link.label"
                                :href="link.url ?? ''"
                                :class="[
                                    link.active ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                    'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                                ]"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
