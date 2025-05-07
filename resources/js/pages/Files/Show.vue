<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File } from '@/types';
import { ArrowLeftIcon, ListChecks, Pencil, BookOpen, PencilIcon, DownloadIcon, StarIcon, FileIcon, FileTextIcon, FileType2Icon } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';

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
            },
            onFinish: () => {
                isStarring.value = false;
            }
        });
    } catch (error) {
        isStarring.value = false;
        console.error('Error toggling star', error);
    }
};

const isPdf = computed(() => props.fileInfo.extension.toLowerCase() === 'pdf');
const isTxt = computed(() => props.fileInfo.extension.toLowerCase() === 'txt');
const isImage = computed(() => ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(props.fileInfo.extension.toLowerCase()));
const isPreviewable = computed(() => isPdf.value || isTxt.value || isImage.value);

const isOwner = computed(() => {
    return props.file.can_edit === true;
});

const downloadFile = () => {
    const downloadUrl = route('files.download', { file: props.file.id }); // Use the backend route for downloading
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', props.file.name);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

</script>

<template>
    <Head :title="`File: ${file.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        href="/files"
                        class="inline-flex items-center gap-1 text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeftIcon class="h-4 w-4" />
                        Back to Files
                    </Link>
                    <h1 class="text-2xl font-bold">File Details</h1>
                </div>
                <div class="flex items-center gap-3">
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
                </div>
                <div class="flex items-center gap-2">
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
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- File Information -->
                <div class="space-y-4 md:col-span-1">
                    <div class="rounded-lg border border-border p-4">
                        <h2 class="text-lg font-semibold mb-3">{{file.name}}</h2>
                        <div v-if="file.description" class="mb-3 text-sm">
                            <p class="text-muted-foreground">{{ file.description }}</p>
                        </div>
                        <dl class="space-y-2 text-sm">
<!--                            <div class="flex justify-between">-->
<!--                                <dt class="font-medium text-muted-foreground">Name:</dt>-->
<!--                                <dd class="text-right">{{ file.name }}</dd>-->
<!--                            </div>-->
                            <div class="flex justify-between">
                                <dt class="font-medium text-muted-foreground">Type:</dt>
                                <dd class="text-right uppercase">{{ fileInfo.extension }}</dd>
                            </div>
                            <div class="flex justify-between" v-if="fileInfo.size">
                                <dt class="font-medium text-muted-foreground">Size:</dt>
                                <dd class="text-right">{{ fileInfo.size }}</dd>
                            </div>
                            <div class="flex justify-between" v-if="fileInfo.lastModified">
                                <dt class="font-medium text-muted-foreground">Last Modified:</dt>
                                <dd class="text-right">{{ fileInfo.lastModified }}</dd>
                            </div>
                            <div class="flex justify-between pt-2 mt-2 border-t border-border">
                                <dt class="font-medium text-muted-foreground">Uploaded by:</dt>
                                <dd class="text-right">{{ file.user.last_name }}, {{ file.user.first_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-muted-foreground">Upload Date:</dt>
                                <dd class="text-right">{{ new Date(file.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</dd>
                            </div>
                            <div class="pt-2 mt-2 border-t border-border">
                                <dt class="font-medium text-muted-foreground mb-2">Tags:</dt>
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
                    </div>
                </div>

                <!-- File Preview -->
                <div class="space-y-4 md:col-span-2">
                    <div class="rounded-lg border border-border p-4">
                        <h2 class="text-lg font-semibold mb-3">File Preview</h2>

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

            <div class="mt-4">
                <h3 class="text-lg font-medium">Study Materials</h3>
                <div class="mt-2 flex flex-wrap gap-4">
                    <div class="w-full md:w-auto">
                        <h4 class="mb-2 font-medium">Flashcards</h4>
                        <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0">
                            <Link :href="route('files.flashcards.index', file.id)">
                                <Button variant="outline" class="w-full sm:w-auto">
                                    <BookOpen class="mr-2 h-4 w-4" />
                                    View Flashcards
                                </Button>
                            </Link>
                            <Link v-if="isOwner" :href="route('files.flashcards.create', file.id)">
                                <Button variant="outline" class="w-full sm:w-auto">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Add Flashcard
                                </Button>
                            </Link>
                            <Link :href="route('files.flashcards.practice', file.id)">
                                <Button variant="default" class="w-full sm:w-auto">
                                    <BookOpen class="mr-2 h-4 w-4" />
                                    Practice
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <div class="w-full md:w-auto">
                        <h4 class="mb-2 font-medium">Quizzes</h4>
                        <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0">
                            <Link :href="route('files.quizzes.index', file.id)">
                                <Button variant="outline" class="w-full sm:w-auto">
                                    <ListChecks class="mr-2 h-4 w-4" />
                                    View Quizzes
                                </Button>
                            </Link>
                            <Link v-if="isOwner" :href="route('files.quizzes.create', file.id)">
                                <Button variant="outline" class="w-full sm:w-auto">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    Add Quiz
                                </Button>
                            </Link>
                            <Link :href="route('files.quizzes.test', file.id)">
                                <Button variant="default" class="w-full sm:w-auto">
                                    <ListChecks class="mr-2 h-4 w-4" />
                                    Take Quiz
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

