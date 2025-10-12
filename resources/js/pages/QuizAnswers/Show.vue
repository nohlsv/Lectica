<template>
    <AppLayout title="Quiz Answer Details">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-white leading-tight">
                    Quiz Answer Details
                </h2>
                <Link 
                    :href="route('quiz-answers.index')" 
                    class="text-yellow-400 hover:text-yellow-300 text-sm font-medium"
                >
                    ← Back to Answer History
                </Link>
            </div>
        </template>

        <div class="bg-gradient min-h-screen py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Answer Result Card -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div 
                                    class="w-12 h-12 rounded-full flex items-center justify-center border-2"
                                    :class="answer.is_correct ? 'bg-green-900/50 border-green-400' : 'bg-red-900/50 border-red-400'"
                                >
                                    <svg 
                                        v-if="answer.is_correct" 
                                        class="w-6 h-6 text-green-400" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg 
                                        v-else 
                                        class="w-6 h-6 text-red-400" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white" :class="answer.is_correct ? 'text-green-400' : 'text-red-400'">
                                        {{ answer.is_correct ? 'Correct Answer' : 'Incorrect Answer' }}
                                    </h3>
                                    <p class="text-sm text-gray-300">
                                        Answered on {{ formatDate(answer.answered_at) }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Quiz Type Badge -->
                            <span class="bg-blue-900/50 text-blue-300 text-sm font-medium px-3 py-1 rounded-full border border-blue-400">
                                {{ formatQuizType(answer.quiz.type) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Context Information -->
                <div v-if="context" class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-white mb-4">Context</h4>
                        
                        <!-- Battle Context -->
                        <div v-if="answer.context_type === 'battle'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="font-medium text-white">Battle vs {{ context.monster.name }}</h5>
                                    <p class="text-sm text-gray-300">
                                        {{ context.file ? 'File: ' + context.file.name : 'Collection: ' + context.collection.name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getBattleStatusClass(context.status)"
                                    >
                                        {{ formatBattleStatus(context.status) }}
                                    </span>
                                    <div class="text-sm text-gray-300 mt-1">
                                        <Link 
                                            :href="route('battles.show', context.id)"
                                            class="text-yellow-400 hover:text-yellow-300"
                                        >
                                            View Battle
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multiplayer Context -->
                        <div v-else-if="answer.context_type === 'multiplayer'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="font-medium text-white">Multiplayer Game</h5>
                                    <p class="text-sm text-gray-300">
                                        {{ context.file ? 'File: ' + context.file.name : 'Collection: ' + context.collection.name }}
                                    </p>
                                    <div class="mt-2">
                                        <span class="text-sm text-gray-300">Players: </span>
                                        <span class="text-sm font-medium text-white">{{ context.participants.length }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getGameStatusClass(context.status)"
                                    >
                                        {{ formatGameStatus(context.status) }}
                                    </span>
                                    <div class="text-sm text-gray-300 mt-1">
                                        <Link 
                                            :href="route('multiplayer-games.show', context.id)"
                                            class="text-yellow-400 hover:text-yellow-300"
                                        >
                                            View Game
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Details -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-white mb-4">Question</h4>
                        
                        <div class="mb-6">
                            <div class="text-lg font-medium text-white mb-2">
                                {{ answer.quiz.question }}
                            </div>
                            
                            <!-- File Source -->
                            <div v-if="answer.quiz.file" class="text-sm text-gray-300">
                                Source: {{ answer.quiz.file.name }}
                            </div>
                        </div>

                        <!-- Answer Summary -->
                        <div class="mb-6 p-4 bg-gray-800/50 rounded-lg border border-gray-600">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Your Answer -->
                                <div>
                                    <h5 class="font-medium text-white mb-2">Your Answer:</h5>
                                    <div class="font-medium" :class="answer.is_correct ? 'text-green-400' : 'text-red-400'">
                                        {{ formatAnswer(answer.user_answer) }}
                                    </div>
                                </div>
                                
                                <!-- Correct Answer -->
                                <div v-if="!answer.is_correct">
                                    <h5 class="font-medium text-white mb-2">Correct Answer:</h5>
                                    <div class="font-medium text-green-400">
                                        {{ formatAnswer(answer.correct_answer) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multiple Choice Options -->
                        <div v-if="answer.quiz.type === 'multiple_choice' && answer.quiz.options" class="mb-6">
                            <h5 class="font-medium text-white mb-3">Options:</h5>
                            <div class="space-y-2">
                                <div 
                                    v-for="(option, index) in answer.quiz.options" 
                                    :key="index"
                                    class="flex items-center p-3 rounded-lg border-2"
                                    :class="getOptionClass(option, answer.correct_answer, answer.user_answer)"
                                >
                                    <div class="flex items-center justify-center w-6 h-6 rounded-full border-2 mr-3"
                                         :class="getOptionIconClass(option, answer.correct_answer, answer.user_answer)"
                                    >
                                        <span class="text-xs font-bold">{{ String.fromCharCode(65 + index) }}</span>
                                    </div>
                                    <span class="font-medium">{{ option }}</span>
                                    
                                    <!-- Indicators -->
                                    <div class="ml-auto flex space-x-2">
                                        <span v-if="option === answer.user_answer" class="text-xs bg-blue-900/50 text-blue-300 px-2 py-1 rounded border border-blue-400">
                                            Your Answer
                                        </span>
                                        <span v-if="isCorrectOption(option, answer.quiz)" class="text-xs bg-green-900/50 text-green-300 px-2 py-1 rounded border border-green-400">
                                            Correct
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- True/False -->
                        <div v-else-if="answer.quiz.type === 'true_false'" class="mb-6">
                            <h5 class="font-medium text-white mb-3">Your Answer:</h5>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-4">
                                    <div 
                                        class="px-4 py-2 rounded-lg border-2"
                                        :class="answer.user_answer === 'True' 
                                            ? (answer.is_correct ? 'border-green-400 bg-green-900/30' : 'border-red-400 bg-red-900/30')
                                            : 'border-gray-600 bg-gray-800/50'"
                                    >
                                        <span class="font-medium text-white">True</span>
                                    </div>
                                    <div 
                                        class="px-4 py-2 rounded-lg border-2"
                                        :class="answer.user_answer === 'False' 
                                            ? (answer.is_correct ? 'border-green-400 bg-green-900/30' : 'border-red-400 bg-red-900/30')
                                            : 'border-gray-600 bg-gray-800/50'"
                                    >
                                        <span class="font-medium text-white">False</span>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-300">
                                    Correct answer: <span class="font-medium text-green-400">{{ formatAnswer(answer.correct_answer) }}</span>
                                </div>
                            </div>
                        </div>



                        <!-- Explanation -->
                        <div v-if="answer.quiz.explanation" class="mb-6">
                            <h5 class="font-medium text-white mb-3">Explanation:</h5>
                            <div class="p-4 bg-blue-900/30 border-l-4 border-blue-400 rounded-r-lg">
                                <p class="text-blue-300">{{ answer.quiz.explanation }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-white mb-4">Additional Information</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h5 class="font-medium text-white mb-2">Answer Details</h5>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-300">Answered At:</dt>
                                        <dd class="text-sm font-medium text-white">{{ formatDateTime(answer.answered_at) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-300">Result:</dt>
                                        <dd class="text-sm font-medium" :class="answer.is_correct ? 'text-green-400' : 'text-red-400'">
                                            {{ answer.is_correct ? 'Correct' : 'Incorrect' }}
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-300">Context:</dt>
                                        <dd class="text-sm font-medium text-white">{{ formatContextType(answer.context_type) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h5 class="font-medium text-white mb-2">Quiz Information</h5>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-300">Type:</dt>
                                        <dd class="text-sm font-medium text-white">{{ formatQuizType(answer.quiz.type) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-300">Difficulty:</dt>
                                        <dd class="text-sm font-medium text-white">{{ answer.quiz.difficulty || 'Not specified' }}</dd>
                                    </div>
                                    <div v-if="answer.quiz.points" class="flex justify-between">
                                        <dt class="text-sm text-gray-300">Points:</dt>
                                        <dd class="text-sm font-medium text-white">{{ answer.quiz.points }}</dd>
                                    </div>
                                </dl>
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
    name: 'QuizAnswerShow',
    components: {
        AppLayout,
        Link
    },
    props: {
        answer: Object,
        context: Object
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
                day: 'numeric'
            })
        },
        formatDateTime(date) {
            return new Date(date).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            })
        },
        formatQuizType(type) {
            const typeMap = {
                'multiple_choice': 'Multiple Choice',
                'true_false': 'True/False',
                'enumeration': 'Enumeration'
            }
            return typeMap[type] || type
        },
        formatContextType(type) {
            const typeMap = {
                'battle': 'Battle',
                'multiplayer': 'Multiplayer Game'
            }
            return typeMap[type] || type
        },
        formatBattleStatus(status) {
            const statusMap = {
                'active': 'Active',
                'won': 'Victory',
                'lost': 'Defeat',
                'abandoned': 'Abandoned'
            }
            return statusMap[status] || status
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
        getBattleStatusClass(status) {
            const classes = {
                'active': 'bg-yellow-100 text-yellow-800',
                'won': 'bg-green-100 text-green-800',
                'lost': 'bg-red-100 text-red-800',
                'abandoned': 'bg-gray-100 text-gray-800'
            }
            return classes[status] || 'bg-gray-100 text-gray-800'
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
        getOptionClass(option, correctAnswer, userAnswer) {
            const isCorrect = this.isCorrectAnswer(option, correctAnswer);
            if (isCorrect && option === userAnswer) {
                return 'border-green-400 bg-green-900/50 text-white ring-2 ring-green-400/50' // Correct answer that user selected - highlighted
            } else if (isCorrect) {
                return 'border-green-400 bg-green-900/30 text-white' // Correct answer (not selected by user)
            } else if (option === userAnswer) {
                return 'border-red-400 bg-red-900/30 text-white' // Wrong answer that user selected
            }
            return 'border-gray-600 bg-gray-800/50 text-white' // Other options
        },
        getOptionIconClass(option, correctAnswer, userAnswer) {
            const isCorrect = this.isCorrectAnswer(option, correctAnswer);
            if (isCorrect && option === userAnswer) {
                return 'border-green-400 text-green-400 bg-green-900/50' // Correct answer that user selected - highlighted
            } else if (isCorrect) {
                return 'border-green-400 text-green-400' // Correct answer (not selected by user)
            } else if (option === userAnswer) {
                return 'border-red-400 text-red-400' // Wrong answer that user selected
            }
            return 'border-gray-400 text-gray-400' // Other options
        },
        isCorrectAnswer(option, correctAnswer) {
            if (Array.isArray(correctAnswer)) {
                return correctAnswer.includes(option);
            }
            return correctAnswer === option;
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