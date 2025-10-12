<script setup lang="ts">
import CollectionModal from '@/components/CollectionModal.vue';
import TagInput from '@/components/TagInput.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Tag } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileIcon, UploadIcon, XIcon, FolderIcon } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { toast } from 'vue-sonner';
import axios from 'axios';

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

// Initialize form with proper typing for multiple files
const form = useForm({
    files: [] as File[],
    description: '',
    tags: [],
    verified: false,
});

// File upload reference and state
const fileInputRef = ref<HTMLInputElement | null>(null);
const selectedFiles = ref<File[]>([]);
const fileNames = ref<string[]>([]);
const isDragOver = ref(false);
const userCollections = ref<Collection[]>([]);
const selectedCollections = ref<number[]>([]);
const showCollectionModal = ref(false);
const tempCollections = ref<number[]>([]);
const showCreateNew = ref(false);
const newCollectionName = ref('');
const isCreating = ref(false);

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

// Handle multiple file upload
const handleFileUpload = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
        const files = Array.from(input.files);
        addFiles(files);
    }
};

// Add files to the selection
const addFiles = (files: File[]) => {
    // Filter out files that are already selected
    const newFiles = files.filter(file => 
        !selectedFiles.value.some(existingFile => 
            existingFile.name === file.name && existingFile.size === file.size
        )
    );
    
    selectedFiles.value.push(...newFiles);
    // Initialize names for new files
    newFiles.forEach(file => {
        fileNames.value.push(file.name.replace(/\.[^/.]+$/, ''));
    });
    form.files = [...selectedFiles.value];
};

// Remove a file from selection
const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
    fileNames.value.splice(index, 1);
    form.files = [...selectedFiles.value];
};

// Update file name
const updateFileName = (index: number, name: string) => {
    fileNames.value[index] = name;
};

// Collection modal functions
const openCollectionModal = () => {
    tempCollections.value = [...selectedCollections.value];
    showCollectionModal.value = true;
};

const closeCollectionModal = () => {
    showCollectionModal.value = false;
    showCreateNew.value = false;
    newCollectionName.value = '';
};

const onCollectionSuccess = (message?: string) => {
    selectedCollections.value = [...tempCollections.value];
    toast.success(message || 'Collections updated successfully!');
    closeCollectionModal();
};

// Toggle collection and auto-save selection
const toggleTempCollection = (collectionId: number) => {
    const index = tempCollections.value.indexOf(collectionId);
    if (index === -1) {
        tempCollections.value.push(collectionId);
    } else {
        tempCollections.value.splice(index, 1);
    }
    
    // Automatically save the selection
    selectedCollections.value = [...tempCollections.value];
};

// Fetch user's collections
const fetchUserCollections = async () => {
    try {
        const response = await axios.get('/user/collections');
        userCollections.value = response.data;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

// Create new collection
const createNewCollection = async () => {
    if (!newCollectionName.value.trim()) return;

    isCreating.value = true;
    try {
        const response = await axios.post('/collections', {
            name: newCollectionName.value.trim(),
            is_public: false,
        });

        // Add the new collection to the list and select it
        const newCollection = {
            id: response.data.id,
            name: response.data.name,
            file_count: 0,
            is_public: false,
        };
        
        userCollections.value.push(newCollection);
        toggleTempCollection(response.data.id); // Ensure it's selected
        
        console.log('Created and selected collection:', newCollection.name, 'ID:', response.data.id);
        console.log('tempCollections now contains:', tempCollections.value);

        // Reset form
        showCreateNew.value = false;
        newCollectionName.value = '';
        
        toast.success('Collection created successfully!');
    } catch (error) {
        console.error('Failed to create collection:', error);
        let errorMessage = 'Unknown error occurred';
        
        if (axios.isAxiosError(error)) {
            if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            } else if (error.response?.data?.errors) {
                // Handle validation errors
                const errors = Object.values(error.response.data.errors).flat();
                errorMessage = errors.join(', ');
            } else {
                errorMessage = `HTTP ${error.response?.status}: ${error.response?.statusText}`;
            }
        } else if (error instanceof Error) {
            errorMessage = error.message;
        }
        
        toast.error(`Failed to create collection: ${errorMessage}`);
    } finally {
        isCreating.value = false;
    }
};

// Initialize collections on component mount
onMounted(() => {
    fetchUserCollections();
});

// Handle file dragover
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = true;
};

// Handle drag leave
const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
};

// Handle file drop
const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
    if (event.dataTransfer && event.dataTransfer.files.length > 0) {
        const files = Array.from(event.dataTransfer.files);
        addFiles(files);
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



// Form submission for multiple files
const submit = () => {
    if (!form.files || form.files.length === 0) {
        toast.error('Please select at least one file to upload.');
        return;
    }

    // Upload files sequentially
    uploadFiles();
};

// Upload multiple files with shared metadata
const uploadFiles = async () => {
    const totalFiles = form.files.length;
    let successCount = 0;
    let errorCount = 0;
    let lastUploadedFileId: number | null = null;

    for (let i = 0; i < form.files.length; i++) {
        const file = form.files[i];
        
        // Create individual form for each file
        const fileForm = useForm({
            name: fileNames.value[i] || file.name.replace(/\.[^/.]+$/, ''), // Use custom name or fallback
            description: form.description,
            file: file,
            tags: form.tags,
            collections: selectedCollections.value,
        });

        try {
            await new Promise<void>((resolve, reject) => {
                fileForm.post('/files', {
                    onSuccess: (page: any) => {
                        console.log(`Successfully uploaded: ${file.name}`);
                        console.log('Upload response:', page);
                        
                        // For multi-file uploads, check if we got file ID in flash data
                        if (page.props?.flash?.uploaded_file_id) {
                            lastUploadedFileId = page.props.flash.uploaded_file_id;
                            console.log('Captured file ID from flash:', lastUploadedFileId);
                        }
                        // Handle redirect response for single file uploads
                        else if (page.url && page.url.includes('/files/')) {
                            const match = page.url.match(/\/files\/(\d+)/);
                            if (match) {
                                lastUploadedFileId = parseInt(match[1]);
                                console.log('Captured file ID from URL:', lastUploadedFileId);
                            }
                        }
                        
                        successCount++;
                        resolve();
                    },
                    onError: (errors) => {
                        console.error('Upload failed for', file.name, ':', errors);
                        errorCount++;
                        resolve(); // Continue with next file even if this one fails
                    },
                    headers: {
                        'X-Multi-File-Upload': totalFiles > 1 ? 'true' : 'false'
                    }
                });
            });
        } catch (error) {
            errorCount++;
        }
    }

    // Show final result
    if (successCount === totalFiles) {
        toast.success(`All ${totalFiles} files uploaded successfully!`);
        // Reset form
        selectedFiles.value = [];
        fileNames.value = [];
        form.files = [];
        form.description = '';
        form.tags = [];
        selectedCollections.value = [];
        
        // Redirect based on number of files uploaded
        setTimeout(() => {
            if (totalFiles === 1 && lastUploadedFileId) {
                // Single file: redirect to the file page
                window.location.href = `/files/${lastUploadedFileId}`;
            } else {
                // Multiple files: redirect to MyFiles with pending filter and sort by created date
                window.location.href = '/myfiles?sort=created_at&direction=desc';
            }
        }, 1500);
    } else if (successCount > 0) {
        toast.success(`${successCount} files uploaded successfully, ${errorCount} failed.`);
        // Always redirect to MyFiles with pending filter for partial success
        setTimeout(() => {
            window.location.href = '/myfiles?pending=true&sort=created_at&direction=desc';
        }, 2000);
    } else {
        toast.error('All file uploads failed. Please try again.');
    }
};
</script>

<template>
    <Head title="Upload File" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gradient flex min-h-screen flex-col gap-6 p-6">
            <!-- Header -->
            <div class="mx-auto flex max-w-md items-center justify-center gap-4">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline px-10 py-2 text-center text-2xl font-bold">Upload New File</h1>
            </div>

            <!-- Form -->
            <div class="bg-container flex w-full justify-center self-center rounded-md border-8 border-[#680d00] p-6">
                <form @submit.prevent="submit" class="w-full max-w-xl space-y-6">
                    <!-- Multiple File Upload -->
                    <div class="space-y-2">
                        <label for="files" class="pixel-outline block text-sm font-medium">Files</label>
                        <div
                            class="hover:border-primary flex cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-yellow-300 bg-black/50 p-6 transition-colors"
                            :class="{ 
                                'border-primary bg-primary/5': selectedFiles.length > 0,
                                'border-green-400 bg-green-500/10': isDragOver
                            }"
                            @click="fileInputRef?.click()"
                            @dragover="handleDragOver"
                            @dragleave="handleDragLeave"
                            @drop="handleDrop"
                        >
                            <input 
                                type="file" 
                                id="files" 
                                ref="fileInputRef" 
                                class="hidden" 
                                multiple
                                accept=".pdf,.doc,.docx,.pptx,.txt,.xlsx"
                                @change="handleFileUpload" 
                            />

                            <div v-if="selectedFiles.length === 0" class="flex flex-col items-center gap-3">
                                <div class="bg-primary/10 rounded-full p-4">
                                    <UploadIcon class="text-primary h-6 w-6" />
                                </div>
                                <div class="text-center">
                                    <p class="pixel-outline text-sm font-medium">Click to upload or drag and drop multiple files</p>
                                    <p class="text-muted-foreground pixel-outline mt-1 text-xs">PDF, DOC, DOCX, PPTX, TXT, XLSX (Max 25MB each)</p>
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center gap-3 w-full">
                                <div class="rounded-full bg-green-500/10 p-4">
                                    <FileIcon class="h-6 w-6 text-green-500" />
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium">{{ selectedFiles.length }} file(s) selected</p>
                                    <p class="text-muted-foreground mt-1 text-xs">Click to add more files</p>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Files List -->
                        <div v-if="selectedFiles.length > 0" class="space-y-2 max-h-60 overflow-y-auto">
                            <div 
                                v-for="(file, index) in selectedFiles" 
                                :key="`${file.name}-${file.size}`"
                                class="bg-black/30 border border-yellow-300/50 rounded-md p-3"
                            >
                                <div class="flex items-start gap-2">
                                    <FileIcon class="h-4 w-4 text-green-500 mt-0.5 flex-shrink-0" />
                                    <div class="flex-1 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-gray-400">{{ file.name }} ({{ formatFileSize(file.size) }})</span>
                                            <button
                                                type="button"
                                                @click="removeFile(index)"
                                                class="text-red-400 hover:text-red-300 p-1 rounded-full hover:bg-red-400/20"
                                            >
                                                <XIcon class="h-3 w-3" />
                                            </button>
                                        </div>
                                        <input
                                            type="text"
                                            :value="fileNames[index] || file.name.replace(/\.[^/.]+$/, '')"
                                            @input="updateFileName(index, ($event.target as HTMLInputElement).value)"
                                            class="w-full rounded border border-yellow-300/50 bg-black/50 px-2 py-1 text-sm text-white placeholder:text-white/50"
                                            placeholder="File name"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.errors.files" class="mt-1 text-xs text-red-500">
                            {{ form.errors.files }}
                        </div>
                    </div>

                    <!-- File Description -->
                    <div class="space-y-2">
                        <label for="description" class="pixel-outline block text-sm font-medium">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="ring-offset-background pixel-outline w-full resize-none rounded-md border border-yellow-300 bg-black/50 px-3 py-2 text-sm text-white"
                            placeholder="Enter a brief description of this file(s) (optional)"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tags" class="pixel-outline block text-sm font-medium">Tags</label>
                        <TagInput v-model="form.tags" :existing-tags="allTags || []" />
                        <p class="text-muted-foreground pixel-outline text-xs">
                            Add tags to categorize your file. You can create new tags or select existing ones.
                        </p>
                    </div>

                    <!-- Collections -->
                    <div class="space-y-2">
                        <label class="text-foreground pixel-outline block text-sm font-medium">Collections</label>
                        <button
                            type="button"
                            @click="openCollectionModal"
                            class="pixel-outline flex w-full items-center justify-center gap-2 rounded-md border-2 border-yellow-300 bg-black/50 px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-black/70"
                        >
                            <FolderIcon class="h-4 w-4" />
                            <span v-if="selectedCollections.length === 0">Select Collections</span>
                            <span v-else>{{ selectedCollections.length }} Collection(s) Selected</span>
                        </button>
                        
                        <!-- Selected Collections Display -->
                        <div v-if="selectedCollections.length > 0" class="space-y-1">
                            <p class="text-xs font-medium text-yellow-400 pixel-outline">Selected Collections:</p>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="collectionId in selectedCollections"
                                    :key="collectionId"
                                    class="inline-flex items-center rounded bg-yellow-500/20 px-2 py-1 text-xs text-yellow-300 border border-yellow-500/30"
                                >
                                    {{ userCollections.find(c => c.id === collectionId)?.name || 'Unknown' }}
                                </span>
                            </div>
                        </div>
                        
                        <p class="text-muted-foreground pixel-outline text-xs">
                            Select collections to organize your files. You can create new collections from the modal.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-2">
                        <Link
                            href="/files"
                            class="text-foreground pixel-outline broder-border inline-flex items-center justify-center rounded-md border-2 bg-red-500 px-4 py-2 text-sm font-medium duration-300 hover:bg-red-600"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="border-border pixel-outline inline-flex items-center justify-center gap-1.5 rounded-md border-2 bg-[#3aa035] px-4 py-2 text-sm font-medium hover:bg-[#3aa035]/90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing || selectedFiles.length === 0"
                        >
                            <UploadIcon v-if="!form.processing" class="pixel-outline-icon h-4 w-4" />
                            {{ form.processing ? 'Uploading...' : selectedFiles.length === 1 ? 'Upload File' : `Upload ${selectedFiles.length} Files` }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Collection Selection Modal -->
        <div v-if="showCollectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
            <div class="bg-container w-full max-w-lg rounded-xl border-2 border-[#ffd700] p-6 shadow-[8px_8px_0px_rgba(0,0,0,0.8)]">
                <h2 class="pixel-outline mb-6 text-xl font-bold text-[#ffd700]">Select Collections</h2>

                <!-- Selection Mode -->
                <div v-if="!showCreateNew" class="mb-6 space-y-4">
                    <p class="text-sm text-[#FFF8DC]/80">
                        Select collections where your files will be added after upload.
                    </p>

                    <div v-if="userCollections.length === 0" class="py-10 text-center">
                        <p class="text-sm text-[#FFF8DC]/60">No collections found. Create your first collection below.</p>
                    </div>

                    <div v-else class="max-h-60 space-y-2 overflow-y-auto">
                        <div
                            v-for="collection in userCollections"
                            :key="collection.id"
                            class="flex cursor-pointer items-center justify-between rounded-md border p-3 transition-colors"
                            :class="[
                                tempCollections.includes(collection.id)
                                    ? 'border-[#ffd700] bg-[#a85a47] shadow-lg'
                                    : 'border-[#0c0a03] bg-[#28231d]/50 hover:bg-[#28231d]',
                            ]"
                            @click="toggleTempCollection(collection.id)"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p
                                        class="text-sm font-medium"
                                        :class="tempCollections.includes(collection.id) ? 'text-white' : 'text-[#FFF8DC]'"
                                    >
                                        {{ collection.name }}
                                    </p>
                                    <span
                                        v-if="tempCollections.includes(collection.id)"
                                        class="inline-flex items-center rounded-full bg-[#ffd700] px-2 py-1 text-xs font-medium text-[#0c0a03]"
                                    >
                                        Selected
                                    </span>
                                </div>
                                <p class="text-xs" :class="tempCollections.includes(collection.id) ? 'text-[#FFF8DC]/80' : 'text-[#FFF8DC]/60'">
                                    {{ collection.file_count }} files
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create New Mode -->
                <div v-if="showCreateNew" class="mb-6">
                    <p class="mb-3 text-sm text-[#FFF8DC]/80">
                        Create a new collection for organizing your files.
                    </p>
                    
                    <!-- Show currently selected collections -->
                    <div v-if="tempCollections.length > 0" class="mb-3 p-2 bg-[#0c0a03] rounded border border-[#ffd700]/30">
                        <p class="text-xs text-[#ffd700] mb-1">Currently Selected:</p>
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-for="collectionId in tempCollections"
                                :key="collectionId"
                                class="inline-flex items-center rounded bg-[#ffd700]/20 px-2 py-1 text-xs text-[#ffd700]"
                            >
                                {{ userCollections.find(c => c.id === collectionId)?.name || `ID: ${collectionId}` }}
                            </span>
                        </div>
                    </div>
                    
                    <label for="new-collection" class="mb-3 block text-sm font-medium text-white/80">Collection Name</label>
                    <input
                        id="new-collection"
                        v-model="newCollectionName"
                        placeholder="Enter collection name"
                        class="pixel-outline placeholder:[#FFF8DC]/60 w-full rounded-lg border-2 border-[#0c0a03] bg-[#28231d] px-3 py-2 text-[#FFF8DC] focus:border-[#ffd700] focus:outline-none"
                        @keydown.enter="createNewCollection"
                        :disabled="isCreating"
                    />
                </div>

                <div class="flex justify-between gap-3">
                    <button
                        @click="showCreateNew = !showCreateNew"
                        class="pixel-outline border-2 border-[#ffd700] bg-[#0c0a03] px-4 py-2 text-[#FFF8DC] shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#a85a47] hover:text-white hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                    >
                        {{ showCreateNew ? '← Back to Selection' : '+ Create New' }}
                    </button>
                    
                    <div class="flex gap-2">
                        <button
                            @click="closeCollectionModal"
                            class="pixel-outline border-2 border-[#0c0a03] bg-[#a85a47] px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#8d4a3a] hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                        >
                            Close
                        </button>

                        <!-- Create Collection Button -->
                        <button
                            v-if="showCreateNew"
                            :disabled="isCreating || !newCollectionName.trim()"
                            @click="createNewCollection"
                            class="pixel-outline border-2 border-[#0c0a03] bg-[#28231d] px-4 py-2 text-[#FFF8DC] shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#0c0a03] hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ isCreating ? 'Creating...' : 'Create & Select' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
