<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem, type Game, type User } from '@/types';
import { ref, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

interface Props {
    games: Game[];
}
const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Game Lobby',
        href: '/games/lobby',
    },
];

const loading = ref(false);
const user = (usePage().props.auth as {user: User}).user;


function createLobby() {
    loading.value = true;

    router.post('/games', { player_one_id: user.id }, {
        onSuccess: () => router.reload(),
        onFinish: () => loading.value = false,
    });
}

function joinGame(gameId: number) {
    loading.value = true;
    router.post(`/games/${gameId}/join`, { player_two_id: user.id }, {
        onSuccess: () => router.reload(),
        onFinish: () => loading.value = false,
    });
}

const echo = window.Echo;

onMounted(() => {
  props.games.forEach(game => {
    echo.channel('game.' + game.id)
      .listen('GameUpdated', (e: any) => {
        // If game is finished or filled, reload lobby
        if (e.game.status === 'finished' || e.game.player_two_id) {
          router.reload();
        }
      });
  });
  echo.channel('game.lobby')
      .listen('GameLobbyUpdate', (e: any) => {
          console.log('Lobby updated:', e);
          router.reload();
      });
});

onUnmounted(() => {
  props.games.forEach(game => {
    echo.channel('game.' + game.id).stopListening('GameUpdated');
  });
});
</script>

<template>
    <Head title="Game Lobby" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">Game Lobby</h1>
            <button @click="createLobby" :disabled="loading" class="mb-4 px-4 py-2 bg-background text-foreground rounded">Create Lobby</button>
            <div v-if="props.games.length === 0" class="text-muted-foreground text-center">
                No open games available.
            </div>
            <div v-else>
                <table class="min-w-full border rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-muted">
                            <th class="px-4 py-2 text-left">Game ID</th>
                            <th class="px-4 py-2 text-left">Player One</th>
                            <th class="px-4 py-2 text-left">Created At</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="game in props.games" :key="game.id" class="border-b">
                            <td class="px-4 py-2">{{ game.id }}</td>
                            <td class="px-4 py-2">{{ game.player_one_id }}</td>
                            <td class="px-4 py-2">{{ game.created_at }}</td>
                            <td class="px-4 py-2">
                                <button @click="joinGame(game.id)" :disabled="loading" class="text-primary hover:underline">Join</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
