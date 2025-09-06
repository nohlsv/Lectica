<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Pencil, Trash2, Plus, BookOpen } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { type File, type Flashcard } from '@/types';
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

interface Props {
    file: File;
    flashcards: Flashcard[];
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const flashcardToDelete = ref<Flashcard['id'] | null>(null);

function deleteFlashcard() {
    if (flashcardToDelete.value !== null) {
        router.delete(route('files.flashcards.destroy', [props.file.id, flashcardToDelete.value]), {
            onSuccess: () => {
                showDeleteModal.value = false;
                flashcardToDelete.value = null;
            },
        });
    }
}

const breadcrumbs = [
    { title: 'Home', href: route('home') },
    { title: props.file.name, href: route('files.show', props.file.id) },
    { title: 'Flashcards', href: route('files.flashcards.index', props.file.id) },
];

const isOwner = computed(() => {
    return props.file.can_edit === true;
});
</script>

<template>
    <Head title="Flashcards" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-6 sm:px-6 lg:px-8 bg-gradient">
            <div class="flex justify-between items-center">
                <div class="flex flex-wrap space-x-2">
                    <Link :href="route('files.show', file.id)">
                        <Button variant="default" class="inline-flex items-center gap-2 px-4 py-2 text-[#fce085] bg-red-700 border-2 border-[#f68500] rounded-md shadow-md hover:bg-yellow-400
                            hover:text-red-700 duration-300 font-bold pixel-outline mb-3">Back to File</Button>
                    </Link>
                    <div class="flex flex-wrap space-x-2">
                    <Link v-if="isOwner" :href="route('files.flashcards.create', file.id)">
                        <Button class="bg-orange-500 text-[#fdf6ee] hover:bg-orange-600 border-blue-700 rounded-lg pixel-outline">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Flashcard
                        </Button>
                    </Link>
                    <Link :href="route('files.flashcards.practice', file.id)">
                        <Button variant="default" class="bg-green-500 text-[#fdf6ee] hover:bg-green-600 border-blue-700 rounded-lg pixel-outline">
                            <BookOpen class="mr-2 h-4 w-4"/>
                            Practice
                        </Button>
                    </Link>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center">
                <h2 class="text-lg text-center sm:text-xl md:text-2xl font-bold welcome-banner py-2 px-4 animate-soft-bounce pixel-outline">Flashcards for "{{ file.name }}"</h2>
            </div>
            <div v-if="!flashcards || flashcards.length === 0" class="text-center py-10">
                <p class="text-muted-foreground">No flashcards found for this file.</p>
                <p class="text-muted-foreground mt-2">Create your first flashcard to start learning!</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="flashcard in flashcards" :key="flashcard.id" class="h-full overflow-hidden rounded-lg transition-all hover:bg-[#322017] bg-[#1C110E] border-[#0c0a03] border-2 text-[#F0EAD6] hover:scale-105 duration-300">
                    <CardHeader>
                        <CardTitle class="line-clamp-2 pixel-outline">{{ flashcard.question }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="mt-2 text-sm text-muted-foreground line-clamp-3 pixel-outline">{{ flashcard.answer }}</div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Link :href="route('files.flashcards.edit', [file.id, flashcard.id])" class="bg-blue-500 text-[#fdf6ee] hover:bg-blue-600 border-blue-700 rounded-lg">
                            <Button variant="outline" size="sm">
                                <Pencil class="h-4 w-4" />
                                <span class="ml-2 pixel-outline">Edit</span>
                            </Button>
                        </Link>
                        <Dialog>
                            <DialogTrigger>
                                <Button class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline" variant="default" size="sm" @click="() => { flashcardToDelete = flashcard.id; showDeleteModal = true; }">
                                    <Trash2 class="h-4 w-4" />
                                    <span class="ml-2 pixel-outline">Delete</span>
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Confirm Deletion</DialogTitle>
                                </DialogHeader>
                                <p>Are you sure you want to delete this flashcard? This action cannot be undone.</p>
                                <DialogFooter>
                                    <Button variant="outline" @click="showDeleteModal=false">Cancel</Button>
                                    <Button class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline" variant="default" @click="deleteFlashcard">
                                        Delete
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
