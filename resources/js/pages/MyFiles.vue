<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type Tag } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { EyeIcon, FileIcon, FolderIcon, PencilIcon, PlusIcon, StarIcon } from 'lucide-vue-next';
import { computed, onMounted, ref, watch, nextTick } from 'vue';
import { toast } from 'vue-sonner';

// Extend File type to include starring functionality
interface ExtendedFile extends File {
    is_starring?: boolean;
}

interface PageProps {
    files: {
        data: File[];
        meta?: {
            last_page: number;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
    };
    tags: Tag[];
    selectedTags: number[];
}

const props = defineProps<PageProps>();

// Files are already sorted from the backend, just use them directly
const sortedFiles = computed(() => {
    return props.files.data as ExtendedFile[];
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: route('home') },
    { title: 'My Files', href: route('myfiles') },
];

// Collection functionality
const showCollectionModal = ref(false);
const selectedFileForCollection = ref<File | null>(null);
const selectedCollection = ref<number | null>(null);
const userCollections = ref<Collection[]>([]);
const showCreateNewCollection = ref(false);
const newCollectionName = ref('');
const isCreatingCollection = ref(false);

// Filters for MyFiles - Initialize from URL parameters
const searchQuery = ref('');
const selectedTags = ref<number[]>(props.selectedTags || []);
const showStarredOnly = ref(false);
const showVerifiedOnly = ref(false);
const showPendingOnly = ref(false);
const showDeniedOnly = ref(false);
const allTags = ref<Tag[]>([]);

const sortOptions = ref([
    { value: 'name', label: 'Name' },
    { value: 'created_at', label: 'Created Date' },
    { value: 'star_count', label: 'Star Count' },
    { value: 'updated_at', label: 'Last Updated' },
]);
const selectedSort = ref('name');
const sortDirection = ref('asc');

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

// Fetch user's collections for adding files
const fetchUserCollections = async () => {
    try {
        const response = await axios.get('/user/collections');
        userCollections.value = response.data;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

const openCollectionModal = (file: File) => {
    selectedFileForCollection.value = file;
    selectedCollection.value = null;
    showCollectionModal.value = true;
    showCreateNewCollection.value = false;
    newCollectionName.value = '';
    fetchUserCollections();
};

const createNewCollection = async () => {
    if (!newCollectionName.value.trim()) return;

    isCreatingCollection.value = true;
    try {
        const response = await axios.post('/collections', {
            name: newCollectionName.value.trim(),
            is_public: false,
        });

        await fetchUserCollections();
        selectedCollection.value = response.data.id;
        showCreateNewCollection.value = false;
        newCollectionName.value = '';
        toast.success('Collection created successfully!');
    } catch (error) {
        toast.error('Failed to create collection');
    } finally {
        isCreatingCollection.value = false;
    }
};

const addToCollection = async () => {
    if (!selectedFileForCollection.value || !selectedCollection.value) return;

    try {
        await router.post(
            route('collections.add-file', selectedCollection.value),
            {
                file_id: selectedFileForCollection.value.id,
            },
            {
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
                },
            },
        );
    } catch (error) {
        toast.error('Failed to add file to collection');
    }
};

// Toggle star functionality for files
const toggleStar = async (file: ExtendedFile) => {
    if (file.is_starring) return;

    file.is_starring = true;

    try {
        await router.post(
            route('files.star', { file: file.id }),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Update the file state
                    file.is_starred = !file.is_starred;
                    file.star_count = file.is_starred ? (file.star_count || 0) + 1 : (file.star_count || 0) - 1;
                    file.is_starring = false;
                },
                onError: (errors) => {
                    file.is_starring = false;
                    toast.error('Failed to star/unstar file');
                },
            },
        );
    } catch (error) {
        file.is_starring = false;
        toast.error('Failed to star/unstar file');
    }
};

// Fetch tags for filtering
const fetchTags = async () => {
    try {
        const response = await axios.get(route('tags.index'));
        allTags.value = response.data;
    } catch (error) {
        console.error('Failed to fetch tags:', error);
    }
};

// Apply filters to the file list
const applyFilters = () => {
    try {
        router.get(
            route('myfiles'),
            {
                search: searchQuery.value,
                tags: selectedTags.value,
                starred: showStarredOnly.value,
                verified: showVerifiedOnly.value,
                pending: showPendingOnly.value,
                denied: showDeniedOnly.value,
                sort: selectedSort.value,
                direction: sortDirection.value,
            },
            { 
                preserveState: true,
                preserveScroll: true,
            },
        );
    } catch (error) {
        console.error('Error applying filters:', error);
    }
};

// Debounced search
let searchTimeout: number | null = null;

const debouncedSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

// Initialize on component mount
onMounted(() => {
    fetchTags();
    
    // Initialize filter values from URL if available
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('search')) {
        searchQuery.value = urlParams.get('search') || '';
    }
    if (urlParams.get('starred') === 'true') {
        showStarredOnly.value = true;
    }
    if (urlParams.get('verified') === 'true') {
        showVerifiedOnly.value = true;
    }
    if (urlParams.get('pending') === 'true') {
        showPendingOnly.value = true;
    }
    if (urlParams.get('denied') === 'true') {
        showDeniedOnly.value = true;
    }
    if (urlParams.get('sort')) {
        selectedSort.value = urlParams.get('sort') || 'name';
    }
    if (urlParams.get('direction')) {
        sortDirection.value = urlParams.get('direction') || 'asc';
    }

    // Set up watchers after component is mounted and values are initialized
    // Watch for search query changes
    watch(searchQuery, () => {
        console.log('Search watcher triggered:', searchQuery.value);
        debouncedSearch();
    });

    // Watch for filter changes - individual watchers for better debugging
    watch(showStarredOnly, (newVal) => {
        console.log('StarredOnly changed to:', newVal);
        applyFilters();
    });
    
    watch(showVerifiedOnly, (newVal) => {
        console.log('VerifiedOnly changed to:', newVal);
        applyFilters();
    });
    
    watch(showPendingOnly, (newVal) => {
        console.log('PendingOnly changed to:', newVal);
        applyFilters();
    });
    
    watch(showDeniedOnly, (newVal) => {
        console.log('DeniedOnly changed to:', newVal);
        applyFilters();
    });

    watch(selectedSort, (newVal) => {
        console.log('Sort changed to:', newVal);
        applyFilters();
    });

    watch(sortDirection, (newVal) => {
        console.log('Sort direction changed to:', newVal);
        applyFilters();
    });

    // Watch for tag selection changes
    watch(selectedTags, (newTags) => {
        console.log('Tags changed to:', newTags);
        applyFilters();
    }, { deep: true });
});
</script>

<template>
    <div class="dark:bg-[#161615]">
        <Head title="My Files" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="bg-lectica flex w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
                <!--Header Section-->
                <div
                    class="mb-4 flex w-full flex-col items-center justify-center gap-4 rounded-xl p-4 text-center sm:mb-6 sm:min-h-[150px] sm:flex-row sm:gap-6 sm:p-6 sm:text-left"
                >
                    <!--Icon-->
                    <div class="relative flex flex-col items-center gap-2">
                        <div
                            class="animate-floating flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-blue-500 to-purple-600 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] sm:h-28 sm:w-28 md:h-32 md:w-32"
                        >
                            <FileIcon class="h-10 w-10 text-white sm:h-14 sm:w-14 md:h-16 md:w-16" />
                        </div>
                    </div>
                    <!--Title-->
                    <div>
                        <h1 class="text-2xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-3xl">
                            My Files Archive
                        </h1>
                        <p class="text-lg text-white/90 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-xl">
                            Your personal collection of shared knowledge
                        </p>
                    </div>
                    <!--Action Button-->
                    <div class="sm:ml-auto">
                        <Link :href="route('files.create')">
                            <Button
                                class="font-pixel border-2 border-white bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:translate-y-[-2px] hover:bg-green-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] sm:px-6 sm:py-4 sm:text-base"
                            >
                                <PlusIcon class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                                <span class="hidden sm:inline">Add New File</span>
                                <span class="sm:hidden">Add</span>
                            </Button>
                        </Link>
                    </div>
                </div>
                <!--Divider-->
                <hr class="-mx-4 h-2 border-2 border-black bg-blue-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
            </div>

            <!--Main Content-->
            <div class="bg-gradient flex h-full flex-1 flex-col gap-4 px-4 pt-4 pb-0 lg:p-8">
                <!-- Search and Filters Section - MyFiles Specific -->
                <div class="mb-4 space-y-3 rounded-lg border-2 border-white/20 bg-black/30 p-3 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm sm:mb-6 sm:space-y-4 sm:rounded-xl sm:p-4 sm:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] lg:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="font-pixel text-sm font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-base lg:text-lg">
                            🔍 Manage My Files
                        </h3>
                        
                        <!-- Search Bar -->
                        <div class="flex flex-1 items-center gap-2 sm:max-w-md">
                            <Input
                                v-model="searchQuery"
                                placeholder="Search my files..."
                                class="flex-1 border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm placeholder:text-white/60 focus:border-yellow-400 sm:px-4"
                            />
                        </div>
                    </div>

                    <!-- Filter Row - MyFiles Specific -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <!-- Status Filters -->
                        <label class="flex items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm hover:bg-white/20 sm:gap-2 sm:px-3 sm:py-2 sm:text-sm">
                            <input
                                type="checkbox"
                                v-model="showStarredOnly"
                                class="h-3 w-3 rounded border-white/30 bg-white/10 text-yellow-400 focus:ring-yellow-400 sm:h-4 sm:w-4"
                            />
                            <StarIcon class="h-3 w-3 text-yellow-400 sm:h-4 sm:w-4" />
                            <span class="hidden sm:inline">Starred</span>
                        </label>
                        
                        <label class="flex items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm hover:bg-white/20 sm:gap-2 sm:px-3 sm:py-2 sm:text-sm">
                            <input
                                type="checkbox"
                                v-model="showVerifiedOnly"
                                class="h-3 w-3 rounded border-white/30 bg-white/10 text-green-400 focus:ring-green-400 sm:h-4 sm:w-4"
                            />
                            <svg class="h-3 w-3 text-green-400 sm:h-4 sm:w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="hidden sm:inline">Verified</span>
                        </label>

                        <label class="flex items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm hover:bg-white/20 sm:gap-2 sm:px-3 sm:py-2 sm:text-sm">
                            <input
                                type="checkbox"
                                v-model="showPendingOnly"
                                class="h-3 w-3 rounded border-white/30 bg-white/10 text-yellow-400 focus:ring-yellow-400 sm:h-4 sm:w-4"
                            />
                            <svg class="h-3 w-3 text-yellow-400 sm:h-4 sm:w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="hidden sm:inline">Pending</span>
                        </label>

                        <label class="flex items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm hover:bg-white/20 sm:gap-2 sm:px-3 sm:py-2 sm:text-sm">
                            <input
                                type="checkbox"
                                v-model="showDeniedOnly"
                                class="h-3 w-3 rounded border-white/30 bg-white/10 text-red-400 focus:ring-red-400 sm:h-4 sm:w-4"
                            />
                            <svg class="h-3 w-3 text-red-400 sm:h-4 sm:w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span class="hidden sm:inline">Denied</span>
                        </label>

                        <!-- Sort Controls - Right aligned -->
                        <div class="ml-auto flex items-center gap-1 sm:gap-2">
                            <select
                                v-model="selectedSort"
                                class="rounded border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm focus:border-yellow-400 focus:ring-yellow-400 sm:px-4 sm:py-3 sm:text-base"
                            >
                                <option v-for="option in sortOptions" :key="option.value" :value="option.value" class="bg-gray-800 text-white">
                                    {{ option.label }}
                                </option>
                            </select>
                            <Button
                                @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                                class="border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm transition-all hover:bg-white/20 sm:px-4 sm:py-3 sm:text-base"
                            >
                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Tags Filter - Always Open -->
                    <div v-if="allTags.length" class="border-t border-white/10 pt-2">
                        <div class="mb-2">
                            <h3 class="text-xs font-medium text-white sm:text-sm">
                                🏷️ Filter by tags ({{ selectedTags.length }} selected)
                            </h3>
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <label
                                v-for="tag in allTags"
                                :key="tag.id"
                                class="flex cursor-pointer items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs text-white backdrop-blur-sm transition-all hover:bg-white/20"
                            >
                                <input
                                    type="checkbox"
                                    :value="tag.id"
                                    v-model="selectedTags"
                                    class="rounded border-white/30 bg-white/10 text-purple-400 focus:ring-purple-400"
                                />
                                <span>{{ tag.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div v-if="files.data.length === 0" class="flex flex-col items-center justify-center py-12">
                    <div
                        class="mb-6 flex h-32 w-32 items-center justify-center rounded-full border-4 border-dashed border-gray-400 bg-gray-100 dark:border-gray-600 dark:bg-gray-800"
                    >
                        <FolderIcon class="h-16 w-16 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h2 class="mb-2 text-xl font-semibold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                        No files found
                    </h2>
                    <p class="mb-6 text-white/90 [text-shadow:1px_1px_0_black,-1px_-1px_0_black,1px_-1px_0_black,-1px_1px_0_black]">
                        You haven't uploaded any files yet.
                    </p>
                    <Link :href="route('files.create')">
                        <Button
                            class="font-pixel border-2 border-white bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:translate-y-[-2px] hover:bg-green-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] sm:px-8 sm:py-4 sm:text-base"
                        >
                            <PlusIcon class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                            <span class="hidden sm:inline">Upload Your First File</span>
                            <span class="sm:hidden">Upload File</span>
                        </Button>
                    </Link>
                </div>

                <div v-else class="space-y-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div
                            v-for="file in sortedFiles"
                            :key="file.id"
                                class="group relative overflow-hidden rounded-lg border border-white/20 bg-white/5 backdrop-blur-sm transition-all duration-200 hover:border-white/40 hover:bg-white/10 hover:shadow-lg"
                            >
                                <!-- File Card Content -->
                                <Link :href="route('files.show', file.id)" class="block p-4">
                                    <!-- Header with file icon and verification status -->
                                    <div class="mb-3 flex items-start justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded bg-blue-500/20">
                                                <FileIcon class="h-4 w-4 text-blue-400" />
                                            </div>
                                            <div v-if="file.verified" class="rounded-full bg-green-500/20 p-1">
                                                <svg class="h-3 w-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div v-else-if="file.is_denied" class="rounded-full bg-red-500/20 p-1">
                                                <svg class="h-3 w-3 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div v-else class="rounded-full bg-yellow-500/20 p-1">
                                                <svg class="h-3 w-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        
                                    </div>

                                    <!-- File title -->
                                    <h3 class="mb-2 line-clamp-2 text-sm font-semibold text-white group-hover:text-yellow-400 sm:text-base">
                                        {{ file.name }}
                                    </h3>

                                    <!-- Description -->
                                    <p class="mb-3 line-clamp-2 text-xs text-white/70 sm:text-sm">
                                        {{ file.description || 'No description provided' }}
                                    </p>

                                    <!-- File metadata -->
                                    <div class="mb-3 space-y-1 text-xs text-white/60">
                                        <div class="flex items-center justify-between">
                                            <span>Created</span>
                                            <span>{{ new Date(file.created_at).toLocaleDateString() }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span>⭐ {{ file.star_count || 0 }} stars</span>
                                            <span v-if="file.verified" class="text-green-400">✓ Verified</span>
                                            <span v-else-if="file.is_denied" class="text-red-400">✗ Denied</span>
                                            <span v-else class="text-yellow-400">⏳ Pending</span>
                                        </div>
                                    </div>

                                    <!-- Denial Reason -->
                                    <div
                                        v-if="file.is_denied && file.denial_reason"
                                        class="mb-3 rounded border border-red-300/20 bg-red-500/10 p-2 text-xs"
                                    >
                                        <strong class="text-red-300">Denial Reason:</strong>
                                        <p class="mt-1 text-red-400">{{ file.denial_reason }}</p>
                                    </div>

                                    <!-- Tags (if any) -->
                                    <div v-if="file.tags && file.tags.length > 0" class="mb-3 flex flex-wrap gap-1">
                                        <span
                                            v-for="tag in file.tags.slice(0, 3)"
                                            :key="tag.id"
                                            class="rounded bg-purple-500/20 px-2 py-0.5 text-xs text-purple-300"
                                        >
                                            {{ tag.name }}
                                        </span>
                                        <span v-if="file.tags.length > 3" class="text-xs text-white/50">
                                            +{{ file.tags.length - 3 }} more
                                        </span>
                                    </div>
                                </Link>

                                <!-- Action buttons -->
                                <div class="border-t border-white/10 p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex gap-1 sm:gap-2">
                                            <Link
                                                :href="route('files.show', file.id)"
                                                class="rounded bg-blue-500/20 px-3 py-2 text-xs text-blue-400 transition-colors hover:bg-blue-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                title="View file"
                                            >
                                                <EyeIcon class="inline h-3 w-3 sm:h-4 sm:w-4" />
                                            </Link>
                                            <Link
                                                :href="route('files.edit', file.id)"
                                                class="rounded bg-green-500/20 px-3 py-2 text-xs text-green-400 transition-colors hover:bg-green-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                title="Edit file"
                                            >
                                                <PencilIcon class="inline h-3 w-3 sm:h-4 sm:w-4" />
                                            </Link>
                                        </div>

                                        <div class="flex gap-1 sm:gap-2">
                                            <button
                                                @click.prevent="toggleStar(file)"
                                                class="rounded bg-yellow-500/20 px-3 py-2 text-xs text-yellow-400 transition-colors hover:bg-yellow-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                :disabled="file.is_starring"
                                                title="Star file"
                                            >
                                                <StarIcon class="inline h-3 w-3 sm:h-4 sm:w-4" :class="file.is_starred ? 'fill-current' : ''" />
                                            </button>
                                            <button
                                                @click.prevent="openCollectionModal(file)"
                                                class="rounded bg-purple-500/20 px-3 py-2 text-xs text-purple-400 transition-colors hover:bg-purple-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                title="Add to collection"
                                            >
                                                <PlusIcon class="inline h-3 w-3 sm:h-4 sm:w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="files.meta && files.meta.last_page > 1" class="mt-8 flex justify-center">
                        <div class="flex space-x-2">
                            <Link
                                v-for="page in files.meta.links"
                                :key="page.label"
                                :href="page.url ? page.url : '#'"
                                v-text="page.label"
                                :class="[
                                    'font-pixel border-2 px-4 py-2 text-sm shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all',
                                    page.active
                                        ? 'border-yellow-400 bg-yellow-500 text-black shadow-[4px_4px_0px_rgba(0,0,0,0.8)]'
                                        : 'border-white/30 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]',
                                    !page.url && 'cursor-not-allowed opacity-50',
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collection Modal -->
            <Transition name="modal">
                <div v-if="showCollectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
                    <div
                        class="w-full max-w-md rounded-xl border-2 border-white bg-gradient-to-br from-gray-900 to-gray-800 p-6 shadow-[8px_8px_0px_rgba(0,0,0,0.8)]"
                    >
                        <h2 class="font-pixel mb-6 text-xl font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                            Add to Collection
                        </h2>

                        <!-- Existing Collections -->
                        <div v-if="userCollections.length > 0" class="mb-4">
                            <p class="mb-3 text-sm text-white/80">Select an existing collection:</p>
                            <div class="max-h-40 space-y-2 overflow-y-auto">
                                <div
                                    v-for="collection in userCollections"
                                    :key="collection.id"
                                    class="flex cursor-pointer items-center justify-between rounded-lg border border-white/20 bg-white/10 p-3 backdrop-blur-sm transition-all hover:border-white/40 hover:bg-white/20"
                                    :class="{ 'border-yellow-400 bg-yellow-500/20': selectedCollection === collection.id }"
                                    @click="selectedCollection = collection.id"
                                >
                                    <div class="flex items-center">
                                        <FolderIcon class="mr-2 h-5 w-5 text-yellow-400" />
                                        <span class="font-medium text-white">{{ collection.name }}</span>
                                    </div>
                                    <span class="text-xs text-white/60">
                                        {{ collection.file_count }} file{{ collection.file_count !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- New Collection -->
                        <div v-if="showCreateNewCollection" class="mb-6">
                            <p class="mb-3 text-sm text-white/80">Create a new collection:</p>
                            <Input
                                v-model="newCollectionName"
                                placeholder="Collection name"
                                class="mb-3 border-2 border-white/30 bg-white/10 text-white backdrop-blur-sm placeholder:text-white/60 focus:border-yellow-400"
                                :disabled="isCreatingCollection"
                            />
                            <Button
                                @click="createNewCollection"
                                :disabled="isCreatingCollection"
                                class="font-pixel w-full border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-green-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                            >
                                {{ isCreatingCollection ? 'Creating...' : 'Create Collection' }}
                            </Button>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <Button
                                variant="outline"
                                @click="showCollectionModal = false"
                                class="font-pixel border-2 border-red-400 bg-red-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-red-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                            >
                                Cancel
                            </Button>
                            <Button
                                v-if="!showCreateNewCollection"
                                @click="showCreateNewCollection = true"
                                class="font-pixel border-2 border-blue-400 bg-blue-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-blue-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                            >
                                Create New
                            </Button>
                            <Button
                                v-else
                                @click="addToCollection"
                                :disabled="!selectedCollection"
                                class="font-pixel border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-green-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Add to Collection
                            </Button>
                        </div>
                    </div>
                </div>
            </Transition>
        </AppLayout>
    </div>
</template>
