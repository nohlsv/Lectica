<template>
    <Head title="My Collections" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">My Collections</h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('collections.browse')"
                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none active:bg-green-900 dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            ></path>
                        </svg>
                        Browse Public
                    </Link>
                    <Link
                        :href="route('collections.create')"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 dark:focus:ring-offset-gray-800"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Collection
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-auto max-w-md flex justify-center mb-6 mx-4">
                <h1 class="welcome-banner animate-soft-bounce px-6 py-2 text-center text-2xl leading-tight font-bold pixel-outline">Collections</h1>
            </div>
            <div class="mx-full sm:px-6 lg:px-8">
                <!-- Filter Tabs -->
                <div class="mb-2 px-4 sm:mb-4">
                    <div class="sm:hidden">
                        <select
                            v-model="filters.type"
                            @change="search"
                            class="block w-full rounded-md shadow-sm border-2 border-yellow-700 text-yellow-600 pixel-outline bg-black/40"
                        >
                            <option value="all" class="text-gray-800 pixel-outline">All Collections</option>
                            <option value="owned" class="text-gray-800 pixel-outline">My Collections</option>
                            <option value="favorited" class="text-gray-800 pixel-outline">Favorites</option>
                            <option value="copied" class="text-gray-800 pixel-outline">Copied</option>
                        </select>
                    </div>
                    <nav class="hidden sm:flex space-x-8 items-center" aria-label="Tabs">
                        <Link
                            :href="route('collections.index', { type: 'all' })"
                            :class="
                                filters.type === 'all'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                            "
                            class="border-b-2 px-1 py-2 text-md font-medium pixel-outline whitespace-nowrap "
                        >
                            All Collections
                        </Link>
                        <Link
                            :href="route('collections.index', { type: 'owned' })"
                            :class="
                                filters.type === 'owned'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                            "
                            class="border-b-2 px-1 py-2 text-md font-medium pixel-outline whitespace-nowrap"
                        >
                            My Collections
                        </Link>
                        <Link
                            :href="route('collections.index', { type: 'favorited' })"
                            :class="
                                filters.type === 'favorited'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                            "
                            class="border-b-2 px-1 py-2 text-md font-medium pixel-outline whitespace-nowrap"
                        >
                            Favorites
                        </Link>
                        <Link
                            :href="route('collections.index', { type: 'copied' })"
                            :class="
                                filters.type === 'copied'
                                    ? 'border-yellow-500 text-yellow-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                            "
                            class="border-b-2 px-1 py-2 text-md font-medium pixel-outline whitespace-nowrap"
                        >
                            Copied
                        </Link>
                    </nav>
                </div>
                <div class="mx-4 my-2">
                        <Link
                            :href="route('collections.create')"
                            class="inline-flex items-center rounded-md pixel-outline border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Collection
                        </Link>
                    </div>
                <!-- Search and Filters -->
                <div class="mb-2 rounded-lg bg-black/40 border-2 border-yellow-700 py-4 px-4 mx-4 shadow">
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <div class="flex-1">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search collections..."
                                class="w-full px-4 pixel-outline rounded-md shadow-sm border-2 border-black text-white bg-black/60"
                                @keyup.enter="search"
                            />
                        </div>
                        <div class="flex gap-2">
                            <select
                                v-model="sortBy"
                                class="text-center rounded-md text-sm pixel-outline border-yellow-700 bg-yellow-800 text-[#fdf6ee] hover:bg-yellow-900"
                                @change="search"
                            >
                                <option class="pixel-outline" value="created_at">Newest</option>
                                <option class="pixel-outline" value="name">Name</option>
                                <option class="pixel-outline" value="files">File Count</option>
                                <option class="pixel-outline" value="popularity">Popularity</option>
                            </select>
                            <button
                                @click="search"
                                class="rounded-md bg-blue-600 text-sm pixel-outline px-4 py-2 text-white hover:bg-blue-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            >
                                Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Collections Grid -->
                <div v-if="collections.data.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="collection in collections.data"
                        :key="collection.id"
                        class="overflow-hidden mx-4 bg-white bg-container shadow-sm transition-shadow hover:shadow-lg sm:rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <!-- Collection Header -->
                            <div class="mb-4 flex items-start justify-between bg-black/60 -mx-5.5 px-4 pt-4">
                                <div class="flex-1">
                                    <div class="mb-2 flex items-center gap-2">
                                        <svg class="h-5 w-5 text-green-500 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            ></path>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 pixel-outline">
                                            {{ collection.name }}
                                        </h3>
                                        <span
                                            v-if="!collection.is_public"
                                            class="inline-flex items-center rounded-full bg-red-700 px-2 py-1 text-xs font-medium text-red-100 pixel-outline"
                                        >
                                            Private
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center rounded-full bg-green-700 px-2 py-1 text-xs font-medium text-green-100 pixel-outline"
                                        >
                                            Public
                                        </span>
                                    </div>
                                    <p v-if="collection.description" class="mb-3 text-sm text-gray-600 dark:text-gray-400 pixel-outline">
                                        {{ collection.description }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button
                                        v-if="collection.can_copy"
                                        @click="copyCollection(collection)"
                                        class="text-gray-400 hover:text-blue-500"
                                        title="Copy Collection"
                                    >
                                        <svg class="h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                    </button>
                                    <button
                                        @click="toggleFavorite(collection)"
                                        :class="collection.is_favorited ? 'text-red-500' : 'text-gray-400 hover:text-red-500'"
                                        title="Toggle Favorite"
                                    >
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Collection Stats -->
                            <div class="mb-4 flex items-center justify-between text-sm text-gray-700 dark:text-gray-400">
                                <div class="flex items-center space-x-4">
                                    <span class="flex items-center pixel-outline">
                                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            ></path>
                                        </svg>
                                        {{ collection.files_count || 0 }} files
                                    </span>
                                    <span class="flex items-center pixel-outline">
                                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        {{ collection.total_questions || 0 }} questions
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2 pixel-outline">
                                    <span v-if="collection.copy_count > 0" class="flex items-center">
                                        <svg class="mr-1 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                        {{ collection.copy_count }}
                                    </span>
                                    <span class="text-xs pixel-outline"> by {{ collection.user.first_name }} {{ collection.user.last_name }} </span>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div v-if="collection.tags && collection.tags.length > 0" class="mb-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in collection.tags.slice(0, 3)"
                                        :key="tag"
                                        class="inline-flex items-center rounded-full bg-blue-700 px-2 py-1 text-xs font-medium text-white pixel-outline"
                                    >
                                        {{ tag }}
                                    </span>
                                    <span v-if="collection.tags.length > 3" class="text-xs text-gray-500 pixel-outline">
                                        +{{ collection.tags.length - 3 }} more
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('collections.show', collection.id)"
                                    class="text-sm font-medium text-indigo-600 pixel-outline hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                >
                                    View Details →
                                </Link>
                                <div v-if="collection.can_edit && collection.is_original" class="flex space-x-2">
                                    <Link
                                        :href="route('collections.edit', collection.id)"
                                        class="text-sm pixel-outline text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteCollection(collection)"
                                        class="text-sm pixel-outline text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
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
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                        ></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100 pixel-outline">No collections found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 pixel-outline">
                        {{ filters.type === 'owned' ? 'Create your first collection to get started.' : 'Try adjusting your search criteria.' }}
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="collections.data.length > 0" class="mt-8">
                    <Pagination :links="collections.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface User {
    id: number;
    first_name: string;
    last_name: string;
}

interface Collection {
    id: number;
    name: string;
    description?: string;
    is_public: boolean;
    is_original: boolean;
    files_count: number;
    total_questions: number;
    copy_count: number;
    tags: string[];
    user: User;
    can_edit: boolean;
    can_copy: boolean;
    is_favorited: boolean;
}

interface Filters {
    type: string;
    search?: string;
    sort: string;
    direction: string;
}

const props = defineProps<{
    collections: {
        data: Collection[];
        links: any[];
    };
    filters: Filters;
}>();

const searchQuery = ref(props.filters.search || '');
const sortBy = ref(props.filters.sort || 'created_at');

const search = () => {
    router.get(
        route('collections.index'),
        {
            type: props.filters.type,
            search: searchQuery.value,
            sort: sortBy.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const toggleFavorite = async (collection: Collection) => {
    try {
        await router.post(
            route('collections.favorite', collection.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    collection.is_favorited = !collection.is_favorited;
                },
            },
        );
    } catch (error) {
        console.error('Failed to toggle favorite:', error);
    }
};

const copyCollection = (collection: Collection) => {
    router.post(route('collections.copy', collection.id));
};

const deleteCollection = (collection: Collection) => {
    if (confirm(`Are you sure you want to delete "${collection.name}"?`)) {
        router.delete(route('collections.destroy', collection.id));
    }
};
</script>
