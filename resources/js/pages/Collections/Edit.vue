<template>
    <Head :title="`Edit ${collection.name}`" />

    <AppLayout>
        <div class="bg-gradient min-h-screen py-12">
            <!-- Hero Section -->
            <div class="mx-auto mb-8 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="welcome-banner">
                        <h1 class="pixel-outline my-2 text-4xl font-bold tracking-wide text-[#FFF8DC]">Edit Collection</h1>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Collection Header Info -->
                <div class="bg-container mb-8 rounded-2xl border-2 border-[#ffd700] p-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-[#ffd700] bg-[#b71400]">
                            <svg class="pixel-outline-icon h-6 w-6 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                />
                            </svg>
                        </div>
                        <div>
                            <h2 class="pixel-outline text-xl font-bold text-[#FFF8DC]">{{ collection.name }}</h2>
                            <p class="pixel-outline text-[#FFF8DC]/70">Editing collection settings and files</p>
                        </div>
                    </div>
                </div>

                <!-- Main Edit Form -->
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
                                <label for="description" class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-[#FFF8DC]">
                                    <svg class="pixel-outline-icon h-5 w-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <span>Description</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full resize-none rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-4 py-3 text-[#FFF8DC] placeholder-[#FFF8DC]/50 transition-all duration-200 focus:border-[#ffd700] focus:ring-2 focus:ring-[#ffd700]"
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

                            <!-- Privacy Setting -->
                            <!-- Visibility -->
                            <div class="space-y-3">
                                <label class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-[#FFF8DC]">
                                    <svg class="pixel-outline-icon h-5 w-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        @click="form.is_public = false"
                                        :class="
                                            !form.is_public
                                                ? 'border-[#b71400] bg-[#b71400]/20 ring-2 ring-[#ffd700]/50'
                                                : 'border-[#ffd700] bg-[#0c0a03] hover:border-[#b71400]'
                                        "
                                        class="rounded-xl border-2 p-6 text-left transition-all duration-300 hover:scale-105"
                                    >
                                        <div class="flex items-start space-x-4">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border-2 border-[#ffd700] bg-[#b71400]"
                                            >
                                                <svg
                                                    class="pixel-outline-icon h-5 w-5 text-[#ffd700]"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="pixel-outline mb-1 text-lg font-semibold text-[#FFF8DC]">Private</h3>
                                                <p class="pixel-outline text-sm leading-relaxed text-[#FFF8DC]/70">
                                                    Only you can see and edit this collection
                                                </p>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="form.is_public = true"
                                        :class="
                                            form.is_public
                                                ? 'border-green-500 bg-green-600/20 ring-2 ring-[#ffd700]/50'
                                                : 'border-[#ffd700] bg-[#0c0a03] hover:border-green-400'
                                        "
                                        class="rounded-xl border-2 p-6 text-left transition-all duration-300 hover:scale-105"
                                    >
                                        <div class="flex items-start space-x-4">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border-2 border-[#ffd700] bg-green-600"
                                            >
                                                <svg
                                                    class="pixel-outline-icon h-5 w-5 text-[#ffd700]"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="pixel-outline mb-1 text-lg font-semibold text-[#FFF8DC]">Public</h3>
                                                <p class="pixel-outline text-sm leading-relaxed text-[#FFF8DC]/70">
                                                    Others can discover, view and copy this collection
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="space-y-3">
                                <label for="tags" class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-[#FFF8DC]">
                                    <svg class="pixel-outline-icon h-5 w-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        class="pixel-outline inline-flex items-center rounded-full border border-[#ffd700] bg-[#b71400] px-3 py-1 text-sm font-medium text-[#FFF8DC] transition-all duration-200 hover:scale-105"
                                    >
                                        {{ tag }}
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="ml-2 text-[#ffd700] transition-colors duration-200 hover:text-[#FFF8DC]"
                                            title="Remove tag"
                                        >
                                            <svg class="pixel-outline-icon h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                    <span v-if="form.tags.length === 0" class="pixel-outline text-sm text-[#FFF8DC]/50 italic">
                                        No tags added yet
                                    </span>
                                </div>
                                <div class="flex overflow-hidden rounded-lg border-2 border-[#ffd700] bg-[#0c0a03]">
                                    <input
                                        v-model="newTag"
                                        type="text"
                                        class="flex-1 bg-transparent px-4 py-3 text-[#FFF8DC] placeholder-[#FFF8DC]/50 focus:outline-none"
                                        placeholder="Add a tag..."
                                        @keyup.enter="addTag"
                                    />
                                    <button
                                        type="button"
                                        @click="addTag"
                                        class="bg-[#b71400] px-6 py-3 font-medium text-[#FFF8DC] transition-all duration-200 hover:bg-[#990f00] focus:ring-2 focus:ring-[#ffd700] focus:outline-none focus:ring-inset"
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

                            <!-- File Management -->
                            <div class="space-y-6">
                                <div class="text-center">
                                    <h3 class="pixel-outline mb-2 flex items-center justify-center space-x-2 text-2xl font-bold text-[#FFF8DC]">
                                        <svg class="pixel-outline-icon h-6 w-6 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>
                                        <span>Manage Files</span>
                                    </h3>
                                    <p class="pixel-outline text-[#FFF8DC]/70">Select and reorder files in your collection</p>
                                    <div class="mx-auto mt-2 h-1 w-16 rounded-full bg-[#ffd700]"></div>
                                </div>

                                <!-- Current Files -->
                                <div class="bg-container rounded-xl border-2 border-[#ffd700] p-6">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-[#FFF8DC]">
                                            <svg
                                                class="pixel-outline-icon h-5 w-5 text-[#ffd700]"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                            <span>Files in Collection</span>
                                        </h4>
                                        <span
                                            class="rounded-full border border-[#ffd700] bg-[#b71400]/30 px-3 py-1 text-sm font-medium text-[#FFF8DC]"
                                        >
                                            {{ form.file_ids.length }} files
                                        </span>
                                    </div>
                                    <div class="custom-scrollbar max-h-64 space-y-2 overflow-y-auto">
                                        <div v-if="form.file_ids.length === 0" class="py-8 text-center text-gray-400">
                                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                                />
                                            </svg>
                                            <p class="pixel-outline">No files in collection yet</p>
                                            <p class="pixel-outline text-sm">Add files from the available list below</p>
                                        </div>
                                        <div
                                            v-for="(fileId, index) in form.file_ids"
                                            :key="fileId"
                                            class="group flex items-center justify-between rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] p-4 transition-all duration-200 hover:border-[#b71400]"
                                        >
                                            <div class="flex items-center space-x-3">
                                                <span
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#ffd700] bg-[#b71400] text-sm font-bold text-[#FFF8DC]"
                                                >
                                                    {{ index + 1 }}
                                                </span>
                                                <div>
                                                    <span class="pixel-outline block font-medium text-[#FFF8DC]">
                                                        {{ getFileName(fileId) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-1">
                                                <button
                                                    v-if="index > 0"
                                                    type="button"
                                                    @click="moveFileUp(index)"
                                                    class="rounded-lg p-2 text-[#FFF8DC]/70 transition-all duration-200 hover:bg-[#b71400]/30 hover:text-[#ffd700]"
                                                    title="Move up"
                                                >
                                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    v-if="index < form.file_ids.length - 1"
                                                    type="button"
                                                    @click="moveFileDown(index)"
                                                    class="rounded-lg p-2 text-[#FFF8DC]/70 transition-all duration-200 hover:bg-[#b71400]/30 hover:text-[#ffd700]"
                                                    title="Move down"
                                                >
                                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="removeFileFromCollection(index)"
                                                    class="rounded-lg p-2 text-[#FFF8DC]/70 transition-all duration-200 hover:bg-red-900/30 hover:text-red-400"
                                                    title="Remove from collection"
                                                >
                                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Available Files -->
                                <div class="bg-container rounded-xl border-2 border-[#ffd700] p-6">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h4 class="pixel-outline flex items-center space-x-2 text-lg font-semibold text-[#FFF8DC]">
                                            <svg
                                                class="pixel-outline-icon h-5 w-5 text-[#ffd700]"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                />
                                            </svg>
                                            <span>Available Files</span>
                                        </h4>
                                        <span
                                            class="rounded-full border border-[#ffd700] bg-green-600/30 px-3 py-1 text-sm font-medium text-[#FFF8DC]"
                                        >
                                            {{ availableFiles.length }} available
                                        </span>
                                    </div>
                                    <div class="custom-scrollbar max-h-64 space-y-2 overflow-y-auto">
                                        <div v-if="availableFiles.length === 0" class="py-8 text-center text-gray-400">
                                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                            <p class="pixel-outline">All files are already in the collection</p>
                                        </div>
                                        <label
                                            v-for="file in availableFiles"
                                            :key="file.id"
                                            class="group flex cursor-pointer items-center space-x-4 rounded-lg border border-[#ffd700] bg-[#0c0a03] p-4 transition-all duration-200 hover:border-[#b71400] hover:bg-[#b71400]/10"
                                        >
                                            <div class="relative">
                                                <input
                                                    type="checkbox"
                                                    :value="file.id"
                                                    @change="toggleFile(file.id)"
                                                    :checked="form.file_ids.includes(file.id)"
                                                    class="h-5 w-5 rounded border-2 border-[#ffd700] bg-transparent text-green-500 transition-all duration-200 focus:ring-2 focus:ring-[#ffd700] focus:ring-offset-0"
                                                />
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="pixel-outline font-medium text-[#FFF8DC] transition-colors duration-200 group-hover:text-[#ffd700]"
                                                    >
                                                        {{ file.title || file.name }}
                                                    </span>
                                                    <span
                                                        class="rounded-full border border-[#ffd700] bg-green-600/20 px-2 py-1 text-xs font-medium text-[#FFF8DC]"
                                                    >
                                                        {{ file.quizzes_count || 0 }} questions
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div v-if="form.errors.file_ids" class="pixel-outline flex items-center space-x-1 text-sm text-red-400">
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

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between border-t-2 border-[#ffd700] pt-6">
                                <Link
                                    :href="route('collections.show', collection.id)"
                                    class="pixel-outline inline-flex items-center space-x-2 rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-6 py-3 font-medium text-[#FFF8DC] transition-all duration-200 hover:scale-105 hover:bg-[#28231d]"
                                >
                                    <svg class="pixel-outline-icon h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    <span>Back to Details</span>
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="pixel-outline inline-flex items-center space-x-2 rounded-lg border-2 border-[#0c0a03] bg-[#b71400] px-8 py-3 font-semibold text-white transition-all duration-200 hover:scale-105 hover:bg-[#990f00] focus:ring-2 focus:ring-[#ffd700] focus:ring-offset-2 focus:ring-offset-[#28231d] focus:outline-none disabled:transform-none disabled:cursor-not-allowed disabled:opacity-50"
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ form.processing ? 'Updating...' : 'Update Collection' }}</span>
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
    background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
    border-radius: 8px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563eb, #7c3aed);
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
