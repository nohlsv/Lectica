<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type SharedData, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FileCard from '@/components/FileCard.vue';
import { TrendingUpIcon, UsersIcon, TagsIcon, GraduationCapIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface RecommendationGroup {
    [key: string]: File[];
}

interface Props {
    recommendations: RecommendationGroup;
}

const props = defineProps<Props>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/home',
    },
];

// Check if there are any recommendations across all categories
const hasAnyRecommendations = computed(() => {
    return Object.values(props.recommendations).some(files => files && files.length > 0);
});

// Map recommendation categories to icons and titles
const recommendationCategories = [
    {
        key: 'trending',
        title: 'Trending Files',
        icon: TrendingUpIcon,
        description: 'Popular files with the most stars this week'
    },
    {
        key: 'program',
        title: 'Files From Your Program',
        icon: GraduationCapIcon,
        description: 'Files shared by students in your program'
    },
    {
        key: 'collaborative',
        title: 'You Might Like These',
        icon: UsersIcon,
        description: 'Based on files you\'ve starred'
    },
    {
        key: 'contentBased',
        title: 'Similar Content',
        icon: TagsIcon,
        description: 'Files with similar tags to ones you\'ve viewed'
    }
];
</script>

<template>
    <Head title="Home" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold">Welcome to Lectica, {{ user.last_name }}, {{ user.first_name }}!</h1>
            </div>

            <!-- Quick Actions -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link href="/files/create" class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        </div>
                        <span class="font-medium">Upload File</span>
                    </Link>

                    <Link href="/files" class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        </div>
                        <span class="font-medium">Browse Files</span>
                    </Link>

                    <Link href="/recommendations" class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <span class="font-medium">All Recommendations</span>
                    </Link>
                </div>
            </div>

            <!-- Recommendations Section -->
            <div class="mb-10">
                <h2 class="text-xl font-semibold mb-6">Recommended Files</h2>

                <div class="grid gap-8">
                    <div v-for="category in recommendationCategories" :key="category.key" class="space-y-4">
                        <!-- Only show categories that have files -->
                        <div v-if="recommendations[category.key]?.length">
                            <div class="flex items-center gap-2 mb-3">
                                <component :is="category.icon" class="h-5 w-5 text-primary" />
                                <h3 class="text-lg font-medium">{{ category.title }}</h3>
                            </div>

                            <p class="text-sm text-muted-foreground mb-4">{{ category.description }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <FileCard
                                    v-for="file in recommendations[category.key]"
                                    :key="file.id"
                                    :file="file"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message when no recommendations are available -->
                <div v-if="!hasAnyRecommendations" class="flex flex-col items-center justify-center p-8 text-center">
                    <div class="rounded-full bg-muted p-3 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground h-6 w-6">
                            <path d="M8 17l4 4 4-4"></path>
                            <path d="M12 12v9"></path>
                            <path d="M20 8h-7"></path>
                            <path d="M18 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8"></path>
                        </svg>
                    </div>
                    <p class="text-muted-foreground">No recommendations available at the moment. Check back later!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>