<script setup lang="ts">
import FileCard from '@/components/FileCard.vue';
import StudyStreakHeatmap from '@/components/StudyStreakHeatmap.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type SharedData, type User } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDateFormat } from '@vueuse/core';
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

// File interaction functions
const toggleStar = async (file: File) => {
    try {
        await router.post(
            route('files.star', { file: file.id }),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    file.is_starred = !file.is_starred;
                    file.star_count = file.is_starred ? (file.star_count || 0) + 1 : (file.star_count || 0) - 1;
                },
            }
        );
    } catch (error) {
        console.error('Error toggling star:', error);
    }
};

// Get college mascot info
const getCollegeMascot = (college: string | undefined) => {
    const collegeMap: Record<string, { abbr: string; mascot: string; image: string }> = {
        'College of Computer Studies': {
            abbr: 'CCST',
            mascot: 'WIZARDS', 
            image: '/images/mascots/CCST WIZARDS.png'
        },
        'College of Engineering and Architecture': {
            abbr: 'CEA',
            mascot: 'FALCONS',
            image: '/images/mascots/CEA FALCONS.png'
        },
        'College of Business and Accountancy': {
            abbr: 'CBA',
            mascot: 'PHOENIX',
            image: '/images/mascots/CBA PHOENIX.png'
        },
        'College of Technology': {
            abbr: 'CTEC',
            mascot: 'DRAGONS',
            image: '/images/mascots/CTEC DRAGONS.png'
        },
        'College of Allied Health and Sciences': {
            abbr: 'CAHS',
            mascot: 'WOLVES',
            image: '/images/mascots/CAHS WOLVES.png'
        },
        'College of Arts and Science': {
            abbr: 'COAS',
            mascot: 'KNIGHTS',
            image: '/images/mascots/COAS KNIGHTS.png'
        }
    };
    return collegeMap[college || ''] || collegeMap['College of Computer Studies'];
};

const collegeMascot = computed(() => {
    const college = user.college || user.program?.college;
    return getCollegeMascot(college);
});

const collegeAbbreviation = computed(() => {
    return collegeMascot.value.abbr;
});

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
    {
        title: 'Manage Program and Tags',
        href: '/faculty/update',
        description: 'Manage programs and tags',
        icon: 'settings',
        color: 'orange',
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
        settings:
            'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z',
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
            <div class="bg-lectica flex max-h-[300px] w-full flex-1 flex-col gap-4 px-2 sm:px-4 pt-4 pb-0">
                <!--Welcome Section-->
                <div
                    class="mb-10 flex min-h-[215px] w-full flex-col items-center justify-center gap-6 rounded-xl p-6 text-center sm:flex-row sm:text-left"
                >
                    <!--Avatar-->
                    <div class="relative flex flex-col items-center gap-2">
                        <img
                            :src="collegeMascot.image"
                            :alt="`${collegeMascot.mascot} mascot`"
                            class="animate-floating w-20 sm:w-24 md:w-28 lg:w-32"
                            style="image-rendering: pixelated"
                            @error="($event.target as HTMLImageElement).src = 'https://cdn130.picsart.com/248878984010212.png'"
                        />
                        <div
                            class="font-pixel border-2 border-white bg-black px-2 sm:px-3 py-1 text-xs sm:text-sm text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)]"
                        >
                            {{ collegeAbbreviation }}
                        </div>
                    </div>
                    <!--Greeting-->
                    <div>
                        <h1 class="text-2xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-3xl">
                            Welcome to Lectica,
                        </h1>
                        <!--Name-->
                        <div class="flex items-start justify-start gap-1 sm:gap-2 flex-wrap">
                            <p
                                class="font-pixel animate-soft-bounce inline-block border-2 border-white bg-black px-2 sm:px-4 py-1 sm:py-2 text-sm sm:text-2xl lg:text-3xl xl:text-4xl font-extrabold text-yellow-300 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] break-words leading-tight"
                            >
                                {{ user.first_name }} {{ user.last_name }}
                            </p>
                            <p
                                class="text-lg sm:text-4xl lg:text-5xl xl:text-6xl font-bold text-white [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] leading-none"
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
            <div class="bg-gradient flex h-full flex-1 flex-col gap-4 px-2 pt-4 pb-0 sm:px-4 lg:p-8">
                <!-- Quick Actions -->
                <div class="mb-6 sm:mb-8">
                    <div class="mb-4 flex flex-col items-start justify-between gap-2 sm:mb-6 sm:flex-row sm:items-center">
                        <h2
                            class="wave text-base font-semibold text-yellow-500 [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black] sm:text-lg md:text-xl"
                        >
                            <span>Q</span><span>u</span><span>i</span><span>c</span><span>k</span><span>_</span><span>A</span><span>c</span
                            ><span>t</span><span>i</span><span>o</span><span>n</span><span>s</span>
                        </h2>
                        <div class="sm:block">
                            <span
                                class="rounded-full border-2 border-yellow-400 bg-yellow-500 px-2 py-1 text-xs font-bold text-black shadow-[2px_2px_0px_rgba(0,0,0,0.8)] sm:px-3"
                            >
                                {{ isAdmin ? 'ADMIN' : isFaculty ? 'FACULTY' : 'STUDENT' }}
                            </span>
                        </div>
                    </div>

                    <!-- Role-specific action grid - Responsive grid -->
                    <div class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                        <Link
                            v-for="action in currentUserActions"
                            :key="action.title"
                            :href="action.href"
                            :class="[
                                'group relative flex flex-col items-center justify-center rounded-lg border-2 p-2 shadow-[2px_2px_0px_rgba(0,0,0,0.8)] transition-all duration-200 hover:translate-y-[-1px] hover:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] sm:rounded-xl sm:p-3 md:p-4 sm:shadow-[4px_4px_0px_rgba(0,0,0,0.8)] hover:sm:shadow-[6px_6px_0px_rgba(0,0,0,0.8)]',
                                getColorClasses(action.color),
                            ]"
                        >
                            <!-- Icon - Responsive sizing -->
                            <div class="mb-1 flex h-6 w-6 items-center justify-center rounded-full bg-white/50 shadow-lg backdrop-blur-sm sm:mb-2 sm:h-8 sm:w-8 md:h-10 md:w-10">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="12"
                                    height="12"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="text-gray-700 dark:text-gray-300 sm:h-4 sm:w-4 md:h-5 md:w-5"
                                >
                                    <path :d="getIconSvg(action.icon)" />
                                </svg>
                            </div>

                            <!-- Title - Responsive text sizing -->
                            <span class="mb-0.5 text-center text-xs font-semibold text-gray-800 dark:text-gray-200 sm:mb-1 sm:text-sm">
                                {{ action.title }}
                            </span>

                            <!-- Description - Hidden on small screens, shown on larger -->
                            <span class="hidden text-center text-xs text-gray-600 dark:text-gray-400 sm:block">
                                {{ action.description }}
                            </span>

                            <!-- Hover effect -->
                            <div
                                class="absolute inset-0 rounded-lg bg-white/10 opacity-0 transition-opacity duration-200 group-hover:opacity-100 sm:rounded-xl"
                            ></div>
                        </Link>
                    </div>

                    <!-- Role-specific tip -->
                    <div class="mt-2 rounded-lg border border-yellow-200 bg-yellow-50 p-2 dark:border-yellow-700 dark:bg-yellow-900/20 sm:mt-4 sm:p-3">
                        <p class="text-xs text-yellow-800 dark:text-yellow-200 sm:text-sm">
                            <span class="font-semibold">💡 Tip:</span>
                            <span v-if="isStudent">Complete quests and battles to level up and unlock new content!</span>
                            <span v-else-if="isFaculty">Use the verification system to moderate and approve student submissions.</span>
                            <span v-else-if="isAdmin">Monitor platform usage through statistics and manage user permissions.</span>
                        </p>
                    </div>
                </div>

                <!-- Notifications Section -->
                <div class="mb-6 w-full sm:mb-8">
                    <div class="bg-container rounded-lg p-3 sm:p-4">
                        <div class="mb-3 flex items-center justify-between sm:mb-4">
                            <div class="flex items-center space-x-1 sm:space-x-2">
                                <Bell class="h-4 w-4 text-blue-600 dark:text-blue-400 sm:h-5 sm:w-5" />
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white sm:text-base lg:text-lg">Recent Notifications</h3>
                                <span
                                    v-if="unreadNotificationsCount > 0"
                                    class="flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white sm:h-5 sm:w-5"
                                >
                                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                                </span>
                            </div>
                            <Link
                                :href="route('notifications.index')"
                                class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 sm:text-sm"
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
                                    class="scrollbar-hide flex gap-4 overflow-x-auto scroll-smooth px-10 sm:gap-6"
                                >
                                    <div
                                        v-for="file in recommendations[category.key]"
                                        :key="file.id"
                                        class="group relative w-72 flex-shrink-0 overflow-hidden rounded-lg border border-white/20 bg-white/5 backdrop-blur-sm transition-all duration-200 hover:border-white/40 hover:bg-white/10 hover:shadow-lg sm:w-80"
                                    >
                                        <!-- File Card Content -->
                                        <Link :href="`/files/${file.id}`" class="block p-4">
                                            <!-- Header with file icon and verification status -->
                                            <div class="mb-3 flex items-start justify-between">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded bg-blue-500/20">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                    <div v-if="file.verified" class="rounded-full bg-green-500/20 p-1">
                                                        <svg class="h-3 w-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- File title -->
                                            <h3 class="mb-2 line-clamp-2 text-sm font-semibold text-white group-hover:text-yellow-400 sm:text-base">
                                                {{ file.name }}
                                            </h3>

                                            <!-- Description -->
                                            <p class="mb-3 line-clamp-2 text-xs text-white/70 sm:text-sm">
                                                {{ file.description || 'No description provided' }}
                                            </p>

                                            <!-- File metadata -->
                                            <div class="mb-3 space-y-1 text-xs text-white/60">
                                                <div class="flex items-center justify-between">
                                                    <span>By {{ file.user.first_name }} {{ file.user.last_name }}</span>
                                                    <span>{{ useDateFormat(file.created_at, 'MMM D, YYYY').value }}</span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span>⭐ {{ file.star_count || 0 }} stars</span>
                                                    <span v-if="file.verified" class="text-green-400">✓ Verified</span>
                                                </div>
                                            </div>

                                            <!-- Tags (if any) -->
                                            <div v-if="file.tags && file.tags.length > 0" class="mb-3 flex flex-wrap gap-1">
                                                <span
                                                    v-for="tag in file.tags.slice(0, 3)"
                                                    :key="tag.id"
                                                    class="rounded bg-purple-500/20 px-2 py-0.5 text-xs text-purple-300"
                                                >
                                                    {{ tag.name }}
                                                </span>
                                                <span v-if="file.tags.length > 3" class="text-xs text-white/50">
                                                    +{{ file.tags.length - 3 }} more
                                                </span>
                                            </div>
                                        </Link>

                                        <!-- Action buttons -->
                                        <div class="border-t border-white/10 p-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex gap-1 sm:gap-2">
                                                    <Link
                                                        :href="`/files/${file.id}`"
                                                        class="rounded bg-blue-500/20 px-3 py-2 text-xs text-blue-400 transition-colors hover:bg-blue-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                        title="View file"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </Link>
                                                </div>

                                                <div class="flex gap-1 sm:gap-2">
                                                    <button
                                                        @click.prevent="toggleStar(file)"
                                                        class="rounded bg-yellow-500/20 px-3 py-2 text-xs text-yellow-400 transition-colors hover:bg-yellow-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                        title="Star file"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-3 w-3 sm:h-4 sm:w-4" :class="file.is_starred ? 'fill-current' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                        </svg>
                                                    </button>
                                                    <Link
                                                        :href="route('collections.index')"
                                                        class="rounded bg-purple-500/20 px-3 py-2 text-xs text-purple-400 transition-colors hover:bg-purple-500/30 sm:px-4 sm:py-2 sm:text-sm"
                                                        title="View collections"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                        </svg>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
        </AppLayout>
    </div>
</template>
