<template>
    <Head title="Create Collection" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Create New Collection
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Collection Name -->
                            <div class="mb-6">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Collection Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Enter collection name..."
                                    required
                                >
                                <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Describe your collection..."
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Privacy Setting -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Visibility
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button
                                        type="button"
                                        @click="form.is_public = false"
                                        :class="!form.is_public
                                            ? 'ring-2 ring-gray-500 border-gray-500 bg-gray-50 dark:bg-gray-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-gray-300'"
                                        class="p-4 border rounded-lg text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            <div>
                                                <h3 class="font-medium">Private</h3>
                                                <p class="text-sm text-gray-500">Only you can see this collection</p>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="form.is_public = true"
                                        :class="form.is_public
                                            ? 'ring-2 ring-green-500 border-green-500 bg-green-50 dark:bg-green-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-green-300'"
                                        class="p-4 border rounded-lg text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div>
                                                <h3 class="font-medium">Public</h3>
                                                <p class="text-sm text-gray-500">Others can discover and copy this collection</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="mb-6">
                                <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tags
                                </label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-sm bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="ml-1 text-blue-600 hover:text-blue-800"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                                <div class="flex">
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-l-md shadow-sm"
                                        placeholder="Add a tag..."
                                        @keyup.enter="addTag"
                                    >
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-r-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        Add
                                    </button>
                                </div>
                                <div v-if="form.errors.tags" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.tags }}
                                </div>
                            </div>

                            <!-- File Selection -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Select Files
                                </label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Choose files to include in your collection. You can reorder them later.
                                </p>

                                <div v-if="userFiles.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        You don't have any files yet.
                                        <Link :href="route('files.create')" class="underline font-medium">Upload some files</Link> first.
                                    </p>
                                </div>

                                <div v-else class="space-y-2 max-h-64 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-4">
                                    <label
                                        v-for="file in userFiles"
                                        :key="file.id"
                                        class="flex items-center space-x-3 p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="file.id"
                                            v-model="form.file_ids"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        >
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ file.title || file.name }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ file.quizzes_count || 0 }} questions
                                                </span>
                                            </div>
                                            <p v-if="file.description" class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ file.description }}
                                            </p>
                                        </div>
                                    </label>
                                </div>
                                <div v-if="form.errors.file_ids" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.file_ids }}
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('collections.index')"
                                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                >
                                    ← Back to Collections
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Create Collection
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface File {
    id: number
    name: string
    title?: string
    description?: string
    quizzes_count: number
}

const props = defineProps<{
    userFiles: File[]
}>()

const newTag = ref('')

const form = useForm({
    name: '',
    description: '',
    is_public: false,
    tags: [] as string[],
    file_ids: [] as number[]
})

const addTag = () => {
    const tag = newTag.value.trim()
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag)
        newTag.value = ''
    }
}

const removeTag = (index: number) => {
    form.tags.splice(index, 1)
}

const submit = () => {
    form.post(route('collections.store'))
}
</script>
