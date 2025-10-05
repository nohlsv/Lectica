<template>
    <Transition name="modal">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm">
            <div class="bg-container w-full max-w-lg rounded-xl border-2 border-[#ffd700] p-6 shadow-[8px_8px_0px_rgba(0,0,0,0.8)]">
                <h2 class="pixel-outline mb-6 text-xl font-bold text-[#ffd700]">Manage File Collections</h2>

                <div v-if="!showCreateNew" class="mb-6 space-y-4">
                    <p class="text-sm text-[#FFF8DC]/80">
                        Select or deselect collections for this file. Changes will be saved when you click "Save Changes".
                    </p>

                    <div v-if="collections.length === 0" class="py-10 text-center">
                        <p class="text-sm text-[#FFF8DC]/60">No collections found. Create your first collection below.</p>
                    </div>

                    <div v-else class="max-h-60 space-y-2 overflow-y-auto">
                        <div
                            v-for="collection in collections"
                            :key="collection.id"
                            class="flex cursor-pointer items-center justify-between rounded-md border p-3 transition-colors"
                            :class="[
                                selectedCollections.includes(collection.id)
                                    ? 'border-[#ffd700] bg-[#a85a47] shadow-lg'
                                    : 'border-[#0c0a03] bg-[#28231d]/50 hover:bg-[#28231d]',
                            ]"
                            @click="toggleCollection(collection.id)"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p
                                        class="text-sm font-medium"
                                        :class="selectedCollections.includes(collection.id) ? 'text-white' : 'text-[#FFF8DC]'"
                                    >
                                        {{ collection.name }}
                                    </p>
                                    <span
                                        v-if="selectedCollections.includes(collection.id)"
                                        class="inline-flex items-center rounded-full bg-[#ffd700] px-2 py-1 text-xs font-medium text-[#0c0a03]"
                                    >
                                        Selected
                                    </span>
                                </div>
                                <p class="text-xs" :class="selectedCollections.includes(collection.id) ? 'text-[#FFF8DC]/80' : 'text-[#FFF8DC]/60'">
                                    {{ collection.files_count }} files
                                </p>
                            </div>
                            <div class="flex items-center">
                                <div v-if="selectedCollections.includes(collection.id)">
                                    <CheckIcon class="h-5 w-5 text-[#ffd700]" />
                                </div>
                                <div v-else>
                                    <div class="h-5 w-5 rounded border-2 border-[#FFF8DC]/30"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="showCreateNew" class="mb-6">
                    <label for="new-collection" class="mb-3 block text-sm font-medium text-white/80">New Collection Name</label>
                    <input
                        id="new-collection"
                        v-model="newCollectionName"
                        placeholder="Enter collection name"
                        class="pixel-outline placeholder:[#FFF8DC]/60 w-full rounded-lg border-2 border-[#0c0a03] bg-[#28231d] px-3 py-2 text-[#FFF8DC] focus:border-[#ffd700] focus:outline-none"
                        @keydown.enter="createNewCollection"
                    />
                </div>

                <div class="flex justify-between gap-3">
                    <button
                        @click="showCreateNew = !showCreateNew"
                        class="pixel-outline border-2 border-[#ffd700] bg-[#0c0a03] px-4 py-2 text-[#FFF8DC] shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#a85a47] hover:text-white hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                    >
                        {{ showCreateNew ? 'Select Existing' : 'Create New' }}
                    </button>
                    <div class="flex gap-2">
                        <button
                            @click="$emit('close')"
                            class="pixel-outline border-2 border-[#0c0a03] bg-[#28231d] px-4 py-2 text-[#FFF8DC] shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#0c0a03] hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                        >
                            Cancel
                        </button>

                        <!-- Save Changes Button -->
                        <button
                            v-if="!showCreateNew"
                            @click="saveChanges"
                            :disabled="saving"
                            class="pixel-outline border-2 border-[#0c0a03] bg-[#a85a47] px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#8d4a3a] hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ saving ? 'Saving...' : 'Save Changes' }}
                        </button>

                        <!-- Create Collection Button -->
                        <button
                            v-if="showCreateNew"
                            :disabled="isCreating || !newCollectionName.trim()"
                            @click="createNewCollection"
                            class="pixel-outline border-2 border-[#0c0a03] bg-[#a85a47] px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-[#8d4a3a] hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ isCreating ? 'Creating...' : 'Create & Add' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import axios from 'axios';
import { CheckIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Collection {
    id: number;
    name: string;
    files_count: number;
    is_public: boolean;
    contains_file: boolean;
}

interface Props {
    show: boolean;
    fileId: number | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    close: [];
    success: [];
}>();

const collections = ref<Collection[]>([]);
const selectedCollections = ref<number[]>([]);
const originalSelections = ref<number[]>([]);
const showCreateNew = ref(false);
const newCollectionName = ref('');
const isCreating = ref(false);
const saving = ref(false);

// Fetch collections when modal opens
watch(
    () => props.show,
    async (newShow) => {
        if (newShow && props.fileId) {
            await fetchCollections();
        }
    },
);

const fetchCollections = async () => {
    if (!props.fileId) return;

    try {
        const response = await axios.get(`/user/collections?file_id=${props.fileId}`);
        collections.value = response.data;

        // Set initial selections based on which collections contain the file
        const initialSelections = collections.value.filter((c) => c.contains_file).map((c) => c.id);

        selectedCollections.value = [...initialSelections];
        originalSelections.value = [...initialSelections];
    } catch (error) {
        console.error('Failed to fetch collections:', error);
        toast.error('Failed to load collections');
        collections.value = [];
    }
};

const toggleCollection = (collectionId: number) => {
    const index = selectedCollections.value.indexOf(collectionId);
    if (index === -1) {
        selectedCollections.value.push(collectionId);
    } else {
        selectedCollections.value.splice(index, 1);
    }
};

const createNewCollection = async () => {
    if (!newCollectionName.value.trim() || !props.fileId) return;

    isCreating.value = true;
    try {
        const response = await axios.post('/collections', {
            name: newCollectionName.value.trim(),
            is_public: false,
        });

        // Add the new collection to the selected list
        selectedCollections.value.push(response.data.id);

        // Refresh collections list
        await fetchCollections();

        showCreateNew.value = false;
        newCollectionName.value = '';
        toast.success('Collection created successfully!');
    } catch (error) {
        toast.error('Failed to create collection');
    } finally {
        isCreating.value = false;
    }
};

const saveChanges = async () => {
    if (!props.fileId) return;

    saving.value = true;

    try {
        // Determine which collections to add to and remove from
        const toAdd = selectedCollections.value.filter((id) => !originalSelections.value.includes(id));
        const toRemove = originalSelections.value.filter((id) => !selectedCollections.value.includes(id));

        const promises = [];

        // Add to new collections
        for (const collectionId of toAdd) {
            promises.push(
                axios.post(`/collections/${collectionId}/files`, {
                    file_id: props.fileId,
                }),
            );
        }

        // Remove from deselected collections
        for (const collectionId of toRemove) {
            promises.push(
                axios.delete(`/collections/${collectionId}/files`, {
                    data: { file_id: props.fileId },
                }),
            );
        }

        await Promise.all(promises);

        // Update original selections to current state
        originalSelections.value = [...selectedCollections.value];

        toast.success('Collection changes saved successfully!');
        emit('success');
        emit('close');
    } catch (error) {
        console.error('Failed to save changes:', error);
        toast.error('Failed to save changes');
    } finally {
        saving.value = false;
    }
};

// Reset state when modal closes
watch(
    () => props.show,
    (newShow) => {
        if (!newShow) {
            showCreateNew.value = false;
            newCollectionName.value = '';
            selectedCollections.value = [];
            originalSelections.value = [];
            collections.value = [];
        }
    },
);
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
