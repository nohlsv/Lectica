<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pencil, Trash2, Plus, ListChecks } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type File , type Quiz } from '@/types';

interface Props {
    file: File;
    quizzes: Quiz[];
    quizTypes: Record<string, string>;
}

const props = defineProps<Props>();

function deleteQuiz(quizId: number) {
    if (confirm('Are you sure you want to delete this quiz?')) {
        router.delete(route('files.quizzes.destroy', [props.file.id, quizId]));
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
            <div class="flex items-center text-sm text-muted-foreground">
                <div v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                    <Link v-if="index < breadcrumbs.length - 1" :href="crumb.href" class="hover:text-foreground">
                        {{ crumb.title }}
                    </Link>
                    <span v-else class="font-medium text-foreground">{{ crumb.title }}</span>

                    <span v-if="index < breadcrumbs.length - 1" class="mx-2">/</span>
                </div>
            </div>
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Quizzes for "{{ file.name }}"</h2>
                <div class="flex space-x-2">
                    <Link :href="route('files.show', file.id)">
                        <Button variant="outline">Back to File</Button>
                    </Link>
                    <Link v-if="isOwner" :href="route('files.quizzes.create', file.id)">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Quiz
                        </Button>
                    </Link>
                    <Link :href="route('files.quizzes.test', file.id)">
                        <Button variant="default">
                            <ListChecks class="mr-2 h-4 w-4" />
                            Take Quiz
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="quizzes.length === 0" class="text-center py-10">
                <p class="text-gray-500">No quizzes found for this file.</p>
                <p class="text-gray-500 mt-2">Create your first quiz to start testing your knowledge!</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-for="quiz in quizzes" :key="quiz.id" class="overflow-hidden">
                    <CardHeader>
                        <div class="flex justify-between items-start">
                            <CardTitle class="line-clamp-2">{{ quiz.question }}</CardTitle>
                            <Badge>{{ quizTypes[quiz.type] }}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="quiz.type === 'multiple_choice' && quiz.options">
                            <p class="text-sm text-gray-500 mb-2">Options:</p>
                            <ul class="list-disc list-inside">
                                <li v-for="(option, index) in quiz.options" :key="index" class="text-sm">
                                    {{ option }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="quiz.type === 'enumeration'">
                            <p class="text-sm text-gray-500">
                                {{ quiz.answers.length }} item(s) to enumerate
                            </p>
                        </div>
                        <div v-else-if="quiz.type === 'true_false'">
                            <p class="text-sm text-gray-500">
                                Answer: {{ quiz.answers[0] === 'true' ? 'True' : 'False' }}
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Link :href="route('files.quizzes.edit', [file.id, quiz.id])">
                            <Button variant="outline" size="sm">
                                <Pencil class="h-4 w-4" />
                                <span class="ml-2">Edit</span>
                            </Button>
                        </Link>
                        <Button variant="destructive" size="sm" @click="deleteQuiz(quiz.id)">
                            <Trash2 class="h-4 w-4" />
                            <span class="ml-2">Delete</span>
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
