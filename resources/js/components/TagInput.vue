<script setup lang="ts">
import { type Tag } from '@/types';
import axios from 'axios';
import { ClockIcon, LinkIcon, TrendingUpIcon, UsersIcon, XIcon } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

interface Props {
    modelValue: Tag[];
    existingTags?: Tag[];
    addNewTags?: boolean;
    showSuggestions?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    existingTags: () => [],
    addNewTags: false,
    showSuggestions: true,
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref('');
const showDropdown = ref(false);
const suggestedTags = ref<any[]>([]);
const relatedTags = ref<any[]>([]);
const isLoading = ref(false);
const currentSection = ref('suggestions'); // 'suggestions' or 'search'

const selectedTags = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const filteredSuggestions = computed(() => {
    return suggestedTags.value.filter((tag) => !selectedTags.value.some((selectedTag) => selectedTag.id === tag.id));
});

const filteredRelatedTags = computed(() => {
    return relatedTags.value.filter((tag) => !selectedTags.value.some((selectedTag) => selectedTag.id === tag.id));
});

// Load personalized suggestions
const loadSuggestions = async (query = '') => {
    isLoading.value = true;
    try {
        // Use route helper instead of hardcoded URL
        const endpoint = query ? route('tags.suggestions') : route('tags.suggestions');
        const response = await axios.get(endpoint, {
            params: { query, limit: 10 },
        });

        suggestedTags.value = response.data.suggestions || [];
    } catch (error) {
        console.error('Error fetching tag suggestions:', error);
        // Fallback to basic search if suggestions fail
        if (query) {
            const response = await axios.get(route('tags.search'), { params: { query } });
            suggestedTags.value = response.data.map((tag) => ({
                ...tag,
                suggestion_type: 'fallback',
            }));
        }
    } finally {
        isLoading.value = false;
    }
};

// Load related tags based on selected tags
const loadRelatedTags = async () => {
    if (selectedTags.value.length === 0) {
        relatedTags.value = [];
        return;
    }

    try {
        const selectedTagIds = selectedTags.value.map((tag) => tag.id);
        const response = await axios.get(route('tags.related'), {
            params: { selected_tags: selectedTagIds, limit: 5 },
        });
        relatedTags.value = response.data.related_tags || [];
    } catch (error) {
        console.error('Error fetching related tags:', error);
        relatedTags.value = [];
    }
};

// Watch for changes in selected tags to update related suggestions
watch(
    selectedTags,
    () => {
        if (props.showSuggestions) {
            loadRelatedTags();
        }
    },
    { deep: true },
);

const addTag = (tag: any) => {
    const tagToAdd = {
        id: tag.id,
        name: tag.name,
        aliases: tag.aliases || [],
    };

    if (!selectedTags.value.some((t) => t.id === tagToAdd.id)) {
        selectedTags.value = [...selectedTags.value, tagToAdd];
    }
    inputValue.value = '';
    showDropdown.value = false;
};

const removeTag = (tagId: number) => {
    selectedTags.value = selectedTags.value.filter((tag) => tag.id !== tagId);
};

const handleInput = () => {
    showDropdown.value = true;
    loadSuggestions(inputValue.value);
};

const focusInput = () => {
    const tagInput = document.getElementById('tag-input');
    if (tagInput) {
        tagInput.focus();
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (filteredSuggestions.value.length > 0) {
            e.stopPropagation();
            addTag(filteredSuggestions.value[0]);
        }
    } else if (e.key === 'Escape') {
        showDropdown.value = false;
    }
};

const getSuggestionIcon = (suggestionType: string) => {
    switch (suggestionType) {
        case 'recent':
            return ClockIcon;
        case 'most_used':
            return TrendingUpIcon;
        case 'popular':
            return UsersIcon;
        case 'related':
            return LinkIcon;
        default:
            return ClockIcon;
    }
};

const getSuggestionLabel = (suggestionType: string) => {
    switch (suggestionType) {
        case 'recent':
            return 'Recently used';
        case 'most_used':
            return 'Most used';
        case 'popular':
            return 'Popular';
        case 'related':
            return 'Related';
        case 'user_match':
            return 'Your tag';
        case 'global_match':
            return 'Search result';
        default:
            return '';
    }
};

const getSuggestionColor = (suggestionType: string) => {
    switch (suggestionType) {
        case 'recent':
            return 'text-blue-600 dark:text-blue-400';
        case 'most_used':
            return 'text-green-600 dark:text-green-400';
        case 'popular':
            return 'text-purple-600 dark:text-purple-400';
        case 'related':
            return 'text-orange-600 dark:text-orange-400';
        case 'user_match':
            return 'text-indigo-600 dark:text-indigo-400';
        default:
            return 'text-gray-600 dark:text-gray-400';
    }
};

onMounted(() => {
    if (props.showSuggestions) {
        loadSuggestions();
    } else if (props.existingTags.length > 0) {
        suggestedTags.value = props.existingTags.map((tag) => ({
            ...tag,
            suggestion_type: 'existing',
        }));
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        const target = e.target as Element;
        if (target && !target.closest('.tag-input-container')) {
            showDropdown.value = false;
        }
    });
});
</script>

<template>
    <div class="tag-input-container relative w-full">
        <div class="border-input flex min-h-10 flex-wrap gap-2 rounded-md border p-2 dark:bg-[#d9d9d9] dark:text-[#2F2F2F]" @click="focusInput">
            <!-- Selected Tags -->
            <div
                v-for="tag in selectedTags"
                :key="tag.id"
                class="bg-primary text-primary-foreground inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs dark:text-[#2F2F2F]"
            >
                {{ tag.name }}
                <button @click.stop="removeTag(tag.id)" class="hover:text-primary/70 dark:text-[#2F2F2F]">
                    <XIcon class="dark:color-[#2F2F2F] h-3 w-3" />
                </button>
            </div>
            <!-- Input -->
            <input
                id="tag-input"
                v-model="inputValue"
                type="text"
                placeholder="Type to search or add tags..."
                class="placeholder:text-muted-foreground flex-1 border-none bg-transparent text-sm outline-none dark:text-[#2F2F2F] dark:placeholder:text-[#666]"
                @input="handleInput"
                @keydown="handleKeydown"
                @focus="showDropdown = true"
            />
        </div>

        <!-- Dropdown -->
        <div
            v-if="showDropdown && (filteredSuggestions.length > 0 || filteredRelatedTags.length > 0 || inputValue.trim())"
            class="border-border bg-background absolute top-full right-0 left-0 z-50 mt-1 max-h-64 overflow-y-auto rounded-md border shadow-lg"
        >
            <!-- Loading indicator -->
            <div v-if="isLoading" class="text-muted-foreground p-2 text-center text-sm">Loading suggestions...</div>

            <!-- Suggestions -->
            <div v-else>
                <!-- Search Results / Suggestions -->
                <div v-if="filteredSuggestions.length > 0">
                    <div class="bg-muted text-muted-foreground px-3 py-1 text-xs font-medium">
                        {{ inputValue ? 'Search Results' : 'Suggestions' }}
                    </div>
                    <button
                        v-for="tag in filteredSuggestions"
                        :key="tag.id"
                        class="hover:bg-accent flex w-full items-center gap-2 px-3 py-2 text-left text-sm"
                        @click="addTag(tag)"
                    >
                        <component :is="getSuggestionIcon(tag.suggestion_type)" class="h-4 w-4" :class="getSuggestionColor(tag.suggestion_type)" />
                        <span class="flex-1">{{ tag.name }}</span>
                        <span class="text-muted-foreground text-xs">{{ getSuggestionLabel(tag.suggestion_type) }}</span>
                    </button>
                </div>

                <!-- Related Tags -->
                <div v-if="filteredRelatedTags.length > 0">
                    <div class="bg-muted text-muted-foreground px-3 py-1 text-xs font-medium">Related Tags</div>
                    <button
                        v-for="tag in filteredRelatedTags"
                        :key="tag.id"
                        class="hover:bg-accent flex w-full items-center gap-2 px-3 py-2 text-left text-sm"
                        @click="addTag(tag)"
                    >
                        <LinkIcon class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                        <span class="flex-1">{{ tag.name }}</span>
                        <span class="text-muted-foreground text-xs">Related</span>
                    </button>
                </div>

                <!-- No results -->
                <div v-if="filteredSuggestions.length === 0 && filteredRelatedTags.length === 0 && !inputValue.trim()">
                    <div class="text-muted-foreground p-3 text-center text-sm">No suggestions available</div>
                </div>
                <div v-else-if="filteredSuggestions.length === 0 && filteredRelatedTags.length === 0 && inputValue.trim()">
                    <div class="text-muted-foreground p-3 text-center text-sm">No matching tags found</div>
                </div>
            </div>
        </div>
    </div>
</template>
