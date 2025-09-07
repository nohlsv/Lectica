<template>
    <Head title="Multiplayer Game" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
                    {{ game.game_mode === 'pvp' ? 'PvP Battle' : `Battle vs ${game.monster?.name || 'Monster'}` }}
                </h2>
                <div class="flex items-center space-x-4">
                    <div v-if="game.game_mode === 'pve' && game.monster" class="flex items-center space-x-2 rounded-lg bg-red-100 px-3 py-1 dark:bg-red-900/20">
                        <img
                            :src="game.monster.image_path || '/images/default-monster.png'"
                            :alt="game.monster.name"
                            class="h-6 w-6 rounded-full object-cover"
                            @error="handleImageError"
                        />
                        <span class="text-sm font-medium text-red-800 dark:text-red-300">
                            {{ game.monster.name }}: {{ game.monster_hp }}❤️
                        </span>
                    </div>
                    <button
                        @click="abandonGame"
                        class="rounded-md bg-gray-600 px-3 py-1 text-sm text-white hover:bg-gray-700"
                    >
                        Abandon Game
                    </button>
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
                                    {{ game.playerOne.first_name.charAt(0) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ game.playerOne.first_name }} {{ game.playerOne.last_name }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-red-500">❤️ {{ game.player_one_hp }}</span>
                                    <span class="text-sm text-yellow-500">⭐ {{ game.player_one_score }}</span>
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
                                    {{ game.playerTwo.first_name }} {{ game.playerTwo.last_name }}
                                </p>
                                <div class="flex items-center justify-end space-x-2">
                                    <span class="text-sm text-yellow-500">⭐ {{ game.player_two_score }}</span>
                                    <span class="text-sm text-red-500">❤️ {{ game.player_two_hp }}</span>
                                </div>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                <span class="font-bold text-green-800 dark:text-green-300">
                                    {{ game.playerTwo.first_name.charAt(0) }}
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

                    <!-- Multiple Choice -->
                    <div v-if="currentQuiz.type === 'multiple_choice'" class="space-y-3">
                        <button
                            v-for="(option, index) in currentQuiz.options"
                            :key="index"
                            @click="selectAnswer(option)"
                            :disabled="answerSubmitted"
                            :class="[
                                'w-full rounded-lg border p-3 text-left transition-colors',
                                selectedAnswer === option
                                    ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                    : 'border-gray-300 hover:border-purple-300 dark:border-gray-600',
                                answerSubmitted && 'opacity-75 cursor-not-allowed'
                            ]"
                        >
                            {{ option }}
                        </button>
                    </div>

                    <!-- True/False -->
                    <div v-else-if="currentQuiz.type === 'true_false'" class="flex space-x-4">
                        <button
                            @click="selectAnswer('True')"
                            :disabled="answerSubmitted"
                            :class="[
                                'flex-1 rounded-lg border p-3 transition-colors',
                                selectedAnswer === 'True'
                                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                    : 'border-gray-300 hover:border-green-300 dark:border-gray-600',
                                answerSubmitted && 'opacity-75 cursor-not-allowed'
                            ]"
                        >
                            True
                        </button>
                        <button
                            @click="selectAnswer('False')"
                            :disabled="answerSubmitted"
                            :class="[
                                'flex-1 rounded-lg border p-3 transition-colors',
                                selectedAnswer === 'False'
                                    ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                                    : 'border-gray-300 hover:border-red-300 dark:border-gray-600',
                                answerSubmitted && 'opacity-75 cursor-not-allowed'
                            ]"
                        >
                            False
                        </button>
                    </div>

                    <!-- Enumeration -->
                    <div v-else-if="currentQuiz.type === 'enumeration'" class="space-y-3">
                        <textarea
                            v-model="selectedAnswer"
                            :disabled="answerSubmitted"
                            placeholder="Enter your answer..."
                            class="w-full rounded-lg border border-gray-300 p-3 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            rows="3"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="submitAnswer"
                            :disabled="!selectedAnswer || answerSubmitted || submitting"
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
                <div v-if="lastAction" class="mt-4 rounded-lg p-4" :class="lastAction.type === 'success' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
                    <div class="flex items-center">
                        <div v-if="lastAction.type === 'success'" class="mr-3 h-5 w-5 text-green-400">✓</div>
                        <div v-else class="mr-3 h-5 w-5 text-red-400">✗</div>
                        <p class="text-sm" :class="lastAction.type === 'success' ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300'">
                            {{ lastAction.message }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

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
const selectedAnswer = ref('');
const answerSubmitted = ref(false);
const submitting = ref(false);
const gameOver = ref(false);
const lastAction = ref<{ type: 'success' | 'error'; message: string } | null>(null);
const currentQuestion = ref(props.currentQuestion);

// Computed properties
const currentQuiz = computed(() => currentQuestion.value);
const isMyTurn = computed(() => {
    const isPlayerOne = props.game.currentUser.id === props.game.playerOne.id;
    return (isPlayerOne && props.game.current_turn === 1) || (!isPlayerOne && props.game.current_turn === 2);
});

// Methods
const selectAnswer = (answer: string) => {
    if (!answerSubmitted.value) {
        selectedAnswer.value = answer;
    }
};

const submitAnswer = async () => {
    if (!selectedAnswer.value || submitting.value) return;

    submitting.value = true;
    answerSubmitted.value = true;

    try {
        const isCorrect = checkAnswer(selectedAnswer.value, currentQuiz.value);

        // Use Inertia router for reliable answer submission
        router.post(route('multiplayer-games.answer', props.game.id), {
            quiz_id: currentQuiz.value?.id,
            answer: selectedAnswer.value,
            is_correct: isCorrect,
        }, {
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
                    lastAction.value = { type: 'error', message: 'It\'s not your turn! Please wait for your opponent.' };
                } else if (errors.game) {
                    lastAction.value = { type: 'error', message: errors.game };
                } else {
                    const errorMessage = Object.values(errors)[0] as string || 'Failed to submit answer';
                    lastAction.value = { type: 'error', message: errorMessage };
                }

                // Reset submission state on error
                answerSubmitted.value = false;
                submitting.value = false;
            }
        });
    } catch (error) {
        console.error('Error submitting answer:', error);
        lastAction.value = { type: 'error', message: 'Failed to submit answer. Please try again.' };

        // Reset submission state so user can try again
        answerSubmitted.value = false;
        submitting.value = false;
    }
};

const checkAnswer = (userAnswer: string, quiz: Quiz): boolean => {
    if (!quiz || !quiz.answers || quiz.answers.length === 0 || !userAnswer) {
        return false;
    }

    const userAnswerLower = userAnswer.toLowerCase().trim();

    if (quiz.type === 'multiple_choice' || quiz.type === 'true_false') {
        // For these types, the first element in answers array is the correct answer
        const correctAnswer = quiz.answers[0].toLowerCase().trim();
        return userAnswerLower === correctAnswer;
    } else if (quiz.type === 'enumeration') {
        // For enumeration, check if user answer contains any of the correct answers
        // Each answer in the array could be a valid response
        return quiz.answers.some(correctAnswer => {
            const correctLower = correctAnswer.toLowerCase().trim();
            return userAnswerLower.includes(correctLower) || correctLower.includes(userAnswerLower);
        });
    }

    return false;
};

const showFeedback = (isCorrect: boolean, damageDealt: number, damageReceived: number) => {
    if (isCorrect) {
        lastAction.value = {
            type: 'success',
            message: `Correct! You dealt ${damageDealt} damage!`
        };
    } else {
        lastAction.value = {
            type: 'error',
            message: `Wrong answer. You took ${damageReceived} damage.`
        };
    }

    // Clear feedback after 3 seconds
    setTimeout(() => {
        lastAction.value = null;
    }, 3000);
};

const resetForNextQuestion = () => {
    selectedAnswer.value = '';
    answerSubmitted.value = false;
};

const getGameResult = (): string => {
    if (props.game.game_mode === 'pvp') {
        const isPlayerOne = props.game.currentUser.id === props.game.playerOne.id;
        if (isPlayerOne) {
            return props.game.player_one_hp > 0 ? 'You won!' : 'You lost!';
        } else {
            return props.game.player_two_hp > 0 ? 'You won!' : 'You lost!';
        }
    } else {
        if (props.game.monster_hp <= 0) {
            return 'Victory! You defeated the monster together!';
        } else {
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

                // Store previous turn state
                const wasMyTurn = isMyTurn.value;

                // Update game state from websocket
                Object.assign(props.game, e.game);

                // Update the current question if it's included in the websocket data
                if (e.game.currentQuestion !== undefined) {
                    currentQuestion.value = e.game.currentQuestion;
                    console.log('Updated current question from websocket:', e.game.currentQuestion);
                }

                // Update feedback with damage information if available
                if (e.damage_dealt !== undefined || e.damage_received !== undefined) {
                    const damageDealt = e.damage_dealt || 0;
                    const damageReceived = e.damage_received || 0;
                    const isCorrect = damageDealt > 0 || damageReceived === 0;
                    showFeedback(isCorrect, damageDealt, damageReceived);
                }

                if (props.game.status === 'finished') {
                    gameOver.value = true;
                    return;
                }

                // Reset submission state when we receive the update
                submitting.value = false;

                // If it became my turn, reset for next question
                if (!wasMyTurn && isMyTurn.value) {
                    resetForNextQuestion();
                    console.log('It\'s now my turn, resetting for next question');
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
});

onUnmounted(() => {
    if (echo) {
        echo.stopListening('MultiplayerGameUpdated');
        echo.stopListening('.whisper:answer-submitted');
        console.log('WebSocket listeners cleaned up');
    }
});
</script>
