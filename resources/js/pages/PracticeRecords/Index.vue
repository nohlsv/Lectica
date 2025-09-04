<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <h1 class="text-2xl font-bold">History</h1>
            <div v-if="records.data.length === 0" class="text-muted-foreground text-center">No practice records found.</div>
            <div v-else class="space-y-4">
                <div v-for="record in records.data" :key="record.id" class="bg-muted rounded-lg border-2 p-4 shadow">
                    <h2 class="text-lg font-semibold">{{ record.file.name }} - {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</h2>
                    <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                    <Link :href="route('practice-records.show', record.id)" class="text-primary underline"> View Details </Link>
                </div>
            </div>
            <!-- Pagination -->
            <div v-if="records.last_page > 1" class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    <Link
                        v-for="page in paginationLinks"
                        :key="page.label"
                        :href="page.url || '#'"
                        :class="[
                            'rounded border px-3 py-1',
                            page.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                            !page.url && 'cursor-not-allowed opacity-50',
                        ]"
                        v-html="page.label"
                    ></Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Record {
    id: number;
    file: {
        name: string;
    };
    type: string;
    correct_answers: number;
    total_questions: number;
    mistakes: string; // JSON string
}
interface Props {
    records: {
        data: Record[];
        current_page: number;
        last_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
}

const props = defineProps<Props>();

const paginationLinks = computed(() => props.records.links);
</script>
