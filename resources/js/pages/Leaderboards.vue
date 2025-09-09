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
        <div class="flex flex-col items-center px-4 py-8">
            <h2 class="pixel-outline mb-6 text-2xl font-bold">Leaderboards</h2>
            <div class="mb-6 flex gap-4">
                <button
                    class="rounded border-2 px-4 py-2 font-bold"
                    :class="activeTab === 'general' ? 'border-green-700 bg-green-500 text-white' : 'border-gray-400 bg-gray-600'"
                    @click="activeTab = 'general'"
                >
                    General Ranking
                </button>
                <button
                    class="rounded border-2 px-4 py-2 font-bold"
                    :class="activeTab === 'multiplayer' ? 'border-blue-700 bg-blue-500 text-white' : 'border-gray-400 bg-gray-600'"
                    @click="activeTab = 'multiplayer'"
                >
                    Multiplayer Ranking
                </button>
            </div>
            <div v-if="activeTab === 'general'" class="w-full max-w-2xl">
                <h3 class="mb-2 text-xl font-bold">General Ranking (by Level)</h3>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-500">
                            <th class="border px-4 py-2">Rank</th>
                            <th class="border px-4 py-2">Name</th>
                            <th class="border px-4 py-2">Level</th>
                            <th class="border px-4 py-2">Experience</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(user, idx) in generalLeaderboard" :key="user.id" class="text-center">
                            <td class="border px-4 py-2">{{ idx + 1 }}</td>
                            <td class="border px-4 py-2">{{ user.first_name }} {{ user.last_name }}</td>
                            <td class="border px-4 py-2">{{ user.level }}</td>
                            <td class="border px-4 py-2">{{ user.experience }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="w-full max-w-2xl">
                <h3 class="mb-2 text-xl font-bold">Multiplayer Ranking (by Wins)</h3>
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-500">
                            <th class="border px-4 py-2">Rank</th>
                            <th class="border px-4 py-2">Name</th>
                            <th class="border px-4 py-2">Level</th>
                            <th class="border px-4 py-2">Wins</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(user, idx) in multiplayerLeaderboard" :key="user.id" class="text-center">
                            <td class="border px-4 py-2">{{ idx + 1 }}</td>
                            <td class="border px-4 py-2">{{ user.first_name }} {{ user.last_name }}</td>
                            <td class="border px-4 py-2">{{ user.level }}</td>
                            <td class="border px-4 py-2">{{ user.wins }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
