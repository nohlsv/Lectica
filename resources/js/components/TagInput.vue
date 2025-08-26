<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { XIcon, PlusIcon, ClockIcon, TrendingUpIcon, UsersIcon, LinkIcon } from 'lucide-vue-next';
import { type Tag } from '@/types';
import axios from 'axios';

interface Props {
  modelValue: Tag[];
  existingTags?: Tag[];
  addNewTags?: boolean;
  showSuggestions?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  existingTags: () => [],
  addNewTags: true,
  showSuggestions: true
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
  set: (value) => emit('update:modelValue', value)
});

const filteredSuggestions = computed(() => {
  return suggestedTags.value.filter(tag =>
    !selectedTags.value.some(selectedTag => selectedTag.id === tag.id)
  );
});

const filteredRelatedTags = computed(() => {
  return relatedTags.value.filter(tag =>
    !selectedTags.value.some(selectedTag => selectedTag.id === tag.id)
  );
});

// Load personalized suggestions
const loadSuggestions = async (query = '') => {
  isLoading.value = true;
  try {
    const endpoint = query ? '/tags/suggestions' : '/tags/suggestions';
    const response = await axios.get(endpoint, {
      params: query ? { query, limit: 10 } : { limit: 10 }
    });

    if (query) {
      suggestedTags.value = response.data.suggestions || [];
      currentSection.value = 'search';
    } else {
      suggestedTags.value = response.data.suggestions || [];
      currentSection.value = 'suggestions';
    }
  } catch (error) {
    console.error('Error fetching tag suggestions:', error);
    // Fallback to basic search if suggestions fail
    if (query) {
      const response = await axios.get('/tags/search', { params: { query } });
      suggestedTags.value = response.data.map(tag => ({
        ...tag,
        suggestion_type: 'fallback'
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
    const selectedTagIds = selectedTags.value.map(tag => tag.id);
    const response = await axios.get('/tags/related', {
      params: { selected_tags: selectedTagIds, limit: 5 }
    });
    relatedTags.value = response.data.related_tags || [];
  } catch (error) {
    console.error('Error fetching related tags:', error);
    relatedTags.value = [];
  }
};

// Watch for changes in selected tags to update related suggestions
watch(selectedTags, () => {
  if (props.showSuggestions) {
    loadRelatedTags();
  }
}, { deep: true });

const addTag = (tag: any) => {
  const tagToAdd = {
    id: tag.id,
    name: tag.name,
    aliases: tag.aliases || []
  };

  if (!selectedTags.value.some(t => t.id === tagToAdd.id)) {
    selectedTags.value = [...selectedTags.value, tagToAdd];
  }
  inputValue.value = '';
  showDropdown.value = false;
};

const createNewTag = async () => {
  if (!inputValue.value.trim() || !props.addNewTags) return;

  // Check if tag already exists in selected tags
  if (selectedTags.value.some(tag => tag.name.toLowerCase() === inputValue.value.toLowerCase())) {
    inputValue.value = '';
    return;
  }

  try {
    const response = await axios.post('/tags', { name: inputValue.value.trim() });
    addTag(response.data);
  } catch (error: any) {
    console.error('Error creating tag:', error);

    // If it's a 422 error (validation), it's likely the tag already exists
    if (error.response?.status === 422) {
      const existingTag = suggestedTags.value.find(
        tag => tag.name.toLowerCase() === inputValue.value.toLowerCase()
      );

      if (existingTag) {
        addTag(existingTag);
      }
    }
  }
};

const removeTag = (tagId: number) => {
  selectedTags.value = selectedTags.value.filter(tag => tag.id !== tagId);
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
    } else if (inputValue.value.trim()) {
        e.stopPropagation();
        createNewTag();
    }
  } else if (e.key === 'Escape') {
    showDropdown.value = false;
  }
};

const getSuggestionIcon = (suggestionType: string) => {
  switch (suggestionType) {
    case 'recent': return ClockIcon;
    case 'most_used': return TrendingUpIcon;
    case 'popular': return UsersIcon;
    case 'related': return LinkIcon;
    default: return PlusIcon;
  }
};

const getSuggestionLabel = (suggestionType: string) => {
  switch (suggestionType) {
    case 'recent': return 'Recently used';
    case 'most_used': return 'Most used';
    case 'popular': return 'Popular';
    case 'related': return 'Related';
    case 'user_match': return 'Your tag';
    case 'global_match': return 'Search result';
    default: return '';
  }
};

const getSuggestionColor = (suggestionType: string) => {
  switch (suggestionType) {
    case 'recent': return 'text-blue-600 dark:text-blue-400';
    case 'most_used': return 'text-green-600 dark:text-green-400';
    case 'popular': return 'text-purple-600 dark:text-purple-400';
    case 'related': return 'text-orange-600 dark:text-orange-400';
    case 'user_match': return 'text-indigo-600 dark:text-indigo-400';
    default: return 'text-gray-600 dark:text-gray-400';
  }
};

onMounted(() => {
  if (props.showSuggestions) {
    loadSuggestions();
  } else if (props.existingTags.length > 0) {
    suggestedTags.value = props.existingTags.map(tag => ({
      ...tag,
      suggestion_type: 'existing'
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
    <div
      class="flex flex-wrap gap-2 p-2 rounded-md border border-input dark:bg-[#d9d9d9] dark:text-[#2F2F2F]  min-h-10"
      @click="focusInput"
    >
      <!-- Selected Tags -->
      <div
        v-for="tag in selectedTags"
        :key="tag.id"
        class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md dark:text-[#2F2F2F]"
      >
        {{ tag.name }}
        <button @click.stop="removeTag(tag.id)" class="dark:text-[#2F2F2F] hover:text-primary/70">
          <XIcon class="h-3 w-3 dark:color-[#2F2F2F]" />
        </button>
      </div>

      <!-- Input -->
      <input
        id="tag-input"
        v-model="inputValue"
        type="text"
        placeholder="Add tags..."
        class="flex-1 outline-none bg-transparent min-w-[100px] text-sm"
        @input="handleInput"
        @focus="showDropdown = true"
        @keydown="handleKeydown"
      />
    </div>

    <!-- Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute z-10 mt-1 w-full rounded-md border border-border bg-background shadow-md py-1 max-h-45 overflow-y-auto"
    >
      <div v-if="isLoading" class="px-3 py-2 text-sm text-muted-foreground">
        Loading...
      </div>

      <div
        v-else-if="filteredSuggestions.length === 0 && inputValue"
        class="px-3 py-2 text-sm"
      >
        <div v-if="props.addNewTags" class="flex items-center justify-between">
          <span>Create "{{ inputValue }}"</span>
          <button
            @click.prevent.stop="createNewTag"
            class="p-1 rounded-sm hover:bg-accent"
          >
            <PlusIcon class="h-4 w-4" />
          </button>
        </div>
        <div v-else>No tags found</div>
      </div>

      <div v-else>
        <div
          v-for="tag in filteredSuggestions"
          :key="tag.id"
          class="px-3 py-2 text-sm hover:bg-accent cursor-pointer"
          @click="addTag(tag)"
        >
          <div class="flex items-center gap-2">
            <component
              :is="getSuggestionIcon(tag.suggestion_type)"
              class="h-4 w-4"
              v-tooltip="getSuggestionLabel(tag.suggestion_type)"
              :class="getSuggestionColor(tag.suggestion_type)"
            />
            <span>{{ tag.name }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
