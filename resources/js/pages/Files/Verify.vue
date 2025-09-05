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
        <div class="p-6 space-y-6 bg-gradient">
            <div class="flex justify-center">
                <h1 class="text-2xl font-bold welcome-banner animate-soft-bounce px-10 py-2 text-center w-fit pixel-outline">Verify Files</h1>
            </div>
                <div class="bg-container p-6">
                <div v-if="props.files.data.length === 0" class="text-center text-muted-foreground">
                    No unverified files available.
                </div>
                <div v-else class="space-y-4 grid md:grid-cols-2 sm:grid-cols-1 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <div v-for="file in props.files.data" :key="file.id" class="p-4 rounded-lg bg-[#8E2C38] border-[#0c0a03] border-2 h-full flex flex-col">
                        <h2 class="text-xl md:text-2xl font-semibold text-[#fdf6ee] pixel-outline">{{ file.name }}</h2>
                        <p class="text-sm lg:text-base text-[#fdf6ee]/50">Uploaded by: <span class="ml-1"> {{ file.user.first_name }} {{ file.user.last_name }}</span></p>
                        <p class="text-sm lg:text-base text-[#fdf6ee]/50">Description: <span class="ml-1">{{ file.description || 'No description provided' }}</span></p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span v-for="tag in file.tags" :key="tag.id" class="bg-accent text-foreground rounded-full px-2 py-1 text-xs">
                                {{ tag.name }}
                            </span>
                        </div>
                        <div class="gap-2 flex items-center">
                            <button class="mt-2 bg-[#6aa7d6] border-2 rounded-md text-[#fdf6ee] hover:bg-[#578ec3] border-[#0c0a03] duration-300 pixel-outline flex items-center cursor-pointer text-sm px-3 py-1.5 sm:text-base sm:px-4 sm:py-2">
                                <Link :href="route('files.show', file.id)" target="_blank" class="flex items-center">
                                    <FileIcon class="w-4 h-4 mr-2 pixel-outline-icon" />
                                    <span>View File</span>
                                </Link>
                            </button>
                            <button
                                @click="verifyFile(file.id)"
                                class="mt-2 bg-[#5cae6e] text-[#fdf6ee] pixel-outline hover:bg-[#4a9159] border-[#0c0a03] duration-300 border-2 rounded-md flex items-center cursor-pointer text-sm px-3 py-1.5 sm:text-base sm:px-4 sm:py-2"
                            >
                                <CheckCircleIcon class="w-4 h-4 mr-2 pixel-outline-icon" />
                                <span>Verify</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    <nav class="flex space-x-2">
                        <button
                            v-for="link in props.files.links"
                            :key="link.label"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            class="text-sm px-3 py-1.5 sm:text-base sm:px-4 sm:py-2 border rounded-md bg"
                            :class="{
                                'bg-[#B23A48] text-primary pixel-outline border-2 border-[#0c0a03]': link.active,
                                'bg-[#3B1A14] hover:bg-[#77252e] duration-300 text-muted-foreground pixel-outline border-2 border-[#0c0a03]': !link.active,
                            }"
                            v-html="link.label"
                        >
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

