<template>
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-6">
            <div class="flex items-center justify-center">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline w-fit px-10 py-2 text-center text-2xl font-bold">History</h1>
            </div>
            <div class="bg-container p-6">
                <div v-if="groupedRecords.length === 0" class="text-muted-foreground text-center">No practice records found.</div>
                <div v-else class="grid grid-cols-1 gap-5 space-y-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4">
                    <div
                        v-for="group in groupedRecords"
                        :key="group.file.id"
                        class="pixel-outline flex h-full flex-col rounded-lg border-2 border-[#0c0a03] bg-[#8E2C38] p-4 shadow"
                    >
                        <h2 class="text-lg font-semibold cursor-pointer" @click="toggleGroup(group.file.id)">{{ group.file.name }}</h2>
                        <div v-if="expandedGroups[group.file.id]" class="mt-2">
                            <div v-for="attempt in group.attempts" :key="attempt.id" class="mb-2">
                                <div class="flex items-center justify-between">
                                    <span>
                                        <span class="font-bold">{{ attempt.type === 'flashcard' ? 'Flashcards' : 'Quiz' }}</span>
                                        <span class="ml-2">Score: {{ attempt.correct_answers }} / {{ attempt.total_questions }}</span>
                                        <span class="ml-2 text-xs text-gray-200">{{ formatDate(attempt.created_at) }}</span>
                                    </span>
                                    <Link :href="route('practice-records.show', attempt.id)" class="text-primary pixel-outline ml-2 rounded-md border-2 border-[#0c0a03] bg-[#10B981] px-2.5 py-0.5 text-base tracking-wide duration-300 hover:scale-105 hover:bg-[#0e9459]">
                                        View Details
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-xs text-gray-300 mt-2">Click to show attempts</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Attempt {
    id: number;
    type: string;
    correct_answers: number;
    total_questions: number;
    created_at: string;
    mistakes: string;
}
interface GroupedRecord {
    file: {
        id: number;
        name: string;
    };
    attempts: Attempt[];
}
interface Props {
    groupedRecords: GroupedRecord[];
}
const props = defineProps<Props>();
const expandedGroups = ref<{ [key: number]: boolean }>({});

function toggleGroup(fileId: number) {
    expandedGroups.value[fileId] = !expandedGroups.value[fileId];
}
function formatDate(dateStr: string) {
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>
