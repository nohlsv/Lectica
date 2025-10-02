<template>
    <Head :title="collection.name" />
    <AppLayout>
    <div class="bg-gradient min-h-screen space-y-6 p-6 sm:px-6 lg:px-8">
        <div class="flex mx-auto max-w-lg justify-center sm:order-2 sm:flex-1 mb-6">
            <h1 class="welcome-banner animate-soft-bounce px-6 py-2 text-center text-2xl leading-tight font-bold pixel-outline">Collection Details</h1>
        </div>
        <Link
            :href="route('collections.index')"
            class="text-red-400 pixel-outline">
            ← Back to Collections
        </Link>
        <!-- Main Collection Info Card (merged main info and description, with battle actions and buttons) -->
        <div class="my-2 mx-full overflow-hidden bg-container shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div class="gap-2 p-6">
                <div class="flex flex-col bg-black/60 -mx-5.5 px-4 py-4">
                    <div class="flex items-center gap-4">
                        <h1 class="text-xl font-bold text-gray-100 pixel-outline">{{ collection.name }}</h1>
                        <span
                            v-if="!collection.is_public"
                            class="inline-flex items-center rounded-full bg-gray-700 px-2 py-1 text-xs font-medium text-gray-300 pixel-outline"
                        >
                            Private
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center rounded-full bg-green-900 px-2 py-1 text-xs font-medium text-green-300 pixel-outline"
                        >
                            Public
                        </span>
                        <span
                            v-if="!collection.is_original"
                            class="inline-flex items-center rounded-full bg-blue-900 px-2 py-1 text-xs font-medium text-blue-300 pixel-outline"
                        >
                            Copy
                        </span>
                    </div>
                    <div v-if="collection.description" class="mt-2 text-gray-700 dark:text-gray-300">
                        {{ collection.description }}
                    </div>
                </div>
                <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400 pixel-outline">
                    <span class="flex items-center">
                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            ></path>
                        </svg>
                        Owner: {{ collection.user.first_name }} {{ collection.user.last_name }}
                    </span>
                    <span class="flex items-center">
                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            ></path>
                        </svg>
                        {{ collection.file_count }} files
                    </span>
                    <span class="flex items-center">
                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        {{ collection.total_questions }} questions
                    </span>
                    <span v-if="collection.copy_count > 0" class="flex items-center">
                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            ></path>
                        </svg>
                        Copied {{ collection.copy_count }} times
                    </span>
                </div>
                <!-- Battle Actions and Buttons -->
                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        v-if="canCopy"
                        @click="copyCollection"
                        class="inline-flex items-center pixel-outline rounded-md border border-transparent bg-blue-600 px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900 dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                        </svg>
                        Copy Collection
                    </button>
                    <button
                        @click="toggleFavorite"
                        :class="
                            isFavorited
                                ? 'bg-red-600 hover:bg-red-700 focus:bg-red-700 focus:ring-red-500 active:bg-red-900'
                                : 'bg-gray-600 hover:bg-gray-700 focus:bg-gray-700 focus:ring-gray-500 active:bg-gray-900'
                        "
                        class="inline-flex items-center pixel-outline rounded-md border border-transparent px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out focus:ring-2 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                            />
                        </svg>
                        {{ isFavorited ? 'Unfavorite' : 'Favorite' }}
                    </button>
                    <Link
                        v-if="canEdit"
                        :href="route('collections.edit', collection.id)"
                        class="inline-flex items-center pixel-outline rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Edit
                    </Link>
                    <Link
                        :href="route('battles.create', { collection_id: collection.id })"
                        class="inline-flex items-center pixel-outline rounded-md border border-transparent bg-red-600 px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-red-700 focus:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none active:bg-red-900"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Solo Battle
                    </Link>
                    <Link
                        :href="route('multiplayer-games.lobby', { collection_id: collection.id })"
                        class="inline-flex items-center pixel-outline rounded-md border border-transparent bg-purple-600 px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-purple-700 focus:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none active:bg-purple-900"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>
                        Multiplayer Battle
                    </Link>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="mx-full sm:-mx-5.5 sm:px-6 lg:px-8">
                <!-- Files in Collection -->
                <div class="overflow-hidden bg-black/60 rounded-md -mt-8 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-md sm:text-lg font-medium text-yellow-500 pixel-outline animate-soft-bounce">Files in Collection ({{ collection.files.length }})</h3>
                            <div v-if="canEdit" class="flex space-x-2">
                                <button
                                    @click="showAddFileModal = true"
                                    class="inline-flex items-center rounded-md border border-transparent pixel-outline bg-green-600 px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none active:bg-green-900"
                                >
                                    <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add File
                                </button>
                            </div>
                        </div>

                        <!-- Files List -->
                        <div v-if="collection.files.length > 0" class="space-y-4">
                            <div
                                v-for="(file, index) in collection.files"
                                :key="file.id"
                                class="flex flex-col items-start justify-between rounded-lg bg-yellow-700 p-4 sm:flex-row sm:items-center"
                            >
                                <div class="flex flex-col items-start space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                                    <div class="flex-shrink-0 pixel-outline-icon">
                                        <span
                                            class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-indigo-100 text-xs font-medium text-black sm:h-6 sm:w-6 sm:text-sm"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-100 pixel-outline sm:mt-0">
                                            {{ file.title || file.name }}
                                        </h4>
                                        <p v-if="file.description" class="text-sm text-gray-400 pixel-outline">
                                            {{ file.description }}
                                        </p>
                                        <div class="mt-1 flex space-y-1 gap-3 text-xs text-gray-400 pixel-outline sm:flex-row sm:space-y-0 sm:space-x-4">
                                            <span>{{ file.quizzes?.length || 0 }} questions</span>
                                            <span>by {{ file.user.first_name }} {{ file.user.last_name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 mt-1">
                                    <Link
                                        :href="route('files.show', file.id)"
                                        class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-500 pixel-outline hover:underline"
                                    >
                                        View
                                    </Link>
                                    <button
                                        v-if="canEdit"
                                        @click="removeFile(file)"
                                        class="text-sm text-red-600 hover:text-red-900 dark:text-red-400 pixel-outline hover:underline"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                ></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-100 pixel-outline">No files in collection</h3>
                            <p class="mt-1 text-sm text-gray-400 pixel-outline">This collection doesn't contain any files yet.</p>
                            <div v-if="canEdit" class="mt-6">
                                <button
                                    @click="showAddFileModal = true"
                                    class="inline-flex items-center rounded-md border border-transparent pixel-outline bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                                >
                                    Add Files
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add File Modal -->
        <div v-if="showAddFileModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity" aria-hidden="true" @click="showAddFileModal = false"></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Add File to Collection</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Select a file to add to this collection.</p>
                        <!-- Add file selection UI here -->
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                        <button
                            @click="showAddFileModal = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    collection: any;
    canEdit?: boolean;
    canCopy?: boolean;
}>();

const { collection, canEdit, canCopy } = props;

const showAddFileModal = ref(false);
const isFavorited = ref(collection.is_favorited);

const toggleFavorite = async () => {
    try {
        await router.post(
            route('collections.favorite', collection.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Update local state instead of mutating prop
                    isFavorited.value = !isFavorited.value;
                },
            },
        );
    } catch (error) {
        console.error('Failed to toggle favorite:', error);
    }
};

const copyCollection = () => {
    router.post(route('collections.copy', props.collection.id));
};

const removeFile = (file: any) => {
    if (confirm(`Remove "${file.title || file.name}" from this collection?`)) {
        router.delete(route('collections.remove-file', props.collection.id), {
            data: { file_id: file.id },
            preserveScroll: true,
        });
    }
};
</script>
