<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ArcElement, BarController, BarElement, CategoryScale, Chart, ChartTypeRegistry, LinearScale, PieController } from 'chart.js';
import { nextTick, onMounted } from 'vue';

// Register required Chart.js components
Chart.register(BarController, BarElement, CategoryScale, LinearScale, PieController, ArcElement);

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
    // New statistics
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
}

const props = defineProps<{ statistics: Statistics }>();

const renderChart = (id: string, type: keyof ChartTypeRegistry, data: any, options: any) => {
    const ctx = document.getElementById(id) as HTMLCanvasElement;
    new Chart(ctx, { type, data, options });
};

onMounted(async () => {
    await nextTick(); // Ensure DOM is fully rendered before accessing canvas elements

    renderChart(
        'filesPerProgramChart',
        'bar',
        {
            labels: props.statistics.most_files_per_program.map((p) => p.name),
            datasets: [
                {
                    label: 'Files per Program',
                    data: props.statistics.most_files_per_program.map((p) => p.files_count),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                },
            ],
        },
        { responsive: true },
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
                    backgroundColor: 'rgba(142, 44, 56, 0.2)',
                    borderColor: 'rgba(142, 44, 56, 1)',
                    borderWidth: 1,
                },
            ],
        },
        { responsive: true },
    );
    renderChart(
        'filesCreatedPerMonthChart',
        'line',
        {
            labels: props.statistics.files_created_per_month.map((m) => m.month),
            datasets: [
                {
                    label: 'Files Created Per Month',
                    data: props.statistics.files_created_per_month.map((m) => m.count),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                },
            ],
        },
        { responsive: true },
    );
    renderChart(
        'storagePerProgramChart',
        'bar',
        {
            labels: props.statistics.storage_per_program.map((p) => p.name),
            datasets: [
                {
                    label: 'Storage Usage Per Program (MB)',
                    data: props.statistics.storage_per_program.map((p) => p.storage_mb),
                    backgroundColor: 'rgba(44, 142, 56, 0.2)',
                    borderColor: 'rgba(44, 142, 56, 1)',
                    borderWidth: 1,
                },
            ],
        },
        { responsive: true },
    );
    renderChart(
        'quizzesPerProgramChart',
        'bar',
        {
            labels: props.statistics.quizzes_per_program.map((p) => p.name),
            datasets: [
                {
                    label: 'Quizzes Per Program',
                    data: props.statistics.quizzes_per_program.map((p) => p.quizzes_count),
                    backgroundColor: 'rgba(44, 56, 142, 0.2)',
                    borderColor: 'rgba(44, 56, 142, 1)',
                    borderWidth: 1,
                },
            ],
        },
        { responsive: true },
    );
    renderChart(
        'flashcardsPerProgramChart',
        'bar',
        {
            labels: props.statistics.flashcards_per_program.map((p) => p.name),
            datasets: [
                {
                    label: 'Flashcards Per Program',
                    data: props.statistics.flashcards_per_program.map((p) => p.flashcards_count),
                    backgroundColor: 'rgba(142, 142, 44, 0.2)',
                    borderColor: 'rgba(142, 142, 44, 1)',
                    borderWidth: 1,
                },
            ],
        },
        { responsive: true },
    );
});
</script>

<template>
    <Head title="Usage and Statistics" />
    <AppLayout>
        <div class="bg-gradient min-h-screen py-10">
            <div class="flex items-center justify-between mb-8 px-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Usage & Statistics Dashboard</h1>
                <span class="text-sm text-gray-500 dark:text-gray-400">Updated: {{ new Date().toLocaleDateString() }}</span>
            </div>
            <div class="mx-auto max-w-7xl px-6">
                <!-- Top stats cards -->
                <!-- Card and chart containers: update for reddish background -->
                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-6 mb-8">
                    <div class="rounded-xl border-2 border-[#8E2C38] bg-[#2c0a0e] p-6 flex flex-col items-center shadow-lg">
                        <h2 class="text-lg font-semibold mb-2 text-[#ffb3b3]">Users</h2>
                        <p class="text-4xl font-extrabold text-[#ffb3b3]">{{ statistics.total_users }}</p>
                    </div>
                    <div class="rounded-xl border-2 border-[#8E2C38] bg-[#2c0a0e] p-6 flex flex-col items-center shadow-lg">
                        <h2 class="text-lg font-semibold mb-2 text-[#ffb3b3]">Files</h2>
                        <p class="text-4xl font-extrabold text-[#ffb3b3]">{{ statistics.total_files }}</p>
                    </div>
                    <div class="rounded-xl border-2 border-[#8E2C38] bg-[#2c0a0e] p-6 flex flex-col items-center shadow-lg">
                        <h2 class="text-lg font-semibold mb-2 text-[#ffb3b3]">Quizzes</h2>
                        <p class="text-4xl font-extrabold text-[#ffb3b3]">{{ statistics.total_quizzes }}</p>
                    </div>
                    <div class="rounded-xl border-2 border-[#8E2C38] bg-[#2c0a0e] p-6 flex flex-col items-center shadow-lg">
                        <h2 class="text-lg font-semibold mb-2 text-[#ffb3b3]">Flashcards</h2>
                        <p class="text-4xl font-extrabold text-[#ffb3b3]">{{ statistics.total_flashcards }}</p>
                    </div>
                    <div class="rounded-xl border-2 border-[#8E2C38] bg-[#2c0a0e] p-6 flex flex-col items-center shadow-lg">
                        <h2 class="text-lg font-semibold mb-2 text-[#ffb3b3]">Tags</h2>
                        <p class="text-4xl font-extrabold text-[#ffb3b3]">{{ statistics.total_tags }}</p>
                    </div>
                    <div class="rounded-xl border-2 border-[#8E2C38] bg-[#2c0a0e] p-6 flex flex-col items-center shadow-lg">
                        <h2 class="text-lg font-semibold mb-2 text-[#ffb3b3]">Programs</h2>
                        <p class="text-4xl font-extrabold text-[#ffb3b3]">{{ statistics.total_programs }}</p>
                    </div>
                </div>

                <!-- Secondary stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                        <h2 class="text-lg font-semibold">Most Quizzes by User</h2>
                        <p class="text-xl">{{ statistics.most_quizzes_by_user.last_name }}, {{ statistics.most_quizzes_by_user.first_name }}</p>
                        <p>{{ statistics.most_quizzes_by_user.quizzes_count }} quizzes</p>
                    </div>
                </div>

                <!-- Charts section -->
                <!-- Chart containers -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="bg-[#2c0a0e] rounded-xl shadow-lg p-6 flex flex-col items-center border-2 border-[#8E2C38]">
                        <h2 class="text-xl font-bold mb-4 text-[#ffb3b3]">Files per Program</h2>
                        <canvas id="filesPerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                    <div class="bg-[#2c0a0e] rounded-xl shadow-lg p-6 flex flex-col items-center border-2 border-[#8E2C38]">
                        <h2 class="text-xl font-bold mb-4 text-[#ffb3b3]">Files by Type</h2>
                        <canvas id="filesByTypeChart" class="w-full max-w-md"></canvas>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="bg-[#2c0a0e] rounded-xl shadow-lg p-6 flex flex-col items-center border-2 border-[#8E2C38]">
                        <h2 class="text-xl font-bold mb-4 text-[#ffb3b3]">Files Created Per Month</h2>
                        <canvas id="filesCreatedPerMonthChart" class="w-full max-w-md"></canvas>
                    </div>
                    <div class="bg-[#2c0a0e] rounded-xl shadow-lg p-6 flex flex-col items-center border-2 border-[#8E2C38]">
                        <h2 class="text-xl font-bold mb-4 text-[#ffb3b3]">Storage Usage Per Program</h2>
                        <canvas id="storagePerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="bg-[#2c0a0e] rounded-xl shadow-lg p-6 flex flex-col items-center border-2 border-[#8E2C38]">
                        <h2 class="text-xl font-bold mb-4 text-[#ffb3b3]">Quizzes Per Program</h2>
                        <canvas id="quizzesPerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                    <div class="bg-[#2c0a0e] rounded-xl shadow-lg p-6 flex flex-col items-center border-2 border-[#8E2C38]">
                        <h2 class="text-xl font-bold mb-4 text-[#ffb3b3]">Flashcards Per Program</h2>
                        <canvas id="flashcardsPerProgramChart" class="w-full max-w-md"></canvas>
                    </div>
                </div>

                <!-- Tag and Quiz lists -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h2 class="pixel-outline text-xl font-bold mb-2">Flashcards per Tag</h2>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in statistics.total_flashcards_per_tag"
                                :key="tag.name"
                                class="text-primary pixel-outline inline-flex items-center rounded-full border-2 border-[#0c0a03] bg-[#8E2C38] px-3 py-1 text-sm font-medium mb-2"
                            >
                                {{ tag.name }}: {{ tag.flashcards_count }} flashcards
                            </span>
                        </div>
                    </div>
                    <div>
                        <h2 class="pixel-outline text-xl font-bold mb-2">Quizzes per Tag</h2>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in statistics.total_quizzes_per_tag"
                                :key="tag.name"
                                class="pixel-outline inline-flex items-center rounded-full border-2 border-[#0c0a03] bg-[#8E2C38] px-3 py-1 text-sm font-medium mb-2"
                            >
                                {{ tag.name }}: {{ tag.quizzes_count }} quizzes
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Averages -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow flex flex-col items-center">
                        <h2 class="text-lg font-semibold">Average Files per User</h2>
                        <p class="text-2xl">{{ statistics.average_files_per_user }}</p>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow flex flex-col items-center">
                        <h2 class="text-lg font-semibold">Average Flashcards per Quiz</h2>
                        <p class="text-2xl">{{ statistics.average_flashcards_per_quiz }}</p>
                    </div>
                </div>

                <!-- New statistics dashboard section -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mb-8">
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow flex flex-col">
                        <h2 class="text-lg font-semibold mb-2">New Entities (Last 7 Days)</h2>
                        <ul class="text-sm space-y-1">
                            <li>Users: <span class="font-bold">{{ statistics.new_users_7d }}</span></li>
                            <li>Files: <span class="font-bold">{{ statistics.new_files_7d }}</span></li>
                            <li>Quizzes: <span class="font-bold">{{ statistics.new_quizzes_7d }}</span></li>
                            <li>Flashcards: <span class="font-bold">{{ statistics.new_flashcards_7d }}</span></li>
                            <li>Tags: <span class="font-bold">{{ statistics.new_tags_7d }}</span></li>
                            <li>Programs: <span class="font-bold">{{ statistics.new_programs_7d }}</span></li>
                        </ul>
                        <h2 class="text-lg font-semibold mt-4 mb-2">New Entities (Last 30 Days)</h2>
                        <ul class="text-sm space-y-1">
                            <li>Users: <span class="font-bold">{{ statistics.new_users_30d }}</span></li>
                            <li>Files: <span class="font-bold">{{ statistics.new_files_30d }}</span></li>
                            <li>Quizzes: <span class="font-bold">{{ statistics.new_quizzes_30d }}</span></li>
                            <li>Flashcards: <span class="font-bold">{{ statistics.new_flashcards_30d }}</span></li>
                            <li>Tags: <span class="font-bold">{{ statistics.new_tags_30d }}</span></li>
                            <li>Programs: <span class="font-bold">{{ statistics.new_programs_30d }}</span></li>
                        </ul>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow flex flex-col">
                        <h2 class="text-lg font-semibold mb-2">Latest Entities</h2>
                        <div class="mb-2">
                            <span class="font-bold">Users:</span>
                            <ul class="text-xs">
                                <li v-for="user in statistics.latest_users" :key="user.id">{{ user.last_name }}, {{ user.first_name }} <span class="text-gray-400">({{ user.created_at }})</span></li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <span class="font-bold">Files:</span>
                            <ul class="text-xs">
                                <li v-for="file in statistics.latest_files" :key="file.id">{{ file.name }} <span class="text-gray-400">({{ file.created_at }})</span></li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <span class="font-bold">Quizzes:</span>
                            <ul class="text-xs">
                                <li v-for="quiz in statistics.latest_quizzes" :key="quiz.id">{{ quiz.name }} <span class="text-gray-400">({{ quiz.created_at }})</span></li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <span class="font-bold">Flashcards:</span>
                            <ul class="text-xs">
                                <li v-for="flashcard in statistics.latest_flashcards" :key="flashcard.id">{{ flashcard.question }} <span class="text-gray-400">({{ flashcard.created_at }})</span></li>
                            </ul>
                        </div>
                        <div class="mb-2">
                            <span class="font-bold">Tags:</span>
                            <ul class="text-xs">
                                <li v-for="tag in statistics.latest_tags" :key="tag.id">{{ tag.name }} <span class="text-gray-400">({{ tag.created_at }})</span></li>
                            </ul>
                        </div>
                        <div>
                            <span class="font-bold">Programs:</span>
                            <ul class="text-xs">
                                <li v-for="program in statistics.latest_programs" :key="program.id">{{ program.name }} <span class="text-gray-400">({{ program.created_at }})</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="pixel-outline rounded-xl border-2 border-[#0c0a03] bg-[#8E2C38] p-6 shadow flex flex-col">
                        <h2 class="text-lg font-semibold mb-2">Popular Entities</h2>
                        <ul class="text-sm space-y-1">
                            <li>File: <span class="font-bold">{{ statistics.most_popular_file.name }}</span> <span class="text-gray-400">({{ statistics.most_popular_file.starred_by_count }} stars)</span></li>
                            <li>Tag: <span class="font-bold">{{ statistics.most_popular_tag.name }}</span> <span class="text-gray-400">({{ statistics.most_popular_tag.files_count }} files)</span></li>
                            <li>Program: <span class="font-bold">{{ statistics.most_popular_program.name }}</span> <span class="text-gray-400">({{ statistics.most_popular_program.files_count }} files)</span></li>
                        </ul>
                        <h2 class="text-lg font-semibold mt-4 mb-2">Storage</h2>
                        <ul class="text-sm space-y-1">
                            <li>Total Storage Used: <span class="font-bold">{{ statistics.total_storage_used_mb }} MB</span></li>
                            <li>Average File Size: <span class="font-bold">{{ statistics.average_file_size_kb }} KB</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
