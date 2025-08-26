<template>
    <Head title="Quests" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Quests
                </h2>
                <Link
                    :href="route('quests.stats')"
                    class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded"
                >
                    View Statistics
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Quest Summary Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Today's Progress</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">{{ questSummary.completed_today }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Completed Today</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">{{ questSummary.total_today }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Available Today</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">{{ questSummary.completion_rate }}%</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Daily Progress</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ questSummary.completed_today }}/{{ questSummary.total_today }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div
                                    class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                    :style="`width: ${questSummary.completion_rate}%`"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quest Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-8 px-6">
                            <button
                                @click="activeTab = 'daily'"
                                :class="activeTab === 'daily'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                                class="py-4 px-1 border-b-2 font-medium text-sm"
                            >
                                Daily Quests ({{ dailyQuests.length }})
                            </button>
                            <button
                                @click="activeTab = 'weekly'"
                                :class="activeTab === 'weekly'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                                class="py-4 px-1 border-b-2 font-medium text-sm"
                            >
                                Weekly Quests ({{ weeklyQuests.length }})
                            </button>
                            <button
                                @click="activeTab = 'one_time'"
                                :class="activeTab === 'one_time'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400'"
                                class="py-4 px-1 border-b-2 font-medium text-sm"
                            >
                                Achievements ({{ oneTimeQuests.length }})
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Daily Quests -->
                        <div v-if="activeTab === 'daily'">
                            <div v-if="dailyQuests.length === 0" class="text-center py-8">
                                <div class="text-6xl mb-4">📅</div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No daily quests available</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Check back tomorrow for new daily challenges!
                                </p>
                            </div>
                            <div v-else class="grid gap-4">
                                <QuestCard
                                    v-for="quest in dailyQuests"
                                    :key="quest.id"
                                    :quest="quest"
                                    type="daily"
                                />
                            </div>
                        </div>

                        <!-- Weekly Quests -->
                        <div v-if="activeTab === 'weekly'">
                            <div v-if="weeklyQuests.length === 0" class="text-center py-8">
                                <div class="text-6xl mb-4">📆</div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No weekly quests available</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Weekly challenges will be available soon!
                                </p>
                            </div>
                            <div v-else class="grid gap-4">
                                <QuestCard
                                    v-for="quest in weeklyQuests"
                                    :key="quest.id"
                                    :quest="quest"
                                    type="weekly"
                                />
                            </div>
                        </div>

                        <!-- One-time Quests (Achievements) -->
                        <div v-if="activeTab === 'one_time'">
                            <div v-if="oneTimeQuests.length === 0" class="text-center py-8">
                                <div class="text-6xl mb-4">🏆</div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No achievements available</h3>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    Complete activities to unlock achievements!
                                </p>
                            </div>
                            <div v-else class="grid gap-4">
                                <QuestCard
                                    v-for="quest in oneTimeQuests"
                                    :key="quest.id"
                                    :quest="quest"
                                    type="achievement"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import QuestCard from '@/components/QuestCard.vue'
import { ref } from 'vue'

const props = defineProps({
    questSummary: Object,
    dailyQuests: Array,
    weeklyQuests: Array,
    oneTimeQuests: Array,
})

const activeTab = ref('daily')
</script>
