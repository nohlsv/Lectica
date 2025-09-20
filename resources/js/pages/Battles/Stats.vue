<template>
    <Head title="Battle Statistics" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Battle Statistics</h2>
        </template>

        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Overall Stats -->
                <div class="mb-8 grid grid-cols-2 sm:grid-cols-4 gap-6 mx-4">
                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg border-2 border-blue-500">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-blue-600 pixel-outline">{{ stats.total_battles }}</div>
                            <div class="text-sm text-gray-400 pixel-outline">Total Battles</div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg border-2 border-green-500">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-green-600 pixel-outline">{{ stats.won_battles }}</div>
                            <div class="text-sm text-gray-400 pixel-outline">Victories</div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg border-2 border-red-500">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-red-600 pixel-outline">{{ stats.lost_battles }}</div>
                            <div class="text-sm text-gray-400 pixel-outline">Defeats</div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg border-2 border-yellow-500">
                        <div class="p-6 text-center">
                            <div class="text-3xl font-bold text-yellow-600 pixel-outline">{{ stats.active_battles }}</div>
                            <div class="text-sm text-gray-400 pixel-outline">Active Battles</div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="mb-8 mx-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-100 pixel-outline">Win Rate</h3>
                            <div class="flex items-center">
                                <div class="mr-4 h-4 flex-1 rounded-full bg-gray-200">
                                    <div
                                        class="h-4 rounded-full bg-green-500 transition-all duration-500 pixel-outline-icon"
                                        :style="{ width: `${stats.win_rate}%` }"
                                    ></div>
                                </div>
                                <span class="text-lg font-bold text-green-600 pixel-outline">{{ stats.win_rate }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-100 pixel-outline">Answer Accuracy</h3>
                            <div class="flex items-center">
                                <div class="mr-4 h-4 flex-1 rounded-full bg-gray-200">
                                    <div
                                        class="h-4 rounded-full bg-blue-500 transition-all duration-500 pixel-outline-icon"
                                        :style="{ width: `${stats.accuracy}%` }"
                                    ></div>
                                </div>
                                <span class="text-lg font-bold text-blue-600 pixel-outline">{{ stats.accuracy }}%</span>
                            </div>
                            <div class="mt-2 text-sm text-gray-400 pixel-outline">
                                {{ stats.correct_answers }}/{{ stats.total_questions }} correct answers
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Battles -->
                <div class="mx-4 overflow-hidden bg-container shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="mb-2 text-lg font-semibold text-gray-100 pixel-outline">Recent Battles</h3>

                        <div v-if="recentBattles.length === 0" class="py-8 text-center">
                            <p class="text-gray-400 pixel-outline">No recent battles</p>
                            <Link
                                :href="route('battles.create')"
                                class="mt-4 inline-flex items-center pixel-outline rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-700"
                            >
                                Start Your First Battle
                            </Link>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="battle in recentBattles"
                                :key="battle.id"
                                class="flex items-center justify-between rounded-lg bg-black/60 pr-4 pt-4 pb-4 -mx-4"
                            >
                                <div class="flex items-center space-x-4 pl-4">
                                    <img
                                        v-if="battle.monster?.image_path"
                                        :src="battle.monster.image_path"
                                        :alt="battle.monster.name"
                                        class="h-16 w-16 rounded-lg object-cover pixel-outline-icon"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                                            <span>vs {{ battle.monster?.name || 'Unknown Monster' }}</span>
                                            <span :class="getStatusBadge(battle.status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                                {{ battle.status.charAt(0).toUpperCase() + battle.status.slice(1) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ battle.file?.title || battle.file?.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end space-y-2">
                                    <div class="text-right">
                                        <div class="text-sm">{{ battle.correct_answers }}/{{ battle.total_questions }} correct</div>
                                        <div class="text-xs text-gray-500">
                                            {{ new Date(battle.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>

                                    <Link
                                        :href="route('battles.show', battle.id)"
                                        class="rounded bg-blue-500 px-3 py-1 text-sm font-bold text-white hover:bg-blue-700"
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
                    <Link :href="route('battles.create')" class="mr-4 rounded-lg bg-green-500 px-6 py-3 font-bold text-white hover:bg-green-700 pixel-outline">
                        New Battle
                    </Link>
                    <Link :href="route('battles.index')" class="rounded-lg bg-blue-500 px-6 py-3 font-bold text-white hover:bg-blue-700 pixel-outline">
                        View Battles
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recentBattles: Array,
});

const getStatusBadge = (status) => {
    const colors = {
        active: 'bg-blue-100 text-blue-800',
        won: 'bg-green-100 text-green-800',
        lost: 'bg-red-100 text-red-800',
        abandoned: 'bg-gray-100 text-gray-800',
    };

    return colors[status] || colors.abandoned;
};
</script>
