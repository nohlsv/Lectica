<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, CheckCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';

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
    notifications: {
        data: Notification[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
    };
}

const props = defineProps<Props>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const markAsRead = (notificationId: string) => {
    router.patch(route('notifications.markAsRead', notificationId), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['notifications'],
        onSuccess: () => {
            toast.info('📖 Notification marked as read');
        },
        onError: () => {
            toast.error('Failed to mark notification as read');
        }
    });
};

const markAllAsRead = () => {
    router.patch(route('notifications.markAllAsRead'), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['notifications'],
        onSuccess: () => {
            toast.success('All notifications marked as read');
        },
        onError: () => {
            toast.error('Failed to mark all notifications as read');
        }
    });
};



const unreadCount = computed(() => {
    return props.notifications.data.filter((n) => !n.read_at).length;
});
</script>

<template>
    <Head title="Notifications" />
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-3 sm:space-y-6 sm:p-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-4 sm:mb-6">
                    <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                        <div class="flex items-center space-x-3 sm:space-x-4">
                            <Bell class="h-8 w-8 text-yellow-300 sm:h-10 sm:w-10" />
                            <div>
                                <h1 class="pixel-font text-xl font-bold text-yellow-200 sm:text-2xl lg:text-3xl">Notifications</h1>
                                <p class="text-sm text-gray-100 sm:text-base">{{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex justify-center sm:justify-end">
                            <button
                                v-if="unreadCount > 0"
                                @click="markAllAsRead"
                                class="pixel-outline flex h-12 items-center justify-center space-x-2 rounded bg-yellow-300 px-4 py-3 font-bold text-black transition-colors hover:bg-yellow-200 sm:h-auto sm:py-2"
                            >
                                <CheckCircle class="h-4 w-4" />
                                <span class="text-sm sm:text-base">Mark all as read</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="space-y-3 sm:space-y-4">
                    <div v-if="props.notifications.data.length === 0" class="py-8 text-center sm:py-12">
                        <Bell class="mx-auto h-10 w-10 text-gray-400 sm:h-12 sm:w-12" />
                        <h3 class="mt-2 text-base font-medium text-gray-100 sm:text-lg">No notifications</h3>
                        <p class="mt-1 text-sm text-gray-200 sm:text-base">You're all caught up!</p>
                    </div>

                    <div
                        v-for="notification in props.notifications.data"
                        :key="notification.id"
                        :class="[
                            'rounded-lg border border-gray-200 bg-white p-4 transition-all duration-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 sm:p-6',
                            !notification.read_at ? 'border-l-4 border-l-blue-500' : '',
                        ]"
                    >
                        <div class="flex flex-col space-y-3 sm:flex-row sm:items-start sm:justify-between sm:space-x-4 sm:space-y-0">
                            <div class="flex flex-1 items-start space-x-2 sm:space-x-3">
                                <div v-if="!notification.read_at" class="mt-1 h-3 w-3 rounded-full bg-blue-600 sm:mt-2"></div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white sm:text-lg">
                                        {{ 
                                            notification.type === 'App\\Notifications\\FileDeniedNotification' ? 'File Denied' :
                                            notification.type === 'App\\Notifications\\ContentGenerationComplete' ? 'Content Generation Complete' :
                                            'Notification'
                                        }}
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 sm:text-sm">
                                        {{ formatDate(notification.created_at) }}
                                    </p>
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 sm:text-base">{{ notification.data.message }}</p>

                                    <!-- File-specific information -->
                                    <div
                                        v-if="notification.data.file_name"
                                        class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                        <p class="text-sm font-medium text-gray-900 dark:text-white sm:text-base">File: {{ notification.data.file_name }}</p>
                                        <p v-if="notification.data.denial_reason" class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            <strong>Reason:</strong> {{ notification.data.denial_reason }}
                                        </p>
                                    </div>

                                    <!-- Action button -->
                                    <div v-if="notification.data.file_id" class="mt-3">
                                        <Link
                                            :href="route('files.show', notification.data.file_id)"
                                            class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm text-white transition-colors hover:bg-blue-700 sm:px-4 sm:text-base"
                                        >
                                            View File
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status and Actions -->
                            <div class="flex flex-row items-center justify-between sm:flex-col sm:items-end sm:space-y-2">
                                <span
                                    :class="[
                                        'rounded px-3 py-1 text-sm whitespace-nowrap sm:px-2 sm:text-xs',
                                        notification.read_at
                                            ? 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300'
                                            : 'bg-blue-100 font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    ]"
                                >
                                    {{ notification.read_at ? 'Read' : 'Unread' }}
                                </span>
                                <button
                                    v-if="!notification.read_at"
                                    @click="markAsRead(notification.id)"
                                    class="rounded px-3 py-2 text-sm text-blue-600 transition-colors hover:bg-blue-50 hover:text-blue-800 dark:text-blue-400 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 sm:px-2 sm:py-1 sm:text-xs"
                                >
                                    Mark as read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="props.notifications.links && props.notifications.links.length > 3" class="mt-6 flex justify-center sm:mt-8">
                    <div class="flex space-x-1 sm:space-x-2">
                        <button
                            v-for="link in props.notifications.links"
                            :key="link.label"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            class="rounded px-3 py-2 text-sm sm:px-4 sm:text-base"
                            :class="{
                                'bg-blue-500 text-white': link.active,
                                'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600': !link.active && link.url,
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500': !link.url,
                            }"
                            v-html="link.label"
                        ></button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.pixel-font {
    font-family: 'Courier New', monospace;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.pixel-outline {
    box-shadow:
        0 0 0 1px currentColor,
        2px 2px 0 0 rgba(0, 0, 0, 0.5);
}
</style>
