<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { CheckIcon, XIcon, SwordIcon, ShieldIcon } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Progress } from '@/components/ui/progress';
import { Input } from '@/components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Label } from '@/components/ui/label';
import { toast } from 'vue-sonner';
import axios from 'axios';

interface Props {
    file: any;
    quizzes: any[];
    battle: any;
    quizTypes: Record<string, string>;
}

const props = defineProps<Props>();

const currentIndex = ref(0);
const userAnswers = ref<Record<number, any>>({});
const showFeedback = ref(false);
const battleFinished = ref(false);
const playerHp = ref(props.battle.player_hp);
const monsterHp = ref(props.battle.monster_hp);
const attackMessages = ref<string[]>([]);
const correctAnswers = ref(props.battle.correct_answers || 0);
const totalAnswered = ref(props.battle.total_questions || 0);

// Initialize userAnswers
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
    if (props.quizzes.length === 0) return null;
    return props.quizzes[currentIndex.value];
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
        let correctCount = 0;
        const requiredAnswers = [...quiz.answers];

        userAnswer.forEach((answer: any) => {
            if (answer && requiredAnswers.some(reqAns =>
                reqAns.toLowerCase() === answer.toLowerCase())) {
                correctCount++;
            }
        });

        return correctCount === requiredAnswers.length;
    }

    return false;
});

const playerHpPercent = computed(() => {
    return (playerHp.value / 100) * 100;
});

const monsterHpPercent = computed(() => {
    return (monsterHp.value / props.battle.monster.hp) * 100;
});

const battleResult = computed(() => {
    if (monsterHp.value <= 0) return 'victory';
    if (playerHp.value <= 0) return 'defeat';
    return null;
});

function checkAnswer() {
    showFeedback.value = true;
    totalAnswered.value++;

    // Process battle mechanics
    if (isCurrentAnswerCorrect.value) {
        correctAnswers.value++;
        // Player attacks monster - use fixed player attack value
        const playerAttack = 20; // Default player attack value
        const damage = calculateDamage(playerAttack, props.battle.monster.defense);
        monsterHp.value = Math.max(0, monsterHp.value - damage);
        attackMessages.value.unshift(`You dealt ${damage} damage to ${props.battle.monster.name}!`);
    } else {
        // Monster attacks player - use default player defense
        const playerDefense = 10; // Default player defense value
        const damage = calculateDamage(props.battle.monster.attack, playerDefense);
        playerHp.value = Math.max(0, playerHp.value - damage);
        attackMessages.value.unshift(`${props.battle.monster.name} dealt ${damage} damage to you!`);
    }

    // Check if battle is over
    if (monsterHp.value <= 0 || playerHp.value <= 0) {
        finishBattle();
    }
}

function calculateDamage(attack: number, defense: number) {
    // Ensure we have valid numbers
    const validAttack = Number(attack) || 0;
    const validDefense = Number(defense) || 0;

    const baseDamage = Math.max(1, validAttack - (validDefense / 2));
    const variance = baseDamage * 0.2; // 20% variance
    const finalDamage = Math.round(baseDamage + (Math.random() * variance * 2 - variance));

    // Ensure we return a valid number, minimum 1 damage
    return Math.max(1, finalDamage || 1);
}

function next() {
    if (currentIndex.value < props.quizzes.length - 1) {
        currentIndex.value++;
        showFeedback.value = false;
    }
}

function finishBattle() {
    battleFinished.value = true;

    // Save battle results
    axios.post(route('battles.complete'), {
        battle_id: props.battle.id,
        player_hp: playerHp.value,
        monster_hp: monsterHp.value,
        correct_answers: correctAnswers.value,
        total_questions: totalAnswered.value,
        status: battleResult.value
    }).then(() => {
        toast.success('Battle results recorded!');
    }).catch(() => {
        toast.error('Failed to save battle results');
    });
}
</script>

<template>
    <Head :title="`Battle: ${battle.monster.name}`" />
    <AppLayout>
        <div class="w-full min-h-screen overflow-hidden bg-cover bg-center flex flex-col"
        >
            <!--             style="background-image: url('/images/game-background.png');">-->

            <!-- Battle Header -->
            <div class="flex justify-between items-center p-4">
                <Link :href="route('files.quizzes.index', file.id)">
                    <Button class="bg-red-500 border-4 border-red-700 text-white font-bold
                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                        hover:bg-red-600 hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                        active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]
                        transition-all duration-150 ease-in-out">
                        Escape Battle
                    </Button>
                </Link>
                <div class="text-xl font-bold bg-black bg-opacity-70 text-white p-2 rounded-lg">
                    {{ file.name }}
                </div>
            </div>

            <!-- Battle Arena -->
            <div class="flex-1 flex flex-col">
                <!-- Monster Area -->
                <div class="flex flex-col items-center mb-4">
                    <div class="flex items-center bg-black bg-opacity-70 p-2 rounded-lg mb-2 w-full max-w-md">
                        <div class="text-white font-bold mr-2">{{ battle.monster.name }}</div>
                        <div class="flex-1">
                            <Progress :value="monsterHpPercent" class="h-4 bg-red-900">
                                <div class="h-full bg-red-500" :style="`width: ${monsterHpPercent}%`"></div>
                            </Progress>
                        </div>
                        <div class="text-white font-bold ml-2">{{ monsterHp }}/{{ battle.monster.hp }}</div>
                    </div>
                    <div class="relative">
                        <img :src="battle.monster.image_path" class="h-40 animate-floating"
                             style="image-rendering: pixelated;" alt="Monster" />
                    </div>
                </div>

                <!-- Battle Log -->
                <div class="bg-black bg-opacity-70 p-2 rounded-lg max-h-28 overflow-y-auto mb-4 mx-4">
                    <div v-for="(message, index) in attackMessages" :key="index" class="text-white text-sm">
                        {{ message }}
                    </div>
                    <div v-if="attackMessages.length === 0" class="text-gray-400 text-sm">
                        The battle is about to begin! Answer correctly to attack the monster.
                    </div>
                </div>

                <!-- Player Area -->
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center bg-black bg-opacity-70 p-2 rounded-lg mb-2 w-full max-w-md">
                        <div class="text-white font-bold mr-2">You</div>
                        <div class="flex-1">
                            <Progress :value="playerHpPercent" class="h-4 bg-green-900">
                                <div class="h-full bg-green-500" :style="`width: ${playerHpPercent}%`"></div>
                            </Progress>
                        </div>
                        <div class="text-white font-bold ml-2">{{ playerHp }}/100</div>
                    </div>
                </div>

                <!-- Quiz Question -->
                <Card class="mx-4 mb-4" v-if="currentQuiz && !battleFinished">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <Badge>{{ quizTypes[currentQuiz.type] }}</Badge>
                        </div>
                        <CardTitle>{{ currentQuiz.question }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <!-- Multiple Choice Question -->
                        <div v-if="currentQuiz.type === 'multiple_choice'" class="w-full">
                            <RadioGroup v-model="userAnswers[currentIndex]"
                                        class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-stretch w-full">
                                <div v-for="(option, index) in currentQuiz.options" :key="index" class="w-full h-full">
                                    <Label :for="`option-${index}`"
                                           class="flex items-center justify-center w-full h-full px-6 py-3 text-lg font-bold
                                            rounded-lg border-4 border-blue-700 bg-blue-500 text-white
                                            shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                                            cursor-pointer select-none text-center text-balance
                                            transform transition-all duration-150 ease-in-out
                                            hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                                            active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                           :class="{
                                            'bg-blue-600 border-blue-800': !showFeedback && userAnswers[currentIndex] === option,
                                            'bg-green-700 border-green-800': showFeedback && option === currentQuiz.answers[0],
                                            'bg-red-500 border-red-700': showFeedback && userAnswers[currentIndex] === option && option !== currentQuiz.answers[0]
                                        }">
                                        <RadioGroupItem :value="option" :id="`option-${index}`" :disabled="showFeedback" class="hidden" />
                                        {{ option }}
                                    </Label>
                                </div>
                            </RadioGroup>
                        </div>

                        <!-- True/False Question -->
                        <div v-else-if="currentQuiz.type === 'true_false'" class="w-full">
                            <RadioGroup v-model="userAnswers[currentIndex]" class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                                <Label for="answer-true"
                                       class="flex items-center justify-center w-full h-full px-6 py-3 text-lg font-bold
                                        rounded-lg border-4 border-green-700 bg-green-500 text-white
                                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)] cursor-pointer"
                                       :class="{
                                        'bg-green-600 border-green-800': !showFeedback && userAnswers[currentIndex] === 'true',
                                        'bg-green-700 border-green-800': showFeedback && 'true' === currentQuiz.answers[0],
                                        'bg-red-500 border-red-700': showFeedback && userAnswers[currentIndex] === 'true' && 'true' !== currentQuiz.answers[0]
                                    }">
                                    <RadioGroupItem value="true" id="answer-true" :disabled="showFeedback" class="hidden" />TRUE
                                </Label>
                                <Label for="answer-false"
                                       class="flex items-center justify-center w-full h-full px-6 py-3 text-lg font-bold
                                        rounded-lg border-4 border-red-700 bg-red-500 text-white
                                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)] cursor-pointer"
                                       :class="{
                                        'bg-red-600 border-red-800': !showFeedback && userAnswers[currentIndex] === 'false',
                                        'bg-green-700 border-green-800': showFeedback && 'false' === currentQuiz.answers[0],
                                        'bg-red-500 border-red-700': showFeedback && userAnswers[currentIndex] === 'false' && 'false' !== currentQuiz.answers[0]
                                    }">
                                    <RadioGroupItem value="false" id="answer-false" :disabled="showFeedback" class="hidden" />FALSE
                                </Label>
                            </RadioGroup>
                        </div>

                        <!-- Enumeration Question -->
                        <div v-else-if="currentQuiz.type === 'enumeration'" class="w-full space-y-4">
                            <p class="text-sm font-bold text-white bg-blue-500 border-4 border-blue-700 px-4 py-2 rounded-lg
                                shadow-[4px_4px_0px_rgba(0,0,0,0.4)]">
                                Enter {{ currentQuiz.answers.length }} answers
                            </p>
                            <div v-for="(answer, index) in userAnswers[currentIndex]" :key="index" class="w-full">
                                <div class="flex items-center w-full px-4 py-2 text-lg font-bold rounded-lg border-4
                                    bg-gradient-to-b from-gray-800 to-gray-900 text-yellow-300
                                    shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                                     :class="{
                                        'border-gray-700': !showFeedback,
                                        'bg-green-700 border-green-800 text-white': showFeedback && currentQuiz.answers.some((a: string) => a.toLowerCase() === answer.toLowerCase()),
                                        'bg-red-700 border-red-800 text-white': showFeedback && answer && !currentQuiz.answers.some((a: string) => a.toLowerCase() === answer.toLowerCase())
                                    }">
                                    <Input v-model="userAnswers[currentIndex][index]" :placeholder="`Answer ${index + 1}`"
                                           :disabled="showFeedback" class="flex-1 text-center bg-transparent border-none outline-none" />
                                </div>
                            </div>
                        </div>

                        <!-- Feedback area -->
                        <div v-if="showFeedback" class="mt-6 p-4 rounded-md"
                             :class="isCurrentAnswerCorrect ? 'bg-green-50 dark:bg-green-950/30' : 'bg-red-50 dark:bg-red-950/30'">
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
                                    <h3 class="text-lg font-medium text-muted-foreground">
                                        {{ isCurrentAnswerCorrect ? 'Correct! You attacked the monster!' : 'Incorrect! The monster attacked you!' }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-between">
                        <Button v-if="!showFeedback" @click="checkAnswer" variant="default"
                                :disabled="
                                (currentQuiz.type === 'multiple_choice' && !userAnswers[currentIndex]) ||
                                (currentQuiz.type === 'true_false' && !userAnswers[currentIndex]) ||
                                (currentQuiz.type === 'enumeration' && userAnswers[currentIndex].every((a: string) => !a))
                            "
                                class="w-full">
                            <SwordIcon class="h-4 w-4 mr-2" />
                            Attack with Answer
                        </Button>
                        <Button v-else @click="next" variant="default" class="w-full">
                            Next Question
                        </Button>
                    </CardFooter>
                </Card>

                <!-- Battle Result -->
                <Card class="mx-4 mb-4" v-if="battleFinished">
                    <CardHeader>
                        <CardTitle class="text-center">
                            {{ battleResult === 'victory' ? 'Victory!' : 'Defeat!' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-center">
                        <div v-if="battleResult === 'victory'" class="text-green-500 text-2xl mb-4">
                            You defeated {{ battle.monster.name }}!
                        </div>
                        <div v-else class="text-red-500 text-2xl mb-4">
                            {{ battle.monster.name }} defeated you!
                        </div>
                        <div class="mt-4">
                            <p>You answered {{ correctAnswers }} out of {{ totalAnswered }} questions correctly.</p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-center">
                        <Link :href="route('files.quizzes.index', file.id)">
                            <Button>Return to Quizzes</Button>
                        </Link>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-floating {
    animation: float 2s ease-in-out infinite;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}
</style>
