<template>
    <div class="fixed bottom-4 right-4 z-50 flex items-center space-x-2 rounded-lg border-2 border-blue-700 bg-blue-500 px-3 py-2 shadow-lg backdrop-blur-sm bg-opacity-95">
        <!-- Music note icon with pulse animation when playing -->
        <div class="text-white text-lg" :class="{ 'animate-pulse': isGlobalMusicEnabled && globalMusicVolume > 0 }">🎵</div>
        
        <!-- Track info (hidden by default, can be shown on hover) -->
        <div class="hidden lg:block text-xs text-white opacity-75">Lectica OST</div>
        
        <!-- Music toggle button with fixed width -->
        <button 
            @click="toggleGlobalMusic" 
            class="text-white hover:scale-110 transition-transform flex items-center justify-center"
            :title="isGlobalMusicEnabled ? 'Mute background music' : 'Enable background music'"
            style="width: 24px; height: 24px;"
        >
            <span class="text-center" style="width: 16px; display: inline-block;">
                {{ getVolumeIcon() }}
            </span>
        </button>
        
        <!-- Volume slider -->
        <input
            type="range"
            min="0"
            max="1"
            step="0.1"
            :value="globalMusicVolume"
            @input="(e) => setGlobalMusicVolume(parseFloat((e.target as HTMLInputElement).value))"
            @change="(e) => setGlobalMusicVolume(parseFloat((e.target as HTMLInputElement).value))"
            class="global-music-slider h-2 bg-gray-300 rounded-lg appearance-none cursor-pointer"
            :disabled="!isGlobalMusicEnabled"
            title="Background music volume"
        />
        
        <!-- Volume percentage -->
        <span class="text-xs text-white min-w-[3ch]">{{ Math.round(globalMusicVolume * 100) }}%</span>
    </div>
</template>

<script setup lang="ts">
import { useGlobalMusic } from '@/composables/useGlobalMusic';
import { watch } from 'vue';

const { 
    globalMusicVolume, 
    isGlobalMusicEnabled, 
    toggleGlobalMusic,
    shouldDisableMusic,
    setGlobalMusicVolume
} = useGlobalMusic();

// Debug volume changes
watch(globalMusicVolume, (newVolume, oldVolume) => {
    console.log('Global Music Control: Volume changed from', oldVolume, 'to', newVolume);
});

// Get volume icon with consistent width
const getVolumeIcon = () => {
    if (!isGlobalMusicEnabled.value || globalMusicVolume.value === 0) {
        return '🔇'; // Muted
    } else if (globalMusicVolume.value < 0.33) {
        return '🔈'; // Low volume
    } else if (globalMusicVolume.value < 0.66) {
        return '🔉'; // Medium volume
    } else {
        return '🔊'; // High volume
    }
};
</script>

<style scoped>
/* Global music slider styling */
.global-music-slider {
    -webkit-appearance: none;
    appearance: none;
    background: #374151;
    border: 2px solid #1f2937;
    border-radius: 4px;
    height: 8px;
    width: 80px;
}

.global-music-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    background: #10b981;
    border: 2px solid #065f46;
    border-radius: 2px;
    cursor: pointer;
    box-shadow: 2px 2px 0px rgba(0, 0, 0, 0.4);
}

.global-music-slider::-webkit-slider-thumb:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 3px 3px 0px rgba(0, 0, 0, 0.4);
}

.global-music-slider::-moz-range-thumb {
    width: 16px;
    height: 16px;
    background: #10b981;
    border: 2px solid #065f46;
    border-radius: 2px;
    cursor: pointer;
    box-shadow: 2px 2px 0px rgba(0, 0, 0, 0.4);
}

.global-music-slider::-moz-range-thumb:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 3px 3px 0px rgba(0, 0, 0, 0.4);
}

.global-music-slider:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>