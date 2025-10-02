<template>
    <div class="rounded-lg border-2 border-blue-500 bg-black/50 p-4">
        <h4 class="mb-3 text-md font-medium text-blue-300 pixel-outline">Join Private Game</h4>
        <form @submit.prevent="handleSubmit" class="flex space-x-3">
            <input
                v-model="gameCode"
                type="text"
                placeholder="Enter game code"
                class="flex-1 rounded-md border-gray-300 bg-gray-900 text-white placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500"
                maxlength="8"
                @input="gameCode = $event.target.value.toUpperCase()"
            />
            <button
                type="submit"
                :disabled="!gameCode || processing"
                class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
            >
                <span v-if="processing">Joining...</span>
                <span v-else>Join</span>
            </button>
        </form>
        <div v-if="error" class="mt-2 text-sm text-red-400">
            {{ error }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    processing?: boolean;
    error?: string;
}

defineProps<Props>();

const emit = defineEmits<{
    submit: [gameCode: string];
}>();

const gameCode = ref('');

const handleSubmit = () => {
    if (gameCode.value.trim()) {
        emit('submit', gameCode.value.trim());
    }
};

// Clear the input when error is cleared
defineExpose({
    clear: () => {
        gameCode.value = '';
    }
});
</script>