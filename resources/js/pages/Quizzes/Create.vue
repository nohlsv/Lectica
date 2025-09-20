<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type File } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Props {
    file: File;
    quizTypes: Record<string, string>;
}
const props = defineProps<Props>();

const form = useForm({
    question: '',
    type: 'multiple_choice',
    options: ['', ''],
    answers: [''],
});

const quizTypeOptions = ref(
    Object.entries(props.quizTypes).map(([value, label]) => ({
        value,
        label,
    })),
);

// Add a new option for multiple choice questions
function addOption() {
    form.options.push('');
}

// Remove an option for multiple choice questions
function removeOption(index: number) {
    if (form.options.length > 2) {
        form.options.splice(index, 1);
    }
}

// Add a new answer for enumeration questions
function addAnswer() {
    form.answers.push('');
}

// Remove an answer for enumeration questions
function removeAnswer(index: number) {
    if (form.answers.length > 1) {
        form.answers.splice(index, 1);
    }
}

// Watch for type changes and update form accordingly
watch(
    () => form.type,
    (newType) => {
        if (newType === 'multiple_choice') {
            form.options = form.options.length < 2 ? ['', ''] : form.options;
            form.answers = [''];
        } else if (newType === 'enumeration') {
            form.options = [];
            form.answers = form.answers.length < 1 ? [''] : form.answers;
        } else if (newType === 'true_false') {
            form.options = [];
            form.answers = ['true'];
        }
    },
);

function submit() {
    form.post(route('files.quizzes.store', props.file.id), {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Create Quiz" />

    <AppLayout>
        <div class="mx-full bg-gradient min-h-screen space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <Link :href="route('files.quizzes.index', file.id)">
                    <Button
                        class="pixel-outline inline-flex items-center gap-2 rounded-md border-2 border-[#f68500] bg-red-700 px-2 py-2 font-bold text-[#fce085] shadow-md duration-300 hover:bg-yellow-400 hover:text-red-700"
                        variant="default"
                        >Back to Quizzes</Button
                    >
                </Link>
            </div>
            <h2 class="text-md welcome-banner animate-soft-bounce pixel-outline px-2 py-2 text-center font-bold sm:px-4 sm:text-xl md:text-2xl">
                Create Quiz
            </h2>

            <Card class="bg-container flex w-full justify-center self-center rounded-md border-8 border-[#680d00] p-6">
                <CardHeader>
                    <CardTitle class="pixel-outline text-center text-2xl text-[#fce085]">New Quiz for "{{ file.name }}"</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label class="pixel-outline mb-2 block" for="question">Question</Label>
                            <Input class="border-yellow-300" id="question" v-model="form.question" type="text" required />
                            <InputError :message="form.errors.question" />
                        </div>

                        <div>
                            <Label class="pixel-outline mb-2 block" for="type">Quiz Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger class="pixel-outline border-yellow-300">
                                    <SelectValue placeholder="Select a quiz type" />
                                </SelectTrigger>
                                <SelectContent class="pixel-outline border-yellow-300">
                                    <SelectItem v-for="option in quizTypeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.type" />
                        </div>

                        <!-- Multiple Choice Options -->
                        <div v-if="form.type === 'multiple_choice'" class="space-y-4">
                            <Label class="pixel-outline">Options</Label>
                            <div v-for="(option, index) in form.options" :key="index" class="flex items-center space-x-2">
                                <Input
                                    class="pixel-outline border-yellow-300"
                                    v-model="form.options[index]"
                                    :placeholder="`Option ${index + 1}`"
                                    required
                                />
                                <Button
                                    type="button"
                                    variant="default"
                                    class="pixel-outline rounded-lg border-red-700 bg-red-500 text-[#fdf6ee] hover:bg-red-600"
                                    size="icon"
                                    @click="removeOption(index)"
                                    :disabled="form.options.length <= 2"
                                >
                                    <Trash2 class="pixel-outline-icon h-4 w-4" />
                                </Button>
                            </div>
                            <Button
                                type="button"
                                variant="default"
                                @click="addOption"
                                class="pixel-outline w-auto border-blue-700 bg-blue-500 text-[#fdf6ee] hover:bg-blue-600"
                            >
                                <Plus class="pixel-outline-icon mr-2 h-4 w-4" />
                                Add Option
                            </Button>
                            <InputError :message="form.errors.options" />

                            <div class="space-y-2">
                                <Label class="pixel-outline">Correct Answer</Label>
                                <RadioGroup v-model="form.answers" class="space-y-2">
                                    <div v-for="(option, index) in form.options" :key="index" class="pixel-outline flex items-center space-x-2">
                                        <RadioGroupItem class="border-yellow-300" :value="option" :id="`option-${index}`" />
                                        <Label :for="`option-${index}`">{{ option }}</Label>
                                    </div>
                                </RadioGroup>
                                <InputError :message="form.errors.answers" />
                            </div>
                        </div>

                        <!-- Enumeration Answers -->
                        <div v-if="form.type === 'enumeration'" class="space-y-4">
                            <Label class="pixel-outline">Correct Answers (Enter each answer separately)</Label>
                            <div v-for="(answer, index) in form.answers" :key="index" class="flex items-center space-x-2">
                                <Input
                                    class="pixel-outline border-yellow-300"
                                    v-model="form.answers[index]"
                                    :placeholder="`Answer ${index + 1}`"
                                    required
                                />
                                <Button
                                    type="button"
                                    variant="default"
                                    size="icon"
                                    class="pixel-outline rounded-lg border-red-700 bg-red-500 text-[#fdf6ee] hover:bg-red-600"
                                    @click="removeAnswer(index)"
                                    :disabled="form.answers.length <= 1"
                                >
                                    <Trash2 class="pixel-outline-icon h-4 w-4" />
                                </Button>
                            </div>
                            <Button
                                type="button"
                                variant="default"
                                @click="addAnswer"
                                class="pixel-outline w-auto border-blue-700 bg-blue-500 text-[#fdf6ee] hover:bg-blue-600"
                            >
                                <Plus class="pixel-outline-icon mr-2 h-4 w-4" />
                                Add Answer
                            </Button>
                            <InputError :message="form.errors.answers" />
                        </div>

                        <!-- True/False Answer -->
                        <div v-if="form.type === 'true_false'" class="space-y-2">
                            <Label class="pixel-outline">Correct Answer</Label>
                            <RadioGroup v-model="form.answers[0]">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem class="border-yellow-300" value="true" id="true" />
                                    <Label class="pixel-outline" for="true">True</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem class="border-yellow-300" value="false" id="false" />
                                    <Label class="pixel-outline" for="false">False</Label>
                                </div>
                            </RadioGroup>
                            <InputError :message="form.errors.answers" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button class="pixel-outline rounded-lg border-red-700 bg-red-500 text-[#fdf6ee] hover:bg-red-600" variant="default"
                            >Cancel</Button
                        >
                    </Link>
                    <Button
                        class="pixel-outline rounded-lg border-green-700 bg-green-500 text-[#fdf6ee] hover:bg-green-600"
                        type="submit"
                        @click="submit"
                        >Create Quiz</Button
                    >
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
