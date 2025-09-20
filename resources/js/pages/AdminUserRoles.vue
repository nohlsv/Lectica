<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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
const updating = ref<number|null>(null);
const filterRole = ref('');


const filteredUsers = computed(() => {
    let result = users.value;
    if (filterRole.value) {
        result = result.filter(user => user.user_role === filterRole.value);
    }
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(user => 
            user.first_name.toLowerCase().includes(query) || 
            user.last_name.toLowerCase().includes(query)
        );
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
        <div class="p-6 max-w-9xl mx-auto bg-gradient min-h-screen">
            <div class="mx-auto max-w-md flex justify-center sm:order-2 sm:flex-1 mb-6 mx-4">
                <h1 class="welcome-banner animate-soft-bounce px-6 py-2 text-center text-2xl leading-tight font-bold pixel-outline">User Role Management</h1>
            </div>
            <div class="mb-4">
                <label for="filter" class="block text-sm font-medium text-gray-500 pixel-outline">Filter by Role</label>
                <select v-model="filterRole" class="border rounded px-2 py-1 w-full bg-black/50 text-white pixel-outline">
                    <option value="" class="bg-black text-white">All</option>
                    <option value="student" class="bg-black text-white">Student</option>
                    <option value="faculty" class="bg-black text-white">Faculty</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="search" class="block text-sm font-medium text-gray-500 pixel-outline">Search by Name</label>
                <input v-model="searchQuery" type="text" id="search" placeholder="Search users..." class="border rounded px-2 py-1 w-full bg-black/50 pixel-outline" />
            </div>
            <div v-if="loading">Loading users...</div>
            <div v-if="error" class="text-red-500 mb-2 pixel-outline">{{ error }}</div>
            <div v-if="!loading && filteredUsers.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="user in filteredUsers" :key="user.id" class="border rounded-lg p-4 shadow bg-container">
                    <h2 class="text-lg font-semibold mb-2 pixel-outline">{{ user.first_name }} {{ user.last_name }}</h2>
                    <p class="text-gray-400 mb-2 pixel-outline">{{ user.email }}</p>
                    <div class="mb-2">
                        <label for="role" class="block text-sm font-medium text-gray-500 pixel-outline">Role</label>
                        <select v-model="user.user_role" :disabled="updating === user.id" class="border rounded px-2 py-1 w-full bg-black/50 pixel-outline">
                            <option value="student" class="bg-black text-white">Student</option>
                            <option value="faculty" class="bg-black text-white">Faculty</option>
                        </select>
                    </div>
                    <button @click="updateRole(user, user.user_role)" :disabled="updating === user.id" class="bg-blue-500 hover:bg-blue-800 text-white pixel-outline px-3 py-1 rounded w-full">
                        Update
                    </button>
                </div>
            </div>
            <div v-if="!loading && !filteredUsers.length" class="text-gray-500">No users found.</div>
        </div>
    </AppLayout>
</template>




