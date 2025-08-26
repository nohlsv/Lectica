<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { XIcon, PlusIcon } from 'lucide-vue-next';
import { type Tag } from '@/types';
import axios from 'axios';

interface Props {
  modelValue: Tag[];
  existingTags?: Tag[];
  addNewTags?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  existingTags: () => [],
  addNewTags: true
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref('');
const showDropdown = ref(false);
const suggestedTags = ref<Tag[]>([]);
const isLoading = ref(false);

const selectedTags = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const filteredSuggestions = computed(() => {
  if (!inputValue.value) return props.existingTags;

  return suggestedTags.value.filter(tag =>
    !selectedTags.value.some(selectedTag => selectedTag.id === tag.id)
  );
});

const loadSuggestions = async (query = '') => {
  if (query.length === 0 && props.existingTags.length > 0) {
    suggestedTags.value = props.existingTags;
    return;
  }

  isLoading.value = true;
  try {
    const response = await axios.get('/tags/search', { params: { query } });
    suggestedTags.value = response.data;
  } catch (error) {
    console.error('Error fetching tags:', error);
  } finally {
    isLoading.value = false;
  }
};

const addTag = (tag: Tag) => {
  if (!selectedTags.value.some(t => t.id === tag.id)) {
    selectedTags.value = [...selectedTags.value, tag];
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
    // Let's add it directly as a new tag object
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
}

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

onMounted(() => {
  if (props.existingTags.length > 0) {
    suggestedTags.value = props.existingTags;
  } else {
    loadSuggestions();
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
      class="flex flex-wrap gap-2 p-2 rounded-md border border-input text-[#333333] bg-[#FFF8F2]/80  min-h-10"
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
          {{ tag.name }}
        </div>
      </div>
    </div>
  </div>
</template>
