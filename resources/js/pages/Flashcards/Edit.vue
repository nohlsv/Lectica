<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type File, type Flashcard } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
interface Props {
    file: File;
    flashcard: Flashcard;
}
const props = defineProps<Props>();

const form = useForm({
    question: props.flashcard.question,
    answer: props.flashcard.answer,
});

function submit() {
    form.put(route('files.flashcards.update', [props.file.id, props.flashcard.id]));
}
</script>

<template>
    <Head title="Edit Flashcard" />

    <AppLayout>
        <div class="mx-auto max-w-3xl space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Edit Flashcard</h2>
                <Link :href="route('files.flashcards.index', file.id)">
                    <Button variant="outline">Back to Flashcards</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Edit Flashcard for "{{ file.name }}"</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="question">Question</Label>
                            <Input id="question" v-model="form.question" type="text" required />
                            <InputError :message="form.errors.question" />
                        </div>

                        <div>
                            <Label for="answer">Answer</Label>
                            <Textarea id="answer" v-model="form.answer" rows="5" required />
                            <InputError :message="form.errors.answer" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Link :href="route('files.flashcards.index', file.id)">
                        <Button variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" @click="submit">Update Flashcard</Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
