<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface GameQuestion {
    quiz_id: number;
    question: string;
    options: string[];
    answers: string[];
    chosen_by: number;
}

interface Game {
    id: number;
    player_one_id: number;
    player_two_id: number;
    player_one_score: number;
    player_two_score: number;
    current_turn: number;
    questions: GameQuestion[];
    status: string;
    game_end_reason?: string;
}

const props = defineProps<{ game: Game; playerOne: any; playerTwo: any }>();
const user = (usePage().props.auth as any).user;
const answer = ref<string>('');
const submitting = ref(false);
const gameState = ref<Game>(props.game);

window.Echo.channel('game.' + gameState.value.id).listen('MultiplayerGameUpdated', (e: any) => {
    const wasWaiting = !gameState.value.player_two_id && !!e.game.player_two_id;
    gameState.value = e.game;
    if (wasWaiting) {
        toast.success('Player Two has joined!');
        startGame(); // Start the game when player two joins
    }
});

const currentQuestion = computed(() => (gameState.value.questions && gameState.value.questions.length > 0 ? gameState.value.questions[0] : null));
const isMyTurn = computed(() => gameState.value.current_turn === user.id);
const isFinished = computed(() => {
    return gameState.value.status === 'finished';
});

watch(
    () => gameState.value.questions,
    (questions) => {
        // Only finish if the game has started and there are no more questions
        if (gameStarted.value && (!questions || questions.length === 0)) {
            if (gameState.value.status !== 'finished') {
                gameState.value.status = 'finished';
                gameState.value.game_end_reason = 'no_more_questions';
                router.post(
                    route('games.finish', gameState.value.id),
                    {
                        status: 'finished',
                        game_end_reason: 'no_more_questions',
                    },
                    {
                        preserveScroll: true,
                        onSuccess: () => toast.success('MultiplayerGame finished: No more questions.'),
                        onError: () => toast.error('Failed to update game status.'),
                    },
                );
            }
        }
    },
);

const winner = computed(() => {
    if (gameState.value.player_one_score >= 5) return props.playerOne;
    if (gameState.value.player_two_score >= 5) return props.playerTwo;
    return null;
});

const gameStarted = computed(() => !!gameState.value.questions && gameState.value.questions.length > 0);
const gamePhase = computed(() => {
    if (!gameState.value.player_two_id) return 'waiting';
    if (gameState.value.status === 'finished') return 'finished';
    return 'playing';
});

function getInitials(player: any) {
    if (!player) return '';
    return `${player.first_name?.[0] ?? ''}${player.last_name?.[0] ?? ''}`.toUpperCase();
}

function submitAnswer() {
    if (!answer.value) return;
    submitting.value = true;
    router.post(
        route('games.answer', gameState.value.id),
        {
            player_id: user.id,
            answer: answer.value,
        },
        {
            onSuccess: () => {
                answer.value = '';
            },
            onFinish: () => {
                submitting.value = false;
            },
            preserveScroll: true,
        },
    );
}

function startGame() {
    router.post(
        route('games.start', gameState.value.id),
        {},
        {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props?.game?.game_end_reason === 'no_quizzes_found' || page.props?.error === 'No quizzes available for either player.') {
                    gameState.value = page.props.game;
                    toast.error(
                        (page.props?.error || 'No quizzes available for either player.') +
                            '\n' +
                            (page.props?.suggestion || 'Add starred files with quizzes to your account and try again.'),
                    );
                } else {
                    toast.success('MultiplayerGame started!');
                }
            },
            onError: (errors) => {
                if (errors?.response?.data?.error === 'No quizzes available for either player.') {
                    gameState.value = errors.response.data.game;
                    toast.error(
                        errors.response.data.error +
                            '\n' +
                            (errors.response.data.suggestion || 'Add starred files with quizzes to your account and try again.'),
                    );
                } else {
                    toast.error('Failed to start game.');
                }
            },
        },
    );
}

function returnToLobby() {
    // Finish the game before returning to lobby
    router.post(
        route('games.finish', gameState.value.id),
        {
            status: 'finished',
            game_end_reason: 'quit',
        },
        {
            preserveScroll: true,
            onFinish: () => router.visit(route('multiplayer-games.lobby')),
        },
    );
}

function forfeitGame() {
    if (confirm('Are you sure you want to forfeit this game? Your opponent will win.')) {
        router.post(route('games.forfeit', gameState.value.id), {}, {
            onSuccess: () => {
                toast.success('Game forfeited successfully.');
            },
            onError: () => {
                toast.error('Failed to forfeit game.');
            },
        });
    }
}
</script>

<template>
    <Head :title="`Game #${gameState.id}`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Quiz Game #{{ gameState.id }}</h2>
                <div v-if="gamePhase === 'playing'" class="flex items-center space-x-4">
                    <button @click="forfeitGame" class="rounded-md bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700">Forfeit</button>
                </div>
            </div>
        </template>
        
        <div class="mx-auto max-w-2xl p-6">
            <div v-if="gamePhase === 'waiting'" class="text-muted py-8 text-center text-lg">
                Waiting for another player to join...
                <br />
                <button class="bg-background text-foreground mt-6 rounded px-4 py-2" @click="returnToLobby">Return to Lobby</button>
            </div>
            <div v-else-if="gamePhase === 'finished'" class="text-primary text-center text-xl font-bold">
                <template v-if="gameState.game_end_reason === 'score_limit'">
                    Game Over! Winner: {{ winner?.first_name }} {{ winner?.last_name }} (Score limit reached)
                </template>
                <template v-else-if="gameState.game_end_reason === 'no_more_questions'">
                    Game Over! No more questions left.<br />
                    Final scores: {{ props.playerOne?.first_name }} {{ props.playerOne?.last_name }} ({{ gameState.player_one_score }}) vs
                    {{ props.playerTwo?.first_name }} {{ props.playerTwo?.last_name }} ({{ gameState.player_two_score }})
                    <br />
                    <span v-if="gameState.player_one_score > gameState.player_two_score"
                        >Winner: {{ props.playerOne?.first_name }} {{ props.playerOne?.last_name }}</span
                    >
                    <span v-else-if="gameState.player_two_score > gameState.player_one_score"
                        >Winner: {{ props.playerTwo?.first_name }} {{ props.playerTwo?.last_name }}</span
                    >
                    <span v-else>Tie!</span>
                </template>
                <template v-else> Game Over! </template>
                <button class="accent-background text-foreground mt-6 rounded px-4 py-2" @click="returnToLobby">Return to Lobby</button>
            </div>
            <div v-else-if="gamePhase === 'playing'">
                <div class="mb-4 flex items-center gap-2">
                    <div class="bg-primary/10 flex h-10 w-10 items-center justify-center rounded-full text-lg font-bold">
                        {{ getInitials(props.playerOne) }}
                    </div>
                    <span>{{ props.playerOne?.first_name }} {{ props.playerOne?.last_name }}</span>
                    <span class="ml-2 font-semibold">Score: {{ gameState.player_one_score }}</span>
                    <span v-if="gameState.current_turn === props.playerOne?.id" class="bg-primary text-foreground ml-2 rounded px-2 py-1 text-xs"
                        >Current Turn</span
                    >
                </div>
                <div class="mb-4 flex items-center gap-2">
                    <div class="bg-primary/10 flex h-10 w-10 items-center justify-center rounded-full text-lg font-bold">
                        {{ getInitials(props.playerTwo) }}
                    </div>
                    <span>{{ props.playerTwo?.first_name }} {{ props.playerTwo?.last_name }}</span>
                    <span class="ml-2 font-semibold">Score: {{ gameState.player_two_score }}</span>
                    <span v-if="gameState.current_turn === props.playerTwo?.id" class="bg-primary text-foreground ml-2 rounded px-2 py-1 text-xs"
                        >Current Turn</span
                    >
                </div>
                <div v-if="currentQuestion">
                    <div class="mb-2 font-semibold">Question:</div>
                    <div class="mb-4">{{ currentQuestion.question }}</div>
                    <div class="mb-2">Options:</div>
                    <div class="mb-4 flex flex-col gap-2">
                        <button
                            v-for="opt in currentQuestion.options"
                            :key="opt"
                            @click="answer = opt"
                            :disabled="!isMyTurn || submitting"
                            :class="['rounded border px-4 py-2', answer === opt ? 'bg-primary text-foreground' : 'bg-card']"
                        >
                            {{ opt }}
                        </button>
                    </div>
                    <button
                        @click="submitAnswer"
                        :disabled="!isMyTurn || submitting || !answer"
                        class="bg-primary text-foreground mt-2 rounded px-4 py-2"
                    >
                        Submit Answer
                    </button>
                    <div v-if="!isMyTurn" class="text-muted-foreground mt-4">Waiting for opponent's turn...</div>
                </div>
                <div v-else class="text-muted-foreground text-center">No more questions left.</div>
            </div>
        </div>
    </AppLayout>
</template>
