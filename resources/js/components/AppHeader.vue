<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { NavigationMenu, NavigationMenuLink, navigationMenuTriggerStyle } from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Bell,
    ChartArea,
    FileChartLine,
    FileIcon,
    FolderOpen,
    Gamepad2,
    HelpCircle,
    LayoutGrid,
    Menu,
    Settings,
    Shield,
    Swords,
    Target,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

interface Notification {
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
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

interface Auth {
    user: User;
}

const auth = computed<Auth>(() => page.props.auth as Auth);

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const activeItemStyles = computed(() => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:text-[#7eea7d]' : ''));

const unreadNotificationsCount = ref(0);
const showNotificationsDropdown = ref(false);
const recentNotifications = ref<Notification[]>([]);

const fetchUnreadCount = async () => {
    try {
        const response = await axios.get('/notifications/unread-count');
        unreadNotificationsCount.value = response.data.count;
    } catch (error) {
        console.error('Error fetching unread notifications count:', error);
    }
};

const fetchRecentNotifications = async () => {
    try {
        const response = await axios.get('/notifications/recent');
        recentNotifications.value = response.data.notifications;
    } catch (error) {
        console.error('Error fetching recent notifications:', error);
    }
};

const markNotificationAsRead = async (notificationId: string) => {
    try {
        await axios.patch(`/notifications/${notificationId}/mark-as-read`);
        // Refresh both counts and recent notifications
        await fetchUnreadCount();
        await fetchRecentNotifications();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllNotificationsAsRead = async () => {
    try {
        await axios.patch('/notifications/mark-all-as-read');
        // Refresh both counts and recent notifications
        await fetchUnreadCount();
        await fetchRecentNotifications();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};

onMounted(() => {
    fetchUnreadCount();
    fetchRecentNotifications();
});

// Primary navigation items (always visible)
const primaryNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/home',
        icon: LayoutGrid,
    },
];

// Content management items
const contentNavItems: NavItem[] = [
    {
        title: 'All Files',
        href: '/files',
        icon: FileIcon,
    },
    {
        title: 'My Files',
        href: '/myfiles',
        icon: FileIcon,
    },
    {
        title: 'Collections',
        href: '/collections',
        icon: FolderOpen,
    },
];

// Gaming and activities items
const gameNavItems: NavItem[] = [
    {
        title: 'Quests',
        href: '/quests',
        icon: Target,
    },
    {
        title: 'Battles',
        href: '/battles',
        icon: Swords,
    },
    {
        title: 'Multiplayer',
        href: '/multiplayer-games',
        icon: Users,
    },
    {
        title: 'Leaderboards',
        href: '/leaderboards',
        icon: ChartArea,
    },
    {
        title: 'History',
        href: '/history',
        icon: FileChartLine,
    },
];

// Faculty/Admin management items
const facultyNavItems: NavItem[] = [
    ...(auth.value.user.user_role === 'faculty' || auth.value.user.user_role === 'admin'
        ? [
              {
                  title: 'Verify Files',
                  href: '/files/verify',
                  icon: FileIcon,
              },
              {
                  title: 'Manage Program and Tags',
                  href: '/faculty/update',
                  icon: Settings,
              },
          ]
        : []),
];

// Admin-only items
const adminNavItems: NavItem[] = [
    ...(auth.value.user.user_role === 'admin'
        ? [
              {
                  title: 'Statistics',
                  href: '/statistics',
                  icon: ChartArea,
              },
              {
                  title: 'User Roles',
                  href: '/admin/user-roles',
                  icon: Users,
              },
              {
                  title: 'Verifications',
                  href: '/admin/verifications',
                  icon: FileIcon,
              },
          ]
        : []),
];

// Support items
const supportNavItems: NavItem[] = [
    {
        title: 'FAQ',
        href: '/faq',
        icon: HelpCircle,
    },
];

// Legacy flat array for backward compatibility (used in mobile menu)
const mainNavItems: NavItem[] = [...primaryNavItems, ...contentNavItems, ...gameNavItems, ...facultyNavItems, ...adminNavItems, ...supportNavItems];

const rightNavItems: NavItem[] = [
    /*
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
    */
];

// Calculate experience progress percentage for current level
const getExperienceProgress = () => {
    const user = auth.value.user;
    const experience = user.experience || 0;
    const experienceToNextLevel = user.experience_to_next_level || 100;

    if (experienceToNextLevel === 0) {
        return 100;
    }

    return Math.round((experience / experienceToNextLevel) * 100);
};
</script>

<template>
    <div>
        <div class="border-sidebar-border/80 border-b bg-[#4d0a02]">
            <div class="md:max-w-8xl mx-auto flex h-16 items-center px-4">
                <!-- Mobile Menu -->
                <div class="xl:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="pixel-outline-icon h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-4">
                                    <!-- Primary Navigation -->
                                    <div class="space-y-1">
                                        <Link
                                            v-for="item in primaryNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>

                                    <!-- Content Section -->
                                    <div class="space-y-1">
                                        <div class="text-muted-foreground px-3 text-xs font-semibold tracking-wider uppercase">Content</div>
                                        <Link
                                            v-for="item in contentNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>

                                    <!-- Activities Section -->
                                    <div class="space-y-1">
                                        <div class="text-muted-foreground px-3 text-xs font-semibold tracking-wider uppercase">Activities</div>
                                        <Link
                                            v-for="item in gameNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>

                                    <!-- Faculty Section -->
                                    <div v-if="facultyNavItems.length > 0" class="space-y-1">
                                        <div class="text-muted-foreground px-3 text-xs font-semibold tracking-wider uppercase">
                                            Faculty Management
                                        </div>
                                        <Link
                                            v-for="item in facultyNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>

                                    <!-- Admin Section -->
                                    <div v-if="adminNavItems.length > 0" class="space-y-1">
                                        <div class="text-muted-foreground px-3 text-xs font-semibold tracking-wider uppercase">Administration</div>
                                        <Link
                                            v-for="item in adminNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>

                                    <!-- Support Section -->
                                    <div class="space-y-1">
                                        <div class="text-muted-foreground px-3 text-xs font-semibold tracking-wider uppercase">Support</div>
                                        <Link
                                            v-for="item in supportNavItems"
                                            :key="item.title"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                            :class="activeItemStyles(item.href)"
                                        >
                                            <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('home')" class="flex items-center gap-x-2 bg-[#4d0a02]">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full text-[#fce3aa] xl:flex xl:flex-1">
                    <NavigationMenu class="relative ml-10 flex h-full items-stretch">
                        <div class="flex h-full items-stretch space-x-2">
                            <!-- Home (always visible) -->
                            <div class="relative flex h-full items-center">
                                <NavigationMenuLink
                                    as-child
                                    :class="[navigationMenuTriggerStyle(), activeItemStyles(primaryNavItems[0].href), 'h-9 cursor-pointer px-3']"
                                >
                                    <Link :href="primaryNavItems[0].href" class="flex items-center">
                                        <component :is="primaryNavItems[0].icon" class="mr-2 h-4 w-4" />
                                        {{ primaryNavItems[0].title }}
                                    </Link>
                                </NavigationMenuLink>
                                <div
                                    v-if="isCurrentRoute(primaryNavItems[0].href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px dark:bg-[#fe9104]"
                                ></div>
                            </div>

                            <!-- Content Dropdown -->
                            <div class="group relative flex h-full items-center">
                                <Button variant="ghost" :class="['flex h-9 cursor-pointer items-center px-3', navigationMenuTriggerStyle()]">
                                    <FileIcon class="mr-2 h-4 w-4" />
                                    Content
                                </Button>
                                <div
                                    class="bg-popover text-popover-foreground absolute top-full left-0 z-50 hidden w-[300px] rounded-md border p-4 shadow-md group-hover:block hover:block"
                                >
                                    <div class="grid gap-1">
                                        <Link
                                            v-for="item in contentNavItems"
                                            :key="item.href"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <div>
                                                <div class="font-medium">{{ item.title }}</div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Activities Dropdown -->
                            <div class="group relative flex h-full items-center">
                                <Button variant="ghost" :class="['flex h-9 cursor-pointer items-center px-3', navigationMenuTriggerStyle()]">
                                    <Gamepad2 class="mr-2 h-4 w-4" />
                                    Activities
                                </Button>
                                <div
                                    class="bg-popover text-popover-foreground absolute top-full left-0 z-50 hidden w-[300px] rounded-md border p-4 shadow-md group-hover:block hover:block"
                                >
                                    <div class="grid gap-1">
                                        <Link
                                            v-for="item in gameNavItems"
                                            :key="item.href"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <div>
                                                <div class="font-medium">{{ item.title }}</div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Faculty Management Dropdown (faculty/admin only) -->
                            <div v-if="facultyNavItems.length > 0" class="group relative flex h-full items-center">
                                <Button variant="ghost" :class="['flex h-9 cursor-pointer items-center px-3', navigationMenuTriggerStyle()]">
                                    <Settings class="mr-2 h-4 w-4" />
                                    Faculty
                                </Button>
                                <div
                                    class="bg-popover text-popover-foreground absolute top-full left-0 z-50 hidden w-[300px] rounded-md border p-4 shadow-md group-hover:block hover:block"
                                >
                                    <div class="grid gap-1">
                                        <Link
                                            v-for="item in facultyNavItems"
                                            :key="item.href"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <div>
                                                <div class="font-medium">{{ item.title }}</div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Management Dropdown (admin only) -->
                            <div v-if="adminNavItems.length > 0" class="group relative flex h-full items-center">
                                <Button variant="ghost" :class="['flex h-9 cursor-pointer items-center px-3', navigationMenuTriggerStyle()]">
                                    <Shield class="mr-2 h-4 w-4" />
                                    Admin
                                </Button>
                                <div
                                    class="bg-popover text-popover-foreground absolute top-full left-0 z-50 hidden w-[300px] rounded-md border p-4 shadow-md group-hover:block hover:block"
                                >
                                    <div class="grid gap-1">
                                        <Link
                                            v-for="item in adminNavItems"
                                            :key="item.href"
                                            :href="item.href"
                                            class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <div>
                                                <div class="font-medium">{{ item.title }}</div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Support -->
                            <div class="relative flex h-full items-center">
                                <NavigationMenuLink
                                    as-child
                                    :class="[navigationMenuTriggerStyle(), activeItemStyles(supportNavItems[0].href), 'h-9 cursor-pointer px-3']"
                                >
                                    <Link :href="supportNavItems[0].href" class="flex items-center">
                                        <component :is="supportNavItems[0].icon" class="mr-2 h-4 w-4" />
                                        {{ supportNavItems[0].title }}
                                    </Link>
                                </NavigationMenuLink>
                                <div
                                    v-if="isCurrentRoute(supportNavItems[0].href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px dark:bg-[#fe9104]"
                                ></div>
                            </div>
                        </div>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <!-- <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer">
                            <Search class="size-5 opacity-80 group-hover:opacity-100" />
                        </Button> -->

                        <div class="hidden space-x-1 lg:flex">
                            <template v-for="item in rightNavItems" :key="item.title">
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="ghost" size="icon" as-child class="group h-9 w-9 cursor-pointer">
                                                <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <!-- Level and XP Display -->
                    <div
                        class="sm:active flex items-center space-x-3 rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-purple-50 px-3 py-1 dark:border-blue-700 dark:from-blue-900/20 dark:to-purple-900/20"
                    >
                        <!-- Level Badge -->
                        <div class="flex items-center space-x-1">
                            <span class="text-xs font-medium text-blue-600 dark:text-blue-400">LVL</span>
                            <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ auth.user.level || 1 }}</span>
                        </div>

                        <!-- XP Progress -->
                        <div class="flex items-center space-x-2">
                            <div class="flex flex-col">
                                <div class="h-2 w-16 rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div
                                        class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300"
                                        :style="{ width: `${getExperienceProgress()}%` }"
                                    ></div>
                                </div>
                                <div class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">
                                    {{ auth.user.experience || 0 }}/{{ auth.user.experience_to_next_level || 100 }} XP
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="focus-within:ring-primary relative size-10 rounded-full p-2 focus-within:ring-2"
                            >
                                <Bell class="h-5 w-5" />
                                <span
                                    v-if="unreadNotificationsCount > 0"
                                    class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white"
                                >
                                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                                </span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-80">
                            <div class="p-4">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                    <div class="flex items-center space-x-2">
                                        <button
                                            v-if="unreadNotificationsCount > 0"
                                            @click="markAllNotificationsAsRead"
                                            class="text-xs text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                        >
                                            Mark all as read
                                        </button>
                                        <Link
                                            :href="route('notifications.index')"
                                            class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            View all
                                        </Link>
                                    </div>
                                </div>
                                <div v-if="recentNotifications.length === 0" class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No notifications
                                </div>
                                <div v-else class="space-y-3">
                                    <div
                                        v-for="notification in recentNotifications"
                                        :key="notification.id"
                                        class="border-b border-gray-100 pb-3 last:border-b-0 dark:border-gray-700"
                                    >
                                        <div class="flex items-start justify-between space-x-3">
                                            <div class="flex min-w-0 flex-1 items-start space-x-3">
                                                <div v-if="!notification.read_at" class="mt-2 h-2 w-2 rounded-full bg-blue-600"></div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ notification.data.message }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ new Date(notification.created_at).toLocaleDateString() }}
                                                    </p>
                                                </div>
                                            </div>
                                            <button
                                                v-if="!notification.read_at"
                                                @click="markNotificationAsRead(notification.id)"
                                                class="rounded px-2 py-1 text-xs text-blue-600 transition-colors hover:bg-gray-100 hover:text-blue-800 dark:text-blue-400 dark:hover:bg-gray-800 dark:hover:text-blue-300"
                                            >
                                                Mark as read
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="focus-within:ring-primary relative size-10 w-auto rounded-full p-1 focus-within:ring-2"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.last_name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.first_name + ' ' + auth.user?.last_name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="border-sidebar-border/70 flex w-full border-b">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>
