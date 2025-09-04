<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type File } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon, FileIcon } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface Props {
    files: {
        data: File[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
    };
}

const props = defineProps<Props>();

const verifyFile = (fileId: number) => {
    router.patch(
        route('files.verify.update', fileId),
        {},
        {
            onSuccess: () => {
                toast.success('File verified successfully!');
                router.reload(); // Refresh the file list after verification
            },
            onError: () => {
                toast.error('Failed to verify the file. Please try again.');
            },
        },
    );
};
</script>

<template>
    <Head title="Verify Files" />
    <AppLayout>
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Verify Files</h1>
                <p class="text-muted-foreground text-sm">Review and verify uploaded files for quality and accuracy</p>
            </div>

            <!-- Empty State -->
            <div v-if="props.files.data.length === 0" class="py-12 text-center">
                <div class="mx-auto max-w-md">
                    <CheckCircleIcon class="mx-auto h-12 w-12 text-green-500" />
                    <h3 class="text-foreground mt-2 text-sm font-medium">All files verified</h3>
                    <p class="text-muted-foreground mt-1 text-sm">There are no unverified files at the moment.</p>
                    <div class="mt-6">
                        <Link
                            href="/files"
                            class="bg-primary hover:bg-primary/90 inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm"
                        >
                            Browse All Files
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Files Grid -->
            <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="file in props.files.data"
                    :key="file.id"
                    class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <!-- File Header -->
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <FileIcon class="h-8 w-8 text-blue-500" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-lg font-medium text-gray-900 dark:text-gray-100">
                                    {{ file.name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">by {{ file.user.first_name }} {{ file.user.last_name }}</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                        >
                            Pending
                        </span>
                    </div>

                    <!-- File Description -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ file.description || 'No description provided' }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div v-if="file.tags && file.tags.length > 0" class="mb-4">
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-for="tag in file.tags"
                                :key="tag.id"
                                class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- File Metadata -->
                    <div class="mb-4 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <div>Uploaded: {{ new Date(file.created_at).toLocaleDateString() }}</div>
                        <div>ID: {{ file.id }}</div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between space-x-2">
                        <Link
                            :href="`/files/${file.id}`"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm leading-4 font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            View
                        </Link>
                        <button
                            @click="verifyFile(file.id)"
                            class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <CheckCircleIcon class="mr-1.5 h-4 w-4" />
                            Verify
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.files.links && props.files.links.length > 3" class="mt-8">
                <nav class="flex items-center justify-between">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <template v-for="link in props.files.links" :key="link.label">
                            <Link
                                v-if="link.url && !link.active"
                                :href="link.url"
                                v-html="link.label"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                :class="
                                    link.active
                                        ? 'relative inline-flex cursor-default items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500'
                                        : 'relative inline-flex cursor-not-allowed items-center border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400'
                                "
                            />
                        </template>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
