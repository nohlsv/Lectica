<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, CheckCircle, X } from 'lucide-vue-next';
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
        minute: '2-digit'
    });
};

const markAsRead = async (notificationId: string) => {
    try {
        await router.patch(route('notifications.markAsRead', notificationId));
        router.reload({ only: ['notifications'] });
    } catch (error) {
        toast.error('Failed to mark notification as read');
    }
};

const markAllAsRead = async () => {
    try {
        await router.patch(route('notifications.markAllAsRead'));
        router.reload({ only: ['notifications'] });
        toast.success('All notifications marked as read');
    } catch (error) {
        toast.error('Failed to mark all notifications as read');
    }
};

const unreadCount = computed(() => {
    return props.notifications.data.filter(n => !n.read_at).length;
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
                            <h1 class="text-3xl font-bold text-yellow-200 pixel-font">Notifications</h1>
                            <p class="text-gray-100">
                                {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                    <button 
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="flex items-center space-x-2 bg-yellow-300 hover:bg-yellow-200 text-black font-bold py-2 px-4 rounded pixel-outline transition-colors"
                    >
                        <CheckCircle class="h-4 w-4" />
                        <span>Mark all as read</span>
                    </button>
                </div>

                <!-- Notifications List -->
                <div class="space-y-4">
                    <div
                        v-if="props.notifications.data.length === 0"
                        class="text-center py-12"
                    >
                        <Bell class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-100">No notifications</h3>
                        <p class="mt-1 text-sm text-gray-200">You're all caught up!</p>
                    </div>

                    <div
                        v-for="notification in props.notifications.data"
                        :key="notification.id"
                        :class="[
                            'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 transition-all duration-200 hover:shadow-md',
                            !notification.read_at ? 'border-l-4 border-l-blue-500' : ''
                        ]"
                    >
                        <div class="flex items-start justify-between space-x-4">
                            <div class="flex items-start space-x-3 flex-1">
                                <div
                                    v-if="!notification.read_at"
                                    class="mt-2 h-3 w-3 rounded-full bg-blue-600"
                                ></div>
                                <div class="flex-1 min-w-0">
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
                                        class="mt-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 p-3"
                                    >
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            File: {{ notification.data.file_name }}
                                        </p>
                                        <p
                                            v-if="notification.data.denial_reason"
                                            class="mt-1 text-sm text-gray-600 dark:text-gray-300"
                                        >
                                            <strong>Reason:</strong> {{ notification.data.denial_reason }}
                                        </p>
                                    </div>

                                    <!-- Action button -->
                                    <div v-if="notification.data.file_id" class="mt-3">
                                        <Link
                                            :href="route('files.show', notification.data.file_id)"
                                            class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline transition-colors"
                                        >
                                            View File
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span
                                    :class="[
                                        'text-xs px-2 py-1 rounded whitespace-nowrap',
                                        notification.read_at 
                                            ? 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300' 
                                            : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-semibold'
                                    ]"
                                >
                                    {{ notification.read_at ? 'Read' : 'Unread' }}
                                </span>
                                <button
                                    v-if="!notification.read_at"
                                    @click="markAsRead(notification.id)"
                                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
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
                                'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed': !link.url
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