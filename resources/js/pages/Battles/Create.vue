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
                                
                                <!-- Question Count Summary (if validation is loaded) -->
                                <div v-if="questionValidation" class="mb-4 rounded-lg bg-black/30 p-3">
                                    <div class="grid grid-cols-3 gap-4 text-sm">
                                        <div class="text-center">
                                            <div class="text-green-400 font-medium pixel-outline">Easy (T/F)</div>
                                            <div class="text-gray-300 pixel-outline">{{ questionValidation.counts.easy }} questions</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-yellow-400 font-medium pixel-outline">Medium (MC)</div>
                                            <div class="text-gray-300 pixel-outline">{{ questionValidation.counts.medium }} questions</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-red-400 font-medium pixel-outline">Hard (Enum)</div>
                                            <div class="text-gray-300 pixel-outline">{{ questionValidation.counts.hard }} questions</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-xs text-center text-gray-400 pixel-outline">
                                        Total: {{ questionValidation.counts.total }} questions
                                    </div>
                                </div>

                                <!-- Loading indicator -->
                                <div v-else-if="isLoadingValidation" class="mb-4 rounded-lg bg-black/30 p-3 text-center">
                                    <div class="text-gray-400 pixel-outline">Loading question analysis...</div>
                                </div>

                                <!-- Warning for no questions -->
                                <div v-if="questionValidation && !questionValidation.has_questions" class="mb-4 rounded-lg border border-red-500 bg-red-900/20 p-3">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <p class="text-red-300 font-medium pixel-outline">No questions available!</p>
                                    </div>
                                    <p class="mt-1 text-sm text-red-200 pixel-outline">
                                        This source has no quiz questions. Please generate quizzes first or choose a different source.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'easy'"
                                        :disabled="!!(questionValidation && questionValidation.counts.easy === 0)"
                                        :class="[
                                            selectedDifficulty === 'easy'
                                                ? 'border-green-500 bg-black/50 ring-2 ring-green-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-green-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500',
                                            questionValidation && questionValidation.counts.easy === 0 
                                                ? 'opacity-50 cursor-not-allowed' 
                                                : ''
                                        ]"
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
                                        <p class="text-sm text-gray-400 pixel-outline">True/False (10 EXP)</p>
                                        <div v-if="questionValidation" class="mt-1 text-xs">
                                            <span v-if="questionValidation.counts.easy > 0" class="text-green-400 pixel-outline">
                                                {{ questionValidation.counts.easy }} available
                                            </span>
                                            <span v-else class="text-red-400 pixel-outline">
                                                No True/False questions
                                            </span>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'medium'"
                                        :disabled="!!(questionValidation && questionValidation.counts.medium === 0)"
                                        :class="[
                                            selectedDifficulty === 'medium'
                                                ? 'border-yellow-500 bg-black/50 ring-2 ring-yellow-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-yellow-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500',
                                            questionValidation && questionValidation.counts.medium === 0 
                                                ? 'opacity-50 cursor-not-allowed' 
                                                : ''
                                        ]"
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
                                        <p class="text-sm text-gray-400 pixel-outline">Multiple Choice (20 EXP)</p>
                                        <div v-if="questionValidation" class="mt-1 text-xs">
                                            <span v-if="questionValidation.counts.medium > 0" class="text-yellow-400 pixel-outline">
                                                {{ questionValidation.counts.medium }} available
                                            </span>
                                            <span v-else class="text-red-400 pixel-outline">
                                                No Multiple Choice questions
                                            </span>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'hard'"
                                        :disabled="!!(questionValidation && questionValidation.counts.hard === 0)"
                                        :class="[
                                            selectedDifficulty === 'hard'
                                                ? 'border-red-500 bg-black/50 ring-2 ring-red-500 hover:scale-105 transition-transform duration-500'
                                                : 'border-gray-300 bg-black/30 hover:border-red-300 dark:border-gray-600 hover:scale-105 transition-transform duration-500',
                                            questionValidation && questionValidation.counts.hard === 0 
                                                ? 'opacity-50 cursor-not-allowed' 
                                                : ''
                                        ]"
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
                                        <p class="text-sm text-gray-500 pixel-outline">Enumeration (30 EXP)</p>
                                        <div v-if="questionValidation" class="mt-1 text-xs">
                                            <span v-if="questionValidation.counts.hard > 0" class="text-red-400 pixel-outline">
                                                {{ questionValidation.counts.hard }} available
                                            </span>
                                            <span v-else class="text-red-400 pixel-outline">
                                                No Enumeration questions
                                            </span>
                                        </div>
                                    </button>
                                </div>

                                <!-- Difficulty-specific warnings -->
                                <div v-if="currentDifficultyWarning" class="mt-4 rounded-lg border border-yellow-500 bg-yellow-900/20 p-3">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <p class="text-yellow-300 font-medium pixel-outline">{{ currentDifficultyWarning }}</p>
                                    </div>
                                    <p v-if="currentDifficultyCount > 0 && currentDifficultyCount < 5" class="mt-1 text-sm text-yellow-200 pixel-outline">
                                        EXP rewards will be reduced by {{ Math.round((1 - Math.max(0.6, currentDifficultyCount / 5)) * 100) }}% due to fewer questions.
                                    </p>
                                </div>
                            </div>

                            <!-- Battle Info -->
                            <div v-if="questionValidation && currentDifficultyCount > 0" class="mb-6">
                                <div class="rounded-lg bg-black/30 p-4">
                                    <div class="text-center">
                                        <h4 class="text-lg font-medium text-gray-300 pixel-outline mb-2">Battle Challenge</h4>
                                        <p class="text-sm text-gray-400 pixel-outline mb-3">
                                            Face {{ currentDifficultyCount }} random monsters in sequence. Each question spawns a new monster!
                                        </p>
                                        <div class="text-sm text-gray-400 pixel-outline">
                                            <div>{{ getExpReward(selectedDifficulty, currentDifficultyCount) }} EXP per correct {{ selectedDifficulty }} question</div>
                                            <span v-if="currentDifficultyCount < 5" class="text-yellow-400">
                                                ({{ Math.round((Math.max(0.6, currentDifficultyCount / 5)) * 100) }}% of normal due to short battle)
                                            </span>
                                            <div class="mt-1">+ 50 EXP victory bonus</div>
                                        </div>
                                        <div class="mt-3 p-2 bg-red-900/20 rounded border border-red-500">
                                            <p class="text-red-300 text-sm pixel-outline">⚠️ You have 3 hearts - 3 wrong answers = defeat!</p>
                                        </div>
                                    </div>
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

                                <div class="flex flex-col items-end">
                                    <!-- Validation error messages -->
                                    <div v-if="!canSubmit" class="mb-2 text-right">
                                        <p v-if="questionValidation && !questionValidation.has_questions" class="text-sm text-red-400 pixel-outline">
                                            No questions available
                                        </p>
                                        <p v-else-if="questionValidation && questionValidation.counts[selectedDifficulty] === 0" class="text-sm text-red-400 pixel-outline">
                                            No {{ selectedDifficulty === 'easy' ? 'True/False' : selectedDifficulty === 'medium' ? 'Multiple Choice' : 'Enumeration' }} questions available for {{ selectedDifficulty }} difficulty
                                        </p>

                                        <p v-else-if="!(form.source_type === 'file' ? form.file_id : form.collection_id)" class="text-sm text-yellow-400 pixel-outline">
                                            Please select a source
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="form.processing || !canSubmit"
                                        :class="[
                                            'inline-flex items-center pixel-outline rounded-md border border-transparent px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out focus:ring-2 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800',
                                            canSubmit && !form.processing
                                                ? 'bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-indigo-500 active:bg-indigo-900'
                                                : 'bg-gray-500 cursor-not-allowed opacity-50'
                                        ]"
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
                                        {{ canSubmit ? 'Start Battle' : 'Cannot Start Battle' }}
                                    </button>
                                </div>
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
import axios from 'axios';

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

interface QuestionCounts {
    easy: number;
    medium: number;
    hard: number;
    total: number;
    [key: string]: number;
}

interface QuestionValidation {
    counts: QuestionCounts;
    warnings: Record<string, string>;
    has_questions: boolean;
}

const props = defineProps<{
    files: File[];
    collections: Collection[];
}>();

const selectedDifficulty = ref('easy');
const questionValidation = ref<QuestionValidation | null>(null);
const isLoadingValidation = ref(false);

const form = useForm({
    source_type: 'file',
    file_id: '',
    collection_id: '',
    difficulty: 'easy',
});

// Monsters are now randomly selected for each question

const selectedSource = computed(() => {
    if (form.source_type === 'file' && form.file_id) {
        return props.files.find(f => f.id.toString() === form.file_id);
    } else if (form.source_type === 'collection' && form.collection_id) {
        return props.collections.find(c => c.id.toString() === form.collection_id);
    }
    return null;
});

const canSubmit = computed(() => {
    const hasSource = form.source_type === 'file' ? form.file_id : form.collection_id;
    const hasQuestions = questionValidation.value?.has_questions !== false;
    const hasQuestionsForDifficulty = (questionValidation.value?.counts?.[selectedDifficulty.value] || 0) > 0;
    
    return hasSource && hasQuestions && hasQuestionsForDifficulty;
});

const currentDifficultyWarning = computed(() => {
    if (!questionValidation.value?.warnings) return null;
    return questionValidation.value.warnings[selectedDifficulty.value] || null;
});

const currentDifficultyCount = computed(() => {
    if (!questionValidation.value?.counts) return 0;
    return questionValidation.value.counts[selectedDifficulty.value] || 0;
});

// Function to fetch question counts for validation
const fetchQuestionCounts = async () => {
    const sourceId = form.source_type === 'file' ? form.file_id : form.collection_id;
    
    console.log('fetchQuestionCounts called:', { sourceType: form.source_type, sourceId });
    
    if (!sourceId) {
        console.log('No source ID, clearing validation');
        questionValidation.value = null;
        return;
    }

    isLoadingValidation.value = true;
    
    try {
        console.log('Making API request with params:', {
            source_type: form.source_type,
            source_id: sourceId
        });
        
        const response = await axios.get('/api/question-counts', {
            params: {
                source_type: form.source_type,
                source_id: sourceId
            }
        });
        
        console.log('API response received:', response.data);
        questionValidation.value = response.data;
    } catch (error: any) {
        console.error('Failed to fetch question counts:', error);
        console.error('Error details:', error.response?.data);
        questionValidation.value = null;
    } finally {
        isLoadingValidation.value = false;
    }
};

// Utility functions
const getExpReward = (difficulty: string, questionCount: number) => {
    const baseRewards: { [key: string]: number } = { easy: 10, medium: 20, hard: 30 };
    const baseExp = baseRewards[difficulty] || 20;
    
    if (questionCount < 5) {
        const scalingFactor = Math.max(0.6, questionCount / 5);
        return Math.round(baseExp * scalingFactor);
    }
    
    return baseExp;
};

// Removed getDifficultyBadgeClass since we no longer show monsters

// No need to reset monster selection since we removed it

// Reset file/collection when source type changes
watch(
    () => form.source_type,
    () => {
        form.file_id = '';
        form.collection_id = '';
        questionValidation.value = null;
    },
);

// Watch for changes in file/collection selection to fetch question counts
watch(
    () => form.source_type === 'file' ? form.file_id : form.collection_id,
    () => {
        fetchQuestionCounts();
    }
);

// Watch for changes in selectedDifficulty to update form
watch(
    () => selectedDifficulty.value,
    (newDifficulty) => {
        form.difficulty = newDifficulty;
    }
);

const submit = () => {
    console.log('Form submission started');

    // Final validation check
    if (!questionValidation.value?.has_questions) {
        alert('Cannot start battle: No questions available for this source.');
        return;
    }

    if (questionValidation.value.counts[selectedDifficulty.value] === 0) {
        alert(`Cannot start battle: No ${selectedDifficulty.value} questions available for this source.`);
        return;
    }

    // Prepare form data based on source type
    const formData: any = {
        source_type: form.source_type,
        difficulty: selectedDifficulty.value,
        monster_id: 1, // Default monster ID since we'll use random monsters
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
