<template>
    <Head :title="`Battle Results: ${monster?.name || 'Monster'}`" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Battle Results</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Battle Summary Card -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <!-- Battle Result Header -->
                        <div class="mb-6 text-center">
                            <div v-if="battle.status === 'won'" class="text-green-600">
                                <div class="mb-4 text-6xl">🏆</div>
                                <h2 class="mb-2 text-3xl font-bold">Victory!</h2>
                                <p class="text-gray-600 dark:text-gray-400">You have defeated {{ monster?.name }}!</p>
                            </div>
                            <div v-else-if="battle.status === 'lost'" class="text-red-600">
                                <div class="mb-4 text-6xl">💀</div>
                                <h2 class="mb-2 text-3xl font-bold">Defeat</h2>
                                <p class="text-gray-600 dark:text-gray-400">{{ monster?.name }} has defeated you!</p>
                            </div>
                            <div v-else-if="battle.status === 'active'" class="text-blue-600">
                                <div class="mb-4 text-6xl">⚔️</div>
                                <h2 class="mb-2 text-3xl font-bold">Battle in Progress</h2>
                                <p class="text-gray-600 dark:text-gray-400">The battle continues!</p>
                            </div>
                            <div v-else class="text-gray-600">
                                <div class="mb-4 text-6xl">🚪</div>
                                <h2 class="mb-2 text-3xl font-bold">Battle Abandoned</h2>
                                <p class="text-gray-600 dark:text-gray-400">You left the battle.</p>
                            </div>
                        </div>

                        <!-- Battle Info Grid -->
                        <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Player Stats -->
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">Player</h3>
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
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ monster?.name || 'Monster' }}</h3>
                                <div class="mb-3 flex justify-center">
                                    <img
                                        v-if="monster?.image_path"
                                        :src="monster.image_path"
                                        :alt="monster.name"
                                        class="h-16 w-16 rounded-lg object-cover"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                    <div v-else class="text-sm text-red-500">No image_path found</div>
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
                        <div class="mb-6 rounded-lg bg-blue-50 p-4 dark:bg-blue-900/30">
                            <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Study Material</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ file?.title || file?.name }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">
                                Battle started: {{ new Date(battle.created_at).toLocaleString() }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col justify-center gap-4 sm:flex-row">
                            <!-- For active battles, the controller will automatically show BattleQuiz -->
                            <Link
                                v-if="battle.status === 'active'"
                                :href="route('battles.show', battle.id)"
                                class="rounded-lg bg-green-500 px-6 py-3 text-center font-bold text-white hover:bg-green-700"
                            >
                                Continue Battle
                            </Link>

                            <!-- Start New Battle button -->
                            <Link
                                :href="route('battles.create')"
                                class="rounded-lg bg-blue-500 px-6 py-3 text-center font-bold text-white hover:bg-blue-700"
                            >
                                Start New Battle
                            </Link>

                            <!-- Back to battles list -->
                            <Link
                                :href="route('battles.index')"
                                class="rounded-lg bg-gray-500 px-6 py-3 text-center font-bold text-white hover:bg-gray-700"
                            >
                                Back to Battles
                            </Link>

                            <!-- View Statistics -->
                            <Link
                                :href="route('battles.stats')"
                                class="rounded-lg bg-purple-500 px-6 py-3 text-center font-bold text-white hover:bg-purple-700"
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    battle: Object,
    monster: Object,
    file: Object,
    quizTypes: Object,
});

const accuracy = computed(() => {
    if (props.battle.total_questions === 0) return 0;
    return Math.round((props.battle.correct_answers / props.battle.total_questions) * 100);
});

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
        4: 'Expert',
    };
    return levels[difficulty] || 'Unknown';
};
</script>
