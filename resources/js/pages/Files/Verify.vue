<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type File } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircleIcon, FileIcon, XCircleIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    files: {
        data: File[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
    };
    filters?: {
        college: string;
        show_all_colleges: boolean;
    };
    user_college?: string;
    available_colleges?: string[];
    is_admin?: boolean;
}

const props = defineProps<Props>();

const showDenyModal = ref(false);
const selectedFileId = ref<number | null>(null);
const denialReason = ref('');
const verifyingFiles = ref<Set<number>>(new Set());

// Filter state (only for admin users)
const currentCollegeFilter = ref(props.filters?.college || 'my_college');
const showAllColleges = ref(props.filters?.show_all_colleges || false);

const verifyFile = (fileId: number) => {
    // Prevent multiple verification attempts for the same file
    if (verifyingFiles.value.has(fileId)) {
        return;
    }

    verifyingFiles.value.add(fileId);

    router.patch(
        route('files.verify.update', fileId),
        {},
        {
            onSuccess: () => {
                toast.success('File verified successfully!');
                verifyingFiles.value.delete(fileId);
                router.reload({ only: ['files'] }); // Refresh only the files data
            },
            onError: () => {
                toast.error('Failed to verify the file. Please try again.');
                verifyingFiles.value.delete(fileId);
            },
        },
    );
};

const openDenyModal = (fileId: number) => {
    selectedFileId.value = fileId;
    denialReason.value = '';
    showDenyModal.value = true;
};

const denyFile = () => {
    if (!selectedFileId.value || !denialReason.value.trim()) {
        toast.error('Please provide a reason for denial.');
        return;
    }

    router.patch(
        route('files.verify.deny', selectedFileId.value),
        { denial_reason: denialReason.value },
        {
            onSuccess: () => {
                toast.success('File denied and user notified!');
                showDenyModal.value = false;
                selectedFileId.value = null;
                denialReason.value = '';
                router.reload({ only: ['files'] }); // Refresh only the files data
            },
            onError: () => {
                toast.error('Failed to deny the file. Please try again.');
            },
        },
    );
};

// Filter methods
const applyCollegeFilter = (college: string) => {
    currentCollegeFilter.value = college;
    showAllColleges.value = college === 'all';

    const params = new URLSearchParams();
    if (college !== 'my_college') {
        params.set('college', college);
    }
    if (college === 'all') {
        params.set('show_all_colleges', 'true');
    }

    const queryString = params.toString();
    const url = queryString ? `/files/verify?${queryString}` : '/files/verify';

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getFilterDisplayText = () => {
    if (showAllColleges.value || currentCollegeFilter.value === 'all') {
        return 'All Colleges';
    }
    if (currentCollegeFilter.value === 'my_college') {
        if (!props.user_college) {
            return 'All Colleges';
        }
        return props.user_college ? `My College (${props.user_college})` : 'My College';
    }
    return currentCollegeFilter.value;
};
</script>

<template>
    <Head title="Verify Files" />
    <AppLayout>
        <div class="bg-gradient min-h-screen space-y-4 p-3 sm:space-y-6 sm:p-6">
            <div class="mx-auto flex max-w-md justify-center">
                <h1 class="welcome-banner animate-soft-bounce pixel-outline w-fit px-6 py-2 text-center text-lg font-bold sm:px-10 sm:text-xl lg:text-2xl">Verify Files</h1>
            </div>

            <!-- College Filter (Admin Only) -->
            <div v-if="props.is_admin && (props.user_college || (props.available_colleges && props.available_colleges.length > 0))" class="bg-container p-3 sm:p-4">
                <div class="flex flex-col gap-3 sm:gap-4">
                    <label class="pixel-outline text-sm font-medium text-gray-200 sm:text-base">Filter by College:</label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:flex lg:flex-wrap">
                        <!-- My College Button -->
                        <button
                            v-if="props.user_college"
                            @click="applyCollegeFilter('my_college')"
                            :class="[
                                'pixel-outline rounded-md border-2 px-4 py-3 text-sm font-medium transition-colors sm:py-2 sm:text-base lg:px-3 lg:py-1.5',
                                currentCollegeFilter === 'my_college' && !showAllColleges
                                    ? 'border-blue-500 bg-blue-600 text-white'
                                    : 'border-gray-500 bg-gray-700 text-gray-200 hover:bg-gray-600',
                            ]"
                        >
                            My College ({{ props.user_college }})
                        </button>

                        <!-- All Colleges Button -->
                        <button
                            @click="applyCollegeFilter('all')"
                            :class="[
                                'pixel-outline rounded-md border-2 px-4 py-3 text-sm font-medium transition-colors sm:py-2 sm:text-base lg:px-3 lg:py-1.5',
                                showAllColleges || currentCollegeFilter === 'all'
                                    ? 'border-green-500 bg-green-600 text-white'
                                    : 'border-gray-500 bg-gray-700 text-gray-200 hover:bg-gray-600',
                            ]"
                        >
                            All Colleges
                        </button>

                        <!-- Individual College Buttons -->
                        <button
                            v-for="college in props.available_colleges?.filter((c) => c !== props.user_college)"
                            :key="college"
                            @click="applyCollegeFilter(college)"
                            :class="[
                                'pixel-outline rounded-md border-2 px-4 py-3 text-sm font-medium transition-colors sm:py-2 sm:text-base lg:px-3 lg:py-1.5',
                                currentCollegeFilter === college && !showAllColleges
                                    ? 'border-purple-500 bg-purple-600 text-white'
                                    : 'border-gray-500 bg-gray-700 text-gray-200 hover:bg-gray-600',
                            ]"
                        >
                            {{ college }}
                        </button>
                    </div>
                </div>

                <!-- Current filter indicator -->
                <div class="pixel-outline mt-3 text-sm text-gray-400 sm:text-xs">
                    Currently showing: <span class="font-medium text-gray-200">{{ getFilterDisplayText() }}</span>
                    <span v-if="props.files.data.length > 0" class="ml-2">({{ props.files.data.length }} files)</span>
                </div>
            </div>

            <!-- Faculty College Info (Non-Admin) -->
            <div v-else-if="!props.is_admin && props.user_college" class="bg-container p-3 sm:p-4">
                <div class="pixel-outline text-sm text-gray-400 sm:text-base">
                    Showing files from: <span class="font-medium text-gray-200">{{ props.user_college }}</span>
                    <span v-if="props.files.data.length > 0" class="ml-2">({{ props.files.data.length }} files)</span>
                </div>
            </div>

            <div class="bg-container p-3 sm:p-6">
                <div v-if="props.files.data.length === 0" class="text-muted-foreground py-8 text-center text-base sm:py-12 sm:text-lg">No unverified files available.</div>
                <div v-else class="grid gap-3 sm:gap-4 sm:grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    <div
                        v-for="file in props.files.data"
                        :key="file.id"
                        class="bg-container flex h-full flex-col rounded-lg border-2 border-[#0c0a03] p-3 sm:p-4"
                    >
                        <h2 
                            class="pixel-outline text-lg font-semibold text-[#fdf6ee] sm:text-xl md:text-2xl truncate" 
                            :title="file.name"
                        >
                            {{ file.name }}
                        </h2>
                        <p class="text-sm text-[#fdf6ee]/50 sm:text-base lg:text-base">
                            Uploaded by: <span class="ml-1">{{ file.user.first_name }} {{ file.user.last_name }}</span>
                        </p>
                        <p v-if="file.user.program" class="text-sm text-[#fdf6ee]/50 sm:text-base lg:text-base">
                            College: <span class="ml-1">{{ file.user.program.college }}</span>
                        </p>
                        <p v-if="file.user.program" class="text-sm text-[#fdf6ee]/50 sm:text-base lg:text-base">
                            Program: <span class="ml-1">{{ file.user.program.name }}</span>
                        </p>
                        <p class="text-sm text-[#fdf6ee]/50 sm:text-base lg:text-base">
                            Description: <span class="ml-1">{{ file.description || 'No description provided' }}</span>
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span v-for="tag in file.tags" :key="tag.id" class="bg-accent text-foreground rounded-full px-2 py-1 text-xs sm:text-sm">
                                {{ tag.name }}
                            </span>
                        </div>
                        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                            <button
                                class="pixel-outline flex h-12 cursor-pointer items-center justify-center rounded-md border-2 border-[#0c0a03] bg-[#6aa7d6] px-4 py-3 text-sm font-medium text-[#fdf6ee] duration-300 hover:bg-[#578ec3] sm:h-auto sm:px-4 sm:py-2 sm:text-base"
                            >
                                <Link :href="route('files.show', file.id)" target="_blank" class="flex items-center">
                                    <FileIcon class="pixel-outline-icon mr-2 h-4 w-4" />
                                    <span>View File</span>
                                </Link>
                            </button>
                            <button
                                @click="verifyFile(file.id)"
                                :disabled="verifyingFiles.has(file.id)"
                                :class="[
                                    'pixel-outline flex h-12 items-center justify-center rounded-md border-2 border-[#0c0a03] px-4 py-3 text-sm font-medium text-[#fdf6ee] duration-300 sm:h-auto sm:px-4 sm:py-2 sm:text-base',
                                    verifyingFiles.has(file.id)
                                        ? 'cursor-not-allowed bg-gray-500 opacity-50'
                                        : 'cursor-pointer bg-[#5cae6e] hover:bg-[#4a9159]',
                                ]"
                            >
                                <CheckCircleIcon class="pixel-outline-icon mr-2 h-4 w-4" />
                                <span>{{ verifyingFiles.has(file.id) ? 'Verifying...' : 'Verify' }}</span>
                            </button>
                            <button
                                @click="openDenyModal(file.id)"
                                class="pixel-outline flex h-12 cursor-pointer items-center justify-center rounded-md border-2 border-[#0c0a03] bg-[#B23A48] px-4 py-3 text-sm font-medium text-[#fdf6ee] duration-300 hover:bg-[#9a2f3d] sm:h-auto sm:px-4 sm:py-2 sm:text-base"
                            >
                                <XCircleIcon class="pixel-outline-icon mr-2 h-4 w-4" />
                                <span>Deny</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Pagination -->
                <div v-if="props.files.links && props.files.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex space-x-1 sm:space-x-2">
                        <button
                            v-for="link in props.files.links"
                            :key="link.label"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            class="rounded px-3 py-2 text-sm sm:px-4 sm:text-base"
                            :class="{
                                'bg-blue-500 text-white': link.active,
                                'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600': !link.active && link.url,
                                'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500': !link.url,
                            }"
                            v-html="link.label"
                        ></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Denial Modal -->
        <div v-if="showDenyModal" class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4">
            <div class="bg-container w-full max-w-md rounded-lg p-4 sm:p-6">
                <h3 class="pixel-outline mb-4 text-lg font-bold text-[#fdf6ee] sm:text-xl">Deny File</h3>
                <p class="mb-4 text-sm text-[#fdf6ee]/70 sm:text-base">Please provide a reason for denying this file:</p>
                <textarea
                    v-model="denialReason"
                    placeholder="Enter reason for denial..."
                    class="mb-4 w-full rounded border-2 border-[#0c0a03] bg-[#3B1A14] px-3 py-3 text-sm text-[#fdf6ee] placeholder-[#fdf6ee]/50 focus:border-[#B23A48] focus:outline-none sm:py-2 sm:text-base"
                    rows="4"
                ></textarea>
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button
                        @click="showDenyModal = false"
                        class="pixel-outline h-12 rounded border-2 border-[#0c0a03] bg-[#3B1A14] px-4 py-3 text-sm font-medium text-[#fdf6ee] duration-300 hover:bg-[#77252e] sm:h-auto sm:py-2 sm:text-base"
                    >
                        Cancel
                    </button>
                    <button
                        @click="denyFile"
                        class="pixel-outline h-12 rounded border-2 border-[#0c0a03] bg-[#B23A48] px-4 py-3 text-sm font-medium text-[#fdf6ee] duration-300 hover:bg-[#9a2f3d] sm:h-auto sm:py-2 sm:text-base"
                    >
                        Deny File
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
