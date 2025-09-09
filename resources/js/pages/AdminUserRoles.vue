<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from 'vue-sonner';

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
    <div class="p-6 max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-4">User Role Management</h1>
        <div v-if="loading">Loading users...</div>
        <div v-if="error" class="text-red-500 mb-2">{{ error }}</div>
        <table v-if="!loading && users.length" class="w-full border">
            <thead>
                <tr>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Email</th>
                    <th class="p-2 border">Role</th>
                    <th class="p-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td class="p-2 border">{{ user.first_name }} {{ user.last_name }}</td>
                    <td class="p-2 border">{{ user.email }}</td>
                    <td class="p-2 border">
                        <select v-model="user.user_role" :disabled="updating === user.id" class="border rounded px-2 py-1">
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                        </select>
                    </td>
                    <td class="p-2 border">
                        <button @click="updateRole(user, user.user_role)" :disabled="updating === user.id" class="bg-blue-500 hover:bg-blue-300 text-white px-3 py-1 rounded">
                            Update
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-if="!loading && !users.length" class="text-gray-500">No users found.</div>
    </div>
    </AppLayout>
</template>

