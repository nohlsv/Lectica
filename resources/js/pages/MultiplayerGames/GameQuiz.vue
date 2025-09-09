<template>
    <Head title="Multiplayer Game" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
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
                    <button @click="abandonGame" class="rounded-md bg-gray-600 px-3 py-1 text-sm text-white hover:bg-gray-700">Abandon Game</button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Game Status Bar -->
                <div class="mb-6 rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <!-- Player 1 -->
                        <div class="flex items-center space-x-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                                <span class="font-bold text-blue-800 dark:text-blue-300">
                                    {{ gameState.playerOne.first_name.charAt(0) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ gameState.playerOne.first_name }} {{ gameState.playerOne.last_name }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    <!-- PVP Mode: Show Accuracy and Streak -->
                                    <template v-if="gameState.game_mode === 'pvp'">
                                        <span class="text-sm text-blue-500">🎯 {{ gameState.player_one_accuracy || 0 }}%</span>
                                        <span class="text-sm text-purple-500">🔥 {{ gameState.player_one_streak || 0 }}</span>
                                        <span class="text-sm text-yellow-500">⭐ {{ gameState.player_one_score }}</span>
                                    </template>
                                    <!-- PVE Mode: Show HP and Score -->
                                    <template v-else>
                                        <span class="text-sm text-red-500">❤️ {{ gameState.player_one_hp }}</span>
                                        <span class="text-sm text-yellow-500">⭐ {{ gameState.player_one_score }}</span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Turn Indicator -->
                        <div class="text-center">
                            <div v-if="isMyTurn" class="rounded-full bg-green-100 px-4 py-2 dark:bg-green-900/20">
                                <p class="font-medium text-green-800 dark:text-green-300">Your Turn!</p>
                            </div>
                            <div v-else class="rounded-full bg-gray-100 px-4 py-2 dark:bg-gray-700">
                                <p class="text-gray-600 dark:text-gray-400">Opponent's Turn</p>
                            </div>
                        </div>

                        <!-- Player 2 -->
                        <div class="flex items-center space-x-3">
                            <div>
                                <p class="text-right font-medium text-gray-900 dark:text-gray-100">
                                    {{ gameState.playerTwo.first_name }} {{ gameState.playerTwo.last_name }}
                                </p>
                                <div class="flex items-center justify-end space-x-2">
                                    <!-- PVP Mode: Show Accuracy and Streak -->
                                    <template v-if="gameState.game_mode === 'pvp'">
                                        <span class="text-sm text-yellow-500">⭐ {{ gameState.player_two_score }}</span>
                                        <span class="text-sm text-purple-500">🔥 {{ gameState.player_two_streak || 0 }}</span>
                                        <span class="text-sm text-blue-500">🎯 {{ gameState.player_two_accuracy || 0 }}%</span>
                                    </template>
                                    <!-- PVE Mode: Show Score and HP -->
                                    <template v-else>
                                        <span class="text-sm text-yellow-500">⭐ {{ gameState.player_two_score }}</span>
                                        <span class="text-sm text-red-500">❤️ {{ gameState.player_two_hp }}</span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <span class="font-bold text-green-800 dark:text-green-300">
                                    {{ gameState.playerTwo.first_name.charAt(0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Question -->
                <div v-if="currentQuiz && isMyTurn" class="mb-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <div class="mb-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ quizTypes[currentQuiz.type] || 'Question' }}</span>
                            <span class="text-sm text-gray-500">Source: {{ game.source_name }}</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ currentQuiz.question }}
                        </h3>
                    </div>

                    <!-- Timer Constraint -->
                    <div class="mb-4 flex items-center justify-between">
                        <span class="text-sm font-semibold text-purple-600">
                            Time left: {{ timer }}s
                        </span>
                        <span v-if="timer === 0" class="text-sm text-red-500">Time's up!</span>
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
                                    ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                    : 'border-gray-300 hover:border-purple-300 dark:border-gray-600',
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
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-300 hover:border-green-300 dark:border-gray-600',
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
                                    ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                                    : 'border-gray-300 hover:border-red-300 dark:border-gray-600',
                                (answerSubmitted || timedOut) && 'cursor-not-allowed opacity-75',
                            ]"
                        >
                            False
                        </button>
                    </div>

                    <!-- Enumeration -->
                    <div v-else-if="currentQuiz.type === 'enumeration'" class="space-y-3">
                        <p class="font-bold text-purple-700 mb-2">Please provide {{ currentQuiz.answers.length }} answers:</p>
                        <div v-for="idx in currentQuiz.answers.length" :key="idx" class="mb-2">
                            <input
                                type="text"
                                :placeholder="`Answer ${idx}`"
                                v-model="selectedAnswer[idx - 1]"
                                @input="updateEnumerationAnswer(idx - 1, selectedAnswer[idx - 1])"
                                :disabled="answerSubmitted || timedOut"
                                class="w-full rounded-lg border border-gray-300 p-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="submitAnswer"
                            :disabled="!selectedAnswer || answerSubmitted || submitting || timedOut"
                            class="inline-flex items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <span v-if="submitting" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ submitting ? 'Submitting...' : 'Submit Answer' }}
                        </button>
                    </div>
                </div>

                <!-- Waiting for Opponent -->
                <div v-else-if="!isMyTurn" class="rounded-lg bg-gray-50 p-8 text-center dark:bg-gray-900">
                    <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-purple-200 border-t-purple-600"></div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Waiting for opponent...</h3>
                    <p class="text-gray-500">Your opponent is answering their question.</p>
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
                        }}</span
                        >: "{{ opponentFeedback?.answer }}"
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
import { computed, onMounted, ref, watch, onUnmounted } from 'vue';

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
    // Add accuracy tracking fields
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

// Timer constraint
const TIMER_DURATION = 30; // seconds per question
const timer = ref(TIMER_DURATION);
let timerInterval: number | undefined;
const timedOut = ref(false);

// Computed properties
const currentQuiz = computed(() => currentQuestion.value);
const isMyTurn = computed(() => {
    const isPlayerOne = gameState.value.currentUser.id === gameState.value.playerOne.id;
    return (isPlayerOne && gameState.value.current_turn === 1) || (!isPlayerOne && gameState.value.current_turn === 2);
});

// Watch for turn changes to start/reset timer
watch(isMyTurn, (newVal) => {
    if (newVal) {
        startTimer();
    } else {
        stopTimer();
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

const submitAnswer = async () => {
    if (!currentQuiz.value) return;
    if (
        (currentQuiz.value.type === 'enumeration' && Array.isArray(selectedAnswer.value) && selectedAnswer.value.every((a) => !a)) ||
        (!selectedAnswer.value && currentQuiz.value.type !== 'enumeration') ||
        submitting.value ||
        timedOut.value
    ) return;

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

                    // Reset submission state on error
                    answerSubmitted.value = false;
                    submitting.value = false;
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

const showFeedback = (isCorrect: boolean, damageDealt: number, damageReceived: number) => {
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
            lastAction.value = {
                type: 'success',
                message: `Correct! You dealt ${damageDealt} damage!`,
            };
        } else {
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

const startTimer = () => {
    timer.value = TIMER_DURATION;
    timedOut.value = false;
    stopTimer();
    timerInterval = window.setInterval(() => {
        timer.value = Math.max(timer.value - 1, 0);
        if (timer.value <= 0) {
            stopTimer();
            handleTimeout();
        }
    }, 1000);
};

const stopTimer = () => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = undefined;
    }
};

const handleTimeout = () => {
    if (!answerSubmitted.value && isMyTurn.value) {
        timedOut.value = true;
        // Ensure selectedAnswer is set to an empty value appropriate for the quiz type
        if (currentQuiz.value?.type === 'enumeration') {
            selectedAnswer.value = Array(currentQuiz.value.answers.length).fill('');
        } else {
            selectedAnswer.value = '';
        }
        submitAnswer();
        lastAction.value = { type: 'error', message: "Time's up! Answer auto-submitted." };
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
    timer.value = TIMER_DURATION;
    stopTimer();
    if (isMyTurn.value) {
        startTimer();
    }
};

// Watch for question change to reset enumeration fields
watch(currentQuiz, (newQuiz) => {
    if (newQuiz && newQuiz.type === 'enumeration') {
        if (!Array.isArray(selectedAnswer.value) || selectedAnswer.value.length !== newQuiz.answers.length) {
            selectedAnswer.value = Array(newQuiz.answers.length).fill('');
        }
    }
});

// Clean up timer on unmount
onUnmounted(() => {
    stopTimer();
});

const getGameResult = (): string => {
    if (gameState.value.game_mode === 'pvp') {
        const isPlayerOne = gameState.value.currentUser.id === gameState.value.playerOne.id;
        const myAccuracy = isPlayerOne ? gameState.value.player_one_accuracy || 0 : gameState.value.player_two_accuracy || 0;
        const opponentAccuracy = isPlayerOne ? gameState.value.player_two_accuracy || 0 : gameState.value.player_one_accuracy || 0;
            const myAccuracy = isPlayerOne ? (gameState.value.player_one_accuracy ?? 0) : (gameState.value.player_two_accuracy ?? 0);
            const opponentAccuracy = isPlayerOne ? (gameState.value.player_two_accuracy ?? 0) : (gameState.value.player_one_accuracy ?? 0);
        console.log('Game result calculation:', {
            isPlayerOne,
            myAccuracy,
            opponentAccuracy,
            player_one_accuracy: gameState.value.player_one_accuracy,
            player_two_accuracy: gameState.value.player_two_accuracy,
        });

        if (myAccuracy > opponentAccuracy) {
            return `Victory! 🎯 Your accuracy: ${myAccuracy}% vs Opponent: ${opponentAccuracy}%`;
        } else if (myAccuracy < opponentAccuracy) {
            return `Defeat! 📉 Your accuracy: ${myAccuracy}% vs Opponent: ${opponentAccuracy}%`;
            const myHP = isPlayerOne ? (gameState.value.player_one_hp ?? 0) : (gameState.value.player_two_hp ?? 0);
            const opponentHP = isPlayerOne ? (gameState.value.player_two_hp ?? 0) : (gameState.value.player_one_hp ?? 0);
            if (myAccuracy === 0 && opponentAccuracy === 0) {
                return `No answers recorded! 🤔 The game ended unexpectedly.`;
            } else {
                return `Tie! 🤝 Both players achieved ${myAccuracy}% accuracy`;
            }
        }
    } else {
        // PVE Mode: Original HP-based results
        if (gameState.value.monster_hp <= 0) {
        if ((gameState.value.monster_hp ?? 0) <= 0) {
            return 'Defeat! The monster was too strong.';
        }
    }
};

const abandonGame = () => {
    if (confirm('Are you sure you want to abandon this game?')) {
        router.post(route('multiplayer-games.abandon', props.game.id));
    }
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/default-monster.png';
};

// WebSocket listener for real-time updates
let echo: any;

onMounted(() => {
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

                        setTimeout(() => {
                            showOpponentAction.value = false;
                        }, 4000);
                    }
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
                if ((gameState.value.player_one_accuracy ?? 0) !== (previousPlayerOneAccuracy ?? 0)) {
                        change: gameState.value.player_one_accuracy - (previousPlayerOneAccuracy || 0),
                    };
                        change: (gameState.value.player_one_accuracy ?? 0) - (previousPlayerOneAccuracy ?? 0),
                        accuracyAnimation.value = null;
                    }, 2000);
                }

                if (gameState.value.player_two_accuracy !== previousPlayerTwoAccuracy) {
                    accuracyAnimation.value = {
                if ((gameState.value.player_two_accuracy ?? 0) !== (previousPlayerTwoAccuracy ?? 0)) {
                        change: gameState.value.player_two_accuracy - (previousPlayerTwoAccuracy || 0),
                    };
                        change: (gameState.value.player_two_accuracy ?? 0) - (previousPlayerTwoAccuracy ?? 0),
                        accuracyAnimation.value = null;
                    }, 2000);
                }

                // Show streak animations if changed (using the updated values)
                if (gameState.value.player_one_streak !== previousPlayerOneStreak) {
                    streakAnimation.value = {
                if ((gameState.value.player_one_streak ?? 0) !== (previousPlayerOneStreak ?? 0)) {
                        streak: gameState.value.player_one_streak,
                    };
                        streak: gameState.value.player_one_streak ?? 0,
                        streakAnimation.value = null;
                    }, 2000);
                }

                if (gameState.value.player_two_streak !== previousPlayerTwoStreak) {
                    streakAnimation.value = {
                if ((gameState.value.player_two_streak ?? 0) !== (previousPlayerTwoStreak ?? 0)) {
                        streak: gameState.value.player_two_streak,
                    };
                        streak: gameState.value.player_two_streak ?? 0,
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

                // If it became my turn, reset for next question
                if (!wasMyTurn && isMyTurn.value) {
                    resetForNextQuestion();
                    console.log("It's now my turn, resetting for next question");
                }
            })
            .listenForWhisper('answer-submitted', (e: any) => {
                console.log('Answer whisper received from opponent:', e);
                // The server will process this and broadcast back as MultiplayerGameUpdated
            })
            .error((error: any) => {
                console.error('WebSocket error:', error);
                lastAction.value = { type: 'error', message: 'Connection lost. Trying to reconnect...' };
            });

        console.log('WebSocket connection established for game:', props.game.id);
    } else {
        console.error('Laravel Echo not available');
        lastAction.value = { type: 'error', message: 'Real-time connection not available' };
    }

    // Start timer immediately if it's my turn on mount (host first question)
    if (isMyTurn.value) {
        startTimer();
    }
});
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
