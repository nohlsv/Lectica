<template>
    <Head title="Battles" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Battle History
                </h2>
                <Link
                    :href="route('battles.create')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Start New Battle
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="battles.data.length === 0" class="text-center py-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                No battles yet
                            </h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Start your first battle to test your knowledge!
                            </p>
                            <Link
                                :href="route('battles.create')"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                            >
                                Start Battle
                            </Link>
                        </div>

                        <div v-else class="grid gap-6">
                            <div
                                v-for="battle in battles.data"
                                :key="battle.id"
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <img
                                            v-if="battle.monster?.image_path"
                                            :src="`/images/monsters/${battle.monster.image_path}`"
                                            :alt="battle.monster.name"
                                            class="w-16 h-16 rounded-lg object-cover"
                                            @error="$event.target.style.display = 'none'"
                                        />
                                        <div>
                                            <h3 class="text-lg font-semibold">
                                                vs {{ battle.monster?.name || 'Unknown Monster' }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                File: {{ battle.file?.title || battle.file?.name }}
                                            </p>
                                            <div class="flex space-x-2 mt-2">
                                                <span
                                                    :class="getStatusBadge(battle.status)"
                                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                                >
                                                    {{ battle.status.charAt(0).toUpperCase() + battle.status.slice(1) }}
                                                </span>
                                                <span
                                                    v-if="battle.monster?.difficulty"
                                                    :class="getDifficultyBadge(battle.monster.difficulty).color"
                                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                                >
                                                    {{ getDifficultyBadge(battle.monster.difficulty).text }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex space-x-4 text-sm">
                                            <div>
                                                <span class="text-green-600">❤️ {{ battle.player_hp }}</span>
                                            </div>
                                            <div>
                                                <span class="text-red-600">👹 {{ battle.monster_hp }}</span>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ battle.correct_answers }}/{{ battle.total_questions }} correct
                                        </div>
                                        <div class="mt-2">
                                            <Link
                                                :href="route('battles.show', battle.id)"
                                                :class="battle.status === 'active'
                                                    ? 'bg-green-500 hover:bg-green-700'
                                                    : 'bg-gray-500 hover:bg-gray-700'"
                                                class="text-white font-bold py-1 px-3 rounded text-sm"
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
                                    :class="page === battles.current_page
                                        ? 'bg-blue-500 text-white'
                                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                    class="px-3 py-2 rounded"
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
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps({
    battles: Object,
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

const getDifficultyBadge = (difficulty) => {
    const levels = {
        1: { text: 'Easy', color: 'bg-green-100 text-green-800' },
        2: { text: 'Medium', color: 'bg-yellow-100 text-yellow-800' },
        3: { text: 'Hard', color: 'bg-red-100 text-red-800' },
        4: { text: 'Expert', color: 'bg-purple-100 text-purple-800' },
    }

    return levels[difficulty] || levels[1]
}
</script>
