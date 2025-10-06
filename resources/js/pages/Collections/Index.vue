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

        <div class="bg-gradient min-h-screen py-12">
            <!-- Hero Section -->
            <div class="mx-auto mb-8 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="welcome-banner animate-soft-bounce mx-auto mb-4 w-fit px-10 py-4">
                        <h1 class="pixel-outline text-4xl font-bold tracking-wide">My Collections</h1>
                    </div>
                    <p class="pixel-outline mb-4 text-lg text-[#FFF8DC]">Organize and manage your study materials</p>
                    <div class="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-[#ffd700] to-[#ff6910]"></div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filter Tabs -->
                <div class="mb-8">
                    <!-- Mobile Select -->
                    <div class="sm:hidden">
                        <select
                            v-model="filters.type"
                            @change="search"
                            class="pixel-outline block w-full rounded-xl border-2 border-purple-500/50 bg-gray-800/90 px-4 py-3 text-purple-300 shadow-sm"
                        >
                            <option value="all" class="bg-gray-800 text-white">All Collections</option>
                            <option value="owned" class="bg-gray-800 text-white">My Collections</option>
                            <option value="favorited" class="bg-gray-800 text-white">Favorites</option>
                            <option value="copied" class="bg-gray-800 text-white">Copied</option>
                        </select>
                    </div>

                    <!-- Desktop Tabs -->
                    <div class="hidden sm:block">
                        <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-2">
                            <nav class="flex space-x-2" aria-label="Tabs">
                                <Link
                                    :href="route('collections.index', { type: 'all' })"
                                    :class="
                                        filters.type === 'all'
                                            ? 'border-[#ffd700] bg-[#b71400] text-white shadow-lg'
                                            : 'border-[#0c0a03] text-[#FFF8DC] hover:bg-[#28231d] hover:text-white'
                                    "
                                    class="pixel-outline rounded-xl border-2 px-6 py-3 font-medium whitespace-nowrap transition-all duration-300"
                                >
                                    All Collections
                                </Link>
                                <Link
                                    :href="route('collections.index', { type: 'owned' })"
                                    :class="
                                        filters.type === 'owned'
                                            ? 'border-[#ffd700] bg-[#b71400] text-white shadow-lg'
                                            : 'border-[#0c0a03] text-[#FFF8DC] hover:bg-[#28231d] hover:text-white'
                                    "
                                    class="pixel-outline rounded-xl border-2 px-6 py-3 font-medium whitespace-nowrap transition-all duration-300"
                                >
                                    My Collections
                                </Link>
                                <Link
                                    :href="route('collections.index', { type: 'favorited' })"
                                    :class="
                                        filters.type === 'favorited'
                                            ? 'border-[#ffd700] bg-[#b71400] text-white shadow-lg'
                                            : 'border-[#0c0a03] text-[#FFF8DC] hover:bg-[#28231d] hover:text-white'
                                    "
                                    class="pixel-outline rounded-xl border-2 px-6 py-3 font-medium whitespace-nowrap transition-all duration-300"
                                >
                                    Favorites
                                </Link>
                                <Link
                                    :href="route('collections.index', { type: 'copied' })"
                                    :class="
                                        filters.type === 'copied'
                                            ? 'border-[#ffd700] bg-[#b71400] text-white shadow-lg'
                                            : 'border-[#0c0a03] text-[#FFF8DC] hover:bg-[#28231d] hover:text-white'
                                    "
                                    class="pixel-outline rounded-xl border-2 px-6 py-3 font-medium whitespace-nowrap transition-all duration-300"
                                >
                                    Copied
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- Action Bar -->
                <div class="mb-8">
                    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <Link
                            :href="route('collections.create')"
                            class="pixel-outline inline-flex items-center space-x-2 rounded-lg border-2 border-[#0c0a03] bg-[#a85a47] px-6 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-110 hover:bg-[#8d4a3a]"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Create Collection</span>
                        </Link>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="mb-8">
                    <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-6 shadow-xl">
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <div class="flex-1">
                                <div class="relative">
                                    <svg
                                        class="pixel-outline-icon absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-[#ffd700]"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search collections..."
                                        class="pixel-outline w-full rounded-xl border-2 border-[#0c0a03] bg-[#28231d] py-3 pr-4 pl-10 text-[#FFF8DC] placeholder-[#FFF8DC]/60 transition-all duration-300 focus:border-[#ffd700]"
                                        @keyup.enter="search"
                                    />
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <select
                                    v-model="sortBy"
                                    class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#28231d] px-4 py-3 text-[#FFF8DC] transition-all duration-300 focus:border-[#ffd700]"
                                    @change="search"
                                >
                                    <option value="created_at" class="bg-[#28231d] text-[#FFF8DC]">Newest</option>
                                    <option value="name" class="bg-[#28231d] text-[#FFF8DC]">Name</option>
                                    <option value="files" class="bg-[#28231d] text-[#FFF8DC]">File Count</option>
                                    <option value="popularity" class="bg-[#28231d] text-[#FFF8DC]">Popularity</option>
                                </select>
                                <button
                                    @click="search"
                                    class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#b71400] px-6 py-3 font-medium text-white transition-all duration-300 hover:scale-110 hover:bg-[#990f00]"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collections Grid -->
                <div v-if="collections.data.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="collection in collections.data"
                        :key="collection.id"
                        class="bg-container overflow-hidden rounded-2xl border-2 border-[#0c0a03] shadow-xl transition-all duration-300 hover:scale-105 hover:border-[#ffd700] hover:shadow-2xl"
                    >
                        <div class="p-6">
                            <!-- Collection Header -->
                            <div class="mb-4">
                                <div class="mb-3 flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="mb-2 flex items-center gap-2">
                                            <div
                                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-[#ffd700] bg-[#b71400]"
                                            >
                                                <svg
                                                    class="pixel-outline-icon h-4 w-4 text-[#ffd700]"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                    />
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <h3 class="pixel-outline truncate text-lg font-bold text-[#FFF8DC]">
                                                    {{ collection.name }}
                                                </h3>
                                                <div class="mt-1 flex items-center gap-2">
                                                    <span
                                                        v-if="!collection.is_public"
                                                        class="pixel-outline inline-flex items-center rounded-full border border-[#0c0a03] bg-[#b71400] px-2 py-1 text-xs font-medium text-[#FFF8DC]"
                                                    >
                                                        Private
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="pixel-outline inline-flex items-center rounded-full border border-[#0c0a03] bg-[#10B981] px-2 py-1 text-xs font-medium text-white"
                                                    >
                                                        Public
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-if="collection.description" class="pixel-outline line-clamp-2 text-sm text-[#FFF8DC]/80">
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
                                            <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            </div>

                            <!-- Collection Stats -->
                            <div class="mb-4 border-t border-[#ffd700]/30 pt-4">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div class="flex items-center space-x-2 text-[#FFF8DC]">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#0c0a03] bg-[#ff6910]">
                                            <svg class="pixel-outline-icon h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="pixel-outline font-semibold text-[#FFF8DC]">{{ collection.file_count || 0 }}</p>
                                            <p class="pixel-outline text-xs">Files</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 text-[#FFF8DC]">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#0c0a03] bg-[#ffd700]">
                                            <svg class="pixel-outline-icon h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="pixel-outline font-semibold text-[#FFF8DC]">{{ collection.total_questions || 0 }}</p>
                                            <p class="pixel-outline text-xs">Questions</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center justify-between text-xs text-[#FFF8DC]/70">
                                    <span class="pixel-outline">by {{ collection.user.first_name }} {{ collection.user.last_name }}</span>
                                    <span v-if="collection.copy_count > 0" class="pixel-outline flex items-center space-x-1">
                                        <svg class="pixel-outline-icon h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <span>{{ collection.copy_count }}</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div v-if="collection.tags && collection.tags.length > 0" class="mb-4">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="tag in collection.tags.slice(0, 3)"
                                        :key="tag"
                                        class="pixel-outline inline-flex items-center rounded-full border border-[#ffd700] bg-[#28231d] px-3 py-1 text-xs font-medium text-[#ffd700]"
                                    >
                                        {{ tag }}
                                    </span>
                                    <span
                                        v-if="collection.tags.length > 3"
                                        class="pixel-outline inline-flex items-center rounded-full border border-[#0c0a03] bg-[#28231d] px-2 py-1 text-xs text-[#FFF8DC]/70"
                                    >
                                        +{{ collection.tags.length - 3 }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between border-t border-[#ffd700]/30 pt-4">
                                <Link
                                    :href="route('collections.show', collection.id)"
                                    class="pixel-outline inline-flex items-center space-x-2 rounded-lg border-2 border-[#0c0a03] bg-[#b71400] px-4 py-2 text-sm font-medium text-white transition-all duration-300 hover:scale-110 hover:bg-[#990f00]"
                                >
                                    <span>View Details</span>
                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>
                                <div v-if="collection.can_edit && collection.is_original" class="flex space-x-2">
                                    <Link
                                        :href="route('collections.edit', collection.id)"
                                        class="pixel-outline rounded-lg border border-[#ffd700] px-3 py-1 text-sm text-[#ffd700] transition-all duration-300 hover:bg-[#28231d] hover:text-white"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteCollection(collection)"
                                        class="pixel-outline rounded-lg border border-[#ff1640] px-3 py-1 text-sm text-[#ff1640] transition-all duration-300 hover:bg-[#cc1232] hover:text-white"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-16 text-center">
                    <div class="bg-container mx-auto max-w-lg rounded-2xl border-2 border-[#ffd700] p-12">
                        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border-2 border-[#ffd700] bg-[#b71400]">
                            <svg class="pixel-outline-icon h-10 w-10 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                />
                            </svg>
                        </div>
                        <h3 class="pixel-outline mb-3 text-xl font-bold text-[#FFF8DC]">No Collections Found</h3>
                        <p class="pixel-outline mb-6 leading-relaxed text-[#FFF8DC]/80">
                            {{
                                filters.type === 'owned'
                                    ? 'Create your first collection to organize your study materials and get started.'
                                    : 'Try adjusting your search criteria or browse different collection types.'
                            }}
                        </p>
                        <div v-if="filters.type === 'owned'" class="space-y-3">
                            <Link
                                :href="route('collections.create')"
                                class="pixel-outline inline-flex items-center space-x-2 rounded-lg border-2 border-[#0c0a03] bg-[#a85a47] px-6 py-3 font-semibold text-white transition-all duration-300 hover:scale-110 hover:bg-[#8d4a3a]"
                            >
                                <svg class="pixel-outline-icon h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <span>Create Your First Collection</span>
                            </Link>
                            <p class="pixel-outline text-sm text-[#FFF8DC]/70">
                                or
                                <Link :href="route('collections.browse')" class="text-[#ffd700] underline hover:text-[#ff6910]"
                                    >browse public collections</Link
                                >
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="collections.data.length > 0" class="mt-8">
                    <Pagination :links="collections.links as any" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
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
    file_count: number;
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
    currentUserId: number;
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
    const form = useForm({
        name: null
    });
    
    form.post(route('collections.copy', collection.id));
};

const deleteCollection = (collection: Collection) => {
    if (confirm(`Are you sure you want to delete "${collection.name}"?`)) {
        router.delete(route('collections.destroy', collection.id));
    }
};
</script>

<style scoped>
/* Custom line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth animations for collection cards */
.collection-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.collection-card:hover {
    transform: translateY(-4px) scale(1.02);
}

/* Gradient border animation */
@keyframes gradient-border {
    0% {
        border-image-source: linear-gradient(45deg, #3b82f6, #8b5cf6);
    }
    50% {
        border-image-source: linear-gradient(45deg, #8b5cf6, #06b6d4);
    }
    100% {
        border-image-source: linear-gradient(45deg, #06b6d4, #3b82f6);
    }
}

/* Enhanced focus states */
.focus-ring:focus {
    outline: 2px solid rgb(59, 130, 246);
    outline-offset: 2px;
}

/* Better scroll behavior */
html {
    scroll-behavior: smooth;
}
</style>
