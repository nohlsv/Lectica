<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { type File } from '@/types';

const props = defineProps<{
    file: File;
}>();

const form = useForm({
    question: '',
    answer: '',
});

function submit() {
    form.post(route('files.flashcards.store', props.file.id), {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Create Flashcard" />

    <AppLayout>
        <div class="mx-auto max-w-3xl space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Create Flashcard</h2>
                <Link :href="route('files.flashcards.index', file.id)">
                    <Button variant="outline">Back to Flashcards</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>New Flashcard for "{{ file.name }}"</CardTitle>
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
                    <Button type="submit" @click="submit">Create Flashcard</Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
