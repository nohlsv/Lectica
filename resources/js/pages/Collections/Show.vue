<template>
    <Head :title="collection.name" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ collection.name }}
                    </h2>
                    <div class="flex items-center space-x-2">
                        <span v-if="!collection.is_public" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            Private
                        </span>
                        <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            Public
                        </span>
                        <span v-if="!collection.is_original" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            Copy
                        </span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button
                        v-if="canCopy"
                        @click="copyCollection"
                        class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Copy Collection
                    </button>
                    <button
                        @click="toggleFavorite"
                        :class="collection.is_favorited
                            ? 'bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:ring-red-500'
                            : 'bg-gray-600 hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:ring-gray-500'"
                        class="inline-flex items-center px-3 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        {{ collection.is_favorited ? 'Unfavorite' : 'Favorite' }}
                    </button>
                    <Link
                        v-if="canEdit"
                        :href="route('collections.edit', collection.id)"
                        class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Collection Info -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Description and Details -->
                            <div class="lg:col-span-2">
                                <div v-if="collection.description" class="mb-4">
                                    <p class="text-gray-700 dark:text-gray-300">{{ collection.description }}</p>
                                </div>

                                <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Created by {{ collection.user.first_name }} {{ collection.user.last_name }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        {{ collection.file_count }} files
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ collection.total_questions }} questions
                                    </span>
                                    <span v-if="collection.copy_count > 0" class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Copied {{ collection.copy_count }} times
                                    </span>
                                </div>

                                <!-- Original Collection Info -->
                                <div v-if="!collection.is_original && collection.original_creator" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        This is a copy of a collection originally created by
                                        <strong>{{ collection.original_creator.first_name }} {{ collection.original_creator.last_name }}</strong>
                                        <Link
                                            v-if="collection.original_collection"
                                            :href="route('collections.show', collection.original_collection.id)"
                                            class="underline hover:no-underline"
                                        >
                                            View original →
                                        </Link>
                                    </p>
                                </div>

                                <!-- Tags -->
                                <div v-if="collection.tags && collection.tags.length > 0" class="mt-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            v-for="tag in collection.tags"
                                            :key="tag"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >
                                            {{ tag }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Battle Actions -->
                            <div class="space-y-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                        Start Playing
                                    </h3>
                                    <div class="space-y-3">
                                        <Link
                                            :href="route('battles.create', { collection_id: collection.id })"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            Solo Battle
                                        </Link>
                                        <Link
                                            :href="route('multiplayer-games.create', { collection_id: collection.id })"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            Multiplayer Battle
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files in Collection -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Files in Collection ({{ collection.files.length }})
                            </h3>
                            <div v-if="canEdit" class="flex space-x-2">
                                <button
                                    @click="showAddFileModal = true"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ file.title || file.name }}
                                        </h4>
                                        <p v-if="file.description" class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ file.description }}
                                        </p>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <span>{{ file.quizzes?.length || 0 }} questions</span>
                                            <span>by {{ file.user.first_name }} {{ file.user.last_name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Link
                                        :href="route('files.show', file.id)"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm"
                                    >
                                        View
                                    </Link>
                                    <button
                                        v-if="canEdit"
                                        @click="removeFile(file)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No files in collection</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                This collection doesn't contain any files yet.
                            </p>
                            <div v-if="canEdit" class="mt-6">
                                <button
                                    @click="showAddFileModal = true"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAddFileModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Add File to Collection
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Select a file to add to this collection.
                        </p>
                        <!-- Add file selection UI here -->
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="showAddFileModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// ...existing code for types and props...

const showAddFileModal = ref(false)

const toggleFavorite = async () => {
    try {
        await router.post(route('collections.favorite', props.collection.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Update the collection state
                props.collection.is_favorited = !props.collection.is_favorited
            }
        })
    } catch (error) {
        console.error('Failed to toggle favorite:', error)
    }
}

const copyCollection = () => {
    router.post(route('collections.copy', props.collection.id))
}

const removeFile = (file: any) => {
    if (confirm(`Remove "${file.title || file.name}" from this collection?`)) {
        router.delete(route('collections.remove-file', props.collection.id), {
            data: { file_id: file.id },
            preserveScroll: true
        })
    }
}
</script>
