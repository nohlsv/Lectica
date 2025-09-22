<template>
    <Head title="Create Multiplayer Game" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Create Multiplayer Battle</h2>
        </template>

        <div class="py-12 min-h-screen bg-gradient">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- General Error Display -->
                        <div
                            v-if="form.errors.general || Object.keys(form.errors).length > 0"
                            class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">There were errors with your submission</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <ul class="list-inside list-disc space-y-1">
                                            <li v-if="form.errors.general">{{ form.errors.general }}</li>
                                            <li v-for="(error, field) in form.errors" :key="field" v-if="field !== 'general'">
                                                <strong>{{ field }}:</strong> {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- PvP Win Condition Selection -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Battle Mode</label>
                                <div class="flex space-x-4">
                                    <button
                                        type="button"
                                        @click="form.pvp_mode = 'accuracy'"
                                        :class="form.pvp_mode === 'accuracy' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                                        class="rounded-md px-4 py-2 text-sm font-medium"
                                    >
                                        Most Accurate Wins
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.pvp_mode = 'hp'"
                                        :class="form.pvp_mode === 'hp' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                                        class="rounded-md px-4 py-2 text-sm font-medium"
                                    >
                                        Most HP Wins
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Choose how the winner is determined in PvP battles.</p>
                            </div>

                            <!-- Source Type Selection -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Choose Battle Source </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        @click="form.source_type = 'file'"
                                        :class="
                                            form.source_type === 'file'
                                                ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500 dark:bg-blue-900/20'
                                                : 'border-gray-300 hover:border-blue-300 dark:border-gray-600'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
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
                                        @click="form.source_type = 'collection'"
                                        :class="
                                            form.source_type === 'collection'
                                                ? 'border-purple-500 bg-purple-50 ring-2 ring-purple-500 dark:bg-purple-900/20'
                                                : 'border-gray-300 hover:border-purple-300 dark:border-gray-600'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
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
                            <div v-if="form.source_type === 'file'" class="mb-6">
                                <label for="file_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Select Your Study File
                                </label>
                                <select
                                    id="file_id"
                                    v-model="form.file_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                                    :required="form.source_type === 'file'"
                                >
                                    <option value="">Choose a file...</option>
                                    <option v-for="file in files" :key="file.id" :value="file.id">
                                        {{ file.title || file.name }} ({{ file.quizzes_count || 0 }} questions)
                                    </option>
                                </select>
                                <div v-if="form.errors.file_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.file_id }}
                                </div>
                            </div>

                            <!-- Collection Selection -->
                            <div v-if="form.source_type === 'collection'" class="mb-6">
                                <label for="collection_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Select Your Collection
                                </label>
                                <div
                                    v-if="collections.length === 0"
                                    class="rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
                                >
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        You don't have any collections with files yet.
                                        <Link :href="route('collections.create')" class="font-medium underline">Create a collection</Link> first.
                                    </p>
                                </div>
                                <select
                                    v-else
                                    id="collection_id"
                                    v-model="form.collection_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                                    :required="form.source_type === 'collection'"
                                >
                                    <option value="">Choose a collection...</option>
                                    <option v-for="collection in collections" :key="collection.id" :value="collection.id">
                                        {{ collection.name }} ({{ collection.file_count }} files, {{ collection.total_questions }} questions)
                                    </option>
                                </select>
                                <div v-if="form.errors.collection_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.collection_id }}
                                </div>
                            </div>

                            <!-- Game Info -->
                            <!-- PvP Game Info -->
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                                <h3 class="mb-2 text-lg font-medium text-red-900 dark:text-red-100">
                                    How PvP (Versus) Battles Work
                                </h3>
                                <ul class="space-y-1 text-sm text-red-700 dark:text-red-300">
                                    <li>• You and another player take turns answering questions</li>
                                    <li>• Correct answers deal damage to your opponent</li>
                                    <li>• Wrong answers cause damage to yourself</li>
                                    <li>• Be the last player standing to win!</li>
                                </ul>
                            </div>                            <!-- Submit Button -->
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('multiplayer-games.index')"
                                    class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                                >
                                    ← Back to Games
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing || !canSubmit"
                                    class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-purple-700 focus:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none active:bg-purple-900 disabled:opacity-50 dark:focus:ring-offset-gray-800"
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
                                    Create Game & Wait for Player
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
import { computed, watch } from 'vue';

interface Monster {
    id: string;
    name: string;
    hp: number;
    attack: number;
    image: string;
}

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

const props = defineProps<{
    monsters: Monster[];
    files: File[];
    collections: Collection[];
}>();

const form = useForm({
    source_type: 'file',
    file_id: '',
    collection_id: '',
    game_mode: 'pvp', // Always PvP now
    pvp_mode: 'accuracy', // Default PvP mode
});

const canSubmit = computed(() => {
    const hasSource = form.source_type === 'file' ? form.file_id : form.collection_id;
    const hasPvpMode = !!form.pvp_mode;
    return hasSource && hasPvpMode;
});

// Reset file/collection when source type changes
watch(
    () => form.source_type,
    () => {
        form.file_id = '';
        form.collection_id = '';
    },
);

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/default-monster.png';
};

const submit = () => {
    form.post(route('multiplayer-games.store'));
};
</script>
