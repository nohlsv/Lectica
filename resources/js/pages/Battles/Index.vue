<template>
    <Head title="Battles" />

    <AppLayout>
        <div class="bg-gradient min-h-screen py-6 sm:py-12">
            <div class="mx-auto mb-4 flex max-w-md justify-center sm:mb-6">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline px-4 py-2 text-center text-lg leading-tight font-bold sm:px-6 sm:text-xl lg:text-2xl">Battles</h1>
            </div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Start Battle Button -->
                <div class="mx-2 mb-4 flex justify-center sm:mx-4 sm:mb-6 sm:justify-end">
                    <Link
                        :href="route('battles.create')"
                        class="pixel-outline inline-flex items-center rounded-md border border-transparent bg-green-600 px-3 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none active:bg-green-900 dark:focus:ring-offset-gray-800 sm:px-4 sm:text-sm"
                    >
                        Start New Battle
                    </Link>
                </div>
            </div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-container bordershadow-sm mx-2 overflow-hidden sm:mx-4 sm:rounded-lg">
                    <div class="p-3 text-gray-900 dark:text-gray-100 sm:p-6">
                        <div v-if="battles.data.length === 0" class="py-8 text-center">
                            <h3 class="pixel-outline text-lg font-medium text-gray-100">No battles yet</h3>
                            <p class="pixel-outline mt-2 text-sm text-gray-400">Start your first battle to test your knowledge!</p>
                            <Link
                                :href="route('battles.create')"
                                class="pixel-outline mt-4 inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-800"
                            >
                                Start Battle
                            </Link>
                        </div>

                        <div v-else class="grid gap-3 sm:gap-6">
                            <div v-for="battle in battles.data" :key="battle.id" class="rounded-lg border-2 border-green-500 bg-black/50 p-3 sm:p-6">
                                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                    <!-- Main Info Section -->
                                    <div class="flex items-center space-x-2 sm:space-x-4">
                                        <img
                                            v-if="battle.monster?.image_path"
                                            :src="battle.monster.image_path"
                                            :alt="battle.monster.name"
                                            class="pixel-outline-icon h-12 w-12 rounded-lg object-cover sm:h-16 sm:w-16"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <h3 class="pixel-outline text-sm font-semibold sm:text-lg">🏟️ Monster Arena Challenge</h3>
                                            <p class="pixel-outline text-xs text-gray-300 sm:text-sm truncate">File: {{ battle.file?.title || battle.file?.name }}</p>
                                            <div class="mt-1 flex flex-wrap gap-1 sm:mt-2 sm:space-x-2">
                                                <span
                                                    :class="getStatusBadge(battle.status)"
                                                    class="pixel-outline-icon rounded-full px-2 py-1 text-xs font-medium"
                                                >
                                                    {{ battle.status.charAt(0).toUpperCase() + battle.status.slice(1) }}
                                                </span>
                                                <span
                                                    v-if="battle.difficulty"
                                                    :class="getDifficultyBadge(battle.difficulty).color"
                                                    class="pixel-outline-icon rounded-full px-2 py-1 text-xs font-medium"
                                                >
                                                    {{ getDifficultyBadge(battle.difficulty).text }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Stats Section - All stats on one line for mobile, right-aligned on desktop -->
                                    <div class="flex flex-col sm:items-end sm:text-right">
                                        <!-- All stats and button on one row -->
                                        <div class="flex items-center justify-between space-x-2 sm:justify-end sm:space-x-4">
                                            <div class="flex items-center space-x-2 sm:space-x-4">
                                                <span class="pixel-outline text-green-600 text-xs sm:text-sm">❤️ {{ battle.player_hp }}</span>
                                                <span class="pixel-outline text-red-600 text-xs sm:text-sm">👹 {{ battle.monster_hp }}</span>
                                                <span class="pixel-outline text-xs text-gray-500">
                                                    {{ battle.correct_answers }}/{{ battle.total_questions }} correct
                                                </span>
                                            </div>
                                            <Link
                                                :href="route('battles.show', battle.id)"
                                                :class="
                                                    battle.status === 'active' ? 'bg-green-500 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-700'
                                                "
                                                class="pixel-outline rounded px-2 py-1 text-xs font-bold text-white sm:px-3 sm:text-sm"
                                            >
                                                {{ battle.status === 'active' ? 'Continue' : 'View' }}
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="battles.last_page > 1" class="mt-6 flex justify-center">
                            <div class="flex space-x-2">
                                <Link
                                    v-for="page in battles.last_page"
                                    :key="page"
                                    :href="route('battles.index', { page })"
                                    :class="page === battles.current_page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                    class="rounded px-3 py-2"
                                >
                                    {{ page }}
                                </Link>
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

defineProps({
    battles: Object,
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

const getDifficultyBadge = (difficulty) => {
    const levels = {
        easy: { text: 'Easy', color: 'bg-green-100 text-green-800' },
        medium: { text: 'Medium', color: 'bg-yellow-100 text-yellow-800' },
        hard: { text: 'Hard', color: 'bg-red-100 text-red-800' },
        // Legacy support for numeric values (if any exist)
        1: { text: 'Easy', color: 'bg-green-100 text-green-800' },
        2: { text: 'Medium', color: 'bg-yellow-100 text-yellow-800' },
        3: { text: 'Hard', color: 'bg-red-100 text-red-800' },
        4: { text: 'Expert', color: 'bg-purple-100 text-purple-800' },
    };

    return levels[difficulty] || levels.easy;
};
</script>
