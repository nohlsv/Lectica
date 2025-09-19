<template>
    <Head title="Game Results" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Game Results</h2>
                <div class="flex items-center space-x-4">
                    <Link :href="route('multiplayer-games.lobby')" class="rounded-md bg-purple-600 px-3 py-1 text-sm text-white hover:bg-purple-700">
                        New Game
                    </Link>
                    <Link :href="route('multiplayer-games.lobby')" class="rounded-md bg-gray-600 px-3 py-1 text-sm text-white hover:bg-gray-700">
                        Back to Lobby
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Game Status Banner -->
                <div class="mb-6 mx-4 overflow-hidden rounded-lg shadow-sm" :class="getGameResultBanner()">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="mb-4">
                                <div
                                    v-if="getGameResult().type === 'victory'"
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-black/50 border-2 border-green-500"
                                >
                                    <svg class="h-8 w-8 text-green-600 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        ></path>
                                    </svg>
                                </div>
                                <div
                                    v-else-if="getGameResult().type === 'defeat'"
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-black/50 border-2 border-red-500"
                                >
                                    <svg class="h-8 w-8 text-red-600 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div v-else class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-black/50 border-2 border-blue-500">
                                    <svg class="h-8 w-8 text-blue-600 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        ></path>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="mb-2 text-2xl font-bold pixel-outline" :class="getGameResult().textColor">
                                {{ getGameResult().title }}
                            </h3>
                            <p class="text-lg pixel-outline" :class="getGameResult().textColor">
                                {{ getGameResult().description }}
                            </p>
                        </div>
                    </div>
                </div>
            
                <!-- Game Info -->
                <div class="mb-6 mx-4 p-6 grid grid-cols-1 gap-6 border-2 border-indigo-500 rounded-lg" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://copilot.microsoft.com/th/id/BCO.ae604036-caed-42e3-b47b-176397eb9693.png'); background-size: cover; background-position: center;">
                    <!-- Game Details -->
                    <div class="rounded-lg bg-black/50 border-2 border-indigo-500 p-6 shadow-sm">
                        <div class="p-4 mb-4 -mx-6.5 items-center bg-black/50 border-2 border-indigo-500">
                            <h4 class="text-lg font-medium text-gray-100 pixel-outline text-center">Game Details</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline mr-4 sm:mr-0">Game Mode:</span>
                                <span class="font-medium text-gray-100 pixel-outline">
                                    {{ game.game_mode === 'pvp' ? 'PvP (Player vs Player)' : 'PvE (Co-op vs Monster)' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline mr-4 sm:mr-0">Study Material:</span>
                                <span class="font-medium text-gray-100 pixel-outline">{{ source_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Duration:</span>
                                <span class="font-medium text-gray-100 pixel-outline">{{ getGameDuration() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Status:</span>
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium pixel-outline" :class="getStatusBadgeClass()">
                                    {{ getStatusLabel() }}
                                </span>
                            </div>
                            <div v-if="game.game_mode === 'pvp'" class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Win Condition:</span>
                                <span class="font-medium text-gray-100 pixel-outline">
                                    {{ game.pvp_mode === 'hp' ? 'Most HP Wins' : 'Most Accurate Wins' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Opponent Info -->
                    <div class="rounded-lg bg-black/50 border-2 border-red-500 p-6 shadow-sm">
                        <div class="p-4 mb-4 -mx-6.5 items-center bg-black/50 border-2 border-red-500">
                            <h4 class="text-lg text-center font-medium text-gray-100 pixel-outline">
                                {{ game.game_mode === 'pvp' ? 'Opponent' : 'Monster Defeated' }}
                            </h4>
                        </div>

                        <div v-if="game.game_mode === 'pve' && monster" class="flex items-center space-x-4">
                            <img
                                :src="monster.image_path || '/images/default-monster.png'"
                                :alt="monster.name"
                                class="h-16 w-16 rounded-full object-cover pixel-outline-icon"
                                @error="handleImageError"
                            />
                            <div>
                                <h5 class="font-medium text-gray-100 pixel-outline">{{ monster.name }}</h5>
                                <p class="text-sm text-gray-400 pixel-outline">Original HP: {{ monster.hp }}</p>
                                <p class="text-sm text-gray-400 pixel-outline">Final HP: {{ game.monster_hp }}</p>
                            </div>
                        </div>

                        <div v-else-if="game.game_mode === 'pvp'" class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-black/50 border-2 border-green-500">
                                    <span class="font-bold text-green-300 pixel-outline">
                                        {{ getOpponent()?.first_name.charAt(0) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-green-300 pixel-outline">
                                        {{ getOpponent()?.first_name }} {{ getOpponent()?.last_name }}
                                    </p>
                                    <p class="text-sm text-gray-400 pixel-outline">Final HP: {{ getOpponentHp() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Player Statistics -->
                <div class="mb-6 mx-4 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Player 1 Stats -->
                    <div class="bg-green-900/50 border-2 border-green-500 py-6 px-8 rounded-lg shadow-sm">
                        <div class="mb-4 flex justify-center items-center space-x-3 bg-black/50 border-2 border-green-500 -mx-8.5 p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-black/50 border-2 border-green-500">
                                <span class="font-bold text-green-300 pixel-outline">
                                    {{ playerOne.first_name.charAt(0) }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-medium text-green-300 pixel-outline">{{ playerOne.first_name }} {{ playerOne.last_name }}</h4>
                                <p class="text-sm text-gray-400 pixel-outline">Player 1</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Final HP:</span>
                                <span class="font-medium text-red-500 pixel-outline">❤️ {{ game.player_one_hp }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Score:</span>
                                <span class="font-medium text-yellow-500 pixel-outline">⭐ {{ game.player_one_score }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Correct Answers:</span>
                                <span class="font-medium text-green-600 pixel-outline">{{ game.correct_answers_p1 || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Total Questions:</span>
                                <span class="font-medium text-blue-300 pixel-outline">{{ game.total_questions_p1 || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Accuracy:</span>
                                <span class="font-medium text-purple-600 pixel-outline">{{ getAccuracy(game.correct_answers_p1, game.total_questions_p1) }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Player 2 Stats -->
                    <div v-if="playerTwo" class="bg-green-900/50 border-2 border-green-500 py-6 px-8 shadow-sm rounded-lg">
                        <div class="mb-4 flex justify-center items-center space-x-3 bg-black/50 border-2 border-green-500 -mx-8.5 p-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-black/50 border-2 border-green-500">
                                <span class="font-bold text-green-300 pixel-outline">
                                    {{ playerTwo.first_name.charAt(0) }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-medium text-green-300 pixel-outline">{{ playerTwo.first_name }} {{ playerTwo.last_name }}</h4>
                                <p class="text-sm text-gray-400 pixel-outline">Player 2</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Final HP:</span>
                                <span class="font-medium text-red-500 pixel-outline">❤️ {{ game.player_two_hp }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Score:</span>
                                <span class="font-medium text-yellow-500 pixel-outline">⭐ {{ game.player_two_score }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Correct Answers:</span>
                                <span class="font-medium text-green-600 pixel-outline">{{ game.correct_answers_p2 || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Total Questions:</span>
                                <span class="font-medium text-blue-300 pixel-outline">{{ game.total_questions_p2 || 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400 pixel-outline">Accuracy:</span>
                                <span class="font-medium text-purple-600 pixel-outline">{{ getAccuracy(game.correct_answers_p2, game.total_questions_p2) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-4 mx-4 justify-center sm:flex-row">
                    <Link
                        :href="route('multiplayer-games.create')"
                        class="flex pixel-outline items-center justify-center rounded-md bg-green-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create New Game
                    </Link>

                    <Link
                        :href="route('multiplayer-games.lobby')"
                        class="flex pixel-outline items-center justify-center rounded-md bg-blue-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            ></path>
                        </svg>
                        Join Another Game
                    </Link>

                    <Link
                        :href="route('multiplayer-games.lobby')"
                        class="flex pixel-outline items-center justify-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="mr-2 h-4 w-4 pixel-outline-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                            ></path>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4l2 2 4-4"
                            ></path>
                        </svg>
                        View My Games
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Player {
    id: number;
    first_name: string;
    last_name: string;
}

interface Monster {
    name: string;
    hp: number;
    attack: number;
    image_path?: string;
}

interface Game {
    id: number;
    game_mode: 'pve' | 'pvp';
    status: string;
    player_one_hp: number;
    player_two_hp: number;
    player_one_score: number;
    player_two_score: number;
    monster_hp?: number;
    correct_answers_p1?: number;
    correct_answers_p2?: number;
    total_questions_p1?: number;
    total_questions_p2?: number;
    created_at: string;
    updated_at: string;
    pvp_mode?: string; // <-- Add pvp_mode
}

const props = defineProps<{
    game: Game;
    monster?: Monster;
    playerOne: Player;
    playerTwo?: Player;
    source_name: string;
    quizTypes: Record<string, string>;
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

// Methods
const getGameResult = () => {
    if (props.game.status === 'abandoned') {
        return {
            type: 'abandoned',
            title: 'Game Abandoned',
            description: 'This game was abandoned before completion.',
            textColor: 'text-gray-800 dark:text-gray-200',
        };
    }

    if (props.game.game_mode === 'pvp') {
        // PvP mode - use pvp_mode
        const isPlayerOne = currentUser.value.id === props.playerOne.id;
        if (props.game.pvp_mode === 'hp') {
            // HP-based PvP result
            const myHp = isPlayerOne ? (props.game.player_one_hp ?? 0) : (props.game.player_two_hp ?? 0);
            const opponentHp = isPlayerOne ? (props.game.player_two_hp ?? 0) : (props.game.player_one_hp ?? 0);
            if (myHp > opponentHp) {
                return {
                    type: 'victory',
                    title: 'Victory!',
                    description: `You won with ${myHp} HP vs opponent's ${opponentHp} HP!`,
                    textColor: 'text-green-800 dark:text-green-300',
                };
            } else if (myHp < opponentHp) {
                return {
                    type: 'defeat',
                    title: 'Defeat',
                    description: `Your opponent won with ${opponentHp} HP vs your ${myHp} HP.`,
                    textColor: 'text-red-800 dark:text-red-300',
                };
            } else {
                return {
                    type: 'tie',
                    title: 'Tie!',
                    description: `Both players finished with ${myHp} HP.`,
                    textColor: 'text-gray-800 dark:text-gray-200',
                };
            }
        } else {
            // Default to accuracy-based PvP result
            const myAccuracy = getAccuracy(
                isPlayerOne ? (props.game.correct_answers_p1 ?? 0) : (props.game.correct_answers_p2 ?? 0),
                isPlayerOne ? (props.game.total_questions_p1 ?? 0) : (props.game.total_questions_p2 ?? 0),
            );
            const opponentAccuracy = getAccuracy(
                isPlayerOne ? (props.game.correct_answers_p2 ?? 0) : (props.game.correct_answers_p1 ?? 0),
                isPlayerOne ? (props.game.total_questions_p2 ?? 0) : (props.game.total_questions_p1 ?? 0),
            );
            const myAccuracyNum = parseFloat(myAccuracy);
            const opponentAccuracyNum = parseFloat(opponentAccuracy);
            if (myAccuracyNum > opponentAccuracyNum) {
                return {
                    type: 'victory',
                    title: 'Victory!',
                    description: `You won with ${myAccuracy}% accuracy vs opponent's ${opponentAccuracy}%!`,
                    textColor: 'text-green-800 dark:text-green-300',
                };
            } else if (myAccuracyNum < opponentAccuracyNum) {
                return {
                    type: 'defeat',
                    title: 'Defeat',
                    description: `Your opponent won with ${opponentAccuracy}% accuracy vs your ${myAccuracy}%.`,
                    textColor: 'text-red-800 dark:text-red-300',
                };
            } else {
                return {
                    type: 'tie',
                    title: 'Tie!',
                    description: `Both players achieved ${myAccuracy}% accuracy.`,
                    textColor: 'text-gray-800 dark:text-gray-200',
                };
            }
        }
    } else {
        // PvE mode - HP-based results
        if ((props.game.monster_hp ?? 1) <= 0) {
            return {
                type: 'victory',
                title: 'Victory!',
                description: 'You and your teammate defeated the monster!',
                textColor: 'text-green-800 dark:text-green-300',
            };
        } else {
            return {
                type: 'defeat',
                title: 'Defeat',
                description: 'The monster proved too powerful.',
                textColor: 'text-red-800 dark:text-red-300',
            };
        }
    }
};

const getGameResultBanner = () => {
    const result = getGameResult();
    switch (result.type) {
        case 'victory':
            return 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800';
        case 'defeat':
            return 'bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800';
        default:
            return 'bg-gray-50 border border-gray-200 dark:bg-gray-800 dark:border-gray-700';
    }
};

const getOpponent = () => {
    const isPlayerOne = currentUser.value.id === props.playerOne.id;
    return isPlayerOne ? props.playerTwo : props.playerOne;
};

const getOpponentHp = () => {
    const isPlayerOne = currentUser.value.id === props.playerOne.id;
    return isPlayerOne ? props.game.player_two_hp : props.game.player_one_hp;
};

const getAccuracy = (correct: number = 0, total: number = 0): string => {
    if (!total || total === 0) return '0';
    return Math.round((correct / total) * 100).toString();
};

const getGameDuration = (): string => {
    const created = new Date(props.game.created_at);
    const updated = new Date(props.game.updated_at);
    const diffMs = updated.getTime() - created.getTime();
    const diffMins = Math.round(diffMs / (1000 * 60));

    if (diffMins < 1) return 'Less than a minute';
    if (diffMins === 1) return '1 minute';
    if (diffMins < 60) return `${diffMins} minutes`;

    const hours = Math.floor(diffMins / 60);
    const mins = diffMins % 60;
    if (hours === 1) {
        return mins > 0 ? `1 hour ${mins} minutes` : '1 hour';
    }
    return mins > 0 ? `${hours} hours ${mins} minutes` : `${hours} hours`;
};

const getStatusBadgeClass = () => {
    switch (props.game.status) {
        case 'finished':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300';
        case 'abandoned':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300';
        default:
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300';
    }
};

const getStatusLabel = () => {
    switch (props.game.status) {
        case 'finished':
            return 'Completed';
        case 'abandoned':
            return 'Abandoned';
        default:
            return 'Unknown';
    }
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/default-monster.png';
};
</script>
