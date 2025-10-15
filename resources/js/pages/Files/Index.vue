<script setup lang="ts">
import CollectionModal from '@/components/CollectionModal.vue';
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Tag, type BreadcrumbItem, type PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDateFormat } from '@vueuse/core';
import axios from 'axios';
import { EyeIcon, FileIcon, PencilIcon, PlusIcon, StarIcon } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

interface File {
    id: number;
    name: string;
    description?: string;
    created_at: string;
    user: {
        first_name: string;
        last_name: string;
    };
    is_starred?: boolean;
    star_count?: number;
    can_edit?: boolean;
    is_starring?: boolean; // Added property
    verified: boolean;
    tags?: Array<{ id: number; name: string }>;
}

interface Props {
    files: PaginatedData<File>;
}

defineProps<Props>();

// Pagination helper functions
const shouldShowPageNumber = (
    page: number,
    currentPage: number,
    lastPage: number
) => {
    // Always show first and last pages
    if (page === 1 || page === lastPage) return true;
    
    // Show pages around current page
    if (Math.abs(page - currentPage) <= 2) return true;
    
    return false;
};

const shouldShowEllipsis = (
    page: number,
    currentPage: number,
    lastPage: number
) => {
    // Show ellipsis between first page and current page area
    if (page === 2 && currentPage - 3 > 1) return true;
    
    // Show ellipsis between current page area and last page
    if (page === lastPage - 1 && currentPage + 3 < lastPage) return true;
    
    return false;
};

// Pagination URL helper functions
const getPrevPageUrl = (files: PaginatedData<File>) => {
    const prevLink = files.links.find(link => link.label === '&laquo; Previous');
    return prevLink?.url || '';
};

const getNextPageUrl = (files: PaginatedData<File>) => {
    const nextLink = files.links.find(link => link.label === 'Next &raquo;');
    return nextLink?.url || '';
};

const getPageUrl = (files: PaginatedData<File>, page: number) => {
    const pageLink = files.links.find(link => link.label === String(page));
    return pageLink?.url || '';
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'description', label: 'Description', class: 'hidden sm:table-cell' },
    { key: 'created_at', label: 'Upload Info', class: 'hidden md:table-cell whitespace-nowrap' },
];

const searchQuery = ref('');
const selectedTags = ref<number[]>([]);
const showStarredOnly = ref(false);
const showSameProgramOnly = ref(false);
const showCollectionModal = ref(false);
const selectedFileForCollection = ref<File | null>(null);
const allTags = ref<Tag[]>([]);

const sortOptions = ref([
    { value: 'name', label: 'Name' },
    { value: 'created_at', label: 'Upload Date' },
    { value: 'star_count', label: 'Star Count' },
]);
const selectedSort = ref('name');
const sortDirection = ref('asc');

const fetchTags = async () => {
    const response = await axios.get(route('tags.index'));
    allTags.value = response.data;
};

onMounted(() => {
    fetchTags();
});

const applyFilters = () => {
    router.get(
        route('files.index'),
        {
            search: searchQuery.value,
            tags: selectedTags.value,
            starred: showStarredOnly.value,
            sameProgram: showSameProgramOnly.value,
            sort: selectedSort.value,
            direction: sortDirection.value,
        },
        { preserveState: true },
    );
};

const toggleStar = async (file: File) => {
    if (file.is_starring) return;

    file.is_starring = true;

    try {
        await router.post(
            route('files.star', { file: file.id }),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    file.is_starred = !file.is_starred;
                    file.star_count = file.is_starred ? (file.star_count || 0) + 1 : (file.star_count || 0) - 1;
                },
                onFinish: () => {
                    file.is_starring = false;
                },
            },
        );
    } catch (error) {
        file.is_starring = false;
        toast.error('Error toggling star', {
            description: 'An error occurred while toggling the star status. Please try again.',
        });
    }
};

const openCollectionModal = (file: File) => {
    selectedFileForCollection.value = file;
    showCollectionModal.value = true;
};

const closeCollectionModal = () => {
    showCollectionModal.value = false;
    selectedFileForCollection.value = null;
};

const onCollectionSuccess = (message?: string) => {
    toast.success(message || 'Collections updated successfully!');
    closeCollectionModal();
};
</script>

<template>
    <div class="dark:bg-[#161615]">
        <Head title="File Explorer" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="bg-lectica flex w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
                <!--Header Section-->
                <div
                    class="mb-4 flex w-full flex-col items-center justify-center gap-4 rounded-xl p-4 text-center sm:mb-6 sm:min-h-[150px] sm:flex-row sm:gap-6 sm:p-6 sm:text-left"
                >
                    <!--Icon-->
                    <div class="relative flex flex-col items-center gap-2">
                        <div
                            class="animate-floating flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-green-500 to-blue-600 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] sm:h-28 sm:w-28 md:h-32 md:w-32"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-10 w-10 text-white sm:h-14 sm:w-14 md:h-16 md:w-16"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                />
                            </svg>
                        </div>
                    </div>
                    <!--Title-->
                    <div>
                        <h1 class="text-2xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-3xl">
                            Knowledge Vault
                        </h1>
                        <p class="text-lg text-white/90 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-xl">
                            Explore the collective wisdom of students
                        </p>
                    </div>
                    <!--Action Button-->
                    <div class="sm:ml-auto">
                        <Link href="/files/create">
                            <Button
                                class="font-pixel border-2 border-white bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:translate-y-[-2px] hover:bg-green-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] sm:px-6 sm:py-4 sm:text-base"
                            >
                                <PlusIcon class="mr-2 h-4 w-4 sm:h-5 sm:w-5" />
                                <span class="hidden sm:inline">Upload New File</span>
                                <span class="sm:hidden">Upload</span>
                            </Button>
                        </Link>
                    </div>
                </div>
                <!--Divider-->
                <hr class="-mx-4 h-2 border-2 border-black bg-green-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
            </div>

            <!--Main Content-->
            <div class="bg-gradient flex h-full max-w-full flex-1 flex-col gap-4 overflow-hidden px-4 pt-6 pb-0 lg:p-8">
                <!-- Search and Filters Section - Compact Mobile-First -->
                <div class="mb-4 space-y-3 rounded-lg border-2 border-white/20 bg-black/30 p-3 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm sm:mb-6 sm:space-y-4 sm:rounded-xl sm:p-4 sm:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] lg:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="font-pixel text-sm font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-base lg:text-lg">
                            🔍 Search & Filter
                        </h3>
                        
                        <!-- Search Bar -->
                        <div class="flex flex-1 items-center gap-2 sm:max-w-md">
                            <Input
                                v-model="searchQuery"
                                @input="applyFilters"
                                placeholder="Search files..."
                                class="flex-1 border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm placeholder:text-white/60 focus:border-yellow-400 sm:px-4"
                            />
                        </div>
                    </div>

                    <!-- Compact Filter Row -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <!-- Quick Filters -->
                        <label class="flex items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm hover:bg-white/20 sm:gap-2 sm:px-3 sm:py-2 sm:text-sm">
                            <input
                                type="checkbox"
                                v-model="showStarredOnly"
                                @change="applyFilters"
                                class="h-3 w-3 rounded border-white/30 bg-white/10 text-yellow-400 focus:ring-yellow-400 sm:h-4 sm:w-4"
                            />
                            <StarIcon class="h-3 w-3 sm:h-4 sm:w-4" />
                            <span class="hidden sm:inline">Starred</span>
                        </label>
                        
                        <label class="flex items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm hover:bg-white/20 sm:gap-2 sm:px-3 sm:py-2 sm:text-sm">
                            <input
                                type="checkbox"
                                v-model="showSameProgramOnly"
                                @change="applyFilters"
                                class="h-3 w-3 rounded border-white/30 bg-white/10 text-blue-400 focus:ring-blue-400 sm:h-4 sm:w-4"
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <span class="hidden sm:inline">My Program</span>
                        </label>

                        <!-- Sort Controls - Right aligned -->
                        <div class="ml-auto flex items-center gap-1 sm:gap-2">
                            <select
                                v-model="selectedSort"
                                @change="applyFilters"
                                class="rounded border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm focus:border-yellow-400 focus:ring-yellow-400 sm:px-4 sm:py-3 sm:text-base"
                            >
                                <option v-for="option in sortOptions" :key="option.value" :value="option.value" class="bg-gray-800 text-white">
                                    {{ option.label }}
                                </option>
                            </select>
                            <Button
                                @click="
                                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                                    applyFilters();
                                "
                                class="border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm transition-all hover:bg-white/20 sm:px-4 sm:py-3 sm:text-base"
                            >
                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Tags Filter - Collapsible -->
                    <div v-if="allTags.length" class="border-t border-white/10 pt-2">
                        <details class="group">
                            <summary class="cursor-pointer text-xs font-medium text-white hover:text-yellow-400 sm:text-sm">
                                <span class="inline-flex items-center gap-1">
                                    🏷️ Filter by tags ({{ selectedTags.length }} selected)
                                    <svg class="h-3 w-3 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </summary>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <label
                                    v-for="tag in allTags"
                                    :key="tag.id"
                                    class="flex cursor-pointer items-center gap-1 rounded border border-white/30 bg-white/10 px-2 py-1 text-xs text-white backdrop-blur-sm transition-all hover:bg-white/20"
                                >
                                    <input
                                        type="checkbox"
                                        :value="tag.id"
                                        v-model="selectedTags"
                                        @change="applyFilters"
                                        class="rounded border-white/30 bg-white/10 text-purple-400 focus:ring-purple-400"
                                    />
                                    <span>{{ tag.name }}</span>
                                </label>
                            </div>
                        </details>
                    </div>
                </div>

                <!-- Files Cards Grid -->
                <div
                    class="bg-container max-w-full overflow-hidden rounded-xl border-2 border-white/20 p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm sm:p-6"
                >
                    <h3 class="font-pixel mb-4 text-base font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-lg">
                        📚 Files
                    </h3>

                    <!-- No files message -->
                    <div v-if="files.data.length === 0" class="py-12 text-center">
                        <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                            <FileIcon class="h-8 w-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-white">No files found</h3>
                        <p class="mt-2 text-sm text-white/70">Try adjusting your search or filters to find what you're looking for.</p>
                    </div>

                    <!-- Files Grid -->
                    <div v-else class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <div
                                v-for="file in files.data"
                                :key="file.id"
                            class="group relative overflow-hidden rounded-lg border border-white/20 bg-white/5 backdrop-blur-sm transition-all duration-200 hover:border-white/40 hover:bg-white/10 hover:shadow-lg"
                        >
                            <!-- File Card Content -->
                            <Link :href="`/files/${file.id}`" class="block p-4">
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
                                    </div>
                                    
                                    <!-- Star indicator -->
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
                                        <span>By {{ file.user.first_name }} {{ file.user.last_name }}</span>
                                        <span>{{ useDateFormat(file.created_at, 'MMM D, YYYY').value }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>⭐ {{ file.star_count || 0 }} stars</span>
                                        <span v-if="file.verified" class="text-green-400">✓ Verified</span>
                                    </div>
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
                                            :href="`/files/${file.id}`"
                                            class="rounded bg-blue-500/20 px-3 py-2 text-xs text-blue-400 transition-colors hover:bg-blue-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                            title="View file"
                                        >
                                            <EyeIcon class="inline h-3 w-3 sm:h-4 sm:w-4" />
                                        </Link>
                                        <Link
                                            v-if="file.can_edit"
                                            :href="`/files/${file.id}/edit`"
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
                    <div v-if="files.total > 0" class="mt-6 flex items-center justify-between border-t border-white/10 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="files.current_page > 1"
                                :href="getPrevPageUrl(files)"
                                preserve-scroll
                                class="relative inline-flex items-center rounded-md border border-white/20 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="files.current_page < files.last_page"
                                :href="getNextPageUrl(files)"
                                preserve-scroll
                                class="relative ml-3 inline-flex items-center rounded-md border border-white/20 bg-white/5 px-4 py-2 text-sm font-medium text-white hover:bg-white/10"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-white">
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
                                        v-if="files.current_page > 1"
                                        :href="getPrevPageUrl(files)"
                                        preserve-scroll
                                        class="relative inline-flex items-center rounded-l-md border border-white/20 bg-white/5 px-2 py-2 text-sm font-medium text-white hover:bg-white/10"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                        </svg>
                                    </Link>
                                    <template v-for="i in files.last_page" :key="i">
                                        <Link
                                            v-if="shouldShowPageNumber(i, files.current_page, files.last_page)"
                                            :href="getPageUrl(files, i)"
                                            preserve-scroll
                                            :class="[
                                                i === files.current_page ? 'z-10 bg-yellow-500/20 text-yellow-400' : 'text-white hover:bg-white/10',
                                                'relative inline-flex items-center border border-white/20 bg-white/5 px-4 py-2 text-sm font-medium'
                                            ]"
                                        >
                                            {{ i }}
                                        </Link>
                                        <span
                                            v-else-if="shouldShowEllipsis(i, files.current_page, files.last_page)"
                                            class="relative inline-flex items-center border border-white/20 bg-white/5 px-4 py-2 text-sm font-medium text-white"
                                        >
                                            ...
                                        </span>
                                    </template>
                                    <Link
                                        v-if="files.current_page < files.last_page"
                                        :href="getNextPageUrl(files)"
                                        preserve-scroll
                                        class="relative inline-flex items-center rounded-r-md border border-white/20 bg-white/5 px-2 py-2 text-sm font-medium text-white hover:bg-white/10"
                                    >
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                        </svg>
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Collection Modal -->
            <CollectionModal
                :show="showCollectionModal"
                :file-id="selectedFileForCollection?.id || null"
                @close="closeCollectionModal"
                @success="onCollectionSuccess"
            />
        </AppLayout>
    </div>
</template>
