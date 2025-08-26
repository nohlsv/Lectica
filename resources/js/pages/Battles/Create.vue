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
                            <!-- File Selection -->
                            <div class="mb-6">
                                <label for="file_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Select Your Study File
                                </label>
                                <select
                                    id="file_id"
                                    v-model="form.file_id"
                                    class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Choose a file...</option>
                                    <option
                                        v-for="file in files"
                                        :key="file.id"
                                        :value="file.id"
                                    >
                                        {{ file.title || file.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.file_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.file_id }}
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
                                            ? 'ring-2 ring-green-500 border-green-500'
                                            : 'border-gray-300 dark:border-gray-600'"
                                        class="p-4 border rounded-lg text-center hover:border-green-500 transition-colors bg-white dark:bg-gray-700"
                                    >
                                        <div class="text-green-600 text-2xl mb-2">🟢</div>
                                        <div class="font-semibold text-green-600">Easy</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">For beginners</div>
                                    </button>
                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'medium'"
                                        :class="selectedDifficulty === 'medium'
                                            ? 'ring-2 ring-yellow-500 border-yellow-500'
                                            : 'border-gray-300 dark:border-gray-600'"
                                        class="p-4 border rounded-lg text-center hover:border-yellow-500 transition-colors bg-white dark:bg-gray-700"
                                    >
                                        <div class="text-yellow-600 text-2xl mb-2">🟡</div>
                                        <div class="font-semibold text-yellow-600">Medium</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Intermediate challenge</div>
                                    </button>
                                    <button
                                        type="button"
                                        @click="selectedDifficulty = 'hard'"
                                        :class="selectedDifficulty === 'hard'
                                            ? 'ring-2 ring-red-500 border-red-500'
                                            : 'border-gray-300 dark:border-gray-600'"
                                        class="p-4 border rounded-lg text-center hover:border-red-500 transition-colors bg-white dark:bg-gray-700"
                                    >
                                        <div class="text-red-600 text-2xl mb-2">🔴</div>
                                        <div class="font-semibold text-red-600">Hard</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">Expert level</div>
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
                                            ? 'ring-2 ring-blue-500 border-blue-500'
                                            : 'border-gray-300 dark:border-gray-600'"
                                        class="p-4 border rounded-lg text-center hover:border-blue-500 transition-colors bg-white dark:bg-gray-700"
                                    >
                                        <img
                                            v-if="monster.image_path"
                                            :src="monster.image_path"
                                            :alt="monster.name"
                                            class="w-16 h-16 mx-auto mb-2 rounded-lg object-cover"
                                        />
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ monster.name }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            HP: {{ monster.hp }} | ATK: {{ monster.attack }} | DEF: {{ monster.defense }}
                                        </div>
                                        <div v-if="monster.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ monster.description }}
                                        </div>
                                    </button>
                                </div>
                                <div v-if="form.errors.monster_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.monster_id }}
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('battles.index')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing || !form.file_id || !form.monster_id"
                                    class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded"
                                >
                                    <span v-if="form.processing">Starting Battle...</span>
                                    <span v-else>Start Battle!</span>
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
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { toast } from 'vue-sonner'

const props = defineProps({
    monsters: Array,
    files: Array,
})

const form = useForm({
    file_id: '',
    monster_id: '',
})

const selectedDifficulty = ref('easy')
const availableMonsters = ref([])

// Get monsters by difficulty from the config
const getMonstersByDifficulty = (difficulty) => {
    return props.monsters.filter(monster => {
        // Handle both string and numeric difficulty values
        if (typeof monster.difficulty === 'string') {
            return monster.difficulty === difficulty
        }

        // Legacy numeric difficulty mapping
        if (difficulty === 'easy') return monster.difficulty === 1
        if (difficulty === 'medium') return monster.difficulty === 2 || monster.difficulty === 3
        if (difficulty === 'hard') return monster.difficulty === 4
        return false
    })
}

// Watch for difficulty changes
watch(selectedDifficulty, (newDifficulty) => {
    availableMonsters.value = getMonstersByDifficulty(newDifficulty)
    form.monster_id = '' // Reset monster selection when difficulty changes
}, { immediate: true })

const submit = () => {
    // Validate that both file and monster are selected
    if (!form.file_id) {
        toast.error('Please select a file');
        return;
    }

    if (!form.monster_id) {
        toast.error('Please select a monster');
        return;
    }

    // Ensure monster_id is a string
    form.monster_id = String(form.monster_id)
    form.post(route('battles.store'))
}
</script>
