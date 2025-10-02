<template>
    <Head title="Waiting for Player" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Waiting for Player to Join</h2>
            </div>
        </template>

        <div class="bg-gradient min-h-screen py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Game Info Card -->
                <div class="bg-container mx-4 mb-6 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center">
                            <!-- Loading Animation -->
                            <div
                                class="pixel-outline-icon mx-auto mb-6 h-16 w-16 animate-spin rounded-full border-4 border-purple-200 border-t-purple-600"
                            ></div>

                            <h3 class="pixel-outline mb-2 text-xl font-medium text-gray-100">Game Created Successfully!</h3>
                            <p class="pixel-outline mb-6 text-gray-400">Waiting for another player to join your battle...</p>

                            <!-- Game Mode Badge -->
                            <div class="mb-6 flex justify-center">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                    :class="
                                        game.game_mode === 'pvp'
                                            ? 'pixel-outline bg-red-900/20 text-red-300'
                                            : 'pixel-outline bg-green-900/20 text-green-300'
                                    "
                                >
                                    {{ game.game_mode === 'pvp' ? 'PvP (Player vs Player)' : 'PvE (Co-op vs Monster)' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Game Creator Info -->
                            <div class="rounded-lg border border-2 border-green-500 bg-black/50 p-4">
                                <h4 class="pixel-outline mb-3 font-medium text-gray-100">Game Creator</h4>
                                <div class="flex items-center space-x-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-green-500 bg-black/50">
                                        <span class="pixel-outline font-bold text-green-300">
                                            {{ game.playerOne.first_name.charAt(0) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="pixel-outline font-medium text-green-300">
                                            {{ game.playerOne.first_name }} {{ game.playerOne.last_name }}
                                        </p>
                                        <p class="pixel-outline text-sm text-gray-400">Ready to battle!</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Monster/Opponent Info -->
                            <div class="rounded-lg border border-2 border-red-500 bg-black/50 p-4">
                                <h4 class="pixel-outline mb-3 font-medium text-gray-100">
                                    {{ game.game_mode === 'pvp' ? 'Battle Type' : 'Your Opponent' }}
                                </h4>

                                <div v-if="game.game_mode === 'pve' && game.monster" class="flex items-center space-x-3">
                                    <img
                                        :src="game.monster.image_path || '/images/default-monster.png'"
                                        :alt="game.monster.name"
                                        class="pixel-outline-icon h-10 w-10 rounded-full object-cover"
                                        @error="handleImageError"
                                    />
                                    <div>
                                        <p class="pixel-outline font-medium text-red-700">{{ game.monster.name }}</p>
                                        <p class="pixel-outline text-sm text-gray-400">
                                            HP: {{ game.monster.hp }} | Attack: {{ game.monster.attack }}
                                        </p>
                                    </div>
                                </div>

                                <div v-else-if="game.game_mode === 'pvp'" class="flex items-center space-x-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-red-500 bg-black/50">
                                        <svg class="pixel-outline-icon h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="pixel-outline font-medium text-gray-100">Direct Combat</p>
                                        <p class="pixel-outline text-sm text-gray-400">Battle another player head-to-head</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Study Material Info -->
                        <div class="mt-6 rounded-lg border-2 border-yellow-500 bg-black/50 p-4">
                            <h4 class="pixel-outline mb-2 font-medium text-gray-100">Study Material</h4>
                            <p class="pixel-outline text-yellow-300">{{ game.source_name }}</p>
                            <p class="pixel-outline text-sm text-gray-400">Questions will be drawn from this source</p>
                        </div>

                        <!-- Private Game Code (if applicable) -->
                        <div v-if="game.is_private && game.game_code" class="mt-6 rounded-lg border-2 border-blue-500 bg-black/50 p-4">
                            <h4 class="pixel-outline mb-2 font-medium text-blue-300">🔐 Private Game</h4>
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <p class="pixel-outline mb-1 text-blue-200">Game Code:</p>
                                    <code class="rounded bg-blue-900/30 px-2 py-1 font-mono text-lg font-bold text-blue-100">{{
                                        game.game_code
                                    }}</code>
                                </div>
                                <button
                                    @click="copyGameCode"
                                    :class="[
                                        'rounded-md px-3 py-2 text-sm transition-colors',
                                        copied ? 'bg-green-600 text-white' : 'bg-blue-600 text-white hover:bg-blue-700',
                                    ]"
                                >
                                    {{ copied ? '✓ Copied!' : 'Copy Code' }}
                                </button>
                            </div>
                            <p class="pixel-outline mt-2 text-xs text-blue-400">Share this code with your opponent to let them join the game</p>
                        </div>

                        <!-- Game Rules -->
                        <div class="mt-6 rounded-lg border border-2 border-blue-800 bg-blue-900/20 p-4">
                            <h4 class="pixel-outline mb-2 font-medium text-blue-100">
                                {{ game.game_mode === 'pvp' ? 'PvP Rules' : 'PvE Rules' }}
                            </h4>
                            <ul class="pixel-outline space-y-1 text-sm text-blue-300">
                                <template v-if="game.game_mode === 'pve'">
                                    <li>• Players take turns answering questions</li>
                                    <li>• Correct answers deal damage to the monster</li>
                                    <li>• Wrong answers let the monster attack the current player</li>
                                    <li>• Work together to defeat the monster!</li>
                                </template>
                                <template v-else>
                                    <li>• Players take turns answering questions</li>
                                    <li>• Correct answers deal damage to your opponent</li>
                                    <li>• Wrong answers cause damage to yourself</li>
                                    <li>• Last player standing wins!</li>
                                </template>
                            </ul>
                        </div>

                        <!-- Share Game Link -->
                        <div class="mt-6 text-center">
                            <p class="pixel-outline mb-3 text-sm text-gray-300">
                                Share this game with a friend or wait for someone to join from the lobby!
                            </p>
                            <div class="flex items-center space-x-2">
                                <input
                                    :value="gameUrl"
                                    readonly
                                    class="pixel-outline flex-1 rounded-md border-2 border-gray-600 bg-black/50 px-3 py-2 text-sm"
                                />
                                <button
                                    @click="copyGameUrl"
                                    class="pixel-outline rounded-md bg-blue-600 px-3 py-2 text-sm text-white hover:bg-blue-700"
                                >
                                    {{ copied ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                        </div>

                        <!-- Wait Time Display -->
                        <div class="mt-6 text-center">
                            <p class="pixel-outline text-sm text-gray-400">Waiting for {{ waitTime }} seconds...</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mx-4 grid grid-cols-2 gap-4">
                    <Link
                        :href="route('multiplayer-games.lobby')"
                        class="pixel-outline flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 disabled:opacity-50"
                    >
                        <svg class="pixel-outline-icon mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Lobby
                    </Link>

                    <button
                        @click="refreshLobby"
                        :disabled="refreshing"
                        class="pixel-outline flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50"
                    >
                        <svg
                            class="pixel-outline-icon mr-2 h-4 w-4"
                            :class="refreshing ? 'animate-spin' : ''"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            ></path>
                        </svg>
                        {{ refreshing ? 'Refreshing...' : 'Refresh' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Game {
    id: number;
    game_mode: 'pve' | 'pvp';
    playerOne: {
        first_name: string;
        last_name: string;
    };
    currentUser: {
        id: number;
    };
    monster?: {
        name: string;
        hp: number;
        attack: number;
        image_path?: string;
    };
    source_name: string;
    is_private?: boolean;
    game_code?: string;
}

const props = defineProps<{
    game: Game;
}>();

// Component state
const copied = ref(false);
const refreshing = ref(false);
const waitTime = ref(0);

// Computed properties
const gameUrl = computed(() => {
    // Use game code for a more user-friendly URL (works for both private and public games)
    if (props.game.game_code) {
        return `${window.location.origin}/game/${props.game.game_code}`;
    }
    // Fallback to game ID if no code is available
    return `${window.location.origin}/multiplayer-games/${props.game.id}`;
});

// Methods
const copyGameUrl = async () => {
    try {
        await navigator.clipboard.writeText(gameUrl.value);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy URL:', error);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = gameUrl.value;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
};

const copyGameCode = async () => {
    try {
        await navigator.clipboard.writeText(props.game.game_code || '');
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy game code:', error);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = props.game.game_code || '';
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
};

const refreshLobby = () => {
    refreshing.value = true;
    router.reload({
        onFinish: () => {
            refreshing.value = false;
        },
    });
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/default-monster.png';
};

// Wait time counter
let waitInterval: number;

const startWaitTimer = () => {
    waitTime.value = 0;
    waitInterval = setInterval(() => {
        waitTime.value++;
    }, 1000);
};

const stopWaitTimer = () => {
    if (waitInterval) {
        clearInterval(waitInterval);
    }
};

// WebSocket listener for when a player joins
let echo: any;

onMounted(() => {
    startWaitTimer();

    if (window.Echo) {
        echo = window.Echo.private(`multiplayer-game.${props.game.id}`)
            .listen('MultiplayerGameUpdated', (e: any) => {
                console.log('WaitingRoom received game update:', e);

                // Handle game started event
                if (e.event_type === 'game_started') {
                    console.log('Game started! Redirecting to game...');
                    stopWaitTimer();

                    // Show a brief message before redirecting
                    setTimeout(() => {
                        router.visit(route('multiplayer-games.show', props.game.id));
                    }, 1000);
                    return;
                }

                // Fallback: Check if player two has joined and game is active
                if (e.game.player_two_id && e.game.status === 'active') {
                    console.log('Player 2 joined! Redirecting to game...');
                    stopWaitTimer();
                    router.visit(route('multiplayer-games.show', props.game.id));
                }
            })
            .error((error: any) => {
                console.error('WaitingRoom WebSocket error:', error);
            });

        console.log('WaitingRoom WebSocket connection established for game:', props.game.id);
    } else {
        console.error('Laravel Echo not available in WaitingRoom');
    }
});

onUnmounted(() => {
    stopWaitTimer();

    if (echo) {
        echo.stopListening('MultiplayerGameUpdated');
    }
});
</script>
