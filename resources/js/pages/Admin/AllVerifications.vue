<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight pixel-font">
                All Verifications
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-black/70 backdrop-blur-sm border border-yellow-400/50 overflow-hidden shadow-xl sm:rounded-lg pixel-outline">
                    <div class="p-6 lg:p-8">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <Link 
                                    :href="route('admin.verifications')" 
                                    class="inline-flex items-center text-blue-400 hover:text-blue-300 mb-2"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Back to Pending
                                </Link>
                                <h1 class="text-2xl font-bold text-yellow-200 pixel-font">All Verifications</h1>
                                <p class="text-gray-100 mt-1">Complete history of user verifications</p>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="flex items-center space-x-4">
                                <select
                                    v-model="statusFilter"
                                    class="px-3 py-2 bg-black/50 border border-gray-600 rounded pixel-outline text-white"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-yellow-900/30 border border-yellow-400/50 p-4 rounded pixel-outline">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-yellow-400">{{ getStatusCount('pending') }}</div>
                                        <div class="text-yellow-200 text-sm">Pending</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-green-900/30 border border-green-400/50 p-4 rounded pixel-outline">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-green-400">{{ getStatusCount('approved') }}</div>
                                        <div class="text-green-200 text-sm">Approved</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-red-900/30 border border-red-400/50 p-4 rounded pixel-outline">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-red-400">{{ getStatusCount('rejected') }}</div>
                                        <div class="text-red-200 text-sm">Rejected</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-blue-900/30 border border-blue-400/50 p-4 rounded pixel-outline">
                                <div class="flex items-center">
                                    <svg class="w-8 h-8 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-blue-400">{{ users?.data?.length || 0 }}</div>
                                        <div class="text-blue-200 text-sm">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-600">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider pixel-font">User</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider pixel-font">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider pixel-font">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider pixel-font">Uploaded</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider pixel-font">Verified By</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider pixel-font">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-600">
                                    <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-800/30 transition-colors">
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
                                                    <div class="text-sm font-medium text-white">
                                                        {{ user.first_name }} {{ user.last_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-400">
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full pixel-outline" :class="{
                                                'bg-yellow-900 text-yellow-200 border-yellow-400': user.verification_status === 'pending',
                                                'bg-green-900 text-green-200 border-green-400': user.verification_status === 'approved',
                                                'bg-red-900 text-red-200 border-red-400': user.verification_status === 'rejected'
                                            }">
                                                {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            {{ formatDate(user.document_uploaded_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                            <div v-if="user.verifiedBy">
                                                <div class="font-medium">{{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }}</div>
                                                <div class="text-gray-400 text-xs">{{ formatDate(user.verified_at) }}</div>
                                            </div>
                                            <span v-else class="text-gray-500">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link 
                                                :href="route('admin.verifications.show', user.id)" 
                                                class="bg-yellow-400 hover:bg-yellow-500 text-white font-bold py-1 px-3 rounded pixel-outline transition-colors"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links && users.links.length > 3" class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-400">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <template v-for="link in users.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-2 text-sm rounded pixel-outline transition-colors"
                                        :class="{
                                            'bg-yellow-400 text-black': link.active,
                                            'bg-gray-600 text-white hover:bg-gray-500': !link.active
                                        }"
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                    <span
                                        v-else
                                        class="px-3 py-2 text-sm rounded pixel-outline bg-gray-800 text-gray-500 cursor-not-allowed"
                                        v-html="link.label"
                                    ></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    users: Object
});

const statusFilter = ref('');

const filteredUsers = computed(() => {
    if (!props.users?.data) return [];
    if (!statusFilter.value) {
        return props.users.data;
    }
    return props.users.data.filter(user => user.verification_status === statusFilter.value);
});

const getStatusCount = (status) => {
    if (!props.users?.data) return 0;
    return props.users.data.filter(user => user.verification_status === status).length;
};

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