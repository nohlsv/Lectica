<template>
    <div class="min-h-screen bg-gradient-to-br from-red-950 via-red-900 to-red-800 flex items-center justify-center p-4">
        <div class="bg-black/70 backdrop-blur-sm border border-yellow-400/50 rounded-lg shadow-2xl p-8 max-w-2xl w-full pixel-outline">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-yellow-200 mb-2 pixel-font">Verification Status</h1>
                <p class="text-gray-100">
                    Track your document verification progress
                </p>
            </div>

            <!-- Status Card -->
            <div class="mb-8 p-6 rounded-lg pixel-outline" :class="{
                'bg-yellow-900/30 border-yellow-400/50': user.verification_status === 'pending',
                'bg-green-900/30 border-green-400/50': user.verification_status === 'approved',
                'bg-red-900/30 border-red-400/50': user.verification_status === 'rejected'
            }">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mr-4" :class="{
                        'bg-yellow-400': user.verification_status === 'pending',
                        'bg-green-400': user.verification_status === 'approved',
                        'bg-red-400': user.verification_status === 'rejected'
                    }">
                        <svg v-if="user.verification_status === 'pending'" class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else-if="user.verification_status === 'approved'" class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold" :class="{
                            'text-yellow-200': user.verification_status === 'pending',
                            'text-green-200': user.verification_status === 'approved',
                            'text-red-200': user.verification_status === 'rejected'
                        }">
                            {{ getStatusTitle(user.verification_status) }}
                        </h2>
                        <p class="text-gray-100 text-sm">
                            {{ getStatusDescription(user.verification_status) }}
                        </p>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div v-if="user.verification_notes" class="mt-4 p-4 bg-red-950/50 border border-red-400/30 rounded pixel-outline">
                    <h3 class="text-sm font-semibold text-gray-200 mb-2">Admin Notes:</h3>
                    <p class="text-gray-100">{{ user.verification_notes }}</p>
                </div>
            </div>

            <!-- Timeline -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-yellow-200 mb-4 pixel-font">Verification Timeline</h3>
                <div class="space-y-4">
                    <!-- Email Verification -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-green-400 flex items-center justify-center mr-4">
                            <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-100 font-medium">Email Verified</p>
                            <p class="text-gray-200 text-sm">{{ formatDate(user.email_verified_at) }}</p>
                        </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-4" :class="{
                            'bg-green-400': user.document_uploaded_at,
                            'bg-gray-600': !user.document_uploaded_at
                        }">
                            <svg class="w-4 h-4" :class="{
                                'text-black': user.document_uploaded_at,
                                'text-gray-400': !user.document_uploaded_at
                            }" fill="currentColor" viewBox="0 0 20 20">
                                <path v-if="user.document_uploaded_at" fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                <path v-else fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium" :class="{
                                'text-gray-100': user.document_uploaded_at,
                                'text-gray-300': !user.document_uploaded_at
                            }">Document Uploaded</p>
                            <p class="text-gray-200 text-sm">
                                {{ user.document_uploaded_at ? formatDate(user.document_uploaded_at) : 'Not yet uploaded' }}
                            </p>
                        </div>
                    </div>

                    <!-- Admin Verification -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center mr-4" :class="{
                            'bg-green-400': user.verification_status === 'approved',
                            'bg-red-400': user.verification_status === 'rejected',
                            'bg-yellow-400': user.verification_status === 'pending' && user.document_uploaded_at,
                            'bg-gray-600': user.verification_status === 'pending' && !user.document_uploaded_at
                        }">
                            <svg v-if="user.verification_status === 'approved'" class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <svg v-else-if="user.verification_status === 'rejected'" class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <svg v-else-if="user.verification_status === 'pending' && user.document_uploaded_at" class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <div v-else class="w-2 h-2 bg-gray-400 rounded-full"></div>
                        </div>
                        <div>
                            <p class="font-medium" :class="{
                                'text-green-200': user.verification_status === 'approved',
                                'text-red-200': user.verification_status === 'rejected',
                                'text-yellow-200': user.verification_status === 'pending' && user.document_uploaded_at,
                                'text-gray-300': user.verification_status === 'pending' && !user.document_uploaded_at
                            }">Admin Verification</p>
                            <p class="text-gray-200 text-sm">
                                {{ getVerificationDateText() }}
                            </p>
                            <p v-if="user.verifiedBy && user.verified_at" class="text-gray-300 text-xs">
                                Verified by {{ user.verifiedBy.first_name }} {{ user.verifiedBy.last_name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <Link 
                    v-if="user.verification_status === 'rejected' || !user.document_uploaded_at"
                    :href="route('verification.upload')" 
                    class="flex-1 bg-yellow-300 hover:bg-yellow-200 text-white font-bold py-3 px-4 rounded pixel-outline text-center transition-colors"
                >
                    {{ user.verification_status === 'rejected' ? 'Upload New Document' : 'Upload Document' }}
                </Link>
                
                <Link 
                    v-if="user.verification_status === 'approved'"
                    :href="route('home')" 
                    class="flex-1 bg-green-300 hover:bg-green-200 text-white font-bold py-3 px-4 rounded pixel-outline text-center transition-colors"
                >
                    Continue to Dashboard
                </Link>
                
                <button 
                    @click="refreshStatus" 
                    class="flex-1 bg-red-700 hover:bg-red-600 text-white font-bold py-3 px-4 rounded pixel-outline transition-colors"
                >
                    Refresh Status
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    user: Object
});

const getStatusTitle = (status) => {
    const titles = {
        pending: 'Verification Pending',
        approved: 'Verification Approved',
        rejected: 'Verification Rejected'
    };
    return titles[status] || 'Unknown Status';
};

const getStatusDescription = (status) => {
    const descriptions = {
        pending: 'Your document is under review by our admin team.',
        approved: 'Congratulations! Your account has been verified.',
        rejected: 'Your document was rejected. Please review the notes and upload a new document.'
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
    router.reload({ only: ['user'] });
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