<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight pixel-font">
                Pending Verifications
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                                        <div class="bg-black/70 backdrop-blur-sm border border-yellow-400/50 overflow-hidden shadow-xl sm:rounded-lg pixel-outline">
                    <div class="p-6 lg:p-8">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-yellow-200 pixel-font">Pending Verifications</h1>
                                <p class="text-gray-100 mt-1">Review and approve user verification documents</p>
                            </div>
                            <Link 
                                :href="route('admin.verifications.all')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded pixel-outline transition-colors"
                            >
                                View All Verifications
                            </Link>
                        </div>

                        <!-- Pending Count -->
                        <div v-if="pendingUsers.length > 0" class="mb-6 p-4 bg-red-900/40 border border-yellow-400/60 rounded pixel-outline">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-200 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-yellow-100 font-medium">{{ pendingUsers.length }} verification{{ pendingUsers.length !== 1 ? 's' : '' }} pending approval</span>
                            </div>
                        </div>

                        <!-- No Pending Verifications -->
                        <div v-if="pendingUsers.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5l7-7 7 7M5 20l7-7 7 7"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-100 mb-2">No Pending Verifications</h3>
                            <p class="text-gray-200">All user verifications have been processed.</p>
                        </div>

                        <!-- Pending Users Table -->
                        <div v-if="pendingUsers.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-600">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider pixel-font">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider pixel-font">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider pixel-font">Program</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider pixel-font">Uploaded</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider pixel-font">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-600">
                                    <tr v-for="user in pendingUsers" :key="user.id" class="hover:bg-gray-800/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-yellow-400 flex items-center justify-center">
                                                        <span class="text-white font-semibold">
                                                            {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-100">
                                                        {{ user.first_name }} {{ user.last_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-200">
                                                        {{ user.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full pixel-outline" :class="{
                                                'bg-blue-900 text-blue-200 border-blue-400': user.user_role === 'student',
                                                'bg-green-900 text-green-200 border-green-400': user.user_role === 'faculty'
                                            }">
                                                {{ user.user_role.charAt(0).toUpperCase() + user.user_role.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                                            {{ user.program ? user.program.name : 'N/A' }}
                                            <div v-if="user.year_of_study" class="text-xs text-gray-200">
                                                {{ user.year_of_study }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                                            {{ formatDate(user.document_uploaded_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <Link 
                                                :href="route('admin.verifications.show', user.id)" 
                                                class="bg-yellow-300 hover:bg-yellow-200 text-white font-bold py-1 px-3 rounded pixel-outline transition-colors"
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
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    pendingUsers: Array
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