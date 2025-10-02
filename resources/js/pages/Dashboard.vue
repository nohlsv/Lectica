<script setup lang="ts">
import FileCard from '@/components/FileCard.vue';
import StudyStreakHeatmap from '@/components/StudyStreakHeatmap.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type SharedData, type User } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { Bell, GraduationCapIcon, TagsIcon, TrendingUpIcon, UsersIcon } from 'lucide-vue-next';
import { computed, onMounted, reactive, ref } from 'vue';

const scrollContainers = reactive<Record<string, HTMLElement | null>>({});

const scrollLeft = (key: string) => {
    const container = scrollContainers[key];
    if (container) {
        container.scrollBy({ left: -300, behavior: 'smooth' });
    }
};

const scrollRight = (key: string) => {
    const container = scrollContainers[key];
    if (container) {
        container.scrollBy({ left: 300, behavior: 'smooth' });
    }
};

// Notifications
const recentNotifications = ref<DashboardNotification[]>([]);
const unreadNotificationsCount = ref(0);

const fetchRecentNotifications = async () => {
    try {
        const response = await axios.get('/notifications/recent');
        recentNotifications.value = response.data.notifications;
    } catch (error) {
        console.error('Error fetching recent notifications:', error);
    }
};

const fetchUnreadCount = async () => {
    try {
        const response = await axios.get('/notifications/unread-count');
        unreadNotificationsCount.value = response.data.count;
    } catch (error) {
        console.error('Error fetching unread notifications count:', error);
    }
};

onMounted(() => {
    fetchRecentNotifications();
    fetchUnreadCount();
});

interface RecommendationGroup {
    [key: string]: File[];
}

interface HeatmapData {
    date: string;
    value: number;
    count: number;
    points: number;
}

interface StreakStats {
    current_streak: number;
    longest_streak: number;
    total_study_days: number;
    heatmap_data: HeatmapData[];
}

interface DashboardNotification {
    id: string;
    type: string;
    notifiable_type: string;
    notifiable_id: number;
    data: {
        file_id?: number;
        file_name?: string;
        denial_reason?: string;
        message: string;
    };
    read_at: string | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    recommendations: RecommendationGroup;
    streakStats: StreakStats;
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
const isFaculty = computed(() => user.user_role === 'faculty');
const isAdmin = computed(() => user.user_role === 'admin');

// Define role-specific quick actions
const studentActions = [
    {
        title: 'Upload File',
        href: '/files/create',
        description: 'Share your study materials',
        icon: 'upload',
        color: 'blue',
    },
    {
        title: 'Browse Files',
        href: '/files',
        description: 'Find study resources',
        icon: 'browse',
        color: 'green',
    },
    {
        title: 'Game Battles',
        href: '/battles',
        description: 'Challenge yourself',
        icon: 'sword',
        color: 'red',
    },
    {
        title: 'Multiplayer',
        href: '/multiplayer-games',
        description: 'Play with friends',
        icon: 'users',
        color: 'purple',
    },
    {
        title: 'Quests',
        href: '/quests',
        description: 'Complete challenges',
        icon: 'target',
        color: 'orange',
    },
    {
        title: 'My Progress',
        href: '/history',
        description: 'View your stats',
        icon: 'chart',
        color: 'indigo',
    },
];

const facultyActions = [
    {
        title: 'Verify Files',
        href: '/files/verify',
        description: 'Review student uploads',
        icon: 'check',
        color: 'green',
    },
    {
        title: 'My Files',
        href: '/myfiles',
        description: 'Manage your content',
        icon: 'folder',
        color: 'blue',
    },
    {
        title: 'Upload Resource',
        href: '/files/create',
        description: 'Share teaching materials',
        icon: 'upload',
        color: 'indigo',
    },
    {
        title: 'Collections',
        href: '/collections',
        description: 'Organize file sets',
        icon: 'collection',
        color: 'purple',
    },
    {
        title: 'Student Leaderboards',
        href: '/leaderboards',
        description: 'Monitor student performance',
        icon: 'trophy',
        color: 'yellow',
    },
    {
        title: 'Browse All Files',
        href: '/files',
        description: 'Explore all resources',
        icon: 'browse',
        color: 'gray',
    },
];

const adminActions = [
    {
        title: 'User Management',
        href: '/admin/user-roles',
        description: 'Manage user roles',
        icon: 'users',
        color: 'red',
    },
    {
        title: 'System Statistics',
        href: '/statistics',
        description: 'View platform analytics',
        icon: 'chart',
        color: 'blue',
    },
    {
        title: 'Verify Files',
        href: '/files/verify',
        description: 'Moderate content',
        icon: 'check',
        color: 'green',
    },
    {
        title: 'All Files',
        href: '/files',
        description: 'System-wide file access',
        icon: 'browse',
        color: 'indigo',
    },
    {
        title: 'Collections',
        href: '/collections',
        description: 'Manage file collections',
        icon: 'collection',
        color: 'purple',
    },
    {
        title: 'Leaderboards',
        href: '/leaderboards',
        description: 'Platform engagement',
        icon: 'trophy',
        color: 'yellow',
    },
    {
        title: 'FAQ',
        href: '/faq',
        description: 'Help & Guidelines',
        icon: 'help',
        color: 'blue',
    },
];

// Get current user's actions based on role
const currentUserActions = computed(() => {
    if (isAdmin.value) return adminActions;
    if (isFaculty.value) return facultyActions;
    return studentActions;
});

// Helper function to get icon SVG
const getIconSvg = (iconName: string) => {
    const icons = {
        upload: 'M5 12h14 M12 5v14',
        browse: 'M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z M14 2v6h6',
        sword: 'M6.5 6.5L17.5 17.5 M6.5 17.5L17.5 6.5',
        users: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0 M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',
        target: 'M12 2L2 7l10 5 10-5-10-5z M2 17l10 5 10-5 M2 12l10 5 10-5',
        chart: 'M3 3v18h18 M9 17V9 M15 17v-4 M21 17v-6',
        check: 'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11',
        folder: 'M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z',
        collection: 'M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z M3 6h18 M8 6v4 M16 6v4',
        trophy: 'M6 9H4.5a2.5 2.5 0 0 1 0-5H6 M18 9h1.5a2.5 2.5 0 0 0 0-5H18 M12 12.5a4.5 4.5 0 0 0 0-9 4.5 4.5 0 0 0 0 9z M12 12.5v7.5',
        help: 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3 M12 17h.01',
    };
    return icons[iconName] || icons.browse;
};

// Helper function to get color classes
const getColorClasses = (color: string) => {
    const colors = {
        blue: 'border-blue-200 bg-blue-50 hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/20 dark:hover:bg-blue-800/30',
        green: 'border-green-200 bg-green-50 hover:bg-green-100 dark:border-green-700 dark:bg-green-900/20 dark:hover:bg-green-800/30',
        red: 'border-red-200 bg-red-50 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:hover:bg-red-800/30',
        purple: 'border-purple-200 bg-purple-50 hover:bg-purple-100 dark:border-purple-700 dark:bg-purple-900/20 dark:hover:bg-purple-800/30',
        orange: 'border-orange-200 bg-orange-50 hover:bg-orange-100 dark:border-orange-700 dark:bg-orange-900/20 dark:hover:bg-orange-800/30',
        indigo: 'border-indigo-200 bg-indigo-50 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/20 dark:hover:bg-indigo-800/30',
        yellow: 'border-yellow-200 bg-yellow-50 hover:bg-yellow-100 dark:border-yellow-700 dark:bg-yellow-900/20 dark:hover:bg-yellow-800/30',
        gray: 'border-gray-200 bg-gray-50 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900/20 dark:hover:bg-gray-800/30',
    };
    return colors[color] || colors.gray;
};
</script>

<template>
    <div class="dark:bg-[#161615]">
        <Head title="Home" />
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="bg-lectica flex max-h-[300px] w-full flex-1 flex-col gap-4 px-4 pt-4 pb-0">
                <!--Welcome Section-->
                <div
                    class="mb-10 flex min-h-[215px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left"
                >
                    <!--Avatar-->
                    <div class="relative flex flex-col items-center gap-2">
                        <img
                            src="https://cdn130.picsart.com/248878984010212.png"
                            class="animate-floating w-20 sm:w-28 md:w-32"
                            style="image-rendering: pixelated"
                        />
                        <div
                            class="font-pixel border-2 border-white bg-black px-3 py-1 text-sm text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] sm:text-base"
                        >
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
                <hr class="-mx-4 h-2 border-2 border-black bg-red-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
            </div>
            <!--Main Content-->
            <div class="bg-gradient flex h-full flex-1 flex-col gap-4 px-4 pt-4 pb-0 lg:p-8">
                <!-- Quick Actions -->
                <div class="mb-8">
                    <div class="mb-6 flex items-center justify-between">
                        <h2
                            class="wave text-lg font-semibold text-yellow-500 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-xl"
                        >
                            <span>Q</span><span>u</span><span>i</span><span>c</span><span>k</span><span>_</span><span>A</span><span>c</span
                            ><span>t</span><span>i</span><span>o</span><span>n</span><span>s</span>
                        </h2>
                        <div class="hidden sm:block">
                            <span
                                class="rounded-full border-2 border-yellow-400 bg-yellow-500 px-3 py-1 text-xs font-bold text-black shadow-[2px_2px_0px_rgba(0,0,0,0.8)]"
                            >
                                {{ isAdmin ? 'ADMIN' : isFaculty ? 'FACULTY' : 'STUDENT' }}
                            </span>
                        </div>
                    </div>

                    <!-- Role-specific action grid -->
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                        <Link
                            v-for="action in currentUserActions"
                            :key="action.title"
                            :href="action.href"
                            :class="[
                                'group relative flex flex-col items-center justify-center rounded-xl border-2 p-4 shadow-[4px_4px_0px_rgba(0,0,0,0.8)] transition-all duration-200 hover:translate-y-[-2px] hover:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]',
                                getColorClasses(action.color),
                            ]"
                        >
                            <!-- Icon -->
                            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white/50 shadow-lg backdrop-blur-sm">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-gray-700 dark:text-gray-300"
                                >
                                    <path :d="getIconSvg(action.icon)" />
                                </svg>
                            </div>

                            <!-- Title -->
                            <span class="mb-1 text-center text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ action.title }}
                            </span>

                            <!-- Description -->
                            <span class="text-center text-xs text-gray-600 dark:text-gray-400">
                                {{ action.description }}
                            </span>

                            <!-- Hover effect -->
                            <div
                                class="absolute inset-0 rounded-xl bg-white/10 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                            ></div>
                        </Link>
                    </div>

                    <!-- Role-specific tip -->
                    <div class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-700 dark:bg-yellow-900/20">
                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                            <span class="font-semibold">💡 Tip:</span>
                            <span v-if="isStudent">Complete quests and battles to level up and unlock new content!</span>
                            <span v-else-if="isFaculty">Use the verification system to moderate and approve student submissions.</span>
                            <span v-else-if="isAdmin">Monitor platform usage through statistics and manage user permissions.</span>
                        </p>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div class="mb-8 w-full">
                    <div class="bg-container rounded-lg p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <Bell class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Notifications</h3>
                                <span
                                    v-if="unreadNotificationsCount > 0"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white"
                                >
                                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                                </span>
                            </div>
                            <Link
                                :href="route('notifications.index')"
                                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                View All
                            </Link>
                        </div>

                        <div v-if="recentNotifications.length === 0" class="py-8 text-center">
                            <Bell class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No notifications yet</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="notification in recentNotifications.slice(0, 3)"
                                :key="notification.id"
                                class="flex items-start space-x-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                                :class="{
                                    'border-blue-200 bg-blue-50 dark:border-blue-600 dark:bg-blue-900/20': !notification.read_at,
                                    'dark:bg-gray-800/50': notification.read_at,
                                }"
                            >
                                <div v-if="!notification.read_at" class="mt-2 h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ notification.data.message }}
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                                        {{ new Date(notification.created_at).toLocaleDateString() }}
                                    </p>
                                    <div v-if="notification.data.file_name" class="mt-1">
                                        <Link
                                            v-if="notification.data.file_id"
                                            :href="route('files.show', notification.data.file_id)"
                                            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            View {{ notification.data.file_name }}
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Study Streaks Section -->
                <div class="mb-8 w-full">
                    <StudyStreakHeatmap :streak-data="streakStats" />
                </div>

                <!-- Recommendations Section -->
                <div class="relative mt-4 mb-10">
                    <h2
                        class="animate-soft-bounce mb-6 text-xl font-semibold text-yellow-500 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                    >
                        Recommended Files
                    </h2>
                    <div v-for="category in recommendationCategories" :key="category.key" class="mb-10">
                        <!-- Only show categories that have files -->
                        <div v-if="recommendations[category.key]?.length">
                            <div class="mb-3 flex items-center gap-2">
                                <component :is="category.icon" class="text-primary h-5 w-5 drop-shadow-[0_-2px_0_black]" />
                                <h3 class="text-lg font-medium [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                                    {{ category.title }}
                                </h3>
                            </div>
                            <p class="mb-6 text-sm [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">{{ category.description }}</p>
                            <!-- Scrollable Files -->
                            <div class="relative">
                                <!-- Left Button -->
                                <button
                                    @click="scrollLeft(category.key)"
                                    class="absolute top-1/2 left-0 z-10 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black"
                                >
                                    <
                                </button>
                                <!-- Right Button -->
                                <button
                                    @click="scrollRight(category.key)"
                                    class="absolute top-1/2 right-0 z-10 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white hover:bg-black"
                                >
                                    >
                                </button>
                                <!-- Scroll container -->
                                <div
                                    :ref="(el) => (scrollContainers[category.key] = el as HTMLElement | null)"
                                    class="scrollbar-hide flex gap-6 overflow-x-auto scroll-smooth px-10"
                                >
                                    <FileCard
                                        v-for="file in recommendations[category.key]"
                                        :key="file.id"
                                        :file="file"
                                        class="w-auto max-w-xs flex-shrink-0"
                                    />
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
            <footer class="font-pixel mt-0 w-full border-4 border-black bg-yellow-800 p-2 text-center text-white shadow-[4px_4px_0px_rgba(0,0,0,1)]">
                <p class="text-lg">
                    © 2025 <span class="border-2 border-white bg-black px-2 py-1 text-yellow-300 shadow-[2px_2px_0px_rgba(0,0,0,1)]">Lectica</span>
                </p>
            </footer>
        </AppLayout>
    </div>
</template>
