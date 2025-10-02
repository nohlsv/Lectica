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

        <div class="bg-gradient min-h-screen py-12">
            <div class="mx-full -my-12 sm:px-6 lg:px-8">
                <!-- Quest Summary Card -->
                <div class="mx-full bg-lectica mb-6 overflow-hidden shadow-sm sm:-mx-7.25">
                    <div class="p-6">
                        <h3 class="pixel-outline wave mb-4 text-center text-lg font-semibold text-gray-100 text-yellow-500 sm:text-xl md:text-2xl">
                            <span>T</span><span>o</span><span>d</span><span>a</span><span>y</span><span>'</span><span>s</span><span>_</span
                            ><span>P</span><span>r</span><span>o</span><span>g</span><span>r</span><span>e</span><span>s</span><span>s</span>
                        </h3>
                        <div class="grid grid-cols-1 gap-6 sm:gap-4 md:grid-cols-3">
                            <div class="text-center">
                                <div class="pixel-outline text-3xl font-bold text-blue-600">{{ questSummary.completed_today }}</div>
                                <div class="pixel-outline text-sm text-gray-400">Completed Today</div>
                            </div>
                            <div class="text-center">
                                <div class="pixel-outline text-3xl font-bold text-green-600">{{ questSummary.total_today }}</div>
                                <div class="pixel-outline text-sm text-gray-400">Available Today</div>
                            </div>
                            <div class="text-center">
                                <div class="pixel-outline text-3xl font-bold text-purple-600">{{ questSummary.completion_rate }}%</div>
                                <div class="pixel-outline text-sm text-gray-400">Completion Rate</div>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mx-full mt-4 rounded-lg px-3 py-2">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="pixel-outline text-sm font-medium text-gray-300">Daily Progress</span>
                                <span class="pixel-outline text-sm text-gray-400">
                                    {{ questSummary.completed_today }}/{{ questSummary.total_today }}
                                </span>
                            </div>
                            <div class="pixel-outline-icon h-2.5 w-full rounded-full bg-gray-800">
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
                <div class="bg-container mx-4 mb-8 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b bg-black/60">
                        <nav class="flex space-x-8 px-6">
                            <button
                                @click="activeTab = 'daily'"
                                :class="
                                    activeTab === 'daily'
                                        ? 'pixel-outline border-yellow-500 text-yellow-600'
                                        : 'pixel-outline border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                                "
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Daily Quests ({{ dailyQuests.length }})
                            </button>
                            <button
                                @click="activeTab = 'weekly'"
                                :class="
                                    activeTab === 'weekly'
                                        ? 'pixel-outline border-yellow-500 text-yellow-600'
                                        : 'pixel-outline border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                                "
                                class="border-b-2 px-1 py-4 text-sm font-medium"
                            >
                                Weekly Quests ({{ weeklyQuests.length }})
                            </button>
                            <button
                                @click="activeTab = 'one_time'"
                                :class="
                                    activeTab === 'one_time'
                                        ? 'pixel-outline border-yellow-500 text-yellow-600'
                                        : 'pixel-outline border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
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
