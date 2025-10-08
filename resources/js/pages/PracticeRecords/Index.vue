<template>
    <AppLayout title="Practice Performance">
        <template #header>
            <h2 class="font-semibold text-lg text-white leading-tight sm:text-xl">
                Practice Performance
            </h2>
        </template>

        <div class="bg-gradient min-h-screen py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-container overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-3 sm:p-6">
                        <div v-if="groupedRecords.length === 0" class="text-center py-6 sm:py-8">
                            <div class="text-gray-400">
                                <svg class="mx-auto h-10 w-10 text-gray-500 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <h3 class="mt-2 text-xs font-medium text-white sm:text-sm">No practice records found</h3>
                                <p class="mt-1 text-xs text-gray-400 px-4 sm:text-sm">Start practicing with quiz files to see your performance history!</p>
                            </div>
                        </div>

                        <!-- Grouped Practice Records -->
                        <div v-else class="space-y-3 sm:space-y-6">
                            <div 
                                v-for="group in groupedRecords" 
                                :key="group.file.id"
                                class="border border-gray-600 rounded-lg overflow-hidden"
                            >
                                <!-- Group Header - Clickable -->
                                <button 
                                    @click="toggleDetails(group.file.id)"
                                    class="w-full bg-gray-800/70 px-3 py-3 border-b border-gray-600 hover:bg-gray-700/70 transition-colors focus:outline-none focus:ring-2 focus:ring-white/20 sm:px-6 sm:py-4"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="text-left flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-white truncate sm:text-lg">
                                                📝 {{ group.file.name }}
                                            </h3>
                                            <p class="text-xs text-gray-300 mt-1 sm:text-sm">
                                                {{ group.attempts.length }} attempt{{ group.attempts.length !== 1 ? 's' : '' }} • 
                                                {{ getLatestScore(group) }}
                                            </p>
                                        </div>
                                        <div class="text-right ml-2">
                                            <Link
                                                :href="route('files.show', group.file.id)"
                                                @click.stop
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-900/50 text-blue-300 border border-blue-400 hover:bg-blue-800/50 transition-colors mr-2 sm:px-3"
                                            >
                                                📚 View File
                                            </Link>
                                            <span class="text-white/60 ml-2 transition-opacity duration-200">
                                                {{ showDetails[group.file.id] ? '▲' : '▼' }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                                
                                <!-- Always visible mini chart -->
                                <div v-if="group.attempts.length > 1" class="border-b border-gray-600 p-2 bg-gray-900/30 sm:p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <h4 class="text-xs font-medium text-white/80 sm:text-sm">Performance Trend</h4>
                                        <span class="text-xs text-gray-400">{{ group.attempts.length }} attempts</span>
                                    </div>
                                    <canvas :ref="setChartRef(group.file.id)" class="h-8 w-full sm:h-10"></canvas>
                                </div>
                                
                                <!-- Collapsible Practice Details -->
                                <div v-if="showDetails[group.file.id]" class="transition-all duration-300 ease-in-out">
                                    <div class="divide-y divide-gray-700">
                                        <Link
                                            v-for="attempt in getVisibleAttempts(group)" 
                                            :key="attempt.id"
                                            :href="route('practice-records.show', attempt.id)"
                                            class="block p-3 hover:bg-gray-800/30 transition-colors sm:p-4"
                                        >
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <!-- Attempt Type and Score -->
                                                    <div class="mb-2">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span 
                                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                                                :class="attempt.type === 'flashcard' ? 'bg-purple-900/50 text-purple-300 border border-purple-400' : 'bg-green-900/50 text-green-300 border border-green-400'"
                                                            >
                                                                {{ attempt.type === 'flashcard' ? '🃏 Flashcards' : '📝 Quiz' }}
                                                            </span>
                                                            <span class="text-xs text-gray-400">
                                                                {{ formatDate(attempt.created_at) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Score and Performance -->
                                                    <div class="flex flex-wrap items-center gap-2 text-sm sm:text-base">
                                                        <span class="font-medium text-white">
                                                            Score: {{ attempt.correct_answers }}/{{ attempt.total_questions }}
                                                        </span>
                                                        <span 
                                                            class="font-bold"
                                                            :class="(attempt.correct_answers / attempt.total_questions) >= 0.7 ? 'text-green-400' : 'text-red-400'"
                                                        >
                                                            ({{ Math.round((attempt.correct_answers / attempt.total_questions) * 100) }}%)
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <!-- Click indicator -->
                                                <div class="flex items-center text-xs text-gray-400 sm:text-sm">
                                                    <span class="mr-1">View Details</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </Link>
                                    </div>

                                    
                                    <!-- Show More/Show Less Button -->
                                    <div v-if="group.attempts.length > 3" class="border-t border-gray-700 p-3 text-center sm:p-4">
                                        <button
                                            @click="toggleShowMore(group.file.id)"
                                            class="inline-flex items-center px-3 py-2 rounded text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600 hover:bg-gray-600 transition-colors sm:px-4 sm:text-sm"
                                        >
                                            {{
                                                showAllAttempts[group.file.id]
                                                    ? `Show Less (${group.attempts.length - 3} hidden)`
                                                    : `Show More (+${group.attempts.length - 3} more)`
                                            }}
                                        </button>
                                    </div>


                                </div>
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
import { nextTick, onMounted, ref } from 'vue';

interface Attempt {
    id: number;
    type: string;
    correct_answers: number;
    total_questions: number;
    created_at: string;
    mistakes: string;
}
interface GroupedRecord {
    file: {
        id: number;
        name: string;
    };
    attempts: Attempt[];
}
interface Props {
    groupedRecords: GroupedRecord[];
}
const props = defineProps<Props>();
const chartRefs = ref<{ [key: number]: HTMLCanvasElement | null }>({});
const chartInstances = ref<{ [key: number]: Chart | null }>({});
const showAllAttempts = ref<{ [key: number]: boolean }>({});
const showDetails = ref<{ [key: number]: boolean }>({});

function setChartRef(fileId: number) {
    return (el: any) => {
        if (el && el instanceof HTMLCanvasElement) {
            chartRefs.value[fileId] = el;
        }
    };
}

function getVisibleAttempts(group: GroupedRecord) {
    const isShowingAll = showAllAttempts.value[group.file.id];
    return isShowingAll ? group.attempts : group.attempts.slice(0, 3);
}

function toggleShowMore(fileId: number) {
    showAllAttempts.value[fileId] = !showAllAttempts.value[fileId];
}

function toggleDetails(fileId: number) {
    showDetails.value[fileId] = !showDetails.value[fileId];
}

// Render charts immediately on mount (they're always visible now)
onMounted(() => {
    nextTick(() => {
        // Small delay to ensure DOM elements are rendered before creating charts
        setTimeout(() => {
            props.groupedRecords.forEach((group) => {
                if (group.attempts.length > 1) {
                    renderChart(group.file.id);
                }
            });
        }, 100);
    });
});

function getLatestScore(group: GroupedRecord) {
    if (group.attempts.length === 0) return 'N/A';
    const latest = group.attempts[0]; // Assuming attempts are sorted by latest first
    const percentage = Math.round((latest.correct_answers / latest.total_questions) * 100);
    return `Latest: ${latest.correct_answers}/${latest.total_questions} (${percentage}%)`;
}
function renderChart(fileId: number) {
    const group = props.groupedRecords.find((g) => g.file.id === fileId);
    if (!group || group.attempts.length < 2) return;
    const canvas = chartRefs.value[fileId];
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    if (chartInstances.value[fileId]) {
        chartInstances.value[fileId].destroy();
    }
    const labels = group.attempts.map((a) => new Date(a.created_at).toLocaleDateString());
    const data = group.attempts.map((a) => (a.total_questions > 0 ? Math.round((a.correct_answers / a.total_questions) * 100) : 0));
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
    chartInstances.value[fileId] = new Chart(ctx, {
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

function formatDate(dateStr: string) {
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>
