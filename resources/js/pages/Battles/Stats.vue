<template>
    <Head title="Battle Statistics" />

    <AppLayout>
        <div class="bg-gradient min-h-screen py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Overall Stats -->
                <div class="mx-4 mb-8 grid grid-cols-2 gap-6 sm:grid-cols-4">
                    <div class="overflow-hidden border-2 border-blue-500 bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="pixel-outline text-3xl font-bold text-blue-600">{{ stats.total_battles }}</div>
                            <div class="pixel-outline text-sm text-gray-400">Total Battles</div>
                        </div>
                    </div>

                    <div class="overflow-hidden border-2 border-green-500 bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="pixel-outline text-3xl font-bold text-green-600">{{ stats.won_battles }}</div>
                            <div class="pixel-outline text-sm text-gray-400">Victories</div>
                        </div>
                    </div>

                    <div class="overflow-hidden border-2 border-red-500 bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="pixel-outline text-3xl font-bold text-red-600">{{ stats.lost_battles }}</div>
                            <div class="pixel-outline text-sm text-gray-400">Defeats</div>
                        </div>
                    </div>

                    <div class="overflow-hidden border-2 border-yellow-500 bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="pixel-outline text-3xl font-bold text-yellow-600">{{ stats.active_battles }}</div>
                            <div class="pixel-outline text-sm text-gray-400">Active Battles</div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="mx-4 mb-8 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="pixel-outline mb-4 text-lg font-semibold text-gray-100">Win Rate</h3>
                            <div class="flex items-center">
                                <div class="mr-4 h-4 flex-1 rounded-full bg-gray-200">
                                    <div
                                        class="pixel-outline-icon h-4 rounded-full bg-green-500 transition-all duration-500"
                                        :style="{ width: `${stats.win_rate}%` }"
                                    ></div>
                                </div>
                                <span class="pixel-outline text-lg font-bold text-green-600">{{ stats.win_rate }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-black/50 shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="pixel-outline mb-4 text-lg font-semibold text-gray-100">Answer Accuracy</h3>
                            <div class="flex items-center">
                                <div class="mr-4 h-4 flex-1 rounded-full bg-gray-200">
                                    <div
                                        class="pixel-outline-icon h-4 rounded-full bg-blue-500 transition-all duration-500"
                                        :style="{ width: `${stats.accuracy}%` }"
                                    ></div>
                                </div>
                                <span class="pixel-outline text-lg font-bold text-blue-600">{{ stats.accuracy }}%</span>
                            </div>
                            <div class="pixel-outline mt-2 text-sm text-gray-400">
                                {{ stats.correct_answers }}/{{ stats.total_questions }} correct answers
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Battles -->
                <div class="bg-container mx-4 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <h3 class="pixel-outline mb-2 text-lg font-semibold text-gray-100">Recent Battles</h3>

                        <div v-if="recentBattles.length === 0" class="py-8 text-center">
                            <p class="pixel-outline text-gray-400">No recent battles</p>
                            <Link
                                :href="route('battles.create')"
                                class="pixel-outline mt-4 inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-700"
                            >
                                Start Your First Battle
                            </Link>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="battle in recentBattles"
                                :key="battle.id"
                                class="-mx-4 flex items-center justify-between rounded-lg bg-black/60 pt-4 pr-4 pb-4"
                            >
                                <div class="flex items-center space-x-4 pl-4">
                                    <img
                                        v-if="battle.monster?.image_path"
                                        :src="battle.monster.image_path"
                                        :alt="battle.monster.name"
                                        class="pixel-outline-icon h-16 w-16 rounded-lg object-cover"
                                        @error="$event.target.style.display = 'none'"
                                    />
                                    <div>
                                        <div class="flex items-center space-x-2 font-semibold text-gray-900 dark:text-gray-100">
                                            <span>🏟️ Monster Arena Challenge</span>
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
                    <Link
                        :href="route('battles.create')"
                        class="pixel-outline mr-4 rounded-lg bg-green-500 px-6 py-3 font-bold text-white hover:bg-green-700"
                    >
                        New Battle
                    </Link>
                    <Link
                        :href="route('battles.index')"
                        class="pixel-outline rounded-lg bg-blue-500 px-6 py-3 font-bold text-white hover:bg-blue-700"
                    >
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
