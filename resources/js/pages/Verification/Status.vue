<template>
    <div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-red-950 via-red-900 to-red-800 p-4">
        <div class="pixel-outline w-full max-w-2xl rounded-lg border border-yellow-400/50 bg-black/70 p-8 shadow-2xl backdrop-blur-sm">
            <div class="mb-8 text-center">
                <h1 class="pixel-font mb-2 text-3xl font-bold text-yellow-200">Verification Status</h1>
                <p class="text-gray-100">Track your document verification progress</p>
            </div>

            <!-- Status Card -->
            <div
                class="pixel-outline mb-8 rounded-lg p-6"
                :class="{
                    'border-yellow-400/50 bg-yellow-900/30': user.verification_status === 'pending',
                    'border-green-400/50 bg-green-900/30': user.verification_status === 'approved',
                    'border-red-400/50 bg-red-900/30': user.verification_status === 'rejected',
                }"
            >
                <div class="mb-4 flex items-center">
                    <div
                        class="mr-4 flex h-12 w-12 items-center justify-center rounded-full"
                        :class="{
                            'bg-yellow-400': user.verification_status === 'pending',
                            'bg-green-400': user.verification_status === 'approved',
                            'bg-red-400': user.verification_status === 'rejected',
                        }"
                    >
                        <svg v-if="user.verification_status === 'pending'" class="h-6 w-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <svg v-else-if="user.verification_status === 'approved'" class="h-6 w-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <svg v-else class="h-6 w-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2
                            class="text-xl font-bold"
                            :class="{
                                'text-yellow-200': user.verification_status === 'pending',
                                'text-green-200': user.verification_status === 'approved',
                                'text-red-200': user.verification_status === 'rejected',
                            }"
                        >
                            {{ getStatusTitle(user.verification_status) }}
                        </h2>
                        <p class="text-sm text-gray-100">
                            {{ getStatusDescription(user.verification_status) }}
                        </p>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div v-if="user.verification_notes" class="pixel-outline mt-4 rounded border border-red-400/30 bg-red-950/50 p-4">
                    <h3 class="mb-2 text-sm font-semibold text-gray-200">Admin Notes:</h3>
                    <p class="text-gray-100">{{ user.verification_notes }}</p>
                </div>
            </div>

            <!-- Timeline -->
            <div class="mb-8">
                <h3 class="pixel-font mb-4 text-lg font-semibold text-yellow-200">Verification Timeline</h3>
                <div class="space-y-4">
                    <!-- Email Verification -->
                    <div class="flex items-center">
                        <div class="mr-4 flex h-8 w-8 items-center justify-center rounded-full bg-green-400">
                            <svg class="h-4 w-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-100">Email Verified</p>
                            <p class="text-sm text-gray-200">{{ formatDate(user.email_verified_at) }}</p>
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="flex items-center">
                        <div
                            class="mr-4 flex h-8 w-8 items-center justify-center rounded-full"
                            :class="{
                                'bg-green-400': user.document_uploaded_at,
                                'bg-gray-600': !user.document_uploaded_at,
                            }"
                        >
                            <svg
                                class="h-4 w-4"
                                :class="{
                                    'text-black': user.document_uploaded_at,
                                    'text-gray-400': !user.document_uploaded_at,
                                }"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    v-if="user.document_uploaded_at"
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                                <path
                                    v-else
                                    fill-rule="evenodd"
                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="font-medium"
                                :class="{
                                    'text-gray-100': user.document_uploaded_at,
                                    'text-gray-300': !user.document_uploaded_at,
                                }"
                            >
                                Document Uploaded
                            </p>
                            <p class="text-sm text-gray-200">
                                {{ user.document_uploaded_at ? formatDate(user.document_uploaded_at) : 'Not yet uploaded' }}
                            </p>
                        </div>
                    </div>

                    <!-- Admin Verification -->
                    <div class="flex items-center">
                        <div
                            class="mr-4 flex h-8 w-8 items-center justify-center rounded-full"
                            :class="{
                                'bg-green-400': user.verification_status === 'approved',
                                'bg-red-400': user.verification_status === 'rejected',
                                'bg-yellow-400': user.verification_status === 'pending' && user.document_uploaded_at,
                                'bg-gray-600': user.verification_status === 'pending' && !user.document_uploaded_at,
                            }"
                        >
                            <svg v-if="user.verification_status === 'approved'" class="h-4 w-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg
                                v-else-if="user.verification_status === 'rejected'"
                                class="h-4 w-4 text-black"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <svg
                                v-else-if="user.verification_status === 'pending' && user.document_uploaded_at"
                                class="h-4 w-4 text-black"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <div v-else class="h-2 w-2 rounded-full bg-gray-400"></div>
                        </div>
                        <div>
                            <p
                                class="font-medium"
                                :class="{
                                    'text-green-200': user.verification_status === 'approved',
                                    'text-red-200': user.verification_status === 'rejected',
                                    'text-yellow-200': user.verification_status === 'pending' && user.document_uploaded_at,
                                    'text-gray-300': user.verification_status === 'pending' && !user.document_uploaded_at,
                                }"
                            >
                                Admin Verification
                            </p>
                            <p class="text-sm text-gray-200">
                                {{ getVerificationDateText() }}
                            </p>
                            <p v-if="user.verifiedBy && user.verified_at" class="text-xs text-gray-300">
                                Verified by {{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-4 sm:flex-row">
                <Link
                    v-if="user.verification_status === 'rejected' || !user.document_uploaded_at"
                    :href="route('verification.upload')"
                    class="pixel-outline flex-1 rounded bg-yellow-300 px-4 py-3 text-center font-bold text-white transition-colors hover:bg-yellow-200"
                >
                    {{ user.verification_status === 'rejected' ? 'Upload New Document' : 'Upload Document' }}
                </Link>

                <Link
                    v-if="user.verification_status === 'approved'"
                    :href="route('home')"
                    class="pixel-outline flex-1 rounded bg-green-300 px-4 py-3 text-center font-bold text-white transition-colors hover:bg-green-200"
                >
                    Continue to Dashboard
                </Link>

                <button
                    @click="refreshStatus"
                    class="pixel-outline flex-1 rounded bg-red-700 px-4 py-3 font-bold text-white transition-colors hover:bg-red-600"
                >
                    Refresh Status
                </button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="pixel-outline flex-1 rounded bg-gray-700 px-4 py-3 font-bold text-white transition-colors hover:bg-gray-600"
                >
                    Logout
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps({
    user: Object,
});

const page = usePage();

// Show success message if it exists in the session
onMounted(() => {
    if (page.props.flash?.success) {
        toast.success(page.props.flash.success);
    }
});

const getStatusTitle = (status) => {
    const titles = {
        pending: 'Verification Pending',
        approved: 'Verification Approved',
        rejected: 'Verification Rejected',
    };
    return titles[status] || 'Unknown Status';
};

const getStatusDescription = (status) => {
    const descriptions = {
        pending: 'Your document is under review by our admin team.',
        approved: 'Congratulations! Your account has been verified.',
        rejected: 'Your document was rejected. Please review the notes and upload a new document.',
    };
    return descriptions[status] || 'Status unknown';
};

const getVerificationDateText = () => {
    if (props.user.verified_at) {
        return formatDate(props.user.verified_at);
    } else if (props.user.document_uploaded_at) {
        return 'Under review';
    } else {
        return 'Waiting for document upload';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

const refreshStatus = () => {
    toast.success('Refreshing verification status...');

    // Hard reload the entire page
    window.location.reload();
};
</script>

<style scoped>
.pixel-font {
    font-family: 'Courier New', monospace;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.pixel-outline {
    box-shadow:
        0 0 0 1px currentColor,
        2px 2px 0 0 rgba(0, 0, 0, 0.5);
}
</style>
