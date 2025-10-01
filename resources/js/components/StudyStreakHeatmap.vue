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
                currentWeek.push({ date: '', value: -1, count: 0, points: 0 });
            }
        }
        
        currentWeek.push(day);
        
        // Complete week on Saturday or end of data
        if (dayOfWeek === 6 || i === data.length - 1) {
            // Pad end of week if needed
            while (currentWeek.length < 7) {
                currentWeek.push({ date: '', value: -1, count: 0, points: 0 });
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card class="bg-container border-2 border-gold/30">
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-orange-500/20 rounded-lg">
                            <Flame class="h-6 w-6 text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-white/70 pixel-outline">Current Streak</p>
                            <p class="text-2xl font-bold text-white pixel-outline">{{ streakData?.current_streak || 0 }} days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-container border-2 border-gold/30">
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-500/20 rounded-lg">
                            <Trophy class="h-6 w-6 text-gold" />
                        </div>
                        <div>
                            <p class="text-sm text-white/70 pixel-outline">Best Streak</p>
                            <p class="text-2xl font-bold text-white pixel-outline">{{ streakData?.longest_streak || 0 }} days</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-container border-2 border-gold/30">
                <CardContent class="p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-500/20 rounded-lg">
                            <Calendar class="h-6 w-6 text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-white/70 pixel-outline">Total Days</p>
                            <p class="text-2xl font-bold text-white pixel-outline">{{ streakData?.total_study_days || 0 }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Heatmap -->
        <Card class="bg-container border-2 border-gold/30">
            <CardHeader>
                <CardTitle class="text-white pixel-outline flex items-center gap-2">
                    <Calendar class="h-5 w-5 text-gold" />
                    Study Activity - Last 365 Days
                </CardTitle>
            </CardHeader>
            <CardContent class="p-6">
                        <div class="overflow-x-auto">
                    <div class="relative min-w-[800px]">
                        <!-- Heatmap grid with weeks -->
                        <div class="flex gap-1">
                            <!-- Day labels -->
                            <div class="flex flex-col gap-1 mr-2 text-xs text-white/70">
                                <div v-for="(day, index) in dayLabels" :key="day" class="h-3 flex items-center">
                                    {{ index % 2 === 1 ? day : '' }}
                                </div>
                            </div>

                            <!-- Weeks container -->
                            <div v-if="weeksData.length > 0" class="flex gap-1">
                                <div 
                                    v-for="(week, weekIndex) in weeksData" 
                                    :key="weekIndex"
                                    class="flex flex-col gap-1"
                                >
                                    <div
                                        v-for="(day, dayIndex) in week"
                                        :key="`${weekIndex}-${dayIndex}`"
                                        class="w-3 h-3 rounded-sm cursor-pointer transition-all hover:scale-110"
                                        :class="getIntensityColor(day.value)"
                                        @mouseenter="day.date ? showTooltip($event, day) : null"
                                        @mouseleave="hideTooltip"
                                        :title="day.date ? `${day.date}: ${day.count} questions, ${day.points} points` : ''"
                                    ></div>
                                </div>
                            </div>
                            
                            <!-- Fallback if no weeks data -->
                            <div v-else class="text-white/50 text-sm p-4">
                                No activity data available
                            </div>
                        </div>                        <!-- Legend -->
                        <div class="flex items-center justify-between mt-4 text-xs text-white/70">
                            <span class="pixel-outline">Less</span>
                            <div class="flex gap-1 items-center">
                                <div class="h-3 w-3 rounded-sm bg-gray-800/50 border border-gray-700/50"></div>
                                <div class="h-3 w-3 rounded-sm bg-green-900/60 border border-green-800/50"></div>
                                <div class="h-3 w-3 rounded-sm bg-green-700/70 border border-green-600/50"></div>
                                <div class="h-3 w-3 rounded-sm bg-green-500/80 border border-green-400/50"></div>
                                <div class="h-3 w-3 rounded-sm bg-green-300 border border-green-200/50"></div>
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
            <div class="text-white/80 mt-1">
                <div>Questions: {{ tooltip.data.count }}</div>
                <div>Points: {{ tooltip.data.points }}</div>
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
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: rgba(212, 175, 55, 0.5);
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(212, 175, 55, 0.7);
}
</style>