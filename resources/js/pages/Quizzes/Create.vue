<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
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

const props = defineProps({
    file: Object,
    quizTypes: Object,
});

const form = useForm({
    question: '',
    type: 'multiple_choice',
    options: ['', ''],
    answers: [''],
});

const quizTypeOptions = ref(Object.entries(props.quizTypes).map(([value, label]) => ({
    value,
    label,
})));

// Add a new option for multiple choice questions
function addOption() {
    form.options.push('');
}

// Remove an option for multiple choice questions
function removeOption(index) {
    if (form.options.length > 2) {
        form.options.splice(index, 1);
    }
}

// Add a new answer for enumeration questions
function addAnswer() {
    form.answers.push('');
}

// Remove an answer for enumeration questions
function removeAnswer(index) {
    if (form.answers.length > 1) {
        form.answers.splice(index, 1);
    }
}

// Watch for type changes and update form accordingly
watch(() => form.type, (newType) => {
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
});

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

    <AuthLayout>
        <div class="mx-auto max-w-3xl space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Create Quiz</h2>
                <Link :href="route('files.quizzes.index', file.id)">
                    <Button variant="outline">Back to Quizzes</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>New Quiz for "{{ file.name }}"</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <Label for="question">Question</Label>
                            <Input id="question" v-model="form.question" type="text" required />
                            <InputError :message="form.errors.question" />
                        </div>

                        <div>
                            <Label for="type">Quiz Type</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a quiz type" />
                                </SelectTrigger>
                                <SelectContent>
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
                                <Input v-model="form.options[index]" :placeholder="`Option ${index + 1}`" required />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    @click="removeOption(index)"
                                    :disabled="form.options.length <= 2"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                            <Button type="button" variant="outline" @click="addOption" class="w-full">
                                <Plus class="h-4 w-4 mr-2" />
                                Add Option
                            </Button>
                            <InputError :message="form.errors.options" />

                            <div class="space-y-2">
                                <Label>Correct Answer</Label>
                                <RadioGroup v-model="form.answers[0]">
                                    <div
                                        v-for="(option, index) in form.options"
                                        :key="index"
                                        class="flex items-center space-x-2"
                                    >
                                        <RadioGroupItem :value="option" :id="`option-${index}`" />
                                        <Label :for="`option-${index}`">{{ option }}</Label>
                                    </div>
                                </RadioGroup>
                                <InputError :message="form.errors.answers" />
                            </div>
                        </div>

                        <!-- Enumeration Answers -->
                        <div v-if="form.type === 'enumeration'" class="space-y-4">
                            <Label>Correct Answers (Enter each answer separately)</Label>
                            <div v-for="(answer, index) in form.answers" :key="index" class="flex items-center space-x-2">
                                <Input v-model="form.answers[index]" :placeholder="`Answer ${index + 1}`" required />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="icon"
                                    @click="removeAnswer(index)"
                                    :disabled="form.answers.length <= 1"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                            <Button type="button" variant="outline" @click="addAnswer" class="w-full">
                                <Plus class="h-4 w-4 mr-2" />
                                Add Answer
                            </Button>
                            <InputError :message="form.errors.answers" />
                        </div>

                        <!-- True/False Answer -->
                        <div v-if="form.type === 'true_false'" class="space-y-2">
                            <Label>Correct Answer</Label>
                            <RadioGroup v-model="form.answers[0]">
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="true" id="true" />
                                    <Label for="true">True</Label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <RadioGroupItem value="false" id="false" />
                                    <Label for="false">False</Label>
                                </div>
                            </RadioGroup>
                            <InputError :message="form.errors.answers" />
                        </div>
                    </form>
                </CardContent>
                <CardFooter class="flex justify-end space-x-2">
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" @click="submit">Create Quiz</Button>
                </CardFooter>
            </Card>
        </div>
    </AuthLayout>
</template>
