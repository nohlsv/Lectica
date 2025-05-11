<template>
    <AppLayout>
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold">Practice Records</h1>
            <div v-if="records.data.length === 0" class="text-center text-muted-foreground">
                No practice records found.
            </div>
            <div v-else class="space-y-4">
                <div v-for="record in records.data" :key="record.id" class="p-4 bg-muted border-2 rounded-lg shadow">
                    <h2 class="text-lg font-semibold">
                        {{ record.file.name }} - {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}
                    </h2>
                    <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                    <Link :href="route('practice-records.show', record.id)" class="text-primary underline">
                        View Details
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

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
</script>
