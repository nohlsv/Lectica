<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type Tag } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { FileIcon, FolderIcon, PlusIcon, StarIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

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

// Computed property to group files by first letter
const groupedFiles = computed(() => {
    const grouped: Record<string, File[]> = {};

    props.files.data.forEach((file) => {
        const firstLetter = file.name.charAt(0).toUpperCase();
        if (!grouped[firstLetter]) {
            grouped[firstLetter] = [];
        }
        grouped[firstLetter].push(file);
    });

    // Sort groups alphabetically
    return Object.keys(grouped)
        .sort()
        .reduce<Record<string, File[]>>((result, key) => {
            result[key] = grouped[key];
            return result;
        }, {});
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
</script>

<template>
    <div class="dark:bg-[#161615]">
        <Head title="My Files" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="bg-lectica flex max-h-[200px] w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
                <!--Header Section-->
                <div class="mb-6 flex min-h-[150px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left">
                    <!--Icon-->
                    <div class="relative flex flex-col items-center gap-2">
                        <div class="animate-floating flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-blue-500 to-purple-600 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] sm:h-28 sm:w-28 md:h-32 md:w-32">
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
                            <Button class="font-pixel border-2 border-white bg-green-600 px-4 py-2 text-white shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:bg-green-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:translate-y-[-2px] transition-all">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Add New File
                            </Button>
                        </Link>
                    </div>
                </div>
                <!--Divider-->
                <hr class="-mx-4 h-2 border-2 border-black bg-blue-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
            </div>

            <!--Main Content-->
            <div class="bg-gradient flex h-full flex-1 flex-col gap-4 px-4 pt-4 pb-0 lg:p-8">

                <div v-if="files.data.length === 0" class="flex flex-col items-center justify-center py-12">
                    <div class="mb-6 flex h-32 w-32 items-center justify-center rounded-full border-4 border-dashed border-gray-400 bg-gray-100 dark:border-gray-600 dark:bg-gray-800">
                        <FolderIcon class="h-16 w-16 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h2 class="mb-2 text-xl font-semibold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">No files found</h2>
                    <p class="mb-6 text-white/90 [text-shadow:1px_1px_0_black,-1px_-1px_0_black,1px_-1px_0_black,-1px_1px_0_black]">You haven't uploaded any files yet.</p>
                    <Link :href="route('files.create')">
                        <Button class="font-pixel border-2 border-white bg-green-600 px-6 py-3 text-white shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:bg-green-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:translate-y-[-2px] transition-all">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Upload Your First File
                        </Button>
                    </Link>
                </div>

                <div v-else class="space-y-8">
                    <div v-for="(files, letter) in groupedFiles" :key="letter" class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="font-pixel bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-4 py-2 rounded-lg shadow-[4px_4px_0px_rgba(0,0,0,0.8)] text-xl font-bold">
                                {{ letter }}
                            </div>
                            <hr class="flex-1 h-1 bg-gradient-to-r from-yellow-400 to-transparent rounded" />
                        </div>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
                            <div v-for="file in files" :key="file.id" class="group relative bg-container">
                                <Link :href="route('files.show', file.id)" class="block no-underline">
                                    <Card class="h-full transition-all duration-200 hover:shadow-[8px_8px_0px_rgba(0,0,0,0.8)] hover:translate-y-[-2px] border-2 border-gray-300 bg-container dark:border-gray-600 dark:bg-gray-800/90">
                                    <CardHeader class="pb-2">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center">
                                                <FileIcon class="text-primary mr-2 h-5 w-5" />
                                                <CardTitle class="max-w-[200px] truncate text-lg" :title="file.name">
                                                    {{ file.name }}
                                                </CardTitle>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <StarIcon
                                                    :class="[
                                                        'h-5 w-5',
                                                        file.is_starred ? 'fill-yellow-400 text-yellow-400' : 'text-muted-foreground',
                                                    ]"
                                                />
                                                <button
                                                    @click.stop.prevent="openCollectionModal(file)"
                                                    class="border-border bg-background text-foreground hover:bg-accent inline-flex h-6 w-6 items-center justify-center rounded-md border opacity-0 transition-colors group-hover:opacity-100"
                                                    title="Add to Collection"
                                                >
                                                    <PlusIcon class="h-3 w-3" />
                                                </button>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <CardDescription class="line-clamp-2">
                                            {{ file.description || 'No description provided' }}
                                        </CardDescription>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <Badge
                                                v-for="tag in file.tags"
                                                :key="tag.id"
                                                class="truncate border-2 border-[#0c0a03] bg-[#faa800] text-xs text-[#661500]"
                                                :title="tag.name"
                                            >
                                                {{ tag.name }}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                    <CardFooter class="pixel-outline flex justify-between text-xs tracking-wide text-[#fdf6ee]/75">
                                        <span>Created: {{ new Date(file.created_at).toLocaleDateString() }}</span>
                                        <div class="flex items-center space-x-2">
                                            <div class="flex items-center">
                                                <span>{{ file.flashcards_count || 0 }} flashcards</span>
                                            </div>
                                            <span>•</span>
                                            <div class="flex items-center">
                                                <span>{{ file.quizzes_count || 0 }} quizzes</span>
                                            </div>
                                        </div>
                                    </CardFooter>
                                </Card>
                            </Link>
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
                                'font-pixel border-2 px-4 py-2 text-sm transition-all shadow-[2px_2px_0px_rgba(0,0,0,0.8)]',
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
                <div class="w-full max-w-md rounded-xl border-2 border-white bg-gradient-to-br from-gray-900 to-gray-800 p-6 shadow-[8px_8px_0px_rgba(0,0,0,0.8)]">
                    <h2 class="font-pixel mb-6 text-xl font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">Add to Collection</h2>

                    <!-- Existing Collections -->
                    <div v-if="userCollections.length > 0" class="mb-4">
                        <p class="mb-3 text-sm text-white/80">Select an existing collection:</p>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <div
                                v-for="collection in userCollections"
                                :key="collection.id"
                                class="flex cursor-pointer items-center justify-between rounded-lg border border-white/20 bg-white/10 p-3 backdrop-blur-sm transition-all hover:bg-white/20 hover:border-white/40"
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
                            class="mb-3 border-2 border-white/30 bg-white/10 text-white placeholder:text-white/60 backdrop-blur-sm focus:border-yellow-400" 
                            :disabled="isCreatingCollection" 
                        />
                        <Button 
                            @click="createNewCollection" 
                            :disabled="isCreatingCollection" 
                            class="font-pixel w-full border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] hover:bg-green-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all"
                        > 
                            {{ isCreatingCollection ? 'Creating...' : 'Create Collection' }}
                        </Button>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <Button 
                            variant="outline" 
                            @click="showCollectionModal = false" 
                            class="font-pixel border-2 border-red-400 bg-red-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] hover:bg-red-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all"
                        > 
                            Cancel 
                        </Button>
                        <Button 
                            v-if="!showCreateNewCollection" 
                            @click="showCreateNewCollection = true" 
                            class="font-pixel border-2 border-blue-400 bg-blue-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] hover:bg-blue-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all"
                        >
                            Create New
                        </Button>
                        <Button 
                            v-else 
                            @click="addToCollection" 
                            :disabled="!selectedCollection"
                            class="font-pixel border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] hover:bg-green-700 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
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
