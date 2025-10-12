<script setup lang="ts">
import CollectionModal from '@/components/CollectionModal.vue';
import TagInput from '@/components/TagInput.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Tag } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileIcon, UploadIcon, XIcon, FolderIcon, Loader2Icon } from 'lucide-vue-next';
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

// Duplicate file detection
const duplicateFiles = ref<Map<string, DuplicateFileInfo>>(new Map());
const filesToUpdate = ref<Map<string, DuplicateFileInfo>>(new Map());
const checkingDuplicates = ref(false);
const fileHashes = ref<Map<string, string>>(new Map()); // Track hashes of selected files
const isUploading = ref(false); // Track upload process state
const uploadProgress = ref({ current: 0, total: 0 }); // Track upload progress

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

interface DuplicateFileInfo {
    existingFile: {
        id: number;
        name: string;
        description: string;
        tags: any[];
        url: string;
    };
    fileName: string;
    fileIndex: number;
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
const addFiles = async (files: File[]) => {
    // First filter: Remove files that are already selected by name and size
    let newFiles = files.filter(file => 
        !selectedFiles.value.some(existingFile => 
            existingFile.name === file.name && existingFile.size === file.size
        )
    );
    
    if (newFiles.length === 0) return;
    
    // Second filter: Remove files that have the same hash as existing files
    const filteredFiles = [];
    const skippedFiles = [];
    
    for (const file of newFiles) {
        // Calculate hash for the new file
        const hashResult = await calculateFileHash(file);
        let fileHash = hashResult.hash;
        
        // If client-side hashing failed, create a fallback identifier
        if (!fileHash) {
            try {
                const buffer = await file.arrayBuffer();
                const uint8Array = new Uint8Array(buffer);
                // Create a more robust fallback hash
                const sampleBytes = Math.min(1024, uint8Array.length);
                let hashSum = file.size;
                for (let i = 0; i < sampleBytes; i += Math.max(1, Math.floor(sampleBytes / 32))) {
                    hashSum = (hashSum * 31 + uint8Array[i]) >>> 0;
                }
                fileHash = `fallback_${hashSum}_${file.size}`;
            } catch (error) {
                fileHash = `basic_${file.name}_${file.size}_${file.lastModified}`;
            }
        }
        
        // Get all existing hashes to check against
        const existingHashes = Array.from(fileHashes.value.values());
        let isDuplicateOfExisting = false;
        
        // Check against already selected files by hash
        if (existingHashes.includes(fileHash)) {
            isDuplicateOfExisting = true;
        }
        
        // Check against files in duplicate queue by name
        if (duplicateFiles.value.has(file.name)) {
            isDuplicateOfExisting = true;
        }
        
        // Check against files in update queue by name
        if (filesToUpdate.value.has(file.name)) {
            isDuplicateOfExisting = true;
        }
        
        // Also check if any files in the update queue have the same hash
        for (const [updateFileName, updateInfo] of filesToUpdate.value.entries()) {
            // Try to get the hash we might have stored for this file
            const updateFileHash = fileHashes.value.get(updateFileName);
            if (updateFileHash && updateFileHash === fileHash) {
                isDuplicateOfExisting = true;
                break;
            }
        }
        
        if (isDuplicateOfExisting) {
            skippedFiles.push(file.name);
        } else {
            filteredFiles.push(file);
            // Store the hash for this file
            fileHashes.value.set(file.name, fileHash);
        }
    }
    
    // Show notification for skipped files
    if (skippedFiles.length > 0) {
        toast.info(`Skipped ${skippedFiles.length} file(s) that are already being handled: ${skippedFiles.join(', ')}`);
    }
    
    if (filteredFiles.length === 0) return;
    
    // Check for duplicates against database
    await checkForDuplicates(filteredFiles);
    
    selectedFiles.value.push(...filteredFiles);
    // Initialize names for new files
    filteredFiles.forEach(file => {
        fileNames.value.push(file.name.replace(/\.[^/.]+$/, ''));
    });
    form.files = [...selectedFiles.value];
};

// Remove a file from selection
const removeFile = (index: number) => {
    const removedFile = selectedFiles.value[index];
    
    selectedFiles.value.splice(index, 1);
    fileNames.value.splice(index, 1);
    form.files = [...selectedFiles.value];
    
    // Remove from duplicates if it exists
    if (removedFile) {
        duplicateFiles.value.delete(removedFile.name);
        filesToUpdate.value.delete(removedFile.name);
        // Clean up hash tracking
        fileHashes.value.delete(removedFile.name);
    }
    
    // Update file indices in remaining duplicates to account for the removed file
    for (const [fileName, duplicate] of duplicateFiles.value.entries()) {
        if (duplicate.fileIndex > index) {
            duplicate.fileIndex = duplicate.fileIndex - 1;
        }
    }
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

// Calculate SHA256 hash of a file using Web Crypto API


// Calculate file hash client-side with fallback to backend
const calculateFileHash = async (file: File): Promise<{ hash: string; method: 'client' | 'backend' }> => {
    // Try Web Crypto API first (works on localhost and HTTPS)
    try {
        if (window.crypto && window.crypto.subtle) {
            const buffer = await file.arrayBuffer();
            const hashBuffer = await window.crypto.subtle.digest('SHA-256', buffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const hash = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            return { hash, method: 'client' };
        }
    } catch (error) {
        console.warn('Web Crypto API failed, will use backend fallback:', error);
    }
    
    // Fallback to backend calculation
    return { hash: '', method: 'backend' };
};

// Check for duplicate files
const checkForDuplicates = async (files: File[]) => {
    checkingDuplicates.value = true;
    
    // Only remove duplicates for files being checked (not all duplicates)
    files.forEach(file => {
        duplicateFiles.value.delete(file.name);
    });
    
    try {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Try client-side hash calculation first
            const hashResult = await calculateFileHash(file);
            
            let response;
            
            if (hashResult.method === 'client' && hashResult.hash) {
                // Use client-side hash for duplicate check
                response = await axios.get(`/files/check-duplicate/${hashResult.hash}`);
            } else {
                // Fallback to backend hash calculation
                const formData = new FormData();
                formData.append('file', file);
                formData.append('name', file.name);
                
                response = await axios.post('/files/check-duplicate', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    }
                });
            }
            
            if (response.data.exists) {
                duplicateFiles.value.set(file.name, {
                    existingFile: response.data.file,
                    fileName: file.name,
                    fileIndex: selectedFiles.value.length + i
                });
            }
        }
    } catch (error) {
        console.error('Error checking for duplicates:', error);
    } finally {
        checkingDuplicates.value = false;
    }
};

// Handle duplicate file action (mark for update or skip)
const handleDuplicateFile = (fileName: string, action: 'update' | 'skip') => {
    const duplicate = duplicateFiles.value.get(fileName);
    if (!duplicate) return;
    
    if (action === 'update') {
        // Mark file for update during upload process
        filesToUpdate.value.set(fileName, duplicate);
        toast.info(`${duplicate.existingFile.name} will be updated during upload.`);
        // Keep the hash in tracking when moving to update queue
        // (hash will remain in fileHashes map to prevent re-adding same file)
    } else {
        // Skip action - just show confirmation
        toast.info(`Skipped duplicate file: ${fileName}`);
        // For skip action, we'll remove the hash when the file is removed
    }
    
    // Remove from duplicates list
    duplicateFiles.value.delete(fileName);
    
    // Remove file from selected files for both update and skip actions
    const fileIndex = selectedFiles.value.findIndex(f => f.name === fileName);
    console.log(`Handling duplicate file: ${fileName}, action: ${action}, fileIndex: ${fileIndex}, selectedFiles before:`, selectedFiles.value.map(f => f.name));
    
    if (fileIndex !== -1) {
        // For update action, don't remove hash (keep it to prevent re-adding)
        // For skip action, removeFile will clean up the hash
        if (action === 'update') {
            const removedFile = selectedFiles.value[fileIndex];
            selectedFiles.value.splice(fileIndex, 1);
            fileNames.value.splice(fileIndex, 1);
            form.files = [...selectedFiles.value];
            
            console.log(`Removed file for update: ${fileName}, selectedFiles after:`, selectedFiles.value.map(f => f.name));
            
            // Update file indices in remaining duplicates
            for (const [fileName, duplicate] of duplicateFiles.value.entries()) {
                if (duplicate.fileIndex > fileIndex) {
                    duplicate.fileIndex = duplicate.fileIndex - 1;
                }
            }
            // Note: We intentionally keep the hash in fileHashes for update files
        } else {
            // For skip, use the normal removeFile which cleans up everything
            removeFile(fileIndex);
            console.log(`Removed file for skip: ${fileName}, selectedFiles after:`, selectedFiles.value.map(f => f.name));
        }
    } else {
        console.error(`Could not find file ${fileName} in selectedFiles for ${action}. Available files:`, selectedFiles.value.map(f => f.name));
    }
};

// Handle all duplicate files at once
const handleAllDuplicates = (action: 'update' | 'skip') => {
    const duplicateNames = Array.from(duplicateFiles.value.keys());
    
    if (action === 'update') {
        // Mark all duplicates for update during upload process
        duplicateNames.forEach(fileName => {
            const duplicate = duplicateFiles.value.get(fileName);
            if (duplicate) {
                filesToUpdate.value.set(fileName, duplicate);
            }
        });
        
        toast.info(`${duplicateNames.length} existing files will be updated during upload.`);
    } else {
        toast.info(`Skipping ${duplicateNames.length} duplicate files.`);
    }
    
    // Remove all processed duplicates from duplicates list
    duplicateNames.forEach(fileName => {
        duplicateFiles.value.delete(fileName);
    });
    
    // Remove all duplicate files from selection
    duplicateNames.forEach(fileName => {
        const fileIndex = selectedFiles.value.findIndex(f => f.name === fileName);
        if (fileIndex !== -1) {
            if (action === 'update') {
                // For update action, remove from selection but keep hash tracking
                const removedFile = selectedFiles.value[fileIndex];
                selectedFiles.value.splice(fileIndex, 1);
                fileNames.value.splice(fileIndex, 1);
                form.files = [...selectedFiles.value];
                
                // Update file indices in remaining duplicates
                for (const [remainingFileName, duplicate] of duplicateFiles.value.entries()) {
                    if (duplicate.fileIndex > fileIndex) {
                        duplicate.fileIndex = duplicate.fileIndex - 1;
                    }
                }
                // Keep the hash in fileHashes to prevent re-adding same file
            } else {
                // For skip action, remove everything including hash
                removeFile(fileIndex);
            }
        }
    });
};



// Get dynamic upload button text based on actions to be performed
const getUploadButtonText = () => {
    const uploadCount = selectedFiles.value.length;
    const updateCount = filesToUpdate.value.size;
    
    if (uploadCount > 0 && updateCount > 0) {
        return `Upload ${uploadCount} & Update ${updateCount}`;
    } else if (uploadCount > 0) {
        return uploadCount === 1 ? 'Upload File' : `Upload ${uploadCount} Files`;
    } else if (updateCount > 0) {
        return updateCount === 1 ? 'Update File' : `Update ${updateCount} Files`;
    }
    
    return 'Upload Files';
};

// Form submission for multiple files
const submit = () => {
    // Prevent multiple submissions
    if (isUploading.value) {
        return;
    }

    if ((!form.files || form.files.length === 0) && filesToUpdate.value.size === 0) {
        toast.error('Please select at least one file to upload or have files marked for update.');
        return;
    }

    // Check if there are unhandled duplicates
    if (duplicateFiles.value.size > 0) {
        toast.error('Please handle all duplicate files before uploading. Use "Update" or "Skip" for each duplicate.');
        return;
    }

    // Set uploading state and upload files sequentially
    isUploading.value = true;
    uploadFiles();
};

// Upload multiple files with shared metadata
const uploadFiles = async () => {
    const totalFiles = form.files.length;
    let successCount = 0;
    let errorCount = 0;
    let updateCount = 0;
    let lastUploadedFileId: number | null = null;

    // Initialize progress tracking
    const totalOperations = totalFiles + filesToUpdate.value.size;
    uploadProgress.value = { current: 0, total: totalOperations };

    console.log('Starting upload process with:', {
        filesToUpdate: Array.from(filesToUpdate.value.keys()),
        selectedFiles: selectedFiles.value.map(f => f.name),
        formFiles: form.files.map(f => f.name)
    });

    // First, handle any files marked for update
    if (filesToUpdate.value.size > 0) {
        console.log('Processing files for update:', Array.from(filesToUpdate.value.keys()));
        for (const [fileName, duplicate] of filesToUpdate.value.entries()) {
            try {
                // Extract tag names from tag objects
                const tagNames = form.tags.map((tag: any) => {
                    if (typeof tag === 'string') {
                        return tag;
                    }
                    if (tag && typeof tag === 'object' && tag.name) {
                        return tag.name;
                    }
                    return String(tag);
                });

                console.log(`Updating file ${fileName} with data:`, {
                    description: form.description,
                    tags: tagNames,
                    collections: selectedCollections.value,
                });

                await axios.put(`/files/${duplicate.existingFile.id}`, {
                    description: form.description,
                    tags: tagNames,
                    collections: selectedCollections.value,
                });
                updateCount++;
                uploadProgress.value.current++;
                console.log(`Successfully updated existing file: ${fileName}`);
            } catch (error) {
                console.error(`Failed to update ${fileName}:`, error);
                errorCount++;
                uploadProgress.value.current++;
            }
        }
    }

    // Then upload new files
    console.log('Processing files for upload:', form.files.map(f => f.name));
    for (let i = 0; i < form.files.length; i++) {
        const file = form.files[i];
        
        // Skip files that are in the update queue
        if (filesToUpdate.value.has(file.name)) {
            console.warn(`Skipping upload for ${file.name} as it's in the update queue`);
            continue;
        }
        
        console.log(`Starting upload for file: ${file.name}`);
        
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
                        uploadProgress.value.current++;
                        resolve();
                    },
                    onError: (errors) => {
                        console.error('Upload failed for', file.name, ':', errors);
                        errorCount++;
                        uploadProgress.value.current++;
                        resolve(); // Continue with next file even if this one fails
                    },
                    headers: {
                        'X-Multi-File-Upload': (totalFiles > 1 || filesToUpdate.value.size > 0) ? 'true' : 'false'
                    }
                });
            });
        } catch (error) {
            errorCount++;
            uploadProgress.value.current++;
        }
    }

    // Show final result
    const totalActions = successCount + updateCount;
    const hasMultipleActions = totalFiles > 1 || filesToUpdate.value.size > 0;
    
    if (totalActions > 0 && errorCount === 0) {
        let message = '';
        if (successCount > 0 && updateCount > 0) {
            message = `Successfully uploaded ${successCount} new files and updated ${updateCount} existing files!`;
        } else if (successCount > 0) {
            message = `All ${successCount} files uploaded successfully!`;
        } else if (updateCount > 0) {
            message = `All ${updateCount} existing files updated successfully!`;
        }
        toast.success(message);
        
        // Reset form
        selectedFiles.value = [];
        fileNames.value = [];
        form.files = [];
        form.description = '';
        form.tags = [];
        selectedCollections.value = [];
        filesToUpdate.value.clear(); // Clear the update queue
        fileHashes.value.clear(); // Clear hash tracking
        
        // Redirect based on number of actions
        setTimeout(() => {
            if (!hasMultipleActions && lastUploadedFileId) {
                // Single file upload: redirect to the file page
                window.location.href = `/files/${lastUploadedFileId}`;
            } else {
                // Multiple actions: redirect to MyFiles with pending filter and sort by created date
                window.location.href = '/myfiles?sort=updated_at&direction=desc';
            }
        }, 1500);
    } else if (totalActions > 0) {
        toast.success(`Completed ${totalActions} actions, ${errorCount} failed.`);
        // Always redirect to MyFiles with pending filter for partial success
        setTimeout(() => {
            window.location.href = '/myfiles?pending=true&sort=created_at&direction=desc';
        }, 2000);
    } else {
        toast.error('All actions failed. Please try again.');
    }
    
    // Reset uploading state and progress
    isUploading.value = false;
    uploadProgress.value = { current: 0, total: 0 };
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

                        <!-- Duplicate Files Warning -->
                        <div v-if="duplicateFiles.size > 0" class="space-y-2">
                            <div class="bg-orange-500/20 border border-orange-400/50 rounded-md p-3">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-orange-300">⚠️ Duplicate Files Detected ({{ duplicateFiles.size }})</h4>
                                    <div v-if="duplicateFiles.size > 1" class="flex gap-2">
                                        <button
                                            type="button"
                                            @click="handleAllDuplicates('update')"
                                            class="bg-blue-500/20 text-blue-300 px-3 py-1 rounded text-xs hover:bg-blue-500/30 transition-colors"
                                        >
                                            Update All
                                        </button>
                                        <button
                                            type="button"
                                            @click="handleAllDuplicates('skip')"
                                            class="bg-red-500/20 text-red-300 px-3 py-1 rounded text-xs hover:bg-red-500/30 transition-colors"
                                        >
                                            Skip All
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Info tip -->
                                <div class="bg-blue-500/20 border border-blue-400/30 rounded p-2 mb-3">
                                    <p class="text-xs text-blue-200">
                                        💡 <strong>Update</strong> will merge your current description, tags, and selected collections with the existing files (no duplicates added).
                                    </p>
                                </div>
                                
                                <div class="space-y-2">
                                    <div 
                                        v-for="[fileName, duplicate] in duplicateFiles" 
                                        :key="fileName"
                                        class="bg-black/30 border border-orange-400/30 rounded p-2"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <p class="text-xs text-orange-200 font-medium">{{ fileName }}</p>
                                                <p class="text-xs text-orange-300/80">
                                                    Already exists as: 
                                                    <Link 
                                                        :href="duplicate.existingFile.url" 
                                                        class="text-blue-400 hover:text-blue-300 underline"
                                                        target="_blank"
                                                    >
                                                        {{ duplicate.existingFile.name }}
                                                    </Link>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex gap-2 text-xs">
                                            <button
                                                type="button"
                                                @click="handleDuplicateFile(fileName, 'update')"
                                                class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded hover:bg-blue-500/30 transition-colors"
                                            >
                                                Update Existing
                                            </button>
                                            <button
                                                type="button"
                                                @click="handleDuplicateFile(fileName, 'skip')"
                                                class="bg-red-500/20 text-red-300 px-2 py-1 rounded hover:bg-red-500/30 transition-colors"
                                            >
                                                Skip This File
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Files Marked for Update -->
                        <div v-if="filesToUpdate.size > 0" class="space-y-2">
                            <div class="bg-green-500/20 border border-green-400/50 rounded-md p-3">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-medium text-green-300">
                                        📄 Files to Update During Upload ({{ filesToUpdate.size }})
                                    </h4>
                                </div>
                                
                                <div class="bg-green-500/20 border border-green-400/30 rounded p-2 mb-3">
                                    <p class="text-xs text-green-200">
                                        ✅ These existing files will be updated with your current description, tags, and collections when you upload.
                                    </p>
                                </div>
                                
                                <div class="space-y-2">
                                    <div 
                                        v-for="[fileName, duplicate] in filesToUpdate" 
                                        :key="fileName"
                                        class="bg-black/30 border border-green-400/30 rounded p-2"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="text-xs text-green-200 font-medium">{{ fileName }}</p>
                                                <p class="text-xs text-green-300/80">
                                                    Will update: 
                                                    <Link 
                                                        :href="duplicate.existingFile.url" 
                                                        class="text-blue-400 hover:text-blue-300 underline"
                                                        target="_blank"
                                                    >
                                                        {{ duplicate.existingFile.name }}
                                                    </Link>
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="() => { filesToUpdate.delete(fileName); fileHashes.delete(fileName); }"
                                                class="text-red-400 hover:text-red-300 p-1 rounded-full hover:bg-red-400/20"
                                                title="Remove from update queue"
                                            >
                                                <XIcon class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checking Duplicates Loading -->
                        <div v-if="checkingDuplicates" class="bg-blue-500/20 border border-blue-400/50 rounded-md p-3">
                            <div class="flex items-center gap-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-400 border-t-transparent"></div>
                                <span class="text-sm text-blue-300">Checking for duplicate files...</span>
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
                            :disabled="isUploading || (selectedFiles.length === 0 && filesToUpdate.size === 0) || duplicateFiles.size > 0"
                        >
                            <Loader2Icon v-if="isUploading" class="pixel-outline-icon h-4 w-4 animate-spin" />
                            <UploadIcon v-else class="pixel-outline-icon h-4 w-4" />
                            {{ 
                                isUploading ? `Uploading... (${uploadProgress.current}/${uploadProgress.total})` : 
                                duplicateFiles.size > 0 ? 'Handle Duplicates First' :
                                getUploadButtonText()
                            }}
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
