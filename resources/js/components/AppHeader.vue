<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChartArea, FileChartLine, FileIcon, FolderOpen, LayoutGrid, Menu, Swords, Target } from 'lucide-vue-next';
import { computed } from 'vue';

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

const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/home',
        icon: LayoutGrid,
    },
    {
        title: 'Files',
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
        title: 'History',
        href: '/history',
        icon: FileChartLine,
    },
    // Add faculty/admin pages conditionally
    ...(auth.value.user.user_role === 'faculty' || auth.value.user.user_role === 'admin'
        ? [
              {
                  title: 'Verify Files',
                  href: '/files/verify',
                  icon: FileIcon,
              },
              {
                  title: 'Statistics',
                  href: '/statistics',
                  icon: ChartArea,
              },
          ]
        : []),
];

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
        <div class="border-sidebar-border/80 border-b">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="hover:bg-accent flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        {{ item.title }}
                                    </Link>
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

                <Link :href="route('home')" class="flex items-center gap-x-2 bg-[#4d0a02] ">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1 text-[#fce3aa] pixel-outline">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <Link :href="item.href">
                                    <NavigationMenuLink
                                        :class="[navigationMenuTriggerStyle(), activeItemStyles(item.href), 'h-9 cursor-pointer px-3']"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                        {{ item.title }}
                                    </NavigationMenuLink>
                                </Link>
                                <div
                                    v-if="isCurrentRoute(item.href)"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px dark:bg-[#fe9104]"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
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
                        class="hidden items-center space-x-3 rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-purple-50 px-3 py-1 md:flex dark:border-blue-700 dark:from-blue-900/20 dark:to-purple-900/20"
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
