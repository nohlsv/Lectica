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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        </div>
                    </div>
                </div>

                <!-- Question Timeline -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-white mb-4">Question Timeline</h4>
                        
                        <div class="space-y-4">
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
                                            <span class="bg-gray-700 text-gray-200 text-xs font-medium px-2.5 py-0.5 rounded-full mr-2">
                                                Question {{ index + 1 }}
                                            </span>
                                            <span 
                                                :class="answer.is_correct ? 'bg-green-900/50 text-green-300 border border-green-400' : 'bg-red-900/50 text-red-300 border border-red-400'" 
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
                                            <h5 class="font-medium text-white">{{ answer.quiz.question }}</h5>
                                        </div>

                                        <!-- Quiz Type Badge -->
                                        <div class="mb-2">
                                            <span class="bg-blue-600 text-blue-100 text-xs font-medium px-2.5 py-0.5 rounded">
                                                {{ formatQuizType(answer.quiz.type) }}
                                            </span>
                                        </div>

                                        <!-- Your Answer -->
                                        <div class="mb-2">
                                            <div class="text-sm text-gray-300">Your Answer:</div>
                                            <div class="font-medium" :class="answer.is_correct ? 'text-green-400' : 'text-red-400'">
                                                {{ formatAnswer(answer.user_answer) }}
                                            </div>
                                        </div>

                        <!-- Correct Answer (if wrong) -->
                        <div v-if="!answer.is_correct" class="mb-2">
                            <div class="text-sm text-gray-300">Correct Answer:</div>
                            <div class="font-medium text-green-400">{{ formatAnswer(answer.correct_answer) }}</div>
                        </div>                                        <!-- Options for Multiple Choice -->
                                        <div v-if="answer.quiz.type === 'multiple_choice' && answer.quiz.options" class="mb-2">
                                            <div class="text-sm text-gray-300">Options:</div>
                                            <div class="grid grid-cols-2 gap-1 text-sm">
                                                <div 
                                                    v-for="(option, optIndex) in answer.quiz.options" 
                                                    :key="optIndex"
                                                    class="p-1 rounded"
                                                    :class="isCorrectOption(option, answer.quiz) ? 'bg-green-800/50 text-green-300' : 'bg-gray-800/50 text-gray-300'"
                                                >
                                                    {{ String.fromCharCode(65 + optIndex) }}. {{ option }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Explanation if available -->
                                        <div v-if="answer.quiz.explanation" class="mb-2">
                                            <div class="text-sm text-gray-300">Explanation:</div>
                                            <div class="text-sm text-gray-200 bg-blue-900/50 p-2 rounded">
                                                {{ answer.quiz.explanation }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timestamp -->
                                    <div class="ml-4 flex-shrink-0 text-sm text-gray-400">
                                        {{ formatTime(answer.answered_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="answers.length === 0" class="text-center py-8">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-white">No answers recorded</h3>
                                    <p class="mt-1 text-sm text-gray-400">You haven't answered any questions in this game yet.</p>
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

    methods: {
        formatAnswer(answer) {
            if (typeof answer === 'object') {
                if (Array.isArray(answer)) {
                    // For enumeration answers, format nicely
                    const validAnswers = answer.filter(a => a && a.toString().trim());
                    if (validAnswers.length === 0) return 'No answer provided';
                    if (validAnswers.length === 1) return validAnswers[0];
                    return validAnswers.map((a, index) => `${index + 1}. ${a}`).join(' | ');
                }
                return JSON.stringify(answer);
            }
            return answer?.toString() || 'No answer provided';
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
        isCorrectOption(option, quiz) {
            if (quiz.answers && Array.isArray(quiz.answers)) {
                return quiz.answers.includes(option);
            }
            return quiz.answers === option;
        }
    }
}
</script>