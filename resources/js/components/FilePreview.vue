<script setup lang="ts">
import { FileArchiveIcon, FileIcon, FilePresentationIcon, FileSpreadsheetIcon, FileTextIcon } from 'lucide-vue-next';
import { computed } from 'vue';

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
    <div class="file-preview border-border bg-background rounded-md border">
        <!-- Preview not available message -->
        <div v-if="!isPreviewable" class="flex h-64 flex-col items-center justify-center gap-4 p-4 text-center">
            <div class="bg-accent rounded-full p-4">
                <FileIcon v-if="fileType === 'other'" class="text-foreground h-6 w-6" />
                <FileTextIcon v-else-if="fileType === 'word'" class="h-6 w-6 text-blue-500" />
                <FilePresentationIcon v-else-if="fileType === 'presentation'" class="h-6 w-6 text-red-500" />
                <FileSpreadsheetIcon v-else-if="fileType === 'spreadsheet'" class="h-6 w-6 text-green-500" />
                <FileArchiveIcon v-else-if="fileType === 'archive'" class="h-6 w-6 text-amber-500" />
            </div>
            <div>
                <p class="text-foreground">Preview not available for this file type</p>
                <p class="text-muted-foreground text-sm">{{ fileName }}</p>
            </div>
            <a
                :href="fileUrl"
                download
                class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium"
            >
                Download to view
            </a>
        </div>

        <!-- Image preview -->
        <div v-else-if="fileType === 'image'" class="flex items-center justify-center p-4">
            <img :src="fileUrl" :alt="fileName" class="max-h-[600px] max-w-full rounded object-contain" />
        </div>

        <!-- PDF preview -->
        <div v-else-if="fileType === 'pdf'" class="h-[600px] w-full">
            <object :data="fileUrl" type="application/pdf" class="h-full w-full rounded">
                <div class="flex h-full flex-col items-center justify-center gap-4 p-4 text-center">
                    <p class="text-foreground">Unable to display PDF</p>
                    <a
                        :href="fileUrl"
                        target="_blank"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Open PDF in new tab
                    </a>
                </div>
            </object>
        </div>

        <!-- Video preview -->
        <div v-else-if="fileType === 'video'" class="flex items-center justify-center p-4">
            <video controls class="max-h-[600px] max-w-full rounded">
                <source :src="fileUrl" :type="`video/${fileExtension}`" />
                Your browser does not support the video tag.
            </video>
        </div>

        <!-- Audio preview -->
        <div v-else-if="fileType === 'audio'" class="flex items-center justify-center p-8">
            <audio controls class="w-full">
                <source :src="fileUrl" :type="`audio/${fileExtension}`" />
                Your browser does not support the audio element.
            </audio>
        </div>

        <!-- Text preview for small text files (could be enhanced with syntax highlighting) -->
        <div v-else-if="fileType === 'text'" class="p-4">
            <div class="border-border bg-accent/50 max-h-[600px] overflow-auto rounded border p-4">
                <p class="text-muted-foreground text-sm">Text preview is limited. For larger files, please download.</p>
                <iframe :src="fileUrl" class="mt-2 h-[500px] w-full border-0"></iframe>
            </div>
        </div>
    </div>
</template>
