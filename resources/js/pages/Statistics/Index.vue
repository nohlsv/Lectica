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
    user_with_most_stars: { last_name: string; first_name: string; files_sum_stars: number }; // New statistic
    most_quizzes_by_user: { last_name: string; first_name: string; quizzes_count: number }; // Updated statistic
    average_flashcards_per_quiz: number; // Updated statistic
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

    // renderChart('tagsUsageChart', 'pie', {
    //     labels: props.statistics.most_used_tags.map((t) => t.name),
    //     datasets: [{
    //         label: 'Tag Usage',
    //         data: props.statistics.most_used_tags.map((t) => t.files_count),
    //         backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
    //     }],
    // }, { responsive: true });
});
</script>

<template>
    <Head title="Usage and Statistics" />
    <AppLayout>
        <div class="bg-gradient space-y-4 p-6">
            <div class="flex items-center justify-center">
                <h1 class="welcome-banner pixel-outline animate-soft-bounce w-fit px-10 py-2 text-center text-2xl font-bold">Usage and Statistics</h1>
            </div>
            <div class="bg-container min-h-screen p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Total Users</h2>
                        <p class="text-3xl font-bold">{{ statistics.total_users }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Total Files</h2>
                        <p class="text-3xl font-bold">{{ statistics.total_files }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Total Quizzes</h2>
                        <p class="text-3xl font-bold">{{ statistics.total_quizzes }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Total Flashcards</h2>
                        <p class="text-3xl font-bold">{{ statistics.total_flashcards }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Total Tags</h2>
                        <p class="text-3xl font-bold">{{ statistics.total_tags }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Total Programs</h2>
                        <p class="text-3xl font-bold">{{ statistics.total_programs }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Most Active User</h2>
                        <p class="text-xl">{{ statistics.most_active_user.last_name }}, {{ statistics.most_active_user.first_name }}</p>
                        <p>{{ statistics.most_active_user.files_count }} files</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Average Files per User</h2>
                        <p class="text-2xl">{{ statistics.average_files_per_user }}</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">User with Most Stars</h2>
                        <p class="text-xl">{{ statistics.user_with_most_stars.last_name }}, {{ statistics.user_with_most_stars.first_name }}</p>
                        <p>{{ statistics.user_with_most_stars.files_sum_stars }} stars</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Most Quizzes by User</h2>
                        <p class="text-xl">{{ statistics.most_quizzes_by_user.last_name }}, {{ statistics.most_quizzes_by_user.first_name }}</p>
                        <p>{{ statistics.most_quizzes_by_user.quizzes_count }} quizzes</p>
                    </div>
                    <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                        <h2 class="text-lg font-semibold">Average Flashcards per Quiz</h2>
                        <p class="text-2xl">{{ statistics.average_flashcards_per_quiz }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="pixel-outline text-xl font-bold">Files per Program</h2>
                    <div class="mx-auto w-full max-w-md">
                        <canvas id="filesPerProgramChart"></canvas>
                    </div>
                </div>

                <!-- <div class="mt-6">
                    <h2 class="text-xl font-bold">Tag Usage</h2>
                    <div class="w-full max-w-md mx-auto">
                        <canvas id="tagsUsageChart"></canvas>
                    </div>
                </div> -->

                <div class="mt-6">
                    <h2 class="pixel-outline text-xl font-bold">Flashcards per Tag</h2>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in statistics.total_flashcards_per_tag"
                            :key="tag.name"
                            class="text-primary pixel-outline inline-flex items-center rounded-full border-2 border-[#0c0a03] bg-[#8E2C38] px-3 py-1 text-sm font-medium"
                        >
                            {{ tag.name }}: {{ tag.flashcards_count }} flashcards
                        </span>
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="pixel-outline text-xl font-bold">Quizzes per Tag</h2>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in statistics.total_quizzes_per_tag"
                            :key="tag.name"
                            class="pixel-outline inline-flex items-center rounded-full border-2 border-[#0c0a03] bg-[#8E2C38] px-3 py-1 text-sm font-medium"
                        >
                            {{ tag.name }}: {{ tag.quizzes_count }} quizzes
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
