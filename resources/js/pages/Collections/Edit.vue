<template>
    <Head :title="`Edit ${collection.name}`" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Edit Collection: {{ collection.name }}</h2>
        </template>

        <div class="bg-gradient min-h-screen py-12">
            <div class="mx-4 mx-auto mb-6 flex max-w-md justify-center sm:order-2 sm:flex-1">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline px-6 py-2 text-center text-2xl leading-tight font-bold">
                    Edit Collection
                </h1>
            </div>
            <div class="mx-full sm:px-6 lg:px-8">
                <div class="bg-container mx-4 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Collection Name -->
                            <div class="mb-6">
                                <label for="name" class="text-md pixel-outline mb-2 block font-medium text-gray-300"> Collection Name * </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded-md border-yellow-500 bg-black/60 px-3 py-2 shadow-sm"
                                    required
                                />
                                <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <label for="description" class="text-md pixel-outline mb-2 block font-medium text-gray-300"> Description </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-md border-yellow-300 bg-black/60 px-3 py-2 shadow-sm"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Privacy Setting -->
                            <div class="mb-6">
                                <label class="text-md pixel-outline mb-2 block font-medium text-gray-300"> Visibility </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        @click="form.is_public = false"
                                        :class="
                                            !form.is_public
                                                ? 'border-gray-500 bg-black/50 ring-2 ring-indigo-500 transition-transform duration-500 hover:scale-105'
                                                : 'border-gray-300 bg-black/30 transition-transform duration-500 hover:scale-105 hover:border-gray-300 dark:border-gray-600'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                class="pixel-outline-icon mr-3 h-5 w-5 text-indigo-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                ></path>
                                            </svg>
                                            <div>
                                                <h3 class="pixel-outline font-medium">Private</h3>
                                                <p class="pixel-outline text-sm text-gray-500">Only you can see this collection</p>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="form.is_public = true"
                                        :class="
                                            form.is_public
                                                ? 'border-green-500 bg-black/50 ring-2 ring-green-500 transition-transform duration-500 hover:scale-105'
                                                : 'border-gray-600 bg-black/30 transition-transform duration-500 hover:scale-105 hover:border-green-300'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg
                                                class="pixel-outline-icon mr-3 h-5 w-5 text-green-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                            <div>
                                                <h3 class="pixel-outline font-medium">Public</h3>
                                                <p class="pixel-outline text-sm text-gray-500">Others can discover and copy this collection</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="mb-6">
                                <label for="tags" class="text-md pixel-outline mb-2 block font-medium text-gray-300"> Tags </label>
                                <div class="mb-2 flex flex-wrap gap-2">
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="pixel-outline inline-flex items-center rounded-full bg-blue-900 px-2 py-1 text-sm text-blue-200"
                                    >
                                        {{ tag }}
                                        <button type="button" @click="removeTag(index)" class="ml-1 text-blue-600 hover:text-blue-800">
                                            <svg class="pixel-outline-icon h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                                <div class="flex">
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        class="flex-1 rounded-l-md border-gray-300 bg-black/60 px-3 py-2 shadow-sm"
                                        placeholder="Add a tag..."
                                        @keyup.enter="addTag"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="pixel-outline rounded-r-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                    >
                                        Add
                                    </button>
                                </div>
                                <div v-if="form.errors.tags" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.tags }}
                                </div>
                            </div>

                            <!-- File Management -->
                            <div class="mb-6">
                                <label class="text-md pixel-outline mb-2 block font-medium text-gray-300"> Manage Files </label>
                                <p class="pixel-outline mb-4 text-sm text-gray-400">Select and reorder files in your collection. Drag to reorder.</p>

                                <!-- Current Files -->
                                <div class="mb-4">
                                    <h4 class="text-md pixel-outline mb-2 font-medium text-gray-300">
                                        Files in Collection ({{ selectedFiles.length }})
                                    </h4>
                                    <div class="max-h-48 space-y-2 overflow-y-auto rounded-md border border-blue-600 bg-black/50 p-2">
                                        <div
                                            v-for="(fileId, index) in form.file_ids"
                                            :key="fileId"
                                            class="flex items-center justify-between rounded border border-blue-800 bg-blue-900/30 p-2"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <span class="pixel-outline font-mono text-sm text-gray-400">{{ index + 1 }}.</span>
                                                <span class="pixel-outline text-sm font-medium">
                                                    {{ getFileName(fileId) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    v-if="index > 0"
                                                    type="button"
                                                    @click="moveFileUp(index)"
                                                    class="text-gray-400 hover:text-gray-600"
                                                    title="Move up"
                                                >
                                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 15l7-7 7 7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    v-if="index < form.file_ids.length - 1"
                                                    type="button"
                                                    @click="moveFileDown(index)"
                                                    class="text-gray-400 hover:text-gray-600"
                                                    title="Move down"
                                                >
                                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        ></path>
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="removeFileFromCollection(index)"
                                                    class="text-red-600 hover:text-red-800"
                                                    title="Remove from collection"
                                                >
                                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        ></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Available Files -->
                                <div>
                                    <h4 class="text-md pixel-outline mb-2 font-medium text-gray-300">Available Files</h4>
                                    <div class="max-h-48 space-y-2 overflow-y-auto rounded-md border border-green-500 bg-black/50 p-2">
                                        <label
                                            v-for="file in availableFiles"
                                            :key="file.id"
                                            class="flex cursor-pointer items-center space-x-3 rounded p-2 hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="file.id"
                                                @change="toggleFile(file.id)"
                                                :checked="form.file_ids.includes(file.id)"
                                                class="focus:ring-opacity-50 pixel-outline-icon rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200"
                                            />
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="pixel-outline text-sm font-medium text-gray-100">
                                                        {{ file.title || file.name }}
                                                    </span>
                                                    <span class="pixel-outline text-center text-xs text-gray-400">
                                                        {{ file.quizzes_count || 0 }} questions
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div v-if="form.errors.file_ids" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.file_ids }}
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between">
                                <Link :href="route('collections.show', collection.id)" class="pixel-outline text-red-400 hover:text-red-100">
                                    ← Back to Details
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="pixel-outline inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                >
                                    <svg
                                        v-if="form.processing"
                                        class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Update Collection
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface File {
    id: number;
    name: string;
    title?: string;
    description?: string;
    quizzes_count: number;
}

interface Collection {
    id: number;
    name: string;
    description?: string;
    is_public: boolean;
    tags: string[];
    files: File[];
}

const props = defineProps<{
    collection: Collection;
    userFiles: File[];
}>();

const newTag = ref('');

const form = useForm({
    name: props.collection.name,
    description: props.collection.description || '',
    is_public: props.collection.is_public,
    tags: [...(props.collection.tags || [])],
    file_ids: props.collection.files.map((file) => file.id),
});

const selectedFiles = computed(() => props.collection.files.filter((file) => form.file_ids.includes(file.id)));

const availableFiles = computed(() => props.userFiles.filter((file) => !form.file_ids.includes(file.id)));

const addTag = () => {
    const tag = newTag.value.trim();
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
        newTag.value = '';
    }
};

const removeTag = (index: number) => {
    form.tags.splice(index, 1);
};

const toggleFile = (fileId: number) => {
    const index = form.file_ids.indexOf(fileId);
    if (index === -1) {
        form.file_ids.push(fileId);
    } else {
        form.file_ids.splice(index, 1);
    }
};

const moveFileUp = (index: number) => {
    if (index > 0) {
        const temp = form.file_ids[index];
        form.file_ids[index] = form.file_ids[index - 1];
        form.file_ids[index - 1] = temp;
    }
};

const moveFileDown = (index: number) => {
    if (index < form.file_ids.length - 1) {
        const temp = form.file_ids[index];
        form.file_ids[index] = form.file_ids[index + 1];
        form.file_ids[index + 1] = temp;
    }
};

const removeFileFromCollection = (index: number) => {
    form.file_ids.splice(index, 1);
};

const getFileName = (fileId: number) => {
    const file = [...props.collection.files, ...props.userFiles].find((f) => f.id === fileId);
    return file ? file.title || file.name : `File ${fileId}`;
};

const submit = () => {
    form.put(route('collections.update', props.collection.id));
};
</script>
