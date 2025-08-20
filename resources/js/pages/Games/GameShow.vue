<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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

const props = defineProps<{ game: Game, playerOne: any, playerTwo: any }>();
const user = (usePage().props.auth as any).user;
const answer = ref<string>('');
const submitting = ref(false);
const gameState = ref<Game>(props.game);


window.Echo.channel('game.' + gameState.value.id)
  .listen('MultiplayerGameUpdated', (e: any) => {
    const wasWaiting = !gameState.value.player_two_id && !!e.game.player_two_id;
    gameState.value = e.game;
    if (wasWaiting) {
      toast.success('Player Two has joined!');
      startGame(); // Start the game when player two joins
    }
  });

const currentQuestion = computed(() => (gameState.value.questions && gameState.value.questions.length > 0) ? gameState.value.questions[0] : null);
const isMyTurn = computed(() => gameState.value.current_turn === user.id);
const isFinished = computed(() => {
  return gameState.value.status === 'finished';
});

watch(() => gameState.value.questions, (questions) => {
  // Only finish if the game has started and there are no more questions
  if (gameStarted.value && (!questions || questions.length === 0)) {
    if (gameState.value.status !== 'finished') {
      gameState.value.status = 'finished';
      gameState.value.game_end_reason = 'no_more_questions';
      router.post(`/games/${gameState.value.id}/finish`, {
        status: 'finished',
        game_end_reason: 'no_more_questions',
      }, {
        preserveScroll: true,
        onSuccess: () => toast.success('MultiplayerGame finished: No more questions.'),
        onError: () => toast.error('Failed to update game status.'),
      });
    }
  }
});

const winner = computed(() => {
  if (gameState.value.player_one_score >= 5) return props.playerOne;
  if (gameState.value.player_two_score >= 5) return props.playerTwo;
  return null;
});

let gameStarted = computed(() => !!gameState.value.questions && gameState.value.questions.length > 0);
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
  router.post(`/games/${gameState.value.id}/answer`, {
    player_id: user.id,
    answer: answer.value,
  }, {
    onSuccess: () => { answer.value = ''; },
    onFinish: () => { submitting.value = false; },
    preserveScroll: true,
  });
}

function startGame() {
  router.post(`/games/${gameState.value.id}/start`, {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props?.game?.game_end_reason === 'no_quizzes_found' || page.props?.error === 'No quizzes available for either player.') {
        gameState.value = page.props.game;
        toast.error(
          (page.props?.error || 'No quizzes available for either player.') +
          '\n' +
          (page.props?.suggestion || 'Add starred files with quizzes to your account and try again.')
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
          (errors.response.data.suggestion || 'Add starred files with quizzes to your account and try again.')
        );
      } else {
        toast.error('Failed to start game.');
      }
    },
  });
}

function returnToLobby() {
  // Finish the game before returning to lobby
  router.post(`/games/${gameState.value.id}/finish`, {
    status: 'finished',
    game_end_reason: 'quit',
  }, {
    preserveScroll: true,
    onFinish: () => router.visit('/games/lobby'),
  });
}
</script>

<template>
  <Head :title="`Game #${gameState.id}`" />
  <AppLayout>
    <div class="max-w-2xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-6">Quiz Game</h1>
      <div v-if="gamePhase === 'waiting'" class="text-center text-lg text-muted py-8">
        Waiting for another player to join...
        <br>
        <button class="mt-6 px-4 py-2 bg-background text-foreground rounded" @click="returnToLobby">Return to Lobby</button>
      </div>
      <div v-else-if="gamePhase === 'finished'" class="text-xl font-bold text-center text-primary">
        <template v-if="gameState.game_end_reason === 'score_limit'">
          Game Over! Winner: {{ winner?.first_name }} {{ winner?.last_name }} (Score limit reached)
        </template>
        <template v-else-if="gameState.game_end_reason === 'no_more_questions'">
          Game Over! No more questions left.<br>
          Final scores: {{ props.playerOne?.first_name }} {{ props.playerOne?.last_name }} ({{ gameState.player_one_score }}) vs {{ props.playerTwo?.first_name }} {{ props.playerTwo?.last_name }} ({{ gameState.player_two_score }})
          <br>
          <span v-if="gameState.player_one_score > gameState.player_two_score">Winner: {{ props.playerOne?.first_name }} {{ props.playerOne?.last_name }}</span>
          <span v-else-if="gameState.player_two_score > gameState.player_one_score">Winner: {{ props.playerTwo?.first_name }} {{ props.playerTwo?.last_name }}</span>
          <span v-else>Tie!</span>
        </template>
        <template v-else>
          Game Over!
        </template>
        <button class="mt-6 px-4 py-2 accent-background text-foreground rounded" @click="returnToLobby">Return to Lobby</button>
      </div>
      <div v-else-if="gamePhase === 'playing'">
        <div class="flex items-center gap-2 mb-4">
          <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-lg font-bold">
            {{ getInitials(props.playerOne) }}
          </div>
          <span>{{ props.playerOne?.first_name }} {{ props.playerOne?.last_name }}</span>
          <span class="ml-2 font-semibold">Score: {{ gameState.player_one_score }}</span>
          <span v-if="gameState.current_turn === props.playerOne?.id" class="ml-2 px-2 py-1 bg-primary text-foreground rounded text-xs">Current Turn</span>
        </div>
        <div class="flex items-center gap-2 mb-4">
          <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-lg font-bold">
            {{ getInitials(props.playerTwo) }}
          </div>
          <span>{{ props.playerTwo?.first_name }} {{ props.playerTwo?.last_name }}</span>
          <span class="ml-2 font-semibold">Score: {{ gameState.player_two_score }}</span>
          <span v-if="gameState.current_turn === props.playerTwo?.id" class="ml-2 px-2 py-1 bg-primary text-foreground rounded text-xs">Current Turn</span>
        </div>
        <div v-if="currentQuestion">
          <div class="mb-2 font-semibold">Question:</div>
          <div class="mb-4">{{ currentQuestion.question }}</div>
          <div class="mb-2">Options:</div>
          <div class="mb-4 flex flex-col gap-2">
            <button v-for="opt in currentQuestion.options" :key="opt" @click="answer = opt" :disabled="!isMyTurn || submitting" :class="['px-4 py-2 rounded border', answer === opt ? 'bg-primary text-foreground' : 'bg-card']">
              {{ opt }}
            </button>
          </div>
          <button @click="submitAnswer" :disabled="!isMyTurn || submitting || !answer" class="mt-2 px-4 py-2 bg-primary text-foreground rounded">Submit Answer</button>
          <div v-if="!isMyTurn" class="mt-4 text-muted-foreground">Waiting for opponent's turn...</div>
        </div>
        <div v-else class="text-center text-muted-foreground">No more questions left.</div>
      </div>
    </div>
  </AppLayout>
</template>
