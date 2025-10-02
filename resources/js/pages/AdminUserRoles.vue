<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';

const searchQuery = ref('');

interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    user_role: 'student' | 'faculty';
}

const users = ref<User[]>([]);
const loading = ref(false);
const error = ref('');
const updating = ref<number | null>(null);
const filterRole = ref('');

const filteredUsers = computed(() => {
    let result = users.value;
    if (filterRole.value) {
        result = result.filter((user) => user.user_role === filterRole.value);
    }
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((user) => user.first_name.toLowerCase().includes(query) || user.last_name.toLowerCase().includes(query));
    }
    return result;
});

async function fetchUsers() {
    loading.value = true;
    error.value = '';
    try {
        const res = await axios.get('/api/users');
        users.value = res.data;
    } catch (e) {
        error.value = 'Failed to load users.';
    } finally {
        loading.value = false;
    }
}

async function updateRole(user: User, newRole: 'student' | 'faculty') {
    updating.value = user.id;
    try {
        await axios.patch(`/api/users/${user.id}/role`, { role: newRole });
        user.user_role = newRole;
        toast.info('User role updated successfully.');
    } catch (e) {
        error.value = 'Failed to update role.';
        toast.error('Failed to update user role.');
    } finally {
        updating.value = null;
    }
}

onMounted(fetchUsers);
</script>
<template>
    <Head title="Admin - User Roles" />
    <AppLayout>
        <div class="max-w-9xl bg-gradient mx-auto min-h-screen p-6">
            <div class="mx-4 mx-auto mb-6 flex max-w-md justify-center sm:order-2 sm:flex-1">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline px-6 py-2 text-center text-2xl leading-tight font-bold">
                    User Role Management
                </h1>
            </div>
            <div class="mb-4">
                <label for="filter" class="pixel-outline block text-sm font-medium text-gray-500">Filter by Role</label>
                <select v-model="filterRole" class="pixel-outline w-full rounded border bg-black/50 px-2 py-1 text-white">
                    <option value="" class="bg-black text-white">All</option>
                    <option value="student" class="bg-black text-white">Student</option>
                    <option value="faculty" class="bg-black text-white">Faculty</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="search" class="pixel-outline block text-sm font-medium text-gray-500">Search by Name</label>
                <input
                    v-model="searchQuery"
                    type="text"
                    id="search"
                    placeholder="Search users..."
                    class="pixel-outline w-full rounded border bg-black/50 px-2 py-1"
                />
            </div>
            <div v-if="loading">Loading users...</div>
            <div v-if="error" class="pixel-outline mb-2 text-red-500">{{ error }}</div>
            <div v-if="!loading && filteredUsers.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <div v-for="user in filteredUsers" :key="user.id" class="bg-container rounded-lg border p-4 shadow">
                    <h2 class="pixel-outline mb-2 text-lg font-semibold">{{ user.first_name }} {{ user.last_name }}</h2>
                    <p class="pixel-outline mb-2 text-gray-400">{{ user.email }}</p>
                    <div class="mb-2">
                        <label for="role" class="pixel-outline block text-sm font-medium text-gray-500">Role</label>
                        <select
                            v-model="user.user_role"
                            :disabled="updating === user.id"
                            class="pixel-outline w-full rounded border bg-black/50 px-2 py-1"
                        >
                            <option value="student" class="bg-black text-white">Student</option>
                            <option value="faculty" class="bg-black text-white">Faculty</option>
                        </select>
                    </div>
                    <button
                        @click="updateRole(user, user.user_role)"
                        :disabled="updating === user.id"
                        class="pixel-outline w-full rounded bg-blue-500 px-3 py-1 text-white hover:bg-blue-800"
                    >
                        Update
                    </button>
                </div>
            </div>
            <div v-if="!loading && !filteredUsers.length" class="text-gray-500">No users found.</div>
        </div>
    </AppLayout>
</template>
