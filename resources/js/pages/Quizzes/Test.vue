<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import AppLayout from '@/layouts/AppLayout.vue';
import { type File, type Quiz, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckIcon, ChevronLeft, ChevronRight, RotateCcw, XIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    file: File;
    quizzes: Quiz[];
    quizTypes: Record<string, string>;
}

const props = defineProps<Props>();

const currentIndex = ref(0);
const userAnswers = ref<Record<number, any>>({});
const showFeedback = ref(false);
const quizFinished = ref(false);
const shuffled = ref(false);
interface QuizQuestion extends Quiz {}

const quizQuestions = ref<QuizQuestion[]>([...props.quizzes]);

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

        userAnswer.forEach((answer: string) => {
            if (answer && requiredAnswers.some((reqAns) => reqAns.toLowerCase() === answer.toLowerCase())) {
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
            // const allCorrect = true;
            const requiredAnswers = [...quiz.answers];

            // Check if user has provided all required answers
            const userAnswersLowerCase = (userAnswer as string[]).map((ans: string) => ans.toLowerCase());
            const requiredAnswersLowerCase = requiredAnswers.map((ans) => ans.toLowerCase());

            // Check if user has all the required answers
            const missingAnswers = requiredAnswersLowerCase.filter((reqAns) => !userAnswersLowerCase.includes(reqAns));

            if (missingAnswers.length === 0) {
                correctCount++;
            }
        }
    });

    const percentage = Math.round((correctCount / quizQuestions.value.length) * 100);
    return { correct: correctCount, total: quizQuestions.value.length, percentage };
});

// Get user data and college mascot info
const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

// Get college mascot info
const getCollegeMascot = (college: string | undefined) => {
    const collegeMap: Record<string, { abbr: string; mascot: string; image: string }> = {
        'College of Computer Studies': {
            abbr: 'CCST',
            mascot: 'Merlin the Wizard', 
            image: '/images/mascots/CCST WIZARDS.png'
        },
        'College of Engineering and Architecture': {
            abbr: 'CEA',
            mascot: 'Boingo the Falcon',
            image: '/images/mascots/CEA FALCONS.png'
        },
        'College of Business and Accountancy': {
            abbr: 'CBA',
            mascot: 'Toastimus the Phoenix',
            image: '/images/mascots/CBA PHOENIX.png'
        },
        'College of Technology': {
            abbr: 'CTEC',
            mascot: 'Scroggle the Dragon',
            image: '/images/mascots/CTEC DRAGONS.png'
        },
        'College of Allied Health and Sciences': {
            abbr: 'CAHS',
            mascot: 'Howlton the Wolf',
            image: '/images/mascots/CAHS WOLVES.png'
        },
        'College of Arts and Science': {
            abbr: 'COAS',
            mascot: 'Boofus the Knight',
            image: '/images/mascots/COAS KNIGHTS.png'
        }
    };
    return collegeMap[college || ''] || collegeMap['College of Computer Studies'];
};

const collegeMascot = computed(() => {
    const college = user.value.college || user.value.program?.college;
    return getCollegeMascot(college);
});

function checkAnswer() {
    showFeedback.value = true;
}

function next() {
    if (currentIndex.value < quizQuestions.value.length - 1) {
        currentIndex.value++;
        showFeedback.value = false;
    } else if (!quizFinished.value) {
        finishQuiz();
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
    const array = [...props.quizzes];
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

    toast.success('Quizzes shuffled successfully!');
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

    toast.success('Quiz order reset successfully!');
}

function updateEnumerationAnswer(index: number, value: string) {
    userAnswers.value[currentIndex.value][index] = value;
}

function isAnswerCorrect(quiz: Quiz, userAnswer: any) {
    if (quiz.type === 'multiple_choice' || quiz.type === 'true_false') {
        return userAnswer === quiz.answers[0];
    } else if (quiz.type === 'enumeration') {
        const requiredAnswers = [...quiz.answers];
        const userAnswersLowerCase = (userAnswer as string[]).map((ans: string) => ans.toLowerCase());
        const requiredAnswersLowerCase = requiredAnswers.map((ans) => ans.toLowerCase());
        const missingAnswers = requiredAnswersLowerCase.filter((reqAns) => !userAnswersLowerCase.includes(reqAns));
        return missingAnswers.length === 0;
    }
    return false;
}

function finishQuiz() {
    quizFinished.value = true;

    // Record practice data
    const mistakes = quizQuestions.value
        .map((quiz, index) => {
            const userAnswer = userAnswers.value[index];
            if (!isAnswerCorrect(quiz, userAnswer)) {
                return {
                    question: quiz.question,
                    your_answer: userAnswer,
                    correct_answer: quiz.answers[0],
                };
            }
            return null;
        })
        .filter(Boolean);

    axios
        .post(route('practice-records.store'), {
            file_id: props.file.id,
            type: 'quiz',
            correct_answers: score.value.correct,
            total_questions: score.value.total,
            mistakes,
        })
        .then(() => {
            toast.success('Quiz results saved successfully!');
        })
        .catch(() => {
            toast.error('Failed to save quiz results.');
        });
}

// Add or remove enumeration answer fields as needed
watch(
    () => currentQuiz.value,
    (newQuiz) => {
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
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Take Quiz" />
    <AppLayout>
        <div class="bg-gradient flex h-full min-h-screen flex-1 flex-col gap-4 px-4 pt-4 pb-0 lg:p-8">
            <h2 class="pixel-outline welcome-banner animate-soft-bounce mt-3 text-center text-lg font-bold break-words sm:text-xl md:text-2xl">
                Practice Quiz: {{ file.name }}
            </h2>
            <div class="flex justify-between">
                <div class="flex space-x-2">
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button
                            class="pixel-outline border-4 border-red-700 bg-red-500 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:bg-red-600 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                        >
                            Escape
                        </Button>
                    </Link>
                    <Button
                        class="pixel-outline border-4 border-yellow-600 bg-yellow-500 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:bg-yellow-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                        @click="shuffleQuizzes"
                        v-if="!shuffled && !quizFinished"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="mr-2 h-4 w-4"
                        >
                            <path d="M2 18h1.4c1.3 0 2.5-.6 3.3-1.7l6.1-8.6c.7-1.1 2-1.7 3.3-1.7H22"></path>
                            <path d="m18 2 4 4-4 4"></path>
                            <path d="M2 6h1.9c1.5 0 2.9.8 3.7 2l.3.5"></path>
                            <path d="M22 18h-5.9c-1.3 0-2.6-.7-3.3-1.8l-.5-.8"></path>
                            <path d="m18 14 4 4-4 4"></path>
                        </svg>
                        Shuffle
                    </Button>
                    <Button
                        class="pixel-outline border-4 border-yellow-600 bg-yellow-500 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:bg-yellow-700 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                        @click="resetOrder"
                        v-if="shuffled && !quizFinished"
                    >
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Reset Order
                    </Button>
                </div>
            </div>
            <div
                class="relative flex min-h-screen w-full flex-col overflow-hidden bg-cover bg-center"
                style="background-image: url('/images/game-background.png')"
            >
                <!-- Question Box -->
                <div
                    class="font-pixel absolute top-10 right-4 left-4 flex w-auto items-center gap-3 border-[6px] border-black bg-gradient px-3 py-3 shadow-[6px_6px_0px_rgba(0,0,0,1),-3px_-3px_0px_rgba(0,0,0,1)] sm:top-10 sm:left-1/6 sm:-translate-x-1/12 sm:gap-6 sm:px-6 sm:py-4 md:left-1/3 md:-translate-x-1/4 2xl:left-1/2 2xl:-translate-x-1/2"
                    style="image-rendering: pixelated"
                >
                    <div
                        class="text-md font-pixel absolute -top-5 left-0 border-2 border-white bg-black px-3 py-1 text-white shadow-[2px_2px_0px_rgba(0,0,0,0.8)] sm:text-base"
                    >
                        {{ collegeMascot.mascot }}
                    </div>
                    <div class="flex-1 text-center text-sm text-white sm:text-left sm:text-xl md:text-lg lg:text-xl xl:text-xl 2xl:text-2xl">
                        "{{ currentQuiz?.question }}"
                    </div>
                    <div class="flex-shrink-0">
                        <img
                            :src="collegeMascot.image"
                            class="animate-floating w-20 sm:w-28 md:w-32"
                            style="image-rendering: pixelated"
                        />
                    </div>
                </div>
                <div class="bg-container mb-2 flex min-h-[250px] w-full items-center justify-center rounded-xl bg-cover bg-center p-6"></div>
                <div v-if="quizQuestions.length === 0" class="py-10 text-center">
                    <p class="text-muted-foreground">No quizzes available for this file.</p>
                    <p class="text-muted-foreground mt-2">Create quizzes to start testing your knowledge.</p>
                    <Link :href="route('files.quizzes.create', file.id)" class="mt-4 inline-block">
                        <Button>Create Quiz</Button>
                    </Link>
                </div>
                <div v-else-if="quizFinished" class="space-y-4">
                    <Card class="border-rounded-lg border-4 border-[#1d0101] bg-[#300303]">
                        <CardHeader>
                            <CardTitle class="pixel-outline text-center">Quiz Completed!</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="relative h-40 w-40">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="pixel-outline text-3xl font-bold text-green-500">{{ score.percentage }}%</span>
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
                                            class="text-primary progress-glow stroke-current"
                                            stroke-width="10"
                                            stroke-linecap="butt"
                                            fill="transparent"
                                            r="40"
                                            cx="50"
                                            cy="50"
                                            :stroke-dasharray="`${score.percentage * 2.51} 251.2`"
                                            transform="rotate(-90 50 50)"
                                        />
                                    </svg>
                                </div>
                                <p class="pixel-outline mt-4 text-xl">
                                    You got <span class="pixel-outline font-bold">{{ score.correct }}</span> out of
                                    <span class="pixel-outline font-bold">{{ score.total }}</span> questions correct.
                                </p>
                                <div class="mt-6">
                                    <Button
                                        @click="reset"
                                        class="pixel-outline border-4 border-green-700 bg-green-500 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:bg-green-600 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                        >Practice the Quiz Again</Button
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <div v-else class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="text-muted-foreground text-sm">Question {{ currentIndex + 1 }} of {{ quizQuestions.length }}</div>
                        <div class="w-1/2">
                            <Progress :value="progress" />
                        </div>
                    </div>
                    <Card class="border-rounded-lg min-h-[300px] border-4 border-[#1d0101] bg-[#300303]" v-if="currentQuiz">
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <Badge>{{ quizTypes[currentQuiz.type] }}</Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <!-- Multiple Choice Question -->
                            <div v-if="currentQuiz.type === 'multiple_choice'" class="w-full">
                                <RadioGroup v-model="userAnswers[currentIndex]" class="grid w-full grid-cols-1 items-stretch gap-4 sm:grid-cols-2">
                                    <div v-for="(option, index) in currentQuiz.options" :key="index" class="h-full w-full">
                                        <Label
                                            :for="`option-${index}`"
                                            class="pixel-outline flex h-full w-full transform cursor-pointer items-center justify-center rounded-lg border-4 border-green-700 bg-green-500 px-6 py-3 text-center text-lg font-bold text-balance text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out select-none hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                            :class="{
                                                'border-green-800 bg-green-600': !showFeedback && userAnswers[currentIndex] === option,
                                                'border-green-800 bg-green-700': showFeedback && option === currentQuiz.answers[0],
                                                'border-red-700 bg-red-500':
                                                    showFeedback && userAnswers[currentIndex] === option && option !== currentQuiz.answers[0],
                                            }"
                                            ><RadioGroupItem :value="option" :id="`option-${index}`" :disabled="showFeedback" class="hidden" />{{
                                                option
                                            }}
                                        </Label>
                                    </div>
                                </RadioGroup>
                            </div>
                            <!-- True/False Question - Arcade Style -->
                            <div v-else-if="currentQuiz.type === 'true_false'" class="w-full">
                                <RadioGroup v-model="userAnswers[currentIndex]" class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
                                    <!-- TRUE Button -->
                                    <Label
                                        for="answer-true"
                                        class="pixel-outline flex h-full w-full transform cursor-pointer items-center justify-center rounded-lg border-4 border-green-700 bg-green-500 px-6 py-3 text-center text-lg font-bold text-balance text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out select-none hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                        :class="{
                                            'border-green-800 bg-green-600': !showFeedback && userAnswers[currentIndex] === 'true',
                                            'border-green-800 bg-green-700': showFeedback && 'true' === currentQuiz.answers[0],
                                            'border-red-700 bg-red-500':
                                                showFeedback && userAnswers[currentIndex] === 'true' && 'true' !== currentQuiz.answers[0],
                                        }"
                                        ><RadioGroupItem value="true" id="answer-true" :disabled="showFeedback" class="hidden" />TRUE
                                    </Label>
                                    <!-- FALSE Button -->
                                    <Label
                                        for="answer-false"
                                        class="pixel-outline flex h-full w-full transform cursor-pointer items-center justify-center rounded-lg border-4 border-red-700 bg-red-500 px-6 py-3 text-center text-lg font-bold text-balance text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out select-none hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                        :class="{
                                            'border-red-800 bg-red-600': !showFeedback && userAnswers[currentIndex] === 'false',
                                            'border-green-800 bg-green-700': showFeedback && 'false' === currentQuiz.answers[0],
                                            'border-red-700 bg-red-500':
                                                showFeedback && userAnswers[currentIndex] === 'false' && 'false' !== currentQuiz.answers[0],
                                        }"
                                        ><RadioGroupItem value="false" id="answer-false" :disabled="showFeedback" class="hidden" />FALSE
                                    </Label>
                                </RadioGroup>
                            </div>

                            <!-- Enumeration Question -->
                            <div v-else-if="currentQuiz.type === 'enumeration'" class="w-full space-y-4">
                                <p
                                    class="pixel-outline rounded-lg border-4 border-green-700 bg-green-500 px-4 py-2 text-sm font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                                >
                                    INSERT {{ currentQuiz.answers.length }} COINS (answers) TO CONTINUE
                                </p>
                                <div v-for="(answer, index) in userAnswers[currentIndex]" :key="index" class="w-full">
                                    <div
                                        class="flex w-full transform items-center rounded-lg border-4 bg-gradient-to-b from-gray-800 to-gray-900 px-4 py-2 text-lg font-bold text-yellow-300 shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                        :class="{
                                            'border-gray-700': !showFeedback,
                                            'border-green-800 bg-green-700 text-white':
                                                showFeedback && currentQuiz.answers.some((a) => a.toLowerCase() === answer.toLowerCase()),
                                            'border-red-800 bg-red-700 text-white':
                                                showFeedback && answer && !currentQuiz.answers.some((a) => a.toLowerCase() === answer.toLowerCase()),
                                        }"
                                    >
                                        <div class="absolute -mt-5 h-2 w-10 rounded-sm bg-yellow-400 shadow-[inset_0_-1px_2px_rgba(0,0,0,0.5)]"></div>
                                        <Input
                                            v-model="userAnswers[currentIndex][index]"
                                            :placeholder="`Coin Slot ${index + 1}`"
                                            :disabled="showFeedback"
                                            class="placeholder-opacity-60 pixel-outline flex-1 border-none bg-transparent text-center placeholder-yellow-400 outline-none focus:animate-bounce"
                                        />
                                        <span
                                            v-if="showFeedback && answer && currentQuiz.answers.some((a) => a.toLowerCase() === answer.toLowerCase())"
                                            class="ml-3 text-white"
                                            >💰</span
                                        >
                                        <span
                                            v-if="
                                                showFeedback && answer && !currentQuiz.answers.some((a) => a.toLowerCase() === answer.toLowerCase())
                                            "
                                            class="ml-3 text-white"
                                            >🚫</span
                                        >
                                    </div>
                                </div>
                                <div
                                    v-if="showFeedback && !isCurrentAnswerCorrect"
                                    class="mt-4 rounded-lg border-4 border-green-700 bg-green-100 p-3 shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                                >
                                    <p class="font-bold text-green-800">Correct answers:</p>
                                    <ul class="mt-1 list-inside list-disc text-green-900">
                                        <li v-for="(answer, i) in currentQuiz.answers" :key="i">
                                            {{ answer }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Feedback area -->
                            <div
                                v-if="showFeedback"
                                class="mt-6 rounded-md p-4"
                                :class="isCurrentAnswerCorrect ? 'bg-green-50 dark:bg-green-950/30' : 'bg-red-50 dark:bg-red-950/30'"
                            >
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
                                        <h3 class="text-muted-foreground dark:text-muted-foreground text-lg font-medium">
                                            {{ isCurrentAnswerCorrect ? 'Correct!' : 'Incorrect' }}
                                        </h3>
                                        <p
                                            v-if="!isCurrentAnswerCorrect && currentQuiz.type === 'multiple_choice'"
                                            class="text-muted-foreground dark:text-muted-foreground mt-1"
                                        >
                                            The correct answer is: {{ currentQuiz.answers[0] }}
                                        </p>
                                        <p
                                            v-else-if="!isCurrentAnswerCorrect && currentQuiz.type === 'true_false'"
                                            class="text-muted-foreground dark:text-muted-foreground mt-1"
                                        >
                                            The correct answer is: {{ currentQuiz.answers[0] === 'true' ? 'True' : 'False' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="flex justify-between">
                            <Button @click="previous" :disabled="currentIndex === 0" variant="outline">
                                <ChevronLeft class="mr-2 h-4 w-4" />
                                Previous
                            </Button>

                            <Button
                                v-if="!showFeedback"
                                @click="checkAnswer"
                                variant="default"
                                :disabled="
                                    (currentQuiz.type === 'multiple_choice' && !userAnswers[currentIndex]) ||
                                    (currentQuiz.type === 'true_false' && !userAnswers[currentIndex]) ||
                                    (currentQuiz.type === 'enumeration' && userAnswers[currentIndex].every((a: string) => !a))
                                "
                            >
                                Check Answer
                            </Button>

                            <Button v-else @click="next" variant="default">
                                {{ currentIndex === quizQuestions.length - 1 ? 'Finish Quiz' : 'Next Question' }}
                                <ChevronRight v-if="currentIndex !== quizQuestions.length - 1" class="ml-2 h-4 w-4" />
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
