<script setup lang="ts">
import { computed, ref, onMounted, withDefaults } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Flame, Trophy, Calendar } from 'lucide-vue-next';

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

const props = withDefaults(defineProps<{
    streakData?: StreakStats;
}>(), {
    streakData: () => ({
        current_streak: 0,
        longest_streak: 0,
        total_study_days: 0,
        heatmap_data: [],
    }),
});

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
                    flashcards_reviewed: 0
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
                    flashcards_reviewed: 0
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 max-w-4xl mx-auto">
            <Card class="bg-container border-2 border-gold/30 hover:border-gold/50 transition-colors">
                <CardContent class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="p-1.5 sm:p-2 bg-orange-500/20 rounded-lg flex-shrink-0">
                            <Flame class="h-5 w-5 sm:h-6 sm:w-6 text-orange-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm text-white/70 pixel-outline truncate">Current Streak</p>
                            <p class="text-xl sm:text-2xl font-bold text-white pixel-outline">{{ streakData?.current_streak || 0 }} days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-container border-2 border-gold/30 hover:border-gold/50 transition-colors">
                <CardContent class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="p-1.5 sm:p-2 bg-yellow-500/20 rounded-lg flex-shrink-0">
                            <Trophy class="h-5 w-5 sm:h-6 sm:w-6 text-gold" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm text-white/70 pixel-outline truncate">Best Streak</p>
                            <p class="text-xl sm:text-2xl font-bold text-white pixel-outline">{{ streakData?.longest_streak || 0 }} days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-container border-2 border-gold/30 hover:border-gold/50 transition-colors sm:col-span-2 lg:col-span-1">
                <CardContent class="p-3 sm:p-4">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="p-1.5 sm:p-2 bg-blue-500/20 rounded-lg flex-shrink-0">
                            <Calendar class="h-5 w-5 sm:h-6 sm:w-6 text-blue-400" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm text-white/70 pixel-outline truncate">Total Days</p>
                            <p class="text-xl sm:text-2xl font-bold text-white pixel-outline">{{ streakData?.total_study_days || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Heatmap -->
        <Card class="bg-container border-2 border-gold/30 hover:border-gold/50 transition-colors max-w-6xl mx-auto">
            <CardHeader class="pb-3">
                <CardTitle class="text-white pixel-outline flex items-center gap-2 text-sm sm:text-base">
                    <Calendar class="h-4 w-4 sm:h-5 sm:w-5 text-gold flex-shrink-0" />
                    <span class="truncate">Study Activity - Last 365 Days</span>
                </CardTitle>
            </CardHeader>
            <CardContent class="p-3 sm:p-4 lg:p-6">
                <div class="overflow-x-auto heatmap-scroll">
                    <div class="relative min-w-[280px] sm:min-w-[600px] lg:min-w-[800px] mx-auto">
                        <!-- Heatmap grid with weeks -->
                        <div class="flex gap-0.5 sm:gap-1 justify-center">
                            <!-- Day labels -->
                            <div class="flex flex-col gap-0.5 sm:gap-1 mr-1 sm:mr-2 text-[10px] sm:text-xs text-white/70 flex-shrink-0">
                                <div v-for="(day, index) in dayLabels" :key="day" class="h-3 sm:h-4 flex items-center justify-center">
                                    {{ day }}
                                </div>
                            </div>

                            <!-- Weeks container -->
                            <div v-if="weeksData.length > 0" class="flex gap-0.5 sm:gap-1">
                                <div 
                                    v-for="(week, weekIndex) in weeksData" 
                                    :key="weekIndex"
                                    class="flex flex-col gap-0.5 sm:gap-1"
                                >
                                    <div
                                        v-for="(day, dayIndex) in week"
                                        :key="`${weekIndex}-${dayIndex}`"
                                        class="w-3 h-3 sm:w-4 sm:h-4 rounded-sm cursor-pointer transition-all duration-200 hover:scale-110 hover:z-10 relative"
                                        :class="getIntensityColor(day.value)"
                                        @mouseenter="day.date ? showTooltip($event, day) : null"
                                        @mouseleave="hideTooltip"
                                        :title="getTooltipText(day)"
                                    ></div>
                                </div>
                            </div>
                            
                            <!-- Fallback if no weeks data -->
                            <div v-else class="text-white/50 text-xs sm:text-sm p-4 text-center">
                                No activity data available
                            </div>
                        </div>
                        
                        <!-- Legend -->
                        <div class="flex items-center justify-between mt-3 sm:mt-4 text-[10px] sm:text-xs text-white/70 max-w-md mx-auto">
                            <span class="pixel-outline">Less</span>
                            <div class="flex gap-0.5 sm:gap-1 items-center">
                                <div class="h-2 w-2 sm:h-3 sm:w-3 rounded-sm bg-gray-800/50 border border-gray-700/50"></div>
                                <div class="h-2 w-2 sm:h-3 sm:w-3 rounded-sm bg-green-900/60 border border-green-800/50"></div>
                                <div class="h-2 w-2 sm:h-3 sm:w-3 rounded-sm bg-green-700/70 border border-green-600/50"></div>
                                <div class="h-2 w-2 sm:h-3 sm:w-3 rounded-sm bg-green-500/80 border border-green-400/50"></div>
                                <div class="h-2 w-2 sm:h-3 sm:w-3 rounded-sm bg-green-300 border border-green-200/50"></div>
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
            class="fixed z-50 bg-black/90 text-white text-sm p-3 rounded-lg border border-gold/30 pixel-outline pointer-events-none"
            :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
        >
            <div class="font-semibold">{{ formatDate(tooltip.data.date) }}</div>
            <div class="text-white/80 mt-1 space-y-0.5">
                <!-- Performance Stats -->
                <div v-if="tooltip.data.count > 0 || tooltip.data.correct_answers > 0" class="space-y-0.5">
                    <div v-if="tooltip.data.count > 0">Questions Answered: {{ tooltip.data.count }}</div>
                    <div v-if="tooltip.data.correct_answers > 0">Correct Answers: {{ tooltip.data.correct_answers }}</div>
                </div>
                
                <!-- XP and Rewards -->
                <div v-if="tooltip.data.points > 0" class="text-yellow-400 font-medium">
                    XP Earned: {{ tooltip.data.points }}
                </div>
                
                <!-- Activities -->
                <div v-if="tooltip.data.quizzes_completed > 0 || tooltip.data.battles_participated > 0 || tooltip.data.flashcards_reviewed > 0" class="space-y-0.5 pt-1 border-t border-white/20">
                    <div v-if="tooltip.data.quizzes_completed > 0">📝 Quizzes Completed: {{ tooltip.data.quizzes_completed }}</div>
                    <div v-if="tooltip.data.battles_participated > 0">⚔️ Battles Participated: {{ tooltip.data.battles_participated }}</div>
                    <div v-if="tooltip.data.flashcards_reviewed > 0">🃏 Flashcards Reviewed: {{ tooltip.data.flashcards_reviewed }}</div>
                </div>
                
                <!-- Study Time -->
                <div v-if="tooltip.data.time_studied > 0" class="text-blue-400 pt-1 border-t border-white/20">
                    ⏱️ Study Time: {{ tooltip.data.time_studied }} min
                </div>
                
                <!-- No Activity Message -->
                <div v-if="tooltip.data.value === 0 && tooltip.data.count === 0 && tooltip.data.points === 0" class="text-white/60">
                    No activity this day
                </div>
                <div v-if="tooltip.data.value > 0" class="mt-1">
                    <Badge 
                        :variant="tooltip.data.value >= 3 ? 'default' : 'secondary'"
                        class="text-xs"
                    >
                        {{ tooltip.data.value === 4 ? 'Very Active' : 
                           tooltip.data.value === 3 ? 'Active' : 
                           tooltip.data.value === 2 ? 'Moderate' : 'Light' }}
                    </Badge>
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