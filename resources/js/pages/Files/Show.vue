<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeftIcon,
    BookOpenIcon,
    CheckCircleIcon,
    ClockIcon,
    DownloadIcon,
    FileIcon,
    FileType2Icon,
    ListChecks,
    Loader2Icon,
    PencilIcon,
    PlusIcon,
    Share2Icon,
    ShieldCheckIcon,
    StarIcon,
    XCircleIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    file: File;
    fileInfo: {
        extension: string;
        exists: boolean;
        size: string | null;
        url: string | null;
        lastModified: string | null;
    };
}

const props = defineProps<Props>();

const isStarred = ref(props.file.is_starred || false);
const isStarring = ref(false);
const isVerifying = ref(false);
const showCollectionModal = ref(false);
const showDenyModal = ref(false);
const denialReason = ref('');
const isDenying = ref(false);
const userCollections = ref<Collection[]>([]);
const selectedCollection = ref<number | null>(null);
const showCreateNewCollection = ref(false);
const newCollectionName = ref('');
const isCreatingCollection = ref(false);

interface Collection {
    id: number;
    name: string;
    file_count: number;
    is_public: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: props.file.name,
        href: `/files/${props.file.id}`,
    },
];

const toggleStar = async () => {
    if (isStarring.value) return;

    isStarring.value = true;

    try {
        router.post(
            route('files.star', { file: props.file.id }),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    isStarred.value = !isStarred.value;
                    toast.success(isStarred.value ? 'File starred successfully!' : 'File unstarred successfully!');
                },
                onFinish: () => {
                    isStarring.value = false;
                },
            },
        );
    } catch (error) {
        isStarring.value = false;
        toast.error('Error toggling star.');
    }
};

const verifyFile = async () => {
    if (isVerifying.value) return;

    isVerifying.value = true;

    try {
        router.patch(
            route('files.verify.update', { file: props.file.id }),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    props.file.verified = true;
                    toast.success('File verified successfully!');
                },
                onError: () => {
                    toast.error('Failed to verify the file.');
                },
                onFinish: () => {
                    isVerifying.value = false;
                },
            },
        );
    } catch (error) {
        isVerifying.value = false;
        toast.error('Error verifying the file.');
    }
};

const openDenyModal = () => {
    denialReason.value = '';
    showDenyModal.value = true;
};

const denyFile = async () => {
    if (!denialReason.value.trim()) {
        toast.error('Please provide a reason for denial.');
        return;
    }

    if (isDenying.value) return;

    isDenying.value = true;

    try {
        router.patch(
            route('files.verify.deny', { file: props.file.id }),
            { denial_reason: denialReason.value },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    props.file.is_denied = true;
                    props.file.denial_reason = denialReason.value;
                    showDenyModal.value = false;
                    toast.success('File denied and user notified!');
                },
                onError: () => {
                    toast.error('Failed to deny the file.');
                },
                onFinish: () => {
                    isDenying.value = false;
                },
            },
        );
    } catch (error) {
        isDenying.value = false;
        toast.error('Error denying the file.');
    }
};

const copyShareLink = async () => {
    try {
        await navigator.clipboard.writeText(window.location.href);
        toast.success('Link copied to clipboard!');
    } catch (error) {
        toast.error('Failed to copy link');
    }
};

const isPdf = computed(() => props.fileInfo.extension.toLowerCase() === 'pdf');
const isTxt = computed(() => props.fileInfo.extension.toLowerCase() === 'txt');
const isImage = computed(() => ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(props.fileInfo.extension.toLowerCase()));
const isOfficeFile = computed(() => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(props.fileInfo.extension.toLowerCase()));
const isPreviewable = computed(() => isPdf.value || isTxt.value || isImage.value || isOfficeFile.value);

const isOwner = computed(() => {
    return props.file.can_edit === true;
});

const isGenerating = ref(false);
const isDialogOpen = ref(false);

const generateOptions = ref({
    generate_flashcards: true,
    flashcards_count: 5,
    generate_multiple_choice_quizzes: true,
    multiple_choice_count: 5,
    generate_enumeration_quizzes: false,
    enumeration_count: 3,
    generate_true_false_quizzes: false,
    true_false_count: 3,
});

interface Auth {
    user: User;
}
const page = usePage();
const auth = computed<Auth>(() => page.props.auth as Auth);
const canVerify = computed(() => ['faculty', 'admin'].includes(auth.value.user.user_role));

const submitGenerateRequest = async () => {
    if (isGenerating.value) return;

    isGenerating.value = true;

    router.post(route('files.generate-flashcards-quizzes', { file: props.file.id }), generateOptions.value, {
        preserveScroll: true,
        onSuccess: () => {
            isDialogOpen.value = false; // Close the dialog
            toast.success('Flashcards and quizzes generated successfully!');
        },
        onError: () => {
            toast.error('Failed to generate flashcards and quizzes.');
        },
        onFinish: () => {
            isGenerating.value = false;
        },
    });
};

// Fetch user's collections for adding files
const fetchUserCollections = async () => {
    try {
        const response = await fetch('/user/collections');
        const data = await response.json();
        userCollections.value = data;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

const openCollectionModal = () => {
    selectedCollection.value = null;
    showCollectionModal.value = true;
    showCreateNewCollection.value = false;
    newCollectionName.value = '';
    fetchUserCollections();
};

const createNewCollection = async () => {
    if (!newCollectionName.value.trim()) return;

    isCreatingCollection.value = true;
    try {
        const response = await axios.post('/collections', {
            name: newCollectionName.value.trim(),
            is_public: false,
        });

        await fetchUserCollections();
        selectedCollection.value = response.data.id;
        showCreateNewCollection.value = false;
        newCollectionName.value = '';
        toast.success('Collection created successfully!');
    } catch (error) {
        toast.error('Failed to create collection');
    } finally {
        isCreatingCollection.value = false;
    }
};

const addToCollection = async () => {
    if (!selectedCollection.value) return;

    try {
        await router.post(
            route('collections.add-file', selectedCollection.value),
            {
                file_id: props.file.id,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    showCollectionModal.value = false;
                    selectedCollection.value = null;
                    toast.success('File added to collection successfully!');
                },
                onError: (errors) => {
                    if (errors.file) {
                        toast.error(errors.file);
                    } else {
                        toast.error('Failed to add file to collection');
                    }
                },
            },
        );
    } catch (error) {
        toast.error('Failed to add file to collection');
    }
};
</script>

<template>
    <Head :title="`File: ${file.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dark:bg-[#161615]">
            <!-- Compact Header with Actions -->
            <div class="bg-lectica flex w-full flex-col px-4 pt-2 pb-2">
                <div class="flex items-center justify-between gap-2 rounded-xl p-2">
                    <!--File Info-->
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded border-2 border-white bg-gradient-to-br from-blue-500 to-purple-600">
                            <FileIcon class="h-4 w-4 text-white" />
                        </div>
                        <div>
                            <h1 class="text-sm font-pixel text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                                {{ file.name }}
                            </h1>
                            <div class="flex items-center space-x-1 text-xs text-white/80">
                                <span>{{ fileInfo.size ? Math.round(Number(fileInfo.size) / 1024 / 1024 * 100) / 100 + ' MB' : 'Unknown size' }}</span>
                                <span>•</span>
                                <span>{{ new Date(file.created_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>

                    <!--Actions-->
                    <div class="flex items-center space-x-1">
                        <!-- Download -->
                        <a
                            :href="route('files.download', { file: file.id })"
                            download
                            class="flex h-7 items-center justify-center rounded border border-blue-400/70 bg-blue-400/20 px-2 transition-all hover:bg-blue-400/30"
                            title="Download"
                        >
                            <DownloadIcon class="h-3 w-3 text-blue-300 mr-1" />
                            <span class="text-blue-300 font-pixel">Download</span>
                        </a>


                        <!-- Add to Collection -->
                        <button
                            @click="showCollectionModal = true"
                            class="flex h-7 items-center justify-center rounded border border-purple-400/70 bg-purple-400/20 px-2 transition-all hover:bg-purple-400/30"
                            title="Add to Collection"
                        >
                            <PlusIcon class="h-3 w-3 text-purple-300 mr-1" />
                            <span class="text-purple-300 font-pixel">Add to Collection</span>
                        </button>

                        <!-- Star -->
                        <button
                            @click="toggleStar"
                            class="flex h-7 items-center justify-center rounded border border-yellow-400/70 bg-yellow-400/20 px-2 transition-all hover:bg-yellow-400/30"
                            title="Star File"
                        >
                            <StarIcon
                                :class="[
                                    'h-3 w-3 transition-colors mr-1',
                                    file.is_starred 
                                        ? 'fill-yellow-300 text-yellow-300' 
                                        : 'text-white/60 hover:text-yellow-300'
                                ]"
                            />
                            <span class="font-pixel" :class="file.is_starred ? 'text-yellow-300' : 'text-white/60'">
                                {{ file.is_starred ? 'Starred' : 'Star' }}
                            </span>
                        </button>

                        <!-- Back -->
                        <Link href="/files">
                            <Button class="font-pixel border border-white bg-red-600 px-2 py-1 text-white transition-all hover:bg-red-900">
                                <ArrowLeftIcon class="mr-1 h-3 w-3" />
                                Back
                            </Button>
                        </Link>
                    </div>
                </div>
                <!--Divider-->
                <hr class="-mx-4 h-0.5 border border-black bg-blue-500" />
            </div>

            <!--Main Content - Two Column Layout-->
            <div class="bg-gradient flex h-full flex-1 flex-col px-4 py-3 lg:px-6">
                <!-- File Info and Preview Grid -->
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_2fr]">
                    <!-- Left Column: File Info & Actions -->
                    <div class="space-y-3">
                        <!-- File Details Card -->
                        <div class="rounded border border-white/20 bg-black/30 p-2 backdrop-blur-sm">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-pixel font-bold text-yellow-400">
                                    📄 Info
                                </h4>
                                <!-- Edit Button -->
                                <Link v-if="isOwner" :href="route('files.edit', file.id)">
                                    <button
                                        class="flex items-center justify-center rounded border border-orange-400/70 bg-orange-400/20 px-2 py-1 text-xs transition-all hover:bg-orange-400/30"
                                        title="Edit File"
                                    >
                                        <PencilIcon class="h-3 w-3 text-orange-300 mr-1" />
                                        <span class="text-orange-300 font-pixel">Edit</span>
                                    </button>
                                </Link>
                            </div>
                            <!-- Verification Status -->
                            <div class="mb-3 text-xs">
                                <span
                                    v-if="file.verified"
                                    class="font-pixel inline-flex items-center rounded border border-green-400 bg-green-600 px-2 py-1 font-bold text-white"
                                >
                                    ✓ Verified
                                </span>
                                <span
                                    v-else-if="file.is_denied"
                                    class="font-pixel inline-flex items-center rounded border border-red-400 bg-red-600 px-2 py-1 font-bold text-white"
                                >
                                    ✗ Denied
                                </span>
                                <span
                                    v-else
                                    class="font-pixel inline-flex items-center rounded border border-yellow-400 bg-yellow-600 px-2 py-1 font-bold text-black"
                                >
                                    ⏳ Pending
                                </span>
                            </div>

                            <!-- Denial Reason -->
                            <div v-if="file.is_denied && file.denial_reason" class="mb-3 rounded border border-red-400 bg-red-600/20 p-2">
                                <p class="font-medium text-red-300">Denial Reason:</p>
                                <p class="text-white">{{ file.denial_reason }}</p>
                            </div>

                            <!-- Compact File Details -->
                            <div class="space-y-2">
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Type:</span>
                                    <span class="font-bold text-white uppercase">{{ fileInfo.extension }}</span>
                                </div>
                                <div v-if="fileInfo.size" class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Size:</span>
                                    <span class="font-bold text-white">{{ ( Number(fileInfo.size) / 1024 / 1024).toFixed(2) }} MB</span>
                                </div>
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Uploaded By:</span>
                                    <span class="font-bold text-white">{{ file.user.last_name }}, {{ file.user.first_name }}</span>
                                </div>
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Uploaded Date:</span>
                                    <span class="font-bold text-white">{{ new Date(file.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) }}</span>
                                </div>
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Last Modified Date:</span>
                                    <span class="font-bold text-white">{{ new Date(file.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' }) }}</span>
                                </div>
                                <div v-if="file.tags && file.tags.length" class="rounded bg-white/5 p-1.5">
                                    <div class="text-yellow-400 mb-1">Tags:</div>
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="tag in file.tags" :key="tag.id" class="font-pixel inline-flex px-1 py-0.5 rounded bg-purple-600 text-white border border-purple-400">
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Study Materials -->
                        <div class="rounded-lg border-2 border-white/20 bg-black/30 p-4 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                            <h4 class="font-pixel mb-4 text-lg font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">📚 Study Materials</h4>
                            <div class="space-y-4 flex flex-col items-center">
                                <!-- Flashcards -->
                                <div class="w-full rounded-lg border border-blue-400/30 bg-blue-600/10 p-3 backdrop-blur-sm">
                                    <h5 class="font-pixel mb-3 text-center font-bold text-blue-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]">📖 Flashcards</h5>
                                    <div class="flex gap-2">
                                        <Link :href="route('files.flashcards.index', file.id)" class="flex-1">
                                            <button class="font-pixel w-full border-2 border-blue-400 bg-blue-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-blue-700 hover:scale-[1.02]">
                                                View
                                            </button>
                                        </Link>
                                        <Link :href="route('files.flashcards.practice', file.id)" class="flex-1">
                                            <button class="font-pixel w-full border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-green-700 hover:scale-[1.02]">
                                                Practice
                                            </button>
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Quizzes -->
                                <div class="w-full rounded-lg border border-purple-400/30 bg-purple-600/10 p-3 backdrop-blur-sm">
                                    <h5 class="font-pixel mb-3 text-center font-bold text-purple-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]">🧠 Quizzes</h5>
                                    <div class="flex gap-2">
                                        <Link :href="route('files.quizzes.index', file.id)" class="flex-1">
                                            <button class="font-pixel w-full border-2 border-purple-400 bg-purple-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-purple-700 hover:scale-[1.02]">
                                                View
                                            </button>
                                        </Link>
                                        <Link :href="route('files.quizzes.test', file.id)" class="flex-1">
                                            <button class="font-pixel w-full border-2 border-orange-400 bg-orange-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-orange-700 hover:scale-[1.02]">
                                                Practice
                                            </button>
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Battle Mode -->
                                <div v-if="file.verified" class="w-full rounded-lg border border-red-400/30 bg-red-600/10 p-3 backdrop-blur-sm">
                                    <h5 class="font-pixel mb-3 text-center font-bold text-red-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]">⚔️ Battle Mode</h5>
                                    <div class="flex gap-2">
                                        <Link :href="route('battles.create', { file_id: file.id })" class="flex-1">
                                            <button class="font-pixel w-full border-2 border-red-400 bg-red-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-red-700 hover:scale-[1.02]">
                                                Solo Battle
                                            </button>
                                        </Link>
                                        <Link :href="route('multiplayer-games.lobby', { file_id: file.id })" class="flex-1">
                                            <button class="font-pixel w-full border-2 border-purple-400 bg-purple-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-purple-700 hover:scale-[1.02]">
                                                Multiplayer
                                            </button>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Generate Content -->
                                <div v-if="isOwner && file.verified" class="w-full rounded-lg border border-yellow-400/30 bg-yellow-600/10 p-3 backdrop-blur-sm">
                                    <h5 class="font-pixel mb-3 text-center font-bold text-yellow-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]">🎲 Generate Content</h5>
                                    <Dialog v-model:open="isDialogOpen" onOpenChange="isDialogOpen = $event">
                                        <DialogTrigger asChild>
                                            <button class="font-pixel w-full border-2 border-yellow-400 bg-yellow-600 px-4 py-2 text-black shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-yellow-500 hover:scale-[1.02]">
                                                <PencilIcon class="mr-1 h-3 w-3 inline" />
                                                Generate Flashcards & Quizzes
                                            </button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>Generate Flashcards & Quizzes</DialogTitle>
                                            </DialogHeader>
                                            <div class="space-y-4">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" v-model="generateOptions.generate_flashcards" id="generate_flashcards" />
                                                    <label for="generate_flashcards" class="text-sm font-medium">Generate Flashcards</label>
                                                </div>
                                                <div v-if="generateOptions.generate_flashcards" class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <label for="flashcards_count" class="text-sm font-medium">Flashcards Count:</label>
                                                        <span class="text-sm text-gray-600">{{ generateOptions.flashcards_count }}</span>
                                                    </div>
                                                    <input
                                                        type="range"
                                                        id="flashcards_count"
                                                        v-model="generateOptions.flashcards_count"
                                                        min="1"
                                                        max="15"
                                                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                                                    />
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        v-model="generateOptions.generate_multiple_choice_quizzes"
                                                        id="generate_multiple_choice_quizzes"
                                                    />
                                                    <label for="generate_multiple_choice_quizzes" class="text-sm font-medium"
                                                        >Generate Multiple Choice Quizzes</label
                                                    >
                                                </div>
                                                <div v-if="generateOptions.generate_multiple_choice_quizzes" class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <label for="multiple_choice_count" class="text-sm font-medium">Multiple Choice Count:</label>
                                                        <span class="text-sm text-gray-600">{{ generateOptions.multiple_choice_count }}</span>
                                                    </div>
                                                    <input
                                                        type="range"
                                                        id="multiple_choice_count"
                                                        v-model="generateOptions.multiple_choice_count"
                                                        min="1"
                                                        max="15"
                                                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                                                    />
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        v-model="generateOptions.generate_enumeration_quizzes"
                                                        id="generate_enumeration_quizzes"
                                                    />
                                                    <label for="generate_enumeration_quizzes" class="text-sm font-medium"
                                                        >Generate Enumeration Quizzes</label
                                                    >
                                                </div>
                                                <div v-if="generateOptions.generate_enumeration_quizzes" class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <label for="enumeration_count" class="text-sm font-medium">Enumeration Count:</label>
                                                        <span class="text-sm text-gray-600">{{ generateOptions.enumeration_count }}</span>
                                                    </div>
                                                    <input
                                                        type="range"
                                                        id="enumeration_count"
                                                        v-model="generateOptions.enumeration_count"
                                                        min="1"
                                                        max="15"
                                                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                                                    />
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <input
                                                        type="checkbox"
                                                        v-model="generateOptions.generate_true_false_quizzes"
                                                        id="generate_true_false_quizzes"
                                                    />
                                                    <label for="generate_true_false_quizzes" class="text-sm font-medium"
                                                        >Generate True/False Quizzes</label
                                                    >
                                                </div>
                                                <div v-if="generateOptions.generate_true_false_quizzes" class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <label for="true_false_count" class="text-sm font-medium">True/False Count:</label>
                                                        <span class="text-sm text-gray-600">{{ generateOptions.true_false_count }}</span>
                                                    </div>
                                                    <input
                                                        type="range"
                                                        id="true_false_count"
                                                        v-model="generateOptions.true_false_count"
                                                        min="1"
                                                        max="15"
                                                        class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 dark:bg-gray-700"
                                                    />
                                                </div>
                                            </div>
                                            <DialogFooter>
                                                <Button :disabled="isGenerating" @click="submitGenerateRequest">
                                                    {{ isGenerating ? 'Generating...' : 'Generate' }}
                                                </Button>
                                            </DialogFooter>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Verification Section -->
                        <div v-if="canVerify && !file.verified && !file.is_denied" class="rounded-lg border-2 border-orange-400/70 bg-gradient-to-r from-orange-600/30 to-red-600/30 p-3 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-pixel text-sm font-bold text-orange-200 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                                    🛡️ Verification Actions
                                </h4>
                                <div class="bg-red-500 text-white text-xs px-2 py-1 rounded-full font-pixel">
                                    PENDING
                                </div>
                            </div>
                            <div class="space-y-3">
                                <!-- Verify Button -->
                                <div class="space-y-1">
                                    <button
                                        @click="verifyFile"
                                        :disabled="isVerifying"
                                        class="font-pixel w-full flex items-center justify-center border-2 border-green-400 bg-green-600 px-4 py-3 text-sm text-white shadow-[3px_3px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-green-700 hover:scale-[1.02] disabled:opacity-50 disabled:hover:scale-100"
                                    >
                                        <ShieldCheckIcon v-if="!isVerifying" class="mr-2 h-5 w-5" />
                                        <Loader2Icon v-else class="mr-2 h-5 w-5 animate-spin" />
                                        {{ isVerifying ? 'Verifying...' : '✓ Approve & Verify' }}
                                    </button>
                                    <p class="text-xs text-green-200/80 text-center">
                                        Mark file as verified and allow study material generation
                                    </p>
                                </div>

                                <!-- Deny Button -->
                                <div class="space-y-1">
                                    <button
                                        @click="openDenyModal"
                                        class="font-pixel w-full flex items-center justify-center border-2 border-red-400 bg-red-600 px-4 py-3 text-sm text-white shadow-[3px_3px_0px_rgba(0,0,0,0.8)] transition-all hover:bg-red-700 hover:scale-[1.02]"
                                    >
                                        <XCircleIcon class="mr-2 h-5 w-5" />
                                        ✗ Deny File
                                    </button>
                                    <p class="text-xs text-red-200/80 text-center">
                                        Reject file and provide feedback to uploader
                                    </p>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-orange-200/80 text-center">
                                Review and moderate this file submission
                            </p>
                        </div>

                        <!-- Admin Status Display (for already processed files) -->
                        <div v-else-if="canVerify && (file.verified || file.is_denied)" class="rounded-lg border-2 border-white/20 bg-black/30 p-3 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                            <h4 class="font-pixel mb-2 text-sm font-bold text-blue-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                                🛡️ Verification Status
                            </h4>
                            <div v-if="file.verified" class="rounded-lg border-2 border-green-400 bg-green-600/20 p-3 backdrop-blur-sm">
                                <div class="flex items-center justify-center mb-1">
                                    <ShieldCheckIcon class="mr-2 h-5 w-5 text-green-400" />
                                    <span class="font-pixel text-sm text-green-300">File Verified</span>
                                </div>
                                <p class="text-xs text-green-200/80 text-center">
                                    This file has been approved and is available for study materials
                                </p>
                            </div>
                            <div v-else-if="file.is_denied" class="rounded-lg border-2 border-red-400 bg-red-600/20 p-3 backdrop-blur-sm">
                                <div class="flex items-center justify-center mb-2">
                                    <XCircleIcon class="mr-2 h-5 w-5 text-red-400" />
                                    <span class="font-pixel text-sm text-red-300">File Denied</span>
                                </div>
                                <div v-if="file.denial_reason" class="rounded bg-red-500/20 p-2">
                                    <p class="text-xs text-red-200 text-center font-medium">
                                        Reason: "{{ file.denial_reason }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: File Preview -->
                    <div class="space-y-3">
                        <!-- File Preview Section - Main Focus -->
                        <div class="rounded-lg border-2 border-white/20 bg-black/30 p-3 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                            <h3 class="font-pixel mb-3 text-lg font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                                � File Preview
                            </h3>
                            <!-- Full File Preview Content -->
                            <div class="rounded border border-white/20 p-3 backdrop-blur-sm">
                                <div v-if="fileInfo.exists && isPreviewable">
                                    <!-- PDF Preview -->
                                    <div v-if="isPdf && fileInfo.url" class="w-full border-2 border-yellow-400 rounded bg-white/5 backdrop-blur-sm" style="height: 75vh;">
                                        <object :data="fileInfo.url" type="application/pdf" class="h-full w-full rounded">
                                            <div class="flex h-full items-center justify-center text-center p-4">
                                                <div>
                                                    <FileType2Icon class="mx-auto mb-2 h-12 w-12 text-white/60" />
                                                    <p class="text-white/80 mb-2">PDF preview not available in your browser</p>
                                                    <a :href="fileInfo.url" target="_blank" class="text-yellow-400 underline hover:text-yellow-300">
                                                        Open PDF in new tab
                                                    </a>
                                                </div>
                                            </div>
                                        </object>
                                    </div>

                                    <!-- Image Preview -->
                                    <div v-else-if="isImage && fileInfo.url" class="flex justify-center">
                                        <img :src="fileInfo.url" :alt="file.name" class="max-w-full h-auto rounded border-2 border-blue-400 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]" style="max-height: 75vh;" />
                                    </div>

                                    <!-- Text Preview -->
                                    <div v-else-if="isTxt" class="overflow-auto rounded border-2 border-green-400 bg-green-600/10 p-4 backdrop-blur-sm" style="max-height: 75vh;">
                                        <pre class="text-sm whitespace-pre-wrap text-white">{{ file.content }}</pre>
                                    </div>

                                    <!-- Office File Preview -->
                                    <div v-else-if="isOfficeFile && fileInfo.url" class="w-full border-2 border-purple-400 rounded bg-purple-600/5 backdrop-blur-sm" style="height: 75vh;">
                                        <iframe
                                            :src="`https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileInfo.url)}`"
                                            width="100%"
                                            height="100%"
                                            frameborder="0"
                                            class="rounded"
                                        >
                                            This is an embedded Microsoft Office document, powered by Office Online.
                                        </iframe>
                                    </div>
                                </div>

                                <!-- Extracted Text Content -->
                                <div v-else-if="!isPreviewable && file.content" class="overflow-auto rounded border-2 border-orange-400 bg-orange-600/10 p-4 backdrop-blur-sm" style="max-height: 75vh;">
                                    <h4 class="mb-3 font-medium text-orange-300">📝 Extracted Text Content</h4>
                                    <pre class="text-sm whitespace-pre-wrap text-white">{{ file.content }}</pre>
                                </div>

                                <!-- File Not Found -->
                                <div v-else class="flex items-center justify-center rounded border-2 border-red-400 bg-red-600/10 backdrop-blur-sm p-8" style="height: 50vh;">
                                    <div class="text-center">
                                        <FileIcon class="mx-auto mb-4 h-16 w-16 text-white/60" />
                                        <p class="text-lg text-white/80 mb-2">File content not available</p>
                                        <p class="text-sm text-white/60">The file may be too large or in an unsupported format</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add to Collection Modal -->
            <Dialog v-model:open="showCollectionModal" onOpenChange="showCollectionModal = $event">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add File to Collection</DialogTitle>
                    </DialogHeader>
                    <div class="space-y-4">
                        <div v-if="!showCreateNewCollection">
                            <p class="text-muted-foreground mb-3 text-sm">Select a collection to add this file to.</p>
                            <div v-if="userCollections.length === 0" class="py-10 text-center">
                                <p class="text-muted-foreground text-sm">No collections found.</p>
                            </div>
                            <div v-else class="max-h-60 space-y-2 overflow-y-auto">
                                <div
                                    v-for="collection in userCollections"
                                    :key="collection.id"
                                    class="hover:bg-accent flex cursor-pointer items-center justify-between rounded-md border p-3 transition-colors"
                                    :class="{ 'border-primary bg-primary/5': selectedCollection === collection.id }"
                                    @click="selectedCollection = collection.id"
                                >
                                    <div>
                                        <p class="text-sm font-medium">{{ collection.name }}</p>
                                        <p class="text-muted-foreground text-xs">{{ collection.file_count }} files</p>
                                    </div>
                                    <div v-if="selectedCollection === collection.id">
                                        <CheckCircleIcon class="text-primary h-5 w-5" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="showCreateNewCollection" class="space-y-3">
                            <p class="text-muted-foreground text-sm">Create a new collection for this file.</p>
                            <Input
                                v-model="newCollectionName"
                                placeholder="Enter collection name"
                                class="w-full"
                                :disabled="isCreatingCollection"
                                @keydown.enter="createNewCollection"
                            />
                        </div>
                    </div>
                    <DialogFooter class="flex justify-between">
                        <Button @click="showCreateNewCollection = !showCreateNewCollection" variant="ghost" class="text-sm">
                            {{ showCreateNewCollection ? 'Select Existing' : 'Create New' }}
                        </Button>
                        <div class="flex gap-2">
                            <Button variant="outline" @click="showCollectionModal = false"> Cancel </Button>
                            <Button v-if="!showCreateNewCollection" @click="addToCollection" :disabled="!selectedCollection">
                                Add to Collection
                            </Button>
                            <Button v-else @click="createNewCollection" :disabled="!newCollectionName.trim() || isCreatingCollection">
                                <span v-if="isCreatingCollection">Creating...</span>
                                <span v-else>Create & Add</span>
                            </Button>
                        </div>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Deny File Modal -->
            <Dialog v-model:open="showDenyModal" onOpenChange="showDenyModal = $event">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle class="flex items-center text-red-600">
                            <XCircleIcon class="mr-2 h-5 w-5" />
                            Deny File Submission
                        </DialogTitle>
                    </DialogHeader>
                    <div class="space-y-4">
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                            <div class="flex items-start">
                                <XCircleIcon class="mr-2 h-5 w-5 text-red-500 mt-0.5" />
                                <div>
                                    <h4 class="text-sm font-medium text-red-800">File Will Be Denied</h4>
                                    <p class="text-xs text-red-600 mt-1">
                                        This action will mark the file as denied and notify the uploader. 
                                        The file will not be available for study materials generation.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Denial <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="denialReason"
                                placeholder="Please provide a clear reason for denying this file (e.g., inappropriate content, wrong format, poor quality, etc.)"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none"
                                rows="4"
                                required
                            ></textarea>
                        </div>
                    </div>
                    <DialogFooter class="gap-2">
                        <Button variant="outline" @click="showDenyModal = false" class="border-gray-300">
                            Cancel
                        </Button>
                        <Button 
                            @click="denyFile" 
                            :disabled="!denialReason.trim() || isDenying" 
                            class="bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
                        >
                            <XCircleIcon v-if="!isDenying" class="mr-2 h-4 w-4" />
                            <Loader2Icon v-else class="mr-2 h-4 w-4 animate-spin" />
                            {{ isDenying ? 'Denying File...' : 'Deny File' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
