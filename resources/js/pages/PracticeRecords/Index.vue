<template>
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-6">
            <div class="mx-auto flex max-w-md items-center justify-center">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline w-fit px-10 py-2 text-center text-2xl font-bold">History</h1>
            </div>
            <div class="bg-container p-6">
                <div v-if="groupedRecords.length === 0" class="text-muted-foreground text-center">No practice records found.</div>
                <div v-else class="grid grid-cols-1 gap-5 space-y-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4">
                    <div
                        v-for="group in groupedRecords"
                        :key="group.file.id"
                        class="pixel-outline flex h-full flex-col rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow"
                    >
                        <h2
                            class="cursor-pointer text-lg font-semibold text-white drop-shadow-[0_1px_0_#0c0a03,0_-1px_0_#0c0a03,1px_0_0_#0c0a03,-1px_0_0_#0c0a03]"
                            @click="toggleGroup(group.file.id)"
                        >
                            {{ group.file.name }}
                        </h2>
                        <div v-if="expandedGroups[group.file.id]" class="mt-2">
                            <div v-for="attempt in group.attempts" :key="attempt.id" class="mb-2">
                                <div class="flex items-center justify-between">
                                    <span>
                                        <span
                                            class="font-bold text-white drop-shadow-[0_1px_0_#0c0a03,0_-1px_0_#0c0a03,1px_0_0_#0c0a03,-1px_0_0_#0c0a03]"
                                            >{{ attempt.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</span
                                        >
                                        <span class="ml-2 text-white drop-shadow-[0_1px_0_#0c0a03,0_-1px_0_#0c0a03,1px_0_0_#0c0a03,-1px_0_0_#0c0a03]"
                                            >Score: {{ attempt.correct_answers }} / {{ attempt.total_questions }}</span
                                        >
                                        <span
                                            class="ml-2 text-xs text-white drop-shadow-[0_1px_0_#0c0a03,0_-1px_0_#0c0a03,1px_0_0_#0c0a03,-1px_0_0_#0c0a03]"
                                            >{{ formatDate(attempt.created_at) }}</span
                                        >
                                    </span>
                                    <Link
                                        :href="route('practice-records.show', attempt.id)"
                                        class="text-primary pixel-outline ml-2 rounded-md border-2 border-[#0c0a03] bg-[#10B981] px-2.5 py-0.5 text-base tracking-wide duration-300 hover:scale-105 hover:bg-[#0e9459]"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>
                            <div v-if="group.attempts.length > 1" class="mt-4">
                                <canvas :ref="setChartRef(group.file.id)" class="h-32 w-full"></canvas>
                            </div>
                        </div>
                        <div v-else class="mt-2 text-xs text-white drop-shadow-[0_1px_0_#0c0a03,0_-1px_0_#0c0a03,1px_0_0_#0c0a03,-1px_0_0_#0c0a03]">
                            Click to show attempts
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
import { nextTick, ref } from 'vue';

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
const expandedGroups = ref<{ [key: number]: boolean }>({});
const chartRefs = ref<{ [key: number]: HTMLCanvasElement | null }>({});
const chartInstances = ref<{ [key: number]: Chart | null }>({});

function setChartRef(fileId: number) {
    // Only set the ref, do not render chart here to avoid recursive updates
    return (el: HTMLCanvasElement | null) => {
        chartRefs.value[fileId] = el;
    };
}
function toggleGroup(fileId: number) {
    expandedGroups.value[fileId] = !expandedGroups.value[fileId];
    if (expandedGroups.value[fileId]) {
        // Wait for DOM update, then render chart
        nextTick(() => renderChart(fileId));
    } else {
        if (chartInstances.value[fileId]) {
            chartInstances.value[fileId].destroy();
            chartInstances.value[fileId] = null;
        }
    }
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
        beforeDraw: (chart) => {
            const ctx = chart.ctx;
            ctx.save();
            ctx.shadowColor = '#0c0a03';
            ctx.shadowBlur = 4;
        },
        afterDraw: (chart) => {
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
