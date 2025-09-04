<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type File, type Quiz } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ListChecks, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
        <div class="mx-auto max-w-7xl space-y-6 p-6 sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <div class="text-muted-foreground flex items-center text-sm">
                <div v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                    <Link v-if="index < breadcrumbs.length - 1" :href="crumb.href" class="hover:text-foreground">
                        {{ crumb.title }}
                    </Link>
                    <span v-else class="text-foreground font-medium">{{ crumb.title }}</span>

                    <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
                </div>
            </div>
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

            <div v-if="quizzes.length === 0" class="py-10 text-center">
                <p class="text-muted-foreground">No quizzes found for this file.</p>
                <p class="text-muted-foreground mt-2">Create your first quiz to start testing your knowledge!</p>
            </div>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="quiz in quizzes" :key="quiz.id" class="overflow-hidden">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <CardTitle class="line-clamp-2">{{ quiz.question }}</CardTitle>
                            <Badge>{{ quizTypes[quiz.type] }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="quiz.type === 'multiple_choice' && quiz.options">
                            <p class="text-muted-foreground mb-2 text-sm">Options:</p>
                            <ul class="list-inside list-disc">
                                <li v-for="(option, index) in quiz.options" :key="index" class="text-sm">
                                    {{ option }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="quiz.type === 'enumeration'">
                            <p class="text-muted-foreground text-sm">{{ quiz.answers.length }} item(s) to enumerate</p>
                        </div>
                        <div v-else-if="quiz.type === 'true_false'">
                            <p class="text-muted-foreground text-sm">Answer: {{ quiz.answers[0] === 'true' ? 'True' : 'False' }}</p>
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
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    @click="
                                        () => {
                                            quizToDelete = quiz.id;
                                            showDeleteModal = true;
                                        }
                                    "
                                >
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
                                    <Button variant="destructive" @click="deleteQuiz"> Delete </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
