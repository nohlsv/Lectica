<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { CheckIcon, XIcon, ChevronLeft, ChevronRight, RotateCcw } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Progress } from '@/components/ui/progress';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Checkbox } from '@/components/ui/checkbox';

const props = defineProps({
    file: Object,
    quizzes: Array,
    quizTypes: Object,
});

const currentIndex = ref(0);
const userAnswers = ref({});
const showFeedback = ref(false);
const quizFinished = ref(false);
const shuffled = ref(false);
const quizQuestions = ref([...props.quizzes]);

// Initialize userAnswers with empty values for each quiz type
props.quizzes.forEach((quiz, index) => {
    if (quiz.type === 'multiple_choice') {
        userAnswers.value[index] = '';
    } else if (quiz.type === 'enumeration') {
        userAnswers.value[index] = Array(quiz.answers.length).fill('');
    } else if (quiz.type === 'true_false') {
        userAnswers.value[index] = '';
    }
});

const currentQuiz = computed(() => {
    if (quizQuestions.value.length === 0) return null;
    return quizQuestions.value[currentIndex.value];
});

const progress = computed(() => {
    if (quizQuestions.value.length === 0) return 0;
    return Math.round(((currentIndex.value + 1) / quizQuestions.value.length) * 100);
});

const isCurrentAnswerCorrect = computed(() => {
    if (!currentQuiz.value) return false;

    const index = currentIndex.value;
    const quiz = currentQuiz.value;
    const userAnswer = userAnswers.value[index];

    if (quiz.type === 'multiple_choice') {
        return userAnswer === quiz.answers[0];
    } else if (quiz.type === 'true_false') {
        return userAnswer === quiz.answers[0];
    } else if (quiz.type === 'enumeration') {
        // Check if all required answers are provided correctly (case insensitive)
        let correctCount = 0;
        const requiredAnswers = [...quiz.answers];

        userAnswer.forEach(answer => {
            if (answer && requiredAnswers.some(reqAns =>
                reqAns.toLowerCase() === answer.toLowerCase())) {
                correctCount++;
            }
        });

        // Check if all required answers are given
        return correctCount === requiredAnswers.length;
    }

    return false;
});

const score = computed(() => {
    if (quizQuestions.value.length === 0) return { correct: 0, total: 0, percentage: 0 };

    let correctCount = 0;
    quizQuestions.value.forEach((quiz, index) => {
        const userAnswer = userAnswers.value[index];

        if (quiz.type === 'multiple_choice' || quiz.type === 'true_false') {
            if (userAnswer === quiz.answers[0]) {
                correctCount++;
            }
        } else if (quiz.type === 'enumeration') {
            // For enumeration, check if all required answers are provided
            let allCorrect = true;
            const requiredAnswers = [...quiz.answers];

            // Check if user has provided all required answers
            const userAnswersLowerCase = userAnswer.map(ans => ans.toLowerCase());
            const requiredAnswersLowerCase = requiredAnswers.map(ans => ans.toLowerCase());

            // Check if user has all the required answers
            const missingAnswers = requiredAnswersLowerCase.filter(
                reqAns => !userAnswersLowerCase.includes(reqAns)
            );

            if (missingAnswers.length === 0) {
                correctCount++;
            }
        }
    });

    const percentage = Math.round((correctCount / quizQuestions.value.length) * 100);
    return { correct: correctCount, total: quizQuestions.value.length, percentage };
});

function checkAnswer() {
    showFeedback.value = true;
}

function next() {
    if (currentIndex.value < quizQuestions.value.length - 1) {
        currentIndex.value++;
        showFeedback.value = false;
    } else if (!quizFinished.value) {
        quizFinished.value = true;
    }
}

function previous() {
    if (currentIndex.value > 0) {
        currentIndex.value--;
        showFeedback.value = false;
    }
}

function reset() {
    currentIndex.value = 0;
    quizFinished.value = false;
    showFeedback.value = false;

    // Reset user answers
    props.quizzes.forEach((quiz, index) => {
        if (quiz.type === 'multiple_choice') {
            userAnswers.value[index] = '';
        } else if (quiz.type === 'enumeration') {
            userAnswers.value[index] = Array(quiz.answers.length).fill('');
        } else if (quiz.type === 'true_false') {
            userAnswers.value[index] = '';
        }
    });
}

function shuffleQuizzes() {
    // Fisher-Yates shuffle algorithm
    let array = [...props.quizzes];
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    quizQuestions.value = array;
    currentIndex.value = 0;
    showFeedback.value = false;
    quizFinished.value = false;
    shuffled.value = true;

    // Reset user answers based on new order
    quizQuestions.value.forEach((quiz, index) => {
        if (quiz.type === 'multiple_choice') {
            userAnswers.value[index] = '';
        } else if (quiz.type === 'enumeration') {
            userAnswers.value[index] = Array(quiz.answers.length).fill('');
        } else if (quiz.type === 'true_false') {
            userAnswers.value[index] = '';
        }
    });
}

function resetOrder() {
    quizQuestions.value = [...props.quizzes];
    currentIndex.value = 0;
    showFeedback.value = false;
    quizFinished.value = false;
    shuffled.value = false;

    // Reset user answers
    props.quizzes.forEach((quiz, index) => {
        if (quiz.type === 'multiple_choice') {
            userAnswers.value[index] = '';
        } else if (quiz.type === 'enumeration') {
            userAnswers.value[index] = Array(quiz.answers.length).fill('');
        } else if (quiz.type === 'true_false') {
            userAnswers.value[index] = '';
        }
    });
}

function updateEnumerationAnswer(index, value) {
    userAnswers.value[currentIndex.value][index] = value;
}

// Add or remove enumeration answer fields as needed
watch(() => currentQuiz.value, (newQuiz) => {
    if (newQuiz && newQuiz.type === 'enumeration') {
        const index = currentIndex.value;
        const answersCount = newQuiz.answers.length;

        // Initialize if not already
        if (!userAnswers.value[index] || !Array.isArray(userAnswers.value[index])) {
            userAnswers.value[index] = Array(answersCount).fill('');
        }

        // Adjust the array length if needed
        if (userAnswers.value[index].length < answersCount) {
            while (userAnswers.value[index].length < answersCount) {
                userAnswers.value[index].push('');
            }
        } else if (userAnswers.value[index].length > answersCount) {
            userAnswers.value[index] = userAnswers.value[index].slice(0, answersCount);
        }
    }
}, { immediate: true });
</script>

<template>
    <Head title="Take Quiz" />

    <AuthLayout>
        <div class="mx-auto max-w-4xl space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Quiz: {{ file.name }}</h2>
                <div class="flex space-x-2">
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button variant="outline">Back to Quizzes</Button>
                    </Link>
                    <Button @click="shuffleQuizzes" v-if="!shuffled && !quizFinished">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2"><path d="M2 18h1.4c1.3 0 2.5-.6 3.3-1.7l6.1-8.6c.7-1.1 2-1.7 3.3-1.7H22"></path><path d="m18 2 4 4-4 4"></path><path d="M2 6h1.9c1.5 0 2.9.8 3.7 2l.3.5"></path><path d="M22 18h-5.9c-1.3 0-2.6-.7-3.3-1.8l-.5-.8"></path><path d="m18 14 4 4-4 4"></path></svg>
                        Shuffle
                    </Button>
                    <Button @click="resetOrder" v-if="shuffled && !quizFinished">
                        <RotateCcw class="h-4 w-4 mr-2" />
                        Reset Order
                    </Button>
                </div>
            </div>

            <div v-if="quizQuestions.length === 0" class="text-center py-10">
                <p class="text-gray-500">No quizzes available for this file.</p>
                <p class="text-gray-500 mt-2">Create quizzes to start testing your knowledge.</p>
                <Link :href="route('files.quizzes.create', file.id)" class="mt-4 inline-block">
                    <Button>Create Quiz</Button>
                </Link>
            </div>

            <div v-else-if="quizFinished" class="space-y-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-center">Quiz Completed!</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="relative h-40 w-40">
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-3xl font-bold">{{ score.percentage }}%</span>
                                </div>
                                <svg class="h-40 w-40" viewBox="0 0 100 100">
                                    <circle
                                        class="text-muted-foreground stroke-current"
                                        stroke-width="10"
                                        fill="transparent"
                                        r="40"
                                        cx="50"
                                        cy="50"
                                    />
                                    <circle
                                        class="text-primary stroke-current"
                                        stroke-width="10"
                                        stroke-linecap="round"
                                        fill="transparent"
                                        r="40"
                                        cx="50"
                                        cy="50"
                                        :stroke-dasharray="`${score.percentage * 2.51} 251.2`"
                                        transform="rotate(-90 50 50)"
                                    />
                                </svg>
                            </div>
                            <p class="mt-4 text-xl">
                                You got <span class="font-bold">{{ score.correct }}</span> out of <span class="font-bold">{{ score.total }}</span> questions correct.
                            </p>
                            <div class="mt-6">
                                <Button @click="reset" class="w-full">Take the Quiz Again</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="space-y-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Question {{ currentIndex + 1 }} of {{ quizQuestions.length }}
                    </div>
                    <div class="w-1/2">
                        <Progress :value="progress" />
                    </div>
                </div>

                <Card class="min-h-[300px]">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <Badge>{{ quizTypes[currentQuiz.type] }}</Badge>
                        </div>
                        <CardTitle class="mt-2">{{ currentQuiz.question }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <!-- Multiple Choice Question -->
                        <div v-if="currentQuiz.type === 'multiple_choice'" class="space-y-4">
                            <RadioGroup v-model="userAnswers[currentIndex]">
                                <div
                                    v-for="(option, index) in currentQuiz.options"
                                    :key="index"
                                    class="flex items-center space-x-2 p-2 rounded-md"
                                    :class="{
                                        'bg-green-100 dark:bg-green-900/20': showFeedback && option === currentQuiz.answers[0],
                                        'bg-red-100 dark:bg-red-900/20': showFeedback && userAnswers[currentIndex] === option && option !== currentQuiz.answers[0],
                                    }"
                                >
                                    <RadioGroupItem :value="option" :id="`option-${index}`" :disabled="showFeedback" />
                                    <Label :for="`option-${index}`" class="flex-1">
                                        {{ option }}
                                    </Label>
                                    <CheckIcon v-if="showFeedback && option === currentQuiz.answers[0]" class="h-5 w-5 text-green-500" />
                                    <XIcon
                                        v-if="showFeedback && userAnswers[currentIndex] === option && option !== currentQuiz.answers[0]"
                                        class="h-5 w-5 text-red-500"
                                    />
                                </div>
                            </RadioGroup>
                        </div>

                        <!-- True/False Question -->
                        <div v-else-if="currentQuiz.type === 'true_false'" class="space-y-4">
                            <RadioGroup v-model="userAnswers[currentIndex]">
                                <div
                                    class="flex items-center space-x-2 p-2 rounded-md"
                                    :class="{
                                        'bg-green-100 dark:bg-green-900/20': showFeedback && 'true' === currentQuiz.answers[0],
                                        'bg-red-100 dark:bg-red-900/20': showFeedback && userAnswers[currentIndex] === 'true' && 'true' !== currentQuiz.answers[0],
                                    }"
                                >
                                    <RadioGroupItem value="true" id="answer-true" :disabled="showFeedback" />
                                    <Label for="answer-true" class="flex-1">True</Label>
                                    <CheckIcon v-if="showFeedback && 'true' === currentQuiz.answers[0]" class="h-5 w-5 text-green-500" />
                                    <XIcon
                                        v-if="showFeedback && userAnswers[currentIndex] === 'true' && 'true' !== currentQuiz.answers[0]"
                                        class="h-5 w-5 text-red-500"
                                    />
                                </div>
                                <div
                                    class="flex items-center space-x-2 p-2 rounded-md"
                                    :class="{
                                        'bg-green-100 dark:bg-green-900/20': showFeedback && 'false' === currentQuiz.answers[0],
                                        'bg-red-100 dark:bg-red-900/20': showFeedback && userAnswers[currentIndex] === 'false' && 'false' !== currentQuiz.answers[0],
                                    }"
                                >
                                    <RadioGroupItem value="false" id="answer-false" :disabled="showFeedback" />
                                    <Label for="answer-false" class="flex-1">False</Label>
                                    <CheckIcon v-if="showFeedback && 'false' === currentQuiz.answers[0]" class="h-5 w-5 text-green-500" />
                                    <XIcon
                                        v-if="showFeedback && userAnswers[currentIndex] === 'false' && 'false' !== currentQuiz.answers[0]"
                                        class="h-5 w-5 text-red-500"
                                    />
                                </div>
                            </RadioGroup>
                        </div>

                        <!-- Enumeration Question -->
                        <div v-else-if="currentQuiz.type === 'enumeration'" class="space-y-4">
                            <p class="text-sm text-gray-500 mb-2">Enter all {{ currentQuiz.answers.length }} correct answers:</p>
                            <div
                                v-for="(answer, index) in userAnswers[currentIndex]"
                                :key="index"
                                class="space-y-2"
                            >
                                <div
                                    class="flex items-center space-x-2 p-2 rounded-md"
                                    :class="{
                                        'bg-green-100 dark:bg-green-900/20': showFeedback && currentQuiz.answers.some(a => a.toLowerCase() === answer.toLowerCase()),
                                        'bg-red-100 dark:bg-red-900/20': showFeedback && answer && !currentQuiz.answers.some(a => a.toLowerCase() === answer.toLowerCase()),
                                    }"
                                >
                                    <Input
                                        v-model="userAnswers[currentIndex][index]"
                                        :placeholder="`Answer ${index + 1}`"
                                        :disabled="showFeedback"
                                    />
                                    <CheckIcon
                                        v-if="showFeedback && answer && currentQuiz.answers.some(a => a.toLowerCase() === answer.toLowerCase())"
                                        class="h-5 w-5 text-green-500 ml-2"
                                    />
                                    <XIcon
                                        v-if="showFeedback && answer && !currentQuiz.answers.some(a => a.toLowerCase() === answer.toLowerCase())"
                                        class="h-5 w-5 text-red-500 ml-2"
                                    />
                                </div>
                            </div>

                            <div v-if="showFeedback">
                                <div v-if="!isCurrentAnswerCorrect" class="mt-4 p-3 bg-muted rounded-md">
                                    <p class="font-medium">Correct answers:</p>
                                    <ul class="list-disc list-inside mt-1">
                                        <li v-for="(answer, i) in currentQuiz.answers" :key="i">
                                            {{ answer }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback area -->
                        <div v-if="showFeedback" class="mt-6 p-4 rounded-md" :class="isCurrentAnswerCorrect ? 'bg-green-50 dark:bg-green-950/30' : 'bg-red-50 dark:bg-red-950/30'">
                            <div class="flex items-center">
                                <div v-if="isCurrentAnswerCorrect" class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                        <CheckIcon class="h-6 w-6 text-green-600 dark:text-green-300" />
                                    </div>
                                </div>
                                <div v-else class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                                        <XIcon class="h-6 w-6 text-red-600 dark:text-red-300" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ isCurrentAnswerCorrect ? 'Correct!' : 'Incorrect' }}
                                    </h3>
                                    <p v-if="!isCurrentAnswerCorrect && currentQuiz.type === 'multiple_choice'" class="mt-1 text-gray-700 dark:text-gray-300">
                                        The correct answer is: {{ currentQuiz.answers[0] }}
                                    </p>
                                    <p v-else-if="!isCurrentAnswerCorrect && currentQuiz.type === 'true_false'" class="mt-1 text-gray-700 dark:text-gray-300">
                                        The correct answer is: {{ currentQuiz.answers[0] === 'true' ? 'True' : 'False' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-between">
                        <Button
                            @click="previous"
                            :disabled="currentIndex === 0"
                            variant="outline"
                        >
                            <ChevronLeft class="h-4 w-4 mr-2" />
                            Previous
                        </Button>

                        <Button
                            v-if="!showFeedback"
                            @click="checkAnswer"
                            variant="default"
                            :disabled="
                                (currentQuiz.type === 'multiple_choice' && !userAnswers[currentIndex]) ||
                                (currentQuiz.type === 'true_false' && !userAnswers[currentIndex]) ||
                                (currentQuiz.type === 'enumeration' && userAnswers[currentIndex].every(a => !a))
                            "
                        >
                            Check Answer
                        </Button>

                        <Button
                            v-else
                            @click="next"
                            variant="default"
                        >
                            {{ currentIndex === quizQuestions.length - 1 ? 'Finish Quiz' : 'Next Question' }}
                            <ChevronRight v-if="currentIndex !== quizQuestions.length - 1" class="h-4 w-4 ml-2" />
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>

        <!-- Alert dialog for finished quiz -->
        <AlertDialog :open="quizFinished">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Quiz Completed!</AlertDialogTitle>
                    <AlertDialogDescription>
                        You scored {{ score.correct }} out of {{ score.total }} questions correctly ({{ score.percentage }}%).
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogAction @click="reset">Take Again</AlertDialogAction>
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button variant="outline">Back to Quizzes</Button>
                    </Link>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AuthLayout>
</template>
