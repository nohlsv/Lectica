<template>
    <div class="space-y-6">
        <!-- PvP Win Condition Selection -->
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Battle Mode</label>
            <div class="flex space-x-4">
                <button
                    type="button"
                    @click="$emit('update:pvpMode', 'accuracy')"
                    :class="pvpMode === 'accuracy' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                    class="rounded-md px-4 py-2 text-sm font-medium transition-colors hover:opacity-80"
                >
                    Most Accurate Wins
                </button>
                <button
                    type="button"
                    @click="$emit('update:pvpMode', 'hp')"
                    :class="pvpMode === 'hp' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                    class="rounded-md px-4 py-2 text-sm font-medium transition-colors hover:opacity-80"
                >
                    Most HP Wins
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500">Choose how the winner is determined in PvP battles.</p>
        </div>

        <!-- Source Type Selection -->
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Choose Battle Source</label>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <button
                    type="button"
                    @click="$emit('update:sourceType', 'file')"
                    :class="[
                        'rounded-lg border p-4 text-left transition-colors',
                        sourceType === 'file'
                            ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500 dark:bg-blue-900/20'
                            : 'border-gray-300 hover:border-blue-300 dark:border-gray-600',
                    ]"
                >
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            ></path>
                        </svg>
                        <div>
                            <h3 class="font-medium">Single File</h3>
                            <p class="text-sm text-gray-500">Battle with one specific file</p>
                        </div>
                    </div>
                </button>

                <button
                    type="button"
                    @click="$emit('update:sourceType', 'collection')"
                    :class="[
                        'rounded-lg border p-4 text-left transition-colors',
                        sourceType === 'collection'
                            ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500 dark:bg-blue-900/20'
                            : 'border-gray-300 hover:border-blue-300 dark:border-gray-600',
                    ]"
                >
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            ></path>
                        </svg>
                        <div>
                            <h3 class="font-medium">Collection</h3>
                            <p class="text-sm text-gray-500">Battle with multiple files</p>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- File Selection -->
        <div v-if="sourceType === 'file'">
            <label for="file_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select File</label>
            <select
                id="file_id"
                :value="fileId"
                @change="$emit('update:fileId', $event.target.value)"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                required
            >
                <option value="">Choose a file...</option>
                <option v-for="file in files" :key="file.id" :value="file.id">
                    {{ file.title || file.name }}
                    <span v-if="file.quizzes_count">({{ file.quizzes_count }} questions)</span>
                </option>
            </select>
            <div v-if="errors.file_id" class="mt-2 text-sm text-red-600">
                {{ errors.file_id }}
            </div>
        </div>

        <!-- Collection Selection -->
        <div v-if="sourceType === 'collection'">
            <label for="collection_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Collection</label>
            <div v-if="collections.length === 0" class="rounded-md bg-yellow-50 p-4 dark:bg-yellow-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">No Collections Available</h3>
                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <p>You need to create collections with files first before you can use this option.</p>
                        </div>
                    </div>
                </div>
            </div>
            <select
                v-else
                id="collection_id"
                :value="collectionId"
                @change="$emit('update:collectionId', $event.target.value)"
                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                required
            >
                <option value="">Choose a collection...</option>
                <option v-for="collection in collections" :key="collection.id" :value="collection.id">
                    {{ collection.name }} ({{ collection.file_count }} files, {{ collection.total_questions }} questions)
                </option>
            </select>
            <div v-if="errors.collection_id" class="mt-2 text-sm text-red-600">
                {{ errors.collection_id }}
            </div>
        </div>

        <!-- Game Privacy Settings -->
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Game Privacy</label>
            <div class="space-y-3">
                <label class="flex items-center">
                    <input
                        :checked="isPrivate"
                        @change="$emit('update:isPrivate', $event.target.checked)"
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                    />
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Make this a private game</span>
                </label>
                <p class="text-xs text-gray-500">Private games won't appear in the public lobby. Players will need a game code to join.</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface File {
    id: number;
    name: string;
    title?: string;
    quizzes_count?: number;
}

interface Collection {
    id: number;
    name: string;
    file_count: number;
    total_questions: number;
}

interface Props {
    files: File[];
    collections: Collection[];
    sourceType: 'file' | 'collection';
    fileId: string | number;
    collectionId: string | number;
    pvpMode: 'accuracy' | 'hp';
    isPrivate: boolean;
    errors: Record<string, string>;
}

defineProps<Props>();

defineEmits<{
    'update:sourceType': [value: 'file' | 'collection'];
    'update:fileId': [value: string];
    'update:collectionId': [value: string];
    'update:pvpMode': [value: 'accuracy' | 'hp'];
    'update:isPrivate': [value: boolean];
}>();
</script>
