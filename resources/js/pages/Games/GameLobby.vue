<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

interface User {
    id: number;
    first_name: string;
    last_name: string;
}

interface Monster {
    id: string;
    name: string;
    hp: number;
    attack: number;
    image: string;
}

interface MultiplayerGame {
    id: number;
    player_one_id: number;
    player_two_id?: number;
    monster_id: string;
    file_id?: number;
    collection_id?: number;
    status: string;
    created_at: string;
    playerOne: User;
    playerTwo?: User;
    monster: Monster;
    source_name: string;
}

interface Props {
    waitingGames: {
        data: MultiplayerGame[];
        links: any[];
    };
}

const props = defineProps<Props>();

const loading = ref(false);
const user = (usePage().props.auth as {user: User}).user;

function joinGame(gameId: number) {
    loading.value = true;
    router.post(route('multiplayer-games.join', gameId), {}, {
        onSuccess: () => router.reload(),
        onFinish: () => loading.value = false,
    });
}

const echo = window.Echo;

onMounted(() => {
  props.waitingGames.data.forEach(game => {
    echo.channel('multiplayer-game.' + game.id)
      .listen('MultiplayerGameUpdated', (e: any) => {
        // If game is started or filled, reload lobby
        if (e.game.status === 'active' || e.game.player_two_id) {
          router.reload();
        }
      });
  });
});

onUnmounted(() => {
  props.waitingGames.data.forEach(game => {
    echo.leave('multiplayer-game.' + game.id);
  });
});

const formatTimeAgo = (dateString: string) => {
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
</script>

<template>
    <Head title="Multiplayer Game Lobby" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Multiplayer Game Lobby
                </h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('multiplayer-games.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        My Games
                    </Link>
                    <Link
                        :href="route('multiplayer-games.create')"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Game
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Info Banner -->
                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">
                                Multiplayer Battle Lobby
                            </h3>
                            <p class="text-sm text-purple-700 dark:text-purple-300">
                                Join an existing game or create your own. Work together with another player to defeat monsters!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Waiting Games -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            Games Waiting for Players ({{ waitingGames.data.length }})
                        </h3>

                        <div v-if="waitingGames.data.length > 0" class="space-y-4">
                            <div
                                v-for="game in waitingGames.data"
                                :key="game.id"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >
                                <div class="flex items-center space-x-4">
                                    <!-- Monster Avatar -->
                                    <div class="flex-shrink-0">
                                        <img
                                            :src="game.monster.image"
                                            :alt="game.monster.name"
                                            class="w-12 h-12 rounded-full"
                                        >
                                    </div>

                                    <!-- Game Info -->
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Battle vs {{ game.monster.name }}
                                            </h4>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                Waiting
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                by {{ game.playerOne.first_name }} {{ game.playerOne.last_name }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ formatTimeAgo(game.created_at) }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                                {{ game.source_name }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            <span>Monster HP: {{ game.monster.hp }}</span>
                                            <span>Attack: {{ game.monster.attack }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Join Button -->
                                <button
                                    @click="joinGame(game.id)"
                                    :disabled="loading"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Join Battle
                                </button>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No games waiting</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                No multiplayer games are currently waiting for players. Create one to get started!
                            </p>
                            <div class="mt-6">
                                <Link
                                    :href="route('multiplayer-games.create')"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create Multiplayer Game
                                </Link>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="waitingGames.data.length > 0 && waitingGames.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <Link
                                        v-if="waitingGames.links.prev"
                                        :href="waitingGames.links.prev"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                    >
                                        Previous
                                    </Link>
                                    <Link
                                        v-if="waitingGames.links.next"
                                        :href="waitingGames.links.next"
                                        class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                    >
                                        Next
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
