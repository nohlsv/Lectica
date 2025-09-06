<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import InputError from '@/components/InputError.vue';
import { ref, watch } from 'vue';
import { Plus, Trash2 } from 'lucide-vue-next';
import { type File , type Quiz } from '@/types';

interface Props {
    file: File;
    quiz: Quiz;
    quizTypes: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
    question: props.quiz.question,
    type: props.quiz.type,
    options: props.quiz.options || [],
    answers: props.quiz.answers || [],
});

const quizTypeOptions = ref(Object.entries(props.quizTypes).map(([value, label]) => ({
    value,
    label,
})));

// Ensure options is an array with at least 2 items for multiple choice
if (props.quiz.type === 'multiple_choice' && (!form.options || form.options.length < 2)) {
    form.options = form.options ? [...form.options] : [];
    while (form.options.length < 2) {
        form.options.push('');
    }
}

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
watch(() => form.type, (newType) => {
    if (newType === 'multiple_choice') {
        form.options = form.options.length < 2 ? ['', ''] : form.options;
        form.answers = form.answers.length ? [form.answers[0]] : [''];
    } else if (newType === 'enumeration') {
        form.options = [];
        form.answers = form.answers.length < 1 ? [''] : form.answers;
    } else if (newType === 'true_false') {
        form.options = [];
        form.answers = form.answers.length ? [form.answers[0]] : ['true'];
        if (!['true', 'false'].includes(form.answers[0])) {
            form.answers = ['true'];
        }
    }
});

function submit() {
    form.put(route('files.quizzes.update', [props.file.id, props.quiz.id]));
}
</script>

<template>
    <Head title="Edit Quiz" />

    <AppLayout>
        <div class="mx-full space-y-6 p-6 sm:px-6 lg:px-8 bg-gradient">
            <div class="flex justify-between">
                <Link :href="route('files.quizzes.index', file.id)">
                    <Button class="inline-flex items-center gap-2 px-4 py-2 text-[#fce085] bg-red-700 border-2 border-[#f68500] rounded-md shadow-md hover:bg-yellow-400
                            hover:text-red-700 duration-300 font-bold pixel-outline" variant="default">Back to Quizzes</Button>
                </Link>
            </div>
            <h2 class="text-md text-center sm:text-xl md:text-2xl font-bold welcome-banner py-2 px-2 sm:px-4 animate-soft-bounce pixel-outline">Edit Quiz</h2>

            <Card class="flex justify-center p-6 self-center w-full bg-container border-[#680d00] border-8 rounded-md">
                <CardHeader>
                    <CardTitle class="text-2xl text-[#fce085] pixel-outline text-center">Edit Quiz for "{{ file.name }}"</CardTitle>
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
                                <SelectTrigger class="border-yellow-300">
                                    <SelectValue class="pixel-outline" placeholder="Select a quiz type" />
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
                            <Label>Options</Label>
                            <div v-for="(option, index) in form.options" :key="index" class="flex items-center space-x-2">
                                <Input class="pixel-outline border-yellow-300" v-model="form.options[index]" :placeholder="`Option ${index + 1}`" required />
                                <Button
                                    type="button"
                                    variant="default"
                                    class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline"
                                    size="icon"
                                    @click="removeOption(index)"
                                    :disabled="form.options.length <= 2"
                                >
                                    <Trash2 class="h-4 w-4 pixel-outline-icon" />
                                </Button>
                            </div>
                            <Button type="button" variant="default" @click="addOption" class="w-auto bg-blue-500 text-[#fdf6ee] hover:bg-blue-600 border-blue-700 pixel-outline">
                                <Plus class="h-4 w-4 mr-2 pixel-outline-icon" />
                                Add Option
                            </Button>
                            <InputError :message="form.errors.options" />

                            <div class="space-y-2">
                                <Label class="pixel-outline">Correct Answer</Label>
                                <RadioGroup v-model="form.answers[0]">
                                    <div
                                        v-for="(option, index) in form.options"
                                        :key="index"
                                        class="flex items-center space-x-2 pixel-outline"
                                    >
                                        <RadioGroupItem class="border-yellow-300":value="option" :id="`option-${index}`" />
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
                                <Input class="pixel-outline border-yellow-300" v-model="form.answers[index]" :placeholder="`Answer ${index + 1}`" required />
                                <Button
                                    type="button"
                                    variant="default"
                                    class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline"
                                    size="icon"
                                    @click="removeAnswer(index)"
                                    :disabled="form.answers.length <= 1"
                                >
                                    <Trash2 class="h-4 w-4 pixel-outline-icon" />
                                </Button>
                            </div>
                            <Button type="button" variant="default" @click="addAnswer" class="w-auto bg-blue-500 text-[#fdf6ee] hover:bg-blue-600 border-blue-700 pixel-outline">
                                <Plus class="h-4 w-4 mr-2 pixel-outline-icon" />
                                Add Answer
                            </Button>
                            <InputError :message="form.errors.answers" />
                        </div>

                        <!-- True/False Answer -->
                        <div v-if="form.type === 'true_false'" class="space-y-2">
                            <Label class="pixel-outline">Correct Answer</Label>
                            <RadioGroup v-model="form.answers[0]">
                                <div class="flex items-center space-x-2 pixel-outline">
                                    <RadioGroupItem class="border-yellow-300" value="true" id="true" />
                                    <Label for="true">True</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem class="border-yellow-300" value="false" id="false" />
                                    <Label for="false">False</Label>
                                </div>
                            </RadioGroup>
                            <InputError :message="form.errors.answers" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button class="bg-red-500 text-[#fdf6ee] hover:bg-red-600 border-red-700 rounded-lg pixel-outline" variant="default">Cancel</Button>
                    </Link>
                    <Button class="bg-green-500 text-[#fdf6ee] hover:bg-green-600 border-green-700 rounded-lg pixel-outline" type="submit" @click="submit">Update Quiz</Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
