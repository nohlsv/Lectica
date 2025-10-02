<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Calendar, Flame, Trophy } from 'lucide-vue-next';
import { computed, ref, withDefaults } from 'vue';

interface HeatmapData {
    date: string;
    value: number;
    count: number;
    points: number;
    quizzes_completed: number;
    correct_answers: number;
    time_studied: number;
    battles_participated: number;
    flashcards_reviewed: number;
}

interface StreakStats {
    current_streak: number;
    longest_streak: number;
    total_study_days: number;
    heatmap_data: HeatmapData[];
}

const props = withDefaults(
    defineProps<{
        streakData?: StreakStats;
    }>(),
    {
        streakData: () => ({
            current_streak: 0,
            longest_streak: 0,
            total_study_days: 0,
            heatmap_data: [],
        }),
    },
);

// Tooltip state
const tooltip = ref<{
    show: boolean;
    x: number;
    y: number;
    data: HeatmapData | null;
}>({
    show: false,
    x: 0,
    y: 0,
    data: null,
});

// Calculate weeks for grid layout
const weeksData = computed(() => {
    if (!props.streakData?.heatmap_data?.length) {
        console.log('No heatmap data available');
        return [];
    }

    const weeks: HeatmapData[][] = [];
    let currentWeek: HeatmapData[] = [];

    // Start from the beginning of the data range and organize into weeks
    const data = props.streakData.heatmap_data;
    console.log('Processing heatmap data:', data.length, 'days');

    for (let i = 0; i < data.length; i++) {
        const day = data[i];
        const dayOfWeek = new Date(day.date).getDay(); // 0 = Sunday, 6 = Saturday

        // If this is the first day and it's not Sunday, pad the beginning
        if (i === 0 && dayOfWeek !== 0) {
            for (let j = 0; j < dayOfWeek; j++) {
                currentWeek.push({
                    date: '',
                    value: -1,
                    count: 0,
                    points: 0,
                    quizzes_completed: 0,
                    correct_answers: 0,
                    time_studied: 0,
                    battles_participated: 0,
                    flashcards_reviewed: 0,
                });
            }
        }

        currentWeek.push(day);

        // Complete week on Saturday or end of data
        if (dayOfWeek === 6 || i === data.length - 1) {
            // Pad end of week if needed
            while (currentWeek.length < 7) {
                currentWeek.push({
                    date: '',
                    value: -1,
                    count: 0,
                    points: 0,
                    quizzes_completed: 0,
                    correct_answers: 0,
                    time_studied: 0,
                    battles_participated: 0,
                    flashcards_reviewed: 0,
                });
            }
            weeks.push([...currentWeek]);
            currentWeek = [];
        }
    }

    console.log('Generated weeks:', weeks.length);
    return weeks;
});

// Get color class based on intensity
const getIntensityColor = (value: number): string => {
    if (value === -1) return 'bg-transparent'; // Empty cells
    if (value === 0) return 'bg-gray-800/50 border border-gray-700/50';
    if (value === 1) return 'bg-green-900/60 border border-green-800/50';
    if (value === 2) return 'bg-green-700/70 border border-green-600/50';
    if (value === 3) return 'bg-green-500/80 border border-green-400/50';
    if (value === 4) return 'bg-green-300 border border-green-200/50';
    return 'bg-gray-800/50 border border-gray-700/50';
};

// Generate comprehensive tooltip text
const getTooltipText = (data: HeatmapData): string => {
    if (!data.date || data.value === -1) return '';

    const activities = [];
    if (data.count > 0) activities.push(`${data.count} questions`);
    if (data.correct_answers > 0) activities.push(`${data.correct_answers} correct`);
    if (data.points > 0) activities.push(`${data.points} XP`);
    if (data.quizzes_completed > 0) activities.push(`${data.quizzes_completed} quizzes`);
    if (data.battles_participated > 0) activities.push(`${data.battles_participated} battles`);
    if (data.flashcards_reviewed > 0) activities.push(`${data.flashcards_reviewed} flashcards`);
    if (data.time_studied > 0) activities.push(`${data.time_studied}min study`);

    if (activities.length === 0) {
        return `${data.date}: No activity`;
    }

    return `${data.date}: ${activities.join(', ')}`;
};

// Calculate this week's stats
const thisWeekStats = computed(() => {
    if (!props.streakData?.heatmap_data?.length) {
        return {
            daysActive: 0,
            totalXP: 0,
            totalQuestions: 0,
            correctAnswers: 0,
            quizzesCompleted: 0,
            battlesParticipated: 0,
            flashcardsReviewed: 0,
            studyTime: 0,
            weekProgress: 0,
            daysPassed: 0,
            daysRemaining: 7,
        };
    }

    // Get current week (Sunday to Saturday)
    const today = new Date();
    const currentDayOfWeek = today.getDay(); // 0 = Sunday, 1 = Monday, etc.

    // Calculate the start of this week (Sunday)
    const weekStart = new Date(today);
    weekStart.setDate(today.getDate() - currentDayOfWeek);
    weekStart.setHours(0, 0, 0, 0); // Start of Sunday

    // Calculate the end of this week (Saturday)
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    weekEnd.setHours(23, 59, 59, 999); // End of Saturday

    const thisWeekData = props.streakData.heatmap_data.filter((day) => {
        const dayDate = new Date(day.date + 'T00:00:00'); // Ensure proper date parsing
        return dayDate >= weekStart && dayDate <= weekEnd;
    });

    const stats = thisWeekData.reduce(
        (acc, day) => {
            if (day.value > 0) acc.daysActive++;
            acc.totalXP += day.points || 0;
            acc.totalQuestions += day.count || 0;
            acc.correctAnswers += day.correct_answers || 0;
            acc.quizzesCompleted += day.quizzes_completed || 0;
            acc.battlesParticipated += day.battles_participated || 0;
            acc.flashcardsReviewed += day.flashcards_reviewed || 0;
            acc.studyTime += day.time_studied || 0;
            return acc;
        },
        {
            daysActive: 0,
            totalXP: 0,
            totalQuestions: 0,
            correctAnswers: 0,
            quizzesCompleted: 0,
            battlesParticipated: 0,
            flashcardsReviewed: 0,
            studyTime: 0,
        },
    );

    // Calculate how many days have passed in the current week (including today)
    const daysPassed = currentDayOfWeek + 1; // +1 because Sunday is 0 but we want to count it as 1 day

    return {
        ...stats,
        weekProgress: Math.round((stats.daysActive / 7) * 100),
        daysPassed: daysPassed,
        daysRemaining: 7 - daysPassed,
    };
});

// Generate motivation text based on performance
const motivationText = computed(() => {
    const stats = thisWeekStats.value;
    const streak = props.streakData?.current_streak || 0;

    if (stats.daysActive === 0) {
        return 'Ready to start your learning journey? Every expert was once a beginner! 🌱';
    }

    if (stats.weekProgress >= 85) {
        return `Incredible! ${stats.daysActive}/7 days active this week! You're on fire! 🔥`;
    }

    if (stats.weekProgress >= 70) {
        return `Great progress! ${stats.daysActive} days active this week. Keep pushing! 💪`;
    }

    if (stats.weekProgress >= 40) {
        return `Good start! ${stats.daysActive} days active. Can you reach 5 days this week? 🎯`;
    }

    if (streak >= 7) {
        return `${streak}-day streak! You're building amazing habits! 🏆`;
    }

    if (streak >= 3) {
        return `${streak} days in a row! Consistency is key to mastery! ⭐`;
    }

    return `${stats.daysActive} active days this week. Small steps lead to big wins! 🚀`;
});

// Handle mouse events for tooltip
const showTooltip = (event: MouseEvent, data: HeatmapData) => {
    if (data.value === -1 || !data.date) return;

    tooltip.value = {
        show: true,
        x: event.clientX + 10,
        y: event.clientY - 50,
        data,
    };
};

const hideTooltip = () => {
    tooltip.value.show = false;
};

// Format date for tooltip
const formatDate = (dateStr: string): string => {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Month labels for the heatmap
const monthLabels = computed(() => {
    if (!props.streakData?.heatmap_data?.length) return [];

    const months: { label: string; position: number }[] = [];
    let currentMonth = -1;

    props.streakData.heatmap_data.forEach((day, index) => {
        const date = new Date(day.date);
        const month = date.getMonth();

        if (month !== currentMonth) {
            currentMonth = month;
            const weekIndex = Math.floor(index / 7);
            months.push({
                label: date.toLocaleDateString('en-US', { month: 'short' }),
                position: weekIndex * 14, // 14px per week (12px cell + 2px gap)
            });
        }
    });

    console.log('Month labels:', months);
    return months;
});

const dayLabels = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
</script>

<template>
    <div class="space-y-6">
        <!-- Streak Statistics -->
        <div class="mx-auto grid max-w-4xl grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-3">
            <Card class="bg-container border-gold/30 hover:border-gold/50 border-2 transition-colors">
                <CardContent class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex-shrink-0 rounded-lg bg-orange-500/20 p-1.5 sm:p-2">
                            <Flame class="h-5 w-5 text-orange-400 sm:h-6 sm:w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="pixel-outline truncate text-xs text-white/70 sm:text-sm">Current Streak</p>
                            <p class="pixel-outline text-xl font-bold text-white sm:text-2xl">{{ streakData?.current_streak || 0 }} days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-container border-gold/30 hover:border-gold/50 border-2 transition-colors">
                <CardContent class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex-shrink-0 rounded-lg bg-yellow-500/20 p-1.5 sm:p-2">
                            <Trophy class="text-gold h-5 w-5 sm:h-6 sm:w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="pixel-outline truncate text-xs text-white/70 sm:text-sm">Best Streak</p>
                            <p class="pixel-outline text-xl font-bold text-white sm:text-2xl">{{ streakData?.longest_streak || 0 }} days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-container border-gold/30 hover:border-gold/50 border-2 transition-colors sm:col-span-2 lg:col-span-1">
                <CardContent class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex-shrink-0 rounded-lg bg-blue-500/20 p-1.5 sm:p-2">
                            <Calendar class="h-5 w-5 text-blue-400 sm:h-6 sm:w-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="pixel-outline truncate text-xs text-white/70 sm:text-sm">Total Days</p>
                            <p class="pixel-outline text-xl font-bold text-white sm:text-2xl">{{ streakData?.total_study_days || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Heatmap -->
        <Card class="bg-container border-gold/30 hover:border-gold/50 mx-auto max-w-6xl border-2 transition-colors">
            <CardHeader class="pb-3">
                <CardTitle class="pixel-outline flex items-center gap-2 text-sm text-white sm:text-base">
                    <Calendar class="text-gold h-4 w-4 flex-shrink-0 sm:h-5 sm:w-5" />
                    <span class="truncate">Study Activity - Last 365 Days</span>
                </CardTitle>
            </CardHeader>
            <CardContent class="p-3 sm:p-4 lg:p-6">
                <!-- This Week's Stats & Motivation -->
                <div class="border-gold/20 mb-6 rounded-lg border bg-black/30 p-4">
                    <div class="mb-4 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h3 class="pixel-outline mb-1 text-lg font-bold text-white">This Week's Progress (Sun-Sat)</h3>
                            <p class="pixel-outline text-sm text-white/80">{{ motivationText }}</p>
                            <p class="mt-1 text-xs text-white/60">
                                {{ thisWeekStats.daysPassed }} of 7 days passed • {{ thisWeekStats.daysRemaining }} days remaining
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-right">
                                <div class="text-gold pixel-outline text-2xl font-bold">{{ thisWeekStats.weekProgress }}%</div>
                                <div class="text-xs text-white/70">Complete</div>
                            </div>
                            <div class="relative h-16 w-16">
                                <svg class="h-16 w-16 -rotate-90 transform" viewBox="0 0 36 36">
                                    <path
                                        d="m18,2.0845 a 15.9155,15.9155 0 0,1 0,31.831 a 15.9155,15.9155 0 0,1 0,-31.831"
                                        fill="none"
                                        stroke="rgba(255,255,255,0.2)"
                                        stroke-width="2"
                                    />
                                    <path
                                        d="m18,2.0845 a 15.9155,15.9155 0 0,1 0,31.831 a 15.9155,15.9155 0 0,1 0,-31.831"
                                        fill="none"
                                        stroke="#ffd700"
                                        stroke-width="2"
                                        :stroke-dasharray="`${thisWeekStats.weekProgress}, 100`"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-gold pixel-outline text-xs font-bold">{{ thisWeekStats.daysActive }}/7</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complete Stats Grid -->
                    <div class="grid grid-cols-2 gap-3 text-center sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
                        <!-- Always show these core stats -->
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-yellow-400">{{ thisWeekStats.totalXP }}</div>
                            <div class="text-xs text-white/70">XP Earned</div>
                        </div>
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-blue-400">{{ thisWeekStats.totalQuestions }}</div>
                            <div class="text-xs text-white/70">Questions</div>
                        </div>
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-emerald-400">{{ thisWeekStats.correctAnswers }}</div>
                            <div class="text-xs text-white/70">Correct</div>
                        </div>
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-green-400">{{ thisWeekStats.studyTime }}m</div>
                            <div class="text-xs text-white/70">Study Time</div>
                        </div>
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-purple-400">{{ thisWeekStats.quizzesCompleted }}</div>
                            <div class="text-xs text-white/70">📝 Quizzes</div>
                        </div>
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-red-400">{{ thisWeekStats.battlesParticipated }}</div>
                            <div class="text-xs text-white/70">⚔️ Battles</div>
                        </div>
                        <div class="rounded border border-white/10 bg-black/40 p-2">
                            <div class="pixel-outline text-lg font-bold text-indigo-400">{{ thisWeekStats.flashcardsReviewed }}</div>
                            <div class="text-xs text-white/70">🃏 Flashcards</div>
                        </div>
                    </div>
                </div>

                <div class="heatmap-scroll overflow-x-auto">
                    <div class="relative mx-auto min-w-[280px] sm:min-w-[600px] lg:min-w-[800px]">
                        <!-- Heatmap grid with weeks -->
                        <div class="flex justify-center gap-0.5 sm:gap-1">
                            <!-- Day labels -->
                            <div class="mr-1 flex flex-shrink-0 flex-col gap-0.5 text-[10px] text-white/70 sm:mr-2 sm:gap-1 sm:text-xs">
                                <div v-for="(day, index) in dayLabels" :key="day" class="flex h-3 items-center justify-center sm:h-4">
                                    {{ day }}
                                </div>
                            </div>

                            <!-- Weeks container -->
                            <div v-if="weeksData.length > 0" class="flex gap-0.5 sm:gap-1">
                                <div v-for="(week, weekIndex) in weeksData" :key="weekIndex" class="flex flex-col gap-0.5 sm:gap-1">
                                    <div
                                        v-for="(day, dayIndex) in week"
                                        :key="`${weekIndex}-${dayIndex}`"
                                        class="relative h-3 w-3 cursor-pointer rounded-sm transition-all duration-200 hover:z-10 hover:scale-110 sm:h-4 sm:w-4"
                                        :class="getIntensityColor(day.value)"
                                        @mouseenter="day.date ? showTooltip($event, day) : null"
                                        @mouseleave="hideTooltip"
                                        :title="getTooltipText(day)"
                                    ></div>
                                </div>
                            </div>

                            <!-- Fallback if no weeks data -->
                            <div v-else class="p-4 text-center text-xs text-white/50 sm:text-sm">No activity data available</div>
                        </div>

                        <!-- Legend -->
                        <div class="mx-auto mt-3 flex max-w-md items-center justify-between text-[10px] text-white/70 sm:mt-4 sm:text-xs">
                            <span class="pixel-outline">Less</span>
                            <div class="flex items-center gap-0.5 sm:gap-1">
                                <div class="h-2 w-2 rounded-sm border border-gray-700/50 bg-gray-800/50 sm:h-3 sm:w-3"></div>
                                <div class="h-2 w-2 rounded-sm border border-green-800/50 bg-green-900/60 sm:h-3 sm:w-3"></div>
                                <div class="h-2 w-2 rounded-sm border border-green-600/50 bg-green-700/70 sm:h-3 sm:w-3"></div>
                                <div class="h-2 w-2 rounded-sm border border-green-400/50 bg-green-500/80 sm:h-3 sm:w-3"></div>
                                <div class="h-2 w-2 rounded-sm border border-green-200/50 bg-green-300 sm:h-3 sm:w-3"></div>
                            </div>
                            <span class="pixel-outline">More</span>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- Tooltip -->
    <Teleport to="body">
        <div
            v-if="tooltip.show && tooltip.data"
            class="border-gold/30 pixel-outline pointer-events-none fixed z-50 rounded-lg border bg-black/90 p-3 text-sm text-white"
            :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
        >
            <div class="font-semibold">{{ formatDate(tooltip.data.date) }}</div>
            <div class="mt-1 space-y-0.5 text-white/80">
                <!-- Performance Stats -->
                <div v-if="tooltip.data.count > 0 || tooltip.data.correct_answers > 0" class="space-y-0.5">
                    <div v-if="tooltip.data.count > 0">Questions Answered: {{ tooltip.data.count }}</div>
                    <div v-if="tooltip.data.correct_answers > 0">Correct Answers: {{ tooltip.data.correct_answers }}</div>
                </div>

                <!-- XP and Rewards -->
                <div v-if="tooltip.data.points > 0" class="font-medium text-yellow-400">XP Earned: {{ tooltip.data.points }}</div>

                <!-- Activities -->
                <!-- <div v-if="tooltip.data.quizzes_completed > 0 || tooltip.data.battles_participated > 0 || tooltip.data.flashcards_reviewed > 0" class="space-y-0.5 pt-1 border-t border-white/20"> -->
                <div v-if="tooltip.data.quizzes_completed > 0">📝 Quizzes Completed: {{ tooltip.data.quizzes_completed }}</div>
                <div v-if="tooltip.data.battles_participated > 0">⚔️ Battles Participated: {{ tooltip.data.battles_participated }}</div>
                <div v-if="tooltip.data.flashcards_reviewed > 0">🃏 Flashcards Reviewed: {{ tooltip.data.flashcards_reviewed }}</div>
                <!-- </div> -->

                <!-- Study Time -->
                <div v-if="tooltip.data.time_studied > 0" class="border-t border-white/20 pt-1 text-blue-400">
                    ⏱️ Study Time: {{ tooltip.data.time_studied }} min
                </div>

                <!-- No Activity Message -->
                <div v-if="tooltip.data.value === 0 && tooltip.data.count === 0 && tooltip.data.points === 0" class="text-white/60">
                    No activity this day
                </div>
                <div v-if="tooltip.data.value > 0" class="mt-2 border-t border-white/20 pt-2">
                    <div
                        class="pixel-outline inline-block rounded px-2 py-1 text-xs font-bold text-white"
                        :class="{
                            'border border-green-500/50 bg-green-600/80': tooltip.data.value === 4,
                            'border border-green-600/50 bg-green-700/80': tooltip.data.value === 3,
                            'border border-blue-500/50 bg-blue-600/80': tooltip.data.value === 2,
                            'border border-gray-500/50 bg-gray-600/80': tooltip.data.value === 1,
                        }"
                    >
                        {{
                            tooltip.data.value === 4
                                ? 'Very Active'
                                : tooltip.data.value === 3
                                  ? 'Active'
                                  : tooltip.data.value === 2
                                    ? 'Moderate'
                                    : 'Light'
                        }}
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* Custom scrollbar for the heatmap */
.heatmap-scroll::-webkit-scrollbar {
    height: 4px;
}

.heatmap-scroll::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 2px;
}

.heatmap-scroll::-webkit-scrollbar-thumb {
    background: rgba(212, 175, 55, 0.6);
    border-radius: 2px;
    transition: background-color 0.2s;
}

.heatmap-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 175, 55, 0.8);
}

/* Enhanced hover effects */
.heatmap-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(212, 175, 55, 0.6) rgba(0, 0, 0, 0.2);
}

/* Smooth scrolling on mobile */
@media (max-width: 640px) {
    .heatmap-scroll {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }

    .heatmap-scroll::-webkit-scrollbar {
        height: 3px;
    }
}

/* Focus styles for accessibility */
.heatmap-scroll:focus-within {
    outline: 2px solid rgba(212, 175, 55, 0.5);
    outline-offset: 2px;
    border-radius: 4px;
}
</style>
