<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/DataTable.vue';
import { type BreadcrumbItem, type PaginatedData } from '@/types';

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
import { Head, Link, router } from '@inertiajs/vue3';
import { EyeIcon, PencilIcon, StarIcon } from 'lucide-vue-next';
import { useDateFormat } from '@vueuse/core';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tag } from '@/types';
import { toast } from 'vue-sonner';

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
    router.get(route('files.index'), {
        search: searchQuery.value,
        tags: selectedTags.value,
        starred: showStarredOnly.value,
        sameProgram: showSameProgramOnly.value,
        sort: selectedSort.value,
        direction: sortDirection.value,
    }, { preserveState: true });
};

const toggleStar = async (file: File) => {
    if (file.is_starring) return;

    file.is_starring = true;

    try {
        await router.post(route('files.star', { file: file.id }), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                file.is_starred = !file.is_starred;
                file.star_count = file.is_starred ? (file.star_count || 0) + 1 : (file.star_count || 0) - 1;
            },
            onFinish: () => {
                file.is_starring = false;
            }
        });
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
        const response = await axios.get('/api/user/collections');
        userCollections.value = response.data;
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
    <Head title="File List" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gradient p-6 space-y-6">
            <div class="flex flex-col gap-4 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-grow flex items-center justify-center">
                        <h1 class="text-xl font-semibold welcome-banner animate-soft-bounce py-2 px-10 pixel-outline">Files</h1>
                    </div>
                    <Link href="/files/create" class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium  bg-[#6B7A58] text-[#fdf6ee] hover:bg-[#7F8F6A] border-border border-2 pixel-outline duration-300 tracking-wide">
                        Upload New File
                    </Link>
                </div>
                <div class="bg-container p-6">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-4">
                            <Input
                                v-model="searchQuery"
                                placeholder="Search files..."
                                class="w-full !bg-[#FFF8F2]/80 !text-[#333333] !border-2 !border-border"
                            />
                            <Button @click="applyFilters" class="border-border bg-[#5cae6e] border-2 text-[#fdf6ee] pixel-outline tracking-wide duration-300 hover:bg-[#8be6a0]">Search</Button>
                        </div>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 text-sm font-medium">
                                    <input type="checkbox" v-model="showStarredOnly" @change="applyFilters" />
                                    <span>Show Starred Only</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm font-medium">
                                    <input type="checkbox" v-model="showSameProgramOnly" @change="applyFilters" />
                                    <span>Show Users from Same Program Only</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <label for="sort" class="text-sm font-medium">Sort By:</label>
                                    <select id="sort" v-model="selectedSort" @change="applyFilters" class="border rounded px-2 py-1 text-sm bg-background">
                                        <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <Button @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'; applyFilters()" class="text-sm font-medium">
                                        {{ sortDirection === 'asc' ? 'Ascending' : 'Descending' }}
                                    </Button>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="tag in allTags"
                                    :key="tag.id"
                                    :variant="selectedTags.includes(tag.id) ? 'default' : 'secondary'"
                                    @click="selectedTags.includes(tag.id) ? selectedTags.splice(selectedTags.indexOf(tag.id), 1) : selectedTags.push(tag.id)"
                                    class="cursor-pointer"
                                >
                                    {{ tag.name }}
                                </Badge>
                            </div>
                        </div>

                    <div class="rounded-xl border border-border p-4 sm:p-6 mb-8 overflow-hidden">
                        <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <DataTable :data="files" :columns="columns" class="min-w-full">
                                <!-- Custom cell template to clamp content text -->
                                <template #cell-description="{ item }">
                                    <p class="max-w-full sm:line-clamp-2 line-clamp-4 text-sm text-muted-foreground">
                                        {{ item.description ? item.description : 'No description provided' }}
                                    </p>
                                </template>
                                <template #cell-created_at="{ item }">
                                    <p class="max-w-full text-sm text-muted-foreground">
                                        By {{item.user.last_name}}, {{item.user.first_name}}<br>
                                        {{ useDateFormat(item.created_at, 'MMM D, YYYY').value }}
                                    </p>
                                </template>

                                <template #actions="{ item }">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/files/${item.id}`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                                            title="View file details"
                                        >
                                            <EyeIcon class="h-4 w-4" />
                                        </Link>
                                        <Link
                                            v-if="item.can_edit"
                                            :href="`/files/${item.id}/edit`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                                            title="Edit file"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </Link>
                                        <div v-else class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-muted-foreground opacity-40" title="Only the uploader can edit this file">
                                            <PencilIcon class="h-4 w-4" />
                                        </div>
                                        <button
                                            @click.prevent="toggleStar(item)"
                                            class="inline-flex items-center justify-center rounded-full p-1 hover:bg-accent transition-colors"
                                            :class="{'text-amber-500': item.is_starred, 'text-muted-foreground': !item.is_starred}"
                                            :disabled="item.is_starring"
                                        >
                                            <StarIcon class="h-4 w-4" :fill="item.is_starred ? 'currentColor' : 'none'" />
                                        </button>
                                        <span>{{ item.star_count || 0 }}</span>
                                    </div>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Badge
                        v-for="tag in allTags"
                        :key="tag.id"
                        :variant="selectedTags.includes(tag.id) ? 'default' : 'secondary'"
                        @click="selectedTags.includes(tag.id) ? selectedTags.splice(selectedTags.indexOf(tag.id), 1) : selectedTags.push(tag.id)"
                        class="cursor-pointer"
                    >
                        {{ tag.name }}
                    </Badge>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-border p-4 sm:p-6 mb-8 overflow-hidden">
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <DataTable :data="files" :columns="columns" class="min-w-full">
                    <!-- Custom cell template to clamp content text -->
                    <template #cell-description="{ item }">
                        <p class="max-w-full sm:line-clamp-2 line-clamp-4 text-sm text-muted-foreground">
                            {{ item.description ? item.description : 'No description provided' }}
                        </p>
                    </template>
                    <template #cell-created_at="{ item }">
                        <p class="max-w-full text-sm text-muted-foreground">
                            By {{item.user.last_name}}, {{item.user.first_name}}<br>
                            {{ useDateFormat(item.created_at, 'MMM D, YYYY').value }}
                        </p>
                    </template>

                    <template #actions="{ item }">
                        <div class="flex items-center gap-2">
                            <Link
                                :href="`/files/${item.id}`"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                                title="View file details"
                            >
                                <EyeIcon class="h-4 w-4" />
                            </Link>
                            <Link
                                v-if="item.can_edit"
                                :href="`/files/${item.id}/edit`"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                                title="Edit file"
                            >
                                <PencilIcon class="h-4 w-4" />
                            </Link>
                            <div v-else class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-border bg-background text-muted-foreground opacity-40" title="Only the uploader can edit this file">
                                <PencilIcon class="h-4 w-4" />
                            </div>
                            <button
                                @click.prevent="toggleStar(item)"
                                class="inline-flex items-center justify-center rounded-full p-1 hover:bg-accent transition-colors"
                                :class="{'text-amber-500': item.is_starred, 'text-muted-foreground': !item.is_starred}"
                                :disabled="item.is_starring"
                            >
                                <StarIcon class="h-4 w-4" :fill="item.is_starred ? 'currentColor' : 'none'" />
                            </button>
                            <span>{{ item.star_count || 0 }}</span>
                            <Button @click.prevent="openCollectionModal(item)" class="text-sm">
                                Add to Collection
                            </Button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <!-- Collection Modal -->
        <Transition name="modal">
            <div v-if="showCollectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
                <div class="w-full max-w-md rounded-lg bg-background p-6">
                    <h2 class="text-lg font-semibold mb-4">Add File to Collection</h2>
                    <div class="mb-4">
                        <label for="collection" class="block text-sm font-medium mb-2">Select Collection</label>
                        <select id="collection" v-model="selectedCollection" class="w-full rounded border bg-background px-3 py-2 text-sm">
                            <option v-for="collection in userCollections" :key="collection.id" :value="collection.id">
                                {{ collection.name }} ({{ collection.file_count }})
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button @click="showCollectionModal = false" variant="outline" class="text-sm">
                            Cancel
                        </Button>
                        <Button @click="addToCollection" class="text-sm">
                            Add to Collection
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>
