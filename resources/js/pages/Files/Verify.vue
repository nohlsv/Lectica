<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { type File } from '@/types';
import { ref } from 'vue';

interface Props {
    files: {
        data: File[];
        links: any;
    };
}

const props = defineProps<Props>();

const verifyFile = (fileId: number) => {
    router.patch(route('files.verify.update', fileId), {}, {
        onSuccess: () => {
            alert('File verified successfully!');
        },
    });
};
</script>

<template>
    <Head title="Verify Files" />
    <AppLayout>
        <div class="p-6 space-y-6">
            <h1 class="text-2xl font-bold">Verify Files</h1>
            <div v-if="props.files.data.length === 0" class="text-center text-muted-foreground">
                No unverified files available.
            </div>
            <div v-else class="space-y-4">
                <div v-for="file in props.files.data" :key="file.id" class="p-4 bg-white rounded-lg shadow">
                    <h2 class="text-lg font-semibold">{{ file.name }}</h2>
                    <p class="text-sm text-muted-foreground">Uploaded by: {{ file.user.first_name }} {{ file.user.last_name }}</p>
                    <p class="text-sm text-muted-foreground">Description: {{ file.description || 'No description provided' }}</p>
                    <button
                        @click="verifyFile(file.id)"
                        class="mt-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90"
                    >
                        Verify
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
