<template>
    <Head :title="`Battle Results: ${monster?.name || 'Monster'}`" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Battle Results
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Battle Summary Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <!-- Battle Result Header -->
                        <div class="text-center mb-6">
                            <div v-if="battle.status === 'won'" class="text-green-600">
                                <div class="text-6xl mb-4">🏆</div>
                                <h2 class="text-3xl font-bold mb-2">Victory!</h2>
                                <p class="text-gray-600 dark:text-gray-400">You have defeated {{ monster?.name }}!</p>
                            </div>
                            <div v-else-if="battle.status === 'lost'" class="text-red-600">
                                <div class="text-6xl mb-4">💀</div>
                                <h2 class="text-3xl font-bold mb-2">Defeat</h2>
                                <p class="text-gray-600 dark:text-gray-400">{{ monster?.name }} has defeated you!</p>
                            </div>
                            <div v-else-if="battle.status === 'active'" class="text-blue-600">
                                <div class="text-6xl mb-4">⚔️</div>
                                <h2 class="text-3xl font-bold mb-2">Battle in Progress</h2>
                                <p class="text-gray-600 dark:text-gray-400">The battle continues!</p>
                            </div>
                            <div v-else class="text-gray-600">
                                <div class="text-6xl mb-4">🚪</div>
                                <h2 class="text-3xl font-bold mb-2">Battle Abandoned</h2>
                                <p class="text-gray-600 dark:text-gray-400">You left the battle.</p>
                            </div>
                        </div>

                        <!-- Battle Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Player Stats -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Player</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Health:</span>
                                        <span class="font-semibold">{{ battle.player_hp }}/100</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Correct Answers:</span>
                                        <span class="font-semibold text-green-600">{{ battle.correct_answers }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Total Questions:</span>
                                        <span class="font-semibold">{{ battle.total_questions }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Accuracy:</span>
                                        <span class="font-semibold">{{ accuracy }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Monster Stats -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ monster?.name || 'Monster' }}</h3>
                                <div class="flex justify-center mb-3">
                                    <img
                                        v-if="monster?.image_path"
                                        :src="monster.image_path"
                                        :alt="monster.name"
                                        class="w-20 h-20 rounded-lg object-cover"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Health:</span>
                                        <span class="font-semibold">{{ battle.monster_hp }}/{{ monster?.hp || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Attack:</span>
                                        <span class="font-semibold">{{ monster?.attack || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Defense:</span>
                                        <span class="font-semibold">{{ monster?.defense || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Difficulty:</span>
                                        <span class="font-semibold">{{ getDifficultyText(monster?.difficulty) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- File Information -->
                        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Study Material</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ file?.title || file?.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">
                                Battle started: {{ new Date(battle.created_at).toLocaleString() }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <!-- For active battles, the controller will automatically show BattleQuiz -->
                            <Link
                                v-if="battle.status === 'active'"
                                :href="route('battles.show', battle.id)"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-center"
                            >
                                Continue Battle
                            </Link>

                            <!-- Start New Battle button -->
                            <Link
                                :href="route('battles.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center"
                            >
                                Start New Battle
                            </Link>

                            <!-- Back to battles list -->
                            <Link
                                :href="route('battles.index')"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg text-center"
                            >
                                Back to Battles
                            </Link>

                            <!-- View Statistics -->
                            <Link
                                :href="route('battles.stats')"
                                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg text-center"
                            >
                                View Statistics
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed } from 'vue'

const props = defineProps({
    battle: Object,
    monster: Object,
    file: Object,
    quizTypes: Object,
})

const accuracy = computed(() => {
    if (props.battle.total_questions === 0) return 0
    return Math.round((props.battle.correct_answers / props.battle.total_questions) * 100)
})

const getDifficultyText = (difficulty) => {
    // Handle both string and numeric difficulty values
    if (typeof difficulty === 'string') {
        return difficulty.charAt(0).toUpperCase() + difficulty.slice(1); // Capitalize first letter
    }

    // Legacy numeric difficulty mapping
    const levels = {
        1: 'Easy',
        2: 'Medium',
        3: 'Hard',
        4: 'Expert'
    }
    return levels[difficulty] || 'Unknown'
}
</script>
