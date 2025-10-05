<template>
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-6">
            <div class="flex items-center justify-center">
                <h1 class="welcome-banner animate-soft-bounce w-fit px-10 py-2 text-2xl font-bold">History Details</h1>
            </div>
            <!-- Progress Chart -->
            <div v-if="progressRecords && progressRecords.length > 1" class="bg-container mb-8 rounded-lg border-2 border-[#0c0a03] p-6">
                <h2 class="mb-2 text-lg font-bold">Your Progress Over Time</h2>
                <canvas ref="progressChart"></canvas>
            </div>
            <div class="bg-container p-6">
                <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                    <h2 class="text-xl font-semibold">{{ record.file.name }}</h2>
                    <p>Type: {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</p>
                    <p>
                        <span class="font-bold">Score:</span> {{ record.correct_answers }} / {{ record.total_questions }} (
                        <span :class="scoreColorClass">{{ formattedScorePercentage }}</span> )
                    </p>
                    
                    <!-- Take Quiz Again Link -->
                    <div class="mt-4">
                        <Link
                            :href="record.type === 'flashcard' ? route('files.flashcards.practice', record.file.id) : route('files.quizzes.test', record.file.id)"
                            class="pixel-outline inline-block rounded-md border-2 border-[#0c0a03] bg-[#10B981] px-4 py-2 text-base font-semibold text-white tracking-wide duration-300 hover:scale-105 hover:bg-[#0e9459]"
                        >
                            {{ record.type === 'flashcard' ? 'Practice Flashcards Again' : 'Take Quiz Again' }}
                        </Link>
                    </div>
                    
                    <div v-if="decodedMistakes && decodedMistakes.length > 0" class="mt-4">
                        <h3 class="text-lg font-semibold tracking-wide text-red-200">Mistakes:</h3>
                        <ul class="">
                            <li v-for="(mistake, index) in decodedMistakes" :key="index" class="mt-2">
                                <p>
                                    <span class="text-[#fb9e1b]">Question:</span> <span class="ml-1 font-extrabold">{{ mistake.question }}</span>
                                </p>
                                <p class="ml-5">
                                    <span class="text-[#6aa7d6]">Your Answer:</span>
                                    <span class="ml-1 font-extrabold">{{ mistake.your_answer }}</span>
                                </p>
                                <p class="ml-5">
                                    <span class="text-[#5cae6e]">Correct Answer:</span>
                                    <span class="ml-1 font-extralight">{{ mistake.correct_answer }}</span>
                                </p>
                            </li>
                        </ul>
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
