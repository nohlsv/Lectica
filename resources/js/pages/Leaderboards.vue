<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type SharedData, type User } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Award, Crown, Eye, EyeOff, Filter, GraduationCap, Medal, Trophy } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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
const selectedCollege = ref('all');
const loading = ref(false);
const showAll = ref(false);

// Get current user
const page = usePage<SharedData>();
const currentUser = computed(() => page.props.auth.user as User);

// Utility functions for user ranking and filtering
const getUserRankInLeaderboard = (userId: number, leaderboard: any[]) => {
    const index = leaderboard.findIndex((user) => user.id === userId);
    return index !== -1 ? index + 1 : null;
};

const isUserInTopTen = (userId: number, leaderboard: any[]) => {
    const rank = getUserRankInLeaderboard(userId, leaderboard);
    return rank !== null && rank <= 10;
};

const getTopThree = (leaderboard: any[]) => leaderboard.slice(0, 3);
const getTopTen = (leaderboard: any[]) => leaderboard.slice(0, 10);
const getAllExceptTopThree = (leaderboard: any[]) => leaderboard.slice(3);

// Get user's card if not in top 10
const getUserCard = (userId: number, leaderboard: any[]) => {
    if (isUserInTopTen(userId, leaderboard)) return null;
    return leaderboard.find((user) => user.id === userId) || null;
};

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
        await Promise.all([loadGeneralLeaderboard(), loadMultiplayerLeaderboard(), loadStreakLeaderboard()]);
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
        <!-- Header Section inspired by Files -->
        <div class="bg-lectica flex max-h-[200px] w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
            <div
                class="mb-6 flex min-h-[150px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left"
            >
                <!-- Icon -->
                <div class="relative flex flex-col items-center gap-2">
                    <div
                        class="animate-floating flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-yellow-500 to-red-600 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] sm:h-28 sm:w-28 md:h-32 md:w-32"
                    >
                        <Crown class="h-10 w-10 text-white sm:h-14 sm:w-14 md:h-16 md:w-16" />
                    </div>
                </div>
                <!-- Title -->
                <div>
                    <h1 class="text-2xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-3xl">
                        🏆 Hall of Champions
                    </h1>
                    <p class="text-lg text-white/90 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-xl">
                        Compete with the best scholars across all colleges
                    </p>
                </div>
            </div>
            <!-- Divider -->
            <hr class="-mx-4 h-2 border-2 border-black bg-yellow-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
        </div>

        <!-- Main Content -->
        <div class="bg-gradient flex h-full flex-1 flex-col gap-6 px-4 pt-6 pb-0 lg:p-8">
            <!-- Controls Section -->
            <div class="mb-6 flex flex-col items-center justify-between gap-4 lg:flex-row">
                <!-- College Filter -->
                <div class="flex flex-col items-center gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <Filter class="text-gold h-4 w-4" />
                        <span class="pixel-outline text-sm font-bold text-white">Filter by College:</span>
                    </div>
                    <Select v-model="selectedCollege" @update:modelValue="onCollegeChange">
                        <SelectTrigger class="bg-container border-gold/30 pixel-outline w-64 text-white">
                            <SelectValue placeholder="Select College" />
                        </SelectTrigger>
                        <SelectContent class="bg-container border-gold/30">
                            <SelectItem value="all" class="hover:bg-gold/20 text-white">
                                <div class="flex items-center gap-2">
                                    <GraduationCap class="h-4 w-4" />
                                    All Colleges
                                </div>
                            </SelectItem>
                            <SelectItem v-for="college in colleges" :key="college" :value="college" class="hover:bg-gold/20 text-white">
                                {{ college }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Show All Toggle -->
                <div class="flex items-center gap-4">
                    <div v-if="loading" class="pixel-outline text-sm text-white/70">Loading...</div>
                    <Button
                        @click="showAll = !showAll"
                        variant="outline"
                        class="bg-container border-gold/30 hover:bg-gold/20 pixel-outline text-white"
                    >
                        <component :is="showAll ? EyeOff : Eye" class="mr-2 h-4 w-4" />
                        {{ showAll ? 'Show Top Only' : 'Show All Rankings' }}
                    </Button>
                </div>
            </div>
            <!-- Side-by-Side Leaderboards Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- General Ranking -->
                <div class="border-gold/30 overflow-hidden rounded-lg border bg-black/50">
                    <div class="from-gold/20 to-gold/10 border-gold/30 border-b bg-gradient-to-r p-4">
                        <h3 class="pixel-outline text-lg font-bold text-white">🎓 Academic Excellence</h3>
                        <p class="pixel-outline text-sm text-white/70">Ranked by Level & Experience</p>
                    </div>
                    <div class="max-h-96 space-y-2 overflow-y-auto p-4">
                        <div
                            v-for="(user, index) in showAll ? generalLeaderboard : getTopTen(generalLeaderboard)"
                            :key="user.id"
                            :class="[
                                'rounded-lg border p-3 transition-all duration-200 hover:scale-[1.02]',
                                user.id === currentUser?.id
                                    ? 'bg-gold/20 border-gold animate-pulse shadow-lg'
                                    : 'bg-container hover:border-gold/50 border-white/20',
                                index < 3 ? 'ring-gold/30 ring-2' : '',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1">
                                        <span
                                            :class="[
                                                'text-sm font-bold',
                                                index === 0
                                                    ? 'text-yellow-400'
                                                    : index === 1
                                                      ? 'text-gray-300'
                                                      : index === 2
                                                        ? 'text-yellow-600'
                                                        : 'text-gold',
                                            ]"
                                        >
                                            #{{ index + 1 }}
                                        </span>
                                        <Trophy v-if="index === 0" class="h-4 w-4 text-yellow-400" />
                                        <Medal v-else-if="index === 1" class="h-4 w-4 text-gray-300" />
                                        <Award v-else-if="index === 2" class="h-4 w-4 text-yellow-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">
                                            {{ user.first_name }} {{ user.last_name }}
                                            <span v-if="user.id === currentUser?.id" class="text-gold ml-1 text-xs">(You)</span>
                                        </p>
                                        <p class="text-xs text-white/60">{{ user.college }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-white">Lv.{{ user.level }}</p>
                                    <p class="text-xs text-white/70">{{ user.experience }} XP</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current User Card (if not in top 10) -->
                        <div v-if="getUserCard(currentUser?.id || 0, generalLeaderboard) && !showAll" class="border-gold/30 mt-4 border-t pt-4">
                            <div class="bg-gold/20 border-gold animate-pulse rounded-lg border p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1">
                                            <span class="text-gold text-sm font-bold"
                                                >#{{ getUserRankInLeaderboard(currentUser?.id || 0, generalLeaderboard) }}</span
                                            >
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white">
                                                {{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.first_name }}
                                                {{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.last_name }}
                                                <span class="text-gold ml-1 text-xs">(You)</span>
                                            </p>
                                            <p class="text-xs text-white/60">{{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.college }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-white">
                                            Lv.{{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.level }}
                                        </p>
                                        <p class="text-xs text-white/70">
                                            {{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.experience }} XP
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="generalLeaderboard.length === 0" class="py-8 text-center text-white/50">
                            <p>No data available</p>
                            <p class="mt-1 text-xs">Be the first to appear on this leaderboard!</p>
                        </div>
                    </div>
                </div>

                <!-- Multiplayer Ranking -->
                <div class="border-gold/30 overflow-hidden rounded-lg border bg-black/50">
                    <div class="from-gold/20 to-gold/10 border-gold/30 border-b bg-gradient-to-r p-4">
                        <h3 class="pixel-outline text-lg font-bold text-white">⚔️ Battle Champions</h3>
                        <p class="pixel-outline text-sm text-white/70">Ranked by Multiplayer Wins</p>
                    </div>
                    <div class="max-h-96 space-y-2 overflow-y-auto p-4">
                        <div
                            v-for="(user, index) in showAll ? multiplayerLeaderboard : getTopTen(multiplayerLeaderboard)"
                            :key="user.id"
                            :class="[
                                'rounded-lg border p-3 transition-all duration-200 hover:scale-[1.02]',
                                user.id === currentUser?.id
                                    ? 'bg-gold/20 border-gold animate-pulse shadow-lg'
                                    : 'bg-container hover:border-gold/50 border-white/20',
                                index < 3 ? 'ring-gold/30 ring-2' : '',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1">
                                        <span
                                            :class="[
                                                'text-sm font-bold',
                                                index === 0
                                                    ? 'text-yellow-400'
                                                    : index === 1
                                                      ? 'text-gray-300'
                                                      : index === 2
                                                        ? 'text-yellow-600'
                                                        : 'text-gold',
                                            ]"
                                        >
                                            #{{ index + 1 }}
                                        </span>
                                        <Trophy v-if="index === 0" class="h-4 w-4 text-yellow-400" />
                                        <Medal v-else-if="index === 1" class="h-4 w-4 text-gray-300" />
                                        <Award v-else-if="index === 2" class="h-4 w-4 text-yellow-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">
                                            {{ user.first_name }} {{ user.last_name }}
                                            <span v-if="user.id === currentUser?.id" class="text-gold ml-1 text-xs">(You)</span>
                                        </p>
                                        <p class="text-xs text-white/60">{{ user.college }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-blue-400">{{ user.wins }}</p>
                                    <p class="text-xs text-white/70">Wins</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current User Card (if not in top 10) -->
                        <div v-if="getUserCard(currentUser?.id || 0, multiplayerLeaderboard) && !showAll" class="border-gold/30 mt-4 border-t pt-4">
                            <div class="bg-gold/20 border-gold animate-pulse rounded-lg border p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1">
                                            <span class="text-gold text-sm font-bold"
                                                >#{{ getUserRankInLeaderboard(currentUser?.id || 0, multiplayerLeaderboard) }}</span
                                            >
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white">
                                                {{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.first_name }}
                                                {{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.last_name }}
                                                <span class="text-gold ml-1 text-xs">(You)</span>
                                            </p>
                                            <p class="text-xs text-white/60">
                                                {{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.college }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-blue-400">
                                            {{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.wins }}
                                        </p>
                                        <p class="text-xs text-white/70">Wins</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="multiplayerLeaderboard.length === 0" class="py-8 text-center text-white/50">
                            <p>No data available</p>
                            <p class="mt-1 text-xs">Be the first to appear on this leaderboard!</p>
                        </div>
                    </div>
                </div>

                <!-- Study Streaks Ranking -->
                <div class="border-gold/30 overflow-hidden rounded-lg border bg-black/50">
                    <div class="from-gold/20 to-gold/10 border-gold/30 border-b bg-gradient-to-r p-4">
                        <h3 class="pixel-outline text-lg font-bold text-white">🔥 Study Streaks</h3>
                        <p class="pixel-outline text-sm text-white/70">Ranked by Longest Streak</p>
                    </div>
                    <div class="max-h-96 space-y-2 overflow-y-auto p-4">
                        <div
                            v-for="(user, index) in showAll ? streakLeaderboard : getTopTen(streakLeaderboard)"
                            :key="user.id"
                            :class="[
                                'rounded-lg border p-3 transition-all duration-200 hover:scale-[1.02]',
                                user.id === currentUser?.id
                                    ? 'bg-gold/20 border-gold animate-pulse shadow-lg'
                                    : 'bg-container hover:border-gold/50 border-white/20',
                                index < 3 ? 'ring-gold/30 ring-2' : '',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1">
                                        <span
                                            :class="[
                                                'text-sm font-bold',
                                                index === 0
                                                    ? 'text-yellow-400'
                                                    : index === 1
                                                      ? 'text-gray-300'
                                                      : index === 2
                                                        ? 'text-yellow-600'
                                                        : 'text-gold',
                                            ]"
                                        >
                                            #{{ index + 1 }}
                                        </span>
                                        <Trophy v-if="index === 0" class="h-4 w-4 text-yellow-400" />
                                        <Medal v-else-if="index === 1" class="h-4 w-4 text-gray-300" />
                                        <Award v-else-if="index === 2" class="h-4 w-4 text-yellow-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">
                                            {{ user.first_name }} {{ user.last_name }}
                                            <span v-if="user.id === currentUser?.id" class="text-gold ml-1 text-xs">(You)</span>
                                        </p>
                                        <p class="text-xs text-white/60">{{ user.college }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-orange-400">{{ user.longest_streak }}d</p>
                                    <p class="text-xs text-white/70">Best Streak</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current User Card (if not in top 10) -->
                        <div v-if="getUserCard(currentUser?.id || 0, streakLeaderboard) && !showAll" class="border-gold/30 mt-4 border-t pt-4">
                            <div class="bg-gold/20 border-gold animate-pulse rounded-lg border p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1">
                                            <span class="text-gold text-sm font-bold"
                                                >#{{ getUserRankInLeaderboard(currentUser?.id || 0, streakLeaderboard) }}</span
                                            >
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white">
                                                {{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.first_name }}
                                                {{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.last_name }}
                                                <span class="text-gold ml-1 text-xs">(You)</span>
                                            </p>
                                            <p class="text-xs text-white/60">{{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.college }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-orange-400">
                                            {{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.longest_streak }}d
                                        </p>
                                        <p class="text-xs text-white/70">Best Streak</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="streakLeaderboard.length === 0" class="py-8 text-center text-white/50">
                            <p>No data available</p>
                            <p class="mt-1 text-xs">Be the first to appear on this leaderboard!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
