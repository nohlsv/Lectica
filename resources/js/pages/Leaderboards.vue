<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';

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
  const [generalRes, multiplayerRes] = await Promise.all([
    axios.get('/api/leaderboard/general'),
    axios.get('/api/leaderboard/multiplayer'),
  ]);
  generalLeaderboard.value = generalRes.data;
  multiplayerLeaderboard.value = multiplayerRes.data;
});
</script>

<template>
  <Head title="Leaderboards" />
  <AppLayout>
    <div class="flex flex-col items-center py-8 px-4">
      <h2 class="pixel-outline text-2xl font-bold mb-6">Leaderboards</h2>
      <div class="flex gap-4 mb-6">
        <button
          class="px-4 py-2 rounded font-bold border-2"
          :class="activeTab === 'general' ? 'bg-green-500 text-white border-green-700' : 'bg-gray-600 border-gray-400'"
          @click="activeTab = 'general'"
        >General Ranking</button>
        <button
          class="px-4 py-2 rounded font-bold border-2"
          :class="activeTab === 'multiplayer' ? 'bg-blue-500 text-white border-blue-700' : 'bg-gray-600 border-gray-400'"
          @click="activeTab = 'multiplayer'"
        >Multiplayer Ranking</button>
      </div>
      <div v-if="activeTab === 'general'" class="w-full max-w-2xl">
        <h3 class="text-xl font-bold mb-2">General Ranking (by Level)</h3>
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
        <h3 class="text-xl font-bold mb-2">Multiplayer Ranking (by Wins)</h3>
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
