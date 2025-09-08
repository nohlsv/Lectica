<template>
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-6">
            <div class="flex items-center justify-center">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline w-fit px-10 py-2 text-center text-2xl font-bold">History</h1>
            </div>
            <div class="bg-container p-6">
                <div v-if="records.data.length === 0" class="text-muted-foreground text-center">No practice records found.</div>
                <div v-else class="grid grid-cols-1 gap-5 space-y-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4">
                    <div
                        v-for="record in records.data"
                        :key="record.id"
                        class="pixel-outline flex h-full flex-col rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow"
                    >
                        <h2 class="text-lg font-semibold">{{ record.file.name }} - {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</h2>
                        <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                        <button
                            class="text-primary pixel-outline mt-5 self-start rounded-md border-2 border-[#0c0a03] bg-[#10B981] px-2.5 py-0.5 text-base tracking-wide duration-300 hover:scale-105 hover:bg-[#0e9459]"
                        >
                            <Link :href="route('practice-records.show', record.id)"> View Details </Link>
                        </button>
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
                                'flex items-center justify-center rounded border px-3 py-1.5 text-sm sm:px-4 sm:py-2 sm:text-base',
                                page.active
                                    ? 'text-primary pixel-outline border-2 border-[#0c0a03] bg-[#B23A48]'
                                    : 'border-[#0c0a03] bg-[#3B1A14] duration-300 hover:bg-[#77252e]',
                                !page.url && 'cursor-not-allowed opacity-50',
                            ]"
                            v-html="page.label"
                        ></Link>
                    </div>
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
