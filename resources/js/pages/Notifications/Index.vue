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
        <div class="bg-gradient min-h-screen space-y-6 p-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Bell class="h-8 w-8 text-yellow-300" />
                        <div>
                            <h1 class="pixel-font text-3xl font-bold text-yellow-200">Notifications</h1>
                            <p class="text-gray-100">{{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="pixel-outline flex items-center space-x-2 rounded bg-yellow-300 px-4 py-2 font-bold text-black transition-colors hover:bg-yellow-200"
                    >
                        <CheckCircle class="h-4 w-4" />
                        <span>Mark all as read</span>
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="space-y-4">
                    <div v-if="props.notifications.data.length === 0" class="py-12 text-center">
                        <Bell class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-100">No notifications</h3>
                        <p class="mt-1 text-sm text-gray-200">You're all caught up!</p>
                    </div>

                    <div
                        v-for="notification in props.notifications.data"
                        :key="notification.id"
                        :class="[
                            'rounded-lg border border-gray-200 bg-white p-6 transition-all duration-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800',
                            !notification.read_at ? 'border-l-4 border-l-blue-500' : '',
                        ]"
                    >
                        <div class="flex items-start justify-between space-x-4">
                            <div class="flex flex-1 items-start space-x-3">
                                <div v-if="!notification.read_at" class="mt-2 h-3 w-3 rounded-full bg-blue-600"></div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ notification.type === 'App\\Notifications\\FileDeniedNotification' ? 'File Denied' : 'Notification' }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(notification.created_at) }}
                                    </p>
                                    <p class="mt-2 text-gray-700 dark:text-gray-300">{{ notification.data.message }}</p>

                                    <!-- File-specific information -->
                                    <div
                                        v-if="notification.data.file_name"
                                        class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">File: {{ notification.data.file_name }}</p>
                                        <p v-if="notification.data.denial_reason" class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            <strong>Reason:</strong> {{ notification.data.denial_reason }}
                                        </p>
                                    </div>

                                    <!-- Action button -->
                                    <div v-if="notification.data.file_id" class="mt-3">
                                        <Link
                                            :href="route('files.show', notification.data.file_id)"
                                            class="inline-flex items-center text-sm text-blue-600 underline transition-colors hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            View File
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span
                                    :class="[
                                        'rounded px-2 py-1 text-xs whitespace-nowrap',
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
                                    class="rounded px-2 py-1 text-xs text-blue-600 transition-colors hover:bg-blue-50 hover:text-blue-800 dark:text-blue-400 dark:hover:bg-blue-900/20 dark:hover:text-blue-300"
                                >
                                    Mark as read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <nav class="flex space-x-2">
                        <button
                            v-for="link in props.notifications.links"
                            :key="link.label"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            class="rounded-md border px-3 py-1.5 text-sm sm:px-4 sm:py-2 sm:text-base"
                            :class="{
                                'border-blue-500 bg-blue-600 text-white': link.active,
                                'border-gray-300 bg-white text-gray-700 hover:bg-gray-50': !link.active && link.url,
                                'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400': !link.url,
                            }"
                            v-html="link.label"
                        ></button>
                    </nav>
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
