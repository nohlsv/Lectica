<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type User } from '@/types';
import { ArrowLeftIcon, StarIcon, CheckCircleIcon, PencilIcon, DownloadIcon, BookOpen, ListChecks, FileType2Icon, FileIcon, PlusIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogTrigger } from '@/components/ui/dialog';
import axios from 'axios';

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

const formattedFileSize = computed(() => {
    const size = props.fileInfo.size ? Number(props.fileInfo.size) : 0;

    if (isNaN(size) || size <= 0) {
        return '0 B';
    }
    if (size < 1024) {
        return size + ' B';
    }
    if (size < 1024 * 1024) {
        return (size / 1024).toFixed(2) + ' KB';
    }
    return (size / (1024 * 1024)).toFixed(2) + ' MB';
});


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
        router.post(route('files.star', { file: props.file.id }), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                isStarred.value = !isStarred.value;
                toast.success(isStarred.value ? 'File starred successfully!' : 'File unstarred successfully!');
            },
            onFinish: () => {
                isStarring.value = false;
            }
        });
    } catch (error) {
        isStarring.value = false;
        toast.error('Error toggling star.');
    }
};

const verifyFile = async () => {
    if (isVerifying.value) return;

    isVerifying.value = true;

    try {
        router.patch(route('files.verify.update', { file: props.file.id }), {}, {
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
            }
        });
    } catch (error) {
        isVerifying.value = false;
        toast.error('Error verifying the file.');
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
        }
    });
};

// Fetch user's collections for adding files
const fetchUserCollections = async () => {
    try {
        const response = await fetch('/api/user/collections');
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
        const response = await axios.post('/api/collections', {
            name: newCollectionName.value.trim(),
            is_public: false
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
        await router.post(route('collections.add-file', selectedCollection.value), {
            file_id: props.file.id
        }, {
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
            }
        });
    } catch (error) {
        toast.error('Failed to add file to collection');
    }
};
</script>

<template>
    <Head :title="`File: ${file.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gradient p-6">

            <!-- First Container (Opening) -->
            <div class="flex flex-col justify-between gap-4">

                <!-- Back btn -->
                <div class="flex items-start mt-3 ml-3">
                    <Link
                        href="/files"
                        class="inline-flex items-center gap-2 px-3 py-1 text-[#fce085] bg-red-700 border-2 border-[#f68500] rounded-md shadow-md hover:bg-yellow-400
                             hover:text-red-700 duration-300 font-bold"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                        Back
                    </Link>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        @click="toggleStar"
                        class="inline-flex items-center justify-center rounded-md bg-background px-3 py-2 text-sm font-medium hover:bg-accent transition-colors border border-border"
                        :class="{'text-amber-500': isStarred, 'text-muted-foreground': !isStarred}"
                        :disabled="isStarring"
                    >
                        <StarIcon class="h-5 w-5 mr-2" :fill="isStarred ? 'currentColor' : 'none'" />
                        {{ file.star_count || 0 }}
                        {{ isStarred ? 'Starred' : 'Star' }}
                    </button>
                    <button
                        v-if="!file.verified && canVerify"
                        @click="verifyFile"
                        class="inline-flex items-center justify-center rounded-md bg-background px-3 py-2 text-sm font-medium hover:bg-accent transition-colors border border-border"
                        :disabled="isVerifying"
                    >
                        <CheckCircleIcon class="h-5 w-5 mr-2" />
                        {{ isVerifying ? 'Verifying...' : 'Verify' }}
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        v-if="file.can_edit === true"
                        :href="route('files.edit', { file: file.id })"
                        class="inline-flex items-center justify-center gap-1 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <PencilIcon class="h-4 w-4" />
                        Edit
                    </Link>
                    <a
                        :href="route('files.download', { file: file.id })"
                        download
                        class="inline-flex items-center justify-center gap-1 rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-accent"
                    >
                        <DownloadIcon class="h-4 w-4" />
                        Download
                    </a>
                    <button
                        @click="openCollectionModal"
                        class="inline-flex items-center justify-center gap-1 rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-accent"
                    >
                        <PlusIcon class="h-4 w-4" />
                        Add to Collection
                    </button>
                </div>
            </div>

            <div class="bg-[url(https://copilot.microsoft.com/th/id/BCO.ab3e539b-6d32-496d-af01-807c0c4549fc.png)] ml-3 mr-3 mb-3 border-[#f68500] border-8 rounded-md">
                <!-- Separator -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-4 md:col-span-1">

                        <!-- File Information (1st Major Container)-->
                        <div class="p-4">
                            <h2 class="text-3xl font-semibold mb-3 text-[#fb9e1b] pixel-outline align-middle">{{file.name}}</h2>
                            <!--<div v-if="file.description" class="mb-3 text-sm">
                                <p class="text-muted-foreground">{{ file.description }}</p>
                                </div>
                            -->
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-base text-[#fce085] pixel-outline">File Type:</dt>
                                    <dd class="text-right uppercase pixel-outline">{{ fileInfo.extension }}</dd>
                                </div>
                                <div
                                    class="gap-2 w-full border-t border-border pt-4 flex justify-center"
                                    v-if="isOwner && file.verified"
                                >
                                    <ArrowRightLeftIcon class="h-4 w-4 mr-2 pixel-outline-icon"/>
                                    {{ showFlashcards ? 'Switch to Quizzes' : 'Switch to Flashcards' }}
                                </button>
                                </div>

                                <!-- Toggleable Content -->
                                <div class="flex flex-col">
                                    <div class="w-full py-2">
                                        <div v-if="showFlashcards" key="flashcards">
                                            <div class="flex flex-wrap mb-2 gap-6 justify-center">
                                                <Link :href="route('files.flashcards.index', file.id)">
                                                    <Button class="w-full sm:w-auto text-xs bg-[#A67C52] text-[#fdf6ee] hover:bg-[#8c6b44] border-[#0c0a03] border-2 pixel-outline">
                                                        <BookOpen class="mr-2 h-3 w-3 pixel-outline-icon" />
                                                        View Flashcards
                                                    </Button>
                                                </Link>
                                                <Link :href="route('files.flashcards.practice', file.id)">
                                                    <Button class="w-full sm:w-auto text-xs bg-[#6B8F8C] text-[#fdf6ee] hover:bg-[#597973] border-[#0c0a03] border-2 pixel-outline">
                                                        <BookOpen class="mr-2 h-3 w-3 pixel-outline-icon" />
                                                        Practice
                                                    </Button>
                                                </Link>
                                            </div>
                                        </div>
                                        <div v-else key="quizzes">
                                            <div class="flex flex-wrap mb-2 gap-6 justify-center">
                                            <Link :href="route('files.quizzes.index', file.id)">
                                                <Button class="w-full sm:w-auto text-xs bg-[#6B8F8C] text-[#fdf6ee] hover:bg-[#7FA19E] border-[#0c0a03] border-2 pixel-outline">
                                                <ListChecks class="mr-2 h-3 w-3 pixel-outline-icon" />
                                                View Quizzes
                                                </Button>
                                            </Link>
                                            <Link :href="route('files.quizzes.test', file.id)">
                                                <Button class="w-full sm:w-auto text-xs bg-[#A67C52] text-[#fdf6ee] hover:bg-[#B88D63] border-[#0c0a03] border-2 pixel-outline">
                                                <ListChecks class="mr-2 h-3 w-3 pixel-outline-icon" />
                                                Practice
                                                </Button>
                                            </Link>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-white animate-soft-bounce pixel-outline text-sm text-center">Tip: Get your files verified to generate flashcards and quizzes automatically!</p>
                                    </div>
                                </div>

                                <!-- Generate flashcards and quiz button -->
                                <div
                                        class="flex justify-center"
                                        v-if="isOwner && file.verified"
                                    >
                                        <Dialog v-model:open="isDialogOpen" onOpenChange="isDialogOpen = $event">
                                            <DialogTrigger asChild>
                                               <Button
                                                    class="sm:w-auto flex items-center justify-center text-sm sm:text-base gap-2 rounded-lg border-2 border-[#ff6f00] bg-gradient-to-r from-[#ffb347] to-[#ffcc33] px-3 sm:px-5 py-3 sm:py-5 font-semibold text-[#fdf6ee] shadow-md transition-all duration-300 hover:scale-110 hover:from-[#e6a03c] hover:to-[#e6b82c] pixel-outline active:scale-95"
                                                        >
                                                <PencilIcon class="h-6 w-6 sm:h-8 sm:w-8 text-[#fdf6ee] pixel-outline pixel-outline-icon flex-shrink-0" />
                                                    <span class="text-center leading-tight">Generate Flashcards & Quizzes</span>
                                                </Button>

                                            </DialogTrigger>
                                            <DialogContent class="bg-[#8E2C38] text-[#fbd178] border-[#0c0a03] max-w-[95vw] sm:max-w-md">
                                                <DialogHeader>
                                                    <DialogTitle class="text-[#F5E3C8] text-xl pixel-outline">Generate Flashcards & Quizzes</DialogTitle>
                                                </DialogHeader>
                                                <div class="space-y-4">
                                                    <div class="flex items-center gap-2 ">
                                                        <input type="checkbox" v-model="generateOptions.generate_flashcards" id="generate_flashcards" />
                                                        <label for="generate_flashcards" class="text-sm font-medium">Generate Flashcards</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_flashcards" class="flex items-center gap-2 border-b mb-10 pb-2 border-[#fbd178]">
                                                        <label for="flashcards_count" class="text-sm font-medium pixel-outline text-[#F5E3C8]">Flashcards Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="flashcards_count"
                                                            v-model="generateOptions.flashcards_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.flashcards_count = Math.min(Math.max(generateOptions.flashcards_count, 1), 15)"
                                                            class="!bg-[#FFF8F2]/80 !text-[#333333] !border-2 !border-[#0c0a03]"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" v-model="generateOptions.generate_multiple_choice_quizzes" id="generate_multiple_choice_quizzes" />
                                                        <label for="generate_multiple_choice_quizzes" class="text-sm font-medium">Generate Multiple Choice Quizzes</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_multiple_choice_quizzes" class="flex items-center gap-2">
                                                        <label for="multiple_choice_count" class="text-sm font-medium pixel-outline text-[#F5E3C8]">Multiple Choice Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="multiple_choice_count"
                                                            v-model="generateOptions.multiple_choice_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.multiple_choice_count = Math.min(Math.max(generateOptions.multiple_choice_count, 1), 15)"
                                                            class="!bg-[#FFF8F2]/80 !text-[#333333] !border-2 !border-[#0c0a03]"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" v-model="generateOptions.generate_enumeration_quizzes" id="generate_enumeration_quizzes" />
                                                        <label for="generate_enumeration_quizzes" class="text-sm font-medium">Generate Enumeration Quizzes</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_enumeration_quizzes" class="flex items-center gap-2">
                                                        <label for="enumeration_count" class="text-sm font-medium pixel-outline text-[#F5E3C8]">Enumeration Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="enumeration_count"
                                                            v-model="generateOptions.enumeration_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.enumeration_count = Math.min(Math.max(generateOptions.enumeration_count, 1), 15)"
                                                            class="!bg-[#FFF8F2]/80 !text-[#333333] !border-2 !border-[#0c0a03]"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" v-model="generateOptions.generate_true_false_quizzes" id="generate_true_false_quizzes" />
                                                        <label for="generate_true_false_quizzes" class="text-sm font-medium">Generate True/False Quizzes</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_true_false_quizzes" class="flex items-center gap-2">
                                                        <label for="true_false_count" class="text-sm font-medium pixel-outline text-[#F5E3C8]">True/False Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="true_false_count"
                                                            v-model="generateOptions.true_false_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.true_false_count = Math.min(Math.max(generateOptions.true_false_count, 1), 15)"
                                                            class="!bg-[#FFF8F2]/80 !text-[#333333] !border-2 !border-[#0c0a03]"
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button :disabled="isGenerating" @click="submitGenerateRequest" class="border-[#0c0a03] bg-[#10B981] hover:bg-[#1fe1a2] border-2 text-[#fdf6ee] pixel-outline tracking-wide hover:scale-110 duration-300">
                                                        {{ isGenerating ? 'Generating...' : 'Generate' }}
                                                    </Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Preview (2nd Major Contianer) -->
                    <div class="space-y-4 md:col-span-2">
                        <div class="p-4">
                                <!-- Top buttons container-->
                                <div class="flex flex-row justify-between items-center gap-3 px-4 py-3">
                                    <h2 class="text-xl font-semibold justify-center flex text-left px-4 py-3  text-[#fce085] pixel-outline">File Preview</h2>

                                    <!-- Buttons container -->
                                    <div class="flex flex-wrap items-center gap-3 justify-center mt-3 md:mt-0">
                                        <button
                                            @click="toggleStar"
                                            class="inline-flex items-center justify-center rounded-md bg-[#c9631a] px-3 py-2 text-sm font-medium hover:bg-[#ad5215] border-2 border-[#0c0a03] duration-300 hover:scale-105 pixel-outline"
                                            :class="{'text-yellow-300': isStarred, 'bg-[#6f4f3b]': !isStarred}"
                                            :disabled="isStarring"
                                        >
                                            <StarIcon class="h-5 w-5 mr-2 pixel-outline-icon" :fill="isStarred ? 'currentColor' : 'none'" />
                                            {{ file.star_count || 0 }}
                                            {{ isStarred ? 'Starred' : 'Star' }}
                                        </button>

                                        <button
                                            v-if="!file.verified && canVerify"
                                            @click="verifyFile"
                                            class="inline-flex items-center justify-center rounded-md bg-[#5cae6e] px-3 py-2 text-sm font-medium text-[#fdf6ee] pixel-outline hover:bg-[#4a9159] border-[#0c0a03] duration-300 border-2"
                                            :disabled="isVerifying"
                                        >
                                            <CheckCircleIcon class="h-5 w-5 mr-2 pixel-outline-icon" />
                                            {{ isVerifying ? 'Verifying...' : 'Verify' }}
                                        </button>

                                        <a
                                            v-if="file.can_edit === true"
                                            :href="route('files.edit', { file: file.id })"
                                            class="inline-flex items-center justify-center gap-1 rounded-md border-2 bg-[#6aa7d6] px-4 py-2 text-sm font-medium text-[#fdf6ee] hover:bg-[#578ec3] border-[#0c0a03] duration-300 pixel-outline"
                                        >
                                            <PencilIcon class="h-4 w-4 pixel-outline-icon" />
                                            Edit
                                        </a>
                                        <a
                                            :href="route('files.download', { file: file.id })"
                                            download
                                            class="inline-flex items-center justify-center gap-1 rounded-md border-2 bg-[#d98c5f] px-4 py-2 text-sm font-medium text-[#fdf6ee] hover:bg-[#b3744e] border-[#0c0a03] duration-300 pixel-outline"
                                        >
                                            <DownloadIcon class="h-4 w-4 pixel-outline-icon" />
                                            Download
                                        </a>
                                    </div>
                                </div>

                            <div v-if="fileInfo.exists && isPreviewable" class="mt-2">
                                <!-- PDF Preview -->
                                <div v-if="isPdf && fileInfo.url" class="w-full h-[500px] border-5 border-[#feaf00] rounded-md">
                                    <object
                                        :data="fileInfo.url"
                                        type="application/pdf"
                                        class="w-full h-full"
                                    >
                                        <div class="flex items-center justify-center h-full bg-accent/20 p-4 text-center">
                                            <div>
                                                <FileType2Icon class="h-10 w-10 mx-auto mb-2 text-muted-foreground" />
                                                <p>PDF preview not available in your browser.</p>
                                                <a :href="fileInfo.url" target="_blank" class="text-primary underline mt-2 inline-block">
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
                                        class="max-w-full max-h-[500px] object-contain rounded-md"
                                    />
                                </div>

                                <!-- Text Preview -->
                                <div v-else-if="isTxt" class="max-h-[500px] overflow-auto rounded-md bg-accent/50 p-4">
                                    <pre class="text-sm whitespace-pre-wrap">{{ file.content }}</pre>
                                </div>

                                <!-- Office File Preview -->
                                <div v-else-if="isOfficeFile && fileInfo.url" class="w-full h-[500px] border border-[#0c0a03] rounded-md">
                                    <iframe
                                        :src="`https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileInfo.url)}`"
                                        width="100%"
                                        height="100%"
                                        frameborder="0"
                                    >
                                        This is an embedded
                                        <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by
                                        <a target="_blank" href="http://office.com/webapps">Office Online</a>.
                                    </iframe>
                                </div>
                            </div>

                            <!-- Extracted Text Content -->
                            <div v-if="!isPreviewable && file.content" class="mt-4">
                                <h3 class="text-md font-medium mb-2 text-[#fce085]">Extracted Text</h3>
                                <div class="max-h-[400px] overflow-auto rounded-md bg-accent/60 p-4">
                                    <pre class="text-sm whitespace-pre-wrap">{{ file.content }}</pre>
                                </div>
                            </div>

                            <!-- Text Preview -->
                            <div v-else-if="isTxt" class="max-h-[500px] overflow-auto rounded-md bg-accent/50 p-4">
                                <pre class="text-sm whitespace-pre-wrap">{{ file.content }}</pre>
                            </div>

                            <!-- Office File Preview -->
                            <div v-else-if="isOfficeFile && fileInfo.url" class="w-full h-[500px] border border-border rounded-md">
                                <iframe
                                    :src="`https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileInfo.url)}`"
                                    width="100%"
                                    height="100%"
                                    frameborder="0"
                                >
                                    This is an embedded
                                    <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by
                                    <a target="_blank" href="http://office.com/webapps">Office Online</a>.
                                </iframe>
                            </div>
                        </div>

                        <!-- Extracted Text Content -->
                        <div v-if="!isPreviewable && file.content" class="mt-4">
                            <h3 class="text-md font-medium mb-2">Extracted Text</h3>
                            <div class="max-h-[400px] overflow-auto rounded-md bg-accent/50 p-4">
                                <pre class="text-sm whitespace-pre-wrap">{{ file.content }}</pre>
                            </div>
                        </div>

                        <!-- File Not Found -->
                        <div v-if="!fileInfo.exists" class="flex items-center justify-center h-[200px] bg-accent/20 rounded-md">
                            <div class="text-center">
                                <FileIcon class="h-10 w-10 mx-auto mb-2 text-muted-foreground" />
                                <p>File content not available.</p>
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
                            <p class="text-sm text-muted-foreground mb-3">
                                Select a collection to add this file to.
                            </p>
                            <div v-if="userCollections.length === 0" class="text-center py-10">
                                <p class="text-sm text-muted-foreground">No collections found.</p>
                            </div>
                            <div v-else class="space-y-2 max-h-60 overflow-y-auto">
                                <div
                                    v-for="collection in userCollections"
                                    :key="collection.id"
                                    class="flex items-center justify-between p-3 rounded-md border cursor-pointer hover:bg-accent transition-colors"
                                    :class="{'border-primary bg-primary/5': selectedCollection === collection.id}"
                                    @click="selectedCollection = collection.id"
                                >
                                    <div>
                                        <p class="text-sm font-medium">{{ collection.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ collection.file_count }} files</p>
                                    </div>
                                    <div v-if="selectedCollection === collection.id">
                                        <CheckCircleIcon class="h-5 w-5 text-primary" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="showCreateNewCollection" class="space-y-3">
                            <p class="text-sm text-muted-foreground">Create a new collection for this file.</p>
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
                        <Button
                            @click="showCreateNewCollection = !showCreateNewCollection"
                            variant="ghost"
                            class="text-sm"
                        >
                            {{ showCreateNewCollection ? 'Select Existing' : 'Create New' }}
                        </Button>
                        <div class="flex gap-2">
                            <Button variant="outline" @click="showCollectionModal = false">
                                Cancel
                            </Button>
                            <Button
                                v-if="!showCreateNewCollection"
                                @click="addToCollection"
                                :disabled="!selectedCollection"
                            >
                                Add to Collection
                            </Button>
                            <Button
                                v-else
                                @click="createNewCollection"
                                :disabled="!newCollectionName.trim() || isCreatingCollection"
                            >
                                <span v-if="isCreatingCollection">Creating...</span>
                                <span v-else>Create & Add</span>
                            </Button>
                        </div>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
