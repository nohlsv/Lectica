<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold">Practice Record Details</h1>
            <div class="p-4 bg-muted rounded-lg shadow">
                <h2 class="text-lg font-semibold">{{ record.file.name }}</h2>
                <p>Type: {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</p>
                <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                <div v-if="decodedMistakes && decodedMistakes.length > 0" class="mt-4">
                    <h3 class="text-lg font-semibold">Mistakes</h3>
                    <ul class="list-disc list-inside">
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
console.log('Record:', props.record);

const decodedMistakes = computed(() => {
    try {
        console.log('Mistakes JSON:', JSON.parse(props.record.mistakes || '[]'));
        return JSON.parse(props.record.mistakes || '[]');
    } catch (error) {
        console.error('Failed to parse mistakes JSON:', error);
        return [];
    }
});
</script>
