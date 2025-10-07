import { ref, watch } from 'vue';

// Create a more persistent global state that survives page changes
const createGlobalState = () => {
    // Check if we already have global state in window object
    if (typeof window !== 'undefined' && (window as any).__lectiaGlobalMusic) {
        console.log('Global Music: Using existing global state');
        return (window as any).__lectiaGlobalMusic;
    }
    
    const state = {
        globalMusicVolume: ref(0.3),
        isGlobalMusicEnabled: ref(true),
        currentPage: ref(''),
        globalMusicAudio: ref<HTMLAudioElement | null>(null),
        isMusicInitialized: ref(false),
        savedMusicPosition: ref(0)
    };
    
    if (typeof window !== 'undefined') {
        (window as any).__lectiaGlobalMusic = state;
        console.log('Global Music: Created new global state');
    }
    
    return state;
};

const globalState = createGlobalState();
const globalMusicVolume = globalState.globalMusicVolume;
const isGlobalMusicEnabled = globalState.isGlobalMusicEnabled;
const currentPage = globalState.currentPage;
const globalMusicAudio = globalState.globalMusicAudio;
const isMusicInitialized = globalState.isMusicInitialized;
const savedMusicPosition = globalState.savedMusicPosition;

// Pages where global music should be disabled (active game pages have their own music)
const MUSIC_DISABLED_PAGES = [
    'MultiplayerGames/GameQuiz',
    'Battles/BattleQuiz'
];

// Set up watchers only once
if (typeof window !== 'undefined' && !(window as any).__lectiaWatchersSetup) {
    console.log('Global Music: Setting up watchers for the first time');
    
    // Watch for volume changes
    watch(globalMusicVolume, (newVolume, oldVolume) => {
        console.log('Global Music: Volume changed from', oldVolume, 'to', newVolume);
        if (globalMusicAudio.value) {
            globalMusicAudio.value.volume = newVolume;
            console.log('Global Music: Audio volume set to', globalMusicAudio.value.volume);
        }
        saveGlobalMusicSettings();
    });

    // Watch for enabled/disabled changes
    watch(isGlobalMusicEnabled, (enabled) => {
        console.log('Global Music: Enabled state changed to', enabled);
        if (enabled && !shouldDisableMusic()) {
            startGlobalMusic();
        } else {
            stopGlobalMusic();
        }
        saveGlobalMusicSettings();
    });
    
    (window as any).__lectiaWatchersSetup = true;
}

// Load settings from localStorage
const loadGlobalMusicSettings = () => {
    const savedVolume = localStorage.getItem('lectica-global-music-volume');
    if (savedVolume !== null) {
        const volume = parseFloat(savedVolume);
        globalMusicVolume.value = isNaN(volume) ? 0.3 : Math.max(0, Math.min(1, volume));
    }

    const savedEnabled = localStorage.getItem('lectica-global-music-enabled');
    if (savedEnabled !== null) {
        isGlobalMusicEnabled.value = savedEnabled === 'true';
    }
};

// Save settings to localStorage
const saveGlobalMusicSettings = () => {
    localStorage.setItem('lectica-global-music-volume', globalMusicVolume.value.toString());
    localStorage.setItem('lectica-global-music-enabled', isGlobalMusicEnabled.value.toString());
};

// Initialize global music
const initializeGlobalMusic = () => {
    if (isMusicInitialized.value) {
        console.log('Global Music: Already initialized, skipping...');
        return;
    }

    console.log('Global Music: Initializing for the first time...');
    loadGlobalMusicSettings();
    
    // Create the audio element
    try {
        globalMusicAudio.value = new Audio('/sfx/lectica_ost_own.mp3');
        globalMusicAudio.value.loop = true;
        globalMusicAudio.value.volume = globalMusicVolume.value;
        globalMusicAudio.value.preload = 'auto';
        
        // Try to enable autoplay (some browsers support this)
        globalMusicAudio.value.autoplay = true;
        
        // Set additional attributes that might help with autoplay
        try {
            globalMusicAudio.value.setAttribute('autoplay', '');
            globalMusicAudio.value.setAttribute('preload', 'auto');
        } catch (attrError) {
            console.log('Global Music: Could not set autoplay attributes:', attrError);
        }
        
        // Add error handling
        globalMusicAudio.value.addEventListener('error', (e: Event) => {
            console.error('Global Music: Failed to load audio file:', e);
        });
        
        globalMusicAudio.value.addEventListener('canplaythrough', () => {
            console.log('Global Music: Audio file loaded successfully');
            // Try to start playing as soon as it's ready
            if (isGlobalMusicEnabled.value && !shouldDisableMusic()) {
                startGlobalMusic();
            }
        });
        
        globalMusicAudio.value.addEventListener('loadeddata', () => {
            console.log('Global Music: Audio data loaded');
            // Another opportunity to try starting
            if (isGlobalMusicEnabled.value && !shouldDisableMusic()) {
                startGlobalMusic();
            }
        });
        
        console.log('Global Music: Audio element created successfully');
    } catch (error) {
        console.error('Global Music: Failed to create audio element:', error);
        return;
    }
    
    isMusicInitialized.value = true;
    
    // Start music if enabled and not on a disabled page
    if (isGlobalMusicEnabled.value && !shouldDisableMusic()) {
        // Try to start immediately
        startGlobalMusic();
        
        // Also try again after page loads as backup
        setTimeout(() => {
            startGlobalMusic();
        }, 500);
        
        // And once more after full page load
        setTimeout(() => {
            startGlobalMusic();
        }, 1500);
    }
};

// Start global music with multiple attempt strategies
const startGlobalMusic = () => {
    if (!globalMusicAudio.value || !isGlobalMusicEnabled.value) {
        console.log('Global Music: Cannot start - audio not initialized or music disabled');
        return;
    }
    
    // Check if current page should have music disabled
    if (shouldDisableMusic()) {
        console.log('Global Music: Not starting on disabled page');
        stopGlobalMusic();
        return;
    }

    // Don't restart if already playing
    if (!globalMusicAudio.value.paused) {
        console.log('Global Music: Already playing');
        return;
    }

    console.log('Global Music: Attempting to start...');
    
    // Resume from saved position for smooth transitions
    if (savedMusicPosition.value > 0) {
        globalMusicAudio.value.currentTime = savedMusicPosition.value;
    }

    // Try multiple strategies to start music
    const attemptPlay = () => {
        return globalMusicAudio.value!.play()
            .then(() => {
                console.log('Global Music: Started successfully');
                return true;
            })
            .catch((error) => {
                console.log('Global Music: Play attempt failed:', error);
                return false;
            });
    };

    // First attempt - immediate play
    attemptPlay().then((success) => {
        if (!success) {
            // Second attempt - try again after a short delay
            setTimeout(() => {
                attemptPlay().then((secondSuccess) => {
                    if (!secondSuccess) {
                        // Third attempt - set up user interaction listeners as fallback
                        console.log('Global Music: Setting up user interaction fallback');
                        const startOnInteraction = () => {
                            if (globalMusicAudio.value && isGlobalMusicEnabled.value && !shouldDisableMusic()) {
                                globalMusicAudio.value.play()
                                    .then(() => {
                                        console.log('Global Music: Started after user interaction');
                                    })
                                    .catch((err) => console.log('Global Music: Failed to start after user interaction:', err));
                            }
                        };
                        
                        // Use more comprehensive event listeners
                        ['click', 'keydown', 'touchstart', 'mousemove', 'scroll'].forEach(event => {
                            document.addEventListener(event, startOnInteraction, { once: true, passive: true });
                        });
                    }
                });
            }, 1000);
        }
    });
};

// Stop global music
const stopGlobalMusic = () => {
    if (globalMusicAudio.value && !globalMusicAudio.value.paused) {
        console.log('Global Music: Stopping...');
        // Save current position for smooth resumption
        savedMusicPosition.value = globalMusicAudio.value.currentTime;
        globalMusicAudio.value.pause();
    }
};

// Check if music should be disabled on current page
const shouldDisableMusic = () => {
    return MUSIC_DISABLED_PAGES.some(disabledPage => 
        currentPage.value.includes(disabledPage)
    );
};

// Update current page and handle music accordingly
const updateCurrentPage = (pageName: string) => {
    const previousPage = currentPage.value;
    currentPage.value = pageName;
    
    console.log('Global Music: Page changed from', previousPage, 'to', pageName);
    
    if (shouldDisableMusic()) {
        console.log('Global Music: Stopping for game page:', pageName);
        stopGlobalMusic();
    } else if (isGlobalMusicEnabled.value) {
        console.log('Global Music: Starting for page:', pageName);
        // Small delay to ensure smooth transition
        setTimeout(() => {
            startGlobalMusic();
        }, 500);
    }
};

// Toggle global music on/off
const toggleGlobalMusic = () => {
    isGlobalMusicEnabled.value = !isGlobalMusicEnabled.value;
};

// Set global music volume (0 to 1)
const setGlobalMusicVolume = (volume: number) => {
    const newVolume = Math.max(0, Math.min(1, volume));
    console.log('Global Music: Setting volume to', newVolume);
    globalMusicVolume.value = newVolume;
};

// Auto-initialize when composable is first loaded (only once)
if (typeof window !== 'undefined' && !(window as any).__lectiaGlobalMusicAutoInit) {
    console.log('Global Music: Setting up auto-initialization');
    (window as any).__lectiaGlobalMusicAutoInit = true;
    
    // Try to initialize immediately
    initializeGlobalMusic();
    
    // Also try after a tiny delay as backup
    setTimeout(() => {
        initializeGlobalMusic();
    }, 10);
}

// Composable hook
export function useGlobalMusic() {
    return {
        globalMusicVolume,
        isGlobalMusicEnabled,
        initializeGlobalMusic,
        startGlobalMusic,
        stopGlobalMusic,
        updateCurrentPage,
        toggleGlobalMusic,
        setGlobalMusicVolume,
        shouldDisableMusic
    };
}