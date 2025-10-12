<template>
    <AppLayout title="Practice Record Details">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-white leading-tight">
                    Practice Record Details
                </h2>
                <Link 
                    :href="route('practice-records.index')" 
                    class="text-maroon-400 hover:text-maroon-300 text-sm font-medium"
                >
                    ← Back to Records
                </Link>
            </div>
        </template>

        <div class="bg-gradient min-h-screen py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Progress Chart -->
                <div v-if="progressRecords && progressRecords.length > 1" class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Your Progress Over Time</h2>
                        <canvas ref="progressChart"></canvas>
                    </div>
                </div>

                <!-- Record Info -->
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">
                                    {{ record.file.name }}
                                </h3>
                                <p class="text-sm text-gray-300">
                                    Type: {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-400 mt-1">
                                    {{ record.created_at ? formatDate(record.created_at) : 'Unknown date' }}
                                </div>
                            </div>
                        </div>

                        <!-- Record Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Total Questions</div>
                                <div class="text-2xl font-bold text-white">{{ record.total_questions }}</div>
                            </div>
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Correct Answers</div>
                                <div class="text-2xl font-bold text-green-400">{{ record.correct_answers }}</div>
                            </div>
                            <div class="bg-gray-800/50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-300">Accuracy</div>
                                <div class="text-2xl font-bold" :class="typeof numericScorePercentage === 'number' && numericScorePercentage >= 70 ? 'text-green-400' : 'text-red-400'">
                                    {{ formattedScorePercentage }}
                                </div>
                            </div>
                        </div>

                        <!-- Take Quiz Again Link -->
                        <div class="mt-4">
                            <Link
                                :href="
                                    record.type === 'flashcard'
                                        ? route('files.flashcards.practice', record.file.id)
                                        : route('files.quizzes.test', record.file.id)
                                "
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                {{ record.type === 'flashcard' ? 'Practice Flashcards Again' : 'Take Quiz Again' }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Mistakes Timeline -->
                <div v-if="decodedMistakes && decodedMistakes.length > 0" class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-white mb-4">Mistakes Review</h4>
                        
                        <div class="space-y-4">
                            <div 
                                v-for="(mistake, index) in decodedMistakes" 
                                :key="index"
                                class="border-l-4 border-red-500 pl-4 pb-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <!-- Question Number and Status -->
                                        <div class="flex items-center mb-2">
                                            <span class="bg-gray-700 text-gray-200 text-xs font-medium px-2.5 py-0.5 rounded-full mr-2">
                                                Question {{ index + 1 }}
                                            </span>
                                            <span class="bg-red-900/50 text-red-300 border border-red-400 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                Incorrect
                                            </span>
                                        </div>

                                        <!-- Question -->
                                        <div class="mb-3">
                                            <h5 class="font-medium text-white">{{ mistake.question }}</h5>
                                        </div>

                                        <!-- Quiz Type Badge -->
                                        <div class="mb-2">
                                            <span class="bg-blue-600 text-blue-100 text-xs font-medium px-2.5 py-0.5 rounded">
                                                {{ formatQuizType(mistake.type || 'unknown') }}
                                            </span>
                                        </div>

                                        <!-- Your Answer (for non-enumeration questions) -->
                                        <div v-if="mistake.type !== 'enumeration'" class="mb-2">
                                            <div class="text-sm text-gray-300">Your Answer:</div>
                                            <div class="font-medium text-red-400">
                                                {{ formatAnswer(mistake.your_answer) }}
                                            </div>
                                        </div>

                                        <!-- Correct Answer (for non-enumeration questions) -->
                                        <div v-if="mistake.type !== 'enumeration'" class="mb-2">
                                            <div class="text-sm text-gray-300">Correct Answer:</div>
                                            <div class="font-medium text-green-400">
                                                {{ formatAnswer(mistake.correct_answer) }}
                                            </div>
                                        </div>

                                        <!-- Enhanced Enumeration Display -->
                                        <div v-if="mistake.type === 'enumeration'" class="mb-2">
                                            <!-- Expected Answers -->
                                            <div class="mb-3">
                                                <div class="text-sm text-gray-300">Expected Answers:</div>
                                                <div class="bg-gray-800/50 p-2 rounded">
                                                    <div v-if="getEnumerationAnswers(mistake).length > 0" class="space-y-1">
                                                        <div 
                                                            v-for="(answer, answerIndex) in getEnumerationAnswers(mistake)" 
                                                            :key="answerIndex"
                                                            class="text-sm text-green-300"
                                                        >
                                                            {{ answerIndex + 1 }}. {{ answer }}
                                                        </div>
                                                    </div>
                                                    <div v-else class="text-sm text-green-300">
                                                        {{ mistake.correct_answer }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- User's Enumeration Answers with Individual Checking -->
                                            <div v-if="getUserEnumerationAnswers(mistake).length > 0 || (Array.isArray(mistake.your_answer) && mistake.your_answer.length > 0)" class="mb-3">
                                                <div class="text-sm text-gray-300">Your Answers:</div>
                                                <div class="bg-gray-800/30 p-2 rounded">
                                                    <div 
                                                        v-for="(userAns, userIndex) in getUserEnumerationAnswers(mistake)" 
                                                        :key="userIndex"
                                                        class="text-sm flex items-center"
                                                        :class="isCorrectEnumerationAnswer(userAns, getEnumerationAnswers(mistake)) ? 'text-green-400' : 'text-red-400'"
                                                    >
                                                        <svg v-if="isCorrectEnumerationAnswer(userAns, getEnumerationAnswers(mistake))" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        <svg v-else class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ userIndex + 1 }}. {{ userAns }}
                                                    </div>
                                                    <div v-if="getUserEnumerationAnswers(mistake).length === 0" class="text-sm text-gray-400 italic">
                                                        No answers provided
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Show missing answers -->
                                            <div v-if="getEnumerationAnswers(mistake).length > 0 && getUserEnumerationAnswers(mistake).length >= 0" class="mb-2">
                                                <div class="text-sm text-gray-300">Analysis:</div>
                                                <div class="bg-blue-900/30 p-2 rounded text-sm">
                                                    <div class="text-blue-300">
                                                        Found {{ getUserEnumerationAnswers(mistake).filter((a: any) => isCorrectEnumerationAnswer(a, getEnumerationAnswers(mistake))).length }} 
                                                        out of {{ getEnumerationAnswers(mistake).length }} required answers
                                                    </div>
                                                    <div v-if="getMissingAnswers(getUserEnumerationAnswers(mistake), getEnumerationAnswers(mistake)).length > 0" class="mt-1">
                                                        <span class="text-yellow-300">Missing: </span>
                                                        <span class="text-yellow-200">{{ getMissingAnswers(getUserEnumerationAnswers(mistake), getEnumerationAnswers(mistake)).join(', ') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Explanation if available -->
                                        <div v-if="mistake.explanation" class="mb-2">
                                            <div class="text-sm text-gray-300">Explanation:</div>
                                            <div class="text-sm text-gray-200 bg-blue-900/50 p-2 rounded">
                                                {{ mistake.explanation }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State for no mistakes -->
                            <div v-if="decodedMistakes.length === 0" class="text-center py-8">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-white">Perfect Score!</h3>
                                    <p class="mt-1 text-sm text-gray-400">You answered all questions correctly.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Perfect Score State -->
                <div v-else class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center py-8">
                            <div class="text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-white">Perfect Score!</h3>
                                <p class="mt-1 text-sm text-gray-400">You answered all questions correctly in this {{ record.type === 'flashcard' ? 'flashcard session' : 'quiz' }}.</p>
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
import { Link } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { computed, onMounted, ref } from 'vue';

interface Props {
    record: {
        id: number;
        file: {
            id: number;
            name: string;
        };
        type: string;
        correct_answers: number;
        total_questions: number;
        mistakes: string; // JSON string
        created_at?: string;
    };
    progressRecords: Array<{
        id: number;
        created_at: string;
        correct_answers: number;
        total_questions: number;
    }>;
}

const props = defineProps<Props>();
const progressChart = ref<HTMLCanvasElement | null>(null);

const decodedMistakes = computed(() => {
    try {
        return JSON.parse(props.record.mistakes || '[]');
    } catch (error) {
        console.error('Failed to parse mistakes JSON:', error);
        return [];
    }
});

const numericScorePercentage = computed<number | string>(() => {
    if (props.record.total_questions === 0) {
        return 'N/A';
    }
    const percentage = (props.record.correct_answers / props.record.total_questions) * 100;
    return parseFloat(percentage.toFixed(0)); // Return as a number
});

const formattedScorePercentage = computed<string>(() => {
    if (numericScorePercentage.value === 'N/A') {
        return 'N/A';
    }
    return `${numericScorePercentage.value}%`;
});

const scoreColorClass = computed<string>(() => {
    if (typeof numericScorePercentage.value !== 'number') {
        return 'text-white'; // Default color for N/A or if no score
    }

    const percentage = numericScorePercentage.value;

    if (percentage <= 20) return 'text-red-600';
    if (percentage <= 30) return 'text-red-400';
    if (percentage <= 40) return 'text-yellow-500';
    if (percentage <= 50) return 'text-yellow-400';
    if (percentage <= 60) return 'text-orange-500';
    if (percentage <= 70) return 'text-orange-400';
    if (percentage <= 80) return 'text-green-600';
    if (percentage <= 90) return 'text-green-500';
    return 'text-green-400'; // 91-100%
});

// Formatting methods
function formatAnswer(answer: any): string {
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
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatQuizType(type: string): string {
    const typeMap: Record<string, string> = {
        'multiple_choice': 'Multiple Choice',
        'true_false': 'True/False',
        'enumeration': 'Enumeration',
        'unknown': 'Unknown'
    };
    return typeMap[type] || type;
}

function isCorrectEnumerationAnswer(userAnswer: string, correctAnswers: any): boolean {
    if (Array.isArray(correctAnswers)) {
        return correctAnswers.some(correct => 
            correct.toLowerCase().trim() === userAnswer.toLowerCase().trim()
        );
    }
    if (typeof correctAnswers === 'string') {
        return correctAnswers.toLowerCase().trim() === userAnswer.toLowerCase().trim();
    }
    return false;
}

function getMissingAnswers(userAnswers: any, correctAnswers: any): string[] {
    if (!Array.isArray(correctAnswers)) return [];
    if (!Array.isArray(userAnswers)) return correctAnswers;
    
    const validUserAnswers = userAnswers.filter((a: any) => a && a.toString().trim());
    const missing = correctAnswers.filter(correct => 
        !validUserAnswers.some((userAns: any) => 
            userAns.toString().toLowerCase().trim() === correct.toLowerCase().trim()
        )
    );
    
    return missing;
}

function getEnumerationAnswers(mistake: any): string[] {
    // Try different possible data structures for enumeration answers
    
    // First check if correct_answer is already an array
    if (Array.isArray(mistake.correct_answer)) {
        return mistake.correct_answer;
    }
    
    // Check if there's a quiz.answers property
    if (mistake.quiz && Array.isArray(mistake.quiz.answers)) {
        return mistake.quiz.answers;
    }
    
    // Check if correct_answer is a comma-separated string
    if (typeof mistake.correct_answer === 'string') {
        const answers = mistake.correct_answer.split(',').map((a: string) => a.trim()).filter((a: string) => a);
        if (answers.length > 1) {
            return answers;
        }
    }
    
    // Check if it's stored as a JSON string
    if (typeof mistake.correct_answer === 'string') {
        try {
            const parsed = JSON.parse(mistake.correct_answer);
            if (Array.isArray(parsed)) {
                return parsed;
            }
        } catch (e) {
            // Not JSON, continue
        }
    }
    
    // Fallback: return as single item array
    return mistake.correct_answer ? [mistake.correct_answer] : [];
}

function getUserEnumerationAnswers(mistake: any): string[] {
    // Try different possible data structures for user enumeration answers
    
    // First check if your_answer is already an array
    if (Array.isArray(mistake.your_answer)) {
        return mistake.your_answer.filter((a: any) => a && a.toString().trim());
    }
    
    // Check if your_answer is a comma-separated string
    if (typeof mistake.your_answer === 'string') {
        const answers = mistake.your_answer.split(',').map((a: string) => a.trim()).filter((a: string) => a);
        if (answers.length > 0) {
            return answers;
        }
    }
    
    // Check if it's stored as a JSON string
    if (typeof mistake.your_answer === 'string') {
        try {
            const parsed = JSON.parse(mistake.your_answer);
            if (Array.isArray(parsed)) {
                return parsed.filter((a: any) => a && a.toString().trim());
            }
        } catch (e) {
            // Not JSON, continue
        }
    }
    
    // Fallback: return as single item array if not empty
    return mistake.your_answer && mistake.your_answer.toString().trim() ? [mistake.your_answer.toString().trim()] : [];
}

// Chart.js: Render progress chart
onMounted(() => {
    if (progressChart.value && props.progressRecords && props.progressRecords.length > 1) {
        const ctx = progressChart.value.getContext('2d');
        if (!ctx) return;
        const labels = props.progressRecords.map((r) => new Date(r.created_at).toLocaleDateString());
        const data = props.progressRecords.map((r) => (r.total_questions > 0 ? Math.round((r.correct_answers / r.total_questions) * 100) : 0));
        // Chart.js plugin for outlined text
        const outlinedTextPlugin = {
            id: 'outlinedText',
            beforeDraw: (chart: any) => {
                const ctx = chart.ctx;
                ctx.save();
                ctx.shadowColor = '#0c0a03';
                ctx.shadowBlur = 4;
            },
            afterDraw: (chart: any) => {
                const ctx = chart.ctx;
                ctx.shadowColor = 'transparent';
                ctx.shadowBlur = 0;
                ctx.restore();
            },
        };
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Score (%)',
                        data,
                        fill: false,
                        borderColor: '#fb9e1b',
                        backgroundColor: '#fb9e1b',
                        tension: 0.2,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 14,
                            },
                        },
                    },
                    title: {
                        display: false,
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#0c0a03',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1,
                    },
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: { display: true, text: 'Score (%)', color: '#fff', font: { weight: 'bold', size: 14 } },
                        ticks: {
                            color: '#fff',
                            font: { weight: 'bold', size: 12 },
                        },
                        grid: { color: 'rgba(255,255,255,0.2)' },
                    },
                    x: {
                        title: { display: true, text: 'Date', color: '#fff', font: { weight: 'bold', size: 14 } },
                        ticks: {
                            color: '#fff',
                            font: { weight: 'bold', size: 12 },
                        },
                        grid: { color: 'rgba(255,255,255,0.2)' },
                    },
                },
            },
            plugins: [outlinedTextPlugin],
        });
    }
});
</script>
