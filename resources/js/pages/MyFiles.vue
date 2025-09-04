<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { FileIcon, FolderIcon, StarIcon, PlusIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { type File, type Tag, type BreadcrumbItem } from '@/types';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

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

    props.files.data.forEach(file => {
        const firstLetter = file.name.charAt(0).toUpperCase();
        if (!grouped[firstLetter]) {
            grouped[firstLetter] = [];
        }
        grouped[firstLetter].push(file);
    });

    // Sort groups alphabetically
    return Object.keys(grouped).sort().reduce<Record<string, File[]>>((result, key) => {
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
    showCreateNewCollection.value = false;
    newCollectionName.value = '';
    fetchUserCollections();
};

const createNewCollection = async () => {
    if (!newCollectionName.value.trim()) return;

    isCreatingCollection.value = true;
    try {
        const response = await axios.post('/api/collections', {
            name: newCollectionName.value.trim(),
            is_public: false
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
    <Head title="My Files" />

    <AppLayout>
        <div class="px-3 sm:px-6 py-6 bg-gradient">
            <!-- Breadcrumbs -->
            <div class="mb-6 flex items-center text-sm text-muted-foreground ml-3">
                <div v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                    <Link v-if="index < breadcrumbs.length - 1" :href="crumb.href" class="hover:text-foreground">
                        {{ crumb.title }}
                    </Link>
                    <span v-else class="font-medium text-foreground">{{ crumb.title }}</span>

                    <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
                </div>
            </div>

            <div class="p-6 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <h1 class="text-2xl font-bold welcome-banner animate-soft-bounce pixel-outline w-fit py-2 px-10">My Files</h1>
                    <Link :href="route('files.create')">
                        <Button class="bg-[#6B7A58] text-[#fdf6ee] hover:bg-[#7F8F6A] border-border border-2 text-base py-3 px-4 sm:px-6 md:px-10 pixel-outline tracking-wide">
                            <PlusIcon class="h-5 w-5 mr-2 pixel-outline-icon" />
                            Add New File
                        </Button>
                    </Link>
                </div>

                <!-- If no files are uploaded -->
                <div v-if="files.data.length === 0" class="flex flex-col items-center justify-center py-12 bg-container min-h-screen">
                    <FolderIcon class="h-16 w-16 text-muted-foreground mb-4" />
                    <h2 class="text-xl font-semibold mb-2 pixel-outline">No files found</h2>
                    <p class="text-muted-foreground mb-6">You haven't uploaded any files yet.</p>
                    <Link :href="route('files.create')">
                        <Button class="bg-[#10B981] hover:bg-[#0e9459] hover:scale-105 duration-300 text-prmary pixel-outline tracking-wide py-3 border-[#0c0a03] border-2">
                            <PlusIcon class="h-4 w-4 mr-2 pixel-outline-icon" />
                            Upload Your First File
                        </Button>
                    </Link>
                </div>

            <div v-else class="space-y-6">
                <div v-for="(files, letter) in groupedFiles" :key="letter" class="space-y-4">
                    <h2 class="text-xl font-bold border-b pb-2">{{ letter }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div
                            v-for="file in files"
                            :key="file.id"
                            class="relative group"
                        >
                            <Link
                                :href="route('files.show', file.id)"
                                class="no-underline block"
                            >
                                <Card class="h-full transition-all hover:shadow-md">
                                    <CardHeader class="pb-2">
                                        <div class="flex justify-between items-start">
                                            <div class="flex items-center">
                                                <FileIcon class="h-5 w-5 mr-2 text-primary" />
                                                <CardTitle class="text-lg truncate max-w-[200px]" :title="file.name">
                                                    {{ file.name }}
                                                </CardTitle>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <StarIcon
                                                    :class="[
                                                        'h-5 w-5',
                                                        file.is_starred ? 'fill-yellow-400 text-yellow-400' : 'text-muted-foreground'
                                                    ]"
                                                />
                                                <button
                                                    @click.stop.prevent="openCollectionModal(file)"
                                                    class="inline-flex h-6 w-6 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent transition-colors opacity-0 group-hover:opacity-100"
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
                                                variant="secondary"
                                                class="text-xs truncate"
                                                :title="tag.name"
                                            >
                                                {{ tag.name }}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                    <CardFooter class="flex justify-between text-xs text-muted-foreground">
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
                <div v-if="files.meta && files.meta.last_page > 1" class="flex justify-center mt-8">
                    <div class="flex space-x-1">
                        <Link
                            v-for="page in files.meta.links"
                            :key="page.label"
                            :href="page.url ? page.url : '#'"
                            v-text="page.label"
                            :class="[
                                'px-3 py-1 rounded border',
                                page.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted',
                                !page.url && 'opacity-50 cursor-not-allowed'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Modal -->
        <Transition name="modal">
            <div
                v-if="showCollectionModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            >
                <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6">
                    <h2 class="text-xl font-semibold mb-4">Add to Collection</h2>

                    <!-- Existing Collections -->
                    <div v-if="userCollections.length > 0" class="mb-4">
                        <p class="text-sm text-muted-foreground mb-2">Select an existing collection:</p>
                        <div class="space-y-2">
                            <div
                                v-for="collection in userCollections"
                                :key="collection.id"
                                class="flex items-center justify-between p-3 rounded-lg border cursor-pointer hover:bg-muted"
                                @click="selectedCollection = collection.id"
                            >
                                <div class="flex items-center">
                                    <FolderIcon class="h-5 w-5 mr-2 text-primary" />
                                    <span class="font-medium">{{ collection.name }}</span>
                                </div>
                                <span class="text-xs text-muted-foreground">
                                    {{ collection.file_count }} file
                                    <span v-if="collection.file_count !== 1">s</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- New Collection -->
                    <div v-if="showCreateNewCollection" class="mb-4">
                        <p class="text-sm text-muted-foreground mb-2">Create a new collection:</p>
                        <Input
                            v-model="newCollectionName"
                            placeholder="Collection name"
                            class="mb-2"
                            :disabled="isCreatingCollection"
                        />
                        <Button
                            @click="createNewCollection"
                            :loading="isCreatingCollection"
                            class="w-full"
                        >
                            Create Collection
                        </Button>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-2">
                        <Button
                            variant="outline"
                            @click="showCollectionModal = false"
                            class="flex-1"
                        >
                            Cancel
                        </Button>
                        <Button
                            v-if="!showCreateNewCollection"
                            @click="showCreateNewCollection = true"
                            class="flex-1"
                        >
                            Create New Collection
                        </Button>
                        <Button
                            v-else
                            @click="addToCollection"
                            class="flex-1"
                        >
                            Add to Collection
                        </Button>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>
