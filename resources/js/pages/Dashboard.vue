<script setup lang="ts">
import FileCard from '@/components/FileCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type SharedData, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { GraduationCapIcon, TagsIcon, TrendingUpIcon, UsersIcon } from 'lucide-vue-next';
import { computed } from 'vue';
import { reactive } from "vue";

const scrollContainers = reactive<Record<string, HTMLElement | null>>({});

const scrollLeft = (key: string) => {
  const container = scrollContainers[key];
  if (container) {
    container.scrollBy({ left: -300, behavior: "smooth" });
  }
};

const scrollRight = (key : string) => {
  const container = scrollContainers[key];
  if (container) {
    container.scrollBy({ left: 300, behavior: "smooth" });
  }
};

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
    return Object.values(props.recommendations).some((files) => files && files.length > 0);
});

// Map recommendation categories to icons and titles
const recommendationCategories = [
    {
        key: 'trending',
        title: 'Trending Files',
        icon: TrendingUpIcon,
        description: 'Popular files with the most stars this week',
    },
    {
        key: 'program',
        title: 'Files From Your Program',
        icon: GraduationCapIcon,
        description: 'Files shared by students in your program',
    },
    {
        key: 'collaborative',
        title: 'You Might Like These',
        icon: UsersIcon,
        description: "Based on files you've starred",
    },
    {
        key: 'contentBased',
        title: 'Similar Content',
        icon: TagsIcon,
        description: "Files with similar tags to ones you've viewed",
    },
];

// Adjust quick actions and information based on user role
const isFacultyOrAdmin = computed(() => ['faculty', 'admin'].includes(user.user_role));
const isStudent = computed(() => user.user_role === 'student');
</script>

<template>
    <div class="dark:bg-[#161615]">
        <Head title="Home" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex w-full flex-1 flex-col max-h-[300px] gap-4 pt-4 px-4 pb-0 bg-lectica">
                <!--Welcome Section-->
                <div
                    class="mb-10 flex min-h-[215px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left"
                >
                    <!--Avatar-->
                    <div class="relative flex flex-col items-center gap-2">
                        <img src="https://cdn130.picsart.com/248878984010212.png"
                            class="w-20 sm:w-28 md:w-32 animate-floating"
                            style="image-rendering: pixelated;"/>
                        <div class="bg-black text-white px-3 py-1 text-sm sm:text-base font-pixel border-2 border-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)]">
                        CCST
                        </div>
                    </div>
                    <!--Greeting-->
                    <div>
                        <h1 class="text-2xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-3xl">
                            Welcome to Lectica,
                        </h1>
                        <!--Name-->
                        <div class="flex items-center justify-center gap-2 sm:justify-start">
                            <p
                                class="font-pixel animate-soft-bounce inline-block border-2 border-white bg-black px-4 py-2 text-2xl font-extrabold text-yellow-300 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] sm:text-3xl md:text-4xl"
                            >
                                {{ user.first_name }} {{ user.last_name }}
                            </p>
                            <p
                                class="text-4xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-5xl md:text-6xl"
                            >
                                !
                            </p>
                        </div>
                    </div>
                </div>
            <!--Divider-->
            <hr class="h-2 -mx-4 bg-red-500 border-2 border-black shadow-[2px_2px_0px_rgba(0,0,0,0.8)]">
            </div>

            <!--Main Content-->
            <div class="flex h-full flex-1 flex-col gap-4 pt-4 px-4 pb-0 lg:p-8 bg-gradient">
                <!-- Quick Actions -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-6 text-yellow-500 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] wave">
                            <span>Q</span><span>u</span><span>i</span><span>c</span><span>k</span><span>_</span><span>A</span><span>c</span>
                            <span>t</span><span>i</span><span>o</span><span>n</span><span>s</span></h2>
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-4 text-center">
                        <Link
                            v-if="isFacultyOrAdmin"
                            href="/files/verify"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors opacity-70 hover:opacity-90 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7eea7d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M9 11l3 3L22 4"></path>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                </svg>
                            </div>
                        <span class="font-medium">Verify Files</span>
                        </Link>
                    </div>
                        <Link
                            v-if="isFacultyOrAdmin"
                            href="/statistics"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors opacity-70 hover:opacity-90 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]"
                        >
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7eea7d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M3 3v18h18"></path>
                                    <path d="M9 17V9"></path>
                                    <path d="M15 17V13"></path>
                                    <path d="M21 17V11"></path>
                                </svg>
                            </div>
                            <span class="font-medium">View Statistics</span>
                        </Link>
                    </div>
                    <div class="grid grid-cols-4 md:grid-cols-4 gap-4 text-center">
                        <Link v-if="isStudent"
                            href="/files/create" class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors opacity-70 hover:opacity-90 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7eea7d"  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                                </div>
                            <span class="font-medium">Upload File</span>
                        </Link>

                        <Link v-if="isStudent"
                            href="/files"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors opacity-70 hover:opacity-90 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7eea7d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                </div>
                            <span class="font-medium">Browse Files</span>
                        </Link>

                        <Link
                            v-if="isStudent"
                            href="/history"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors opacity-70 hover:opacity-90 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7eea7d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <path d="M3 3h18v18H3z"></path>
                                    <path d="M9 17V7"></path>
                                    <path d="M15 17V11"></path>
                                </svg>
                            </div>
                        <span class="font-medium">History</span>
                        </Link>

                        <Link
                            v-if="isStudent"
                            href="/games/lobby"
                            class="flex flex-col items-center justify-center p-6 rounded-xl border border-border bg-card hover:bg-accent transition-colors opacity-70 hover:opacity-90 shadow-[4px_4px_0px_rgba(0,0,0,0.8)]">
                            <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M8 12h8"></path>
                                    <path d="M12 8v8"></path>
                                </svg>
                            </div>
                            <span class="font-medium">Game Lobby</span>
                        </Link>
                    </div>

                <!-- Recommendations Section -->
                <div class="mb-10 relative">
                    <h2 class="text-xl font-semibold mb-6 text-yellow-500 animate-soft-bounce [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                    Recommended Files
                    </h2>
                    <div v-for="category in recommendationCategories" :key="category.key" class="mb-10">
                        <!-- Only show categories that have files -->
                        <div v-if="recommendations[category.key]?.length">
                                <div class="flex items-center gap-2 mb-3">
                                    <component :is="category.icon" class="h-5 w-5 text-primary drop-shadow-[0_-2px_0_black]"/>
                                    <h3 class="text-lg font-medium [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">{{ category.title }}</h3>
                                </div>
                            <p class="text-sm mb-6 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">{{ category.description }}</p>
                            <!-- Scrollable Files -->
                            <div class="relative">
                                <!-- Left Button -->
                                <button @click="scrollLeft(category.key)"
                                    class="absolute left-0 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full z-10 hover:bg-black"><</button>
                                <!-- Right Button -->
                                <button @click="scrollRight(category.key)"
                                    class="absolute right-0 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full z-10 hover:bg-black">></button>
                                <!-- Scroll container -->
                                <div :ref="el => scrollContainers[category.key] = el as HTMLElement | null"class="flex gap-6 overflow-x-auto scrollbar-hide scroll-smooth px-10">
                                    <FileCard
                                        v-for="file in recommendations[category.key]"
                                        :key="file.id"
                                        :file="file"
                                        class="flex-shrink-0 w-auto max-w-xs"/>
                                </div>
                            </div>
                        </div>

                        <!-- Message when no recommendations are available -->
                        <div v-if="!hasAnyRecommendations" class="flex flex-col items-center justify-center p-8 text-center">
                            <div class="bg-muted mb-3 rounded-full p-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-muted-foreground h-6 w-6"
                                >
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
            </div>
            <!--Footer-->
                <footer class="w-full mt-0 bg-yellow-800 border-4 border-black font-pixel text-white text-center p-2 shadow-[4px_4px_0px_rgba(0,0,0,1)]">
                    <p class="text-lg">© 2025 <span class="px-2 py-1 bg-black text-yellow-300 border-2 border-white shadow-[2px_2px_0px_rgba(0,0,0,1)]">Lectica</span></p>
                </footer>
        </AppLayout>
    </div>
</template>
