<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { FileIcon, FolderIcon, StarIcon, PlusIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { type File, type Tag, type BreadcrumbItem } from '@/types';

interface PageProps {
    files: {
        data: File[];
        meta?: {
            last_page: number;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
    };
    tags: Tag[];
    selectedTags: number[];
}

const props = defineProps<PageProps>();

// Computed property to group files by first letter
const groupedFiles = computed(() => {
    const grouped: Record<string, File[]> = {};

    props.files.data.forEach(file => {
        const firstLetter = file.name.charAt(0).toUpperCase();
        if (!grouped[firstLetter]) {
            grouped[firstLetter] = [];
        }
        grouped[firstLetter].push(file);
    });

    // Sort groups alphabetically
    return Object.keys(grouped).sort().reduce<Record<string, File[]>>((result, key) => {
        result[key] = grouped[key];
        return result;
    }, {});
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: route('home') },
    { title: 'My Files', href: route('myfiles') },
];
</script>

<template>
    <Head title="My Files" />

    <AppLayout>
        <div class="py-6">
            <!-- Breadcrumbs -->
            <div class="mb-6 flex items-center text-sm text-muted-foreground ml-3">
                <div v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                    <Link v-if="index < breadcrumbs.length - 1" :href="crumb.href" class="hover:text-foreground">
                        {{ crumb.title }}
                    </Link>
                    <span v-else class="font-medium text-foreground">{{ crumb.title }}</span>

                    <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
                </div>
            </div>

            <div class="bg-gradient p-6 space-y-4 min-h-screen">
                <div class="flex flex-col justify-center items-center">
                    <h1 class="text-3xl font-bold welcome-banner md:py-2 md:px-8 py-2 px-2 animate-soft-bounce items-center justify-center">My Files</h1>
                </div>

                <!-- If no files are uploaded -->
                <div v-if="files.data.length === 0" class="flex flex-col items-center justify-center py-12 bg-container min-h-screen">
                    <FolderIcon class="h-16 w-16 text-muted-foreground mb-4" />
                    <h2 class="text-xl font-semibold mb-2 pixel-outline">No files found</h2>
                    <p class="text-muted-foreground mb-6">You haven't uploaded any files yet.</p>
                    <Link :href="route('files.create')">
                        <Button class="bg-[#10B981] hover:bg-[#0e9459] hover:scale-105 duration-300 text-prmary pixel-outline tracking-wide py-3 border-[#0c0a03] border-2">
                            <PlusIcon class="h-4 w-4 mr-2 pixel-outline-icon" />
                            Upload Your First File
                        </Button>
                    </Link>
                </div>

                <!-- Main container -->
                <div v-else class="bg-container space-y-6 p-6 min-h-screen">
                    <div v-for="(files, letter) in groupedFiles" :key="letter" class="space-y-4">
                        <h2 class="text-2xl font-extrabold border-b border-[#fb9e1b] pb-2 pixel-outline text-[#fce085]">{{ letter }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                            <Link
                                v-for="file in files"
                                :key="file.id"
                                :href="route('files.show', file.id)"
                                class="no-underline"
                            >
                                <Card class="h-full overflow-hidden rounded-lg transition-all bg-[#8E2C38] border-[#0c0a03] border-2 hover:scale-110 duration-300">
                                    <CardHeader class="pb-2">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <FileIcon class="h-6 w-6 text-[#fdf6ee] pixel-outline-icon" />
                                                <CardTitle
                                                class="text-base sm:text-lg truncate max-w-full sm:max-w-[260px] text-[#fdf6ee]"
                                                :title="file.name"
                                                >
                                                {{ file.name }}
                                                </CardTitle>
                                            </div>
                                            <StarIcon
                                                :class="[
                                                'h-5 w-5 shrink-0 self-start sm:self-auto',
                                                file.is_starred ? 'fill-yellow-400 text-yellow-400' : 'text-muted-foreground'
                                                ]"
                                            />
                                            </div>
                                    </CardHeader>
                                    <CardContent>
                                        <CardDescription class="line-clamp-2 text-[#fdf6ee]/75 pixel-outline tracking-wide">
                                            {{ file.description || 'No description provided' }}
                                        </CardDescription>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <Badge
                                                v-for="tag in file.tags"
                                                :key="tag.id"
                                                class="text-xs truncate bg-[#faa800] text-[#661500] border-2 border-[#0c0a03]"
                                                :title="tag.name"
                                            >
                                                {{ tag.name }}
                                            </Badge>
                                        </div>
                                    </CardContent>
                                    <CardFooter class="flex justify-between text-xs text-[#fdf6ee]/75 pixel-outline tracking-wide">
                                        <span>Created: {{ new Date(file.created_at).toLocaleDateString() }}</span>
                                        <div class="flex items-center space-x-2">
                                            <div class="flex items-center">
                                                <span>{{ file.flashcards_count || 0 }} flashcards</span>
                                            </div>
                                            <span>•</span>
                                            <div class="flex items-center">
                                                <span>{{ file.quizzes_count || 0 }} quizzes</span>
                                            </div>
                                        </div>
                                    </CardFooter>
                                </Card>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="files.meta && files.meta.last_page > 1" class="flex justify-center mt-8">
                    <div class="flex space-x-1">
                        <Link
                            v-for="page in files.meta.links"
                            :key="page.label"
                            :href="page.url ? page.url : '#'"
                            v-text="page.label"
                            :class="[
                                'px-3 py-1 rounded border',
                                page.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'hover:bg-muted',
                                !page.url && 'opacity-50 cursor-not-allowed'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
