<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type User } from '@/types';
import { ArrowLeftIcon, ListChecks, Pencil, BookOpen, PencilIcon, DownloadIcon, StarIcon, FileIcon, FileType2Icon, CheckCircleIcon,} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogTrigger } from '@/components/ui/dialog';

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
</script>

<template>
    <Head :title="`File: ${file.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" bg-[#fad380]">

            <!-- First Container (Opening) -->
            <div class="flex flex-col justify-between gap-4">

                <!-- Back btn -->
                <div class="flex items-start mt-3 mb-3 ml-3">
                    <Link
                        href="/files"
                        class="inline-flex items-center gap-2 px-4 py-2 text-white bg-red-700 border-2 border-yellow-400 rounded-md shadow-md hover:bg-yellow-400
                             hover:text-red-700 transition-colors duration-300 font-bold"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Files
                    </Link>
                </div>

                <!-- File Detail part -->
                <div class="rounded-xl px-10 py-2 text-3xl sm:text-3xl md:text-4xl font-extrabold welcome-banner
                            shadow-[2px_2px_0px_rgba(0,0,0,0.8)] animate-soft-bounce justify-center inline-block m-auto mb-3"
                            style="image-rendering: pixelated;">
                    <h1 class="text-2xl md:text-2xl font-extrabold text-center">File Details</h1>
                </div>
            </div>

            <div class="bg-[#801403] ml-3 mr-3 mb-3 border-[#680d00] border-8 rounded-md">
                <!-- Separator -->
                <div class="grid gap-6 md:grid-cols-3">
                    <div class="space-y-4 md:col-span-1">

                        <!-- File Information (1st Major Container)-->
                        <div class="p-4">
                            <h2 class="text-3xl font-semibold mb-3 text-[#fb9e1b] align-middle">{{file.name}}</h2>
                            <!--<div v-if="file.description" class="mb-3 text-sm">
                                <p class="text-muted-foreground">{{ file.description }}</p>
                                </div>
                            -->
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-base text-[#fce085]">File Type:</dt>
                                    <dd class="text-right uppercase">{{ fileInfo.extension }}</dd>
                                </div>
                                <!--<div class="flex justify-between" v-if="fileInfo.size">
                                    <dt class="text-base text-[#fce085]"> File Size:</dt>
                                    <dd class="text-right">{{ fileInfo.size }}</dd>
                                </div>
                                <div class="flex justify-between" v-if="fileInfo.lastModified">
                                    <dt class="text-base text-[#fce085]">Last Modified:</dt>
                                    <dd class="text-right">{{ fileInfo.lastModified }}</dd>
                                </div>-->
                                <div class="flex justify-between">
                                    <dt class="text-base text-[#fce085]">Verified:</dt>
                                    <dd class="text-right">
                                        <span
                                            :class="file.verified ? 'text-green-500' : 'text-red-500'"
                                            class="font-semibold"
                                        >
                                            {{ file.verified ? 'Yes' : 'No' }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between pt-2 mt-2 border-t border-border">
                                    <dt class="text-base text-[#fce085]">Uploaded by:</dt>
                                    <dd class="text-right">{{ file.user.last_name }}, {{ file.user.first_name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-base text-[#fce085]">Upload Date:</dt>
                                    <dd class="text-right">{{ new Date(file.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</dd>
                                </div>
                                <div class="pt-2 mt-2 border-t border-border">
                                    <dt class="text-base text-[#fce085] mb-2">Tags:</dt>
                                    <dd class="flex flex-wrap gap-1">
                                        <span
                                            v-for="tag in file.tags"
                                            :key="tag.id"
                                            class="inline-flex px-2 py-1 text-xs rounded-md bg-primary/10 text-primary"
                                        >
                                            {{ tag.name }}
                                        </span>
                                        <span v-if="!file.tags || file.tags.length === 0" class="text-muted-foreground text-sm">
                                            No tags
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                            <div class="mt-4 border-t border-border py-4 space-y-2">
                                <h3 class="text-lg font-medium">Study Materials</h3>
                                <div class="mt-2">
                                    <div class="w-full py-2">
                                        <h4 class="mb-2 font-medium">Flashcards</h4>
                                        <div class="flex flex-wrap mb-2 gap-6 justify-center">
                                            <Link :href="route('files.flashcards.index', file.id)">
                                                <Button variant="outline" class="w-full sm:w-auto text-xs">
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
                                                <Button variant="default" class="w-full sm:w-auto text-xs">
                                                    <BookOpen class="mr-2 h-3 w-3" />
                                                    Practice
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                    <div class="w-full border-t border-border py-2">
                                        <h4 class="mb-2 font-medium">Quizzes</h4>
                                        <div class="flex flex-wrap mb-2 gap-6 justify-center">
                                            <Link :href="route('files.quizzes.index', file.id)">
                                                <Button variant="outline" class="w-full sm:w-auto text-xs">
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
                                                <Button variant="default" class="w-full sm:w-auto text-xs">
                                                    <ListChecks class="mr-2 h-3 w-3" />
                                                    Take Quiz
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                    <div
                                        class="gap-2 w-full border-t border-border pt-4 flex justify-center"
                                        v-if="isOwner && file.verified"
                                    >
                                        <Dialog v-model:open="isDialogOpen" onOpenChange="isDialogOpen = $event">
                                            <DialogTrigger asChild>
                                                <Button
                                                    class="w-full sm:w-auto flex items-center justify-center gap-1 rounded-md bg-secondary px-4 py-2 text-xs font-medium text-secondary-foreground hover:bg-secondary/90"
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
                                                    <div v-if="generateOptions.generate_flashcards" class="flex items-center gap-2">
                                                        <label for="flashcards_count" class="text-sm font-medium">Flashcards Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="flashcards_count"
                                                            v-model="generateOptions.flashcards_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.flashcards_count = Math.min(Math.max(generateOptions.flashcards_count, 1), 15)"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" v-model="generateOptions.generate_multiple_choice_quizzes" id="generate_multiple_choice_quizzes" />
                                                        <label for="generate_multiple_choice_quizzes" class="text-sm font-medium">Generate Multiple Choice Quizzes</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_multiple_choice_quizzes" class="flex items-center gap-2">
                                                        <label for="multiple_choice_count" class="text-sm font-medium">Multiple Choice Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="multiple_choice_count"
                                                            v-model="generateOptions.multiple_choice_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.multiple_choice_count = Math.min(Math.max(generateOptions.multiple_choice_count, 1), 15)"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" v-model="generateOptions.generate_enumeration_quizzes" id="generate_enumeration_quizzes" />
                                                        <label for="generate_enumeration_quizzes" class="text-sm font-medium">Generate Enumeration Quizzes</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_enumeration_quizzes" class="flex items-center gap-2">
                                                        <label for="enumeration_count" class="text-sm font-medium">Enumeration Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="enumeration_count"
                                                            v-model="generateOptions.enumeration_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.enumeration_count = Math.min(Math.max(generateOptions.enumeration_count, 1), 15)"
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <input type="checkbox" v-model="generateOptions.generate_true_false_quizzes" id="generate_true_false_quizzes" />
                                                        <label for="generate_true_false_quizzes" class="text-sm font-medium">Generate True/False Quizzes</label>
                                                    </div>
                                                    <div v-if="generateOptions.generate_true_false_quizzes" class="flex items-center gap-2">
                                                        <label for="true_false_count" class="text-sm font-medium">True/False Count:</label>
                                                        <Input
                                                            type="number"
                                                            id="true_false_count"
                                                            v-model="generateOptions.true_false_count"
                                                            min="1"
                                                            max="15"
                                                            @input="generateOptions.true_false_count = Math.min(Math.max(generateOptions.true_false_count, 1), 15)"
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

                    <!-- File Preview (2nd Major Contianer) -->
                    <div class="space-y-4 md:col-span-2">
                        <div class="p-4">
                                <!-- Top buttons container-->
                                <div class="flex flex-row justify-between items-center gap-3 px-4 py-3">
                                    <h2 class="text-xl font-semibold justify-center flex text-left px-4 py-3  text-[#fce085]">File Preview</h2>

                                    <!-- Star button -->
                                    <div class="flex flex-wrap items-center gap-3">
                                        <button
                                            @click="toggleStar"
                                            class="inline-flex items-center justify-center rounded-md bg-background px-3 py-2 text-sm font-medium hover:bg-accent transition-colors border-2 border-border"
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


                                    <!-- Edit and dl button -->

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
                                            class="inline-flex items-center justify-center gap-1 rounded-md border-[#661500] bg-[#feaf00] px-4 py-2 text-sm font-medium text-[#661500] hover:bg-accent"
                                        >
                                            <DownloadIcon class="h-4 w-4" />
                                            Download
                                        </a>
                                        </div>
                                 </div>

                            <div v-if="fileInfo.exists && isPreviewable" class="mt-2">
                                <!-- PDF Preview -->
                                <div v-if="isPdf && fileInfo.url" class="w-full h-[500px] border border-border rounded-md">
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
            </div>
        </div>
    </AppLayout>
</template>

