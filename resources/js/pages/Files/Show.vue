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
    BookOpen,
    CheckCircleIcon,
    DownloadIcon,
    FileIcon,
    FileType2Icon,
    ListChecks,
    PencilIcon,
    PlusIcon,
    StarIcon,
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
        <div class="flex flex-col gap-6 p-6">
            <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
                <div class="flex items-center gap-4">
                    <Link href="/files" class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1">
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Files
                    </Link>
                    <h1 class="text-xl font-bold md:text-2xl">File Details</h1>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button
                        @click="toggleStar"
                        class="bg-background hover:bg-accent border-border inline-flex items-center justify-center rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                        :class="{ 'text-amber-500': isStarred, 'text-muted-foreground': !isStarred }"
                        :disabled="isStarring"
                    >
                        <StarIcon class="mr-2 h-5 w-5" :fill="isStarred ? 'currentColor' : 'none'" />
                        {{ file.star_count || 0 }}
                        {{ isStarred ? 'Starred' : 'Star' }}
                    </button>
                    <button
                        v-if="!file.verified && canVerify"
                        @click="verifyFile"
                        class="bg-background hover:bg-accent border-border inline-flex items-center justify-center rounded-md border px-3 py-2 text-sm font-medium transition-colors"
                        :disabled="isVerifying"
                    >
                        <CheckCircleIcon class="mr-2 h-5 w-5" />
                        {{ isVerifying ? 'Verifying...' : 'Verify' }}
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        v-if="file.can_edit === true"
                        :href="route('files.edit', { file: file.id })"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center gap-1 rounded-md px-4 py-2 text-sm font-medium"
                    >
                        <PencilIcon class="h-4 w-4" />
                        Edit
                    </Link>
                    <a
                        :href="route('files.download', { file: file.id })"
                        download
                        class="border-border bg-background text-foreground hover:bg-accent inline-flex items-center justify-center gap-1 rounded-md border px-4 py-2 text-sm font-medium"
                    >
                        <DownloadIcon class="h-4 w-4" />
                        Download
                    </a>
                    <button
                        @click="openCollectionModal"
                        class="border-border bg-background text-foreground hover:bg-accent inline-flex items-center justify-center gap-1 rounded-md border px-4 py-2 text-sm font-medium"
                    >
                        <PlusIcon class="h-4 w-4" />
                        Add to Collection
                    </button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- File Information -->
                <div class="space-y-4 md:col-span-1">
                    <div class="border-border rounded-lg border p-4">
                        <h2 class="mb-3 text-lg font-semibold">{{ file.name }}</h2>
                        <div v-if="file.description" class="mb-3 text-sm">
                            <p class="text-muted-foreground">{{ file.description }}</p>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground font-medium">Type:</dt>
                                <dd class="text-right uppercase">{{ fileInfo.extension }}</dd>
                            </div>
                            <div class="flex justify-between" v-if="fileInfo.size">
                                <dt class="text-muted-foreground font-medium">Size:</dt>
                                <dd class="text-right">{{ fileInfo.size }}</dd>
                            </div>
                            <div class="flex justify-between" v-if="fileInfo.lastModified">
                                <dt class="text-muted-foreground font-medium">Last Modified:</dt>
                                <dd class="text-right">{{ fileInfo.lastModified }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground font-medium">Verified:</dt>
                                <dd class="text-right">
                                    <span :class="file.verified ? 'text-green-500' : 'text-red-500'" class="font-semibold">
                                        {{ file.verified ? 'Yes' : 'No' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="border-border mt-2 flex justify-between border-t pt-2">
                                <dt class="text-muted-foreground font-medium">Uploaded by:</dt>
                                <dd class="text-right">{{ file.user.last_name }}, {{ file.user.first_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground font-medium">Upload Date:</dt>
                                <dd class="text-right">
                                    {{ new Date(file.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </dd>
                            </div>
                            <div class="border-border mt-2 border-t pt-2">
                                <dt class="text-muted-foreground mb-2 font-medium">Tags:</dt>
                                <dd class="flex flex-wrap gap-1">
                                    <span
                                        v-for="tag in file.tags"
                                        :key="tag.id"
                                        class="bg-primary/10 text-primary inline-flex rounded-md px-2 py-1 text-xs"
                                    >
                                        {{ tag.name }}
                                    </span>
                                    <span v-if="!file.tags || file.tags.length === 0" class="text-muted-foreground text-sm"> No tags </span>
                                </dd>
                            </div>
                        </dl>
                        <div class="border-border mt-4 space-y-2 border-t py-4">
                            <h3 class="text-lg font-medium">Study Materials</h3>
                            <div class="mt-2">
                                <div class="w-full py-2">
                                    <h4 class="mb-2 font-medium">Flashcards</h4>
                                    <div class="mb-2 flex flex-wrap justify-center gap-6">
                                        <Link :href="route('files.flashcards.index', file.id)">
                                            <Button variant="outline" class="w-full text-xs sm:w-auto">
                                                <BookOpen class="mr-2 h-3 w-3" />
                                                View Flashcards
                                            </Button>
                                        </Link>
                                        <!-- <Link v-if="isOwner" :href="route('files.flashcards.create', file.id)">
                                            <Button variant="outline" class="w-full sm:w-auto text-xs">
                                                <Pencil class="mr-2 h-3 w-3" />
                                                Add Flashcard
                                            </Button>
                                        </Link> -->
                                        <Link :href="route('files.flashcards.practice', file.id)">
                                            <Button variant="default" class="w-full text-xs sm:w-auto">
                                                <BookOpen class="mr-2 h-3 w-3" />
                                                Practice
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                                <div class="border-border w-full border-t py-2">
                                    <h4 class="mb-2 font-medium">Quizzes</h4>
                                    <div class="mb-2 flex flex-wrap justify-center gap-6">
                                        <Link :href="route('files.quizzes.index', file.id)">
                                            <Button variant="outline" class="w-full text-xs sm:w-auto">
                                                <ListChecks class="mr-2 h-3 w-3" />
                                                View Quizzes
                                            </Button>
                                        </Link>
                                        <!-- <Link v-if="isOwner" :href="route('files.quizzes.create', file.id)">
                                            <Button variant="outline" class="w-full sm:w-auto text-xs">
                                                <Pencil class="mr-2 h-3 w-3" />
                                                Add Quiz
                                            </Button>
                                        </Link> -->
                                        <Link :href="route('files.quizzes.test', file.id)">
                                            <Button variant="default" class="w-full text-xs sm:w-auto">
                                                <ListChecks class="mr-2 h-3 w-3" />
                                                Take Quiz
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                                <div class="border-border flex w-full justify-center gap-2 border-t pt-4" v-if="isOwner && file.verified">
                                    <Dialog v-model:open="isDialogOpen" onOpenChange="isDialogOpen = $event">
                                        <DialogTrigger asChild>
                                            <Button
                                                class="bg-secondary text-secondary-foreground hover:bg-secondary/90 flex w-full items-center justify-center gap-1 rounded-md px-4 py-2 text-xs font-medium sm:w-auto"
                                            >
                                                <PencilIcon class="mr-2 h-3 w-3" />
                                                Generate Flashcards & Quizzes
                                            </Button>
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
                                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
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
                                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
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
                                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
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
                                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
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
                    </div>
                </div>

                <!-- File Preview -->
                <div class="space-y-4 md:col-span-2">
                    <div class="border-border rounded-lg border p-4">
                        <h2 class="mb-3 text-lg font-semibold">File Preview</h2>

                        <div v-if="fileInfo.exists && isPreviewable" class="mt-2">
                            <!-- PDF Preview -->
                            <div v-if="isPdf && fileInfo.url" class="border-border h-[500px] w-full rounded-md border">
                                <object :data="fileInfo.url" type="application/pdf" class="h-full w-full">
                                    <div class="bg-accent/20 flex h-full items-center justify-center p-4 text-center">
                                        <div>
                                            <FileType2Icon class="text-muted-foreground mx-auto mb-2 h-10 w-10" />
                                            <p>PDF preview not available in your browser.</p>
                                            <a :href="fileInfo.url" target="_blank" class="text-primary mt-2 inline-block underline">
                                                Open PDF in new tab
                                            </a>
                                        </div>
                                    </div>
                                </object>
                            </div>

                            <!-- Image Preview -->
                            <div v-else-if="isImage && fileInfo.url" class="flex justify-center">
                                <img :src="fileInfo.url" :alt="file.name" class="max-h-[500px] max-w-full rounded-md object-contain" />
                            </div>

                            <!-- Text Preview -->
                            <div v-else-if="isTxt" class="bg-accent/50 max-h-[500px] overflow-auto rounded-md p-4">
                                <pre class="text-sm whitespace-pre-wrap">{{ file.content }}</pre>
                            </div>

                            <!-- Office File Preview -->
                            <div v-else-if="isOfficeFile && fileInfo.url" class="border-border h-[500px] w-full rounded-md border">
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
                            <h3 class="text-md mb-2 font-medium">Extracted Text</h3>
                            <div class="bg-accent/50 max-h-[400px] overflow-auto rounded-md p-4">
                                <pre class="text-sm whitespace-pre-wrap">{{ file.content }}</pre>
                            </div>
                        </div>

                        <!-- File Not Found -->
                        <div v-if="!fileInfo.exists" class="bg-accent/20 flex h-[200px] items-center justify-center rounded-md">
                            <div class="text-center">
                                <FileIcon class="text-muted-foreground mx-auto mb-2 h-10 w-10" />
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
        </div>
    </AppLayout>
</template>
