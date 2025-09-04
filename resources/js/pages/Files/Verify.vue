<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type File } from '@/types';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { CheckCircleIcon, FileIcon } from 'lucide-vue-next';

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

const showCollectionModal = ref(false);
const selectedFileForCollection = ref<File | null>(null);
const userCollections = ref<Collection[]>([]);
const selectedCollection = ref<number | null>(null);

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

const verifyFile = (fileId: number) => {
    router.patch(route('files.verify.update', fileId), {}, {
        onSuccess: () => {
            toast.success('File verified successfully!');
            router.reload(); // Refresh the file list after verification
        },
        onError: () => {
            toast.error('Failed to verify the file. Please try again.');
        },
    });
};

// Fetch user's collections for adding files
const fetchUserCollections = async () => {
    try {
        const response = await fetch('/api/user/collections');
        const data = await response.json();
        userCollections.value = data;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

const openCollectionModal = (file: File) => {
    selectedFileForCollection.value = file;
    selectedCollection.value = null;
    showCollectionModal.value = true;
    fetchUserCollections();
};

const addToCollection = async () => {
    if (!selectedFileForCollection.value || !selectedCollection.value) return;

    try {
        await router.post(route('collections.add-file', selectedCollection.value), {
            file_id: selectedFileForCollection.value.id
        }, {
            preserveScroll: true,
            onSuccess: () => {
                showCollectionModal.value = false;
                selectedFileForCollection.value = null;
                selectedCollection.value = null;
                toast.success('File added to collection successfully!');
            },
            onError: (errors) => {
                if (errors.file) {
                    toast.error(errors.file);
                } else {
                    toast.error('Failed to add file to collection');
                }
            }
        });
    } catch (error) {
        toast.error('Failed to add file to collection');
    }
};
</script>

<template>
    <Head title="Verify Files" />
    <AppLayout>
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Verify Files</h1>
                <p class="text-sm text-muted-foreground">
                    Review and verify uploaded files for quality and accuracy
                </p>
            </div>

            <!-- Empty State -->
            <div v-if="props.files.data.length === 0" class="text-center py-12">
                <div class="mx-auto max-w-md">
                    <CheckCircleIcon class="mx-auto h-12 w-12 text-green-500" />
                    <h3 class="mt-2 text-sm font-medium text-foreground">All files verified</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        There are no unverified files at the moment.
                    </p>
                    <div class="mt-6">
                        <Link
                            href="/files"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90"
                        >
                            Browse All Files
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Files Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="file in props.files.data"
                    :key="file.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
                >
                    <!-- File Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <FileIcon class="h-8 w-8 text-blue-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ file.name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    by {{ file.user.first_name }} {{ file.user.last_name }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
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
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                            >
                                {{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <!-- File Metadata -->
                    <div class="mb-4 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <div>Uploaded: {{ new Date(file.created_at).toLocaleDateString() }}</div>
                        <div>ID: {{ file.id }}</div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between space-x-2">
                        <div class="flex space-x-2">
                            <Link
                                :href="`/files/${file.id}`"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                View
                            </Link>
                            <button
                                @click="openCollectionModal(file)"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Add to Collection
                            </button>
                        </div>
                        <button
                            @click="verifyFile(file.id)"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            <CheckCircleIcon class="h-4 w-4 mr-1.5" />
                            Verify
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.files.links && props.files.links.length > 3" class="mt-8">
                <nav class="flex items-center justify-between">
                    <div class="flex justify-between flex-1 sm:hidden">
                        <Link
                            v-for="link in props.files.links"
                            :key="link.label"
                            :href="link.url"
                            v-html="link.label"
                            :class="link.active
                                ? 'relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default'
                                : 'relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50'"
                        />
                    </div>
                </nav>
            </div>
        </div>

        <!-- Add to Collection Modal -->
        <div v-if="showCollectionModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCollectionModal = false"></div>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Add File to Collection
                        </h3>
                        <div v-if="userCollections.length === 0" class="text-center py-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No collections found.</p>
                            <Link
                                href="/collections/create"
                                class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500"
                            >
                                Create your first collection
                            </Link>
                        </div>
                        <div v-else class="space-y-2 max-h-64 overflow-y-auto">
                            <div
                                v-for="collection in userCollections"
                                :key="collection.id"
                                class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                :class="{
                                    'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-700': selectedCollection === collection.id,
                                    'bg-white dark:bg-gray-800': selectedCollection !== collection.id
                                }"
                                @click="selectedCollection = collection.id"
                            >
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collection.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ collection.file_count }} file(s) • {{ collection.is_public ? 'Public' : 'Private' }}
                                    </p>
                                </div>
                                <div v-if="selectedCollection === collection.id">
                                    <CheckCircleIcon class="h-5 w-5 text-blue-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="addToCollection"
                            :disabled="!selectedCollection"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Add to Collection
                        </button>
                        <button
                            @click="showCollectionModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
