<template>
    <Head title="Battle Statistics" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Battle Statistics
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Overall Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ stats.total_battles }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Battles</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-green-600">{{ stats.won_battles }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Victories</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-red-600">{{ stats.lost_battles }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Defeats</div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-yellow-600">{{ stats.active_battles }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Active Battles</div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Win Rate</h3>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-4 mr-4">
                                    <div
                                        class="bg-green-500 h-4 rounded-full transition-all duration-500"
                                        :style="{ width: `${stats.win_rate}%` }"
                                    ></div>
                                </div>
                                <span class="text-lg font-bold text-green-600">{{ stats.win_rate }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Answer Accuracy</h3>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-4 mr-4">
                                    <div
                                        class="bg-blue-500 h-4 rounded-full transition-all duration-500"
                                        :style="{ width: `${stats.accuracy}%` }"
                                    ></div>
                                </div>
                                <span class="text-lg font-bold text-blue-600">{{ stats.accuracy }}%</span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                {{ stats.correct_answers }}/{{ stats.total_questions }} correct answers
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Battles -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recent Battles</h3>

                        <div v-if="recentBattles.length === 0" class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No recent battles</p>
                            <Link
                                :href="route('battles.create')"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Start Your First Battle
                            </Link>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="battle in recentBattles"
                                :key="battle.id"
                                class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div class="flex items-center space-x-4">
                                    <img
                                        v-if="battle.monster?.image_path"
                                        :src="`/images/monsters/${battle.monster.image_path}`"
                                        :alt="battle.monster.name"
                                        class="w-16 h-16 rounded-lg object-cover"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            vs {{ battle.monster?.name || 'Unknown Monster' }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ battle.file?.title || battle.file?.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <div class="text-sm">
                                            {{ battle.correct_answers }}/{{ battle.total_questions }} correct
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ new Date(battle.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>

                                    <span
                                        :class="getStatusBadge(battle.status)"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ battle.status.charAt(0).toUpperCase() + battle.status.slice(1) }}
                                    </span>

                                    <Link
                                        :href="route('battles.show', battle.id)"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm"
                                    >
                                        View
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 text-center">
                    <Link
                        :href="route('battles.create')"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg mr-4"
                    >
                        Start New Battle
                    </Link>
                    <Link
                        :href="route('battles.index')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg"
                    >
                        View All Battles
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
    stats: Object,
    recentBattles: Array,
})

const getStatusBadge = (status) => {
    const colors = {
        active: 'bg-blue-100 text-blue-800',
        won: 'bg-green-100 text-green-800',
        lost: 'bg-red-100 text-red-800',
        abandoned: 'bg-gray-100 text-gray-800',
    }

    return colors[status] || colors.abandoned
}
</script>
