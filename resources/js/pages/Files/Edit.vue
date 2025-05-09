<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type Tag } from '@/types';
import { ArrowLeftIcon } from 'lucide-vue-next';
import TagInput from '@/components/TagInput.vue';
import { ref } from 'vue';

interface Props {
    file: File;
    allTags: Tag[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: props.file.name,
        href: `/files/${props.file.id}`,
    },
    {
        title: 'Edit',
        href: `/files/${props.file.id}/edit`,
    },
];

const form = useForm({
    name: props.file.name,
    description: props.file.description || '',
    tags: props.file.tags as (any[] | undefined) || [],
});

const submit = () => {
    form.put(`/files/${props.file.id}`);
};

const isGenerating = ref(false);

const deleteFile = () => {
    if (confirm('Are you sure you want to delete this file?')) {
        form.delete(`/files/${props.file.id}`, {
            onSuccess: () => {
                router.visit('/files');
            },
        });
    }
};
</script>

<template>
    <Head title="Edit File" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Link
                    :href="`/files/${file.id}`"
                    class="inline-flex items-center gap-1 text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to File Details
                </Link>
                <h1 class="text-2xl font-bold">Edit File</h1>
            </div>

            <div class="rounded-lg border border-border p-6">
                <form @submit.prevent="submit" class="space-y-4 max-w-md">
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-foreground">File Name</label>
                        <input
                            type="text"
                            v-model="form.name"
                            id="name"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    
                    <!-- File Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-foreground">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background resize-none"
                            placeholder="Enter a brief description of this file (optional)"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tags" class="block text-sm font-medium text-foreground">Tags</label>
                        <TagInput
                            v-model="form.tags"
                            :existing-tags="allTags"
                        />
                        <p class="text-xs text-muted-foreground">
                            Add tags to categorize your file. You can create new tags or select existing ones.
                        </p>
                    </div>

                    <div class="flex justify-between items-center gap-2">
                        <div> 
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                                @click="deleteFile"
                                :disabled="form.processing"
                            >
                                Delete File
                            </button>
                        </div>

                        <div>
                            <Link
                                :href="`/files/${file.id}`"
                                class="inline-flex items-center justify-center rounded-md border border-border bg-background px-4 py-2 text-sm font-medium text-foreground hover:bg-accent"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

