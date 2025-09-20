<template>
    <Head title="Quests" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Quests</h2>
                <Link :href="route('quests.stats')" class="rounded bg-purple-500 px-4 py-2 font-bold text-white hover:bg-purple-700">
                    View Statistics
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gradient min-h-screen">
            <div class="mx-full sm:px-6 lg:px-8 -my-12">
                <!-- Quest Summary Card -->
                <div class="mb-6 mx-full sm:-mx-7.25 overflow-hidden bg-lectica shadow-sm">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg sm:text-xl md:text-2xl font-semibold text-gray-100 pixel-outline wave text-yellow-500 text-center"><span>T</span><span>o</span><span>d</span><span>a</span><span>y</span><span>'</span><span>s</span><span>_</span><span>P</span><span>r</span><span>o</span><span>g</span><span>r</span><span>e</span><span>s</span><span>s</span></h3>
                        <div class="grid grid-cols-1 gap-6 sm:gap-4 md:grid-cols-3">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600 pixel-outline">{{ questSummary.completed_today }}</div>
                                <div class="text-sm text-gray-400 pixel-outline">Completed Today</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600 pixel-outline">{{ questSummary.total_today }}</div>
                                <div class="text-sm text-gray-400 pixel-outline">Available Today</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600 pixel-outline">{{ questSummary.completion_rate }}%</div>
                                <div class="text-sm text-gray-400 pixel-outline">Completion Rate</div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4 rounded-lg mx-full py-2 px-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-300 pixel-outline">Daily Progress</span>
                                <span class="text-sm text-gray-400 pixel-outline">
                                    {{ questSummary.completed_today }}/{{ questSummary.total_today }}
                                </span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-gray-800 pixel-outline-icon">
                                <div
                                    class="h-2.5 rounded-full bg-blue-600 transition-all duration-300"
                                    :style="`width: ${questSummary.completion_rate}%`"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <hr class="-mx-3.5 h-2 border-2 border-black bg-red-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
                </div>

                <!-- Quest Tabs -->
                <div class="overflow-hidden bg-container mx-4 mb-8 shadow-sm sm:rounded-lg">
                    <div class="border-b bg-black/60">
                        <nav class="flex space-x-8 px-6">
                            <button
                                @click="activeTab = 'daily'"
                                :class="
                                    activeTab === 'daily'
                                    ? 'border-yellow-500 text-yellow-600 pixel-outline'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 pixel-outline'
                                "
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Daily Quests ({{ dailyQuests.length }})
                            </button>
                            <button
                                @click="activeTab = 'weekly'"
                                :class="
                                    activeTab === 'weekly'
                                    ? 'border-yellow-500 text-yellow-600 pixel-outline'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 pixel-outline'
                                "
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Weekly Quests ({{ weeklyQuests.length }})
                            </button>
                            <button
                                @click="activeTab = 'one_time'"
                                :class="
                                    activeTab === 'one_time'
                                    ? 'border-yellow-500 text-yellow-600 pixel-outline'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 pixel-outline'
                                "
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Achievements ({{ oneTimeQuests.length }})
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Daily Quests -->
                        <div v-if="activeTab === 'daily'">
                            <div v-if="dailyQuests.length === 0" class="py-8 text-center">
                                <div class="mb-4 text-6xl">📅</div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No daily quests available</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Check back tomorrow for new daily challenges!</p>
                            </div>
                            <div v-else class="grid gap-4 sm:grid-cols-1">
                                <QuestCard v-for="quest in dailyQuests" :key="quest.id" :quest="quest" type="daily" />
                            </div>
                        </div>

                        <!-- Weekly Quests -->
                        <div v-if="activeTab === 'weekly'">
                            <div v-if="weeklyQuests.length === 0" class="py-8 text-center">
                                <div class="mb-4 text-6xl">📆</div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No weekly quests available</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Weekly challenges will be available soon!</p>
                            </div>
                            <div v-else class="grid gap-4 sm:grid-cols-1">
                                <QuestCard v-for="quest in weeklyQuests" :key="quest.id" :quest="quest" type="weekly" />
                            </div>
                        </div>

                        <!-- One-time Quests (Achievements) -->
                        <div v-if="activeTab === 'one_time'">
                            <div v-if="oneTimeQuests.length === 0" class="py-8 text-center">
                                <div class="mb-4 text-6xl">🏆</div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No achievements available</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Complete activities to unlock achievements!</p>
                            </div>
                            <div v-else class="grid gap-4 sm:grid-cols-1">
                                <QuestCard v-for="quest in oneTimeQuests" :key="quest.id" :quest="quest" type="achievement" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import QuestCard from '@/components/QuestCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    questSummary: Object,
    dailyQuests: Array,
    weeklyQuests: Array,
    oneTimeQuests: Array,
});

const activeTab = ref('daily');
</script>
