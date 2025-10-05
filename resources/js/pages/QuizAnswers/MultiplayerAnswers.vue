<template>
    <AppLayout title="Multiplayer Game Answers">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-white leading-tight">
                    Multiplayer Game Answer History
                </h2>
                <Link 
                    :href="route('quiz-answers.index')" 
                    class="text-maroon-400 hover:text-maroon-300 text-sm font-medium"
                >
                    ← Back to All Answers
                </Link>
            </div>
        </template>

        <div class="bg-gradient min-h-screen py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Game Info -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">
                                    Multiplayer Game
                                </h3>
                                <p class="text-sm text-gray-300">
                                    {{ game.file ? 'File: ' + game.file.name : 'Collection: ' + game.collection.name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getGameStatusClass(game.status)"
                                >
                                    {{ formatGameStatus(game.status) }}
                                </span>
                                <div class="text-sm text-gray-400 mt-1">
                                    {{ formatDate(game.created_at) }}
                                </div>
                            </div>
                        </div>

                        <!-- Game Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Questions Answered</div>
                                <div class="text-2xl font-bold text-white">{{ stats.total_questions }}</div>
                            </div>
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Correct Answers</div>
                                <div class="text-2xl font-bold text-green-400">{{ stats.correct_answers }}</div>
                            </div>
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Your Accuracy</div>
                                <div class="text-2xl font-bold" :class="stats.accuracy >= 70 ? 'text-green-400' : 'text-red-400'">
                                    {{ stats.accuracy }}%
                                </div>
                            </div>
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Players</div>
                                <div class="text-2xl font-bold text-blue-400">{{ game.participants.length }}</div>
                            </div>
                        </div>

                        <!-- Participants -->
                        <div class="mt-4">
                            <div class="text-sm font-medium text-gray-300 mb-2">Participants:</div>
                            <div class="flex flex-wrap gap-2">
                                <span 
                                    v-for="participant in game.participants" 
                                    :key="participant.id"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ participant.user.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs for Your Answers vs All Answers -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-600">
                        <nav class="-mb-px flex">
                            <button
                                @click="activeTab = 'your-answers'"
                                :class="activeTab === 'your-answers' 
                                    ? 'border-maroon-500 text-maroon-400' 
                                    : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500'"
                                class="w-1/2 py-2 px-1 text-center border-b-2 font-medium text-sm"
                            >
                                Your Answers
                            </button>
                            <button
                                @click="activeTab = 'all-answers'"
                                :class="activeTab === 'all-answers' 
                                    ? 'border-maroon-500 text-maroon-400' 
                                    : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500'"
                                class="w-1/2 py-2 px-1 text-center border-b-2 font-medium text-sm"
                            >
                                All Player Answers
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Your Answers Tab -->
                        <div v-if="activeTab === 'your-answers'" class="space-y-4">
                            <div 
                                v-for="(answer, index) in answers" 
                                :key="answer.id"
                                class="border-l-4 pl-4 pb-4"
                                :class="answer.is_correct ? 'border-green-500' : 'border-red-500'"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <!-- Question Number and Status -->
                                        <div class="flex items-center mb-2">
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full mr-2">
                                                Question {{ index + 1 }}
                                            </span>
                                            <span 
                                                :class="answer.is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                <svg v-if="answer.is_correct" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                {{ answer.is_correct ? 'Correct' : 'Incorrect' }}
                                            </span>
                                        </div>

                                        <!-- Question -->
                                        <div class="mb-3">
                                            <h5 class="font-medium text-gray-900">{{ answer.quiz.question }}</h5>
                                        </div>

                                        <!-- Quiz Type Badge -->
                                        <div class="mb-2">
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                {{ formatQuizType(answer.quiz.type) }}
                                            </span>
                                        </div>

                                        <!-- Your Answer -->
                                        <div class="mb-2">
                                            <div class="text-sm text-gray-600">Your Answer:</div>
                                            <div class="font-medium" :class="answer.is_correct ? 'text-green-600' : 'text-red-600'">
                                                {{ formatAnswer(answer.user_answer) }}
                                            </div>
                                        </div>

                                        <!-- Correct Answer (if wrong) -->
                                        <div v-if="!answer.is_correct" class="mb-2">
                                            <div class="text-sm text-gray-600">Correct Answer:</div>
                                            <div class="font-medium text-green-600">{{ answer.quiz.correct_answer }}</div>
                                        </div>
                                    </div>

                                    <!-- Timestamp -->
                                    <div class="ml-4 flex-shrink-0 text-sm text-gray-500">
                                        {{ formatTime(answer.answered_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="answers.length === 0" class="text-center py-8">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No answers recorded</h3>
                                    <p class="mt-1 text-sm text-gray-500">You haven't answered any questions in this game yet.</p>
                                </div>
                            </div>
                        </div>

                        <!-- All Answers Tab -->
                        <div v-else class="space-y-6">
                            <div v-for="(userAnswers, userId) in allAnswers" :key="userId" class="border border-gray-200 rounded-lg p-4">
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-900">{{ getUserName(userId) }}</h4>
                                    <div class="text-sm text-gray-500">
                                        {{ userAnswers.length }} questions answered
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div 
                                        v-for="(answer, index) in userAnswers" 
                                        :key="answer.id"
                                        class="border-l-2 pl-3"
                                        :class="answer.is_correct ? 'border-green-400' : 'border-red-400'"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-1">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        Q{{ index + 1 }}: {{ truncateQuestion(answer.quiz.question) }}
                                                    </span>
                                                </div>
                                                <div class="text-sm">
                                                    <span class="text-gray-600">Answer:</span>
                                                    <span :class="answer.is_correct ? 'text-green-600' : 'text-red-600'" class="font-medium ml-1">
                                                        {{ formatAnswer(answer.user_answer) }}
                                                    </span>
                                                    <span 
                                                        :class="answer.is_correct ? 'text-green-600' : 'text-red-600'" 
                                                        class="ml-2 text-xs"
                                                    >
                                                        {{ answer.is_correct ? '✓' : '✗' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-2 text-xs text-gray-400">
                                                {{ formatTime(answer.answered_at) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State for All Answers -->
                            <div v-if="Object.keys(allAnswers).length === 0" class="text-center py-8">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No answers recorded</h3>
                                    <p class="mt-1 text-sm text-gray-500">No players have answered questions in this game yet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

export default {
    name: 'MultiplayerAnswers',
    components: {
        AppLayout,
        Link
    },
    props: {
        game: Object,
        answers: Array,
        allAnswers: Object,
        stats: Object
    },
    data() {
        return {
            activeTab: 'your-answers'
        }
    },
    methods: {
        formatAnswer(answer) {
            if (typeof answer === 'object') {
                return Array.isArray(answer) ? answer.join(', ') : JSON.stringify(answer)
            }
            return answer
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        },
        formatTime(date) {
            return new Date(date).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            })
        },
        formatGameStatus(status) {
            const statusMap = {
                'waiting': 'Waiting for Players',
                'active': 'In Progress',
                'finished': 'Finished',
                'abandoned': 'Abandoned'
            }
            return statusMap[status] || status
        },
        formatQuizType(type) {
            const typeMap = {
                'multiple_choice': 'Multiple Choice',
                'true_false': 'True/False',
                'enumeration': 'Enumeration'
            }
            return typeMap[type] || type
        },
        getGameStatusClass(status) {
            const classes = {
                'waiting': 'bg-orange-100 text-orange-800',
                'active': 'bg-yellow-100 text-yellow-800',
                'finished': 'bg-green-100 text-green-800',
                'abandoned': 'bg-gray-100 text-gray-800'
            }
            return classes[status] || 'bg-gray-100 text-gray-800'
        },
        getUserName(userId) {
            const participant = this.game.participants.find(p => p.user_id == userId)
            return participant ? participant.user.name : 'Unknown User'
        },
        truncateQuestion(question) {
            return question.length > 50 ? question.substring(0, 50) + '...' : question
        }
    }
}
</script>