<template>
    <Head title="Faculty Updates" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">Faculty Updates</h2>
        </template>

        <div class="min-h-screen bg-gradient py-12">
            <!-- Hero Section -->
            <div class="mx-auto mb-8 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="welcome-banner">
                        <h1 class="pixel-outline my-2 text-4xl font-bold tracking-wide text-[#FFF8DC]">Faculty Management</h1>
                        <p class="pixel-outline text-lg text-[#FFF8DC]/80">Manage Programs and Tags</p>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Tab Navigation -->
                <div class="mb-8 flex space-x-1 rounded-xl border-2 border-[#ffd700] bg-[#0c0a03] p-1">
                    <button
                        @click="activeTab = 'programs'"
                        :class="[
                            'pixel-outline flex-1 rounded-lg px-6 py-3 text-sm font-medium leading-5 transition-all duration-200',
                            activeTab === 'programs'
                                ? 'bg-[#b71400] text-[#FFF8DC] shadow-lg'
                                : 'text-[#FFF8DC]/70 hover:text-[#FFF8DC] hover:bg-[#b71400]/50'
                        ]"
                    >
                        Programs ({{ programs.length }})
                    </button>
                    <button
                        @click="activeTab = 'tags'"
                        :class="[
                            'pixel-outline flex-1 rounded-lg px-6 py-3 text-sm font-medium leading-5 transition-all duration-200',
                            activeTab === 'tags'
                                ? 'bg-[#b71400] text-[#FFF8DC] shadow-lg'
                                : 'text-[#FFF8DC]/70 hover:text-[#FFF8DC] hover:bg-[#b71400]/50'
                        ]"
                    >
                        Tags ({{ tags.length }})
                    </button>
                </div>

                <!-- Programs Tab -->
                <div v-if="activeTab === 'programs'" class="space-y-6">
                    <!-- Add New Program -->
                    <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-6">
                        <h3 class="pixel-outline mb-4 text-xl font-bold text-[#FFF8DC]">Add New Program</h3>
                        <form @submit.prevent="submitProgram" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="program-name" class="pixel-outline block text-sm font-medium text-[#FFF8DC] mb-1">
                                        Program Name *
                                    </label>
                                    <input
                                        id="program-name"
                                        v-model="programForm.name"
                                        type="text"
                                        class="w-full rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-3 py-2 text-[#FFF8DC] placeholder-[#FFF8DC]/50"
                                        placeholder="e.g., Computer Science"
                                        required
                                    />
                                </div>
                                <div>
                                    <label for="program-code" class="pixel-outline block text-sm font-medium text-[#FFF8DC] mb-1">
                                        Program Code *
                                    </label>
                                    <input
                                        id="program-code"
                                        v-model="programForm.code"
                                        type="text"
                                        class="w-full rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-3 py-2 text-[#FFF8DC] placeholder-[#FFF8DC]/50"
                                        placeholder="e.g., CS"
                                        required
                                    />
                                </div>
                                <div>
                                    <label for="program-college" class="pixel-outline block text-sm font-medium text-[#FFF8DC] mb-1">
                                        College *
                                    </label>
                                    <input
                                        id="program-college"
                                        v-model="programForm.college"
                                        type="text"
                                        class="w-full rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-3 py-2 text-[#FFF8DC] placeholder-[#FFF8DC]/50"
                                        placeholder="e.g., College of Engineering"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <Button
                                    type="submit"
                                    :disabled="programForm.processing"
                                    class="pixel-btn-primary flex items-center space-x-2"
                                >
                                    <PlusIcon class="h-4 w-4" />
                                    <span>Add Program</span>
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- Programs List -->
                    <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-6">
                        <h3 class="pixel-outline mb-4 text-xl font-bold text-[#FFF8DC]">Existing Programs</h3>
                        <div class="space-y-3">
                            <div
                                v-for="program in programs"
                                :key="program.id"
                                class="flex items-center justify-between rounded-lg border border-[#ffd700]/30 bg-[#0c0a03] p-4"
                            >
                                <div v-if="editingProgram !== program.id" class="flex-1">
                                    <h4 class="pixel-outline font-semibold text-[#FFF8DC]">{{ program.name }}</h4>
                                    <p class="pixel-outline text-sm text-[#FFF8DC]/70">
                                        Code: {{ program.code }} | College: {{ program.college }}
                                    </p>
                                </div>
                                <form 
                                    v-else 
                                    @submit.prevent="updateProgram(program.id)"
                                    class="flex-1 space-y-2"
                                >
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                        <input
                                            v-model="editProgramForm.name"
                                            type="text"
                                            class="w-full rounded border border-[#ffd700] bg-[#0c0a03] px-2 py-1 text-sm text-[#FFF8DC]"
                                            placeholder="Program name"
                                            required
                                        />
                                        <input
                                            v-model="editProgramForm.code"
                                            type="text"
                                            class="w-full rounded border border-[#ffd700] bg-[#0c0a03] px-2 py-1 text-sm text-[#FFF8DC]"
                                            placeholder="Code"
                                            required
                                        />
                                        <input
                                            v-model="editProgramForm.college"
                                            type="text"
                                            class="w-full rounded border border-[#ffd700] bg-[#0c0a03] px-2 py-1 text-sm text-[#FFF8DC]"
                                            placeholder="College"
                                            required
                                        />
                                    </div>
                                </form>
                                <div class="flex items-center space-x-2">
                                    <template v-if="editingProgram !== program.id">
                                        <button
                                            @click="startEditProgram(program)"
                                            class="rounded p-1 text-blue-400 hover:bg-blue-400/10"
                                        >
                                            <PencilIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteProgram(program.id)"
                                            class="rounded p-1 text-red-400 hover:bg-red-400/10"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </template>
                                    <template v-else>
                                        <button
                                            @click="updateProgram(program.id)"
                                            class="rounded p-1 text-green-400 hover:bg-green-400/10"
                                        >
                                            <CheckIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="cancelEditProgram"
                                            class="rounded p-1 text-gray-400 hover:bg-gray-400/10"
                                        >
                                            <XIcon class="h-4 w-4" />
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags Tab -->
                <div v-if="activeTab === 'tags'" class="space-y-6">
                    <!-- Add New Tag -->
                    <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-6">
                        <h3 class="pixel-outline mb-4 text-xl font-bold text-[#FFF8DC]">Add New Tag</h3>
                        <form @submit.prevent="submitTag" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="tag-name" class="pixel-outline block text-sm font-medium text-[#FFF8DC] mb-1">
                                        Tag Name *
                                    </label>
                                    <input
                                        id="tag-name"
                                        v-model="tagForm.name"
                                        type="text"
                                        class="w-full rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-3 py-2 text-[#FFF8DC] placeholder-[#FFF8DC]/50"
                                        placeholder="e.g., Mathematics"
                                        required
                                    />
                                </div>
                                <div>
                                    <label for="tag-aliases" class="pixel-outline block text-sm font-medium text-[#FFF8DC] mb-1">
                                        Aliases (comma-separated)
                                    </label>
                                    <input
                                        id="tag-aliases"
                                        v-model="aliasInput"
                                        type="text"
                                        class="w-full rounded-lg border-2 border-[#ffd700] bg-[#0c0a03] px-3 py-2 text-[#FFF8DC] placeholder-[#FFF8DC]/50"
                                        placeholder="e.g., Math, Maths, Calculus"
                                    />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <Button
                                    type="submit"
                                    :disabled="tagForm.processing"
                                    class="pixel-btn-primary flex items-center space-x-2"
                                >
                                    <PlusIcon class="h-4 w-4" />
                                    <span>Add Tag</span>
                                </Button>
                            </div>
                        </form>
                    </div>

                    <!-- Tags List -->
                    <div class="bg-container rounded-2xl border-2 border-[#ffd700] p-6">
                        <h3 class="pixel-outline mb-4 text-xl font-bold text-[#FFF8DC]">Existing Tags</h3>
                        <div class="space-y-4">
                            <div
                                v-for="tag in tags"
                                :key="tag.id"
                                class="rounded-lg border border-[#ffd700]/30 bg-[#0c0a03] p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div v-if="editingTag !== tag.id" class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h4 class="pixel-outline font-semibold text-[#FFF8DC]">{{ tag.name }}</h4>
                                            <span class="pixel-outline text-xs text-[#FFF8DC]/50">
                                                ({{ tag.files_count }} files)
                                            </span>
                                        </div>
                                        <div v-if="tag.aliases && tag.aliases.length > 0" class="mt-1 flex flex-wrap gap-1">
                                            <span class="pixel-outline text-xs text-[#FFF8DC]/70 mr-2">Aliases:</span>
                                            <span
                                                v-for="alias in tag.aliases"
                                                :key="alias"
                                                class="inline-flex items-center rounded-full bg-[#b71400]/50 px-2 py-1 text-xs text-[#FFF8DC]"
                                            >
                                                {{ alias }}
                                                <button
                                                    @click="removeAlias(tag.id, alias)"
                                                    class="ml-1 text-red-400 hover:text-red-300"
                                                >
                                                    <XIcon class="h-3 w-3" />
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                    <form 
                                        v-else 
                                        @submit.prevent="updateTag(tag.id)"
                                        class="flex-1 space-y-2"
                                    >
                                        <input
                                            v-model="editTagForm.name"
                                            type="text"
                                            class="w-full rounded border border-[#ffd700] bg-[#0c0a03] px-2 py-1 text-sm text-[#FFF8DC]"
                                            placeholder="Tag name"
                                            required
                                        />
                                        <input
                                            v-model="editAliasInput"
                                            type="text"
                                            class="w-full rounded border border-[#ffd700] bg-[#0c0a03] px-2 py-1 text-sm text-[#FFF8DC]"
                                            placeholder="Aliases (comma-separated)"
                                        />
                                    </form>
                                    <div class="flex items-center space-x-2">
                                        <template v-if="editingTag !== tag.id">
                                            <button
                                                @click="startAddAlias(tag.id)"
                                                class="rounded p-1 text-green-400 hover:bg-green-400/10"
                                                title="Add alias"
                                            >
                                                <PlusIcon class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="startEditTag(tag)"
                                                class="rounded p-1 text-blue-400 hover:bg-blue-400/10"
                                            >
                                                <PencilIcon class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="deleteTag(tag.id)"
                                                class="rounded p-1 text-red-400 hover:bg-red-400/10"
                                            >
                                                <TrashIcon class="h-4 w-4" />
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button
                                                @click="updateTag(tag.id)"
                                                class="rounded p-1 text-green-400 hover:bg-green-400/10"
                                            >
                                                <CheckIcon class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="cancelEditTag"
                                                class="rounded p-1 text-gray-400 hover:bg-gray-400/10"
                                            >
                                                <XIcon class="h-4 w-4" />
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Add Alias Form -->
                                <div v-if="addingAliasTo === tag.id" class="mt-3 pt-3 border-t border-[#ffd700]/20">
                                    <form @submit.prevent="addAlias(tag.id)" class="flex space-x-2">
                                        <input
                                            v-model="newAliasInput"
                                            type="text"
                                            class="flex-1 rounded border border-[#ffd700] bg-[#0c0a03] px-2 py-1 text-sm text-[#FFF8DC]"
                                            placeholder="Enter new alias"
                                            required
                                        />
                                        <button
                                            type="submit"
                                            class="rounded bg-green-600 px-3 py-1 text-xs text-white hover:bg-green-700"
                                        >
                                            Add
                                        </button>
                                        <button
                                            type="button"
                                            @click="cancelAddAlias"
                                            class="rounded bg-gray-600 px-3 py-1 text-xs text-white hover:bg-gray-700"
                                        >
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { PlusIcon, PencilIcon, TrashIcon, CheckIcon, XIcon } from 'lucide-vue-next';
import type { Program } from '@/types';

interface ExtendedTag {
    id: number;
    name: string;
    aliases: string[];
    searchable_terms: string[];
    files_count: number;
}

interface Props {
    programs: Program[];
    tags: ExtendedTag[];
}

const props = defineProps<Props>();

// Tab management
const activeTab = ref<'programs' | 'tags'>('programs');

// Program forms
const programForm = useForm({
    name: '',
    code: '',
    college: '',
});

const editProgramForm = useForm({
    name: '',
    code: '',
    college: '',
});

const editingProgram = ref<number | null>(null);

// Tag forms
const tagForm = useForm({
    name: '',
    aliases: [] as string[],
});

const editTagForm = useForm({
    name: '',
    aliases: [] as string[],
});

const editingTag = ref<number | null>(null);
const aliasInput = ref('');
const editAliasInput = ref('');
const addingAliasTo = ref<number | null>(null);
const newAliasInput = ref('');

// Program methods
function submitProgram() {
    programForm.post(route('faculty.programs.store'), {
        onSuccess: () => {
            programForm.reset();
            toast.success('Program created successfully!');
        },
        onError: () => {
            toast.error('Failed to create program. Please check the form.');
        },
    });
}

function startEditProgram(program: Program) {
    editingProgram.value = program.id;
    editProgramForm.name = program.name;
    editProgramForm.code = program.code;
    editProgramForm.college = program.college || '';
}

function updateProgram(programId: number) {
    editProgramForm.put(route('faculty.programs.update', programId), {
        onSuccess: () => {
            editingProgram.value = null;
            editProgramForm.reset();
            toast.success('Program updated successfully!');
        },
        onError: () => {
            toast.error('Failed to update program. Please check the form.');
        },
    });
}

function cancelEditProgram() {
    editingProgram.value = null;
    editProgramForm.reset();
}

function deleteProgram(programId: number) {
    if (confirm('Are you sure you want to delete this program? This action cannot be undone.')) {
        router.delete(route('faculty.programs.delete', programId), {
            onSuccess: () => {
                toast.success('Program deleted successfully!');
            },
            onError: () => {
                toast.error('Failed to delete program. It may have associated users.');
            },
        });
    }
}

// Tag methods
function submitTag() {
    // Parse aliases from input
    const aliases = aliasInput.value
        .split(',')
        .map(alias => alias.trim())
        .filter(alias => alias.length > 0);

    tagForm.aliases = aliases;

    tagForm.post(route('faculty.tags.store'), {
        onSuccess: () => {
            tagForm.reset();
            aliasInput.value = '';
            toast.success('Tag created successfully!');
        },
        onError: () => {
            toast.error('Failed to create tag. Please check the form.');
        },
    });
}

function startEditTag(tag: ExtendedTag) {
    editingTag.value = tag.id;
    editTagForm.name = tag.name;
    editTagForm.aliases = [...(tag.aliases || [])];
    editAliasInput.value = (tag.aliases || []).join(', ');
}

function updateTag(tagId: number) {
    // Parse aliases from input
    const aliases = editAliasInput.value
        .split(',')
        .map(alias => alias.trim())
        .filter(alias => alias.length > 0);

    editTagForm.aliases = aliases;

    editTagForm.put(route('faculty.tags.update', tagId), {
        onSuccess: () => {
            editingTag.value = null;
            editTagForm.reset();
            editAliasInput.value = '';
            toast.success('Tag updated successfully!');
        },
        onError: () => {
            toast.error('Failed to update tag. Please check the form.');
        },
    });
}

function cancelEditTag() {
    editingTag.value = null;
    editTagForm.reset();
    editAliasInput.value = '';
}

function deleteTag(tagId: number) {
    if (confirm('Are you sure you want to delete this tag? This action cannot be undone.')) {
        router.delete(route('faculty.tags.delete', tagId), {
            onSuccess: () => {
                toast.success('Tag deleted successfully!');
            },
            onError: () => {
                toast.error('Failed to delete tag. It may have associated files.');
            },
        });
    }
}

// Alias management
function startAddAlias(tagId: number) {
    addingAliasTo.value = tagId;
    newAliasInput.value = '';
}

function addAlias(tagId: number) {
    if (!newAliasInput.value.trim()) return;

    router.post(route('faculty.tags.aliases.add', tagId), {
        alias: newAliasInput.value.trim(),
    }, {
        onSuccess: () => {
            addingAliasTo.value = null;
            newAliasInput.value = '';
            toast.success('Alias added successfully!');
        },
        onError: () => {
            toast.error('Failed to add alias.');
        },
    });
}

function removeAlias(tagId: number, alias: string) {
    if (confirm(`Remove alias "${alias}"?`)) {
        router.post(route('faculty.tags.aliases.remove', tagId), {
            alias: alias,
        }, {
            onSuccess: () => {
                toast.success('Alias removed successfully!');
            },
            onError: () => {
                toast.error('Failed to remove alias.');
            },
        });
    }
}

function cancelAddAlias() {
    addingAliasTo.value = null;
    newAliasInput.value = '';
}
</script>