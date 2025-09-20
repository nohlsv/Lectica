<template>
    <Head title="Start New Battle" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Start New Battle</h2>
        </template>

        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-container shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Source Type Selection -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-300 pixel-outline"> Choose Battle Source </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <button
                                        type="button"
                                        @click="form.source_type = 'file'"
                                        :class="
                                            form.source_type === 'file'
                                                ? 'border-blue-500 bg-black/50 ring-2 ring-blue-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-blue-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg class="mr-3 h-5 w-5 text-blue-500 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                ></path>
                                            </svg>
                                            <div>
                                                <h3 class="font-medium pixel-outline">Single File</h3>
                                                <p class="text-sm text-gray-400 pixel-outline">Battle with one specific file</p>
                                            </div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="form.source_type = 'collection'"
                                        :class="
                                            form.source_type === 'collection'
                                                ? 'border-purple-500 bg-black/50 ring-2 ring-purple-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-purple-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg class="mr-3 h-5 w-5 text-purple-500 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                ></path>
                                            </svg>
                                            <div>
                                                <h3 class="font-medium pixel-outline">Collection</h3>
                                                <p class="text-sm text-gray-500 pixel-outline">Battle with multiple files</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- File Selection (when source_type is 'file') -->
                            <div v-if="form.source_type === 'file'" class="mb-6">
                                <label for="file_id" class="mb-2 block text-sm font-medium text-gray-300 pixel-outline">
                                    Select Your Study File
                                </label>
                                <select
                                    id="file_id"
                                    v-model="form.file_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm bg-black/50 pixel-outline"
                                    :required="form.source_type === 'file'"
                                >
                                    <option value="">Choose a file...</option>
                                    <option v-for="file in files" :key="file.id" :value="file.id" class="bg-black text-white">
                                        {{ file.title || file.name }} ({{ file.quizzes_count || 0 }} questions)
                                    </option>
                                </select>
                                <div v-if="form.errors.file_id" class="mt-2 text-sm text-red-600 pixel-outline">
                                    {{ form.errors.file_id }}
                                </div>
                            </div>

                            <!-- Collection Selection (when source_type is 'collection') -->
                            <div v-if="form.source_type === 'collection'" class="mb-6">
                                <label for="collection_id" class="mb-2 block text-sm font-medium text-gray-300 pixel-outline">
                                    Select Your Collection
                                </label>
                                <div
                                    v-if="collections.length === 0"
                                    class="rounded-md border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20"
                                >
                                    <p class="text-sm text-yellow-300 pixel-outline">
                                        You don't have any collections with files yet.
                                        <Link :href="route('collections.create')" class="font-medium underline">Create a collection</Link> first.
                                    </p>
                                </div>
                                <select
                                    v-else
                                    id="collection_id"
                                    v-model="form.collection_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm bg-black/50 pixel-outline"
                                    :required="form.source_type === 'collection'"
                                >
                                    <option value="">Choose a collection...</option>
                                    <option v-for="collection in collections" :key="collection.id" :value="collection.id" class="bg-black text-white">
                                        {{ collection.name }} ({{ collection.file_count }} files, {{ collection.total_questions }} questions)
                                    </option>
                                </select>
                                <div v-if="form.errors.collection_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.collection_id }}
                                </div>
                            </div>

                            <!-- Difficulty Selection -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-300 pixel-outline"> Choose Difficulty </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'easy'"
                                        :class="
                                            selectedDifficulty === 'easy'
                                                ? 'border-green-500 bg-black/50 ring-2 ring-green-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-green-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500'
                                        "
                                        class="rounded-lg border p-4 text-center transition-colors"
                                    >
                                        <div class="mb-2 text-green-500">
                                            <svg class="mx-auto h-8 w-8 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium pixel-outline">Easy</h3>
                                        <p class="text-sm text-gray-400 pixel-outline">Lower HP monsters</p>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'medium'"
                                        :class="
                                            selectedDifficulty === 'medium'
                                                    ? 'border-yellow-500 bg-black/50 ring-2 ring-yellow-500 hover:scale-105 transition-transform duration-500'
                                                    : 'border-gray-300 bg-black/30 hover:border-yellow-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500'
                                        "
                                        class="rounded-lg border p-4 text-center transition-colors"
                                    >
                                        <div class="mb-2 text-yellow-500">
                                            <svg class="mx-auto h-8 w-8 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"
                                                ></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium pixel-outline">Medium</h3>
                                        <p class="text-sm text-gray-400 pixel-outline">Balanced challenge</p>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'hard'"
                                        :class="
                                            selectedDifficulty === 'hard'
                                                ? 'border-red-500 bg-black/50 ring-2 ring-red-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-red-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500'
                                        "
                                        class="rounded-lg border p-4 text-center transition-colors"
                                    >
                                        <div class="mb-2 text-red-500">
                                            <svg class="mx-auto h-8 w-8 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                                ></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium pixel-outline">Hard</h3>
                                        <p class="text-sm text-gray-500 pixel-outline">High HP monsters</p>
                                    </button>
                                </div>
                            </div>

                            <!-- Monster Selection -->
                            <div v-if="availableMonsters.length > 0" class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-300 pixel-outline"> Choose Your Opponent </label>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <button
                                        type="button"
                                        v-for="monster in availableMonsters"
                                        :key="monster.id"
                                        @click="form.monster_id = monster.id"
                                        :class="
                                            form.monster_id === monster.id
                                                ? 'border-indigo-500 bg-black/50 ring-2 ring-indigo-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-indigo-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500'
                                        "
                                        class="rounded-lg border p-4 text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <img
                                                v-if="monster.image_path"
                                                :src="monster.image_path"
                                                :alt="monster.name"
                                                class="mr-3 h-12 w-12 rounded-full pixel-outline-icon"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                            <div>
                                                <h3 class="font-medium pixel-outline">{{ monster.name }}</h3>
                                                <p class="text-sm text-gray-500 pixel-outline">HP: {{ monster.hp }}</p>
                                                <p class="text-sm text-gray-500 pixel-outline">Attack: {{ monster.attack }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                <div v-if="form.errors.monster_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.monster_id }}
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('battles.index')"
                                    class="text-red-500 hover:text-red-700 pixel-outline"
                                >
                                    ← Back to Battles
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing || !canSubmit"
                                    class="inline-flex items-center pixel-outline rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 disabled:opacity-50 dark:focus:ring-offset-gray-800"
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
                                    Start Battle
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
import { computed, ref, watch } from 'vue';

interface Monster {
    id: number;
    name: string;
    hp: number;
    attack: number;
    image_path: string;
    difficulty: string;
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

const selectedDifficulty = ref('easy');

const form = useForm({
    source_type: 'file',
    file_id: '',
    collection_id: '',
    monster_id: '',
});

const availableMonsters = computed(() => {
    return props.monsters.filter((monster) => monster.difficulty === selectedDifficulty.value);
});

const canSubmit = computed(() => {
    const hasSource = form.source_type === 'file' ? form.file_id : form.collection_id;
    return hasSource && form.monster_id && selectedDifficulty.value;
});

// Reset monster selection when difficulty changes
watch(selectedDifficulty, () => {
    form.monster_id = '';
});

// Reset file/collection when source type changes
watch(
    () => form.source_type,
    () => {
        form.file_id = '';
        form.collection_id = '';
    },
);

const submit = () => {
    console.log('Form submission started');

    // Prepare form data based on source type
    const formData = {
        source_type: form.source_type,
        monster_id: form.monster_id,
    };

    // Only include the relevant ID field based on source type
    if (form.source_type === 'file') {
        formData.file_id = form.file_id;
    } else {
        formData.collection_id = form.collection_id;
    }

    console.log('Form data:', formData);

    form.transform((data) => formData).post(route('battles.store'), {
        onStart: () => console.log('Form processing started'),
        onSuccess: (response) => {
            console.log('Form submission successful:', response);
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        },
        onFinish: () => {
            console.log('Form processing finished');
        },
    });
};
</script>
