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

// College and program acronyms as in ProgramSeeder
const collegeAcronyms: { [key: string]: string } = {
    'College of Computer Studies': 'CCS',
    'College of Engineering': 'COE',
    'College of Business': 'COB',
    'College of Technology': 'COT',
    'College of Allied Health and Sciences': 'CAHS',
};
const programAcronyms: { [key: string]: string } = {
    'Computer Science': 'CS',
    'Information Technology': 'IT',
    'Entertainment and Multimedia Computing': 'EMC',
    'Civil Engineering': 'CE',
    'Electrical Engineering': 'EE',
    'Mechanical Engineering': 'ME',
    'Industrial Engineering': 'IE',
    'Architecture': 'ARC',
    'Business Administration': 'BA',
    'Industrial Technology': 'ITECH',
    'Nursing': 'NUR',
    'Midwifery': 'MID',
};

// Use program code or college acronym if available, else fallback to acronym from first letters
const simplifyName = (name: string): string => {
    if (programAcronyms[name]) return programAcronyms[name];
    if (collegeAcronyms[name]) return collegeAcronyms[name];
    if (name.length > 20) {
        return name.split(' ').map(word => word.charAt(0).toUpperCase()).join('');
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
    most_files_per_program: Array<{ name: string; files_count: number }>;
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
    latest_programs: Array<{ id: number; name: string; created_at: string }>;
    most_popular_file: { id: number; name: string; starred_by_count: number };
    most_popular_tag: { id: number; name: string; files_count: number };
    most_popular_program: { id: number; name: string; files_count: number };
    total_storage_used_mb: number;
    average_file_size_kb: number;
    files_by_type: Array<{ extension: string; count: number }>;
    files_created_per_month: Array<{ month: string; count: number }>;
    storage_per_program: Array<{ name: string; storage_mb: number }>;
    quizzes_per_program: Array<{ name: string; quizzes_count: number }>;
    flashcards_per_program: Array<{ name: string; flashcards_count: number }>;
    access_logs: Array<{ user: string; route: string; method: string; accessed_at: string }>;
}

const props = defineProps<{ statistics: Statistics }>();

const renderChart = (id: string, type: keyof ChartTypeRegistry, data: any, options: any) => {
    const ctx = document.getElementById(id) as HTMLCanvasElement;
    new Chart(ctx, { type, data, options });
};

onMounted(async () => {
    await nextTick();
    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    color: '#fff',
                    font: { weight: 'bold' },
                },
            },
            title: {
                color: '#fff',
            },
            tooltip: {
                enabled: true,
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#fff',
                borderWidth: 1,
                displayColors: false,
                callbacks: {
                    label: function(context: any) {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y;
                        const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return [
                            `${label}: ${value}`,
                            `Percentage: ${percentage}%`
                        ];
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: { color: '#fff' },
                grid: { color: 'rgba(255,255,255,0.15)' },
            },
            y: {
                ticks: { color: '#fff' },
                grid: { color: 'rgba(255,255,255,0.15)' },
            },
        },
    };

    // Users/Students per Program chart
    renderChart(
        'filesPerProgramChart',
        'bar',
        {
            labels: props.statistics.users_per_program.map((p) => simplifyName(p.name)),
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
            labels: props.statistics.storage_per_program.map((p) => simplifyName(p.name)),
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
            labels: props.statistics.quizzes_per_program.map((p) => simplifyName(p.name)),
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
            labels: props.statistics.flashcards_per_program.map((p) => simplifyName(p.name)),
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
        <div class="bg-gradient min-h-screen py-10 stats-readable-font">
            <div class="mb-8 flex items-center justify-between px-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Usage & Statistics Dashboard</h1>
                <span class="text-sm text-gray-500 dark:text-gray-400">Updated: {{ new Date().toLocaleDateString() }}</span>
            </div>
            <div class="mx-auto max-w-7xl px-6">
                <!-- Top stats cards -->
                <div class="mb-8 grid grid-cols-2 gap-6 md:grid-cols-4 xl:grid-cols-6">
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold">Users</h2>
                        <p class="text-4xl font-extrabold">{{ statistics.total_users }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold">Files</h2>
                        <p class="text-4xl font-extrabold">{{ statistics.total_files }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold">Quiz Items</h2>
                        <p class="text-4xl font-extrabold">{{ statistics.total_quizzes }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold">Flashcard Items</h2>
                        <p class="text-4xl font-extrabold">{{ statistics.total_flashcards }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold">Tags</h2>
                        <p class="text-4xl font-extrabold">{{ statistics.total_tags }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow-md">
                        <h2 class="mb-2 text-lg font-semibold">Programs</h2>
                        <p class="text-4xl font-extrabold">{{ statistics.total_programs }}</p>
                    </div>
                </div>

                <!-- Secondary stats -->
                <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow">
                        <h2 class="text-lg font-semibold">Most Active User</h2>
                        <p class="text-xl">{{ statistics.most_active_user.last_name }}, {{ statistics.most_active_user.first_name }}</p>
                        <p>{{ statistics.most_active_user.files_count }} files</p>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow">
                        <h2 class="text-lg font-semibold">User with Most Stars</h2>
                        <p class="text-xl">{{ statistics.user_with_most_stars.last_name }}, {{ statistics.user_with_most_stars.first_name }}</p>
                        <p>{{ statistics.user_with_most_stars.files_sum_stars }} stars</p>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow">
                        <h2 class="text-lg font-semibold">Most Quiz Items by User</h2>
                        <p class="text-xl">{{ statistics.most_quizzes_by_user.last_name }}, {{ statistics.most_quizzes_by_user.first_name }}</p>
                        <p>{{ statistics.most_quizzes_by_user.quizzes_count }} quiz items</p>
                    </div>
                </div>

                <!-- Charts section -->
                <div class="mb-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="flex flex-col items-center rounded-xl bg-[#8E2C38] p-6 shadow">
                        <h2 class="pixel-outline mb-4 text-xl font-bold text-white">Users/Students per Program</h2>
                        <canvas id="filesPerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                    <div class="flex flex-col items-center rounded-xl bg-[#8E2C38] p-6 shadow">
                        <h2 class="pixel-outline mb-4 text-xl font-bold text-white">Files by Type</h2>
                        <canvas id="filesByTypeChart" class="w-full max-w-md"></canvas>
                    </div>
                </div>
                <div class="mb-8 grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                    <div class="flex flex-col items-center rounded-xl bg-[#8E2C38] p-6 shadow">
                        <h2 class="pixel-outline mb-4 text-xl font-bold text-white">Storage Usage Per Program</h2>
                        <canvas id="storagePerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                    <div class="flex flex-col items-center rounded-xl bg-[#8E2C38] p-6 shadow">
                        <h2 class="pixel-outline mb-4 text-xl font-bold text-white">Quiz Items Per Program</h2>
                        <canvas id="quizzesPerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                    <div class="flex flex-col items-center rounded-xl bg-[#8E2C38] p-6 shadow">
                        <h2 class="pixel-outline mb-4 text-xl font-bold text-white">Flashcard Items Per Program</h2>
                        <canvas id="flashcardsPerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                </div>

                <!-- Tag and Quiz lists -->
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        <h2 class="pixel-outline mb-2 text-xl font-bold">Flashcard Items per Tag</h2>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in statistics.total_flashcards_per_tag"
                                :key="tag.name"
                                class="text-primary pixel-outline mb-2 inline-flex items-center rounded-full border-2 border-[#0c0a03] bg-[#8E2C38] px-3 py-1 text-sm font-medium"
                            >
                                {{ simplifyName(tag.name) }}: {{ tag.flashcards_count }} flashcard items
                            </span>
                        </div>
                    </div>
                    <div>
                        <h2 class="pixel-outline mb-2 text-xl font-bold">Quiz Items per Tag</h2>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in statistics.total_quizzes_per_tag"
                                :key="tag.name"
                                class="pixel-outline mb-2 inline-flex items-center rounded-full border-2 border-[#0c0a03] bg-[#8E2C38] px-3 py-1 text-sm font-medium"
                            >
                                {{ simplifyName(tag.name) }}: {{ tag.quizzes_count }} quiz items
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Averages -->
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow">
                        <h2 class="text-lg font-semibold">Average Files per User</h2>
                        <p class="text-2xl">{{ statistics.average_files_per_user }}</p>
                    </div>
                    <div class="pixel-outline flex flex-col items-center rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow">
                        <h2 class="text-lg font-semibold">Average Flashcard Items per Quiz</h2>
                        <p class="text-2xl">{{ statistics.average_flashcards_per_quiz }}</p>
                    </div>
                </div>

                <!-- New statistics dashboard section -->
                <div class="mb-8 grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
                    <div class="pixel-outline flex flex-col rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow">
                        <h2 class="mb-2 text-lg font-semibold">New Entities (Last 7 Days)</h2>
                        <ul class="space-y-1 text-sm">
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
                        <h2 class="mt-4 mb-2 text-lg font-semibold">New Entities (Last 30 Days)</h2>
                        <ul class="space-y-1 text-sm">
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
                    <div class="pixel-outline flex flex-col rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow">
                        <h2 class="mb-2 text-lg font-semibold">Latest Entities</h2>
                        <div class="mb-2">
                            <span class="font-bold">Users:</span>
                            <ul class="text-xs">
                                <li v-for="user in statistics.latest_users" :key="user.id">
                                    {{ user.last_name }}, {{ user.first_name }} <span class="text-gray-400">({{ user.created_at }})</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <span class="font-bold">Files:</span>
                            <ul class="text-xs">
                                <li v-for="file in statistics.latest_files" :key="file.id">
                                    {{ file.name }} <span class="text-gray-400">({{ file.created_at }})</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <span class="font-bold">Tags:</span>
                            <ul class="text-xs">
                                <li v-for="tag in statistics.latest_tags" :key="tag.id">
                                    {{ simplifyName(tag.name) }} <span class="text-gray-400">({{ tag.created_at }})</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <span class="font-bold">Programs:</span>
                            <ul class="text-xs">
                                <li v-for="program in statistics.latest_programs" :key="program.id">
                                    {{ simplifyName(program.name) }} <span class="text-gray-400">({{ program.created_at }})</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="pixel-outline flex flex-col rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow">
                        <h2 class="mb-2 text-lg font-semibold">Popular Entities</h2>
                        <ul class="space-y-1 text-sm">
                            <li>
                                File: <span class="font-bold">{{ statistics.most_popular_file.name }}</span>
                                <span class="text-gray-400">({{ statistics.most_popular_file.starred_by_count }} stars)</span>
                            </li>
                            <li>
                                Tag: <span class="font-bold">{{ simplifyName(statistics.most_popular_tag.name) }}</span>
                                <span class="text-gray-400">({{ statistics.most_popular_tag.files_count }} files)</span>
                            </li>
                            <li>
                                Program: <span class="font-bold">{{ simplifyName(statistics.most_popular_program.name) }}</span>
                                <span class="text-gray-400">({{ statistics.most_popular_program.files_count }} files)</span>
                            </li>
                        </ul>
                        <h2 class="mt-4 mb-2 text-lg font-semibold">Storage</h2>
                        <ul class="space-y-1 text-sm">
                            <li>
                                Total Storage Used: <span class="font-bold">{{ statistics.total_storage_used_mb }} MB</span>
                            </li>
                            <li>
                                Average File Size: <span class="font-bold">{{ statistics.average_file_size_kb }} KB</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Access Logs Section -->
                <div class="pixel-outline mt-8 flex flex-col rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow">
                    <h2 class="mb-2 text-lg font-semibold">Recent Access Logs</h2>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="px-2 py-1 text-left">User</th>
                                <th class="px-2 py-1 text-left">Route</th>
                                <th class="px-2 py-1 text-left">Method</th>
                                <th class="px-2 py-1 text-left">Accessed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(log, idx) in statistics.access_logs" :key="idx" class="border-b border-gray-800">
                                <td class="px-2 py-1">{{ log.user }}</td>
                                <td class="px-2 py-1">{{ log.route }}</td>
                                <td class="px-2 py-1">{{ log.method }}</td>
                                <td class="px-2 py-1">{{ new Date(log.accessed_at).toLocaleString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
