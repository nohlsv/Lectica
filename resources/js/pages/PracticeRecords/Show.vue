<template>
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-6">
            <div class="flex items-center justify-center">
                <h1 class="welcome-banner animate-soft-bounce w-fit px-10 py-2 text-2xl font-bold">History Details</h1>
            </div>
            <div class="bg-container p-6">
                <div class="pixel-outline rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4">
                    <h2 class="text-xl font-semibold">{{ record.file.name }}</h2>
                    <p>Type: {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</p>
                    <p>
                        <span class="font-bold">Score:</span> {{ record.correct_answers }} / {{ record.total_questions }} (
                        <span :class="scoreColorClass">{{ formattedScorePercentage }}</span> )
                    </p>
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
import { computed } from 'vue';
interface Props {
    record: {
        id: number;
        file: {
            name: string;
        };
        type: string;
        correct_answers: number;
        total_questions: number;
        mistakes: string; // JSON string
    };
}

const props = defineProps<Props>();

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
</script>
