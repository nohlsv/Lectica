<template>
    <AppLayout>
        <div class="p-6 space-y-4 bg-gradient min-h-screen">
            <div class="flex justify-center items-center">
                <h1 class="text-2xl font-bold welcome-banner animate-soft-bounce pixel-outline w-fit py-2 px-10">History</h1>
            </div>
            <div class="p-6 bg-container">
                <div v-if="records.data.length === 0" class="text-center text-muted-foreground">
                    No practice records found.
                </div>
                <div v-else class="space-y-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4 gap-5">
                    <div v-for="record in records.data" :key="record.id" class="h-full flex flex-col p-4 bg-[#8E2C38] border-[#0c0a03] border-2 pixel-outline rounded-lg shadow">
                        <h2 class="text-lg font-semibold">
                            {{ record.file.name }} - {{ record.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}
                        </h2>
                        <p>Score: {{ record.correct_answers }} / {{ record.total_questions }}</p>
                        <button class="bg-[#10B981] hover:bg-[#0e9459] hover:scale-105 duration-300 mt-5 text-base text-primary border-[#0c0a03] border-2 pixel-outline py-0.5 px-2.5 rounded-md tracking-wide self-start">
                        <Link :href="route('practice-records.show', record.id)">
                            View Details
                        </Link>
                        </button>
                    </div>
                </div>
                <!-- Pagination -->
                <div v-if="records.last_page > 1" class="flex justify-center mt-6">
                    <div class="flex space-x-2">
                        <Link
                            v-for="page in paginationLinks"
                            :key="page.label"
                            :href="page.url || '#'"
                            :class="[
                                'text-sm px-3 py-1.5 sm:text-base sm:px-4 sm:py-2 rounded border items-center justify-center flex',
                                page.active ? 'bg-[#B23A48] text-primary pixel-outline border-2 border-[#0c0a03]' : 'bg-[#3B1A14] border-[#0c0a03] hover:bg-[#77252e] duration-300',
                                !page.url && 'opacity-50 cursor-not-allowed'
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
