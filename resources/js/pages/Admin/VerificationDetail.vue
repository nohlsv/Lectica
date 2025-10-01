<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight pixel-font">
                Verification Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-black/70 backdrop-blur-sm border border-yellow-400/50 overflow-hidden shadow-xl sm:rounded-lg pixel-outline">
                    <div class="p-6 lg:p-8">
                        <!-- Header with Back Button -->
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <Link 
                                    :href="route('admin.verifications')" 
                                    class="inline-flex items-center text-blue-400 hover:text-blue-300 mb-2"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    Back to Pending Verifications
                                </Link>
                                <h1 class="text-2xl font-bold text-yellow-200 pixel-font">Verification Review</h1>
                                <p class="text-gray-100 mt-1">Review and verify user documents</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full pixel-outline" :class="{
                                    'bg-yellow-900 text-yellow-200 border-yellow-400': user.verification_status === 'pending',
                                    'bg-green-900 text-green-200 border-green-400': user.verification_status === 'approved',
                                    'bg-red-900 text-red-200 border-red-400': user.verification_status === 'rejected'
                                }">
                                    {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- User Information -->
                            <div class="space-y-6">
                                <!-- User Details Card -->
                                <div class="bg-red-950/40 p-6 rounded-lg pixel-outline border border-red-400/50">
                                    <h3 class="text-lg font-semibold text-yellow-200 mb-4 pixel-font">User Information</h3>
                                    
                                    <form @submit.prevent="updateUserDetails" class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-100 mb-1">First Name</label>
                                                <input
                                                    v-model="userForm.first_name"
                                                    type="text"
                                                    class="w-full px-3 py-2 bg-black/60 border border-red-400/50 rounded pixel-outline text-white"
                                                    required
                                                />
                                                <InputError :message="userForm.errors.first_name" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-100 mb-1">Last Name</label>
                                                <input
                                                    v-model="userForm.last_name"
                                                    type="text"
                                                    class="w-full px-3 py-2 bg-black/60 border border-red-400/50 rounded pixel-outline text-white"
                                                    required
                                                />
                                                <InputError :message="userForm.errors.last_name" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-100 mb-1">Email</label>
                                            <input
                                                v-model="userForm.email"
                                                type="email"
                                                class="w-full px-3 py-2 bg-black/60 border border-red-400/50 rounded pixel-outline text-white"
                                                required
                                            />
                                            <InputError :message="userForm.errors.email" />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-100 mb-1">Role</label>
                                            <div class="px-3 py-2 bg-red-900/50 border border-red-400/30 rounded pixel-outline text-gray-100">
                                                {{ user.user_role.charAt(0).toUpperCase() + user.user_role.slice(1) }}
                                            </div>
                                        </div>

                                        <div v-if="user.user_role === 'student'" class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-100 mb-1">Program</label>
                                                <select
                                                    v-model="userForm.program_id"
                                                    class="w-full px-3 py-2 bg-black/60 border border-red-400/50 rounded pixel-outline text-white"
                                                    required
                                                >
                                                    <option value="">Select Program</option>
                                                    <option v-for="program in programs" :key="program.id" :value="program.id">
                                                        {{ program.name }}
                                                    </option>
                                                </select>
                                                <InputError :message="userForm.errors.program_id" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-100 mb-1">Year of Study</label>
                                                <select
                                                    v-model="userForm.year_of_study"
                                                    class="w-full px-3 py-2 bg-black/60 border border-red-400/50 rounded pixel-outline text-white"
                                                    required
                                                >
                                                    <option value="">Select Year</option>
                                                    <option value="1st Year">1st Year</option>
                                                    <option value="2nd Year">2nd Year</option>
                                                    <option value="3rd Year">3rd Year</option>
                                                    <option value="4th Year">4th Year</option>
                                                    <option value="5th Year">5th Year</option>
                                                    <option value="Graduate">Graduate</option>
                                                </select>
                                                <InputError :message="userForm.errors.year_of_study" />
                                            </div>
                                        </div>

                                        <button
                                            type="submit"
                                            :disabled="userForm.processing"
                                            class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2 px-4 rounded pixel-outline transition-colors disabled:opacity-50"
                                        >
                                            {{ userForm.processing ? 'Updating...' : 'Update User Details' }}
                                        </button>
                                    </form>
                                </div>

                                <!-- Timeline -->
                                <div class="bg-gray-800/50 p-6 rounded-lg pixel-outline border border-gray-600">
                                    <h3 class="text-lg font-semibold text-yellow-400 mb-4 pixel-font">Timeline</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center text-sm">
                                            <div class="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
                                            <span class="text-gray-300">Email verified: {{ formatDate(user.email_verified_at) }}</span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                                            <span class="text-gray-300">Document uploaded: {{ formatDate(user.document_uploaded_at) }}</span>
                                        </div>
                                        <div v-if="user.verified_at" class="flex items-center text-sm">
                                            <div class="w-2 h-2 rounded-full mr-3" :class="{
                                                'bg-green-400': user.verification_status === 'approved',
                                                'bg-red-400': user.verification_status === 'rejected'
                                            }"></div>
                                            <span class="text-gray-300">
                                                {{ user.verification_status === 'approved' ? 'Approved' : 'Rejected' }}: {{ formatDate(user.verified_at) }}
                                                <span v-if="user.verifiedBy" class="text-gray-400">
                                                    by {{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }}
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document and Actions -->
                            <div class="space-y-6">
                                <!-- Document Display -->
                                <div class="bg-gray-800/50 p-6 rounded-lg pixel-outline border border-gray-600">
                                    <h3 class="text-lg font-semibold text-yellow-400 mb-4 pixel-font">
                                        {{ user.user_role === 'student' ? 'COR Document' : 'ID Document' }}
                                    </h3>
                                    
                                    <div v-if="documentUrl" class="space-y-4">
                                        <div class="border border-gray-600 rounded overflow-hidden">
                                            <img 
                                                v-if="isImage(documentUrl)"
                                                :src="documentUrl" 
                                                :alt="user.user_role === 'student' ? 'COR Document' : 'ID Document'"
                                                class="w-full h-auto max-h-96 object-contain bg-white"
                                            />
                                            <div v-else class="p-8 text-center bg-gray-700">
                                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd"/>
                                                </svg>
                                                <p class="text-gray-300 mb-2">PDF Document</p>
                                                <a 
                                                    :href="documentUrl" 
                                                    target="_blank" 
                                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                                                >
                                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Open PDF
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="flex justify-center">
                                            <a 
                                                :href="documentUrl" 
                                                target="_blank" 
                                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors"
                                            >
                                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                </svg>
                                                View Full Size
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5l7-7 7 7M5 20l7-7 7 7"/>
                                        </svg>
                                        <p class="text-gray-400">No document uploaded</p>
                                    </div>
                                </div>

                                <!-- Verification Actions -->
                                <div v-if="user.verification_status === 'pending'" class="bg-gray-800/50 p-6 rounded-lg pixel-outline border border-gray-600">
                                    <h3 class="text-lg font-semibold text-yellow-400 mb-4 pixel-font">Verification Decision</h3>
                                    
                                    <form @submit.prevent="submitDecision" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-300 mb-2">Admin Notes</label>
                                            <textarea
                                                v-model="verificationForm.notes"
                                                rows="3"
                                                class="w-full px-3 py-2 bg-black/50 border border-gray-600 rounded pixel-outline text-white"
                                                placeholder="Add notes about the verification decision..."
                                            ></textarea>
                                            <InputError :message="verificationForm.errors.notes" />
                                        </div>

                                        <div class="flex space-x-4">
                                            <button
                                                @click="decision = 'approve'"
                                                type="submit"
                                                :disabled="verificationForm.processing"
                                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded pixel-outline transition-colors disabled:opacity-50"
                                            >
                                                {{ verificationForm.processing && decision === 'approve' ? 'Approving...' : 'Approve' }}
                                            </button>
                                            <button
                                                @click="decision = 'reject'"
                                                type="submit"
                                                :disabled="verificationForm.processing"
                                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded pixel-outline transition-colors disabled:opacity-50"
                                            >
                                                {{ verificationForm.processing && decision === 'reject' ? 'Rejecting...' : 'Reject' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Previous Decision Display -->
                                <div v-else class="bg-gray-800/50 p-6 rounded-lg pixel-outline border border-gray-600">
                                    <h3 class="text-lg font-semibold text-yellow-400 mb-4 pixel-font">Verification Decision</h3>
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-300">
                                            Status: 
                                            <span :class="{
                                                'text-green-400': user.verification_status === 'approved',
                                                'text-red-400': user.verification_status === 'rejected'
                                            }">
                                                {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                                            </span>
                                        </p>
                                        <p v-if="user.verification_notes" class="text-sm text-gray-300">
                                            Notes: {{ user.verification_notes }}
                                        </p>
                                        <p v-if="user.verifiedBy" class="text-xs text-gray-400">
                                            Verified by {{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }}
                                            on {{ formatDate(user.verified_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    user: Object,
    documentUrl: String,
    programs: Array
});

const decision = ref('');

const userForm = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    program_id: props.user.program_id,
    year_of_study: props.user.year_of_study
});

const verificationForm = useForm({
    notes: ''
});

const updateUserDetails = () => {
    userForm.patch(route('admin.verifications.update-details', props.user.id));
};

const submitDecision = () => {
    if (decision.value === 'approve') {
        verificationForm.patch(route('admin.verifications.approve', props.user.id));
    } else if (decision.value === 'reject') {
        // Notes are required for rejection, validate first
        if (!verificationForm.notes.trim()) {
            verificationForm.setError('notes', 'Notes are required when rejecting verification.');
            return;
        }
        verificationForm.patch(route('admin.verifications.reject', props.user.id));
    }
};

const isImage = (url) => {
    return /\.(jpg|jpeg|png|gif)$/i.test(url);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
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