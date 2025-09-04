<template>
    <Head title="Start New Battle" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Start New Battle
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <!-- Source Type Selection -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Choose Battle Source
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <button
                                        type="button"
                                        @click="form.source_type = 'file'"
                                        :class="form.source_type === 'file'
                                            ? 'ring-2 ring-blue-500 border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-blue-300'"
                                        class="p-4 border rounded-lg text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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
                                        :class="form.source_type === 'collection'
                                            ? 'ring-2 ring-purple-500 border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-purple-300'"
                                        class="p-4 border rounded-lg text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            <div>
                                                <h3 class="font-medium">Collection</h3>
                                                <p class="text-sm text-gray-500">Battle with multiple files</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- File Selection (when source_type is 'file') -->
                            <div v-if="form.source_type === 'file'" class="mb-6">
                                <label for="file_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Select Your Study File
                                </label>
                                <select
                                    id="file_id"
                                    v-model="form.file_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    :required="form.source_type === 'file'"
                                >
                                    <option value="">Choose a file...</option>
                                    <option
                                        v-for="file in files"
                                        :key="file.id"
                                        :value="file.id"
                                    >
                                        {{ file.title || file.name }} ({{ file.quizzes_count || 0 }} questions)
                                    </option>
                                </select>
                                <div v-if="form.errors.file_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.file_id }}
                                </div>
                            </div>

                            <!-- Collection Selection (when source_type is 'collection') -->
                            <div v-if="form.source_type === 'collection'" class="mb-6">
                                <label for="collection_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Select Your Collection
                                </label>
                                <div v-if="collections.length === 0" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        You don't have any collections with files yet.
                                        <Link :href="route('collections.create')" class="underline font-medium">Create a collection</Link> first.
                                    </p>
                                </div>
                                <select
                                    v-else
                                    id="collection_id"
                                    v-model="form.collection_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    :required="form.source_type === 'collection'"
                                >
                                    <option value="">Choose a collection...</option>
                                    <option
                                        v-for="collection in collections"
                                        :key="collection.id"
                                        :value="collection.id"
                                    >
                                        {{ collection.name }} ({{ collection.file_count }} files, {{ collection.total_questions }} questions)
                                    </option>
                                </select>
                                <div v-if="form.errors.collection_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.collection_id }}
                                </div>
                            </div>

                            <!-- Difficulty Selection -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Choose Difficulty
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'easy'"
                                        :class="selectedDifficulty === 'easy'
                                            ? 'ring-2 ring-green-500 border-green-500 bg-green-50 dark:bg-green-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-green-300'"
                                        class="p-4 border rounded-lg text-center transition-colors"
                                    >
                                        <div class="text-green-500 mb-2">
                                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">Easy</h3>
                                        <p class="text-sm text-gray-500">Lower HP monsters</p>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'medium'"
                                        :class="selectedDifficulty === 'medium'
                                            ? 'ring-2 ring-yellow-500 border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-yellow-300'"
                                        class="p-4 border rounded-lg text-center transition-colors"
                                    >
                                        <div class="text-yellow-500 mb-2">
                                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">Medium</h3>
                                        <p class="text-sm text-gray-500">Balanced challenge</p>
                                    </button>

                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'hard'"
                                        :class="selectedDifficulty === 'hard'
                                            ? 'ring-2 ring-red-500 border-red-500 bg-red-50 dark:bg-red-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-red-300'"
                                        class="p-4 border rounded-lg text-center transition-colors"
                                    >
                                        <div class="text-red-500 mb-2">
                                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">Hard</h3>
                                        <p class="text-sm text-gray-500">High HP monsters</p>
                                    </button>
                                </div>
                            </div>

                            <!-- Monster Selection -->
                            <div v-if="availableMonsters.length > 0" class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Choose Your Opponent
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <button
                                        type="button"
                                        v-for="monster in availableMonsters"
                                        :key="monster.id"
                                        @click="form.monster_id = monster.id"
                                        :class="form.monster_id === monster.id
                                            ? 'ring-2 ring-indigo-500 border-indigo-500'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-indigo-300'"
                                        class="p-4 border rounded-lg text-left transition-colors"
                                    >
                                        <div class="flex items-center">
                                            <img
                                                v-if="monster.image_path"
                                                :src="monster.image_path"
                                                :alt="monster.name"
                                                class="w-12 h-12 rounded-full mr-3"
                                                @error="$event.target.style.display = 'none'"
                                            >
                                            <div>
                                                <h3 class="font-medium">{{ monster.name }}</h3>
                                                <p class="text-sm text-gray-500">HP: {{ monster.hp }}</p>
                                                <p class="text-sm text-gray-500">Attack: {{ monster.attack }}</p>
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
                                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                >
                                    ← Back to Battles
                                </Link>

                                <button
                                    type="submit"
                                    :disabled="form.processing || !canSubmit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Monster {
    id: string
    name: string
    hp: number
    attack: number
    image: string
    difficulty: string
}

interface File {
    id: number
    name: string
    title?: string
    quizzes_count?: number
}

interface Collection {
    id: number
    name: string
    file_count: number
    total_questions: number
}

const props = defineProps<{
    monsters: Monster[]
    files: File[]
    collections: Collection[]
}>()

const selectedDifficulty = ref('easy')

const form = useForm({
    source_type: 'file',
    file_id: '',
    collection_id: '',
    monster_id: ''
})

const availableMonsters = computed(() => {
    return props.monsters.filter(monster =>
        monster.difficulty === selectedDifficulty.value
    )
})

const canSubmit = computed(() => {
    const hasSource = form.source_type === 'file' ? form.file_id : form.collection_id
    return hasSource && form.monster_id && selectedDifficulty.value
})

// Reset monster selection when difficulty changes
watch(selectedDifficulty, () => {
    form.monster_id = ''
})

// Reset file/collection when source type changes
watch(() => form.source_type, () => {
    form.file_id = ''
    form.collection_id = ''
})

const submit = () => {
    form.post(route('battles.store'))
}
</script>
