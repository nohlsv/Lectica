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
const userCollections = ref<Collection[]>([]);
const selectedCollections = ref<number[]>([]);

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

// Handle file upload
const handleFileUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        const file = input.files[0];
        form.file = file;
        fileSelected.value = true;
        fileName.value = file.name;
        fileSize.value = formatFileSize(file.size);

        // Auto-populate the name field if it's empty
        if (!form.name) {
            form.name = file.name.replace(/\.[^/.]+$/, ''); // Remove file extension
        }
    }
};

// Format file size
const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Fetch user's collections
const fetchUserCollections = async () => {
    try {
        const response = await fetch('/api/user/collections');
        const data = await response.json();
        userCollections.value = data;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

// Initialize collections on component mount
fetchUserCollections();

// Toggle collection selection
const toggleCollection = (collectionId: number) => {
    const index = selectedCollections.value.indexOf(collectionId);
    if (index === -1) {
        selectedCollections.value.push(collectionId);
    } else {
        selectedCollections.value.splice(index, 1);
    }
};

// Form submission
const submit = () => {
    if (!form.file) {
        toast.error('Please select a file to upload.');
        return;
    }

    // Add selected collections to form data
    const formData = {
        ...form.data(),
        collections: selectedCollections.value
    };

    form.transform((data) => ({
        ...data,
        collections: selectedCollections.value
    })).post('/files', {
        onSuccess: () => {
            toast.success('File uploaded successfully!');
        },
        onError: (errors) => {
            console.error('Upload failed:', errors);
            toast.error('Failed to upload file. Please try again.');
        }
    });
};
</script>

<template>
    <Head title="Upload File" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 bg-gradient">
            <!-- Header -->
            <div class="flex items-center justify-center gap-4 ">
                <h1 class="text-2xl font-bold welcome-banner py-2 px-10 animate-soft-bounce pixel-outline">Upload New File</h1>
            </div>

            <!-- Form -->
            <div class="flex justify-center p-6 self-center w-full bg-container border-[#680d00] border-8 rounded-md">
                <form @submit.prevent="submit" class="space-y-6 w-full max-w-xl">
                <!-- File Upload -->
                    <div class="space-y-2">
                        <label for="file" class="block text-sm font-medium text-[#fce085]">File</label>
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
                        <label for="name" class="block text-sm font-medium text-[#fce085]">File Name</label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="w-full rounded-md border border-input bg-[#FFF8F2]/80 px-3 py-2 text-sm text-[#333333] ring-offset-background"
                            placeholder="Enter a name for your file"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- File Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-[#fce085]">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-[#FFF8F2]/80 text-[#333333]  px-3 py-2 text-sm ring-offset-background resize-none"
                            placeholder="Enter a brief description of this file (optional)"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tags" class="block text-sm font-medium text-[#fce085]">Tags</label>
                        <TagInput
                            v-model="form.tags"
                            :existing-tags="allTags || []"
                        />
                        <p class="text-xs text-muted-foreground">
                            Add tags to categorize your file. You can create new tags or select existing ones.
                        </p>
                    </div>

                    <!-- Collections -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-foreground">Collections</label>
                        <div class="flex flex-col gap-2">
                            <div
                                v-for="collection in userCollections"
                                :key="collection.id"
                                class="flex items-center gap-3 rounded-md border border-input p-3 text-sm cursor-pointer hover:bg-accent transition-colors"
                                :class="{ 'bg-accent': selectedCollections.includes(collection.id) }"
                                @click="toggleCollection(collection.id)"
                            >
                                <input
                                    type="checkbox"
                                    :id="`collection-${collection.id}`"
                                    :checked="selectedCollections.includes(collection.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <label
                                    :for="`collection-${collection.id}`"
                                    class="flex-1 font-medium text-foreground"
                                >
                                    {{ collection.name }}
                                </label>
                                <span class="text-xs text-muted-foreground">
                                    {{ collection.file_count }} file(s)
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Select collections to organize your file. You can create new collections in the Collections page.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-2">
                        <Link
                            href="/files"
                            class="inline-flex items-center justify-center rounded-md bg-red-500 hover:bg-red-600 px-4 py-2 text-sm font-medium text-foreground pixel-outline broder-border border-2 duration-300"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-md bg-[#3aa035] px-4 py-2 text-sm font-medium hover:bg-[#3aa035]/90 disabled:opacity-50 disabled:cursor-not-allowed border-border border-2 pixel-outline"
                            :disabled="form.processing || !form.file"
                        >
                            <UploadIcon v-if="!form.processing" class="h-4 w-4 pixel-outline-icon" />
                            {{ form.processing ? 'Uploading...' : 'Upload File' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
