<template>
    <Head title="Create Collection" />

    <AppLayout>
        <div class="min-h-screen bg-[#28231d] py-12">
            <!-- Hero Section -->
            <div class="mx-auto mb-8 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="welcome-banner">
                        <h1 class="pixel-outline mb-2 text-4xl font-bold tracking-wide text-[#FFF8DC]">Create Collection</h1>
                        <p class="pixel-outline mb-4 text-lg text-[#FFF8DC]/80">Organize your study materials into a collection</p>
                        <div class="mx-auto h-1 w-24 rounded-full bg-[#ffd700]"></div>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Main Create Form -->
                <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-8">
                    <div class="text-[#FFF8DC]">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Collection Name -->
                            <div class="space-y-3">
                                <label for="name" class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-[#FFF8DC]">
                                    <svg class="pixel-outline-icon h-5 w-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a.997.997 0 01-1.414 0l-7-7A1.997 1.997 0 013 12V7a4 4 0 014-4z"
                                        />
                                    </svg>
                                    <span>Collection Name</span>
                                    <span class="text-red-400">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-4 py-3 text-[#FFF8DC] placeholder-[#FFF8DC]/50 transition-all duration-200 focus:border-[#ffd700] focus:ring-2 focus:ring-[#ffd700]"
                                    placeholder="Enter collection name..."
                                    required
                                />
                                <div v-if="form.errors.name" class="pixel-outline flex items-center space-x-1 text-sm text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <span>{{ form.errors.name }}</span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-3">
                                <label for="description" class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-white">
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <span>Description</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full resize-none rounded-xl border border-gray-600/50 bg-gradient-to-r from-gray-700/50 to-gray-800/50 px-4 py-3 text-white placeholder-gray-400 transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-500"
                                    placeholder="Describe your collection..."
                                ></textarea>
                                <div v-if="form.errors.description" class="pixel-outline flex items-center space-x-1 text-sm text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <span>{{ form.errors.description }}</span>
                                </div>
                            </div>

                            <!-- Visibility -->
                            <div class="space-y-3">
                                <label class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-white">
                                    <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    <span>Visibility</span>
                                </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        @click="updatePublicSetting(false)"
                                        :class="
                                            !form.is_public
                                                ? 'border-indigo-500 bg-gradient-to-br from-indigo-600/30 to-purple-700/30 ring-2 ring-indigo-400/50'
                                                : 'border-gray-600 bg-gradient-to-br from-gray-700/30 to-gray-800/30 hover:border-indigo-400'
                                        "
                                        class="rounded-xl border-2 p-6 text-left transition-all duration-300 hover:scale-105 hover:shadow-lg"
                                    >
                                        <div class="flex items-start space-x-4">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600"
                                            >
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="pixel-outline mb-1 text-lg font-semibold text-white">Private</h3>
                                                <p class="pixel-outline text-sm leading-relaxed text-gray-400">
                                                    Only you can see and edit this collection
                                                </p>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="updatePublicSetting(true)"
                                        :class="
                                            form.is_public
                                                ? 'border-green-500 bg-gradient-to-br from-green-600/30 to-emerald-700/30 ring-2 ring-green-400/50'
                                                : 'border-gray-600 bg-gradient-to-br from-gray-700/30 to-gray-800/30 hover:border-green-400'
                                        "
                                        class="rounded-xl border-2 p-6 text-left transition-all duration-300 hover:scale-105 hover:shadow-lg"
                                    >
                                        <div class="flex items-start space-x-4">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-emerald-600"
                                            >
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="pixel-outline mb-1 text-lg font-semibold text-white">Public</h3>
                                                <p class="pixel-outline text-sm leading-relaxed text-gray-400">
                                                    Others can discover, view and copy this collection
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="space-y-3">
                                <label for="tags" class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-white">
                                    <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a.997.997 0 01-1.414 0l-7-7A1.997 1.997 0 013 12V7a4 4 0 014-4z"
                                        />
                                    </svg>
                                    <span>Tags</span>
                                </label>
                                <div class="mb-4 flex min-h-[2rem] flex-wrap gap-2">
                                    <span
                                        v-for="(tag, index) in form.tags"
                                        :key="index"
                                        class="pixel-outline inline-flex items-center rounded-full bg-gradient-to-r from-blue-600 to-purple-600 px-3 py-1 text-sm font-medium text-white transition-all duration-200 hover:scale-105"
                                    >
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="ml-2 text-blue-200 transition-colors duration-200 hover:text-white"
                                            title="Remove tag"
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    <span v-if="form.tags.length === 0" class="pixel-outline text-sm text-gray-500 italic"> No tags added yet </span>
                                </div>
                                <div
                                    class="flex overflow-hidden rounded-xl border border-gray-600/50 bg-gradient-to-r from-gray-700/50 to-gray-800/50"
                                >
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        class="flex-1 bg-transparent px-4 py-3 text-white placeholder-gray-400 focus:outline-none"
                                        placeholder="Add a tag..."
                                        @keyup.enter="addTag"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-3 font-medium text-white transition-all duration-200 hover:from-blue-700 hover:to-purple-700 focus:ring-2 focus:ring-blue-500 focus:outline-none focus:ring-inset"
                                    >
                                        Add
                                    </button>
                                </div>
                                <div v-if="form.errors.tags" class="pixel-outline flex items-center space-x-1 text-sm text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                    <span>{{ form.errors.tags }}</span>
                                </div>
                            </div>

                            <!-- File Selection -->
                            <div class="space-y-6">
                                <div class="text-center">
                                    <h3 class="pixel-outline mb-2 flex items-center justify-center space-x-2 text-2xl font-bold text-white">
                                        <svg class="h-6 w-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        <span>Select Files</span>
                                    </h3>
                                    <p class="pixel-outline text-gray-400">Choose files to include in your collection</p>
                                    <div class="mx-auto mt-2 h-1 w-16 rounded-full bg-gradient-to-r from-cyan-500 to-blue-500"></div>
                                </div>

                                <div
                                    class="rounded-xl border border-cyan-500/30 bg-gradient-to-br from-cyan-900/20 to-blue-900/20 p-6 backdrop-blur-sm"
                                >
                                    <div v-if="userFiles.length === 0" class="py-8 text-center">
                                        <svg class="mx-auto mb-4 h-16 w-16 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                            />
                                        </svg>
                                        <h4 class="pixel-outline mb-2 text-lg font-semibold text-yellow-100">No Files Available</h4>
                                        <p class="pixel-outline mb-4 text-yellow-200">
                                            You need to upload some files first before creating a collection
                                        </p>
                                        <Link
                                            :href="route('files.create')"
                                            class="pixel-outline inline-flex items-center space-x-2 rounded-xl bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-3 font-medium text-white transition-all duration-200 hover:scale-105 hover:from-yellow-700 hover:to-orange-700"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                />
                                            </svg>
                                            <span>Upload Files</span>
                                        </Link>
                                    </div>

                                    <div v-else class="custom-scrollbar max-h-64 space-y-2 overflow-y-auto">
                                        <label
                                            v-for="file in userFiles"
                                            :key="file.id"
                                            class="group flex cursor-pointer items-center space-x-4 rounded-lg border border-cyan-600/30 bg-gradient-to-r from-cyan-800/20 to-blue-800/20 p-4 transition-all duration-200 hover:border-cyan-400/70 hover:bg-gradient-to-r hover:from-cyan-800/30 hover:to-blue-800/30"
                                        >
                                            <div class="relative">
                                                <input
                                                    type="checkbox"
                                                    :value="file.id"
                                                    v-model="form.file_ids"
                                                    class="h-5 w-5 rounded border-2 border-cyan-400 bg-transparent text-cyan-500 transition-all duration-200 focus:ring-2 focus:ring-cyan-400 focus:ring-offset-0"
                                                />
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="pixel-outline font-medium text-white transition-colors duration-200 group-hover:text-cyan-100"
                                                    >
                                                        {{ file.title || file.name }}
                                                    </span>
                                                    <span class="rounded-full bg-cyan-600/20 px-2 py-1 text-xs font-medium text-cyan-300">
                                                        {{ file.quizzes_count || 0 }} questions
                                                    </span>
                                                </div>
                                                <p v-if="file.description" class="pixel-outline mt-1 text-sm leading-relaxed text-gray-400">
                                                    {{ file.description }}
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                    <div v-if="form.errors.file_ids" class="pixel-outline mt-4 flex items-center space-x-1 text-sm text-red-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <span>{{ form.errors.file_ids }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between border-t border-gray-600/50 pt-6">
                                <Link
                                    :href="route('collections.index')"
                                    class="pixel-outline inline-flex items-center space-x-2 rounded-xl bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-3 font-medium text-white transition-all duration-200 hover:scale-105 hover:from-gray-700 hover:to-gray-800"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <span>Back to Collections</span>
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="pixel-outline inline-flex items-center space-x-2 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-3 font-semibold text-white transition-all duration-200 hover:scale-105 hover:from-green-700 hover:to-emerald-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-gray-800 focus:outline-none disabled:transform-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <svg
                                        v-if="form.processing"
                                        class="h-5 w-5 animate-spin"
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
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span>{{ form.processing ? 'Creating...' : 'Create Collection' }}</span>
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
import { ref } from 'vue';

interface File {
    id: number;
    name: string;
    title?: string;
    description?: string;
    quizzes_count: number;
}

const props = defineProps<{
    userFiles: File[];
}>();

const newTag = ref('');

const form = useForm({
    name: '',
    description: '',
    is_public: false as boolean,
    tags: [] as string[],
    file_ids: [] as number[],
});

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

const updatePublicSetting = (isPublic: boolean) => {
    form.is_public = isPublic;
};

const submit = () => {
    form.post(route('collections.store'));
};
</script>

<style scoped>
/* Custom scrollbar for file lists */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #06b6d4, #3b82f6);
    border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #0891b2, #2563eb);
}

/* Custom checkbox styling */
input[type='checkbox']:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='m13.854 3.646-7.5 7.5a.5.5 0 0 1-.708 0l-3-3a.5.5 0 1 1 .708-.708L6 10.293l7.146-7.147a.5.5 0 0 1 .708.708z'/%3e%3c/svg%3e");
}

/* Animation for form elements */
.form-element {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
