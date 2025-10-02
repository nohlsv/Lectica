<template>
    <Head title="Monster Arena Challenge Results" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Battle Results</h2>
        </template>

        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Battle Summary Card -->
                <div class="mb-6 mx-4 overflow-hidden bg-container shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Battle Result Header -->
                        <div class="mb-6 text-center">
                            <div v-if="battle.status === 'won'" class="text-green-600">
                                <div class="mb-4 text-6xl pixel-outline">🏆</div>
                                <h2 class="mb-2 text-3xl font-bold pixel-outline ">Victory!</h2>
                                <p class="text-gray-600 dark:text-gray-400 pixel-outline">You conquered the monster gauntlet!</p>
                            </div>
                            <div v-else-if="battle.status === 'lost'" class="text-red-600">
                                <div class="mb-4 text-6xl pixel-outline">💀</div>
                                <h2 class="mb-2 text-3xl font-bold pixel-outline">Defeat</h2>
                                <p class="text-gray-600 dark:text-gray-400 pixel-outline">The monsters proved too powerful!</p>
                            </div>
                            <div v-else-if="battle.status === 'active'" class="text-blue-600">
                                <div class="mb-4 text-6xl pixel-outline">⚔️</div>
                                <h2 class="mb-2 text-3xl font-bold pixel-outline">Battle in Progress</h2>
                                <p class="text-gray-600 dark:text-gray-400 pixel-outline">The monster arena awaits!</p>
                            </div>
                            <div v-else class="text-gray-600">
                                <div class="mb-4 text-6xl pixel-outline">🚪</div>
                                <h2 class="mb-2 text-3xl font-bold pixel-outline">Battle Abandoned</h2>
                                <p class="text-gray-600 dark:text-gray-400 pixel-outline">You left the battle.</p>
                            </div>
                        </div>

                        <!-- Battle Info Grid -->
                        <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Player Stats -->
                            <div class="rounded-lg bg-black/50 p-4 border-2 border-green-500">
                                <h3 class="mb-3 text-lg font-semibold text-gray-100 pixel-outline">Player</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Health:</span>
                                        <span class="font-semibold pixel-outline">{{ battle.player_hp }}/100</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Correct Answers:</span>
                                        <span class="font-semibold text-green-600 pixel-outline">{{ battle.correct_answers }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Total Questions:</span>
                                        <span class="font-semibold pixel-outline">{{ battle.total_questions }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Accuracy:</span>
                                        <span class="font-semibold pixel-outline">{{ accuracy }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Battle Challenge Stats -->
                            <div class="rounded-lg bg-black/50 p-4 border-2 border-red-500">
                                <h3 class="mb-3 text-lg font-semibold text-gray-100 pixel-outline">🏟️ Monster Arena Challenge</h3>
                                <div class="mb-3 flex justify-center">
                                    <div class="text-4xl pixel-outline">⚔️</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Monsters Faced:</span>
                                        <span class="font-semibold pixel-outline">{{ battle.total_questions || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Monsters Defeated:</span>
                                        <span class="font-semibold text-green-400 pixel-outline">{{ battle.correct_answers || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400 pixel-outline">Success Rate:</span>
                                        <span class="font-semibold pixel-outline">{{ accuracy }}%</span>
                                    </div>
                                </div>
                                <div class="mt-3 text-center text-sm text-gray-400 pixel-outline">
                                    Each correct answer defeated a random monster!
                                </div>
                            </div>
                        </div>

                        <!-- File Information -->
                        <div class="mb-6 rounded-lg bg-black/50 p-4 border-2 border-blue-500">
                            <h3 class="mb-2 text-lg font-semibold text-gray-100 pixel-outline">Study Material</h3>
                            <p class="text-gray-400 pixel-outline">{{ file?.title || file?.name }}</p>
                            <p class="mt-1 text-sm text-gray-500 pixel-outline">
                                Battle started: {{ new Date(battle.created_at).toLocaleString() }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col justify-center gap-4 sm:flex-row">
                            <!-- For active battles, the controller will automatically show BattleQuiz -->
                            <Link
                                v-if="battle.status === 'active'"
                                :href="route('battles.show', battle.id)"
                                class="rounded-lg bg-green-500 px-6 py-3 text-center font-bold text-white hover:bg-green-700 pixel-outline"
                            >
                                Continue Battle
                            </Link>

                            <!-- Start New Battle button -->
                            <Link
                                :href="route('battles.create')"
                                class="rounded-lg bg-blue-500 px-6 py-3 text-center font-bold text-white hover:bg-blue-700 pixel-outline"
                            >
                                Start New Battle
                            </Link>

                            <!-- Back to battles list -->
                            <Link
                                :href="route('battles.index')"
                                class="rounded-lg bg-red-500 px-6 py-3 text-center font-bold text-white hover:bg-red-700 pixel-outline"
                            >
                                Back to Battles
                            </Link>

                            <!-- View Statistics -->
                            <Link
                                :href="route('battles.stats')"
                                class="rounded-lg bg-purple-500 px-6 py-3 text-center font-bold text-white hover:bg-purple-700 pixel-outline"
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
