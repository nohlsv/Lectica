<template>
    <Head title="Monster Arena Challenge Results" />

    <AppLayout>
        <div class="bg-gradient min-h-screen py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Battle Summary Card -->
                <div class="bg-container mx-4 mb-6 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Battle Result Header -->
                        <div class="mb-6 text-center">
                            <div v-if="battle.status === 'won'" class="text-green-600">
                                <div class="pixel-outline mb-4 text-6xl">🏆</div>
                                <h2 class="pixel-outline mb-2 text-3xl font-bold">Victory!</h2>
                                <p class="pixel-outline text-gray-600 dark:text-gray-400">You conquered the monster gauntlet!</p>
                            </div>
                            <div v-else-if="battle.status === 'lost'" class="text-red-600">
                                <div class="pixel-outline mb-4 text-6xl">💀</div>
                                <h2 class="pixel-outline mb-2 text-3xl font-bold">Defeat</h2>
                                <p class="pixel-outline text-gray-600 dark:text-gray-400">The monsters proved too powerful!</p>
                            </div>
                            <div v-else-if="battle.status === 'active'" class="text-blue-600">
                                <div class="pixel-outline mb-4 text-6xl">⚔️</div>
                                <h2 class="pixel-outline mb-2 text-3xl font-bold">Battle in Progress</h2>
                                <p class="pixel-outline text-gray-600 dark:text-gray-400">The monster arena awaits!</p>
                            </div>
                            <div v-else class="text-gray-600">
                                <div class="pixel-outline mb-4 text-6xl">🚪</div>
                                <h2 class="pixel-outline mb-2 text-3xl font-bold">Battle Abandoned</h2>
                                <p class="pixel-outline text-gray-600 dark:text-gray-400">You left the battle.</p>
                            </div>
                        </div>

                        <!-- Battle Info Grid -->
                        <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Player Stats -->
                            <div class="rounded-lg border-2 border-green-500 bg-black/50 p-4">
                                <h3 class="pixel-outline mb-3 text-lg font-semibold text-gray-100">Player</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Health:</span>
                                        <span class="pixel-outline font-semibold">{{ battle.player_hp }}/100</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Correct Answers:</span>
                                        <span class="pixel-outline font-semibold text-green-600">{{ battle.correct_answers }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Total Questions:</span>
                                        <span class="pixel-outline font-semibold">{{ battle.total_questions }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Accuracy:</span>
                                        <span class="pixel-outline font-semibold">{{ accuracy }}%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Battle Challenge Stats -->
                            <div class="rounded-lg border-2 border-red-500 bg-black/50 p-4">
                                <h3 class="pixel-outline mb-3 text-lg font-semibold text-gray-100">🏟️ Monster Arena Challenge</h3>
                                <div class="mb-3 flex justify-center">
                                    <div class="pixel-outline text-4xl">⚔️</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Monsters Faced:</span>
                                        <span class="pixel-outline font-semibold">{{ battle.total_questions || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Monsters Defeated:</span>
                                        <span class="pixel-outline font-semibold text-green-400">{{ battle.correct_answers || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="pixel-outline text-gray-400">Success Rate:</span>
                                        <span class="pixel-outline font-semibold">{{ accuracy }}%</span>
                                    </div>
                                </div>
                                <div class="pixel-outline mt-3 text-center text-sm text-gray-400">Each correct answer defeated a random monster!</div>
                            </div>
                        </div>

                        <!-- File Information -->
                        <div class="mb-6 rounded-lg border-2 border-blue-500 bg-black/50 p-4">
                            <h3 class="pixel-outline mb-2 text-lg font-semibold text-gray-100">Study Material</h3>
                            <p class="pixel-outline text-gray-400">{{ file?.title || file?.name }}</p>
                            <p class="pixel-outline mt-1 text-sm text-gray-500">Battle started: {{ new Date(battle.created_at).toLocaleString() }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col justify-center gap-4 sm:flex-row">
                            <!-- For active battles, the controller will automatically show BattleQuiz -->
                            <Link
                                v-if="battle.status === 'active'"
                                :href="route('battles.show', battle.id)"
                                class="pixel-outline rounded-lg bg-green-500 px-6 py-3 text-center font-bold text-white hover:bg-green-700"
                            >
                                Continue Battle
                            </Link>

                            <!-- Start New Battle button -->
                            <Link
                                :href="route('battles.create')"
                                class="pixel-outline rounded-lg bg-blue-500 px-6 py-3 text-center font-bold text-white hover:bg-blue-700"
                            >
                                Start New Battle
                            </Link>

                            <!-- Back to battles list -->
                            <Link
                                :href="route('battles.index')"
                                class="pixel-outline rounded-lg bg-red-500 px-6 py-3 text-center font-bold text-white hover:bg-red-700"
                            >
                                Back to Battles
                            </Link>

                            <!-- View Statistics -->
                            <Link
                                :href="route('battles.stats')"
                                class="pixel-outline rounded-lg bg-purple-500 px-6 py-3 text-center font-bold text-white hover:bg-purple-700"
                            >
                                View Statistics
                            </Link>

                            <!-- View Answer History -->
                            <Link
                                v-if="battle.status !== 'active'"
                                :href="route('battles.answers', battle.id)"
                                class="pixel-outline rounded-lg bg-yellow-500 px-6 py-3 text-center font-bold text-white hover:bg-yellow-700"
                            >
                                View Answer History
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
