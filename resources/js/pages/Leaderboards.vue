<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Flame, Trophy, Calendar, Filter, GraduationCap, Crown, Medal, Award, Eye, EyeOff } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { type SharedData, type User } from '@/types';
import axios from 'axios';
import { onMounted, ref, computed, defineComponent } from 'vue';

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
    const index = leaderboard.findIndex(user => user.id === userId);
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
    return leaderboard.find(user => user.id === userId) || null;
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
            <!-- Header Section inspired by Files -->
            <div class="bg-lectica flex max-h-[200px] w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
                <div class="mb-6 flex min-h-[150px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left">
                    <!-- Icon -->
                    <div class="relative flex flex-col items-center gap-2">
                        <div class="animate-floating flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-yellow-500 to-red-600 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] sm:h-28 sm:w-28 md:h-32 md:w-32">
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
                <div class="flex flex-col lg:flex-row items-center justify-between gap-4 mb-6">
                    <!-- College Filter -->
                    <div class="flex flex-col sm:flex-row items-center gap-4">
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
                    </div>

                    <!-- Show All Toggle -->
                    <div class="flex items-center gap-4">
                        <div v-if="loading" class="text-sm text-white/70 pixel-outline">
                            Loading...
                        </div>
                        <Button
                            @click="showAll = !showAll"
                            variant="outline"
                            class="bg-container border-gold/30 text-white hover:bg-gold/20 pixel-outline"
                        >
                            <component :is="showAll ? EyeOff : Eye" class="h-4 w-4 mr-2" />
                            {{ showAll ? 'Show Top Only' : 'Show All Rankings' }}
                        </Button>
                    </div>
                </div>
                <!-- Side-by-Side Leaderboards Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- General Ranking -->
                    <div class="bg-black/50 rounded-lg border border-gold/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-gold/20 to-gold/10 p-4 border-b border-gold/30">
                            <h3 class="text-lg font-bold text-white pixel-outline">🎓 Academic Excellence</h3>
                            <p class="text-sm text-white/70 pixel-outline">Ranked by Level & Experience</p>
                        </div>
                        <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="(user, index) in (showAll ? generalLeaderboard : getTopTen(generalLeaderboard))"
                                :key="user.id"
                                :class="[
                                    'p-3 rounded-lg border transition-all duration-200 hover:scale-[1.02]',
                                    user.id === currentUser?.id
                                        ? 'bg-gold/20 border-gold shadow-lg animate-pulse' 
                                        : 'bg-container border-white/20 hover:border-gold/50',
                                    index < 3 ? 'ring-2 ring-gold/30' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1">
                                            <span :class="[
                                                'font-bold text-sm',
                                                index === 0 ? 'text-yellow-400' :
                                                index === 1 ? 'text-gray-300' :
                                                index === 2 ? 'text-yellow-600' : 'text-gold'
                                            ]">
                                                #{{ index + 1 }}
                                            </span>
                                            <Trophy v-if="index === 0" class="h-4 w-4 text-yellow-400" />
                                            <Medal v-else-if="index === 1" class="h-4 w-4 text-gray-300" />
                                            <Award v-else-if="index === 2" class="h-4 w-4 text-yellow-600" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white text-sm">
                                                {{ user.first_name }} {{ user.last_name }}
                                                <span v-if="user.id === currentUser?.id" class="text-xs text-gold ml-1">(You)</span>
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
                            <div v-if="getUserCard(currentUser?.id || 0, generalLeaderboard) && !showAll" class="mt-4 pt-4 border-t border-gold/30">
                                <div class="p-3 rounded-lg border bg-gold/20 border-gold animate-pulse">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-sm text-gold">#{{ getUserRankInLeaderboard(currentUser?.id || 0, generalLeaderboard) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white text-sm">
                                                    {{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.first_name }} {{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.last_name }}
                                                    <span class="text-xs text-gold ml-1">(You)</span>
                                                </p>
                                                <p class="text-xs text-white/60">{{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.college }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-white">Lv.{{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.level }}</p>
                                            <p class="text-xs text-white/70">{{ getUserCard(currentUser?.id || 0, generalLeaderboard)?.experience }} XP</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="generalLeaderboard.length === 0" class="text-center py-8 text-white/50">
                                <p>No data available</p>
                                <p class="text-xs mt-1">Be the first to appear on this leaderboard!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Multiplayer Ranking -->
                    <div class="bg-black/50 rounded-lg border border-gold/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-gold/20 to-gold/10 p-4 border-b border-gold/30">
                            <h3 class="text-lg font-bold text-white pixel-outline">⚔️ Battle Champions</h3>
                            <p class="text-sm text-white/70 pixel-outline">Ranked by Multiplayer Wins</p>
                        </div>
                        <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="(user, index) in (showAll ? multiplayerLeaderboard : getTopTen(multiplayerLeaderboard))"
                                :key="user.id"
                                :class="[
                                    'p-3 rounded-lg border transition-all duration-200 hover:scale-[1.02]',
                                    user.id === currentUser?.id
                                        ? 'bg-gold/20 border-gold shadow-lg animate-pulse' 
                                        : 'bg-container border-white/20 hover:border-gold/50',
                                    index < 3 ? 'ring-2 ring-gold/30' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1">
                                            <span :class="[
                                                'font-bold text-sm',
                                                index === 0 ? 'text-yellow-400' :
                                                index === 1 ? 'text-gray-300' :
                                                index === 2 ? 'text-yellow-600' : 'text-gold'
                                            ]">
                                                #{{ index + 1 }}
                                            </span>
                                            <Trophy v-if="index === 0" class="h-4 w-4 text-yellow-400" />
                                            <Medal v-else-if="index === 1" class="h-4 w-4 text-gray-300" />
                                            <Award v-else-if="index === 2" class="h-4 w-4 text-yellow-600" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white text-sm">
                                                {{ user.first_name }} {{ user.last_name }}
                                                <span v-if="user.id === currentUser?.id" class="text-xs text-gold ml-1">(You)</span>
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
                            <div v-if="getUserCard(currentUser?.id || 0, multiplayerLeaderboard) && !showAll" class="mt-4 pt-4 border-t border-gold/30">
                                <div class="p-3 rounded-lg border bg-gold/20 border-gold animate-pulse">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-sm text-gold">#{{ getUserRankInLeaderboard(currentUser?.id || 0, multiplayerLeaderboard) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white text-sm">
                                                    {{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.first_name }} {{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.last_name }}
                                                    <span class="text-xs text-gold ml-1">(You)</span>
                                                </p>
                                                <p class="text-xs text-white/60">{{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.college }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-blue-400">{{ getUserCard(currentUser?.id || 0, multiplayerLeaderboard)?.wins }}</p>
                                            <p class="text-xs text-white/70">Wins</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="multiplayerLeaderboard.length === 0" class="text-center py-8 text-white/50">
                                <p>No data available</p>
                                <p class="text-xs mt-1">Be the first to appear on this leaderboard!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Study Streaks Ranking -->
                    <div class="bg-black/50 rounded-lg border border-gold/30 overflow-hidden">
                        <div class="bg-gradient-to-r from-gold/20 to-gold/10 p-4 border-b border-gold/30">
                            <h3 class="text-lg font-bold text-white pixel-outline">🔥 Study Streaks</h3>
                            <p class="text-sm text-white/70 pixel-outline">Ranked by Longest Streak</p>
                        </div>
                        <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="(user, index) in (showAll ? streakLeaderboard : getTopTen(streakLeaderboard))"
                                :key="user.id"
                                :class="[
                                    'p-3 rounded-lg border transition-all duration-200 hover:scale-[1.02]',
                                    user.id === currentUser?.id
                                        ? 'bg-gold/20 border-gold shadow-lg animate-pulse' 
                                        : 'bg-container border-white/20 hover:border-gold/50',
                                    index < 3 ? 'ring-2 ring-gold/30' : ''
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center gap-1">
                                            <span :class="[
                                                'font-bold text-sm',
                                                index === 0 ? 'text-yellow-400' :
                                                index === 1 ? 'text-gray-300' :
                                                index === 2 ? 'text-yellow-600' : 'text-gold'
                                            ]">
                                                #{{ index + 1 }}
                                            </span>
                                            <Trophy v-if="index === 0" class="h-4 w-4 text-yellow-400" />
                                            <Medal v-else-if="index === 1" class="h-4 w-4 text-gray-300" />
                                            <Award v-else-if="index === 2" class="h-4 w-4 text-yellow-600" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white text-sm">
                                                {{ user.first_name }} {{ user.last_name }}
                                                <span v-if="user.id === currentUser?.id" class="text-xs text-gold ml-1">(You)</span>
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
                            <div v-if="getUserCard(currentUser?.id || 0, streakLeaderboard) && !showAll" class="mt-4 pt-4 border-t border-gold/30">
                                <div class="p-3 rounded-lg border bg-gold/20 border-gold animate-pulse">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-sm text-gold">#{{ getUserRankInLeaderboard(currentUser?.id || 0, streakLeaderboard) }}</span>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white text-sm">
                                                    {{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.first_name }} {{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.last_name }}
                                                    <span class="text-xs text-gold ml-1">(You)</span>
                                                </p>
                                                <p class="text-xs text-white/60">{{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.college }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-orange-400">{{ getUserCard(currentUser?.id || 0, streakLeaderboard)?.longest_streak }}d</p>
                                            <p class="text-xs text-white/70">Best Streak</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="streakLeaderboard.length === 0" class="text-center py-8 text-white/50">
                                <p>No data available</p>
                                <p class="text-xs mt-1">Be the first to appear on this leaderboard!</p>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </AppLayout>
</template>
