<template>
    <AppLayout>
        <template #header>
            <h2 class="pixel-font text-xl leading-tight font-semibold text-gray-200">All Verifications</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="pixel-outline overflow-hidden border border-yellow-400/50 bg-black/70 shadow-xl backdrop-blur-sm sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <Link :href="route('admin.verifications')" class="mb-2 inline-flex items-center text-blue-400 hover:text-blue-300">
                                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    Back to Pending
                                </Link>
                                <h1 class="pixel-font text-2xl font-bold text-yellow-200">All Verifications</h1>
                                <p class="mt-1 text-gray-100">Complete history of user verifications</p>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex items-center space-x-4">
                                <select v-model="statusFilter" class="pixel-outline rounded border border-gray-600 bg-black/50 px-3 py-2 text-white">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="pixel-outline rounded border border-yellow-400/50 bg-yellow-900/30 p-4">
                                <div class="flex items-center">
                                    <svg class="mr-3 h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-yellow-400">{{ getStatusCount('pending') }}</div>
                                        <div class="text-sm text-yellow-200">Pending</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pixel-outline rounded border border-green-400/50 bg-green-900/30 p-4">
                                <div class="flex items-center">
                                    <svg class="mr-3 h-8 w-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-green-400">{{ getStatusCount('approved') }}</div>
                                        <div class="text-sm text-green-200">Approved</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pixel-outline rounded border border-red-400/50 bg-red-900/30 p-4">
                                <div class="flex items-center">
                                    <svg class="mr-3 h-8 w-8 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-red-400">{{ getStatusCount('rejected') }}</div>
                                        <div class="text-sm text-red-200">Rejected</div>
                                    </div>
                                </div>
                            </div>

                            <div class="pixel-outline rounded border border-blue-400/50 bg-blue-900/30 p-4">
                                <div class="flex items-center">
                                    <svg class="mr-3 h-8 w-8 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <div>
                                        <div class="text-2xl font-bold text-blue-400">{{ users?.data?.length || 0 }}</div>
                                        <div class="text-sm text-blue-200">Total</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-600">
                                <thead>
                                    <tr class="bg-gray-800/50">
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                            User
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                            Role
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                            Status
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                            Uploaded
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                            Verified By
                                        </th>
                                        <th class="pixel-font px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-300 uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-600">
                                    <tr v-for="user in filteredUsers" :key="user.id" class="transition-colors hover:bg-gray-800/30">
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
                                                    <div class="text-sm font-medium text-white">{{ user.first_name }} {{ user.last_name }}</div>
                                                    <div class="text-sm text-gray-400">
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
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="pixel-outline inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                                :class="{
                                                    'border-yellow-400 bg-yellow-900 text-yellow-200': user.verification_status === 'pending',
                                                    'border-green-400 bg-green-900 text-green-200': user.verification_status === 'approved',
                                                    'border-red-400 bg-red-900 text-red-200': user.verification_status === 'rejected',
                                                }"
                                            >
                                                {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-300">
                                            {{ formatDate(user.document_uploaded_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-300">
                                            <div v-if="user.verifiedBy">
                                                <div class="font-medium">{{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }}</div>
                                                <div class="text-xs text-gray-400">{{ formatDate(user.verified_at) }}</div>
                                            </div>
                                            <span v-else class="text-gray-500">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <Link
                                                :href="route('admin.verifications.show', user.id)"
                                                class="pixel-outline rounded bg-yellow-400 px-3 py-1 font-bold text-white transition-colors hover:bg-yellow-500"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.links && users.links.length > 3" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-400">Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results</div>
                            <div class="flex space-x-1">
                                <template v-for="link in users.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="pixel-outline rounded px-3 py-2 text-sm transition-colors"
                                        :class="{
                                            'bg-yellow-400 text-black': link.active,
                                            'bg-gray-600 text-white hover:bg-gray-500': !link.active,
                                        }"
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                    <span
                                        v-else
                                        class="pixel-outline cursor-not-allowed rounded bg-gray-800 px-3 py-2 text-sm text-gray-500"
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: Object,
});

const statusFilter = ref('');

const filteredUsers = computed(() => {
    if (!props.users?.data) return [];
    if (!statusFilter.value) {
        return props.users.data;
    }
    return props.users.data.filter((user) => user.verification_status === statusFilter.value);
});

const getStatusCount = (status) => {
    if (!props.users?.data) return 0;
    return props.users.data.filter((user) => user.verification_status === status).length;
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
