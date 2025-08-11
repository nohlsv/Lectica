<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type SharedData, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import FileCard from '@/components/FileCard.vue';
import { TrendingUpIcon, UsersIcon, TagsIcon, GraduationCapIcon, HistoryIcon } from 'lucide-vue-next';
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

// Adjust quick actions and information based on user role
const isFacultyOrAdmin = computed(() => ['faculty', 'admin'].includes(user.user_role));
const isStudent = computed(() => user.user_role === 'student');
</script>

<template>
    <div class="bg-[#161615]">
        <Head title="Home" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <!-- Welcome Section -->
                <div class="mb-10 w-full min-h-[215px] bg-[url(/8-bit-bg.png)] bg-cover bg-center rounded-xl flex items-center justify-center p-6">
                    <h1 class="text-2xl font-bold text-white text-shadow-black">Welcome to Lectica, {{ user.last_name }}, {{ user.first_name }}!</h1>
                </div>

                <!-- Quick Actions -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link
                            v-if="isFacultyOrAdmin"
                            href="/files/verify"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors"
                        >
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                            </div>
                            <span class="font-medium">Verify Files</span>
                        </Link>

                        <Link
                            v-if="isFacultyOrAdmin"
                            href="/statistics"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors"
                        >
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M3 3v18h18"></path>
                                    <path d="M9 17V9"></path>
                                    <path d="M15 17V13"></path>
                                    <path d="M21 17V11"></path>
                                </svg>
                            </div>
                            <span class="font-medium">View Statistics</span>
                        </Link>

                        <Link v-if="isStudent"
                            href="/files/create" class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                                </div>
                                    <span class="font-medium">Upload File</span>
                        </Link>

                        <Link v-if="isStudent"
                            href="/files"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                </div>
                                    <span class="font-medium">Browse Files</span>
                        </Link>

                        <Link
                            v-if="isStudent"
                            href="/history"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors"
                        >
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M3 3h18v18H3z"></path>
                                    <path d="M9 17V7"></path>
                                    <path d="M15 17V11"></path>
                                    </svg>
                            </div>
                            <span class="font-medium">History</span>
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
    </div>
</template>
