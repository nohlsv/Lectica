<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type File } from '@/types';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { CheckCircleIcon, FileIcon } from 'lucide-vue-next';

interface Props {
    files: {
        data: File[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
    };
}

const props = defineProps<Props>();

const verifyFile = (fileId: number) => {
    router.patch(route('files.verify.update', fileId), {}, {
        onSuccess: () => {
            toast.success('File verified successfully!');
            router.reload(); // Refresh the file list after verification
        },
        onError: () => {
            toast.error('Failed to verify the file. Please try again.');
        },
    });
};
</script>

<template>
    <Head title="Verify Files" />
    <AppLayout>
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold">Verify Files</h1>
            <div v-if="props.files.data.length === 0" class="text-center text-muted-foreground">
                No unverified files available.
            </div>
            <div v-else class="space-y-4">
                <div v-for="file in props.files.data" :key="file.id" class="p-4 bg-muted foreground rounded-lg shadow">
                    <h2 class="text-lg font-semibold">{{ file.name }}</h2>
                    <p class="text-sm text-muted-foreground">Uploaded by: {{ file.user.first_name }} {{ file.user.last_name }}</p>
                    <p class="text-sm text-muted-foreground">Description: {{ file.description || 'No description provided' }}</p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span v-for="tag in file.tags" :key="tag.id" class="bg-accent text-foreground rounded-full px-2 py-1 text-xs">
                            {{ tag.name }}
                        </span>
                    </div>
                    <div class="gap-2 flex items-center">
                        <button class="mt-2 px-4 py-2 bg-background border-2 text-foreground rounded-md hover:bg-primary/90 flex items-center">
                            <Link :href="route('files.show', file.id)" target="_blank" class="flex items-center">
                                <FileIcon class="w-4 h-4 mr-1" />
                                <span>View File</span>
                            </Link>
                        </button>
                        <button
                            @click="verifyFile(file.id)"
                            class="mt-2 px-4 py-2 bg-accent text-foreground border-2 rounded-md hover:bg-primary/90 flex items-center"
                        >
                            <CheckCircleIcon class="w-4 h-4 mr-1" />
                            <span>Verify</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex justify-center mt-6">
                <nav class="flex space-x-2">
                    <button
                        v-for="link in props.files.links"
                        :key="link.label"
                        :disabled="!link.url"
                        @click="link.url && router.get(link.url)"
                        class="px-4 py-2 border rounded-md"
                        :class="{
                            'bg-primary-foreground text-primary': link.active,
                            'bg-muted text-muted-foreground': !link.active,
                        }"
                        v-html="link.label"
                    >
                    </button>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>

