<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    ChartTypeRegistry,
    LinearScale,
    LineController,
    LineElement,
    PieController,
    PointElement,
    Tooltip,
} from 'chart.js';
import { nextTick, onMounted } from 'vue';

// Register required Chart.js components
Chart.register(BarController, BarElement, CategoryScale, LinearScale, PieController, ArcElement, LineController, LineElement, PointElement, Tooltip);

// Use program code if available, else fallback to acronym from first letters
const simplifyName = (name: string, code?: string): string => {
    // If we have a program code, use it
    if (code && code.trim()) {
        return code;
    }

    // Fallback for non-program data (like colleges or other categories)
    if (name.length > 20) {
        return name
            .split(' ')
            .map((word) => word.charAt(0).toUpperCase())
            .join('');
    }
    return name;
};

interface Statistics {
    total_users: number;
    total_files: number;
    total_quizzes: number;
    total_flashcards: number;
    total_tags: number;
    total_programs: number;
    most_used_tags: Array<{ name: string; files_count: number }>;
    most_files_per_program: Array<{ name: string; code: string; files_count: number }>;
    users_per_program: Array<{ name: string; code: string; users_count: number }>;
    most_active_user: { last_name: string; first_name: string; files_count: number };
    average_files_per_user: number;
    total_flashcards_per_tag: Array<{ name: string; flashcards_count: number }>;
    total_quizzes_per_tag: Array<{ name: string; quizzes_count: number }>;
    user_with_most_stars: { last_name: string; first_name: string; files_sum_stars: number };
    most_quizzes_by_user: { last_name: string; first_name: string; quizzes_count: number };
    average_flashcards_per_quiz: number;
    new_users_7d: number;
    new_files_7d: number;
    new_quizzes_7d: number;
    new_flashcards_7d: number;
    new_tags_7d: number;
    new_programs_7d: number;
    new_users_30d: number;
    new_files_30d: number;
    new_quizzes_30d: number;
    new_flashcards_30d: number;
    new_tags_30d: number;
    new_programs_30d: number;
    latest_users: Array<{ id: number; first_name: string; last_name: string; created_at: string }>;
    latest_files: Array<{ id: number; name: string; created_at: string }>;
    latest_quizzes: Array<{ id: number; name: string; created_at: string }>;
    latest_flashcards: Array<{ id: number; question: string; created_at: string }>;
    latest_tags: Array<{ id: number; name: string; created_at: string }>;
    latest_programs: Array<{ id: number; name: string; code: string; created_at: string }>;
    most_popular_file: { id: number; name: string; starred_by_count: number };
    most_popular_tag: { id: number; name: string; files_count: number };
    most_popular_program: { id: number; name: string; code: string; files_count: number };
    total_storage_used_mb: number;
    average_file_size_kb: number;
    files_by_type: Array<{ extension: string; count: number }>;
    files_created_per_month: Array<{ month: string; count: number }>;
    storage_per_program: Array<{ name: string; code: string; storage_mb: number }>;
    quizzes_per_program: Array<{ name: string; code: string; quizzes_count: number }>;
    flashcards_per_program: Array<{ name: string; code: string; flashcards_count: number }>;
    access_logs: Array<{ user: string; route: string; method: string; accessed_at: string }>;
}

const props = defineProps<{ statistics: Statistics }>();

// Helper functions for formatting
const formatRoute = (route: string): string => {
    const routeParts = route.split('/').filter(Boolean);
    if (routeParts.length === 0) return 'Home';
    
    const actionMap: Record<string, string> = {
        'files': 'File Access',
        'quizzes': 'Quiz Access',
        'flashcards': 'Flashcard Access',
        'statistics': 'Stats View',
        'login': 'Login',
        'register': 'Register',
        'create': 'Create',
        'edit': 'Edit',
        'update': 'Update',
        'delete': 'Delete',
        'store': 'Save',
        'show': 'View'
    };
    
    return actionMap[routeParts[0]] || routeParts[0].charAt(0).toUpperCase() + routeParts[0].slice(1);
};

const formatTime = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
};

const renderChart = (id: string, type: keyof ChartTypeRegistry, data: any, options: any) => {
    const ctx = document.getElementById(id) as HTMLCanvasElement;
    if (!ctx) return;
    
    // Destroy existing chart if it exists to prevent memory leaks
    const existingChart = Chart.getChart(ctx);
    if (existingChart) {
        existingChart.destroy();
    }
    
    // Set canvas dimensions explicitly to container size
    const container = ctx.parentElement;
    if (container) {
        ctx.width = container.clientWidth;
        ctx.height = container.clientHeight;
    }
    
    new Chart(ctx, { type, data, options });
};

onMounted(async () => {
    await nextTick();
    // Chart configuration with mobile responsiveness
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
        },
        animation: {
            duration: 750,
        },
        layout: {
            padding: {
                top: 10,
                bottom: 10
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#fff',
                    font: { 
                        weight: 'bold',
                        size: window.innerWidth < 640 ? 10 : 12 
                    },
                    padding: window.innerWidth < 640 ? 8 : 12,
                    boxWidth: window.innerWidth < 640 ? 12 : 15,
                },
            },
            title: {
                color: '#fff',
                font: {
                    size: window.innerWidth < 640 ? 12 : 14
                }
            },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#fff',
                borderWidth: 1,
                displayColors: false,
                titleFont: {
                    size: window.innerWidth < 640 ? 11 : 13
                },
                bodyFont: {
                    size: window.innerWidth < 640 ? 10 : 12
                },
                callbacks: {
                    label: function (context: any) {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y;
                        const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return [`${label}: ${value}`, `Percentage: ${percentage}%`];
                    },
                },
            },
        },
        scales: {
            x: {
                ticks: { 
                    color: '#fff',
                    font: {
                        size: window.innerWidth < 640 ? 9 : 11
                    },
                    maxRotation: window.innerWidth < 640 ? 45 : 0,
                },
                grid: { color: 'rgba(255,255,255,0.15)' },
            },
            y: {
                ticks: { 
                    color: '#fff',
                    font: {
                        size: window.innerWidth < 640 ? 9 : 11
                    }
                },
                grid: { color: 'rgba(255,255,255,0.15)' },
            },
        },
    };    // Users/Students per Program chart
    renderChart(
        'filesPerProgramChart',
        'bar',
        {
            labels: props.statistics.users_per_program.map((p) => simplifyName(p.name, p.code)),
            datasets: [
                {
                    label: 'Users/Students per Program',
                    data: props.statistics.users_per_program.map((p) => p.users_count),
                    backgroundColor: 'rgba(255,255,255,0.7)',
                    borderColor: '#fff',
                    borderWidth: 2,
                },
            ],
        },
        chartOptions,
    );
    renderChart(
        'filesByTypeChart',
        'bar',
        {
            labels: props.statistics.files_by_type.map((t) => t.extension || 'none'),
            datasets: [
                {
                    label: 'Files by Type',
                    data: props.statistics.files_by_type.map((t) => t.count),
                    backgroundColor: 'rgba(255,255,255,0.7)',
                    borderColor: '#fff',
                    borderWidth: 2,
                },
            ],
        },
        chartOptions,
    );
    renderChart(
        'storagePerProgramChart',
        'bar',
        {
            labels: props.statistics.storage_per_program.map((p) => simplifyName(p.name, p.code)),
            datasets: [
                {
                    label: 'Storage Usage Per Program (MB)',
                    data: props.statistics.storage_per_program.map((p) => p.storage_mb),
                    backgroundColor: 'rgba(255,255,255,0.7)',
                    borderColor: '#fff',
                    borderWidth: 2,
                },
            ],
        },
        chartOptions,
    );
    renderChart(
        'quizzesPerProgramChart',
        'bar',
        {
            labels: props.statistics.quizzes_per_program.map((p) => simplifyName(p.name, p.code)),
            datasets: [
                {
                    label: 'Quiz Items Per Program',
                    data: props.statistics.quizzes_per_program.map((p) => p.quizzes_count),
                    backgroundColor: 'rgba(255,255,255,0.7)',
                    borderColor: '#fff',
                    borderWidth: 2,
                },
            ],
        },
        chartOptions,
    );
    renderChart(
        'flashcardsPerProgramChart',
        'bar',
        {
            labels: props.statistics.flashcards_per_program.map((p) => simplifyName(p.name, p.code)),
            datasets: [
                {
                    label: 'Flashcard Items Per Program',
                    data: props.statistics.flashcards_per_program.map((p) => p.flashcards_count),
                    backgroundColor: 'rgba(255,255,255,0.7)',
                    borderColor: '#fff',
                    borderWidth: 2,
                },
            ],
        },
        chartOptions,
    );
});
</script>

<style scoped>
/* Use a modern, easy-to-read font for the entire statistics page */
.stats-readable-font {
    font-family: 'Inter', 'Segoe UI', 'Arial', 'sans-serif', system-ui;
    font-size: 1rem;
    letter-spacing: 0.01em;
}
</style>

<template>
    <Head title="Usage and Statistics" />
    <AppLayout>
        <div class="bg-gradient stats-readable-font min-h-screen py-6 sm:py-10">
            <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-6 space-y-2 sm:space-y-0">
                <h1 class="pixel-outline text-xl sm:text-2xl md:text-3xl font-bold text-[#ffd700] animate-pulse">Usage & Statistics Dashboard</h1>
                <span class="pixel-outline text-xs sm:text-sm text-[#FFF8DC]/60">Updated: {{ new Date().toLocaleDateString() }}</span>
            </div>
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <!-- Top stats cards -->
                <div class="mb-6 sm:mb-8 grid grid-cols-2 gap-3 sm:gap-4 md:gap-6 md:grid-cols-4 xl:grid-cols-6">
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-3 sm:p-4 md:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="mb-1 sm:mb-2 text-sm sm:text-base md:text-lg font-semibold text-center text-[#FFF8DC]">Users</h2>
                        <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#ffd700]">{{ statistics.total_users }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-3 sm:p-4 md:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="mb-1 sm:mb-2 text-sm sm:text-base md:text-lg font-semibold text-center text-[#FFF8DC]">Files</h2>
                        <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#ffd700]">{{ statistics.total_files }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-3 sm:p-4 md:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="mb-1 sm:mb-2 text-sm sm:text-base md:text-lg font-semibold text-center text-[#FFF8DC]">Quiz Items</h2>
                        <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#ffd700]">{{ statistics.total_quizzes }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-3 sm:p-4 md:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="mb-1 sm:mb-2 text-sm sm:text-base md:text-lg font-semibold text-center text-[#FFF8DC]">Flashcard Items</h2>
                        <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#ffd700]">{{ statistics.total_flashcards }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-3 sm:p-4 md:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="mb-1 sm:mb-2 text-sm sm:text-base md:text-lg font-semibold text-center text-[#FFF8DC]">Tags</h2>
                        <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#ffd700]">{{ statistics.total_tags }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-3 sm:p-4 md:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="mb-1 sm:mb-2 text-sm sm:text-base md:text-lg font-semibold text-center text-[#FFF8DC]">Programs</h2>
                        <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#ffd700]">{{ statistics.total_programs }}</p>
                    </div>
                </div>

                <!-- Secondary stats -->
                <div class="mb-6 sm:mb-8 grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-3">
                    <div class="pixel-outline rounded-xl border-2 border-[#ffd700] bg-container p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="text-base sm:text-lg font-semibold text-[#ffd700]">Most Active User</h2>
                        <p class="text-lg sm:text-xl break-words text-[#FFF8DC]">{{ statistics.most_active_user.last_name }}, {{ statistics.most_active_user.first_name }}</p>
                        <p class="text-sm sm:text-base text-[#FFF8DC]/80">{{ statistics.most_active_user.files_count }} files</p>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#ffd700] bg-container p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="text-base sm:text-lg font-semibold text-[#ffd700]">User with Most Stars</h2>
                        <p class="text-lg sm:text-xl break-words text-[#FFF8DC]">{{ statistics.user_with_most_stars.last_name }}, {{ statistics.user_with_most_stars.first_name }}</p>
                        <p class="text-sm sm:text-base text-[#FFF8DC]/80">{{ statistics.user_with_most_stars.files_sum_stars }} stars</p>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#ffd700] bg-container p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] hover:scale-105 cursor-pointer">
                        <h2 class="text-base sm:text-lg font-semibold text-[#ffd700]">Most Quiz Items by User</h2>
                        <p class="text-lg sm:text-xl break-words text-[#FFF8DC]">{{ statistics.most_quizzes_by_user.last_name }}, {{ statistics.most_quizzes_by_user.first_name }}</p>
                        <p class="text-sm sm:text-base text-[#FFF8DC]/80">{{ statistics.most_quizzes_by_user.quizzes_count }} quiz items</p>
                    </div>
                </div>

                <!-- Charts section -->
                <div class="mb-6 sm:mb-8 grid grid-cols-1 gap-4 sm:gap-6 md:gap-8 md:grid-cols-2">
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]">
                        <h2 class="pixel-outline mb-3 sm:mb-4 text-lg sm:text-xl font-bold text-[#ffd700] text-center">Users/Students per Program</h2>
                        <div class="w-full max-w-xs sm:max-w-md h-[250px] relative">
                            <canvas id="filesPerProgramChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]">
                        <h2 class="pixel-outline mb-3 sm:mb-4 text-lg sm:text-xl font-bold text-[#ffd700] text-center">Files by Type</h2>
                        <div class="w-full max-w-xs sm:max-w-md h-[250px] relative">
                            <canvas id="filesByTypeChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
                <div class="mb-6 sm:mb-8 grid grid-cols-1 gap-4 sm:gap-6 md:gap-8 md:grid-cols-2">
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]">
                        <h2 class="pixel-outline mb-3 sm:mb-4 text-lg sm:text-xl font-bold text-[#ffd700] text-center">Quiz Items Per Program</h2>
                        <div class="w-full max-w-xs sm:max-w-md h-[250px] relative">
                            <canvas id="quizzesPerProgramChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]">
                        <h2 class="pixel-outline mb-3 sm:mb-4 text-lg sm:text-xl font-bold text-[#ffd700] text-center">Flashcard Items Per Program</h2>
                        <div class="w-full max-w-xs sm:max-w-md h-[250px] relative">
                            <canvas id="flashcardsPerProgramChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tag and Quiz lists -->
                <div class="grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2">
                    <div class="pixel-outline rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                        <h2 class="pixel-outline mb-3 sm:mb-4 text-lg sm:text-xl font-bold text-[#ffd700]">Flashcard Items per Tag</h2>
                        <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto">
                            <span
                                v-for="tag in statistics.total_flashcards_per_tag"
                                :key="tag.name"
                                class="pixel-outline mb-2 inline-flex items-center rounded-full border-2 border-[#ffd700]/50 bg-[#0c0a03] px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium text-[#FFF8DC] break-words hover:bg-[#ffd700]/10 transition-colors cursor-pointer"
                            >
                                <span class="truncate max-w-[200px] sm:max-w-none">{{ simplifyName(tag.name) }}: {{ tag.flashcards_count }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                        <h2 class="pixel-outline mb-3 sm:mb-4 text-lg sm:text-xl font-bold text-[#ffd700]">Quiz Items per Tag</h2>
                        <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto">
                            <span
                                v-for="tag in statistics.total_quizzes_per_tag"
                                :key="tag.name"
                                class="pixel-outline mb-2 inline-flex items-center rounded-full border-2 border-[#ffd700]/50 bg-[#0c0a03] px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium text-[#FFF8DC] break-words hover:bg-[#ffd700]/10 transition-colors cursor-pointer"
                            >
                                <span class="truncate max-w-[200px] sm:max-w-none">{{ simplifyName(tag.name) }}: {{ tag.quizzes_count }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Averages -->
                <div class="mt-6 sm:mt-8 grid grid-cols-1 gap-4 sm:gap-6 md:gap-8 md:grid-cols-2">
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] transition-all cursor-pointer">
                        <h2 class="text-base sm:text-lg font-semibold text-center text-[#ffd700]">Average Files per User</h2>
                        <p class="text-xl sm:text-2xl text-[#ffd700] font-bold">{{ statistics.average_files_per_user }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#ffd700] bg-container p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] transition-all cursor-pointer">
                        <h2 class="text-base sm:text-lg font-semibold text-center text-[#ffd700]">Average Flashcard Items per Quiz</h2>
                        <p class="text-xl sm:text-2xl text-[#ffd700] font-bold">{{ statistics.average_flashcards_per_quiz }}</p>
                    </div>
                </div>

                <!-- New statistics dashboard section -->
                <div class="mt-6 sm:mt-8 mb-6 sm:mb-8 grid grid-cols-1 gap-4 sm:gap-6 md:gap-8 md:grid-cols-2 xl:grid-cols-3">
                    <div class="pixel-outline flex flex-col rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] transition-all cursor-pointer h-fit">
                        <h2 class="mb-2 text-base sm:text-lg font-semibold text-[#ffd700]">New Entities (Last 7 Days)</h2>
                        <ul class="space-y-1 text-sm text-[#ffd700]">
                            <li>
                                Users: <span class="font-bold">{{ statistics.new_users_7d }}</span>
                            </li>
                            <li>
                                Files: <span class="font-bold">{{ statistics.new_files_7d }}</span>
                            </li>
                            <li>
                                Tags: <span class="font-bold">{{ statistics.new_tags_7d }}</span>
                            </li>
                            <li>
                                Programs: <span class="font-bold">{{ statistics.new_programs_7d }}</span>
                            </li>
                        </ul>
                        <h2 class="mt-4 mb-2 text-base sm:text-lg font-semibold text-[#ffd700]">New Entities (Last 30 Days)</h2>
                        <ul class="space-y-1 text-sm text-[#ffd700]">
                            <li>
                                Users: <span class="font-bold">{{ statistics.new_users_30d }}</span>
                            </li>
                            <li>
                                Files: <span class="font-bold">{{ statistics.new_files_30d }}</span>
                            </li>
                            <li>
                                Tags: <span class="font-bold">{{ statistics.new_tags_30d }}</span>
                            </li>
                            <li>
                                Programs: <span class="font-bold">{{ statistics.new_programs_30d }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pixel-outline flex flex-col rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] transition-all h-fit">
                        <h2 class="mb-3 text-base sm:text-lg font-semibold text-[#ffd700]">Latest Activity</h2>
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            <div class="mb-2">
                                <span class="font-bold text-[#FFF8DC] block mb-1">Recent Users:</span>
                                <ul class="text-xs text-[#FFF8DC]/80 space-y-1">
                                    <li v-for="user in statistics.latest_users.slice(0, 3)" :key="user.id" 
                                        class="flex justify-between items-center hover:text-[#ffd700] transition-colors cursor-pointer">
                                        <span class="truncate">{{ user.last_name }}, {{ user.first_name }}</span>
                                        <span class="text-[#FFF8DC]/50 text-xs ml-2 whitespace-nowrap">{{ formatTime(user.created_at) }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="mb-2">
                                <span class="font-bold text-[#FFF8DC] block mb-1">Recent Files:</span>
                                <ul class="text-xs text-[#FFF8DC]/80 space-y-1">
                                    <li v-for="file in statistics.latest_files.slice(0, 3)" :key="file.id" 
                                        class="flex justify-between items-center hover:text-[#ffd700] transition-colors cursor-pointer">
                                        <span class="truncate">{{ file.name }}</span>
                                        <span class="text-[#FFF8DC]/50 text-xs ml-2 whitespace-nowrap">{{ formatTime(file.created_at) }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="mb-2">
                                <span class="font-bold text-[#FFF8DC] block mb-1">Recent Tags:</span>
                                <ul class="text-xs text-[#FFF8DC]/80 space-y-1">
                                    <li v-for="tag in statistics.latest_tags.slice(0, 3)" :key="tag.id" 
                                        class="flex justify-between items-center hover:text-[#ffd700] transition-colors cursor-pointer">
                                        <span class="truncate">{{ simplifyName(tag.name) }}</span>
                                        <span class="text-[#FFF8DC]/50 text-xs ml-2 whitespace-nowrap">{{ formatTime(tag.created_at) }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="pixel-outline flex flex-col rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)] transition-all h-fit">
                        <h2 class="mb-3 text-base sm:text-lg font-semibold text-[#ffd700]">Popular Content</h2>
                        <ul class="space-y-3 text-sm text-[#FFF8DC]">
                            <li class="hover:text-[#ffd700] transition-colors cursor-pointer">
                                <span class="block font-bold">Most Popular File:</span>
                                <span class="text-[#FFF8DC]/90">{{ statistics.most_popular_file.name }}</span>
                                <span class="text-[#FFF8DC]/60 block text-xs">({{ statistics.most_popular_file.starred_by_count }} stars)</span>
                            </li>
                            <li class="hover:text-[#ffd700] transition-colors cursor-pointer">
                                <span class="block font-bold">Most Used Tag:</span>
                                <span class="text-[#FFF8DC]/90">{{ simplifyName(statistics.most_popular_tag.name) }}</span>
                                <span class="text-[#FFF8DC]/60 block text-xs">({{ statistics.most_popular_tag.files_count }} files)</span>
                            </li>
                            <li class="hover:text-[#ffd700] transition-colors cursor-pointer">
                                <span class="block font-bold">Top Program:</span>
                                <span class="text-[#FFF8DC]/90">{{
                                    simplifyName(statistics.most_popular_program.name, statistics.most_popular_program.code)
                                }}</span>
                                <span class="text-[#FFF8DC]/60 block text-xs">({{ statistics.most_popular_program.files_count }} files)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Access Logs Section -->
                <div class="pixel-outline mt-6 sm:mt-8 flex flex-col rounded-xl border-2 border-[#ffd700] bg-container p-4 sm:p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                        <h2 class="text-base sm:text-lg font-semibold text-[#ffd700]">Recent System Activity</h2>
                        <span class="text-xs sm:text-sm text-[#FFF8DC]/60 mt-1 sm:mt-0">Last {{ statistics.access_logs?.length || 0 }} activities</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs sm:text-sm">
                            <thead>
                                <tr class="border-b border-[#ffd700]/30">
                                    <th class="px-2 sm:px-3 py-2 text-left text-[#ffd700] font-semibold">User</th>
                                    <th class="px-2 sm:px-3 py-2 text-left text-[#ffd700] font-semibold">Route</th>
                                    <th class="px-2 sm:px-3 py-2 text-left text-[#ffd700] font-semibold">Method</th>
                                    <th class="px-2 sm:px-3 py-2 text-left text-[#ffd700] font-semibold">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(log, idx) in statistics.access_logs" :key="idx" 
                                    class="border-b border-[#ffd700]/10 hover:bg-[#ffd700]/5 transition-colors cursor-pointer"
                                    :class="{ 'bg-[#ffd700]/5': idx % 2 === 0 }">
                                    <td class="px-2 sm:px-3 py-2 text-[#FFF8DC] font-medium">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse" v-if="idx < 3"></div>
                                            <span class="truncate max-w-[100px] sm:max-w-none">{{ log.user }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 sm:px-3 py-2 text-[#FFF8DC]">
                                        <span class="font-mono text-xs bg-[#0c0a03] px-2 py-1 rounded border border-[#ffd700]/30 truncate block max-w-[120px] sm:max-w-none">
                                            {{ log.route }}
                                        </span>
                                    </td>
                                    <td class="px-2 sm:px-3 py-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-500/20 text-green-300 border border-green-500/30': log.method === 'GET',
                                                  'bg-blue-500/20 text-blue-300 border border-blue-500/30': log.method === 'POST',
                                                  'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30': log.method === 'PUT' || log.method === 'PATCH',
                                                  'bg-red-500/20 text-red-300 border border-red-500/30': log.method === 'DELETE'
                                              }">
                                            {{ log.method }}
                                        </span>
                                    </td>
                                    <td class="px-2 sm:px-3 py-2 text-[#FFF8DC]/80 text-xs whitespace-nowrap">
                                        {{ formatTime(log.accessed_at) }}
                                    </td>
                                </tr>
                                <tr v-if="!statistics.access_logs || statistics.access_logs.length === 0">
                                    <td colspan="4" class="px-2 sm:px-3 py-8 text-center text-[#FFF8DC]/60 italic">
                                        No recent activity logs available
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
