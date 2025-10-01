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
                        <Bell class="h-8 w-8 text-blue-600" />
                        <div>
                            <h1 class="text-3xl font-bold text-white-900">Notifications</h1>
                            <p class="text-white-600">
                                {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                    <Button 
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        variant="outline"
                        class="flex items-center space-x-2"
                    >
                        <CheckCircle class="h-4 w-4" />
                        <span>Mark all as read</span>
                    </Button>
                </div>

                <!-- Notifications List -->
                <div class="space-y-4">
                    <div
                        v-if="props.notifications.data.length === 0"
                        class="text-center py-12"
                    >
                        <Bell class="mx-auto h-12 w-12 text-white-400" />
                        <h3 class="mt-2 text-sm font-medium text-white-900">No notifications</h3>
                        <p class="mt-1 text-sm text-white-500">You're all caught up!</p>
                    </div>

                    <Card
                        v-for="notification in props.notifications.data"
                        :key="notification.id"
                        :class="[
                            'transition-all duration-200 hover:shadow-md',
                            !notification.read_at ? 'bg-blue-50 border-blue-200' : ''
                        ]"
                    >
                        <CardHeader class="pb-3">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-3">
                                    <div
                                        v-if="!notification.read_at"
                                        class="mt-1 h-3 w-3 rounded-full bg-blue-600"
                                    ></div>
                                    <div class="flex-1">
                                        <CardTitle class="text-lg">
                                            {{ notification.type === 'App\\Notifications\\FileDeniedNotification' ? 'File Denied' : 'Notification' }}
                                        </CardTitle>
                                        <CardDescription class="mt-1">
                                            {{ formatDate(notification.created_at) }}
                                        </CardDescription>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Badge
                                        :variant="notification.read_at ? 'secondary' : 'default'"
                                        class="text-xs"
                                    >
                                        {{ notification.read_at ? 'Read' : 'Unread' }}
                                    </Badge>
                                    <Button
                                        v-if="!notification.read_at"
                                        @click="markAsRead(notification.id)"
                                        variant="ghost"
                                        size="sm"
                                        class="p-1"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <p class="text-gray-900">{{ notification.data.message }}</p>
                                
                                <!-- File-specific information -->
                                <div
                                    v-if="notification.data.file_name"
                                    class="rounded-lg bg-gray-50 p-3"
                                >
                                    <p class="text-sm font-medium text-gray-900">
                                        File: {{ notification.data.file_name }}
                                    </p>
                                    <p
                                        v-if="notification.data.denial_reason"
                                        class="mt-1 text-sm text-gray-600"
                                    >
                                        <strong>Reason:</strong> {{ notification.data.denial_reason }}
                                    </p>
                                </div>

                                <!-- Action button -->
                                <div v-if="notification.data.file_id">
                                    <Link
                                        :href="route('files.show', notification.data.file_id)"
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        View File
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
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