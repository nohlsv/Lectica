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
        <div class="mx-full bg-gradient space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <Link :href="route('files.flashcards.index', file.id)">
                    <Button
                        variant="default"
                        class="pixel-outline inline-flex items-center gap-2 rounded-md border-2 border-[#f68500] bg-red-700 px-4 py-2 font-bold text-[#fce085] shadow-md duration-300 hover:bg-yellow-400 hover:text-red-700"
                        >Back to Flashcards</Button
                    >
                </Link>
            </div>
            <h2 class="text-md welcome-banner animate-soft-bounce pixel-outline px-2 py-2 text-center font-bold sm:px-4 sm:text-xl md:text-2xl">
                Edit Flashcard
            </h2>

            <Card class="bg-container flex w-full justify-center self-center rounded-md border-8 border-[#680d00] p-6">
                <CardHeader>
                    <CardTitle class="pixel-outline text-center text-2xl text-[#fce085]">Edit Flashcard for "{{ file.name }}"</CardTitle>
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
                        <Button class="pixel-outline rounded-lg border-red-700 bg-red-500 text-[#fdf6ee] hover:bg-red-600" variant="default"
                            >Cancel</Button
                        >
                    </Link>
                    <Button
                        class="pixel-outline rounded-lg border-green-700 bg-green-500 text-[#fdf6ee] hover:bg-green-600"
                        type="submit"
                        @click="submit"
                        >Update Flashcard</Button
                    >
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
