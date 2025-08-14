<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Tag } from '@/types';
import { FileIcon, UploadIcon, } from 'lucide-vue-next';
import TagInput from '@/components/TagInput.vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    allTags?: Tag[];
}

defineProps<Props>();

// Define breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: 'Upload',
        href: '/files/create',
    },
];

// Initialize form with proper typing
const form = useForm({
    name: '',
    description: '',
    file: null as File | null,
    tags: [],
    verified: false,
});

// File upload reference and state
const fileInputRef = ref<HTMLInputElement | null>(null);
const fileSelected = ref(false);
const fileName = ref('');
const fileSize = ref('');

// Handle file upload
const handleFileUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        form.file = file;
        fileSelected.value = true;
        fileName.value = file.name;

        // Reset and set suggested name (remove extension)
        const nameParts = file.name.split('.');
        if (nameParts.length > 1) {
            nameParts.pop(); // Remove extension
        }
        form.name = nameParts.join('.');

        // Format file size
        if (file.size < 1024) {
            fileSize.value = `${file.size} bytes`;
        } else if (file.size < 1024 * 1024) {
            fileSize.value = `${(file.size / 1024).toFixed(2)} KB`;
        } else {
            fileSize.value = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
        }
    }
};

// Handle drag-and-drop events
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    event.dataTransfer!.dropEffect = 'copy';
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    const files = event.dataTransfer?.files;
    if (files && files.length > 0) {
        const file = files[0];
        form.file = file;
        fileSelected.value = true;
        fileName.value = file.name;

        // Reset and set suggested name (remove extension)
        const nameParts = file.name.split('.');
        if (nameParts.length > 1) {
            nameParts.pop(); // Remove extension
        }
        form.name = nameParts.join('.');

        // Format file size
        if (file.size < 1024) {
            fileSize.value = `${file.size} bytes`;
        } else if (file.size < 1024 * 1024) {
            fileSize.value = `${(file.size / 1024).toFixed(2)} KB`;
        } else {
            fileSize.value = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;
        }
    }
};

// Form submission
const submit = () => {
    form.post(route('files.store'), {
        onSuccess: () => {
        },
        onError: () => {
            toast.error('Failed to upload file.');
        },
    });
};
</script>

<template>
    <Head title="Upload File" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold">Upload New File</h1>
            </div>

            <!-- Form -->
            <div class="flex justify-center rounded-lg border border-border p-6 self-center w-full dark:border-[#6c6c6c] dark:bg-[#222222]">
                <form @submit.prevent="submit" class="space-y-6 w-full max-w-xl">
                <!-- File Upload -->
                    <div class="space-y-2">
                        <label for="file" class="block text-sm font-medium text-foreground">File</label>
                        <div
                            class="flex flex-col items-center justify-center rounded-md border-2 border-dashed border-[#6c6c6c] p-6 cursor-pointer hover:border-primary transition-colors"
                            :class="{ 'border-primary bg-primary/5': fileSelected }"
                            @click="fileInputRef?.click()"
                            @dragover="handleDragOver"
                            @drop="handleDrop"
                        >
                            <input
                                type="file"
                                id="file"
                                ref="fileInputRef"
                                class="hidden"
                                @change="handleFileUpload"
                            />

                            <div v-if="!fileSelected" class="flex flex-col items-center gap-3">
                                <div class="rounded-full bg-primary/10 p-4">
                                    <UploadIcon class="h-6 w-6 text-primary" />
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium">Click to upload or drag and drop</p>
                                    <p class="text-xs text-muted-foreground mt-1">PDF, DOC, DOCX, PPTX, TXT, XLSX (Max 25MB)</p>
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center gap-3">
                                <div class="rounded-full bg-green-500/10 p-4">
                                    <FileIcon class="h-6 w-6 text-green-500" />
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium">{{ fileName }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ fileSize }} - Click to change</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.file" class="mt-1 text-xs text-red-500">
                            {{ form.errors.file }}
                            <Link
                                v-if="(form.errors as any).duplicate_file_id"
                                :href="`/files/${(form.errors as any).duplicate_file_id}`"
                                class="ml-1 text-primary hover:underline font-medium"
                            >
                                View duplicate file
                            </Link>
                        </div>
                    </div>

                    <!-- File Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-foreground">File Name</label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="w-full rounded-md border border-input dark:bg-[#d9d9d9] px-3 py-2 text-sm dark:text-[#2F2F2F] ring-offset-background"
                            placeholder="Enter a name for your file"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- File Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-foreground">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border border-input dark:bg-[#d9d9d9] dark:text-[#2F2F2F] px-3 py-2 text-sm ring-offset-background resize-none"
                            placeholder="Enter a brief description of this file (optional)"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tags" class="block text-sm font-medium text-foreground">Tags</label>
                        <TagInput
                            v-model="form.tags"
                            :existing-tags="allTags || []"
                        />
                        <p class="text-xs text-muted-foreground">
                            Add tags to categorize your file. You can create new tags or select existing ones.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-2">
                        <Link
                            href="/files"
                            class="inline-flex items-center justify-center rounded-md border border-border bg-red-500/90 px-4 py-2 text-sm font-medium text-foreground hover:bg-accent"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-md bg-[#3aa035] px-4 py-2 text-sm font-medium hover:bg-[#3aa035]/90 disabled:opacity-50 disabled:cursor-not-allowed "
                            :disabled="form.processing || !form.file"
                        >
                            <UploadIcon v-if="!form.processing" class="h-4 w-4" />
                            {{ form.processing ? 'Uploading...' : 'Upload File' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

