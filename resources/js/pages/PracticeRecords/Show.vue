<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold">Practice Record Details</h1>
            <div class="p-4 bg-white rounded-lg shadow">
                <h2 class="text-lg font-semibold">{{ record.file.name }}</h2>
                <p>Type: {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</p>
                <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                <div v-if="record.mistakes && record.mistakes.length > 0" class="mt-4">
                    <h3 class="text-lg font-semibold">Mistakes</h3>
                    <ul class="list-disc list-inside">
                        <li v-for="(mistake, index) in record.mistakes" :key="index">
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

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    record: Object,
});
</script>
