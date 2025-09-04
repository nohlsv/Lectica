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
        <div class="mx-full space-y-6 p-6 sm:px-6 lg:px-8 bg-gradient">
            <div class="flex justify-between">
                <h2 class="text-md sm:text-xl md:text-2xl font-bold welcome-banner py-2 px-2 sm:px-4 animate-soft-bounce pixel-outline">Edit Flashcard</h2>
                <Link :href="route('files.flashcards.index', file.id)">
                    <Button variant="default" class="inline-flex items-center gap-2 px-4 py-2 text-[#fce085] bg-red-700 border-2 border-[#f68500] rounded-md shadow-md hover:bg-yellow-400
                            hover:text-red-700 duration-300 font-bold pixel-outline mb-3">Back to Flashcards</Button>
                </Link>
            </div>

            <Card class="flex justify-center p-6 self-center w-full bg-container border-[#680d00] border-8 rounded-md">
                <CardHeader>
                    <CardTitle class="text-2xl text-[#fce085] pixel-outline text-center">Edit Flashcard for "{{ file.name }}"</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label class="pixel-outline mb-2 block" for="question">Question</Label>
                            <Input class="border-yellow-300 bg-transparent" id="question" v-model="form.question" type="text" required />
                            <InputError :message="form.errors.question" />
                        </div>

                        <div>
                            <Label class="pixel-outline mb-2 block" for="answer">Answer</Label>
                            <Textarea class="pixel-outline border-yellow-300" id="answer" v-model="form.answer" rows="5" required />
                            <InputError :message="form.errors.answer" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Link :href="route('files.flashcards.index', file.id)">
                        <Button class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline" variant="default">Cancel</Button>
                    </Link>
                    <Button class="bg-green-500 text-[#fdf6ee] hover:bg-green-600 border-green-700 rounded-lg pixel-outline" type="submit" @click="submit">Update Flashcard</Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
