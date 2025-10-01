<template>
    <div class="min-h-screen bg-gradient-to-br from-red-950 via-red-900 to-red-800 flex items-center justify-center p-4">
        <div class="bg-black/70 backdrop-blur-sm border border-yellow-400/50 rounded-lg shadow-2xl p-8 max-w-md w-full pixel-outline">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-yellow-300 mb-2 pixel-font">Document Verification</h1>
                <p class="text-gray-100 text-sm">
                    Upload your {{ documentRequired }} for admin verification
                </p>
            </div>

            <!-- Show current status if document exists -->
            <div v-if="user.verification_document_path" class="mb-6 p-4 bg-red-900/40 border border-red-400/60 rounded pixel-outline">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-red-200 font-medium">Document Uploaded</span>
                </div>
                <p class="text-gray-100 text-sm">Status: 
                    <span :class="{
                        'text-yellow-300': user.verification_status === 'pending',
                        'text-green-300': user.verification_status === 'approved',
                        'text-red-300': user.verification_status === 'rejected'
                    }">
                        {{ user.verification_status.charAt(0).toUpperCase() + user.verification_status.slice(1) }}
                    </span>
                </p>
                <p v-if="user.document_uploaded_at" class="text-gray-200 text-xs mt-1">
                    Uploaded: {{ new Date(user.document_uploaded_at).toLocaleString() }}
                </p>
            </div>

            <!-- Show rejection notes if rejected -->
            <div v-if="user.verification_status === 'rejected' && user.verification_notes" class="mb-6 p-4 bg-red-900/50 border border-red-300/60 rounded pixel-outline">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-200 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-100 font-medium">Verification Rejected</span>
                </div>
                <p class="text-gray-100 text-sm">{{ user.verification_notes }}</p>
                <p class="text-yellow-200 text-sm mt-2">Please upload a new document.</p>
            </div>

            <form @submit.prevent="uploadDocument" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-yellow-200 text-sm font-bold mb-2 pixel-font" for="document">
                        {{ user.user_role === 'student' ? 'COR (Certificate of Registration)' : 'Valid ID' }}
                    </label>
                    <input
                        id="document"
                        name="document"
                        ref="fileInput"
                        type="file"
                        accept="image/*,.pdf"
                        @change="handleFileChange"
                        class="w-full px-3 py-2 bg-black/60 border border-red-400/50 rounded pixel-outline text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-yellow-300 file:text-black hover:file:bg-yellow-200"
                        required
                    />
                    <p class="text-gray-200 text-xs mt-1">
                        Accepted formats: JPG, PNG, PDF (Max 5MB)
                    </p>
                    <InputError :message="form.errors.document" />
                </div>

                <!-- File preview -->
                <div v-if="selectedFile" class="mb-4 p-3 bg-red-950/60 border border-red-400/50 rounded pixel-outline">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-100 text-sm">{{ selectedFile.name }}</span>
                        <span class="text-gray-200 text-xs ml-2">({{ formatFileSize(selectedFile.size) }})</span>
                    </div>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing || !selectedFile"
                    class="w-full bg-yellow-300 hover:bg-yellow-200 text-white font-bold py-3 px-4 rounded pixel-outline transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="form.processing">Uploading...</span>
                    <span v-else>{{ user.verification_document_path ? 'Replace Document' : 'Upload Document' }}</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <Link :href="route('verification.status')" class="text-red-200 hover:text-red-100 text-sm underline">
                    View Verification Status
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    user: Object,
    documentRequired: String
});

const fileInput = ref(null);
const selectedFile = ref(null);

const form = useForm({
    document: null
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    selectedFile.value = file;
    form.document = file;
    console.log('File selected:', file ? file.name : 'No file');
};

const uploadDocument = () => {
    console.log('Uploading document:', form.document);
    
    if (!form.document) {
        alert('Please select a file first');
        return;
    }
    
    form.post(route('verification.upload.store'), {
        forceFormData: true, // Ensure multipart/form-data is used
        onSuccess: () => {
            selectedFile.value = null;
            fileInput.value.value = '';
        },
        onError: (errors) => {
            console.error('Upload failed:', errors);
        }
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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