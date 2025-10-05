<template>
    <AppLayout title="Quiz Answer History">
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Quiz Answer History
            </h2>
        </template>

        <div class="bg-gradient min-h-screen py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-300 uppercase tracking-wide">Total Answers</div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-white">{{ stats.total_answers }}</div>
                        </div>
                    </div>

                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-300 uppercase tracking-wide">Overall Accuracy</div>
                            </div>
                            <div class="mt-2 text-3xl font-bold" :class="stats.accuracy >= 70 ? 'text-green-400' : 'text-red-400'">
                                {{ stats.accuracy }}%
                            </div>
                        </div>
                    </div>

                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-300 uppercase tracking-wide">Battle Answers</div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-blue-400">{{ stats.battle_answers }}</div>
                        </div>
                    </div>

                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-300 uppercase tracking-wide">Multiplayer Answers</div>
                            </div>
                            <div class="mt-2 text-3xl font-bold text-purple-400">{{ stats.multiplayer_answers }}</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Context Type Filter -->
                            <div>
                                <label for="context_type" class="block text-sm font-medium text-white">Context Type</label>
                                <select 
                                    id="context_type" 
                                    v-model="filterForm.context_type" 
                                    @change="applyFilters"
                                    class="mt-1 block w-full rounded-md bg-gray-800 border-gray-600 text-white shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                >
                                    <option value="" class="bg-gray-800 text-white">All</option>
                                    <option value="battle" class="bg-gray-800 text-white">Battle</option>
                                    <option value="multiplayer" class="bg-gray-800 text-white">Multiplayer</option>
                                </select>
                            </div>

                            <!-- Correctness Filter -->
                            <div>
                                <label for="is_correct" class="block text-sm font-medium text-white">Correctness</label>
                                <select 
                                    id="is_correct" 
                                    v-model="filterForm.is_correct" 
                                    @change="applyFilters"
                                    class="mt-1 block w-full rounded-md bg-gray-800 border-gray-600 text-white shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                >
                                    <option value="" class="bg-gray-800 text-white">All</option>
                                    <option value="1" class="bg-gray-800 text-white">Correct</option>
                                    <option value="0" class="bg-gray-800 text-white">Incorrect</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-white">Search</label>
                                <input 
                                    id="search" 
                                    v-model="filterForm.search" 
                                    @keyup.enter="applyFilters"
                                    type="text" 
                                    placeholder="Search questions or answers..."
                                    class="mt-1 block w-full rounded-md bg-gray-800 border-gray-600 text-white placeholder-gray-400 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                >
                            </div>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <button 
                                @click="applyFilters" 
                                class="bg-maroon-600 hover:bg-maroon-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Apply Filters
                            </button>
                            <button 
                                @click="clearFilters" 
                                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quiz Answers List -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Grouped Answers -->
                        <div class="space-y-6">
                            <div 
                                v-for="(group, groupKey) in groupedAnswers" 
                                :key="groupKey"
                                class="border border-gray-600 rounded-lg overflow-hidden"
                            >
                                <!-- Group Header -->
                                <div class="bg-gray-800/70 px-6 py-4 border-b border-gray-600">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-white">
                                                📚 {{ group.file_name }}
                                            </h3>
                                            <p class="text-sm text-gray-300 mt-1">
                                                {{ group.answers.length }} answer{{ group.answers.length !== 1 ? 's' : '' }} • 
                                                {{ Math.round((group.correct_count / group.answers.length) * 100) }}% accuracy
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="flex space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900/50 text-green-300 border border-green-400">
                                                    ✓ {{ group.correct_count }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900/50 text-red-300 border border-red-400">
                                                    ✗ {{ group.answers.length - group.correct_count }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group Content -->
                                <div class="divide-y divide-gray-700">
                                    <div 
                                        v-for="answer in group.answers" 
                                        :key="answer.id" 
                                        class="p-4 hover:bg-gray-800/30"
                                    >
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <!-- Question with Context Badge -->
                                                <div class="mb-3">
                                                    <div class="flex items-start space-x-2 mb-2">
                                                        <span 
                                                            v-if="answer.context_details"
                                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                                            :class="answer.context_type === 'battle' ? 'bg-blue-900/50 text-blue-300 border border-blue-400' : 'bg-purple-900/50 text-purple-300 border border-purple-400'"
                                                        >
                                                            {{ answer.context_type === 'battle' ? '⚔️ Battle' : '👥 Multiplayer' }}
                                                        </span>
                                                        <span class="text-xs text-gray-400">
                                                            {{ formatDate(answer.answered_at) }}
                                                        </span>
                                                    </div>
                                                    <h4 class="font-medium text-white leading-relaxed">{{ answer.quiz.question }}</h4>
                                                </div>

                                                <!-- Answer and Correctness -->
                                                <div class="mb-3">
                                                    <div class="flex items-start space-x-4">
                                                        <div class="flex-1">
                                                            <div class="mb-2">
                                                                <span class="text-sm text-gray-300">Your Answer:</span>
                                                                <span 
                                                                    class="ml-2 font-medium"
                                                                    :class="answer.is_correct ? 'text-green-400' : 'text-red-400'"
                                                                >
                                                                    {{ formatAnswer(answer.user_answer) }}
                                                                </span>
                                                            </div>
                                                            
                                                            <!-- Correct Answer (if incorrect) -->
                                                            <div v-if="!answer.is_correct" class="mb-2">
                                                                <span class="text-sm text-gray-300">Correct Answer:</span>
                                                                <span class="ml-2 font-medium text-green-400">{{ getCorrectAnswer(answer.quiz) }}</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Status Icon -->
                                                        <div class="flex-shrink-0">
                                                            <span 
                                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full"
                                                                :class="answer.is_correct ? 'bg-green-900/50 border border-green-400' : 'bg-red-900/50 border border-red-400'"
                                                            >
                                                                <svg v-if="answer.is_correct" class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                </svg>
                                                                <svg v-else class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Context Link -->
                                                <div v-if="answer.context_details" class="text-sm">
                                                    <Link 
                                                        :href="answer.context_details.route"
                                                        class="text-yellow-400 hover:text-yellow-300"
                                                    >
                                                        {{ answer.context_details.name }}
                                                    </Link>
                                                    <span 
                                                        class="ml-2 px-2 py-1 rounded text-xs font-medium"
                                                        :class="getContextStatusClass(answer.context_details.status)"
                                                    >
                                                        {{ answer.context_details.status }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="ml-4 flex-shrink-0">
                                                <Link 
                                                    :href="route('quiz-answers.show', answer.id)"
                                                    class="text-maroon-400 hover:text-maroon-300 text-sm font-medium"
                                                >
                                                    View Details
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="Object.keys(groupedAnswers).length === 0" class="text-center py-8">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-white">No quiz answers found</h3>
                                    <p class="mt-1 text-sm text-gray-400">Start a battle or multiplayer game to begin answering questions!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="answers.data.length > 0" class="mt-6">
                            <Pagination :links="answers.links" />
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
import { router } from '@inertiajs/vue3'
import Pagination from '@/components/Pagination.vue'

export default {
    name: 'QuizAnswersIndex',
    components: {
        AppLayout,
        Link,
        Pagination
    },
    props: {
        answers: Object,
        stats: Object,
        filters: Object
    },
    data() {
        return {
            filterForm: {
                context_type: this.filters?.context_type || '',
                is_correct: this.filters?.is_correct || '',
                search: this.filters?.search || ''
            }
        }
    },
    computed: {
        groupedAnswers() {
            const groups = {}
            
            // Group answers by file
            this.answers.data.forEach(answer => {
                const fileKey = answer.quiz.file ? answer.quiz.file.id : 'no-file'
                const fileName = answer.quiz.file ? answer.quiz.file.name : 'Unknown File'
                
                if (!groups[fileKey]) {
                    groups[fileKey] = {
                        file_name: fileName,
                        answers: [],
                        correct_count: 0
                    }
                }
                
                groups[fileKey].answers.push(answer)
                if (answer.is_correct) {
                    groups[fileKey].correct_count++
                }
            })
            
            // Sort answers within each group by date (newest first)
            Object.keys(groups).forEach(key => {
                groups[key].answers.sort((a, b) => new Date(b.answered_at) - new Date(a.answered_at))
            })
            
            // Sort groups by total accuracy (highest first), then by answer count
            const sortedGroups = {}
            Object.keys(groups)
                .sort((a, b) => {
                    const accuracyA = groups[a].correct_count / groups[a].answers.length
                    const accuracyB = groups[b].correct_count / groups[b].answers.length
                    
                    if (accuracyA !== accuracyB) {
                        return accuracyB - accuracyA // Higher accuracy first
                    }
                    return groups[b].answers.length - groups[a].answers.length // More answers first
                })
                .forEach(key => {
                    sortedGroups[key] = groups[key]
                })
            
            return sortedGroups
        }
    },
    methods: {
        applyFilters() {
            const params = {}
            
            if (this.filterForm.context_type) {
                params.context_type = this.filterForm.context_type
            }
            if (this.filterForm.is_correct !== '') {
                params.is_correct = this.filterForm.is_correct
            }
            if (this.filterForm.search) {
                params.search = this.filterForm.search
            }

            router.get(route('quiz-answers.index'), params, {
                preserveState: true,
                preserveScroll: true
            })
        },
        clearFilters() {
            this.filterForm = {
                context_type: '',
                is_correct: '',
                search: ''
            }
            router.get(route('quiz-answers.index'), {}, {
                preserveState: true,
                preserveScroll: true
            })
        },
        formatAnswer(answer) {
            if (typeof answer === 'object') {
                return Array.isArray(answer) ? answer.join(', ') : JSON.stringify(answer)
            }
            return answer
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        },
        getContextStatusClass(status) {
            const classes = {
                'active': 'bg-yellow-900/50 text-yellow-300 border border-yellow-400',
                'won': 'bg-green-900/50 text-green-300 border border-green-400',
                'lost': 'bg-red-900/50 text-red-300 border border-red-400',
                'abandoned': 'bg-gray-700 text-gray-300 border border-gray-500',
                'finished': 'bg-blue-900/50 text-blue-300 border border-blue-400',
                'waiting': 'bg-orange-900/50 text-orange-300 border border-orange-400'
            }
            return classes[status] || 'bg-gray-700 text-gray-300 border border-gray-500'
        },
        getCorrectAnswer(quiz) {
            if (quiz.answers && Array.isArray(quiz.answers)) {
                return quiz.answers[0]; // Return first correct answer
            }
            return quiz.answers || 'No correct answer';
        }
    }
}
</script>