<script setup lang="ts">
import CollectionModal from '@/components/CollectionModal.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeftIcon,
    DownloadIcon,
    FileIcon,
    FileType2Icon,
    Loader2Icon,
    PencilIcon,
    PlusIcon,
    ShieldCheckIcon,
    StarIcon,
    XCircleIcon,
} from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { toast } from 'vue-sonner';

interface LocalFile {
    id: number;
    name: string;
    can_edit: boolean;
    is_starred: boolean;
    verified: boolean;
    is_denied: boolean;
    denial_reason: string | null;
    created_at: string;
    updated_at: string;
    content: string | null;
    generation_status: 'pending' | 'processing' | 'completed' | 'completed_with_errors' | 'failed' | null;
    user: {
        id: number;
        first_name: string;
        last_name: string;
    };
    tags: {
        id: number;
        name: string;
    }[];
}

interface Props {
    file: LocalFile;
    fileInfo: {
        extension: string;
        exists: boolean;
        size: string | null;
        url: string | null;
        lastModified: string | null;
    };
    collections: Collection[];
}

interface Collection {
    id: number;
    name: string;
    description: string | null;
    is_public: boolean;
    user_id: number;
    user: {
        id: number;
        last_name: string;
        first_name: string;
    };
    file_count: number;
    created_at: string;
}

interface UserCollection {
    id: number;
    name: string;
    files_count: number;
    is_public: boolean;
    contains_file: boolean;
}

interface Auth {
    user: User;
}

const props = defineProps<Props>();
const page = usePage();
const auth = computed<Auth>(() => page.props.auth as Auth);

// Set up Echo listener for content generation completion
onMounted(() => {
    if (window.Echo) {
        const channel = window.Echo.private(`App.Models.User.${auth.value.user.id}`);
        channel.notification((notification: any) => {
            if (notification.type === 'App\\Notifications\\ContentGenerationComplete' && 
                notification.file_id === props.file.id) {
                // The file data will be updated automatically since we're reloading the page
                if (notification.errors && Object.keys(notification.errors).length > 0) {
                    toast.error('Some content failed to generate. Check notifications for details.');
                } else {
                    toast.success('Study materials generated successfully!');
                }
                // Refresh the page to show new content and updated status
                router.get(route('files.show', props.file.id), {}, {
                    preserveScroll: true,
                    preserveState: true
                });
            }
        });
    }
});

const isStarred = ref(props.file.is_starred || false);
const isStarring = ref(false);
const isVerifying = ref(false);
const showCollectionModal = ref(false);
const showDenyModal = ref(false);

// Note: Echo listener is set up in onMounted
const denialReason = ref('');
const isDenying = ref(false);
const userCollections = ref<UserCollection[]>([]);
const selectedCollection = ref<number | null>(null);
const showCreateNewCollection = ref(false);
const newCollectionName = ref('');
const isCreatingCollection = ref(false);

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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

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

const isGenerating = computed(() => props.file.generation_status === 'pending' || props.file.generation_status === 'processing');
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

const canVerify = computed(() => ['faculty', 'admin'].includes(auth.value.user.user_role));

const noGenerationOptionsSelected = computed(() => {
    return !generateOptions.value.generate_flashcards &&
        !generateOptions.value.generate_multiple_choice_quizzes &&
        !generateOptions.value.generate_enumeration_quizzes &&
        !generateOptions.value.generate_true_false_quizzes;
});

const submitGenerateRequest = async () => {
    if (props.file.generation_status === 'pending' || props.file.generation_status === 'processing') return;

    router.post(route('files.generate-flashcards-quizzes', { file: props.file.id }), generateOptions.value, {
        preserveScroll: true,
        onSuccess: () => {
            isDialogOpen.value = false; // Close the dialog
            toast.success('Flashcards and quizzes generation started!');
        },
        onError: () => {
            toast.error('Failed to generate flashcards and quizzes.');
        }
    });
};

// Fetch user's collections for adding files
const fetchUserCollections = async () => {
    try {
        const url = `/user/collections?file_id=${props.file.id}`;
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        userCollections.value = data;
        console.log('Fetched collections:', data); // Debug log
    } catch (error) {
        console.error('Failed to fetch collections:', error);
        toast.error('Failed to load your collections');
        userCollections.value = [];
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

    // Check if file is already in the selected collection
    const selectedCollectionData = userCollections.value.find((c) => c.id === selectedCollection.value);
    if (selectedCollectionData?.contains_file) {
        toast.error('File is already in this collection');
        return;
    }

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
                    // Refresh the collections data to reflect the new state
                    fetchUserCollections();
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

// Collection modal handlers
const closeCollectionModal = () => {
    showCollectionModal.value = false;
};

const onCollectionSuccess = (message?: string) => {
    toast.success(message || 'Collections updated successfully!');
    closeCollectionModal();
};
</script>

<template>
    <Head :title="`File: ${file.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="dark:bg-[#161615]">
            <!-- Compact Header with Actions -->
            <div class="bg-lectica flex w-full flex-col px-2 pt-2 pb-2 sm:px-4">
                <div class="flex flex-col gap-3 rounded-xl p-2 sm:flex-row sm:items-center sm:justify-between sm:gap-2">
                    <!--File Info-->
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded border-2 border-white bg-gradient-to-br from-blue-500 to-purple-600 sm:h-10 sm:w-10"
                        >
                            <FileIcon class="h-4 w-4 text-white sm:h-5 sm:w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="font-pixel text-xs text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-sm md:text-base truncate">
                                {{ file.name }}
                            </h1>
                            <div class="flex items-center space-x-1 text-xs text-white/80 sm:text-sm">
                                <span>{{
                                    fileInfo.size ? Math.round((Number(fileInfo.size) / 1024 / 1024) * 100) / 100 + ' MB' : 'Unknown size'
                                }}</span>
                                <span class="hidden sm:inline">•</span>
                                <span class="hidden sm:inline">{{ new Date(file.created_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>

                    <!--Actions-->
                    <div class="flex items-center space-x-1 flex-wrap gap-1 sm:flex-nowrap sm:space-x-2">
                        <!-- Download -->
                        <a
                            :href="route('files.download', { file: file.id })"
                            download
                            class="flex h-10 items-center justify-center rounded border border-blue-400/70 bg-blue-400/20 px-3 transition-all hover:bg-blue-400/30 sm:h-7 sm:px-2"
                            title="Download"
                        >
                            <DownloadIcon class="h-4 w-4 text-blue-300 sm:h-3 sm:w-3 sm:mr-1" />
                            <span class="font-pixel text-blue-300 hidden sm:inline text-xs">Download</span>
                        </a>

                        <!-- Add to Collection -->
                        <button
                            @click="openCollectionModal"
                            class="pixel-outline flex h-10 items-center justify-center rounded border border-[#ffd700]/70 bg-[#a85a47]/20 px-3 transition-all hover:bg-[#a85a47]/30 sm:h-7 sm:px-2"
                            title="Add to Collection"
                        >
                            <PlusIcon class="h-4 w-4 text-[#ffd700] sm:h-3 sm:w-3 sm:mr-1" />
                            <span class="font-pixel text-[#ffd700] hidden sm:inline text-xs">Add to Collection</span>
                        </button>

                        <!-- Star -->
                        <button
                            @click="toggleStar"
                            class="flex h-10 items-center justify-center rounded border border-yellow-400/70 bg-yellow-400/20 px-3 transition-all hover:bg-yellow-400/30 sm:h-7 sm:px-2"
                            title="Star File"
                        >
                            <StarIcon
                                :class="[
                                    'h-4 w-4 transition-colors sm:h-3 sm:w-3 sm:mr-1',
                                    file.is_starred ? 'fill-yellow-300 text-yellow-300' : 'text-white/60 hover:text-yellow-300',
                                ]"
                            />
                            <span class="font-pixel hidden sm:inline text-xs" :class="file.is_starred ? 'text-yellow-300' : 'text-white/60'">
                                {{ file.is_starred ? 'Starred' : 'Star' }}
                            </span>
                        </button>

                        <!-- Back -->
                        <Link href="/files">
                            <Button class="font-pixel border border-white bg-red-600 px-3 py-2 text-white transition-all hover:bg-red-900 sm:px-2 sm:py-1">
                                <ArrowLeftIcon class="h-4 w-4 sm:h-3 sm:w-3 sm:mr-1" />
                                <span class="hidden sm:inline text-xs">Back</span>
                            </Button>
                        </Link>
                    </div>
                </div>
                <!--Divider-->
                <hr class="-mx-4 h-0.5 border border-black bg-blue-500" />
            </div>

            <!--Main Content - Two Column Layout-->
            <div class="bg-gradient flex h-full flex-1 flex-col px-2 py-3 sm:px-4 lg:px-6">
                <!-- File Info and Preview Grid -->
                <div class="grid grid-cols-1 gap-2 sm:gap-4 lg:grid-cols-[1fr_2fr]">
                    <!-- Left Column: File Info & Actions -->
                    <div class="space-y-3">
                        <!-- File Details Card -->
                        <div class="rounded border border-white/20 bg-black/30 p-2 backdrop-blur-sm">
                            <div class="mb-2 flex items-center justify-between">
                                <h4 class="font-pixel font-bold text-yellow-400">📄 Info</h4>
                                <!-- Edit Button -->
                                <Link v-if="isOwner" :href="route('files.edit', file.id)">
                                    <button
                                        class="flex items-center justify-center rounded border border-orange-400/70 bg-orange-400/20 px-2 py-1 text-xs transition-all hover:bg-orange-400/30"
                                        title="Edit File"
                                    >
                                        <PencilIcon class="mr-1 h-3 w-3 text-orange-300" />
                                        <span class="font-pixel text-orange-300">Edit</span>
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
                                    <span class="font-bold text-white">{{ (Number(fileInfo.size) / 1024 / 1024).toFixed(2) }} MB</span>
                                </div>
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Uploaded By:</span>
                                    <span class="font-bold text-white">{{ file.user.last_name }}, {{ file.user.first_name }}</span>
                                </div>
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Uploaded Date:</span>
                                    <span class="font-bold text-white">{{
                                        new Date(file.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' })
                                    }}</span>
                                </div>
                                <div class="flex justify-between rounded bg-white/5 p-1.5">
                                    <span class="text-yellow-400">Last Modified Date:</span>
                                    <span class="font-bold text-white">{{
                                        new Date(file.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: '2-digit' })
                                    }}</span>
                                </div>
                                <div v-if="file.tags && file.tags.length" class="rounded bg-white/5 p-1.5">
                                    <div class="mb-1 text-yellow-400">Tags:</div>
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="tag in file.tags"
                                            :key="tag.id"
                                            class="font-pixel inline-flex rounded border border-purple-400 bg-purple-600 px-1 py-0.5 text-white"
                                        >
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Study Materials -->
                        <div class="rounded-lg border-2 border-white/20 bg-black/30 p-4 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                            <h4
                                class="font-pixel mb-4 text-lg font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                            >
                                📚 Study Materials
                            </h4>
                            <div class="flex flex-col items-center space-y-4">
                                <!-- Flashcards -->
                                <div class="w-full rounded-lg border border-blue-400/30 bg-blue-600/10 p-3 backdrop-blur-sm">
                                    <h5
                                        class="font-pixel mb-3 text-center font-bold text-blue-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]"
                                    >
                                        📖 Flashcards
                                    </h5>
                                    <div class="flex gap-2">
                                        <Link :href="route('files.flashcards.index', file.id)" class="flex-1">
                                            <button
                                                class="font-pixel w-full border-2 border-blue-400 bg-blue-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-blue-700"
                                            >
                                                View
                                            </button>
                                        </Link>
                                        <Link :href="route('files.flashcards.practice', file.id)" class="flex-1">
                                            <button
                                                class="font-pixel w-full border-2 border-green-400 bg-green-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-green-700"
                                            >
                                                Practice
                                            </button>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Quizzes -->
                                <div class="w-full rounded-lg border border-purple-400/30 bg-purple-600/10 p-3 backdrop-blur-sm">
                                    <h5
                                        class="font-pixel mb-3 text-center font-bold text-purple-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]"
                                    >
                                        🧠 Quizzes
                                    </h5>
                                    <div class="flex gap-2">
                                        <Link :href="route('files.quizzes.index', file.id)" class="flex-1">
                                            <button
                                                class="font-pixel w-full border-2 border-purple-400 bg-purple-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-purple-700"
                                            >
                                                View
                                            </button>
                                        </Link>
                                        <Link :href="route('files.quizzes.test', file.id)" class="flex-1">
                                            <button
                                                class="font-pixel w-full border-2 border-orange-400 bg-orange-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-orange-700"
                                            >
                                                Practice
                                            </button>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Battle Mode -->
                                <div v-if="file.verified" class="w-full rounded-lg border border-red-400/30 bg-red-600/10 p-3 backdrop-blur-sm">
                                    <h5
                                        class="font-pixel mb-3 text-center font-bold text-red-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]"
                                    >
                                        ⚔️ Battle Mode
                                    </h5>
                                    <div class="flex gap-2">
                                        <Link :href="route('battles.create', { file_id: file.id })" class="flex-1">
                                            <button
                                                class="font-pixel w-full border-2 border-red-400 bg-red-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-red-700"
                                            >
                                                Solo Battle
                                            </button>
                                        </Link>
                                        <Link :href="route('multiplayer-games.lobby', { file_id: file.id })" class="flex-1">
                                            <button
                                                class="font-pixel w-full border-2 border-purple-400 bg-purple-600 px-4 py-2 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-purple-700"
                                            >
                                                Multiplayer
                                            </button>
                                        </Link>
                                    </div>
                                </div>

                                <!-- Generate Content -->
                                <div
                                    v-if="isOwner && file.verified"
                                    class="w-full rounded-lg border border-yellow-400/30 bg-yellow-600/10 p-3 backdrop-blur-sm"
                                >
                                    <h5
                                        class="font-pixel mb-3 text-center font-bold text-yellow-300 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]"
                                    >
                                        🎲 Generate Content
                                    </h5>
                                    <div v-if="isGenerating" class="text-center py-4">
                                        <span class="font-pixel text-yellow-300 animate-pulse">Generating study materials...</span>
                                        <p class="mt-2 text-xs text-yellow-300/70">You will be notified when generation is complete.</p>
                                    </div>
                                    <Dialog v-if="!isGenerating" v-model:open="isDialogOpen" onOpenChange="isDialogOpen = $event">
                                        <DialogTrigger asChild>
                                            <button
                                                class="font-pixel w-full border-2 border-yellow-400 bg-yellow-600 px-4 py-2 text-black shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-yellow-500 disabled:opacity-50 disabled:hover:scale-100 disabled:cursor-not-allowed"
                                                :disabled="isGenerating"
                                            >
                                                <PencilIcon class="mr-1 inline h-3 w-3" />
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
                                                <Button :disabled="isGenerating || noGenerationOptionsSelected" @click="submitGenerateRequest">
                                                    {{ isGenerating ? 'Generating...' : 'Generate' }}
                                                </Button>
                                            </DialogFooter>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Verification Section -->
                        <div
                            v-if="canVerify && !file.verified && !file.is_denied"
                            class="rounded-lg border-2 border-orange-400/70 bg-gradient-to-r from-orange-600/30 to-red-600/30 p-3 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <h4
                                    class="font-pixel text-sm font-bold text-orange-200 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                                >
                                    🛡️ Verification Actions
                                </h4>
                                <div class="font-pixel rounded-full bg-red-500 px-2 py-1 text-xs text-white">PENDING</div>
                            </div>
                            <div class="space-y-3">
                                <!-- Verify Button -->
                                <div class="space-y-1">
                                    <button
                                        @click="verifyFile"
                                        :disabled="isVerifying"
                                        class="font-pixel flex w-full items-center justify-center border-2 border-green-400 bg-green-600 px-4 py-3 text-sm text-white shadow-[3px_3px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-green-700 disabled:opacity-50 disabled:hover:scale-100"
                                    >
                                        <ShieldCheckIcon v-if="!isVerifying" class="mr-2 h-5 w-5" />
                                        <Loader2Icon v-else class="mr-2 h-5 w-5 animate-spin" />
                                        {{ isVerifying ? 'Verifying...' : '✓ Approve & Verify' }}
                                    </button>
                                    <p class="text-center text-xs text-green-200/80">Mark file as verified and allow study material generation</p>
                                </div>

                                <!-- Deny Button -->
                                <div class="space-y-1">
                                    <button
                                        @click="openDenyModal"
                                        class="font-pixel flex w-full items-center justify-center border-2 border-red-400 bg-red-600 px-4 py-3 text-sm text-white shadow-[3px_3px_0px_rgba(0,0,0,0.8)] transition-all hover:scale-[1.02] hover:bg-red-700"
                                    >
                                        <XCircleIcon class="mr-2 h-5 w-5" />
                                        ✗ Deny File
                                    </button>
                                    <p class="text-center text-xs text-red-200/80">Reject file and provide feedback to uploader</p>
                                </div>
                            </div>
                            <p class="mt-2 text-center text-xs text-orange-200/80">Review and moderate this file submission</p>
                        </div>

                        <!-- Admin Status Display (for already processed files) -->
                        <div
                            v-else-if="canVerify && (file.verified || file.is_denied)"
                            class="rounded-lg border-2 border-white/20 bg-black/30 p-3 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm"
                        >
                            <h4
                                class="font-pixel mb-2 text-sm font-bold text-blue-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                            >
                                🛡️ Verification Status
                            </h4>
                            <div v-if="file.verified" class="rounded-lg border-2 border-green-400 bg-green-600/20 p-3 backdrop-blur-sm">
                                <div class="mb-1 flex items-center justify-center">
                                    <ShieldCheckIcon class="mr-2 h-5 w-5 text-green-400" />
                                    <span class="font-pixel text-sm text-green-300">File Verified</span>
                                </div>
                                <p class="text-center text-xs text-green-200/80">This file has been approved and is available for study materials</p>
                            </div>
                            <div v-else-if="file.is_denied" class="rounded-lg border-2 border-red-400 bg-red-600/20 p-3 backdrop-blur-sm">
                                <div class="mb-2 flex items-center justify-center">
                                    <XCircleIcon class="mr-2 h-5 w-5 text-red-400" />
                                    <span class="font-pixel text-sm text-red-300">File Denied</span>
                                </div>
                                <div v-if="file.denial_reason" class="rounded bg-red-500/20 p-2">
                                    <p class="text-center text-xs font-medium text-red-200">Reason: "{{ file.denial_reason }}"</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: File Preview -->
                    <div class="space-y-3">
                        <!-- File Preview Section - Main Focus -->
                        <div class="rounded-lg border-2 border-white/20 bg-black/30 p-3 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                            <h3
                                class="font-pixel mb-3 text-lg font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                            >
                                � File Preview
                            </h3>
                            <!-- Full File Preview Content -->
                            <div class="rounded border border-white/20 p-3 backdrop-blur-sm">
                                <div v-if="fileInfo.exists && isPreviewable">
                                    <!-- PDF Preview -->
                                    <div
                                        v-if="isPdf && fileInfo.url"
                                        class="w-full rounded border-2 border-yellow-400 bg-white/5 backdrop-blur-sm"
                                        style="height: 75vh"
                                    >
                                        <object :data="fileInfo.url" type="application/pdf" class="h-full w-full rounded">
                                            <div class="flex h-full items-center justify-center p-4 text-center">
                                                <div>
                                                    <FileType2Icon class="mx-auto mb-2 h-12 w-12 text-white/60" />
                                                    <p class="mb-2 text-white/80">PDF preview not available in your browser</p>
                                                    <a :href="fileInfo.url" target="_blank" class="text-yellow-400 underline hover:text-yellow-300">
                                                        Open PDF in new tab
                                                    </a>
                                                </div>
                                            </div>
                                        </object>
                                    </div>

                                    <!-- Image Preview -->
                                    <div v-else-if="isImage && fileInfo.url" class="flex justify-center">
                                        <img
                                            :src="fileInfo.url"
                                            :alt="file.name"
                                            class="h-auto max-w-full rounded border-2 border-blue-400 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                                            style="max-height: 75vh"
                                        />
                                    </div>

                                    <!-- Text Preview -->
                                    <div
                                        v-else-if="isTxt"
                                        class="overflow-auto rounded border-2 border-green-400 bg-green-600/10 p-4 backdrop-blur-sm"
                                        style="max-height: 75vh"
                                    >
                                        <pre class="text-sm whitespace-pre-wrap text-white">{{ file.content }}</pre>
                                    </div>

                                    <!-- Office File Preview -->
                                    <div
                                        v-else-if="isOfficeFile && fileInfo.url"
                                        class="w-full rounded border-2 border-purple-400 bg-purple-600/5 backdrop-blur-sm"
                                        style="height: 75vh"
                                    >
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
                                <div
                                    v-else-if="!isPreviewable && file.content"
                                    class="overflow-auto rounded border-2 border-orange-400 bg-orange-600/10 p-4 backdrop-blur-sm"
                                    style="max-height: 75vh"
                                >
                                    <h4 class="mb-3 font-medium text-orange-300">📝 Extracted Text Content</h4>
                                    <pre class="text-sm whitespace-pre-wrap text-white">{{ file.content }}</pre>
                                </div>

                                <!-- File Not Found -->
                                <div
                                    v-else
                                    class="flex items-center justify-center rounded border-2 border-red-400 bg-red-600/10 p-8 backdrop-blur-sm"
                                    style="height: 50vh"
                                >
                                    <div class="text-center">
                                        <FileIcon class="mx-auto mb-4 h-16 w-16 text-white/60" />
                                        <p class="mb-2 text-lg text-white/80">File content not available</p>
                                        <p class="text-sm text-white/60">The file may be too large or in an unsupported format</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collections Section -->
                <div class="mx-auto my-4 max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                    <div class="rounded-lg border-2 border-white/20 bg-black/30 p-6 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm">
                        <div class="mb-6 text-center">
                            <h2
                                class="font-pixel my-2 mb-2 text-2xl font-bold text-yellow-400 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                            >
                                📚 File Collections
                            </h2>
                            <p class="font-pixel text-sm text-white/70">Collections containing this file</p>
                            <div class="mx-auto mt-2 h-1 w-16 rounded-full bg-gradient-to-r from-yellow-500 to-orange-500"></div>
                            
                            <!-- Add to Collections Button -->
                            <div class="mt-4">
                                <button
                                    @click="openCollectionModal"
                                    class="pixel-outline font-pixel inline-flex h-10 items-center justify-center gap-2 rounded border-2 border-[#ffd700]/70 bg-[#a85a47]/20 px-4 py-2 text-sm text-[#ffd700] transition-all hover:bg-[#ffd700]/10 hover:border-[#ffd700] sm:h-8 sm:px-3"
                                    title="Add this file to a collection"
                                >
                                    <PlusIcon class="h-4 w-4 sm:h-3 sm:w-3" />
                                    Add to Collection
                                </button>
                            </div>
                        </div>

                        <div v-if="props.collections && props.collections.length > 0" class="space-y-4">
                            <!-- Collections Grid -->
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="collection in props.collections"
                                    :key="collection.id"
                                    class="group relative overflow-hidden rounded-lg border-2 border-white/20 bg-gradient-to-br from-purple-900/40 to-blue-900/40 p-4 shadow-[3px_3px_0px_rgba(0,0,0,0.8)] backdrop-blur-sm transition-all hover:scale-[1.02] hover:border-yellow-400/50 hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                                >
                                    <!-- Collection Header -->
                                    <div class="mb-3 flex items-start justify-between">
                                        <div class="min-w-0 flex-1">
                                            <h3
                                                class="font-pixel truncate text-sm font-bold text-white [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]"
                                            >
                                                {{ collection.name }}
                                            </h3>
                                            <p class="font-pixel mt-1 text-xs text-white/60">
                                                by {{ collection.user.first_name }} {{ collection.user.last_name }}
                                            </p>
                                        </div>
                                        <div class="mx-2 flex flex-col items-end gap-1">
                                            <!-- Visibility Badge -->
                                            <span
                                                v-if="collection.is_public"
                                                class="inline-flex items-center rounded-full border border-green-400 bg-green-600/20 px-2 py-1 text-xs font-medium text-green-300"
                                            >
                                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>
                                                Public
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center rounded-full border border-gray-400 bg-gray-600/20 px-2 py-1 text-xs font-medium text-gray-300"
                                            >
                                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                                    />
                                                </svg>
                                                Private
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Collection Description -->
                                    <div v-if="collection.description" class="mb-3">
                                        <p class="font-pixel line-clamp-2 text-xs text-white/80">
                                            {{ collection.description }}
                                        </p>
                                    </div>

                                    <!-- Collection Stats -->
                                    <div class="mb-3 flex items-center gap-4">
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                            <span class="font-pixel text-xs text-blue-300">{{ collection.file_count }} files</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <svg class="h-3 w-3 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                            <span class="font-pixel text-xs text-purple-300">{{ formatDate(collection.created_at) }}</span>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="flex justify-end">
                                        <Link
                                            :href="route('collections.show', collection.id)"
                                            class="font-pixel inline-flex items-center gap-1 rounded border-2 border-yellow-400 bg-yellow-600/20 px-3 py-1 text-xs text-yellow-300 transition-all hover:bg-yellow-600/40 hover:text-yellow-200"
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            View Collection
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-8 text-center">
                            <div
                                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white/5 backdrop-blur-sm"
                            >
                                <svg class="h-8 w-8 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                    />
                                </svg>
                            </div>
                            <h3
                                class="font-pixel mb-2 text-lg font-bold text-white/60 [text-shadow:1px_0_black,-1px_0_black,0_1px_black,0_-1px_black]"
                            >
                                No Collections Found
                            </h3>
                            <p class="font-pixel mb-4 text-sm text-white/40">
                                This file is not part of any public collections or collections you own.
                            </p>
                            <Link
                                :href="route('collections.create')"
                                class="font-pixel inline-flex items-center gap-2 rounded border-2 border-blue-400 bg-blue-600/20 px-4 py-2 text-sm text-blue-300 transition-all hover:bg-blue-600/40 hover:text-blue-200"
                            >
                                <PlusIcon class="h-4 w-4" />
                                Create New Collection
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collection Modal -->
            <CollectionModal :show="showCollectionModal" :file-id="file.id" @close="closeCollectionModal" @success="onCollectionSuccess" />

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
                                <XCircleIcon class="mt-0.5 mr-2 h-5 w-5 text-red-500" />
                                <div>
                                    <h4 class="text-sm font-medium text-red-800">File Will Be Denied</h4>
                                    <p class="mt-1 text-xs text-red-600">
                                        This action will mark the file as denied and notify the uploader. The file will not be available for study
                                        materials generation.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">
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
                        <Button variant="outline" @click="showDenyModal = false" class="border-gray-300"> Cancel </Button>
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
