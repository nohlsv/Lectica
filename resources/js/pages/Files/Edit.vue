<script setup lang="ts">
import TagInput from '@/components/TagInput.vue';
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type Tag } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeftIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    file: File;
    allTags: Tag[];
}

const props = defineProps<Props>();

const showDeleteModal = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: props.file.name,
        href: `/files/${props.file.id}`,
    },
    {
        title: 'Edit',
        href: `/files/${props.file.id}/edit`,
    },
];

const form = useForm({
    name: props.file.name,
    description: props.file.description || '',
    tags: (props.file.tags as any[] | undefined) || [],
});

const submit = () => {
    form.put(`/files/${props.file.id}`);
};

const isGenerating = ref(false);
const showDeleteModal = ref(false);
const showCollectionModal = ref(false);
const userCollections = ref<Collection[]>([]);
const selectedCollection = ref<number | null>(null);

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

const deleteFile = () => {
    form.delete(`/files/${props.file.id}`, {
        onSuccess: () => {
            router.visit('/files');
        },
    });
};

// Fetch user's collections for adding files
const fetchUserCollections = async () => {
    try {
        const response = await fetch('/user/collections');
        const data = await response.json();
        userCollections.value = data;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

const openCollectionModal = () => {
    selectedCollection.value = null;
    showCollectionModal.value = true;
    fetchUserCollections();
};

const addToCollection = async () => {
    if (!selectedCollection.value) return;

    try {
        await router.post(
            route('collections.add-file', selectedCollection.value),
            {
                file_id: props.file.id,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    showCollectionModal.value = false;
                    selectedCollection.value = null;
                    // Add success notification here if needed
                },
                onError: (errors) => {
                    console.error('Failed to add file to collection:', errors);
                },
            },
        );
    } catch (error) {
        console.error('Failed to add file to collection:', error);
    }
};
</script>

<template>
    <Head title="Edit File" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Link :href="`/files/${file.id}`" class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1">
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back
                    </Link>
                </div>

                <!-- Centered Title -->
                <div class="sm:flex-1 sm:order-2 flex justify-center">
                    <h1 class="text-2xl font-bold welcome-banner py-2 px-6 animate-soft-bounce text-center leading-tight">
                    Edit File
                    </h1>
                </div>

                <!-- Spacer to balance layout -->
                <div class="sm:flex-1 sm:order-3 hidden sm:block"></div>
            </div>

            <div class="border-border rounded-lg border p-6">
                <form @submit.prevent="submit" class="max-w-md space-y-4">
                    <div class="space-y-2">
                        <label for="name" class="text-foreground block text-sm font-medium">File Name</label>
                        <input
                            type="text"
                            v-model="form.name"
                            id="name"
                            class="border-input bg-background ring-offset-background w-full rounded-md border px-3 py-2 text-sm"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- File Description -->
                    <div class="space-y-2">
                        <label for="description" class="text-foreground block text-sm font-medium">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="border-input bg-background ring-offset-background w-full resize-none rounded-md border px-3 py-2 text-sm"
                            placeholder="Enter a brief description of this file (optional)"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tags" class="text-foreground block text-sm font-medium">Tags</label>
                        <TagInput v-model="form.tags" :existing-tags="allTags" />
                        <p class="text-muted-foreground text-xs">
                            Add tags to categorize your file. You can create new tags or select existing ones.
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <Dialog>
                                <DialogTrigger as-child>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                                        :disabled="form.processing"
                                    >
                                        Delete File
                                    </button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Confirm Deletion</DialogTitle>
                                    </DialogHeader>
                                    <p>Are you sure you want to delete this file? This action cannot be undone.</p>
                                    <DialogFooter>
                                        <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                                        <Button variant="destructive" @click="deleteFile" :disabled="form.processing">
                                            {{ form.processing ? 'Deleting...' : 'Delete' }}
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <div>
                            <Link
                                :href="`/files/${file.id}`"
                                class="border-border bg-background text-foreground hover:bg-accent inline-flex items-center justify-center rounded-md border px-4 py-2 text-sm font-medium"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium"
                                :disabled="form.processing"
                                >
                                Delete File
                                </button>
                            </DialogTrigger>
                            <DialogContent class="bg-[#912414] border-4 border-[#feaf00] rounded-lg">
                                <DialogHeader>
                                <DialogTitle class="pixel-outline tracking-wide">Confirm Deletion</DialogTitle>
                                </DialogHeader>
                                    <p class="pixel-outline tracking-wide">Are you sure you want to delete this file? This action cannot be undone.</p>
                                <DialogFooter>
                                <DialogClose as-child @click="showDeleteModal = false">
                                    <Button variant="black">Cancel</Button>
                                </DialogClose>
                                    <Button Button variant="delete" @click="deleteFile" :disabled="form.processing">
                                    {{ form.processing ? 'Deleting...' : 'Delete' }}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                            </Dialog>

                            <!-- Save Button -->
                            <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-[#10B981] hover:bg-[#0e9459] px-4 py-2 text-sm font-medium border-[#0c0a03] border-2 pixel-outline duration-300"
                            :disabled="form.processing"
                            >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add to Collection Modal -->
        <div v-if="showCollectionModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity" @click="showCollectionModal = false"></div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Add File to Collection</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                            Select a collection to add this file to. You can also create a new collection from the
                            <Link href="/collections/create" class="text-indigo-600 underline hover:text-indigo-500"> Collections page </Link>.
                        </p>

                        <div v-if="userCollections.length === 0" class="py-4 text-center">
                            <p class="text-sm text-gray-500 dark:text-gray-400">No collections found.</p>
                            <Link href="/collections/create" class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                                Create your first collection
                            </Link>
                        </div>

                        <div v-else class="max-h-64 space-y-2 overflow-y-auto">
                            <div
                                v-for="collection in userCollections"
                                :key="collection.id"
                                class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 p-3 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                                :class="{
                                    'border-blue-200 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/20': selectedCollection === collection.id,
                                    'bg-white dark:bg-gray-800': selectedCollection !== collection.id,
                                }"
                                @click="
                                    selectedCollection = collection.id;
                                    addToCollection();
                                "
                            >
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ collection.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ collection.file_count }} file(s) • {{ collection.is_public ? 'Public' : 'Private' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                        <Button variant="outline" @click="showCollectionModal = false">Close</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
