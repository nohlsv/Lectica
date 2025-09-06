<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2, Plus, ListChecks } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { type File , type Quiz } from '@/types';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

interface Props {
    file: File;
    quizzes: Quiz[];
    quizTypes: Record<string, string>;
}

const props = defineProps<Props>();

const showDeleteModal = ref(false);
const quizToDelete = ref<number | null>(null);

function deleteQuiz() {
    if (quizToDelete.value !== null) {
        router.delete(route('files.quizzes.destroy', [props.file.id, quizToDelete.value]), {
            onSuccess: () => {
                showDeleteModal.value = false;
                quizToDelete.value = null;
            },
        });
    }
}

const breadcrumbs = [
    { title: 'Home', href: route('home') },
    { title: props.file.name, href: route('files.show', props.file.id) },
    { title: 'Quizzes', href: route('files.quizzes.index', props.file.id) },
];

const isOwner = computed(() => {
    return props.file.can_edit === true;
});
</script>

<template>
    <Head title="Quizzes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full space-y-6 p-6 sm:px-6 lg:px-8 bg-gradient">
            <div class="flex justify-between">
                <div class="flex flex-wrap space-x-2">
                    <Link :href="route('files.show', file.id)">
                        <Button variant="default" class="inline-flex items-center gap-2 px-4 py-2 text-[#fce085] bg-red-700 border-2 border-[#f68500] rounded-md shadow-md hover:bg-yellow-400
                    hover:text-red-700 duration-300 font-bold pixel-outline mb-3 sm:mb-0">Back to File</Button>
                    </Link>
                    <div class="flex flex-wrap space-x-2">
                        <Link v-if="isOwner" :href="route('files.quizzes.create', file.id)">
                            <Button class="bg-orange-500 text-[#fdf6ee] hover:bg-orange-600 border-blue-700 rounded-lg pixel-outline">
                                <Plus class="mr-2 h-4 w-4" />
                                Create Quiz
                            </Button>
                        </Link>
                        <Link :href="route('files.quizzes.test', file.id)">
                            <Button variant="default" class="bg-green-500 text-[#fdf6ee] hover:bg-green-600 border-blue-700 rounded-lg pixel-outline">
                                <ListChecks class="mr-2 h-4 w-4" />
                                Practice Quiz
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-center">
                <h2 class="text-lg text-center sm:text-xl md:text-2xl font-bold welcome-banner py-2 px-4 animate-soft-bounce pixel-outline">Quizzes for "{{ file.name }}"</h2>
            </div>
            <div v-if="quizzes.length === 0" class="text-center py-10">
                <p class="text-muted-foreground">No quizzes found for this file.</p>
                <p class="text-muted-foreground mt-2">Create your first quiz to start testing your knowledge!</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="quiz in quizzes" :key="quiz.id" class="h-full overflow-hidden rounded-lg transition-all hover:bg-[#322017] bg-[#1C110E] border-[#0c0a03] border-2 text-[#F0EAD6] hover:scale-105 duration-300">
                    <CardHeader>
                        <div class="flex justify-between items-start">
                            <CardTitle class="line-clamp-2 pixel-outline">{{ quiz.question }}</CardTitle>
                            <Badge>{{ quizTypes[quiz.type] }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="quiz.type === 'multiple_choice' && quiz.options">
                            <p class="text-sm text-muted-foreground mb-2 pixel-outline">Options:</p>
                            <ul class="list-disc list-inside">
                                <li v-for="(option, index) in quiz.options" :key="index" class="text-sm pixel-outline">
                                    {{ option }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="quiz.type === 'enumeration'">
                            <p class="text-sm text-muted-foreground pixel-outline">
                                {{ quiz.answers.length }} item(s) to enumerate
                            </p>
                        </div>
                        <div v-else-if="quiz.type === 'true_false'">
                            <p class="text-sm text-muted-foreground pixel-outline">
                                Answer: {{ quiz.answers[0] === 'true' ? 'True' : 'False' }}
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Link :href="route('files.quizzes.edit', [file.id, quiz.id])">
                            <Button variant="default" size="sm" class="bg-blue-500 text-[#fdf6ee] hover:bg-blue-600 border-blue-700">
                                <Pencil class="h-4 w-4" />
                                <span class="ml-2 pixel-outline">Edit</span>
                            </Button>
                        </Link>
                        <Dialog>
                            <DialogTrigger>
                                <Button class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline" variant="default" size="sm" @click="() => { quizToDelete = quiz.id; showDeleteModal = true; }">
                                    <Trash2 class="h-4 w-4" />
                                    <span class="ml-2 pixel-outline">Delete</span>
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Confirm Deletion</DialogTitle>
                                </DialogHeader>
                                <p>Are you sure you want to delete this quiz? This action cannot be undone.</p>
                                <DialogFooter>
                                    <Button variant="outline" @click="showDeleteModal = false">Cancel</Button>
                                    <Button class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline" variant="default" @click="deleteQuiz">
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
