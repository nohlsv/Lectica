<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import CollectionModal from '@/components/CollectionModal.vue';
import { Tag, type BreadcrumbItem, type PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDateFormat } from '@vueuse/core';
import axios from 'axios';
import { CheckCircleIcon, EyeIcon, PencilIcon, PlusIcon, StarIcon } from 'lucide-vue-next';
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
                                            class="pixel-outline flex h-7 items-center justify-center rounded border border-blue-400/70 bg-blue-400/20 px-2 transition-all hover:bg-blue-400/30"
                                            title="View file details"
                                        >
                                            <EyeIcon class="mr-1 h-3 w-3 text-blue-300" />
                                            <span class="font-pixel text-blue-300">View</span>
                                        </Link>
                                        <Link
                                            v-if="item.can_edit"
                                            :href="`/files/${item.id}/edit`"
                                            class="pixel-outline flex h-7 items-center justify-center rounded border border-green-400/70 bg-green-400/20 px-2 transition-all hover:bg-green-400/30"
                                            title="Edit file"
                                        >
                                            <PencilIcon class="mr-1 h-3 w-3 text-green-300" />
                                            <span class="font-pixel text-green-300">Edit</span>
                                        </Link>
                                        <div
                                            v-else
                                            class="pixel-outline flex h-7 items-center justify-center rounded border border-gray-400/70 bg-gray-400/20 px-2 opacity-40"
                                            title="Only the uploader can edit this file"
                                        >
                                            <PencilIcon class="mr-1 h-3 w-3 text-gray-300" />
                                            <span class="font-pixel text-gray-300">Edit</span>
                                        </div>
                                        <button
                                            @click.prevent="toggleStar(item)"
                                            class="pixel-outline flex h-7 items-center justify-center rounded border border-yellow-400/70 bg-yellow-400/20 px-2 transition-all hover:bg-yellow-400/30"
                                            :disabled="item.is_starring"
                                            title="Star File"
                                        >
                                            <StarIcon 
                                                :class="[
                                                    'mr-1 h-3 w-3 transition-colors',
                                                    item.is_starred ? 'fill-yellow-300 text-yellow-300' : 'text-white/60 hover:text-yellow-300'
                                                ]"
                                            />
                                            <span class="font-pixel mr-1" :class="item.is_starred ? 'text-yellow-300' : 'text-white/60'">
                                                {{ item.is_starred ? 'Starred' : 'Star' }}
                                            </span>
                                            <span class="text-xs text-white/60">({{ item.star_count || 0 }})</span>
                                        </button>
                                        <button
                                            @click.prevent="openCollectionModal(item)"
                                            class="pixel-outline flex h-7 items-center justify-center rounded border border-[#ffd700]/70 bg-[#b71400]/20 px-2 transition-all hover:bg-[#b71400]/30"
                                            title="Add to Collection"
                                        >
                                            <PlusIcon class="mr-1 h-3 w-3 text-[#ffd700]" />
                                            <span class="font-pixel text-[#ffd700]">Add to Collection</span>
                                        </button>
                                    </div>
                                </template>
                            </DataTable>
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
