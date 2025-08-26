<template>
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600 transition-all hover:shadow-md">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4">
                <!-- Quest Icon -->
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl"
                         :class="getQuestTypeColor()">
                        {{ getQuestIcon() }}
                    </div>
                </div>

                <!-- Quest Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2 mb-1">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ quest.title }}
                        </h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="getCategoryBadgeColor()">
                            {{ getCategoryText() }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        {{ quest.description }}
                    </p>

                    <!-- Progress Bar (only if quest is assigned to user) -->
                    <div v-if="quest.user_progress" class="mb-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ quest.user_progress.progress }}/{{ quest.user_progress.target }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-600">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="quest.user_progress.is_completed ? 'bg-green-600' : 'bg-blue-600'"
                                :style="`width: ${getProgressPercentage()}%`"
                            ></div>
                        </div>
                    </div>

                    <!-- Quest Requirements -->
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        <span v-if="quest.requirements?.count">
                            Required: {{ quest.requirements.count }} {{ quest.requirements.unit || 'times' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quest Status & Reward -->
            <div class="flex flex-col items-end space-y-2">
                <!-- Status Badge -->
                <div v-if="quest.user_progress">
                    <span v-if="quest.user_progress.is_completed"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Completed
                    </span>
                    <span v-else-if="quest.user_progress.progress > 0"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        In Progress
                    </span>
                    <span v-else
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832L14 10.202a1 1 0 000-1.652l-4.445-2.38z" clip-rule="evenodd"></path>
                        </svg>
                        Available
                    </span>
                </div>

                <!-- XP Reward -->
                <div class="flex items-center space-x-1 text-sm">
                    <span class="text-yellow-500">⭐</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ quest.experience_reward }} XP</span>
                </div>

                <!-- Quest Type Badge -->
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                      :class="getTypeBadgeColor()">
                    {{ getTypeText() }}
                </span>
            </div>
        </div>

        <!-- Completion Date (if completed) -->
        <div v-if="quest.user_progress?.is_completed && quest.user_progress?.completed_at"
             class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Completed on {{ formatDate(quest.user_progress.completed_at) }}</span>
                <span>+{{ quest.experience_reward }} XP earned</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps({
    quest: Object,
    type: {
        type: String,
        default: 'daily'
    }
})

const getProgressPercentage = () => {
    if (!props.quest.user_progress) return 0
    const progress = props.quest.user_progress.progress
    const target = props.quest.user_progress.target
    return Math.min(100, Math.round((progress / target) * 100))
}

const getQuestIcon = () => {
    const icons = {
        'battle': '⚔️',
        'file_upload': '📁',
        'quiz': '❓',
        'study': '📚'
    }
    return icons[props.quest.category] || '🎯'
}

const getQuestTypeColor = () => {
    const colors = {
        'daily': 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300',
        'weekly': 'bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-300',
        'achievement': 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-300'
    }
    return colors[props.type] || colors['daily']
}

const getCategoryBadgeColor = () => {
    const colors = {
        'battle': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'file_upload': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        'quiz': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'study': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
    }
    return colors[props.quest.category] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
}

const getTypeBadgeColor = () => {
    const colors = {
        'daily': 'bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
        'weekly': 'bg-purple-50 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
        'achievement': 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-300'
    }
    return colors[props.type] || colors['daily']
}

const getCategoryText = () => {
    const categories = {
        'battle': 'Battle',
        'file_upload': 'Upload',
        'quiz': 'Quiz',
        'study': 'Study'
    }
    return categories[props.quest.category] || 'Quest'
}

const getTypeText = () => {
    const types = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'achievement': 'Achievement'
    }
    return types[props.type] || 'Quest'
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString()
}
</script>
