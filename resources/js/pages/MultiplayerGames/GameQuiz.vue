<template>
    <Head title="Multiplayer Game" />

    <AppLayout>
        <div class="py-12 bg-gradient min-h-screen">
            <!-- Custom Header -->
            <div class="mb-6 mx-4 bg-container shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl leading-tight font-semibold text-gray-100 pixel-outline">
                        {{ gameState.game_mode === 'pvp' ? 'PvP Battle' : `Battle vs ${gameState.monster?.name || 'Monster'}` }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <div
                            v-if="gameState.game_mode === 'pve' && gameState.monster"
                            class="flex items-center space-x-2 rounded-lg bg-red-100 px-3 py-1 dark:bg-red-900/20"
                        >
                            <img
                                :src="gameState.monster.image_path || '/images/default-monster.png'"
                                :alt="gameState.monster.name"
                                class="h-6 w-6 rounded-full object-cover"
                                @error="handleImageError"
                            />
                            <span class="text-sm font-medium text-red-800 dark:text-red-300">
                                {{ gameState.monster.name }}: {{ gameState.monster_hp }}❤️
                            </span>
                        </div>
                        <button
                            @click="toggleSound"
                            :class="[
                                'rounded-md px-3 py-1 text-sm text-white transition-colors pixel-outline',
                                soundEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700'
                            ]"
                        >
                            {{ soundEnabled ? '🔊 Sound On' : '🔇 Sound Off' }}
                        </button>
                        <button @click="forfeitGame" class="rounded-md bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 pixel-outline">Forfeit</button>
                    </div>
                </div>
            </div>
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Game Status Bar -->
                <div class="mb-6 mx-4 rounded-lg bg-black/50 p-4 shadow-sm border-2 border-green-500">
                    <div class="flex items-center justify-between">
                        <!-- Player 1 -->
                        <div class="flex items-center space-x-3">
                            <div class="hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/50 border-2 border-green-500">
                                <span class="font-bold text-blue-300 pixel-outline">
                                    {{ gameState.playerOne.first_name.charAt(0) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-green-300 pixel-outline">
                                    {{ gameState.playerOne.first_name }} {{ gameState.playerOne.last_name }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    <!-- PvP Mode: Show HP or Accuracy based on pvp_mode -->
                                    <template v-if="gameState.game_mode === 'pvp'">
                                        <template v-if="gameState.pvp_mode === 'hp'">
                                            <!-- Health Bar -->
                                            <div class="flex items-center space-x-1">
                                                <span class="text-xs text-red-400 pixel-outline">HP:</span>
                                                <div class="w-16 h-2 bg-gray-700 rounded border border-gray-600">
                                                    <div 
                                                        :style="{ width: `${Math.max(0, gameState.player_one_hp)}%` }"
                                                        class="h-full rounded transition-all duration-500"
                                                        :class="gameState.player_one_hp > 50 ? 'bg-green-500' : gameState.player_one_hp > 25 ? 'bg-yellow-500' : 'bg-red-500'"
                                                    ></div>
                                                </div>
                                                <span class="text-xs text-white pixel-outline">{{ gameState.player_one_hp }}</span>
                                            </div>
                                            <span class="text-sm text-purple-500 pixel-outline">🔥 {{ gameState.player_one_streak || 0 }}</span>
                                            <span class="text-sm text-yellow-500 pixel-outline">⭐ {{ gameState.player_one_score }}</span>
                                        </template>
                                        <template v-else>
                                            <span class="text-sm text-blue-500 pixel-outline">🎯 {{ gameState.player_one_accuracy || 0 }}%</span>
                                            <span class="text-sm text-purple-500 pixel-outline">🔥 {{ gameState.player_one_streak || 0 }}</span>
                                            <span class="text-sm text-yellow-500 pixel-outline">⭐ {{ gameState.player_one_score }}</span>
                                        </template>
                                    </template>
                                    <!-- PVE Mode: Show HP and Score -->
                                    <template v-else>
                                        <!-- Health Bar for PVE -->
                                        <div class="flex items-center space-x-1">
                                            <span class="text-xs text-red-400 pixel-outline">HP:</span>
                                            <div class="w-16 h-2 bg-gray-700 rounded border border-gray-600">
                                                <div 
                                                    :style="{ width: `${Math.max(0, gameState.player_one_hp)}%` }"
                                                    class="h-full rounded transition-all duration-500"
                                                    :class="gameState.player_one_hp > 50 ? 'bg-green-500' : gameState.player_one_hp > 25 ? 'bg-yellow-500' : 'bg-red-500'"
                                                ></div>
                                            </div>
                                            <span class="text-xs text-white pixel-outline">{{ gameState.player_one_hp }}</span>
                                        </div>
                                        <span class="text-sm text-yellow-500 pixel-outline">⭐ {{ gameState.player_one_score }}</span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Turn Indicator -->
                        <div class="text-center">
                            <div v-if="isMyTurn" class="rounded-full bg-green-900/20 px-4 py-2">
                                <p class="text-sm sm:text-md font-medium text-green-300 pixel-outline">Your Turn!</p>
                            </div>
                            <div v-else class="rounded-full bg-red-900/20 px-4 py-2">
                                <p class="text-sm sm:text-md font-medium text-red-500 pixel-outline">Opponent's Turn</p>
                            </div>
                        </div>

                        <!-- Player 2 -->
                        <div class="flex items-center space-x-3">
                            <div>
                                <p class="text-right font-medium text-green-300 pixel-outline">
                                    {{ gameState.playerTwo.first_name }} {{ gameState.playerTwo.last_name }}
                                </p>
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- PvP Mode: Show HP or Accuracy based on pvp_mode -->
                                    <template v-if="gameState.game_mode === 'pvp'">
                                        <template v-if="gameState.pvp_mode === 'hp'">
                                            <span class="text-sm text-yellow-500 pixel-outline">⭐ {{ gameState.player_two_score }}</span>
                                            <span class="text-sm text-purple-500 pixel-outline">🔥 {{ gameState.player_two_streak || 0 }}</span>
                                            <!-- Health Bar -->
                                            <div class="flex items-center space-x-1">
                                                <span class="text-xs text-white pixel-outline">{{ gameState.player_two_hp }}</span>
                                                <div class="w-16 h-2 bg-gray-700 rounded border border-gray-600">
                                                    <div 
                                                        :style="{ width: `${Math.max(0, gameState.player_two_hp)}%` }"
                                                        class="h-full rounded transition-all duration-500"
                                                        :class="gameState.player_two_hp > 50 ? 'bg-green-500' : gameState.player_two_hp > 25 ? 'bg-yellow-500' : 'bg-red-500'"
                                                    ></div>
                                                </div>
                                                <span class="text-xs text-red-400 pixel-outline">:HP</span>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <span class="text-sm text-yellow-500 pixel-outline">⭐ {{ gameState.player_two_score }}</span>
                                            <span class="text-sm text-purple-500 pixel-outline">🔥 {{ gameState.player_two_streak || 0 }}</span>
                                            <span class="text-sm text-blue-500 pixel-outline">🎯 {{ gameState.player_two_accuracy || 0 }}%</span>
                                        </template>
                                    </template>
                                    <!-- PVE Mode: Show Score and HP -->
                                    <template v-else>
                                        <span class="text-sm text-yellow-500 pixel-outline">⭐ {{ gameState.player_two_score }}</span>
                                        <!-- Health Bar for PVE -->
                                        <div class="flex items-center space-x-1">
                                            <span class="text-xs text-white pixel-outline">{{ gameState.player_two_hp }}</span>
                                            <div class="w-16 h-2 bg-gray-700 rounded border border-gray-600">
                                                <div 
                                                    :style="{ width: `${Math.max(0, gameState.player_two_hp)}%` }"
                                                    class="h-full rounded transition-all duration-500"
                                                    :class="gameState.player_two_hp > 50 ? 'bg-green-500' : gameState.player_two_hp > 25 ? 'bg-yellow-500' : 'bg-red-500'"
                                                ></div>
                                            </div>
                                            <span class="text-xs text-red-400 pixel-outline">:HP</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/50 border-2 border-green-500">
                                <span class="font-bold text-green-300">
                                    {{ gameState.playerTwo.first_name.charAt(0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Question -->
                <div v-if="currentQuiz && isMyTurn" class="mb-6 mx-4 rounded-lg p-6 shadow-sm border-2 border-blue-500" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://copilot.microsoft.com/th/id/BCO.ae604036-caed-42e3-b47b-176397eb9693.png'); background-size: cover; background-position: center;">
                    <div class="mb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm text-gray-400 pixel-outline">{{ quizTypes[currentQuiz.type] || 'Question' }}</span>
                            <span class="text-sm text-gray-400 pixel-outline ml-6 sm:ml-0">Source: {{ game.source_name }}</span>
                        </div>
                        <div class="items-center p-4 -mx-6.5 border-2 border-blue-500 bg-black/50">
                            <h3 class="text-md sm:text-lg font-medium text-center text-blue-200 pixel-outline">
                                {{ currentQuiz.question }}
                            </h3>
                        </div>
                    </div>

                    <!-- Timer Constraint -->
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span 
                                :class="[
                                    'text-sm font-semibold pixel-outline',
                                    timer > 10 ? 'text-blue-500' : timer > 5 ? 'text-yellow-500' : 'text-red-500'
                                ]"
                            > 
                                ⏱️ Time left: {{ timer }}s
                            </span>
                            <div 
                                :class="[
                                    'w-16 h-2 bg-gray-700 rounded-full overflow-hidden',
                                    timer <= 5 ? 'animate-pulse' : ''
                                ]"
                            >
                                <div 
                                    :class="[
                                        'h-full transition-all duration-1000 ease-linear',
                                        getTimerColor(timer, timerDuration)
                                    ]"
                                    :style="{ width: `${(timer / timerDuration) * 100}%` }"
                                ></div>
                            </div>
                        </div>
                        <span v-if="timer === 0" class="text-sm text-red-500 pixel-outline animate-pulse">⏰ Time's up!</span>
                    </div>

                    <!-- Multiple Choice -->
                    <div v-if="currentQuiz.type === 'multiple_choice'" class="space-y-3">
                        <button
                            v-for="(option, index) in currentQuiz.options"
                            :key="index"
                            @click="selectAnswer(option)"
                            :disabled="answerSubmitted || timedOut"
                            :class="[
                                'w-full rounded-lg border p-3 text-left transition-colors',
                                selectedAnswer === option
                                    ? 'bg-black/50 border-2 border-blue-500 text-white shadow-md hover:scale-105 transition-transform duration-500 pixel-outline'
                                    : 'bg-black/50 border-2 border-gray-500 text-gray-300 hover:scale-105 transition-transform duration-500 pixel-outline',
                                (answerSubmitted || timedOut) && 'cursor-not-allowed opacity-75',
                            ]"
                        >
                            {{ option }}
                        </button>
                    </div>

                    <!-- True/False -->
                    <div v-else-if="currentQuiz.type === 'true_false'" class="flex space-x-4">
                        <button
                            @click="selectAnswer('True')"
                            :disabled="answerSubmitted || timedOut"
                            :class="[
                                'flex-1 rounded-lg border p-3 transition-colors',
                                selectedAnswer === 'True'
                                    ? 'bg-green-900/20 border-2 border-green-500 text-white shadow-md hover:scale-105 transition-transform duration-500 pixel-outline'
                                    : 'bg-black/50 border-2 border-gray-500 text-gray-300 hover:scale-105 transition-transform duration-500 pixel-outline',
                                (answerSubmitted || timedOut) && 'cursor-not-allowed opacity-75',
                            ]"
                        >
                            True
                        </button>
                        <button
                            @click="selectAnswer('False')"
                            :disabled="answerSubmitted || timedOut"
                            :class="[
                                'flex-1 rounded-lg border p-3 transition-colors',
                                selectedAnswer === 'False'
                                    ? 'bg-red-900/20 border-2 border-red-500 text-white shadow-md hover:scale-105 transition-transform duration-500 pixel-outline'
                                    : 'bg-black/50 border-2 border-gray-500 text-gray-300 hover:scale-105 transition-transform duration-500 pixel-outline',
                                (answerSubmitted || timedOut) && 'cursor-not-allowed opacity-75',
                            ]"
                        >
                            False
                        </button>
                    </div>

                    <!-- Enumeration -->
                    <div v-else-if="currentQuiz.type === 'enumeration'" class="space-y-3">
                        <p class="mb-2 font-bold text-blue-500 pixel-outline">Please provide {{ currentQuiz.answers.length }} answers:</p>
                        <div v-for="idx in currentQuiz.answers.length" :key="idx" class="mb-2">
                            <input
                                type="text"
                                :placeholder="`Answer ${idx + 1}`"
                                v-model="selectedAnswer[idx]"
                                @input="updateEnumerationAnswer(idx, selectedAnswer[idx])"
                                :disabled="answerSubmitted || timedOut"
                                class="w-full rounded-lg border border-blue-500 p-2 bg-black/50 pixel-outline text-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="() => submitAnswer()"
                            :disabled="!selectedAnswer || answerSubmitted || submitting || timedOut"
                            :class="[
                                'inline-flex items-center pixel-outline rounded-md px-4 py-2 text-sm font-medium text-white transition-colors focus:ring-2 focus:ring-offset-2 disabled:opacity-50',
                                timedOut ? 'bg-red-600' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
                            ]"
                        >
                            <span v-if="submitting" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            <span v-if="timedOut">⏰ Time's Up!</span>
                            <span v-else-if="submitting">Submitting...</span>
                            <span v-else>Submit Answer</span>
                        </button>
                    </div>
                </div>

                <!-- Waiting for Opponent -->
                <div v-else-if="!isMyTurn" class="mx-4 rounded-lg p-8 border-red-500 border-2 text-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://copilot.microsoft.com/th/id/BCO.ae604036-caed-42e3-b47b-176397eb9693.png'); background-size: cover; background-position: center;">
                    <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-red-200 border-t-red-600 pixel-outline-icon"></div>
                    <div class="items-center -mx-8.5 mb-2 bg-black/50 border-2 border-red-500 p-4">
                        <h3 class="text-lg font-medium text-center text-red-500 pixel-outline">Waiting for opponent...</h3>
                    </div> 
                    <p class="text-gray-400 pixel-outline">Your opponent is answering their question.</p>
                </div>

                <!-- Game Over -->
                <div v-else-if="gameOver" class="rounded-lg bg-gray-50 p-8 text-center dark:bg-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Game Over!</h3>
                    <p class="mt-2 text-gray-500">
                        {{ getGameResult() }}
                    </p>
                    <div class="mt-6">
                        <Link
                            :href="route('multiplayer-games.lobby')"
                            class="inline-flex items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
                        >
                            Back to Lobby
                        </Link>
                    </div>
                </div>

                <!-- Last Action Feedback -->
                <div
                    v-if="lastAction"
                    class="mt-4 rounded-lg p-4"
                    :class="lastAction.type === 'success' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'"
                >
                    <div class="flex items-center">
                        <div v-if="lastAction.type === 'success'" class="mr-3 h-5 w-5 text-green-400">✓</div>
                        <div v-else class="mr-3 h-5 w-5 text-red-400">✗</div>
                        <p
                            class="text-sm"
                            :class="lastAction.type === 'success' ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300'"
                        >
                            {{ lastAction.message }}
                        </p>
                    </div>
                </div>

                <!-- Visual Feedback Animations -->
                <div v-if="showOpponentAction" class="mt-4 rounded-lg bg-gray-100 p-4 dark:bg-gray-800">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ opponentFeedback?.name }}'s answer was
                        <span :class="opponentFeedback?.isCorrect ? 'text-green-500' : 'text-red-500'">{{
                            opponentFeedback?.isCorrect ? 'correct' : 'wrong'
                        }}</span>
                        <!--                        : "{{ opponentFeedback?.answer }}"-->
                    </p>
                </div>

                <div v-if="accuracyAnimation" class="mt-4">
                    <transition name="fade">
                        <div v-if="accuracyAnimation.player === 'one'" class="text-center">
                            <p class="text-sm text-blue-500">🎯 Player 1 accuracy: {{ game.player_one_accuracy }}%</p>
                        </div>
                        <div v-else-if="accuracyAnimation.player === 'two'" class="text-center">
                            <p class="text-sm text-green-500">🎯 Player 2 accuracy: {{ game.player_two_accuracy }}%</p>
                        </div>
                    </transition>
                </div>

                <div v-if="streakAnimation" class="mt-4">
                    <transition name="fade">
                        <div v-if="streakAnimation.player === 'one'" class="text-center">
                            <p class="text-sm text-purple-500">🔥 Player 1 streak: {{ streakAnimation.streak }}</p>
                        </div>
                        <div v-else-if="streakAnimation.player === 'two'" class="text-center">
                            <p class="text-sm text-orange-500">🔥 Player 2 streak: {{ streakAnimation.streak }}</p>
                        </div>
                    </transition>
                </div>

                <div v-if="gameStartAnimation" class="mt-4 text-center">
                    <p class="text-lg font-semibold text-green-600">The game has started! 🎉</p>
                </div>

                <div v-if="gameEndAnimation" class="mt-4 text-center">
                    <p class="text-lg font-semibold text-red-600">The game has ended! ⏹️</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

interface Quiz {
    id: number;
    question: string;
    type: string;
    options?: string[];
    answers: string[]; // Changed from correct_answer: string to answers: string[]
}

interface Game {
    id: number;
    game_mode: 'pve' | 'pvp';
    current_turn: number;
    current_question_index: number;
    player_one_hp: number;
    player_two_hp: number;
    player_one_score: number;
    player_two_score: number;
    player_one_accuracy?: number;
    player_two_accuracy?: number;
    player_one_streak?: number;
    player_two_streak?: number;
    player_one_max_streak?: number;
    player_two_max_streak?: number;
    monster_hp?: number;
    status: string;
    playerOne: any;
    playerTwo: any;
    currentUser: any;
    monster?: any;
    source_name: string;
    pvp_mode?: string; // <-- Add pvp_mode to type
}

const props = defineProps<{
    game: Game;
    quizzes: Quiz[];
    currentQuestion: Quiz | null;
    quizTypes: Record<string, string>;
}>();

// Game state
const selectedAnswer = ref<string | string[]>('');
const answerSubmitted = ref(false);
const submitting = ref(false);
const gameOver = ref(false);
const lastAction = ref<{ type: 'success' | 'error'; message: string } | null>(null);
const currentQuestion = ref(props.currentQuestion);

// Create reactive game state to ensure proper updates
const gameState = ref({ ...props.game });

// Visual feedback state
const showOpponentAction = ref(false);
const opponentFeedback = ref<{ name: string; isCorrect: boolean; answer: string } | null>(null);
const accuracyAnimation = ref<{ player: 'one' | 'two'; change: number } | null>(null);
const streakAnimation = ref<{ player: 'one' | 'two'; streak: number } | null>(null);
const gameStartAnimation = ref(false);
const gameEndAnimation = ref(false);
const soundEnabled = ref(true);

// Timer constraint - now managed by server
const DEFAULT_TIMER_DURATION = 30; // default seconds per question (for display purposes)
const timer = ref(0);
const timerDuration = ref(DEFAULT_TIMER_DURATION); // actual duration for current question
const timerRunning = ref(false);
const timedOut = ref(false);
const awaitingTimeoutResponse = ref(false); // Track if we're waiting for a timeout response
let timerSyncInterval: number | undefined;
const playerReady = ref(false);

// Helper function to get warning thresholds based on timer duration
const getWarningThresholds = (duration: number) => {
    if (duration >= 120) { // 2+ minutes
        return { first: 60, second: 30 };
    } else if (duration >= 60) { // 1-2 minutes
        return { first: 30, second: 15 };
    } else { // Less than 1 minute
        return { first: 10, second: 5 };
    }
};

// Helper function to get timer color based on remaining time and total duration
const getTimerColor = (remaining: number, total: number) => {
    const percentage = (remaining / total) * 100;
    if (percentage > 66) {
        return 'bg-green-500';
    } else if (percentage > 33) {
        return 'bg-yellow-500';
    } else {
        return 'bg-red-500';
    }
};

// Computed properties
const currentQuiz = computed(() => currentQuestion.value);
const isMyTurn = computed(() => {
    const isPlayerOne = gameState.value.currentUser.id === gameState.value.playerOne.id;
    return (isPlayerOne && gameState.value.current_turn === 1) || (!isPlayerOne && gameState.value.current_turn === 2);
});

// Watch for turn changes and game state
watch(isMyTurn, (newVal) => {
    if (newVal && gameState.value?.status === 'active') {
        startTimerSync();
    } else {
        stopTimerSync();
    }
});

// Watch for game status changes
watch(() => gameState.value?.status, (newStatus) => {
    if (newStatus === 'active') {
        startTimerSync();
    } else {
        stopTimerSync();
    }
});

// Methods
const selectAnswer = (answer: string) => {
    if (!answerSubmitted.value && !timedOut.value) {
        if (currentQuiz.value?.type === 'enumeration') {
            // Do nothing, handled by input fields
        } else {
            selectedAnswer.value = answer;
        }
    }
};

function updateEnumerationAnswer(idx: number, val: string) {
    if (!answerSubmitted.value && !timedOut.value && Array.isArray(selectedAnswer.value)) {
        selectedAnswer.value[idx] = val;
    }
}

const submitAnswer = async (isTimeout = false) => {
    if (!currentQuiz.value) return;
    if (
        (currentQuiz.value.type === 'enumeration' && Array.isArray(selectedAnswer.value) && selectedAnswer.value.every((a) => !a)) ||
        (!selectedAnswer.value && currentQuiz.value.type !== 'enumeration') ||
        submitting.value ||
        (timedOut.value && !isTimeout) // Only block if timed out AND not called from timeout handler
    )
        return;

    submitting.value = true;
    answerSubmitted.value = true;

    // Prepare answer for submission: always a string
    let answerToSubmit: string;
    if (currentQuiz.value?.type === 'enumeration') {
        // Always treat as string[]
        answerToSubmit = (selectedAnswer.value as string[]).join(', ');
    } else {
        // Always treat as string
        answerToSubmit = selectedAnswer.value as string;
    }

    try {
        const isCorrect = checkAnswer(selectedAnswer.value, currentQuiz.value);

        router.post(
            route('multiplayer-games.answer', props.game.id),
            {
                quiz_id: currentQuiz.value?.id,
                answer: answerToSubmit,
                is_correct: isCorrect,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('Answer submitted successfully, WebSocket will update game state');
                    // Don't reset submission state here - let WebSocket handle it
                },
                onError: (errors) => {
                    console.error('Answer submission errors:', errors);

                    // Handle specific error types
                    if (errors.turn) {
                        lastAction.value = { type: 'error', message: "It's not your turn! Please wait for your opponent." };
                    } else if (errors.game) {
                        lastAction.value = { type: 'error', message: errors.game };
                    } else {
                        const errorMessage = (Object.values(errors)[0] as string) || 'Failed to submit answer';
                        lastAction.value = { type: 'error', message: errorMessage };
                    }

                    // If this was a timeout submission that failed, try to sync game state
                    if (isTimeout && timedOut.value) {
                        console.warn('Timeout answer submission failed, syncing game state');
                        setTimeout(() => fetchFreshGameState(), 2000);
                    }

                    // Reset submission state on error
                    answerSubmitted.value = false;
                    submitting.value = false;
                    awaitingTimeoutResponse.value = false;
                },
            },
        );
    } catch (error) {
        console.error('Error submitting answer:', error);
        lastAction.value = { type: 'error', message: 'Failed to submit answer. Please try again.' };

        // Reset submission state so user can try again
        answerSubmitted.value = false;
        submitting.value = false;
    }
};

const checkAnswer = (userAnswer: string | string[], quiz: Quiz): boolean => {
    if (!quiz || !quiz.answers || quiz.answers.length === 0 || !userAnswer) {
        return false;
    }

    if (quiz.type === 'multiple_choice' || quiz.type === 'true_false') {
        const userAnswerLower = (userAnswer as string).toLowerCase().trim();
        const correctAnswer = quiz.answers[0].toLowerCase().trim();
        return userAnswerLower === correctAnswer;
    } else if (quiz.type === 'enumeration') {
        // Check if all required answers are provided correctly (case insensitive)
        const requiredAnswers = quiz.answers.map((ans) => ans.toLowerCase().trim());
        const userAnswersLower = (userAnswer as string[]).map((ans) => ans.toLowerCase().trim());
        // All required answers must be present
        return requiredAnswers.every((reqAns) => userAnswersLower.includes(reqAns));
    }
    return false;
};

// Sound effects
const correctSfx = new Audio('/sfx/correct.wav');
const incorrectSfx = new Audio('/sfx/incorrect.wav');
const gameStartSfx = new Audio('/sfx/game_start.wav');
const gameEndSfx = new Audio('/sfx/game_end.wav');
const turnStartSfx = new Audio('/sfx/turn_start.wav');
const streakSfx = new Audio('/sfx/streak.wav');
const victorySfx = new Audio('/sfx/victory.wav');
const defeatSfx = new Audio('/sfx/defeat.wav');
const damageSfx = new Audio('/sfx/damage.wav');
// Use existing sound files for timer warnings
const warningSfx = new Audio('/sfx/turn_start.wav'); // Repurpose existing sound
const urgentWarningSfx = new Audio('/sfx/incorrect.wav'); // Use incorrect sound for urgency  
const countdownSfx = new Audio('/sfx/damage.wav'); // Use damage sound for countdown

const showFeedback = (isCorrect: boolean, damageDealt: number, damageReceived: number) => {
    // Play sound effect (if enabled)
    if (soundEnabled.value) {
        if (isCorrect) {
            correctSfx.currentTime = 0;
            correctSfx.play();
        } else {
            incorrectSfx.currentTime = 0;
            incorrectSfx.play();
        }
    }

    if (props.game.game_mode === 'pvp') {
        // PVP Mode: Accuracy-focused feedback
        if (isCorrect) {
            lastAction.value = {
                type: 'success',
                message: `Correct! Your accuracy is improving! 🎯`,
            };
        } else {
            lastAction.value = {
                type: 'error',
                message: `Wrong answer. Your accuracy dropped slightly. 📉`,
            };
        }
    } else {
        // PVE Mode: Original damage-based feedback
        if (isCorrect) {
            playDamageSfx();
            lastAction.value = {
                type: 'success',
                message: `Correct! You dealt ${damageDealt} damage!`,
            };
        } else {
            playDamageSfx();
            lastAction.value = {
                type: 'error',
                message: `Wrong answer. You took ${damageReceived} damage.`,
            };
        }
    }

    // Clear feedback after 3 seconds
    setTimeout(() => {
        lastAction.value = null;
    }, 3000);
};

// Timer is now managed by server - these functions handle WebSocket updates
const startTimerSync = () => {
    stopTimerSync();
    // Poll timer status every second for smooth countdown display
    timerSyncInterval = window.setInterval(() => {
        syncTimerStatus();
    }, 1000);
};

const stopTimerSync = () => {
    if (timerSyncInterval) {
        clearInterval(timerSyncInterval);
        timerSyncInterval = undefined;
    }
    timerRunning.value = false;
};

// Mark player as ready (page loaded and ready to play)
const markPlayerReady = async () => {
    if (!gameState.value?.id || playerReady.value) return;
    
    try {
        await router.post(route('multiplayer-games.ready', gameState.value.id), {}, {
            preserveState: true,
            preserveScroll: true,
        });
        playerReady.value = true;
        console.log('Player marked as ready');
    } catch (error) {
        console.error('Failed to mark player as ready:', error);
    }
};

// Sync with server timer status
const syncTimerStatus = async () => {
    if (!gameState.value?.id) return;
    
    try {
        const response = await fetch(route('multiplayer-games.timer', gameState.value.id));
        const data = await response.json();
        
        if (data.timer.is_running) {
            timer.value = data.timer.remaining_time;
            timerDuration.value = data.timer.duration || DEFAULT_TIMER_DURATION; // Update actual duration
            timerRunning.value = true;
            
            // Play warning sounds based on dynamic timer duration
            if (soundEnabled.value) {
                const warningThresholds = getWarningThresholds(timerDuration.value);
                if (timer.value === warningThresholds.first) {
                    playWarningSound();
                } else if (timer.value === warningThresholds.second) {
                    playUrgentWarningSound();
                } else if (timer.value <= 3 && timer.value > 0) {
                    playCountdownSound();
                }
            }
            
            if (timer.value <= 0) {
                handleTimeout();
            }
        } else {
            timerRunning.value = false;
        }
    } catch (error) {
        console.error('Failed to sync timer status:', error);
    }
};

const handleTimeout = () => {
    // Timer timeout is now handled by server
    // This provides immediate feedback while waiting for server confirmation
    if (!answerSubmitted.value && isMyTurn.value && timer.value <= 0 && !timedOut.value) {
        timedOut.value = true;
        awaitingTimeoutResponse.value = true;
        
        // Play timeout sound immediately
        if (soundEnabled.value) {
            incorrectSfx.currentTime = 0;
            incorrectSfx.play();
        }
        
        // Show immediate feedback
        lastAction.value = { 
            type: 'error', 
            message: "⏰ Time's up! Your answer will be marked as incorrect..." 
        };
        
        console.log('Client-side timeout detected, waiting for server confirmation');
        
        // If server doesn't respond within 5 seconds, try to sync state
        setTimeout(() => {
            if (awaitingTimeoutResponse.value && timedOut.value) {
                console.warn('Server timeout confirmation delayed, fetching fresh state');
                fetchFreshGameState();
            }
        }, 5000);
    }
};

const resetForNextQuestion = () => {
    if (currentQuiz.value?.type === 'enumeration') {
        selectedAnswer.value = Array(currentQuiz.value.answers.length).fill('');
    } else {
        selectedAnswer.value = '';
    }
    answerSubmitted.value = false;
    timedOut.value = false;
    awaitingTimeoutResponse.value = false; // Clear the timeout response flag
    timer.value = timerDuration.value;
    // Timer is now managed by server, just sync status
    syncTimerStatus();
};

// Watch for question change to reset enumeration fields
watch(currentQuiz, (newQuiz) => {
    if (newQuiz && newQuiz.type === 'enumeration') {
        if (!Array.isArray(selectedAnswer.value) || selectedAnswer.value.length !== newQuiz.answers.length) {
            selectedAnswer.value = Array(newQuiz.answers.length).fill('');
        }
    }
});

// Clean up timer and intervals on unmount
onUnmounted(() => {
    stopTimerSync();
    stopStateCheckInterval();
});

const getGameResult = (): string => {
    if (gameState.value.game_mode === 'pvp') {
        // Use pvp_mode to determine win condition
        const pvpMode = gameState.value.pvp_mode ?? 'accuracy';
        const isPlayerOne = gameState.value.currentUser.id === gameState.value.playerOne.id;
        if (pvpMode === 'hp') {
            // HP-based PvP result
            const myHp = isPlayerOne ? (gameState.value.player_one_hp ?? 0) : (gameState.value.player_two_hp ?? 0);
            const opponentHp = isPlayerOne ? (gameState.value.player_two_hp ?? 0) : (gameState.value.player_one_hp ?? 0);
            if (myHp > opponentHp) {
                return `Victory! 🏆 Your HP: ${myHp} vs Opponent: ${opponentHp}`;
            } else if (myHp < opponentHp) {
                return `Defeat! 💔 Your HP: ${myHp} vs Opponent: ${opponentHp}`;
            } else {
                return `Tie! 🤝 Both players have ${myHp} HP`;
            }
        } else {
            // Default to accuracy-based PvP result
            const myAccuracy = isPlayerOne ? (gameState.value.player_one_accuracy ?? 0) : (gameState.value.player_two_accuracy ?? 0);
            const opponentAccuracy = isPlayerOne ? (gameState.value.player_two_accuracy ?? 0) : (gameState.value.player_one_accuracy ?? 0);
            if (myAccuracy > opponentAccuracy) {
                return `Victory! 🎯 Your accuracy: ${myAccuracy}% vs Opponent: ${opponentAccuracy}%`;
            } else if (myAccuracy < opponentAccuracy) {
                return `Defeat! 📉 Your accuracy: ${myAccuracy}% vs Opponent: ${opponentAccuracy}%`;
            } else {
                if (myAccuracy === 0 && opponentAccuracy === 0) {
                    return `No answers recorded! 🤔 The game ended unexpectedly.`;
                } else {
                    return `Tie! 🤝 Both players achieved ${myAccuracy}% accuracy`;
                }
            }
        }
    } else {
        // PVE Mode: Original HP-based results
        if ((gameState.value.monster_hp ?? 1) <= 0) {
            return 'Victory! You defeated the monster together!';
        } else {
            return 'Defeat! The monster was too strong.';
        }
    }
};

const forfeitGame = () => {
    if (confirm('Are you sure you want to forfeit this game? Your opponent will win.')) {
        router.post(route('multiplayer-games.forfeit', props.game.id));
    }
};

const toggleSound = () => {
    soundEnabled.value = !soundEnabled.value;
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/default-monster.png';
};

// Fetch fresh game state from the server
const fetchFreshGameState = async () => {
    try {
        console.log('Fetching fresh game state from server...');
        
        const response = await fetch(route('multiplayer-games.state', props.game.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.game) {
            // Store previous state for comparison
            const wasMyTurn = isMyTurn.value;
            const previousTurn = gameState.value.current_turn;
            
            // Update game state
            Object.assign(gameState.value, data.game);
            Object.assign(props.game, data.game);
            
            // Update current question if provided
            if (data.currentQuestion !== undefined) {
                currentQuestion.value = data.currentQuestion;
            }
            
            console.log('Fresh game state fetched successfully:', {
                previousTurn,
                newTurn: gameState.value.current_turn,
                wasMyTurn,
                isMyTurnNow: isMyTurn.value
            });
            
            // Clear timeout flags since we have fresh state
            awaitingTimeoutResponse.value = false;
            
            // Handle turn changes
            if (!wasMyTurn && isMyTurn.value) {
                resetForNextQuestion();
                console.log('Turn switched after state sync, resetting for next question');
            } else if (wasMyTurn && !isMyTurn.value) {
                // My turn ended, stop timer sync and reset timeout flags
                stopTimerSync();
                timedOut.value = false;
                awaitingTimeoutResponse.value = false;
                console.log('My turn ended after state sync, stopping timer');
            }
            
            // If we were stuck with a timeout but the turn hasn't changed,
            // and it's been more than a minute, the backend might need a nudge
            if (wasMyTurn && isMyTurn.value && timedOut.value) {
                const timeoutAge = Date.now() - (timer.value < timerDuration.value ? (timerDuration.value - timer.value) * 1000 : 60000);
                if (timeoutAge > 60000) { // Timeout was more than 1 minute ago
                    console.warn('Timeout persisted after state sync, game may need server-side correction');
                    // Don't reset the flags yet, let the next ping attempt to fix it
                }
            }
            
            // If game ended, handle it
            if (gameState.value.status === 'finished') {
                gameOver.value = true;
                console.log('Game ended according to fresh state');
            }
            
        } else {
            console.error('Invalid response format from game state API');
        }
        
    } catch (error) {
        console.error('Failed to fetch fresh game state:', error);
        lastAction.value = { type: 'error', message: 'Connection issue. Please check your internet connection.' };
    }
};

// Force synchronization by checking if the game progressed correctly
const forceSyncGameState = async () => {
    console.log('Forcing game state synchronization...');
    
    // First try to fetch fresh state
    await fetchFreshGameState();
    
    // If it's still showing as our turn after timeout, there might be a server-side issue
    // Let's try to "ping" the server to check if our timeout was processed
    if (isMyTurn.value && timedOut.value) {
        try {
            const response = await fetch(route('multiplayer-games.ping', props.game.id), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    check_stale: true,
                    last_action: 'timeout'
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('Ping response:', data);
                
                // If server says game should have progressed, fetch state again
                if (data.should_refresh || data.turn_changed) {
                    setTimeout(() => fetchFreshGameState(), 1000);
                }
            }
        } catch (error) {
            console.error('Failed to ping server for state sync:', error);
        }
    }
};

// WebSocket listener for real-time updates
let echo: any;
let stateCheckInterval: number | undefined;

// Periodic state check to ensure game synchronization
const startStateCheckInterval = () => {
    // Check game state every 30 seconds to catch any missed updates
    stateCheckInterval = window.setInterval(() => {
        // Only check if game is active and not during transitions
        if (gameState.value.status === 'active' && !submitting.value && !awaitingTimeoutResponse.value) {
            console.log('Periodic state check...');
            fetchFreshGameState();
        }
    }, 30000);
};

const stopStateCheckInterval = () => {
    if (stateCheckInterval) {
        clearInterval(stateCheckInterval);
        stateCheckInterval = undefined;
    }
};

onMounted(() => {
    console.log('DEBUG: gameState.value.pvp_mode =', gameState.value.pvp_mode);
    
    // Initialize selectedAnswer for enumeration questions on mount
    if (currentQuiz.value && currentQuiz.value.type === 'enumeration') {
        selectedAnswer.value = Array(currentQuiz.value.answers.length).fill('');
    }
    
    if (window.Echo) {
        echo = window.Echo.private(`multiplayer-game.${props.game.id}`)
            .listen('MultiplayerGameUpdated', (e: any) => {
                console.log('Received game update:', e);

                // Store previous state for comparisons
                const wasMyTurn = isMyTurn.value;
                const previousPlayerOneAccuracy = props.game.player_one_accuracy;
                const previousPlayerTwoAccuracy = props.game.player_two_accuracy;
                const previousPlayerOneStreak = props.game.player_one_streak;
                const previousPlayerTwoStreak = props.game.player_two_streak;

                // Handle different event types
                if (e.event_type === 'game_started') {
                    gameStartAnimation.value = true;
                    setTimeout(() => {
                        gameStartAnimation.value = false;
                    }, 3000);

                    // Navigate to game screen when game starts
                    setTimeout(() => {
                        router.visit(route('multiplayer-games.show', props.game.id));
                    }, 1500);
                } else if (e.event_type === 'game_ended') {
                    gameOver.value = true;
                    gameEndAnimation.value = true;
                    setTimeout(() => {
                        gameEndAnimation.value = false;
                    }, 4000);

                    // Navigate to results screen when game ends
                    setTimeout(() => {
                        router.visit(route('multiplayer-games.show', props.game.id));
                    }, 3000);
                } else if (e.event_type === 'answer_submitted' && e.additional_data) {
                    // Show opponent's action if it's not from current user
                    if (e.additional_data.player_id !== props.game.currentUser.id) {
                        showOpponentAction.value = true;
                        opponentFeedback.value = {
                            name: e.additional_data.player_name,
                            isCorrect: e.additional_data.is_correct,
                            answer: e.additional_data.answer_text,
                        };

                        if (soundEnabled.value) {
                            if (opponentFeedback.value.isCorrect) {
                                correctSfx.currentTime = 0;
                                correctSfx.play();
                            } else {
                                incorrectSfx.currentTime = 0;
                                incorrectSfx.play();
                            }
                        }

                        setTimeout(() => {
                            showOpponentAction.value = false;
                        }, 4000);
                    }
                } else if (e.event_type === 'timer_started') {
                    const duration = e.additional_data?.timer_duration || DEFAULT_TIMER_DURATION;
                    timer.value = duration;
                    timerDuration.value = duration;
                    timerRunning.value = true;
                    timedOut.value = false;
                    startTimerSync();
                } else if (e.event_type === 'timer_delayed') {
                    // Timer is delayed waiting for players to load
                    lastAction.value = { 
                        type: 'success', 
                        message: e.additional_data?.message || 'Waiting for players to load...' 
                    };
                } else if (e.event_type === 'timer_stopped') {
                    timerRunning.value = false;
                    stopTimerSync();
                } else if (e.event_type === 'timer_grace_period' && e.additional_data) {
                    // Grace period handled silently - no UI changes needed
                } else if (e.event_type === 'timer_warning' && e.additional_data) {
                    timer.value = e.additional_data.remaining_time;
                    // Play warning sound based on warning point
                    if (soundEnabled.value && e.additional_data.warning_point) {
                        if (e.additional_data.warning_point === 10) {
                            playWarningSound();
                        } else if (e.additional_data.warning_point === 5) {
                            playUrgentWarningSound();
                        } else if (e.additional_data.warning_point <= 3) {
                            playCountdownSound();
                        }
                    }
                } else if (e.event_type === 'timer_timeout' && e.additional_data) {
                    // Server handled timeout, update UI
                    stopTimerSync(); // Stop timer immediately
                    
                    if (e.additional_data.timed_out_player === props.game.currentUser.id) {
                        timedOut.value = true;
                        answerSubmitted.value = true; // Mark as submitted
                        submitting.value = false; // Stop submitting state
                        
                        // Play timeout sound
                        if (soundEnabled.value) {
                            incorrectSfx.currentTime = 0;
                            incorrectSfx.play();
                        }
                        
                        lastAction.value = { 
                            type: 'error', 
                            message: "⏰ Time's up! Your answer was automatically submitted as incorrect." 
                        };
                        
                        console.log('Player timed out - answer auto-submitted as wrong');
                    } else {
                        // Show opponent timeout message
                        lastAction.value = { 
                            type: 'error', 
                            message: `${e.additional_data.timed_out_player_name} timed out and lost their turn!` 
                        };
                        
                        console.log(`Opponent ${e.additional_data.timed_out_player_name} timed out`);
                    }
                    
                    // Clear timeout and reset for next question after showing message
                    setTimeout(() => {
                        timedOut.value = false;
                        lastAction.value = null;
                        resetForNextQuestion();
                    }, 4000); // Show message a bit longer for timeout
                }

                // Update game state from websocket - use gameState instead of props.game
                Object.assign(gameState.value, e.game);
                Object.assign(props.game, e.game); // Keep props in sync too for animations

                // Handle status-based navigation as a fallback
                if (gameState.value.status === 'active' && e.event_type === 'game_started') {
                    // Game just started, refresh to game screen
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else if (gameState.value.status === 'finished') {
                    gameOver.value = true;
                    // If we're not already on the results screen, navigate there
                    if (!window.location.pathname.includes('show')) {
                        setTimeout(() => {
                            router.visit(route('multiplayer-games.show', props.game.id));
                        }, 2000);
                    }
                }

                // Update the current question if it's included in the websocket data
                if (e.game.currentQuestion !== undefined) {
                    currentQuestion.value = e.game.currentQuestion;
                    console.log('Updated current question from websocket:', e.game.currentQuestion);
                }

                // Show accuracy animations if changed (using the updated values)
                if (gameState.value.player_one_accuracy !== previousPlayerOneAccuracy) {
                    accuracyAnimation.value = {
                        player: 'one',
                        change: (gameState.value.player_one_accuracy ?? 0) - (previousPlayerOneAccuracy ?? 0),
                    };
                    setTimeout(() => {
                        accuracyAnimation.value = null;
                    }, 2000);
                }

                if (gameState.value.player_two_accuracy !== previousPlayerTwoAccuracy) {
                    accuracyAnimation.value = {
                        player: 'two',
                        change: (gameState.value.player_two_accuracy ?? 0) - (previousPlayerTwoAccuracy ?? 0),
                    };
                    setTimeout(() => {
                        accuracyAnimation.value = null;
                    }, 2000);
                }

                // Show streak animations if changed (using the updated values)
                if (gameState.value.player_one_streak !== previousPlayerOneStreak) {
                    streakAnimation.value = {
                        player: 'one',
                        streak: gameState.value.player_one_streak ?? 0,
                    };
                    setTimeout(() => {
                        streakAnimation.value = null;
                    }, 2000);
                }

                if (gameState.value.player_two_streak !== previousPlayerTwoStreak) {
                    streakAnimation.value = {
                        player: 'two',
                        streak: gameState.value.player_two_streak ?? 0,
                    };
                    setTimeout(() => {
                        streakAnimation.value = null;
                    }, 2000);
                }

                // Handle damage feedback for current user's action
                if (e.additional_data && e.additional_data.player_id === gameState.value.currentUser.id) {
                    const damageDealt = e.additional_data.damage_dealt || 0;
                    const damageReceived = e.additional_data.damage_received || 0;
                    const isCorrect = e.additional_data.is_correct;
                    showFeedback(isCorrect, damageDealt, damageReceived);
                }

                // Check if game has finished
                if (gameState.value.status === 'finished') {
                    gameOver.value = true;
                    return;
                }

                // Reset submission state when we receive the update
                submitting.value = false;

                // Clear timeout response flag if we get any game update
                if (awaitingTimeoutResponse.value) {
                    awaitingTimeoutResponse.value = false;
                    console.log('Timeout response received via WebSocket');
                }

                // If it became my turn, reset for next question
                if (!wasMyTurn && isMyTurn.value) {
                    resetForNextQuestion();
                    console.log("It's now my turn, resetting for next question");
                }
                
                // If it's still my turn after a timeout (meaning the game didn't progress properly),
                // try to sync the game state after a short delay
                if (wasMyTurn && isMyTurn.value && timedOut.value) {
                    console.warn('Still my turn after timeout, game may not have progressed properly');
                    setTimeout(() => {
                        if (isMyTurn.value && timedOut.value) {
                            console.warn('Syncing game state - game stuck after timeout');
                            fetchFreshGameState();
                        }
                    }, 3000);
                }
            })
            .listenForWhisper('answer-submitted', (e: any) => {
                console.log('Answer whisper received from opponent:', e);
                // The server will process this and broadcast back as MultiplayerGameUpdated
            })
            .error((error: any) => {
                console.error('WebSocket error:', error);
                lastAction.value = { type: 'error', message: 'Connection lost. Trying to reconnect...' };
                
                // If WebSocket fails, increase the frequency of state checks as a fallback
                stopStateCheckInterval();
                stateCheckInterval = window.setInterval(() => {
                    if (gameState.value.status === 'active' && !gameOver.value) {
                        console.log('WebSocket down, using fallback state check...');
                        fetchFreshGameState();
                    }
                }, 10000); // Check every 10 seconds when WebSocket is down
            });

        console.log('WebSocket connection established for game:', props.game.id);
    } else {
        console.error('Laravel Echo not available');
        lastAction.value = { type: 'error', message: 'Real-time connection not available' };
    }

    // Mark player as ready (page loaded)
    markPlayerReady();
    
    // Start timer sync immediately if game is active
    if (gameState.value?.status === 'active') {
        startTimerSync();
    }

    // Start periodic state checking
    startStateCheckInterval();
});

// Play sfx for game start animation
watch(gameStartAnimation, (val) => {
    if (val && soundEnabled.value) {
        gameStartSfx.currentTime = 0;
        gameStartSfx.play();
    }
});

// Play sfx for game end animation
watch(gameEndAnimation, (val) => {
    if (val && soundEnabled.value) {
        gameEndSfx.currentTime = 0;
        gameEndSfx.play();
    }
});

// Play sfx when player's turn starts
watch(isMyTurn, (val, oldVal) => {
    if (val && !oldVal && soundEnabled.value) {
        turnStartSfx.currentTime = 0;
        turnStartSfx.play();
    }
});

// Play sfx for streaks (3+)
watch(streakAnimation, (val) => {
    if (val && val.streak >= 3) {
        streakSfx.currentTime = 0;
        streakSfx.play();
    }
});

// Play sfx for victory/defeat
watch(gameOver, (val) => {
    if (val) {
        const result = getGameResult();
        if (result.includes('Victory')) {
            victorySfx.currentTime = 0;
            victorySfx.play();
        } else if (result.includes('Defeat')) {
            defeatSfx.currentTime = 0;
            defeatSfx.play();
        }
    }
});

// Play damage sfx when damage is dealt/taken
function playDamageSfx() {
    damageSfx.currentTime = 0;
    damageSfx.play();
}

// Play warning sounds for timer
function playWarningSound() {
    if (soundEnabled.value) {
        warningSfx.currentTime = 0;
        warningSfx.play().catch(() => {}); // Fallback gracefully if audio fails
    }
}

function playUrgentWarningSound() {
    if (soundEnabled.value) {
        urgentWarningSfx.currentTime = 0;
        urgentWarningSfx.play().catch(() => {}); // Fallback gracefully if audio fails
    }
}

function playCountdownSound() {
    if (soundEnabled.value) {
        countdownSfx.currentTime = 0;
        countdownSfx.play().catch(() => {}); // Fallback gracefully if audio fails
    }
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active in <2.1.8 */ {
    opacity: 0;
}
</style>
