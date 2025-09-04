<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <h1 class="text-2xl font-bold">History Details</h1>
            <div class="bg-muted rounded-lg p-4 shadow">
                <h2 class="text-lg font-semibold">{{ record.file.name }}</h2>
                <p>Type: {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</p>
                <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                <div v-if="decodedMistakes && decodedMistakes.length > 0" class="mt-4">
                    <h3 class="text-lg font-semibold">Mistakes</h3>
                    <ul class="list-inside list-disc">
                        <li v-for="(mistake, index) in decodedMistakes" :key="index">
                            <strong>Question:</strong> {{ mistake.question }}<br />
                            <strong>Your Answer:</strong> {{ mistake.your_answer }}<br />
                            <strong>Correct Answer:</strong> {{ mistake.correct_answer }}
                        </li>
                    </ul>
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
