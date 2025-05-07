<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { FileIcon, FolderIcon, StarIcon, PlusIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    files: Object,
    tags: Array,
    selectedTags: Array,
});

// Computed property to group files by first letter
const groupedFiles = computed(() => {
    const grouped = {};

    props.files.data.forEach(file => {
        const firstLetter = file.name.charAt(0).toUpperCase();
        if (!grouped[firstLetter]) {
            grouped[firstLetter] = [];
        }
        grouped[firstLetter].push(file);
    });

    // Sort groups alphabetically
    return Object.keys(grouped).sort().reduce((result, key) => {
        result[key] = grouped[key];
        return result;
    }, {});
});

const breadcrumbs = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'My Files', href: route('myfiles') },
];
</script>

<template>
    <Head title="My Files" />

    <AppLayout>
        <div class="container py-6">
            <!-- Breadcrumbs -->
            <div class="mb-6 flex items-center text-sm text-muted-foreground">
                <div v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                    <Link v-if="index < breadcrumbs.length - 1" :href="crumb.href" class="hover:text-foreground">
                        {{ crumb.title }}
                    </Link>
                    <span v-else class="font-medium text-foreground">{{ crumb.title }}</span>

                    <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
                </div>
            </div>

            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold">My Files</h1>
                <Link :href="route('files.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Add New File
                    </Button>
                </Link>
            </div>

            <div v-if="files.data.length === 0" class="flex flex-col items-center justify-center py-12">
                <FolderIcon class="h-16 w-16 text-muted-foreground mb-4" />
                <h2 class="text-xl font-semibold mb-2">No files found</h2>
                <p class="text-muted-foreground mb-6">You haven't uploaded any files yet.</p>
                <Link :href="route('files.create')">
                    <Button>
                        <PlusIcon class="h-4 w-4 mr-2" />
                        Upload Your First File
                    </Button>
                </Link>
            </div>

            <div v-else class="space-y-6">
                <div v-for="(files, letter) in groupedFiles" :key="letter" class="space-y-4">
                    <h2 class="text-xl font-bold border-b pb-2">{{ letter }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <Link
                            v-for="file in files"
                            :key="file.id"
                            :href="route('files.show', file.id)"
                            class="no-underline"
                        >
                            <Card class="h-full transition-all hover:shadow-md">
                                <CardHeader class="pb-2">
                                    <div class="flex justify-between items-start">
                                        <div class="flex items-center">
                                            <FileIcon class="h-5 w-5 mr-2 text-primary" />
                                            <CardTitle class="text-lg">{{ file.name }}</CardTitle>
                                        </div>
                                        <StarIcon
                                            :class="[
                                                'h-5 w-5',
                                                file.is_starred ? 'fill-yellow-400 text-yellow-400' : 'text-muted-foreground'
                                            ]"
                                        />
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <CardDescription class="line-clamp-2">
                                        {{ file.description || 'No description provided' }}
                                    </CardDescription>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <Badge
                                            v-for="tag in file.tags"
                                            :key="tag.id"
                                            variant="secondary"
                                            class="text-xs"
                                        >
                                            {{ tag.name }}
                                        </Badge>
                                    </div>
                                </CardContent>
                                <CardFooter class="flex justify-between text-xs text-muted-foreground">
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
            <div v-if="files.meta.last_page > 1" class="flex justify-center mt-8">
                <div class="flex space-x-1">
                    <Link
                        v-for="page in files.meta.links"
                        :key="page.label"
                        :href="page.url"
                        v-html="page.label"
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
    </AppLayout>
</template>
