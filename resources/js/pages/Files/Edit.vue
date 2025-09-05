<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File, type Tag } from '@/types';
import { ArrowLeftIcon } from 'lucide-vue-next';
import TagInput from '@/components/TagInput.vue';
import { ref } from 'vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import { DialogClose } from '@/components/ui/dialog'



interface Props {
    file: File;
    allTags: Tag[];
}

const props = defineProps<Props>();

const showDeleteModal = ref(false)

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
    form.delete(`/files/${props.file.id}`, {
        onSuccess: () => {
            router.visit('/files');
        },
    });
};
</script>

<template>
    <Head title="Edit File" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6 bg-gradient">
           <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between w-full px-4 gap-4">
                <!-- Back Button -->
                <div class="sm:flex-1 sm:order-1">
                    <Link
                    :href="`/files/${file.id}`"
                    class="inline-flex items-center gap-2 px-3 py-1 text-[#fce085] bg-red-700 border-2 border-[#f68500] rounded-md shadow-md hover:bg-yellow-400
                            hover:text-red-700 duration-300 font-bold"
                    >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back
                    </Link>
                </div>

                <!-- Centered Title -->
                <div class="sm:flex-1 sm:order-2 flex justify-center">
                    <h1 class="text-2xl font-bold welcome-banner py-2 px-6 animate-soft-bounce text-center leading-tight">
                    Edit File
                    </h1>
                </div>

                <!-- Spacer to balance layout -->
                <div class="sm:flex-1 sm:order-3 hidden sm:block"></div>
            </div>


            <div class="rounded-lg bg-container p-6 flex justify-center">
                <form @submit.prevent="submit" class="space-y-4 max-w-md">
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-[#fce085]">File Name</label>
                        <input
                            type="text"
                            v-model="form.name"
                            id="name"
                            class="w-full rounded-md border border-input bg-[#FFF8F2]/80 px-3 py-2 text-sm text-[#333333] ring-offset-background"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- File Description -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-[#fce085]">Description</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border border-input bg-[#FFF8F2]/80 text-[#333333]  px-3 py-2 text-sm ring-offset-background resize-none"
                            placeholder="Enter a brief description of this file (optional)"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Tags -->
                    <div class="space-y-2">
                        <label for="tags" class="block text-sm font-medium text-[#fce085]">Tags</label>
                        <TagInput
                            v-model="form.tags"
                            :existing-tags="allTags"
                        />
                        <p class="text-xs text-muted-foreground">
                            Add tags to categorize your file. You can create new tags or select existing ones.
                        </p>
                    </div>

                    <div class="flex items-center justify-between w-full px-4 sm:w-auto">
                        <!-- Right-aligned Buttons -->
                        <div class="flex items-center gap-2 ml-auto">
                            <!-- Delete Button with Dialog -->
                            <Dialog>
                            <DialogTrigger as-child>
                                <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md bg-red-500 hover:bg-red-600 px-4 py-2 text-sm font-medium text-foreground pixel-outline border-[#0c0a03] border-2 duration-300"
                                :disabled="form.processing"
                                >
                                Delete File
                                </button>
                            </DialogTrigger>
                            <DialogContent class="bg-[#912414] border-4 border-[#feaf00] rounded-lg">
                                <DialogHeader>
                                <DialogTitle class="pixel-outline tracking-wide">Confirm Deletion</DialogTitle>
                                </DialogHeader>
                                    <p class="pixel-outline tracking-wide">Are you sure you want to delete this file? This action cannot be undone.</p>
                                <DialogFooter>
                                <DialogClose as-child @click="showDeleteModal = false">
                                    <Button variant="black">Cancel</Button>
                                </DialogClose>
                                    <Button Button variant="delete" @click="deleteFile" :disabled="form.processing">
                                    {{ form.processing ? 'Deleting...' : 'Delete' }}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                            </Dialog>

                            <!-- Save Button -->
                            <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-[#10B981] hover:bg-[#0e9459] px-4 py-2 text-sm font-medium border-[#0c0a03] border-2 pixel-outline duration-300"
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

