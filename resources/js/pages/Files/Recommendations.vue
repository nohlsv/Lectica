<script setup lang="ts">
import FileCard from '@/components/FileCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Props {
    recommendations: {
        program: File[];
        collaborative: File[];
        contentBased: File[];
        trending: File[];
    };
    userProgram: string | null;
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

// Define breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: 'Recommendations',
        href: '/recommendations',
    },
];

// Check if we have any recommendations
const hasRecommendations = computed(() => {
    return (
        props.recommendations.program.length > 0 ||
        props.recommendations.collaborative.length > 0 ||
        props.recommendations.contentBased.length > 0 ||
        props.recommendations.trending.length > 0
    );
});

// Format program name for display
const formattedProgram = computed(() => {
    return props.userProgram || 'Your Program';
});

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

const openCollectionModal = (file: File) => {
    selectedFileForCollection.value = file;
    selectedCollection.value = null;
    showCollectionModal.value = true;
    fetchUserCollections();
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
    <Head title="File Recommendations" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Recommended Files</h1>
                <p class="text-muted-foreground text-sm">Discover files that might interest you based on your program and activity</p>
            </div>

            <div v-if="hasRecommendations" class="space-y-8">
                <!-- Program-based Recommendations -->
                <div v-if="recommendations.program.length > 0" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">From {{ formattedProgram }}</h2>
                        <p class="text-muted-foreground text-sm">Files from users in your program</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <FileCard v-for="file in recommendations.program" :key="file.id" :file="file" @add-to-collection="openCollectionModal" />
                    </div>
                </div>

                <!-- Collaborative Filtering Recommendations -->
                <div v-if="recommendations.collaborative.length > 0" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Users Like You Also Starred</h2>
                        <p class="text-muted-foreground text-sm">Based on similar user preferences</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <FileCard
                            v-for="file in recommendations.collaborative"
                            :key="file.id"
                            :file="file"
                            @add-to-collection="openCollectionModal"
                        />
                    </div>
                </div>

                <!-- Content-based Recommendations -->
                <div v-if="recommendations.contentBased.length > 0" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Similar Content</h2>
                        <p class="text-muted-foreground text-sm">Files with similar tags and content</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <FileCard v-for="file in recommendations.contentBased" :key="file.id" :file="file" @add-to-collection="openCollectionModal" />
                    </div>
                </div>

                <!-- Trending Files -->
                <div v-if="recommendations.trending.length > 0" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Trending Now</h2>
                        <p class="text-muted-foreground text-sm">Popular files recently starred by many users</p>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <FileCard v-for="file in recommendations.trending" :key="file.id" :file="file" @add-to-collection="openCollectionModal" />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
                <div class="mx-auto max-w-md">
                    <svg class="text-muted-foreground mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                        ></path>
                    </svg>
                    <h3 class="text-foreground mt-2 text-sm font-medium">No recommendations yet</h3>
                    <p class="text-muted-foreground mt-1 text-sm">
                        Start starring files and engaging with content to get personalized recommendations.
                    </p>
                    <div class="mt-6">
                        <Link
                            href="/files"
                            class="bg-primary hover:bg-primary/90 inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm"
                        >
                            Browse All Files
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add to Collection Modal -->
        <div v-if="showCollectionModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity" @click="showCollectionModal = false"></div>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="mb-4 text-lg leading-6 font-medium text-gray-900">Add File to Collection</h3>
                        <div v-if="userCollections.length === 0" class="py-4 text-center">
                            <p class="text-sm text-gray-500">No collections found.</p>
                        </div>
                        <div v-else class="max-h-64 space-y-2 overflow-y-auto">
                            <div
                                v-for="collection in userCollections"
                                :key="collection.id"
                                class="flex cursor-pointer items-center justify-between rounded-lg border p-3 hover:bg-gray-50"
                                :class="{ 'border-blue-200 bg-blue-50': selectedCollection === collection.id }"
                                @click="selectedCollection = collection.id"
                            >
                                <div>
                                    <p class="text-sm font-medium">{{ collection.name }}</p>
                                    <p class="text-xs text-gray-500">{{ collection.file_count }} file(s)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button
                            @click="addToCollection"
                            :disabled="!selectedCollection"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Add to Collection
                        </button>
                        <button
                            @click="showCollectionModal = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
