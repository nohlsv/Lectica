<template>
    <Head title="Quest Statistics" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Quest Statistics</h2>
                <Link :href="route('quests.index')" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                    Back to Quests
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Overview Statistics -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900 dark:text-gray-100">Overview</h3>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-lg bg-blue-50 p-4 text-center dark:bg-blue-900/20">
                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_completed }}</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Total Completed</div>
                            </div>
                            <div class="rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20">
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.completion_rate }}%</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Completion Rate</div>
                            </div>
                            <div class="rounded-lg bg-yellow-50 p-4 text-center dark:bg-yellow-900/20">
                                <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.total_xp_earned }}</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Total XP Earned</div>
                            </div>
                            <div class="rounded-lg bg-purple-50 p-4 text-center dark:bg-purple-900/20">
                                <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ stats.daily_streak }}</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Daily Streak</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Completion by Category -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Completion by Category</h3>
                            <div v-if="completionsByCategory.length === 0" class="py-8 text-center">
                                <div class="mb-2 text-4xl">📊</div>
                                <p class="text-gray-500 dark:text-gray-400">No quest completions yet</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="category in completionsByCategory"
                                    :key="category.category"
                                    class="flex items-center justify-between rounded-lg bg-gray-50 p-3 dark:bg-gray-700"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="text-2xl">{{ getCategoryIcon(category.category) }}</div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ getCategoryName(category.category) }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ category.count }} completed</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-yellow-600 dark:text-yellow-400">{{ category.total_xp }} XP</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Completions -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Completions</h3>
                            <div v-if="recentCompletions.length === 0" class="py-8 text-center">
                                <div class="mb-2 text-4xl">🎯</div>
                                <p class="text-gray-500 dark:text-gray-400">No recent completions</p>
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="completion in recentCompletions"
                                    :key="completion.completed_at"
                                    class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-600"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="text-xl">{{ getCategoryIcon(completion.category) }}</div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ completion.title }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ formatDate(completion.completed_at) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="inline-flex items-center rounded px-2 py-1 text-xs font-medium"
                                            :class="getTypeBadgeColor(completion.type)"
                                        >
                                            {{ getTypeText(completion.type) }}
                                        </span>
                                        <div class="font-semibold text-yellow-600 dark:text-yellow-400">+{{ completion.experience_reward }} XP</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Badges (if applicable) -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Quest Achievements</h3>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
                            <!-- First Quest Badge -->
                            <div
                                class="rounded-lg p-4 text-center"
                                :class="
                                    stats.total_completed >= 1
                                        ? 'border-2 border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20'
                                        : 'border-2 border-gray-200 bg-gray-50 opacity-50 dark:border-gray-600 dark:bg-gray-700'
                                "
                            >
                                <div class="mb-2 text-2xl">🎯</div>
                                <div
                                    class="text-xs font-medium"
                                    :class="stats.total_completed >= 1 ? 'text-yellow-800 dark:text-yellow-200' : 'text-gray-500'"
                                >
                                    First Quest
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Complete 1 quest</div>
                            </div>

                            <!-- Quest Veteran Badge -->
                            <div
                                class="rounded-lg p-4 text-center"
                                :class="
                                    stats.total_completed >= 10
                                        ? 'border-2 border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20'
                                        : 'border-2 border-gray-200 bg-gray-50 opacity-50 dark:border-gray-600 dark:bg-gray-700'
                                "
                            >
                                <div class="mb-2 text-2xl">🏆</div>
                                <div
                                    class="text-xs font-medium"
                                    :class="stats.total_completed >= 10 ? 'text-blue-800 dark:text-blue-200' : 'text-gray-500'"
                                >
                                    Quest Veteran
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Complete 10 quests</div>
                            </div>

                            <!-- Daily Warrior Badge -->
                            <div
                                class="rounded-lg p-4 text-center"
                                :class="
                                    stats.daily_streak >= 7
                                        ? 'border-2 border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20'
                                        : 'border-2 border-gray-200 bg-gray-50 opacity-50 dark:border-gray-600 dark:bg-gray-700'
                                "
                            >
                                <div class="mb-2 text-2xl">🔥</div>
                                <div
                                    class="text-xs font-medium"
                                    :class="stats.daily_streak >= 7 ? 'text-green-800 dark:text-green-200' : 'text-gray-500'"
                                >
                                    Daily Warrior
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">7-day streak</div>
                            </div>

                            <!-- XP Collector Badge -->
                            <div
                                class="rounded-lg p-4 text-center"
                                :class="
                                    stats.total_xp_earned >= 1000
                                        ? 'border-2 border-purple-200 bg-purple-50 dark:border-purple-800 dark:bg-purple-900/20'
                                        : 'border-2 border-gray-200 bg-gray-50 opacity-50 dark:border-gray-600 dark:bg-gray-700'
                                "
                            >
                                <div class="mb-2 text-2xl">⭐</div>
                                <div
                                    class="text-xs font-medium"
                                    :class="stats.total_xp_earned >= 1000 ? 'text-purple-800 dark:text-purple-200' : 'text-gray-500'"
                                >
                                    XP Collector
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Earn 1000 XP</div>
                            </div>

                            <!-- Perfect Score Badge -->
                            <div
                                class="rounded-lg p-4 text-center"
                                :class="
                                    stats.completion_rate === 100 && stats.total_completed >= 5
                                        ? 'border-2 border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20'
                                        : 'border-2 border-gray-200 bg-gray-50 opacity-50 dark:border-gray-600 dark:bg-gray-700'
                                "
                            >
                                <div class="mb-2 text-2xl">💯</div>
                                <div
                                    class="text-xs font-medium"
                                    :class="
                                        stats.completion_rate === 100 && stats.total_completed >= 5
                                            ? 'text-red-800 dark:text-red-200'
                                            : 'text-gray-500'
                                    "
                                >
                                    Perfectionist
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">100% completion</div>
                            </div>

                            <!-- Quest Master Badge -->
                            <div
                                class="rounded-lg p-4 text-center"
                                :class="
                                    stats.total_completed >= 50
                                        ? 'border-2 border-indigo-200 bg-indigo-50 dark:border-indigo-800 dark:bg-indigo-900/20'
                                        : 'border-2 border-gray-200 bg-gray-50 opacity-50 dark:border-gray-600 dark:bg-gray-700'
                                "
                            >
                                <div class="mb-2 text-2xl">👑</div>
                                <div
                                    class="text-xs font-medium"
                                    :class="stats.total_completed >= 50 ? 'text-indigo-800 dark:text-indigo-200' : 'text-gray-500'"
                                >
                                    Quest Master
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Complete 50 quests</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentCompletions: Array,
    completionsByCategory: Array,
});

const getCategoryIcon = (category) => {
    const icons = {
        battle: '⚔️',
        file_upload: '📁',
        quiz: '❓',
        study: '📚',
    };
    return icons[category] || '🎯';
};

const getCategoryName = (category) => {
    const names = {
        battle: 'Battles',
        file_upload: 'File Uploads',
        quiz: 'Quizzes',
        study: 'Study Sessions',
    };
    return names[category] || 'Other';
};

const getTypeBadgeColor = (type) => {
    const colors = {
        daily: 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
        weekly: 'bg-purple-50 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
        one_time: 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300',
    };
    return colors[type] || colors['daily'];
};

const getTypeText = (type) => {
    const types = {
        daily: 'Daily',
        weekly: 'Weekly',
        one_time: 'Achievement',
    };
    return types[type] || 'Quest';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
