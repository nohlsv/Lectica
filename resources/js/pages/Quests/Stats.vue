<template>
    <Head title="Quest Statistics" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Quest Statistics
                </h2>
                <Link
                    :href="route('quests.index')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Back to Quests
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Overview Statistics -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_completed }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Completed</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.completion_rate }}%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Completion Rate</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.total_xp_earned }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total XP Earned</div>
                            </div>
                            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ stats.daily_streak }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Daily Streak</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Completion by Category -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Completion by Category</h3>
                            <div v-if="completionsByCategory.length === 0" class="text-center py-8">
                                <div class="text-4xl mb-2">📊</div>
                                <p class="text-gray-500 dark:text-gray-400">No quest completions yet</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="category in completionsByCategory"
                                    :key="category.category"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="text-2xl">{{ getCategoryIcon(category.category) }}</div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ getCategoryName(category.category) }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ category.count }} completed
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-yellow-600 dark:text-yellow-400">
                                            {{ category.total_xp }} XP
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Completions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Completions</h3>
                            <div v-if="recentCompletions.length === 0" class="text-center py-8">
                                <div class="text-4xl mb-2">🎯</div>
                                <p class="text-gray-500 dark:text-gray-400">No recent completions</p>
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="completion in recentCompletions"
                                    :key="completion.completed_at"
                                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg"
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
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                              :class="getTypeBadgeColor(completion.type)">
                                            {{ getTypeText(completion.type) }}
                                        </span>
                                        <div class="text-yellow-600 dark:text-yellow-400 font-semibold">
                                            +{{ completion.experience_reward }} XP
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Badges (if applicable) -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quest Achievements</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <!-- First Quest Badge -->
                            <div class="text-center p-4 rounded-lg"
                                 :class="stats.total_completed >= 1
                                    ? 'bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 opacity-50'">
                                <div class="text-2xl mb-2">🎯</div>
                                <div class="text-xs font-medium"
                                     :class="stats.total_completed >= 1 ? 'text-yellow-800 dark:text-yellow-200' : 'text-gray-500'">
                                    First Quest
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Complete 1 quest</div>
                            </div>

                            <!-- Quest Veteran Badge -->
                            <div class="text-center p-4 rounded-lg"
                                 :class="stats.total_completed >= 10
                                    ? 'bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-200 dark:border-blue-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 opacity-50'">
                                <div class="text-2xl mb-2">🏆</div>
                                <div class="text-xs font-medium"
                                     :class="stats.total_completed >= 10 ? 'text-blue-800 dark:text-blue-200' : 'text-gray-500'">
                                    Quest Veteran
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Complete 10 quests</div>
                            </div>

                            <!-- Daily Warrior Badge -->
                            <div class="text-center p-4 rounded-lg"
                                 :class="stats.daily_streak >= 7
                                    ? 'bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 opacity-50'">
                                <div class="text-2xl mb-2">🔥</div>
                                <div class="text-xs font-medium"
                                     :class="stats.daily_streak >= 7 ? 'text-green-800 dark:text-green-200' : 'text-gray-500'">
                                    Daily Warrior
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">7-day streak</div>
                            </div>

                            <!-- XP Collector Badge -->
                            <div class="text-center p-4 rounded-lg"
                                 :class="stats.total_xp_earned >= 1000
                                    ? 'bg-purple-50 dark:bg-purple-900/20 border-2 border-purple-200 dark:border-purple-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 opacity-50'">
                                <div class="text-2xl mb-2">⭐</div>
                                <div class="text-xs font-medium"
                                     :class="stats.total_xp_earned >= 1000 ? 'text-purple-800 dark:text-purple-200' : 'text-gray-500'">
                                    XP Collector
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Earn 1000 XP</div>
                            </div>

                            <!-- Perfect Score Badge -->
                            <div class="text-center p-4 rounded-lg"
                                 :class="stats.completion_rate === 100 && stats.total_completed >= 5
                                    ? 'bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 opacity-50'">
                                <div class="text-2xl mb-2">💯</div>
                                <div class="text-xs font-medium"
                                     :class="stats.completion_rate === 100 && stats.total_completed >= 5 ? 'text-red-800 dark:text-red-200' : 'text-gray-500'">
                                    Perfectionist
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">100% completion</div>
                            </div>

                            <!-- Quest Master Badge -->
                            <div class="text-center p-4 rounded-lg"
                                 :class="stats.total_completed >= 50
                                    ? 'bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-200 dark:border-indigo-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 opacity-50'">
                                <div class="text-2xl mb-2">👑</div>
                                <div class="text-xs font-medium"
                                     :class="stats.total_completed >= 50 ? 'text-indigo-800 dark:text-indigo-200' : 'text-gray-500'">
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
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    stats: Object,
    recentCompletions: Array,
    completionsByCategory: Array,
})

const getCategoryIcon = (category) => {
    const icons = {
        'battle': '⚔️',
        'file_upload': '📁',
        'quiz': '❓',
        'study': '📚'
    }
    return icons[category] || '🎯'
}

const getCategoryName = (category) => {
    const names = {
        'battle': 'Battles',
        'file_upload': 'File Uploads',
        'quiz': 'Quizzes',
        'study': 'Study Sessions'
    }
    return names[category] || 'Other'
}

const getTypeBadgeColor = (type) => {
    const colors = {
        'daily': 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
        'weekly': 'bg-purple-50 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
        'one_time': 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300'
    }
    return colors[type] || colors['daily']
}

const getTypeText = (type) => {
    const types = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'one_time': 'Achievement'
    }
    return types[type] || 'Quest'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
