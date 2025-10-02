<template>
    <Head title="Battles" />

    <AppLayout>
        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-auto max-w-md flex justify-center mb-6 mx-4">
                <h1 class="welcome-banner animate-soft-bounce px-6 py-2 text-center text-2xl leading-tight font-bold pixel-outline">Battles</h1>
            </div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Start Battle Button -->
                <div class="mb-6 flex justify-end mx-4">
                    <Link 
                        :href="route('battles.create')" 
                        class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-green-700 focus:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:outline-none active:bg-green-900 dark:focus:ring-offset-gray-800 pixel-outline"
                    >
                        Start New Battle
                    </Link>
                </div>
            </div>
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden mx-4 bg-container bordershadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="battles.data.length === 0" class="py-8 text-center">
                            <h3 class="text-lg font-medium text-gray-100 pixel-outline">No battles yet</h3>
                            <p class="mt-2 text-sm text-gray-400 pixel-outline">Start your first battle to test your knowledge!</p>
                            <Link
                                :href="route('battles.create')"
                                class="mt-4 inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 pixel-outline text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-800"
                            >
                                Start Battle
                            </Link>
                        </div>

                        <div v-else class="grid gap-6">
                            <div
                                v-for="battle in battles.data"
                                :key="battle.id"
                                class="rounded-lg border-green-500 border-2 bg-black/50 p-6"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <img
                                            v-if="battle.monster?.image_path"
                                            :src="battle.monster.image_path"
                                            :alt="battle.monster.name"
                                            class="h-16 w-16 rounded-lg object-cover pixel-outline-icon"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <div>
                                            <h3 class="text-lg font-semibold pixel-outline">🏟️ Monster Arena Challenge</h3>
                                            <p class="text-sm text-gray-300 pixel-outline">
                                                File: {{ battle.file?.title || battle.file?.name }}
                                            </p>
                                            <div class="mt-2 flex space-x-2">
                                                <span :class="getStatusBadge(battle.status)" class="rounded-full px-2 py-1 text-xs font-medium pixel-outline-icon">
                                                    {{ battle.status.charAt(0).toUpperCase() + battle.status.slice(1) }}
                                                </span>
                                                <span
                                                    v-if="battle.monster?.difficulty"
                                                    :class="getDifficultyBadge(battle.monster.difficulty).color"
                                                    class="rounded-full px-2 py-1 text-xs font-medium pixel-outline-icon"
                                                >
                                                    {{ getDifficultyBadge(battle.monster.difficulty).text }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex space-x-4 text-sm">
                                            <div>
                                                <span class="text-green-600 pixel-outline">❤️ {{ battle.player_hp }}</span>
                                            </div>
                                            <div>
                                                <span class="text-red-600 pixel-outline">👹 {{ battle.monster_hp }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500 pixel-outline">
                                            {{ battle.correct_answers }}/{{ battle.total_questions }} correct
                                        </div>
                                        <div class="mt-2">
                                            <Link
                                                :href="route('battles.show', battle.id)"
                                                :class="
                                                    battle.status === 'active' ? 'bg-green-500 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-700'
                                                "
                                                class="rounded px-3 py-1 text-sm font-bold text-white pixel-outline"
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
        1: { text: 'Easy', color: 'bg-green-100 text-green-800' },
        2: { text: 'Medium', color: 'bg-yellow-100 text-yellow-800' },
        3: { text: 'Hard', color: 'bg-red-100 text-red-800' },
        4: { text: 'Expert', color: 'bg-purple-100 text-purple-800' },
    };

    return levels[difficulty] || levels[1];
};
</script>
