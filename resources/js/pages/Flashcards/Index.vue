<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/Components/ui/card';
import { Pencil, Trash2, Plus, BookOpen } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    file: Object,
    flashcards: Array,
});

function deleteFlashcard(flashcardId) {
    if (confirm('Are you sure you want to delete this flashcard?')) {
        router.delete(route('files.flashcards.destroy', [props.file.id, flashcardId]));
    }
}

const breadcrumbs = [
    { title: 'Home', href: route('home') },
    { title: props.file.name, href: route('files.show', props.file.id) },
    { title: 'Flashcards', href: route('files.flashcards.index', props.file.id) },
];
</script>

<template>
    <Head title="Flashcards" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Flashcards for "{{ file.name }}"</h2>
                <div class="flex space-x-2">
                    <Link :href="route('files.show', file.id)">
                        <Button variant="outline">Back to File</Button>
                    </Link>
                    <Link :href="route('files.flashcards.create', file.id)">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Flashcard
                        </Button>
                    </Link>
                    <Link :href="route('files.flashcards.practice', file.id)">
                        <Button variant="default">
                            <BookOpen class="mr-2 h-4 w-4" />
                            Practice
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="!flashcards || flashcards.length === 0" class="text-center py-10">
                <p class="text-gray-500">No flashcards found for this file.</p>
                <p class="text-gray-500 mt-2">Create your first flashcard to start learning!</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="flashcard in flashcards" :key="flashcard.id" class="overflow-hidden">
                    <CardHeader>
                        <CardTitle class="line-clamp-2">{{ flashcard.question }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="mt-2 text-sm text-gray-700 line-clamp-3">{{ flashcard.answer }}</div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Link :href="route('files.flashcards.edit', [file.id, flashcard.id])">
                            <Button variant="outline" size="sm">
                                <Pencil class="h-4 w-4" />
                                <span class="ml-2">Edit</span>
                            </Button>
                        </Link>
                        <Button variant="destructive" size="sm" @click="deleteFlashcard(flashcard.id)">
                            <Trash2 class="h-4 w-4" />
                            <span class="ml-2">Delete</span>
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
