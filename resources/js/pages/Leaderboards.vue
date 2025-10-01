<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface GeneralLeaderboardUser {
    id: number;
    first_name: string;
    last_name: string;
    level: number;
    experience: number;
}

interface MultiplayerLeaderboardUser {
    id: number;
    first_name: string;
    last_name: string;
    level: number;
    wins: number;
}

const generalLeaderboard = ref<GeneralLeaderboardUser[]>([]);
const multiplayerLeaderboard = ref<MultiplayerLeaderboardUser[]>([]);
const activeTab = ref('general');

onMounted(async () => {
    const [generalRes, multiplayerRes] = await Promise.all([axios.get('/api/leaderboard/general'), axios.get('/api/leaderboard/multiplayer')]);
    generalLeaderboard.value = generalRes.data;
    multiplayerLeaderboard.value = multiplayerRes.data;
});
</script>

<template>
    <Head title="Leaderboards" />
    <AppLayout>
        <div class="flex flex-col min-h-screen items-center px-4 py-8 bg-gradient">
            <div class="flex justify-center mb-6 mx-4">
                <h1 class="welcome-banner animate-soft-bounce px-6 py-2 text-center text-2xl leading-tight font-bold pixel-outline">Leaderboards</h1>
            </div>
            <div class="mb-6 flex gap-4">
                <button
                    class="rounded border-2 px-4 py-2 font-bold pixel-outline"
                    :class="activeTab === 'general' ? 'border-green-700 bg-green-500 hover:bg-green-800 text-white' : 'border-gray-400 bg-gray-600'"
                    @click="activeTab = 'general'"
                >
                    General Ranking
                </button>
                <button
                    class="rounded border-2 px-4 py-2 font-bold pixel-outline"
                    :class="activeTab === 'multiplayer' ? 'border-blue-700 bg-blue-500 hover:bg-blue-800 text-white' : 'border-gray-400 bg-gray-600'"
                    @click="activeTab = 'multiplayer'"
                >
                    Multiplayer Ranking
                </button>
            </div>
            <div v-if="activeTab === 'general'" class="w-full max-w-8xl bg-black/50 p-6 rounded">
                <h3 class="mb-2 text-xl font-bold pixel-outline">General Ranking (by Level)</h3>
                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div
                        v-for="(user, idx) in generalLeaderboard"
                        :key="user.id"
                        class="rounded border bg-container p-4 text-white pixel-outline"
                    >
                        <p class="font-bold">Rank: {{ idx + 1 }}</p>
                        <p>Name: {{ user.first_name }} {{ user.last_name }}</p>
                        <p>Level: {{ user.level }}</p>
                        <p>Experience: {{ user.experience }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="w-full max-w-8xl bg-black/50 p-6 rounded">
                <h3 class="mb-2 text-xl font-bold pixel-outline">Multiplayer Ranking (by Wins)</h3>
                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div
                        v-for="(user, idx) in multiplayerLeaderboard"
                        :key="user.id"
                        class="rounded border bg-container pixel-outline p-4 text-white"
                    >
                        <p class="font-bold">Rank: {{ idx + 1 }}</p>
                        <p>Name: {{ user.first_name }} {{ user.last_name }}</p>
                        <p>Level: {{ user.level }}</p>
                        <p>Wins: {{ user.wins }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
