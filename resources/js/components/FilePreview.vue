<script setup lang="ts">
import { computed } from 'vue';
import { FileIcon, FileTextIcon,FileArchiveIcon, FilePresentationIcon, FileSpreadsheetIcon } from 'lucide-vue-next';

interface Props {
    filePath: string;
    fileName: string;
}

const props = defineProps<Props>();

// Get file extension from filename
const fileExtension = computed(() => {
    const parts = props.fileName.split('.');
    return parts.length > 1 ? parts.pop()?.toLowerCase() : '';
});

// Determine file type based on extension
const fileType = computed(() => {
    const ext = fileExtension.value;

    if (!ext) return 'unknown';

    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) {
        return 'image';
    } else if (['pdf'].includes(ext)) {
        return 'pdf';
    } else if (['txt', 'md', 'html', 'css', 'js', 'json', 'xml'].includes(ext)) {
        return 'text';
    } else if (['doc', 'docx'].includes(ext)) {
        return 'word';
    } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
        return 'spreadsheet';
    } else if (['ppt', 'pptx'].includes(ext)) {
        return 'presentation';
    } else if (['mp4', 'webm', 'ogg', 'mov'].includes(ext)) {
        return 'video';
    } else if (['mp3', 'wav', 'ogg', 'flac'].includes(ext)) {
        return 'audio';
    } else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) {
        return 'archive';
    }

    return 'other';
});

// Get full URL for the file
const fileUrl = computed(() => {
    return `/storage/${props.filePath}`;
});

// Determine if file type is previewable
const isPreviewable = computed(() => {
    return ['image', 'pdf', 'text', 'video', 'audio'].includes(fileType.value);
});
</script>

<template>
    <div class="file-preview rounded-md border border-border bg-background">
        <!-- Preview not available message -->
        <div v-if="!isPreviewable" class="flex h-64 flex-col items-center justify-center gap-4 p-4 text-center">
            <div class="rounded-full bg-accent p-4">
                <FileIcon v-if="fileType === 'other'" class="h-6 w-6 text-foreground" />
                <FileTextIcon v-else-if="fileType === 'word'" class="h-6 w-6 text-blue-500" />
                <FilePresentationIcon v-else-if="fileType === 'presentation'" class="h-6 w-6 text-red-500" />
                <FileSpreadsheetIcon v-else-if="fileType === 'spreadsheet'" class="h-6 w-6 text-green-500" />
                <FileArchiveIcon v-else-if="fileType === 'archive'" class="h-6 w-6 text-amber-500" />
            </div>
            <div>
                <p class="text-foreground">Preview not available for this file type</p>
                <p class="text-sm text-muted-foreground">{{ fileName }}</p>
            </div>
            <a
                :href="fileUrl"
                download
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                Download to view
            </a>
        </div>

        <!-- Image preview -->
        <div v-else-if="fileType === 'image'" class="flex items-center justify-center p-4">
            <img
                :src="fileUrl"
                :alt="fileName"
                class="max-h-[600px] max-w-full rounded object-contain"
            />
        </div>

        <!-- PDF preview -->
        <div v-else-if="fileType === 'pdf'" class="h-[600px] w-full">
            <object
                :data="fileUrl"
                type="application/pdf"
                class="h-full w-full rounded"
            >
                <div class="flex h-full flex-col items-center justify-center gap-4 p-4 text-center">
                    <p class="text-foreground">Unable to display PDF</p>
                    <a
                        :href="fileUrl"
                        target="_blank"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        Open PDF in new tab
                    </a>
                </div>
            </object>
        </div>

        <!-- Video preview -->
        <div v-else-if="fileType === 'video'" class="flex items-center justify-center p-4">
            <video
                controls
                class="max-h-[600px] max-w-full rounded"
            >
                <source :src="fileUrl" :type="`video/${fileExtension}`">
                Your browser does not support the video tag.
            </video>
        </div>

        <!-- Audio preview -->
        <div v-else-if="fileType === 'audio'" class="flex items-center justify-center p-8">
            <audio controls class="w-full">
                <source :src="fileUrl" :type="`audio/${fileExtension}`">
                Your browser does not support the audio element.
            </audio>
        </div>

        <!-- Text preview for small text files (could be enhanced with syntax highlighting) -->
        <div v-else-if="fileType === 'text'" class="p-4">
            <div class="max-h-[600px] overflow-auto rounded border border-border bg-accent/50 p-4">
                <p class="text-sm text-muted-foreground">Text preview is limited. For larger files, please download.</p>
                <iframe :src="fileUrl" class="mt-2 h-[500px] w-full border-0"></iframe>
            </div>
        </div>
    </div>
</template>
