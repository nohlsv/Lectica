<template>
    <Head title="My Multiplayer Games" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    My Multiplayer Games
                </h2>
                <div class="flex space-x-4">
                    <Link
                        :href="route('multiplayer-games.lobby')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Join Game
                    </Link>
                    <Link
                        :href="route('multiplayer-games.create')"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Game
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Games Grid -->
                <div v-if="games.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="game in games.data"
                        :key="game.id"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow"
                    >
                        <div class="p-6">
                            <!-- Game Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <img
                                            :src="game.monster.image"
                                            :alt="game.monster.name"
                                            class="w-8 h-8 rounded-full"
                                        >
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            vs {{ game.monster.name }}
                                        </h3>
                                        <span
                                            :class="getStatusBadgeClass(game.status)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ getStatusLabel(game.status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        {{ game.source_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Players -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-2">
                                        <div class="flex-shrink-0">
                                            <div class="h-6 w-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-medium">
                                                {{ game.playerOne.first_name.charAt(0) }}
                                            </div>
                                        </div>
                                        <span>{{ game.playerOne.first_name }} {{ game.playerOne.last_name }}</span>
                                        <span v-if="game.status !== 'waiting'" class="text-red-500">
                                            {{ game.player_one_hp }}❤️
                                        </span>
                                    </div>
                                    <span class="text-gray-400">vs</span>
                                    <div class="flex items-center space-x-2">
                                        <span v-if="game.status !== 'waiting'" class="text-red-500">
                                            {{ game.player_two_hp }}❤️
                                        </span>
                                        <span v-if="game.playerTwo">
                                            {{ game.playerTwo.first_name }} {{ game.playerTwo.last_name }}
                                        </span>
                                        <span v-else class="text-gray-500 italic">Waiting for player</span>
                                        <div v-if="game.playerTwo" class="flex-shrink-0">
                                            <div class="h-6 w-6 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium">
                                                {{ game.playerTwo.first_name.charAt(0) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Game Stats -->
                            <div v-if="game.status !== 'waiting'" class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span>Monster: {{ game.monster_hp }}❤️</span>
                                    <span v-if="game.status === 'active'">
                                        {{ game.current_turn === 1 ? game.playerOne.first_name : game.playerTwo?.first_name }}'s turn
                                    </span>
                                </div>
                            </div>

                            <!-- Scores -->
                            <div v-if="game.status !== 'waiting'" class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <div class="flex items-center space-x-4">
                                    <span>{{ game.playerOne.first_name }}: {{ game.player_one_score || 0 }} pts</span>
                                    <span v-if="game.playerTwo">{{ game.playerTwo.first_name }}: {{ game.player_two_score || 0 }} pts</span>
                                </div>
                            </div>

                            <!-- Accuracy -->
                            <div v-if="game.total_questions_p1 > 0 || game.total_questions_p2 > 0" class="mb-4">
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <div>{{ game.playerOne.first_name }}: {{ getAccuracy(game.correct_answers_p1, game.total_questions_p1) }}% accuracy</div>
                                    <div v-if="game.playerTwo && game.total_questions_p2 > 0">
                                        {{ game.playerTwo.first_name }}: {{ getAccuracy(game.correct_answers_p2, game.total_questions_p2) }}% accuracy
                                    </div>
                                </div>
                            </div>

                            <!-- Time -->
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                {{ formatTimeAgo(game.created_at) }}
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="route('multiplayer-games.show', game.id)"
                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium"
                                >
                                    {{ game.status === 'active' ? 'Continue Game' : 'View Details' }} →
                                </Link>
                                <div class="flex space-x-2">
                                    <button
                                        v-if="game.status === 'active'"
                                        @click="abandonGame(game)"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm"
                                    >
                                        Abandon
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No multiplayer games</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        You haven't played any multiplayer games yet. Create one to get started!
                    </p>
                    <div class="mt-6">
                        <Link
                            :href="route('multiplayer-games.create')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Multiplayer Game
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="games.data.length > 0" class="mt-8">
                    <Pagination :links="games.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

interface User {
    id: number
    first_name: string
    last_name: string
}

interface Monster {
    id: string
    name: string
    hp: number
    attack: number
    image: string
}

interface MultiplayerGame {
    id: number
    status: string
    created_at: string
    player_one_hp: number
    player_two_hp: number
    monster_hp: number
    player_one_score: number
    player_two_score: number
    current_turn: number
    correct_answers_p1: number
    correct_answers_p2: number
    total_questions_p1: number
    total_questions_p2: number
    playerOne: User
    playerTwo?: User
    monster: Monster
    source_name: string
}

const props = defineProps<{
    games: {
        data: MultiplayerGame[]
        links: any[]
    }
}>()

const getStatusLabel = (status: string) => {
    const labels = {
        waiting: 'Waiting',
        active: 'Active',
        finished: 'Finished',
        abandoned: 'Abandoned'
    }
    return labels[status] || status
}

const getStatusBadgeClass = (status: string) => {
    const classes = {
        waiting: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        active: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        finished: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        abandoned: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
    return classes[status] || classes.finished
}

const getAccuracy = (correct: number, total: number) => {
    if (total === 0) return 0
    return Math.round((correct / total) * 100)
}

const formatTimeAgo = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))

    if (diffInMinutes < 1) return 'Just now'
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`

    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) return `${diffInHours}h ago`

    const diffInDays = Math.floor(diffInHours / 24)
    return `${diffInDays}d ago`
}

const abandonGame = (game: MultiplayerGame) => {
    if (confirm('Are you sure you want to abandon this game?')) {
        router.post(route('multiplayer-games.abandon', game.id))
    }
}
</script>
