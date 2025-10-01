<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Flame, Trophy, Calendar, Filter, GraduationCap } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface GeneralLeaderboardUser {
    id: number;
    first_name: string;
    last_name: string;
    level: number;
    experience: number;
    college: string;
}

interface MultiplayerLeaderboardUser {
    id: number;
    first_name: string;
    last_name: string;
    level: number;
    wins: number;
    college: string;
}

interface StreakLeaderboardUser {
    id: number;
    first_name: string;
    last_name: string;
    level: number;
    college: string;
    current_streak: number;
    longest_streak: number;
    total_study_days: number;
}

const generalLeaderboard = ref<GeneralLeaderboardUser[]>([]);
const multiplayerLeaderboard = ref<MultiplayerLeaderboardUser[]>([]);
const streakLeaderboard = ref<StreakLeaderboardUser[]>([]);
const colleges = ref<string[]>([]);
const activeTab = ref('general');
const selectedCollege = ref('all');
const loading = ref(false);

// Load individual leaderboards
const loadGeneralLeaderboard = async () => {
    const params = selectedCollege.value !== 'all' ? { college: selectedCollege.value } : {};
    const response = await axios.get('/api/leaderboard/general', { params });
    generalLeaderboard.value = response.data;
};

const loadMultiplayerLeaderboard = async () => {
    const params = selectedCollege.value !== 'all' ? { college: selectedCollege.value } : {};
    const response = await axios.get('/api/leaderboard/multiplayer', { params });
    multiplayerLeaderboard.value = response.data;
};

const loadStreakLeaderboard = async () => {
    const params = selectedCollege.value !== 'all' ? { college: selectedCollege.value } : {};
    const response = await axios.get('/api/leaderboard/streaks', { params });
    streakLeaderboard.value = response.data;
};

// Load all data
const loadData = async () => {
    loading.value = true;
    try {
        await Promise.all([
            loadGeneralLeaderboard(),
            loadMultiplayerLeaderboard(),
            loadStreakLeaderboard()
        ]);
    } finally {
        loading.value = false;
    }
};

// Handle college filter change
const onCollegeChange = async () => {
    await loadData();
};

onMounted(async () => {
    // Load colleges first
    const collegesRes = await axios.get('/api/leaderboard/colleges');
    colleges.value = collegesRes.data;
    
    // Then load leaderboard data
    await loadData();
});
</script>

<template>
    <Head title="Leaderboards" />
    <AppLayout>
        <div class="flex flex-col min-h-screen items-center px-4 py-8 bg-gradient">
            <div class="flex justify-center mb-6 mx-4">
                <h1 class="welcome-banner animate-soft-bounce px-6 py-2 text-center text-2xl leading-tight font-bold pixel-outline">Leaderboards</h1>
            </div>

            <!-- College Filter -->
            <div class="mb-6 flex flex-col sm:flex-row items-center gap-4 justify-center">
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-gold" />
                    <span class="text-sm font-bold pixel-outline text-white">Filter by College:</span>
                </div>
                <Select v-model="selectedCollege" @update:modelValue="onCollegeChange">
                    <SelectTrigger class="w-64 bg-container border-gold/30 text-white pixel-outline">
                        <SelectValue placeholder="Select College" />
                    </SelectTrigger>
                    <SelectContent class="bg-container border-gold/30">
                        <SelectItem value="all" class="text-white hover:bg-gold/20">
                            <div class="flex items-center gap-2">
                                <GraduationCap class="h-4 w-4" />
                                All Colleges
                            </div>
                        </SelectItem>
                        <SelectItem 
                            v-for="college in colleges" 
                            :key="college" 
                            :value="college"
                            class="text-white hover:bg-gold/20"
                        >
                            {{ college }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <div v-if="loading" class="text-sm text-white/70 pixel-outline">
                    Loading...
                </div>
            </div>
            <div class="mb-6 flex flex-wrap gap-2 sm:gap-4 justify-center">
                <button
                    class="rounded border-2 px-3 py-2 sm:px-4 font-bold pixel-outline text-sm sm:text-base transition-colors"
                    :class="activeTab === 'general' ? 'border-green-700 bg-green-500 hover:bg-green-800 text-white' : 'border-gray-400 bg-gray-600 hover:bg-gray-500'"
                    @click="activeTab = 'general'"
                >
                    General Ranking
                </button>
                <button
                    class="rounded border-2 px-3 py-2 sm:px-4 font-bold pixel-outline text-sm sm:text-base transition-colors"
                    :class="activeTab === 'multiplayer' ? 'border-blue-700 bg-blue-500 hover:bg-blue-800 text-white' : 'border-gray-400 bg-gray-600 hover:bg-gray-500'"
                    @click="activeTab = 'multiplayer'"
                >
                    Multiplayer Ranking
                </button>
                <button
                    class="rounded border-2 px-3 py-2 sm:px-4 font-bold pixel-outline text-sm sm:text-base transition-colors flex items-center gap-2"
                    :class="activeTab === 'streaks' ? 'border-orange-700 bg-orange-500 hover:bg-orange-800 text-white' : 'border-gray-400 bg-gray-600 hover:bg-gray-500'"
                    @click="activeTab = 'streaks'"
                >
                    <Flame class="h-4 w-4" />
                    Study Streaks
                </button>
            </div>
            <!-- General Leaderboard -->
            <div v-if="activeTab === 'general'" class="w-full max-w-8xl bg-black/50 p-4 sm:p-6 rounded">
                <h3 class="mb-4 text-lg sm:text-xl font-bold pixel-outline">General Ranking (by Level)</h3>
                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div
                        v-for="(user, idx) in generalLeaderboard"
                        :key="user.id"
                        class="rounded border bg-container p-4 text-white pixel-outline transition-transform hover:scale-105"
                    >
                        <p class="font-bold text-gold">Rank: {{ idx + 1 }}</p>
                        <p class="font-semibold">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-sm text-white/80">{{ user.college }}</p>
                        <p>Level: {{ user.level }}</p>
                        <p>Experience: {{ user.experience }}</p>
                    </div>
                </div>
            </div>

            <!-- Multiplayer Leaderboard -->
            <div v-else-if="activeTab === 'multiplayer'" class="w-full max-w-8xl bg-black/50 p-4 sm:p-6 rounded">
                <h3 class="mb-4 text-lg sm:text-xl font-bold pixel-outline">Multiplayer Ranking (by Wins)</h3>
                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div
                        v-for="(user, idx) in multiplayerLeaderboard"
                        :key="user.id"
                        class="rounded border bg-container pixel-outline p-4 text-white transition-transform hover:scale-105"
                    >
                        <p class="font-bold text-gold">Rank: {{ idx + 1 }}</p>
                        <p class="font-semibold">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-sm text-white/80">{{ user.college }}</p>
                        <p>Level: {{ user.level }}</p>
                        <p>Wins: {{ user.wins }}</p>
                    </div>
                </div>
            </div>

            <!-- Study Streaks Leaderboard -->
            <div v-else-if="activeTab === 'streaks'" class="w-full max-w-8xl bg-black/50 p-4 sm:p-6 rounded">
                <h3 class="mb-4 text-lg sm:text-xl font-bold pixel-outline flex items-center gap-2">
                    <Flame class="h-5 w-5 text-orange-400" />
                    Study Streaks Ranking
                </h3>
                <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                    <div
                        v-for="(user, idx) in streakLeaderboard"
                        :key="user.id"
                        class="rounded border bg-container p-4 text-white pixel-outline transition-transform hover:scale-105"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <p class="font-bold text-gold">Rank: {{ idx + 1 }}</p>
                            <Trophy v-if="idx === 0" class="h-4 w-4 text-yellow-400" />
                            <Flame v-else-if="idx < 3" class="h-4 w-4 text-orange-400" />
                        </div>
                        <p class="font-semibold">{{ user.first_name }} {{ user.last_name }}</p>
                        <p class="text-sm text-white/80 mb-2">{{ user.college }}</p>
                        <div class="text-sm space-y-1">
                            <div class="flex items-center gap-1">
                                <Flame class="h-3 w-3 text-orange-400" />
                                <span>Current: {{ user.current_streak }} days</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Trophy class="h-3 w-3 text-gold" />
                                <span>Best: {{ user.longest_streak }} days</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Calendar class="h-3 w-3 text-blue-400" />
                                <span>Total: {{ user.total_study_days }} days</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="streakLeaderboard.length === 0" class="text-center text-white/70 py-8">
                    <p>No study streak data available yet.</p>
                    <p class="text-sm mt-2">Start studying daily to appear on this leaderboard!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
