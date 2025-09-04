<template>
    <Head title="Multiplayer Game Lobby" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Multiplayer Game Lobby</h2>
                <Link
                    :href="route('multiplayer-games.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-purple-700 focus:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none active:bg-purple-900 dark:focus:ring-offset-gray-800"
                >
                    Create New Game
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Lobby Info Banner -->
                <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-center">
                        <svg class="mr-3 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100">Join a Multiplayer Battle</h3>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Choose from available games created by other players. Games update in real-time!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Available Games -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                            Available Games ({{ waitingGames.data.length }} waiting for players)
                        </h3>

                        <!-- Games Grid -->
                        <div v-if="waitingGames.data.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="game in waitingGames.data"
                                :key="game.id"
                                class="rounded-lg border border-gray-200 bg-gray-50 p-4 transition-all hover:border-purple-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
                            >
                                <!-- Game Mode Badge -->
                                <div class="mb-3 flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="game.game_mode === 'pvp'
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
                                            : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'"
                                    >
                                        {{ game.game_mode === 'pvp' ? 'PvP' : 'PvE' }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ formatTimeAgo(game.created_at) }}</span>
                                </div>

                                <!-- Creator Info -->
                                <div class="mb-3 flex items-center">
                                    <div class="mr-3 h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center dark:bg-purple-900/20">
                                        <span class="text-sm font-medium text-purple-600 dark:text-purple-400">
                                            {{ getInitials(game.player_one.first_name + ' ' + game.player_one.last_name) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ game.player_one.first_name }} {{ game.player_one.last_name }}
                                        </p>
                                        <p class="text-xs text-gray-500">Game Creator</p>
                                    </div>
                                </div>

                                <!-- Source Info -->
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ game.file ? game.file.title || game.file.name : game.collection?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ game.file ? 'Single File' : 'Collection' }}
                                    </p>
                                </div>

                                <!-- Monster Info (PvE only) -->
                                <div v-if="game.game_mode === 'pve' && game.monster" class="mb-4 flex items-center rounded-md bg-gray-100 p-2 dark:bg-gray-800">
                                    <img
                                        :src="game.monster.image_path || '/images/default-monster.png'"
                                        :alt="game.monster.name"
                                        class="mr-2 h-8 w-8 rounded-full object-cover"
                                        @error="handleImageError"
                                    />
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ game.monster.name }}</p>
                                        <p class="text-xs text-gray-500">HP: {{ game.monster.hp }} | Attack: {{ game.monster.attack }}</p>
                                    </div>
                                </div>

                                <!-- PvP Info -->
                                <div v-if="game.game_mode === 'pvp'" class="mb-4 rounded-md bg-red-50 p-2 dark:bg-red-900/10">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                        <p class="text-sm font-medium text-red-800 dark:text-red-300">Player vs Player Battle</p>
                                    </div>
                                </div>

                                <!-- Join Button -->
                                <button
                                    @click="joinGame(game.id)"
                                    :disabled="joiningGameId === game.id"
                                    class="w-full rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-800"
                                >
                                    <span v-if="joiningGameId === game.id" class="flex items-center justify-center">
                                        <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Joining...
                                    </span>
                                    <span v-else>Join Game</span>
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No games available</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                No one has created a multiplayer game yet. Be the first to start a battle!
                            </p>
                            <div class="mt-6">
                                <Link
                                    :href="route('multiplayer-games.create')"
                                    class="inline-flex items-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800"
                                >
                                    Create First Game
                                </Link>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="waitingGames.links && waitingGames.links.length > 3" class="mt-6">
                            <nav class="flex justify-center">
                                <div class="flex space-x-1">
                                    <Link
                                        v-for="link in waitingGames.links"
                                        :key="link.label"
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            'px-3 py-2 text-sm rounded-md',
                                            link.active
                                                ? 'bg-purple-600 text-white'
                                                : link.url
                                                ? 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                                                : 'text-gray-400 cursor-not-allowed'
                                        ]"
                                    />
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, watch, ref, onMounted, onUnmounted } from 'vue';
import { getInitials } from '@/composables/useInitials';

interface Player {
    id: number;
    first_name: string;
    last_name: string;
}

interface Monster {
    id: string;
    name: string;
    hp: number;
    attack: number;
    image_path?: string;
}

interface File {
    id: number;
    name: string;
    title?: string;
    quizzes_count?: number;
}

interface Collection {
    id: number;
    name: string;
    file_count: number;
    total_questions: number;
}

interface Game {
    id: number;
    player_one: Player;
    file?: File;
    collection?: Collection;
    monster?: Monster;
    game_mode: 'pve' | 'pvp';
    created_at: string;
}

interface PaginatedGames {
    data: Game[];
    links?: any[];
}

const props = defineProps<{
    monsters: Monster[];
    files: File[];
    collections: Collection[];
    waitingGames: PaginatedGames;
}>();

// Tab state
const activeTab = ref<'create' | 'lobby'>('create');

// Game creation form
const form = useForm({
    source_type: 'file',
    file_id: '',
    collection_id: '',
    monster_id: '',
    game_mode: 'pve',
});

// Game joining state
const joiningGameId = ref<number | null>(null);

// Form validation
const canSubmit = computed(() => {
    const hasSource = form.source_type === 'file' ? form.file_id : form.collection_id;
    // For PVP mode, monster is not required; for PVE mode, monster is required
    const hasRequiredMonster = form.game_mode === 'pvp' || (form.game_mode === 'pve' && form.monster_id);
    return hasSource && hasRequiredMonster;
});

// Reset file/collection when source type changes
watch(
    () => form.source_type,
    () => {
        form.file_id = '';
        form.collection_id = '';
    },
);

// Reset monster when game mode changes from PVE to PVP
watch(
    () => form.game_mode,
    (newMode) => {
        if (newMode === 'pvp') {
            form.monster_id = '';
        }
    },
);

// Form submission
const submit = () => {
    form.post(route('multiplayer-games.store'), {
        onSuccess: () => {
            // Switch to lobby tab after creating game
            activeTab.value = 'lobby';
        },
    });
};

// Join game functionality
const joinGame = async (gameId: number) => {
    joiningGameId.value = gameId;

    try {
        await router.post(route('multiplayer-games.join', gameId));
    } catch (error) {
        console.error('Failed to join game:', error);
    } finally {
        joiningGameId.value = null;
    }
};

// Utility functions
const formatTimeAgo = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours}h ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays}d ago`;
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/default-monster.png';
};

// Real-time lobby updates via websockets
let echo: any;

onMounted(() => {
    // Listen for lobby updates
    if (window.Echo) {
        echo = window.Echo.channel('multiplayer-lobby')
            .listen('MultiplayerGameLobbyUpdate', () => {
                // Refresh the page to get updated games
                router.reload({ only: ['waitingGames'] });
            });
    }
});

onUnmounted(() => {
    if (echo) {
        echo.stopListening('MultiplayerGameLobbyUpdate');
    }
});
</script>
