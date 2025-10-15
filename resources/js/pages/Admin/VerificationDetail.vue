<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="pixel-outline overflow-hidden border border-yellow-400/50 bg-black/70 shadow-xl backdrop-blur-sm sm:rounded-lg">
                    <div class="p-6 lg:p-8">
                        <!-- Header with Back Button -->
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
                                    Back to Pending Verifications
                                </Link>
                                <h1 class="pixel-font text-2xl font-bold text-yellow-200">Verification Review</h1>
                                <p class="mt-1 text-gray-100">Review and verify user documents</p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="pixel-outline inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                    :class="{
                                        'border-yellow-400 bg-yellow-900 text-yellow-200': user.verification_status === 'pending',
                                        'border-green-400 bg-green-900 text-green-200': user.verification_status === 'approved',
                                        'border-red-400 bg-red-900 text-red-200': user.verification_status === 'rejected',
                                    }"
                                >
                                    {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                            <!-- User Information -->
                            <div class="space-y-6">
                                <!-- User Details Card -->
                                <div class="pixel-outline rounded-lg border border-red-400/50 bg-red-950/40 p-6">
                                    <h3 class="pixel-font mb-4 text-lg font-semibold text-yellow-200">User Information</h3>

                                    <form @submit.prevent="updateUserDetails" class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-100">First Name</label>
                                                <input
                                                    v-model="userForm.first_name"
                                                    type="text"
                                                    class="pixel-outline w-full rounded border border-red-400/50 bg-black/60 px-3 py-2 text-white"
                                                    required
                                                />
                                                <InputError :message="userForm.errors.first_name" />
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-100">Last Name</label>
                                                <input
                                                    v-model="userForm.last_name"
                                                    type="text"
                                                    class="pixel-outline w-full rounded border border-red-400/50 bg-black/60 px-3 py-2 text-white"
                                                    required
                                                />
                                                <InputError :message="userForm.errors.last_name" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-100">Email</label>
                                            <input
                                                v-model="userForm.email"
                                                type="email"
                                                class="pixel-outline w-full rounded border border-red-400/50 bg-black/60 px-3 py-2 text-white"
                                                required
                                            />
                                            <InputError :message="userForm.errors.email" />
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-100">Role</label>
                                            <div class="pixel-outline rounded border border-red-400/30 bg-red-900/50 px-3 py-2 text-gray-100">
                                                {{ user.user_role.charAt(0).toUpperCase() + user.user_role.slice(1) }}
                                            </div>
                                        </div>

                                        <div v-if="user.user_role === 'student' || user.user_role === 'faculty'" class="space-y-4">
                                            <!-- College Selection -->
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-100">College</label>
                                                <select
                                                    v-model="selectedCollege"
                                                    class="pixel-outline w-full rounded border border-red-400/50 bg-black/60 px-3 py-2 text-white"
                                                    required
                                                >
                                                    <option v-for="college in colleges" :key="college" :value="college">
                                                        {{ college }}
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Program Selection (visible only for students) -->
                                            <div v-if="user.user_role === 'student'">
                                                <label class="mb-1 block text-sm font-medium text-gray-100">Program</label>
                                                <select
                                                    v-model="userForm.program_id"
                                                    class="pixel-outline w-full rounded border border-red-400/50 bg-black/60 px-3 py-2 text-white"
                                                    required
                                                >
                                                    <option value="">Select Program</option>
                                                    <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                                                        {{ program.name }} ({{ program.code }})
                                                    </option>
                                                </select>
                                                <InputError :message="userForm.errors.program_id" />
                                            </div>
                                            <div v-if="user.user_role === 'student'">
                                                <label class="mb-1 block text-sm font-medium text-gray-100">Year of Study</label>
                                                <select
                                                    v-model="userForm.year_of_study"
                                                    class="pixel-outline w-full rounded border border-red-400/50 bg-black/60 px-3 py-2 text-white"
                                                    required
                                                >
                                                    <option value="">Select Year</option>
                                                    <option value="1st Year">1st Year</option>
                                                    <option value="2nd Year">2nd Year</option>
                                                    <option value="3rd Year">3rd Year</option>
                                                    <option value="4th Year">4th Year</option>
                                                    <option value="5th Year">5th Year</option>
                                                </select>
                                                <InputError :message="userForm.errors.year_of_study" />
                                            </div>
                                        </div>

                                        <button
                                            type="submit"
                                            :disabled="userForm.processing"
                                            class="pixel-outline w-full rounded bg-red-700 px-4 py-2 font-bold text-white transition-colors hover:bg-red-600 disabled:opacity-50"
                                        >
                                            {{ userForm.processing ? 'Updating...' : 'Update User Details' }}
                                        </button>
                                    </form>
                                </div>

                                <!-- Timeline -->
                                <div class="pixel-outline rounded-lg border border-gray-600 bg-gray-800/50 p-6">
                                    <h3 class="pixel-font mb-4 text-lg font-semibold text-yellow-400">Timeline</h3>
                                    <div class="space-y-3">
                                        <div class="flex items-center text-sm">
                                            <div class="mr-3 h-2 w-2 rounded-full bg-green-400"></div>
                                            <span class="text-gray-300">Email verified: {{ formatDate(user.email_verified_at) }}</span>
                                        </div>
                                        <div class="flex items-center text-sm">
                                            <div class="mr-3 h-2 w-2 rounded-full bg-blue-400"></div>
                                            <span class="text-gray-300">Document uploaded: {{ formatDate(user.document_uploaded_at) }}</span>
                                        </div>
                                        <div v-if="user.verified_at" class="flex items-center text-sm">
                                            <div
                                                class="mr-3 h-2 w-2 rounded-full"
                                                :class="{
                                                    'bg-green-400': user.verification_status === 'approved',
                                                    'bg-red-400': user.verification_status === 'rejected',
                                                }"
                                            ></div>
                                            <span class="text-gray-300">
                                                {{ user.verification_status === 'approved' ? 'Approved' : 'Rejected' }}:
                                                {{ formatDate(user.verified_at) }}
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
                                <div class="pixel-outline rounded-lg border border-gray-600 bg-gray-800/50 p-6">
                                    <h3 class="pixel-font mb-4 text-lg font-semibold text-yellow-400">
                                        {{ user.user_role === 'student' ? 'COR Document' : 'ID Document' }}
                                    </h3>

                                    <div v-if="documentUrl" class="space-y-4">
                                        <div class="overflow-hidden rounded border border-gray-600">
                                            <img
                                                v-if="isDocumentImage"
                                                :src="documentUrl"
                                                :alt="user.user_role === 'student' ? 'COR Document' : 'ID Document'"
                                                class="h-auto max-h-96 w-full bg-white object-contain"
                                            />
                                            <div v-else class="bg-gray-700 p-8 text-center">
                                                <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <p class="mb-2 text-gray-300">PDF Document</p>
                                                <a
                                                    :href="documentUrl"
                                                    target="_blank"
                                                    class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    Open PDF
                                                </a>
                                            </div>
                                        </div>

                                        <div class="flex justify-center">
                                            <a
                                                :href="documentUrl"
                                                target="_blank"
                                                class="inline-flex items-center rounded bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700"
                                            >
                                                <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                View Full Size
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="py-8 text-center">
                                        <svg class="mx-auto mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5l7-7 7 7M5 20l7-7 7 7"
                                            />
                                        </svg>
                                        <p class="text-gray-400">No document uploaded</p>
                                    </div>
                                </div>

                                <!-- Verification Actions -->
                                <div
                                    v-if="user.verification_status === 'pending'"
                                    class="pixel-outline rounded-lg border border-gray-600 bg-gray-800/50 p-6"
                                >
                                    <h3 class="pixel-font mb-4 text-lg font-semibold text-yellow-400">Verification Decision</h3>

                                    <form @submit.prevent="submitDecision" class="space-y-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-300">Admin Notes</label>
                                            <textarea
                                                v-model="verificationForm.notes"
                                                rows="3"
                                                class="pixel-outline w-full rounded border border-gray-600 bg-black/50 px-3 py-2 text-white"
                                                placeholder="Add notes about the verification decision..."
                                            ></textarea>
                                            <InputError :message="verificationForm.errors.notes" />
                                        </div>

                                        <div class="flex space-x-4">
                                            <button
                                                @click="decision = 'approve'"
                                                type="submit"
                                                :disabled="verificationForm.processing"
                                                class="pixel-outline flex-1 rounded bg-green-600 px-4 py-3 font-bold text-white transition-colors hover:bg-green-700 disabled:opacity-50"
                                            >
                                                {{ verificationForm.processing && decision === 'approve' ? 'Approving...' : 'Approve' }}
                                            </button>
                                            <button
                                                @click="decision = 'reject'"
                                                type="submit"
                                                :disabled="verificationForm.processing"
                                                class="pixel-outline flex-1 rounded bg-red-600 px-4 py-3 font-bold text-white transition-colors hover:bg-red-700 disabled:opacity-50"
                                            >
                                                {{ verificationForm.processing && decision === 'reject' ? 'Rejecting...' : 'Reject' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Previous Decision Display -->
                                <div v-else class="pixel-outline rounded-lg border border-gray-600 bg-gray-800/50 p-6">
                                    <h3 class="pixel-font mb-4 text-lg font-semibold text-yellow-400">Verification Decision</h3>
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-300">
                                            Status:
                                            <span
                                                :class="{
                                                    'text-green-400': user.verification_status === 'approved',
                                                    'text-red-400': user.verification_status === 'rejected',
                                                }"
                                            >
                                                {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                                            </span>
                                        </p>
                                        <p v-if="user.verification_notes" class="text-sm text-gray-300">Notes: {{ user.verification_notes }}</p>
                                        <p v-if="user.verifiedBy" class="text-xs text-gray-400">
                                            Verified by {{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }} on
                                            {{ formatDate(user.verified_at) }}
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
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps({
    user: Object,
    documentUrl: String,
    programs: Array,
    isDocumentImage: Boolean,
});

const decision = ref('');

const userForm = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    program_id: props.user.program_id ? Number(props.user.program_id) : '',  // Convert to number if exists
    year_of_study: props.user.user_role === 'faculty' ? 'Graduate' : (props.user.year_of_study || ''),
});

// Get unique colleges from programs
const colleges = computed(() => {
    return [...new Set(props.programs.map(program => program.college))].sort();
});

const selectedCollege = ref('');

// Initialize college from program on page load
watch(() => props.user.program_id, (newProgramId) => {
    if (newProgramId) {
        const program = props.programs.find(p => p.id === newProgramId);
        if (program) {
            console.log('Setting initial college from program:', {
                program_id: newProgramId,
                college: program.college,
                program_name: program.name
            });
            selectedCollege.value = program.college;
        }
    }
}, { immediate: true });

const filteredPrograms = computed(() => {
    return selectedCollege.value ? props.programs.filter(program => program.college === selectedCollege.value) : props.programs;
});

// Watch for changes in college to set program_id
watch(selectedCollege, (newCollege) => {
    console.log('College changed:', {
        newCollege,
        role: props.user.user_role,
        currentProgramId: userForm.program_id
    });

    if (!newCollege) {
        console.log('No college selected, clearing program_id');
        userForm.program_id = '';
        return;
    }

    const availablePrograms = props.programs.filter(program => program.college === newCollege);
    console.log('Available programs for college:', availablePrograms);

    if (availablePrograms.length > 0) {
        if (props.user.user_role === 'faculty') {
            // For faculty, always select the first program and ensure Graduate status
            userForm.program_id = Number(availablePrograms[0].id);
            userForm.year_of_study = 'Graduate';
            console.log('Faculty: Auto-selected program and set year:', {
                college: newCollege,
                program_id: userForm.program_id,
                programName: availablePrograms[0].name,
                year_of_study: userForm.year_of_study
            });
        } else if (props.user.user_role === 'student') {
            // For students, check if their current program is in the new college
            const currentProgram = availablePrograms.find(p => p.id === Number(userForm.program_id));
            if (!currentProgram) {
                // If current program isn't in the new college, clear it
                userForm.program_id = '';
                console.log('Student: Cleared program_id as it\'s not in the new college');
            }
        }
    } else {
        userForm.program_id = '';
        console.log('No programs available for selected college');
    }
}, { immediate: true });


// Log initial form state
console.log('Initial form state:', {
    role: props.user.user_role,
    program_id: userForm.program_id,
    user_program: props.user.program,
    selectedCollege: selectedCollege.value
});

const verificationForm = useForm({
    notes: '',
});

const updateUserDetails = () => {
    // For faculty users
    if (props.user.user_role === 'faculty') {
        // Always ensure year_of_study is 'Graduate' for faculty
        userForm.year_of_study = 'Graduate';
        
        // Set program_id for faculty if college is selected
        if (selectedCollege.value) {
            const availablePrograms = props.programs.filter(program => program.college === selectedCollege.value);
            if (availablePrograms.length > 0) {
                userForm.program_id = Number(availablePrograms[0].id); // Ensure it's a number
            }
        }
    }

    // Validation before submit
    if ((props.user.user_role === 'faculty' || props.user.user_role === 'student') && !userForm.program_id) {
        toast.error('Please select a program before updating.');
        return;
    }

    // Ensure program_id is a number before submission
    if (userForm.program_id) {
        userForm.program_id = Number(userForm.program_id);
    }

    console.log('Updating user details:', {
        formData: userForm,
        selectedCollege: selectedCollege.value,
        filteredPrograms: filteredPrograms.value,
        role: props.user.user_role,
        program_id: userForm.program_id
    });

    userForm.patch(route('admin.verifications.update-details', props.user.id), {
        onSuccess: () => {
            console.log('User details updated successfully:', userForm);
            toast.success('User details updated successfully!');
        },
        onError: (errors) => {
            console.error('Update failed:', errors);
            toast.error('Failed to update user details. Please check the form for errors.');
        }
    });
};

const submitDecision = () => {
    if (decision.value === 'approve') {
        verificationForm.patch(route('admin.verifications.approve', props.user.id), {
            onSuccess: () => {
                toast.success(`✅ ${props.user.first_name} ${props.user.last_name}'s verification has been approved!`);
            },
            onError: () => {
                toast.error('Failed to approve verification. Please try again.');
            }
        });
    } else if (decision.value === 'reject') {
        // Notes are required for rejection, validate first
        if (!verificationForm.notes.trim()) {
            verificationForm.setError('notes', 'Notes are required when rejecting verification.');
            toast.error('Please provide notes explaining the rejection reason.');
            return;
        }
        verificationForm.patch(route('admin.verifications.reject', props.user.id), {
            onSuccess: () => {
                toast.success(`❌ ${props.user.first_name} ${props.user.last_name}'s verification has been rejected.`);
            },
            onError: () => {
                toast.error('Failed to reject verification. Please try again.');
            }
        });
    }
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
