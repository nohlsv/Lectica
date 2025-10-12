<template>
    <AppLayout title="Quiz Performance">
        <div class="bg-gradient min-h-screen py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-2 gap-3 mb-4 sm:grid-cols-4 sm:gap-6 sm:mb-6">
                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-3 sm:p-6">
                            <div class="flex items-center">
                                <div class="text-xs font-medium text-gray-300 uppercase tracking-wide sm:text-sm">Total Answers</div>
                            </div>
                            <div class="mt-1 text-xl font-bold text-white sm:mt-2 sm:text-3xl">{{ stats.total_answers }}</div>
                        </div>
                    </div>

                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-3 sm:p-6">
                            <div class="flex items-center">
                                <div class="text-xs font-medium text-gray-300 uppercase tracking-wide sm:text-sm">Accuracy</div>
                            </div>
                            <div class="mt-1 text-xl font-bold sm:mt-2 sm:text-3xl" :class="stats.accuracy >= 70 ? 'text-green-400' : 'text-red-400'">
                                {{ stats.accuracy }}%
                            </div>
                        </div>
                    </div>

                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-3 sm:p-6">
                            <div class="flex items-center">
                                <div class="text-xs font-medium text-gray-300 uppercase tracking-wide sm:text-sm">Battle</div>
                            </div>
                            <div class="mt-1 text-xl font-bold text-blue-400 sm:mt-2 sm:text-3xl">{{ stats.battle_answers }}</div>
                        </div>
                    </div>

                    <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-3 sm:p-6">
                            <div class="flex items-center">
                                <div class="text-xs font-medium text-gray-300 uppercase tracking-wide sm:text-sm">Multiplayer</div>
                            </div>
                            <div class="mt-1 text-xl font-bold text-purple-400 sm:mt-2 sm:text-3xl">{{ stats.multiplayer_answers }}</div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-4 sm:mb-6">
                    <div class="p-3 sm:p-6">
                        <div class="grid grid-cols-1 gap-3 sm:gap-4 md:grid-cols-3">
                            <!-- Context Type Filter -->
                            <div>
                                <label for="context_type" class="block text-xs font-medium text-white sm:text-sm">Context Type</label>
                                <select 
                                    id="context_type" 
                                    v-model="filterForm.context_type" 
                                    @change="applyFilters"
                                    class="mt-1 block w-full rounded-md bg-gray-800 border-gray-600 text-sm text-white shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                >
                                    <option value="" class="bg-gray-800 text-white">All</option>
                                    <option value="battle" class="bg-gray-800 text-white">Battle</option>
                                    <option value="multiplayer" class="bg-gray-800 text-white">Multiplayer</option>
                                </select>
                            </div>

                            <!-- Correctness Filter -->
                            <div>
                                <label for="is_correct" class="block text-xs font-medium text-white sm:text-sm">Correctness</label>
                                <select 
                                    id="is_correct" 
                                    v-model="filterForm.is_correct" 
                                    @change="applyFilters"
                                    class="mt-1 block w-full rounded-md bg-gray-800 border-gray-600 text-sm text-white shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                >
                                    <option value="" class="bg-gray-800 text-white">All</option>
                                    <option value="1" class="bg-gray-800 text-white">Correct</option>
                                    <option value="0" class="bg-gray-800 text-white">Incorrect</option>
                                </select>
                            </div>

                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-xs font-medium text-white sm:text-sm">Search</label>
                                <input 
                                    id="search" 
                                    v-model="filterForm.search" 
                                    @keyup.enter="applyFilters"
                                    type="text" 
                                    placeholder="Search questions or answers..."
                                    class="mt-1 block w-full rounded-md bg-gray-800 border-gray-600 text-sm text-white placeholder-gray-400 shadow-sm focus:border-maroon-500 focus:ring-maroon-500"
                                >
                            </div>
                        </div>
                        <div class="mt-3 flex flex-col gap-2 sm:mt-4 sm:flex-row sm:space-x-2 sm:gap-0">
                            <button 
                                @click="applyFilters" 
                                class="bg-maroon-600 hover:bg-maroon-700 text-white font-bold py-2 px-3 rounded text-sm sm:px-4"
                            >
                                Apply Filters
                            </button>
                            <button 
                                @click="clearFilters" 
                                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-sm sm:px-4"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quiz Answers List -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-3 sm:p-6">
                        <!-- Grouped Answers -->
                        <div class="space-y-3 sm:space-y-6">
                            <div 
                                v-for="(group, groupKey) in groupedAnswers" 
                                :key="groupKey"
                                class="border border-gray-600 rounded-lg overflow-hidden"
                            >
                                <!-- Group Header - Now Clickable -->
                                <button 
                                    @click="toggleQuizGroup(groupKey)"
                                    class="w-full bg-gray-800/70 px-3 py-3 border-b border-gray-600 hover:bg-gray-700/70 transition-colors focus:outline-none focus:ring-2 focus:ring-white/20 sm:px-6 sm:py-4"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="text-left flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-white truncate sm:text-lg">
                                                📚 {{ group.file_name }}
                                            </h3>
                                            <p class="text-xs text-gray-300 mt-1 sm:text-sm">
                                                {{ group.answers.length }} answer{{ group.answers.length !== 1 ? 's' : '' }} • 
                                                {{ Math.round((group.correct_count / group.answers.length) * 100) }}% accuracy
                                            </p>
                                        </div>
                                        <div class="text-right ml-2">
                                            <div class="flex items-center gap-1 sm:gap-2">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-900/50 text-green-300 border border-green-400 sm:px-2.5">
                                                    ✓ {{ group.correct_count }}
                                                </span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-900/50 text-red-300 border border-red-400 sm:px-2.5">
                                                    ✗ {{ group.answers.length - group.correct_count }}
                                                </span>
                                                <span class="text-white/60 ml-1 transition-opacity duration-200">
                                                    {{ showQuizDetails[groupKey] ? '▲' : '▼' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                                
                                <!-- Collapsible Quiz Details -->
                                <div v-if="showQuizDetails[groupKey]" class="transition-all duration-300 ease-in-out">
                                    <!-- Group Content -->
                                    <div class="divide-y divide-gray-700">
                                        <div 
                                            v-for="answer in group.answers" 
                                            :key="answer.id" 
                                            class="p-3 hover:bg-gray-800/30 sm:p-4"
                                        >
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <!-- Question with Context Badge -->
                                                    <div class="mb-3">
                                                        <div class="flex flex-wrap items-start gap-2 mb-2">
                                                            <span 
                                                                v-if="answer.context_details"
                                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium sm:px-2 sm:py-1"
                                                                :class="answer.context_type === 'battle' ? 'bg-blue-900/50 text-blue-300 border border-blue-400' : 'bg-purple-900/50 text-purple-300 border border-purple-400'"
                                                            >
                                                                {{ answer.context_type === 'battle' ? '⚔️ Battle' : '👥 Multiplayer' }}
                                                            </span>
                                                            <span class="text-xs text-gray-400">
                                                                {{ formatDate(answer.answered_at) }}
                                                            </span>
                                                        </div>
                                                        <h4 class="font-medium text-white leading-relaxed text-sm sm:text-base">{{ answer.quiz.question }}</h4>
                                                    </div>

                                                    <!-- Answer and Correctness -->
                                                    <div class="mb-3">
                                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:gap-4">
                                                            <div class="flex-1 min-w-0">
                                                                <div class="mb-2">
                                                                    <span class="text-xs text-gray-300 sm:text-sm">Your Answer:</span>
                                                                    <div class="mt-1">
                                                                        <span 
                                                                            class="font-medium text-sm sm:text-base break-words"
                                                                            :class="answer.is_correct ? 'text-green-400' : 'text-red-400'"
                                                                        >
                                                                            {{ formatAnswer(answer.user_answer) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Correct Answer (if incorrect) -->
                                                                <div v-if="!answer.is_correct" class="mb-2">
                                                                    <span class="text-xs text-gray-300 sm:text-sm">Correct Answer:</span>
                                                                    <div class="mt-1">
                                                                        <span class="font-medium text-green-400 text-sm sm:text-base break-words">{{ formatAnswer(answer.correct_answer) }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Status Icon -->
                                                            <div class="flex-shrink-0 self-start sm:self-center">
                                                                <span 
                                                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full sm:w-8 sm:h-8"
                                                                    :class="answer.is_correct ? 'bg-green-900/50 border border-green-400' : 'bg-red-900/50 border border-red-400'"
                                                                >
                                                                    <svg v-if="answer.is_correct" class="w-3 h-3 text-green-400 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                                    </svg>
                                                                    <svg v-else class="w-3 h-3 text-red-400 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Context Link -->
                                                    <div v-if="answer.context_details" class="text-xs sm:text-sm">
                                                        <Link 
                                                            :href="answer.context_details.route"
                                                            class="text-yellow-400 hover:text-yellow-300 break-words"
                                                        >
                                                            {{ answer.context_details.name }}
                                                        </Link>
                                                        <span 
                                                            class="ml-1 px-1.5 py-0.5 rounded text-xs font-medium sm:ml-2 sm:px-2 sm:py-1"
                                                            :class="getContextStatusClass(answer.context_details.status)"
                                                        >
                                                            {{ answer.context_details.status }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="mt-3 sm:mt-0 sm:ml-4 sm:flex-shrink-0">
                                                    <Link 
                                                        :href="route('quiz-answers.show', answer.id)"
                                                        class="text-maroon-400 hover:text-maroon-300 text-xs font-medium sm:text-sm"
                                                    >
                                                        View Details
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="Object.keys(groupedAnswers).length === 0" class="text-center py-6 sm:py-8">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-10 w-10 text-gray-500 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="mt-2 text-xs font-medium text-white sm:text-sm">No quiz answers found</h3>
                                    <p class="mt-1 text-xs text-gray-400 px-4 sm:text-sm">Start a battle or multiplayer game to begin answering questions!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="answers.data.length > 0" class="mt-4 sm:mt-6">
                            <Pagination :links="answers.links" />
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
            },
            showQuizDetails: {} // This will track which groups are expanded
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
    watch: {
        // Initialize showQuizDetails when groupedAnswers changes
        groupedAnswers: {
            handler(newGroups) {
                // Initialize all group keys to false if not already set
                Object.keys(newGroups).forEach(key => {
                    if (!(key in this.showQuizDetails)) {
                        this.showQuizDetails = {
                            ...this.showQuizDetails,
                            [key]: false
                        };
                    }
                });
            },
            immediate: true
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

        toggleQuizGroup(groupKey) {
            console.log(`Click registered for group ${groupKey}`); // Debug log
            console.log('Current showQuizDetails:', this.showQuizDetails); // Debug log
            
            // Create new object to ensure reactivity in Vue 3
            const currentState = this.showQuizDetails[groupKey] || false;
            this.showQuizDetails = {
                ...this.showQuizDetails,
                [groupKey]: !currentState
            };
            
            console.log(`Toggled group ${groupKey} to ${!currentState}`); // Debug log
            console.log('New showQuizDetails:', this.showQuizDetails); // Debug log
        }
    }
}
</script>