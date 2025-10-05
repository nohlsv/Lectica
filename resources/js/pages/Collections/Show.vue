<template>
    <Head :title="collection.name" />
    <AppLayout>
        <div class="min-h-screen bg-gradient">
            <!-- Header Section -->
            <div class="bg-container relative overflow-hidden">
                <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <!-- Back Button -->
                    <Link
                        :href="route('collections.index')"
                        class="pixel-outline mb-6 inline-flex items-center gap-2 rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-4 py-2 text-sm font-medium text-[#FFF8DC] transition-all hover:bg-[#b71400] hover:text-white"
                    >
                        <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Collections
                    </Link>

                    <!-- Collection Header -->
                    <div class="flex flex-col space-y-4 lg:flex-row lg:items-start lg:justify-between lg:space-y-0">
                        <div class="flex-1">
                            <div class="mb-3 flex flex-wrap items-center gap-3">
                                <h1 class="text-3xl font-bold text-white lg:text-4xl">{{ collection.name }}</h1>

                                <!-- Status Badges -->
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-if="!collection.is_public"
                                        class="inline-flex items-center rounded-full border border-gray-600 bg-gray-800/60 px-3 py-1 text-xs font-medium text-gray-300"
                                    >
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                            />
                                        </svg>
                                        Private
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full border border-green-600 bg-green-900/60 px-3 py-1 text-xs font-medium text-green-300"
                                    >
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        Public
                                    </span>
                                    <span
                                        v-if="!collection.is_original"
                                        class="inline-flex items-center rounded-full border border-blue-600 bg-blue-900/60 px-3 py-1 text-xs font-medium text-blue-300"
                                    >
                                        <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            />
                                        </svg>
                                        Copy
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            <p v-if="collection.description" class="mb-4 text-lg text-gray-300">
                                {{ collection.description }}
                            </p>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="rounded-lg border border-gray-700 bg-black/40 p-3 text-center backdrop-blur-sm">
                                    <div class="text-2xl font-bold text-white">{{ collection.file_count }}</div>
                                    <div class="text-xs tracking-wide text-gray-400 uppercase">Files</div>
                                </div>
                                <div class="rounded-lg border border-gray-700 bg-black/40 p-3 text-center backdrop-blur-sm">
                                    <div class="text-2xl font-bold text-white">{{ collection.total_questions }}</div>
                                    <div class="text-xs tracking-wide text-gray-400 uppercase">Questions</div>
                                </div>
                                <div class="rounded-lg border border-gray-700 bg-black/40 p-3 text-center backdrop-blur-sm">
                                    <div class="text-2xl font-bold text-white">{{ collection.copy_count || 0 }}</div>
                                    <div class="text-xs tracking-wide text-gray-400 uppercase">Copies</div>
                                </div>
                                <div class="rounded-lg border border-gray-700 bg-black/40 p-3 text-center backdrop-blur-sm">
                                    <div class="text-sm font-medium text-white">{{ collection.user.first_name }} {{ collection.user.last_name }}</div>
                                    <div class="text-xs tracking-wide text-gray-400 uppercase">Owner</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Action Buttons Section -->
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-gray-700 bg-black/40 p-6 shadow-2xl backdrop-blur-sm">
                    <h2 class="mb-6 flex items-center gap-2 text-xl font-bold text-white">
                        <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Quick Actions
                    </h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Battle Actions -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium tracking-wide text-gray-300 uppercase">Battle Mode</h3>
                            <div class="space-y-2">
                                <Link
                                    :href="route('battles.create', { collection_id: collection.id })"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-red-600 bg-gradient-to-r from-red-600 to-red-700 px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-red-700 hover:to-red-800 hover:shadow-lg hover:shadow-red-500/25 focus:ring-2 focus:ring-red-500 focus:outline-none"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Solo Battle
                                </Link>
                                <Link
                                    :href="route('multiplayer-games.lobby', { collection_id: collection.id })"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-purple-600 bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-purple-700 hover:to-purple-800 hover:shadow-lg hover:shadow-purple-500/25 focus:ring-2 focus:ring-purple-500 focus:outline-none"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                        />
                                    </svg>
                                    Multiplayer
                                </Link>
                            </div>
                        </div>

                        <!-- Collection Actions -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium tracking-wide text-gray-300 uppercase">Collection</h3>
                            <div class="space-y-2">
                                <button
                                    v-if="canCopy"
                                    @click="copyCollection"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-blue-600 bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-blue-700 hover:to-blue-800 hover:shadow-lg hover:shadow-blue-500/25 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            ? 'border-pink-600 from-pink-600 to-pink-700 hover:from-pink-700 hover:to-pink-800 hover:shadow-pink-500/25 focus:ring-pink-500'
                                            : 'border-gray-600 from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 hover:shadow-gray-500/25 focus:ring-gray-500'
                                    "
                                    class="inline-flex w-full items-center justify-center rounded-lg border bg-gradient-to-r px-4 py-2.5 text-sm font-medium text-white transition-all hover:shadow-lg focus:ring-2 focus:outline-none"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                                        />
                                    </svg>
                                    {{ isFavorited ? 'Unfavorite' : 'Favorite' }}
                                </button>
                            </div>
                        </div>

                        <!-- Management Actions -->
                        <div v-if="canEdit" class="space-y-3">
                            <h3 class="text-sm font-medium tracking-wide text-gray-300 uppercase">Manage</h3>
                            <div class="space-y-2">
                                <Link
                                    :href="route('collections.edit', collection.id)"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-indigo-600 bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-indigo-700 hover:to-indigo-800 hover:shadow-lg hover:shadow-indigo-500/25 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    Edit Collection
                                </Link>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div v-if="canEdit" class="space-y-3">
                            <h3 class="text-sm font-medium tracking-wide text-red-300 uppercase">Danger Zone</h3>
                            <div class="space-y-2">
                                <button
                                    @click="deleteCollection"
                                    class="inline-flex w-full items-center justify-center rounded-lg border border-red-600 bg-gradient-to-r from-red-900/50 to-red-800/50 px-4 py-2.5 text-sm font-medium text-red-300 transition-all hover:from-red-800 hover:to-red-900 hover:text-white hover:shadow-lg hover:shadow-red-500/25 focus:ring-2 focus:ring-red-500 focus:outline-none"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                    Delete Collection
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-gray-700 bg-black/40 shadow-2xl backdrop-blur-sm">
                    <div class="border-b border-gray-700 px-6 py-4">
                        <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                            <div>
                                <h2 class="flex items-center gap-3 text-2xl font-bold text-white">
                                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    Files in Collection
                                    <span class="rounded-full border border-blue-600 bg-blue-900/50 px-3 py-1 text-sm font-medium text-blue-300">
                                        {{ collection.files.length }}
                                    </span>
                                </h2>
                                <p class="mt-1 text-sm text-gray-400">Manage and organize your collection files</p>
                            </div>
                            <button
                                v-if="canEdit"
                                @click="showAddFileModal = true"
                                class="inline-flex items-center rounded-lg border border-green-600 bg-gradient-to-r from-green-600 to-green-700 px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-green-700 hover:to-green-800 hover:shadow-lg hover:shadow-green-500/25 focus:ring-2 focus:ring-green-500 focus:outline-none"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add File
                            </button>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Files Grid -->
                        <div v-if="collection.files.length > 0" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div
                                v-for="(file, index) in collection.files"
                                :key="file.id"
                                class="group relative overflow-hidden rounded-xl border border-gray-600 bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-sm transition-all hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10"
                            >
                                <!-- File Number Badge -->
                                <div class="absolute top-3 left-3 z-10">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600/80 text-sm font-bold text-white backdrop-blur-sm"
                                    >
                                        {{ index + 1 }}
                                    </span>
                                </div>

                                <!-- File Content -->
                                <div class="p-6 pt-14">
                                    <div class="mb-4">
                                        <h3 class="text-lg font-semibold text-white transition-colors group-hover:text-blue-300">
                                            {{ file.title || file.name }}
                                        </h3>
                                        <p v-if="file.description" class="mt-2 line-clamp-2 text-sm text-gray-400">
                                            {{ file.description }}
                                        </p>
                                    </div>

                                    <!-- File Stats -->
                                    <div class="mb-4 flex flex-wrap gap-3">
                                        <div class="flex items-center gap-1.5 rounded-lg border border-blue-700/50 bg-blue-900/30 px-2.5 py-1">
                                            <svg class="h-3.5 w-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            <span class="text-xs font-medium text-blue-300">{{ file.quizzes?.length || 0 }} questions</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 rounded-lg border border-purple-700/50 bg-purple-900/30 px-2.5 py-1">
                                            <svg class="h-3.5 w-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                />
                                            </svg>
                                            <span class="text-xs font-medium text-purple-300"
                                                >{{ file.user.first_name }} {{ file.user.last_name }}</span
                                            >
                                        </div>
                                    </div>

                                    <!-- File Actions -->
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            :href="route('files.show', file.id)"
                                            class="inline-flex flex-1 items-center justify-center rounded-lg border border-green-600 bg-gradient-to-r from-green-600/80 to-green-700/80 px-3 py-2 text-xs font-medium text-white transition-all hover:from-green-600 hover:to-green-700 hover:shadow-md focus:ring-2 focus:ring-green-500 focus:outline-none"
                                        >
                                            <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            View File
                                        </Link>
                                        <button
                                            v-if="canEdit"
                                            @click="removeFile(file)"
                                            class="inline-flex items-center justify-center rounded-lg border border-red-600/50 bg-red-900/20 px-3 py-2 text-xs font-medium text-red-300 transition-all hover:bg-red-900/40 hover:text-red-200 focus:ring-2 focus:ring-red-500 focus:outline-none"
                                        >
                                            <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <!-- Hover Effect Gradient -->
                                <div
                                    class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-500/5 opacity-0 transition-opacity group-hover:opacity-100"
                                ></div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-16 text-center">
                            <div class="mx-auto max-w-md">
                                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full border border-gray-600 bg-gray-800">
                                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <h3 class="mb-2 text-lg font-semibold text-white">No files in collection</h3>
                                <p class="mb-6 text-gray-400">This collection doesn't contain any files yet. Add your first file to get started.</p>
                                <button
                                    v-if="canEdit"
                                    @click="showAddFileModal = true"
                                    class="inline-flex items-center rounded-lg border border-indigo-600 bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-3 text-sm font-medium text-white transition-all hover:from-indigo-700 hover:to-indigo-800 hover:shadow-lg hover:shadow-indigo-500/25 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                >
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add your first file
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

const deleteCollection = () => {
    if (confirm(`Are you sure you want to delete "${collection.name}"? This action cannot be undone.`)) {
        router.delete(route('collections.destroy', collection.id));
    }
};
</script>

<style scoped>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
}


/* Animation for hover effects */
@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.group:hover .animate-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    background-size: 200% 100%;
    animation: shimmer 1.5s ease-in-out;
}

/* Enhanced focus states */
.focus-ring {
    outline: none;
}

.focus-ring:focus {
    outline: 2px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
}

/* Custom scrollbar for better aesthetics */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.4);
}
</style>
