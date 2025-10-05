<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Tag, type BreadcrumbItem, type PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDateFormat } from '@vueuse/core';
import axios from 'axios';
import { EyeIcon, PencilIcon, PlusIcon, StarIcon } from 'lucide-vue-next';
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
}

interface Props {
    files: PaginatedData<File>;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
];

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'description', label: 'Description', class: 'hidden sm:table-cell' },
    { key: 'created_at', label: 'Upload Info', class: 'hidden md:table-cell' },
];

const searchQuery = ref('');
const selectedTags = ref<number[]>([]);
const showStarredOnly = ref(false);
const showSameProgramOnly = ref(false);
const showCollectionModal = ref(false);
const selectedFileForCollection = ref<File | null>(null);
const selectedCollection = ref<number | null>(null);
const userCollections = ref<Collection[]>([]);
const allTags = ref<Tag[]>([]);
const showCreateNewCollection = ref(false);
const newCollectionName = ref('');
const isCreatingCollection = ref(false);

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

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
</script>

<template>
    <div class="dark:bg-[#161615]">
        <Head title="File Explorer" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="bg-lectica flex max-h-[200px] w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
                <!--Header Section-->
                <div
                    class="mb-6 flex min-h-[150px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left"
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
                                class="font-pixel border-2 border-white bg-green-600 px-4 py-2 text-white shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:translate-y-[-2px] hover:bg-green-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]"
                            >
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Upload New File
                            </Button>
                        </Link>
                    </div>
                </div>
                <!--Divider-->
                <hr class="-mx-4 h-2 border-2 border-black bg-green-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
            </div>

            <!--Main Content-->
            <div class="bg-gradient flex h-full flex-1 flex-col gap-4 px-4 pt-6 pb-0 lg:p-8">
                <!-- Search and Filters Section -->
                <div class="mb-6 space-y-4 rounded-xl border-2 border-white/20 bg-black/30 p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                    <h3 class="font-pixel mb-4 text-lg font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                        🔍 Search & Filter
                    </h3>

                    <div class="space-y-4">
                        <!-- Search Bar -->
                        <div class="flex items-center gap-4">
                            <Input
                                v-model="searchQuery"
                                placeholder="Search files..."
                                class="flex-1 border-2 border-white/30 bg-white/10 text-white backdrop-blur-sm placeholder:text-white/60 focus:border-yellow-400"
                            />
                            <Button
                                @click="applyFilters"
                                class="font-pixel border-2 border-yellow-400 bg-yellow-500 px-4 py-2 text-black shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-yellow-400 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                            >
                                Search
                            </Button>
                        </div>

                        <!-- Filter Options -->
                        <div class="flex flex-wrap items-center gap-4">
                            <label
                                class="flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm"
                            >
                                <input
                                    type="checkbox"
                                    v-model="showStarredOnly"
                                    @change="applyFilters"
                                    class="rounded border-white/30 bg-white/10 text-yellow-400 focus:ring-yellow-400"
                                />
                                <StarIcon class="h-4 w-4" />
                                <span>Starred Only</span>
                            </label>
                            <label
                                class="flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm"
                            >
                                <input
                                    type="checkbox"
                                    v-model="showSameProgramOnly"
                                    @change="applyFilters"
                                    class="rounded border-white/30 bg-white/10 text-blue-400 focus:ring-blue-400"
                                />
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                                    />
                                </svg>
                                <span>Same Program</span>
                            </label>
                        </div>

                        <!-- Sort Controls -->
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-medium text-white">Sort by:</span>
                            <select
                                id="sort"
                                v-model="selectedSort"
                                @change="applyFilters"
                                class="rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm focus:border-yellow-400 focus:ring-yellow-400"
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
                                class="font-pixel border border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm transition-all hover:bg-white/20"
                            >
                                {{ sortDirection === 'asc' ? '↑ Ascending' : '↓ Descending' }}
                            </Button>
                        </div>

                        <!-- Tags Filter -->
                        <div v-if="allTags.length" class="space-y-2">
                            <span class="text-sm font-medium text-white">Filter by tags:</span>
                            <div class="flex flex-wrap gap-2">
                                <label
                                    v-for="tag in allTags"
                                    :key="tag.id"
                                    class="flex cursor-pointer items-center gap-1 rounded-lg border border-white/30 bg-white/10 px-2 py-1 text-xs text-white backdrop-blur-sm transition-all hover:bg-white/20"
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
                        </div>
                    </div>
                </div>

                <!-- Files Data Table -->
                <div class="bg-container rounded-xl border-2 border-white/20 p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                    <h3 class="font-pixel mb-4 text-lg font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                        📚 Files
                    </h3>

                    <div class="bg-container overflow-hidden rounded-lg border border-white/20 backdrop-blur-sm">
                        <div class="overflow-x-auto">
                            <DataTable :data="files" :columns="columns" class="min-w-full">
                                <!-- Custom cell template to clamp content text -->
                                <template #cell-description="{ item }">
                                    <p class="text-muted-foreground line-clamp-4 max-w-full text-sm sm:line-clamp-2">
                                        {{ item.description ? item.description : 'No description provided' }}
                                    </p>
                                </template>
                                <template #cell-created_at="{ item }">
                                    <p class="text-muted-foreground max-w-full text-sm">
                                        By {{ item.user.last_name }}, {{ item.user.first_name }}<br />
                                        {{ useDateFormat(item.created_at, 'MMM D, YYYY').value }}
                                    </p>
                                </template>

                                <template #actions="{ item }">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/files/${item.id}`"
                                            class="border-border bg-background text-foreground hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border"
                                            title="View file details"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            v-if="item.can_edit"
                                            :href="`/files/${item.id}/edit`"
                                            class="border-border bg-background text-foreground hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border"
                                            title="Edit file"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                        <div
                                            v-else
                                            class="border-border bg-background text-muted-foreground inline-flex h-8 w-8 items-center justify-center rounded-md border opacity-40"
                                            title="Only the uploader can edit this file"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </div>
                                        <button
                                            @click.prevent="toggleStar(item)"
                                            class="hover:bg-accent inline-flex items-center justify-center rounded-full p-1 transition-colors"
                                            :class="{ 'text-amber-500': item.is_starred, 'text-muted-foreground': !item.is_starred }"
                                            :disabled="item.is_starring"
                                        >
                                            <StarIcon class="h-4 w-4" :fill="item.is_starred ? 'currentColor' : 'none'" />
                                        </button>
                                        <span>{{ item.star_count || 0 }}</span>
                                        <button
                                            @click.prevent="openCollectionModal(item)"
                                            class="border-border bg-background text-foreground hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border transition-colors"
                                            title="Add to Collection"
                                        >
                                            <PlusIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </template>
                            </DataTable>
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
                            Add File to Collection
                        </h2>

                        <div v-if="!showCreateNewCollection" class="mb-6">
                            <label for="collection" class="mb-3 block text-sm font-medium text-white/80">Select Collection</label>
                            <select
                                id="collection"
                                v-model="selectedCollection"
                                class="w-full rounded-lg border-2 border-white/30 bg-white/10 px-3 py-2 text-sm text-white backdrop-blur-sm focus:border-yellow-400 focus:ring-yellow-400"
                            >
                                <option value="" class="bg-gray-800 text-white">Choose a collection...</option>
                                <option
                                    v-for="collection in userCollections"
                                    :key="collection.id"
                                    :value="collection.id"
                                    class="bg-gray-800 text-white"
                                >
                                    {{ collection.name }} ({{ collection.file_count }} files)
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-white/60">Don't see the collection you want? Create a new one below.</p>
                        </div>

                        <div v-if="showCreateNewCollection" class="mb-6">
                            <label for="new-collection" class="mb-3 block text-sm font-medium text-white/80">New Collection Name</label>
                            <Input
                                id="new-collection"
                                v-model="newCollectionName"
                                placeholder="Enter collection name"
                                class="w-full border-2 border-white/30 bg-white/10 text-white backdrop-blur-sm placeholder:text-white/60 focus:border-yellow-400"
                                @keydown.enter="createNewCollection"
                            />
                        </div>

                        <div class="flex justify-between gap-3">
                            <Button
                                @click="showCreateNewCollection = !showCreateNewCollection"
                                class="font-pixel border-2 border-blue-400 bg-blue-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-blue-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                            >
                                {{ showCreateNewCollection ? 'Select Existing' : 'Create New' }}
                            </Button>
                            <div class="flex gap-2">
                                <Button
                                    @click="showCollectionModal = false"
                                    class="font-pixel border-2 border-red-400 bg-red-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-red-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    v-if="!showCreateNewCollection"
                                    @click="addToCollection"
                                    :disabled="!selectedCollection"
                                    class="font-pixel border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-green-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Add to Collection
                                </Button>
                                <Button
                                    v-else
                                    :disabled="isCreatingCollection || !newCollectionName.trim()"
                                    @click="createNewCollection"
                                    class="font-pixel border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-green-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{ isCreatingCollection ? 'Creating...' : 'Create & Add' }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </AppLayout>
    </div>
</template>
