<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="pixel-outline overflow-hidden border border-yellow-400/50 bg-black/70 shadow-xl backdrop-blur-sm sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h1 class="pixel-font text-2xl font-bold text-yellow-200">Pending Verifications</h1>
                                <p class="mt-1 text-gray-100">Review and approve user verification documents</p>
                            </div>
                            <Link
                                :href="route('admin.verifications.all')"
                                class="pixel-outline rounded bg-blue-600 px-4 py-2 font-bold text-white transition-colors hover:bg-blue-700"
                            >
                                View All Verifications
                            </Link>
                        </div>

                        <!-- Pending Count -->
                        <div v-if="pendingUsers.length > 0" class="pixel-outline mb-6 rounded border border-yellow-400/60 bg-red-900/40 p-4">
                            <div class="flex items-center">
                                <svg class="mr-2 h-5 w-5 text-yellow-200" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span class="font-medium text-yellow-100"
                                    >{{ pendingUsers.length }} verification{{ pendingUsers.length !== 1 ? 's' : '' }} pending approval</span
                                >
                            </div>
                        </div>

                        <!-- No Pending Verifications -->
                        <div v-if="pendingUsers.length === 0" class="py-12 text-center">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5l7-7 7 7M5 20l7-7 7 7" />
                            </svg>
                            <h3 class="mb-2 text-lg font-medium text-gray-100">No Pending Verifications</h3>
                            <p class="text-gray-200">All user verifications have been processed.</p>
                        </div>

                        <!-- Pending Users Table -->
                        <div v-if="pendingUsers.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-600">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-100 uppercase">
                                            User
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-100 uppercase">
                                            Role
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-100 uppercase">
                                            Program
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-100 uppercase">
                                            Uploaded
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-100 uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-600">
                                    <tr v-for="user in pendingUsers" :key="user.id" class="transition-colors hover:bg-gray-800/30">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-400">
                                                        <span class="font-semibold text-white">
                                                            {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-100">{{ user.first_name }} {{ user.last_name }}</div>
                                                    <div class="text-sm text-gray-200">
                                                        {{ user.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="pixel-outline inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="{
                                                    'border-blue-400 bg-blue-900 text-blue-200': user.user_role === 'student',
                                                    'border-green-400 bg-green-900 text-green-200': user.user_role === 'faculty',
                                                }"
                                            >
                                                {{ user.user_role.charAt(0).toUpperCase() + user.user_role.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-100">
                                            {{ user.program ? user.program.name : 'N/A' }}
                                            <div v-if="user.year_of_study" class="text-xs text-gray-200">
                                                {{ user.year_of_study }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-100">
                                            {{ formatDate(user.document_uploaded_at) }}
                                        </td>
                                        <td class="space-x-2 px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <Link
                                                :href="route('admin.verifications.show', user.id)"
                                                class="pixel-outline rounded bg-yellow-300 px-3 py-1 font-bold text-white transition-colors hover:bg-yellow-200"
                                            >
                                                Review
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    pendingUsers: Array,
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
.pixel-font {
    font-family: 'Courier New', monospace;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.pixel-outline {
    box-shadow:
        0 0 0 1px currentColor,
        2px 2px 0 0 rgba(0, 0, 0, 0.5);
}
</style>
