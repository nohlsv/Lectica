<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File } from '@/types';
import { ArrowLeftIcon } from 'lucide-vue-next';

interface Props {
    file: File;
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
});

const submit = () => {
    form.put(`/files/${props.file.id}`);
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
                    </div>

                    <div class="flex justify-end gap-2">
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
                </form>
            </div>
        </div>
    </AppLayout>
</template>

