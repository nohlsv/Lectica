<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Progress } from '@/components/ui/progress';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckIcon, SwordIcon, XIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

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
const playerHp = ref(3); // Player starts with 3 hearts
const currentMonster = ref(props.battle.monster); // Current monster for this question
const attackMessages = ref<string[]>([]);
const correctAnswers = ref(props.battle.correct_answers || 0);
const totalAnswered = ref(props.battle.total_questions || 0);

// Visual effects
const monsterShaking = ref(false);
const answerEffectType = ref<'correct' | 'incorrect' | null>(null);
const soundEnabled = ref(true);

// Initialize userAnswers
if (Array.isArray(props.quizzes)) {
    props.quizzes.forEach((quiz, index) => {
        if (quiz.type === 'multiple_choice') {
            userAnswers.value[index] = '';
        } else if (quiz.type === 'enumeration') {
            userAnswers.value[index] = Array(quiz.answers.length).fill('');
        } else if (quiz.type === 'true_false') {
            userAnswers.value[index] = '';
        }
    });
} else {
    console.error('props.quizzes is not an array:', props.quizzes);
}

const currentQuiz = computed(() => {
    if (!Array.isArray(props.quizzes) || props.quizzes.length === 0) return null;
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
            if (answer && requiredAnswers.some((reqAns) => reqAns.toLowerCase() === answer.toLowerCase())) {
                correctCount++;
            }
        });

        return correctCount === requiredAnswers.length;
    }

    return false;
});

const playerHpPercent = computed(() => {
    return (playerHp.value / 3) * 100; // 3 hearts max
});

const battleResult = computed(() => {
    if (playerHp.value <= 0) return 'defeat';

    // If all questions completed and player still has health, it's victory
    if (battleFinished.value && playerHp.value > 0) {
        return 'victory';
    }

    return null;
});

// Sound effects
const correctSfx = new Audio('/sfx/correct.wav');
const incorrectSfx = new Audio('/sfx/incorrect.wav');
const gameStartSfx = new Audio('/sfx/game_start.wav');
const gameEndSfx = new Audio('/sfx/game_end.wav');
const streakSfx = new Audio('/sfx/streak.wav');
const victorySfx = new Audio('/sfx/victory.wav');
const defeatSfx = new Audio('/sfx/defeat.wav');
const damageSfx = new Audio('/sfx/damage.wav');

// Play sfx for game start (first question)
const firstQuestion = ref(true);
watch(currentIndex, (val) => {
    if (firstQuestion.value) {
        gameStartSfx.currentTime = 0;
        gameStartSfx.play();
        firstQuestion.value = false;
    }
});

// Play sfx for streaks (3+ correct in a row)
watch(correctAnswers, (val, oldVal) => {
    if (val >= 3 && val > oldVal) {
        streakSfx.currentTime = 0;
        streakSfx.play();
    }
});

// Play sfx for victory/defeat
watch(battleFinished, (val) => {
    if (val) {
        if (battleResult.value === 'victory') {
            victorySfx.currentTime = 0;
            victorySfx.play();
        } else if (battleResult.value === 'defeat') {
            defeatSfx.currentTime = 0;
            defeatSfx.play();
        }
        gameEndSfx.currentTime = 0;
        gameEndSfx.play();
    }
});

// Play damage sfx when damage is dealt/taken
function playDamageSfx() {
    damageSfx.currentTime = 0;
    damageSfx.play();
}

const nextQuestionDisabled = computed(() => {
    return (
        (currentQuiz.value.type === 'multiple_choice' && !userAnswers[currentIndex.value]) ||
        (currentQuiz.value.type === 'true_false' && !userAnswers[currentIndex.value]) ||
        (currentQuiz.value.type === 'enumeration' && userAnswers[currentIndex.value].every((a: string) => !a))
    );
});

// Initialize sound effects
const audioInit = () => {
    correctSfx.volume = 0.5;
    incorrectSfx.volume = 0.5;
    gameStartSfx.volume = 0.5;
    gameEndSfx.volume = 0.5;
    streakSfx.volume = 0.5;
    victorySfx.volume = 0.5;
    defeatSfx.volume = 0.5;
    damageSfx.volume = 0.5;
};

audioInit();

const currentQuestionIndex = ref(0);
const totalQuestions = ref(Array.isArray(props.quizzes) ? props.quizzes.length : 0);

const updateProgress = () => {
    currentQuestionIndex.value = currentIndex.value + 1;
};

watch(currentIndex, updateProgress);

// Get a random monster for each question
const getRandomMonster = async () => {
    try {
        const response = await axios.get('/api/monsters/random');
        return response.data;
    } catch (error) {
        console.error('Failed to get random monster:', error);
        return props.battle.monster; // Fallback to original monster
    }
};

// Initialize battle state
const initBattleState = async () => {
    playerHp.value = 3; // Start with 3 hearts
    currentMonster.value = await getRandomMonster(); // Start with random monster
    attackMessages.value = [`A wild ${currentMonster.value.name} appears!`];
    correctAnswers.value = props.battle.correct_answers || 0;
    totalAnswered.value = props.battle.total_questions || 0;
    userAnswers.value = {};

    // Reset userAnswers based on quiz type
    if (Array.isArray(props.quizzes)) {
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

    currentIndex.value = 0;
    showFeedback.value = false;
    battleFinished.value = false;
};

initBattleState();

// Sync battle state with props on file change
watch(
    () => props.file,
    (newFile) => {
        if (newFile) {
            initBattleState();
        }
    },
    { immediate: true },
);

const currentQuestion = computed(() => {
    if (!Array.isArray(props.quizzes) || currentIndex.value >= props.quizzes.length) return null;
    return props.quizzes[currentIndex.value];
});

// Format user answer for API submission
const formatUserAnswer = (answer: any): string => {
    if (answer === null || answer === undefined) {
        return '';
    }
    
    if (Array.isArray(answer)) {
        // For enumeration answers, join with commas
        const filtered = answer.filter(a => a !== null && a !== undefined && String(a).trim() !== '');
        return filtered.join(', ');
    }
    
    return String(answer).trim();
};

// Check if the current answer is correct
const checkAnswer = async () => {
    showFeedback.value = true;
    totalAnswered.value++;

    // Set visual effect type
    answerEffectType.value = isCurrentAnswerCorrect.value ? 'correct' : 'incorrect';

    // Record the answer via API
    try {
        const userAnswer = userAnswers.value[currentIndex.value];
        const formattedAnswer = formatUserAnswer(userAnswer);
        
        const payload = {
            quiz_id: currentQuiz.value.id,
            answer: formattedAnswer || 'No answer provided', // Ensure we always send a string
            is_correct: Boolean(isCurrentAnswerCorrect.value) // Ensure boolean type
        };
        
        await axios.post(`/battles/${props.battle.id}/answer`, payload);
    } catch (error: any) {
        console.error('Failed to record answer:', error);
        if (error?.response) {
            console.error('Response data:', error.response.data);
            console.error('Response status:', error.response.status);
        }
        // Don't interrupt the game flow if recording fails
    }

    // Play sound effect (if enabled)
    if (soundEnabled.value) {
        if (isCurrentAnswerCorrect.value) {
            correctSfx.currentTime = 0;
            correctSfx.play();
            playDamageSfx();
        } else {
            incorrectSfx.currentTime = 0;
            incorrectSfx.play();
            playDamageSfx();
        }
    }

    // Process battle mechanics
    if (isCurrentAnswerCorrect.value) {
        correctAnswers.value++;
        // Correct answer: Monster dies instantly
        attackMessages.value.unshift(`You defeated ${currentMonster.value.name} with the correct answer!`);

        // Trigger monster shake animation
        triggerMonsterShake();
    } else {
        // Incorrect answer: Monster attacks (1 damage) and runs away
        playerHp.value = Math.max(0, playerHp.value - 1);
        attackMessages.value.unshift(`${currentMonster.value.name} attacked you for 1 damage and ran away!`);
    }

    // Check if battle is over
    if (playerHp.value <= 0) {
        finishBattle();
    } else if (Array.isArray(props.quizzes) && currentIndex.value >= props.quizzes.length - 1) {
        // If this was the last question and player survived, they win
        finishBattle();
    }

    // Clear visual effects after delay
    clearAnswerEffect();
};

function calculateDamage(attack: number, defense: number) {
    // Ensure we have valid numbers
    const validAttack = Number(attack) || 0;
    const validDefense = Number(defense) || 0;

    const baseDamage = Math.max(1, validAttack - validDefense / 2);
    const variance = baseDamage * 0.2; // 20% variance
    const finalDamage = Math.round(baseDamage + (Math.random() * variance * 2 - variance));

    // Ensure we return a valid number, minimum 1 damage
    return Math.max(1, finalDamage || 1);
}

// Visual effect functions
function triggerMonsterShake() {
    monsterShaking.value = true;
    setTimeout(() => {
        monsterShaking.value = false;
    }, 500);
}

function clearAnswerEffect() {
    setTimeout(() => {
        answerEffectType.value = null;
    }, 2000);
}

function toggleSound() {
    soundEnabled.value = !soundEnabled.value;
}

async function next() {
    if (Array.isArray(props.quizzes) && currentIndex.value < props.quizzes.length - 1) {
        currentIndex.value++;
        showFeedback.value = false;

        // Spawn a new monster for the next question
        currentMonster.value = await getRandomMonster();
        attackMessages.value.unshift(`A wild ${currentMonster.value.name} appears!`);
    } else {
        // No more questions, finish the battle
        finishBattle();
    }
}

function finishBattle() {
    battleFinished.value = true;

    // Save battle results
    axios
        .post(route('battles.complete'), {
            battle_id: props.battle.id,
            player_hp: playerHp.value,
            monster_hp: playerHp.value > 0 ? 0 : 1, // If player survived, all monsters were defeated
            correct_answers: correctAnswers.value,
            total_questions: totalAnswered.value,
            status: battleResult.value,
        })
        .then(() => {
            toast.success('Battle results recorded!');
        })
        .catch(() => {
            toast.error('Failed to save battle results');
        });
}
</script>

<template>
    <Head :title="`Monster Arena Challenge`" />
    <AppLayout>
        <!-- Error state when quizzes is not an array -->
        <div v-if="!Array.isArray(quizzes) || quizzes.length === 0" class="bg-gradient flex min-h-screen items-center justify-center">
            <div class="text-center">
                <h1 class="mb-4 text-2xl font-bold text-white">Battle Error</h1>
                <p class="mb-6 text-white">No quiz questions are available for this battle.</p>
                <Link :href="route('battles.index')" class="rounded bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-700">
                    Back to Battles
                </Link>
            </div>
        </div>

        <div v-else class="bg-gradient flex min-h-screen w-full flex-col overflow-hidden">
            <!--             style="background-image: url('/images/game-background.png');">-->

            <!-- Battle Header -->
            <div class="flex flex-col justify-between p-4">
                <div class="mb-4 flex items-start justify-between">
                    <Link :href="route('files.quizzes.index', file.id)">
                        <Button
                            class="pixel-outline border-4 border-red-700 bg-red-500 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:bg-red-600 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                        >
                            Escape Battle
                        </Button>
                    </Link>
                    <Button
                        @click="toggleSound"
                        :class="[
                            'pixel-outline border-4 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]',
                            soundEnabled ? 'border-green-700 bg-green-500 hover:bg-green-600' : 'border-gray-700 bg-gray-500 hover:bg-gray-600',
                        ]"
                    >
                        {{ soundEnabled ? '🔊 Sound On' : '🔇 Sound Off' }}
                    </Button>
                </div>
                <div class="welcome-banner animate-soft-bounce pixel-outline mb-4 flex grid rounded-lg p-2 text-center text-xl font-bold md:text-3xl">
                    {{ file.name }}
                </div>
                <hr class="-mx-4 h-2 border-2 border-black bg-red-500 shadow-[2px_2px_0px_rgba(0,0,0,0.8)]" />
            </div>

            <!-- Battle Arena -->
            <div
                class="-my-4 flex flex-1 flex-col bg-container"
                style="
                    background-size: cover;
                    background-position: center;
                "
            >
                <!-- Monster Area -->
                <div class="mb-4 flex flex-col items-center">
                    <div class="relative flex w-full items-center justify-center bg-black/70 p-4">
                        <img
                            :src="currentMonster.image_path"
                            :class="[
                                'pixel-outline-icon h-30 transition-all duration-300 sm:h-40',
                                monsterShaking ? 'animate-shake' : 'animate-floating',
                            ]"
                            :alt="currentMonster.name"
                            @error="$event.target.style.display = 'none'"
                        />
                        <div class="flex flex-col rounded-lg p-4">
                            <div class="pixel-outline mb-2 text-left text-sm font-bold text-yellow-300 sm:text-base md:text-lg">
                                {{ quizTypes[currentQuiz.type] }}
                            </div>
                            <div
                                :class="[
                                    'pixel-outline text-base font-bold transition-all duration-300 sm:text-lg md:text-xl',
                                    answerEffectType === 'correct'
                                        ? 'animate-pulse text-green-300'
                                        : answerEffectType === 'incorrect'
                                          ? 'animate-pulse text-red-300'
                                          : 'text-white',
                                ]"
                            >
                                {{ currentQuiz.question }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Player Area -->
                <div class="mb-4 flex items-center justify-center">
                    <div class="mb-2 flex w-full max-w-md items-center rounded-lg border-2 border-green-500 bg-black/50 p-2">
                        <div class="pixel-outline mr-2 font-bold text-white">You</div>
                        <div class="flex flex-1 items-center justify-center space-x-1">
                            <span v-for="n in 3" :key="n" class="text-2xl">
                                {{ n <= playerHp ? '❤️' : '🖤' }}
                            </span>
                        </div>
                        <div class="pixel-outline ml-2 font-bold text-white">{{ playerHp }}/3</div>
                    </div>
                </div>

                <!-- Battle Log -->
                <div class="max-w-auto mx-4 mb-4 max-h-7.5 overflow-y-auto rounded-lg bg-blue-600 p-2">
                    <div v-for="(message, index) in attackMessages" :key="index" class="pixel-outline text-sm text-white">
                        {{ message }}
                    </div>
                    <div v-if="attackMessages.length === 0" class="pixel-outline text-sm text-gray-200">
                        The monster arena challenge is about to begin! Answer correctly to defeat each monster!
                    </div>
                </div>

                <!-- Quiz Question -->
                <Card class="mx-4 mb-10 border-2 border-blue-500 bg-black/50" v-if="currentQuiz && !battleFinished">
                    <CardContent>
                        <!-- Multiple Choice Question -->
                        <div v-if="currentQuiz.type === 'multiple_choice'" class="w-full">
                            <RadioGroup v-model="userAnswers[currentIndex]" class="grid w-full grid-cols-1 items-stretch gap-4 sm:grid-cols-2">
                                <div v-for="(option, index) in currentQuiz.options" :key="index" class="h-full w-full">
                                    <Label
                                        :for="`option-${index}`"
                                        class="pixel-outline flex h-full w-full transform cursor-pointer items-center justify-center rounded-lg border-4 border-blue-700 bg-blue-500 px-6 py-3 text-center text-lg font-bold text-balance text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out select-none hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                        :class="{
                                            'border-blue-800 bg-blue-600': !showFeedback && userAnswers[currentIndex] === option,
                                            'border-green-800 bg-green-700': showFeedback && option === currentQuiz.answers[0],
                                            'border-red-700 bg-red-500':
                                                showFeedback && userAnswers[currentIndex] === option && option !== currentQuiz.answers[0],
                                        }"
                                    >
                                        <RadioGroupItem :value="option" :id="`option-${index}`" :disabled="showFeedback" class="hidden" />
                                        {{ option }}
                                    </Label>
                                </div>
                            </RadioGroup>
                        </div>

                        <!-- True/False Question -->
                        <div v-else-if="currentQuiz.type === 'true_false'" class="w-full">
                            <RadioGroup v-model="userAnswers[currentIndex]" class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
                                <Label
                                    for="answer-true"
                                    class="pixel-outline flex h-full w-full cursor-pointer items-center justify-center rounded-lg border-4 border-green-700 bg-green-500 px-6 py-3 text-lg font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                                    :class="{
                                        'border-green-800 bg-green-600': !showFeedback && userAnswers[currentIndex] === 'true',
                                        'border-green-800 bg-green-700': showFeedback && 'true' === currentQuiz.answers[0],
                                        'border-red-700 bg-red-500':
                                            showFeedback && userAnswers[currentIndex] === 'true' && 'true' !== currentQuiz.answers[0],
                                    }"
                                >
                                    <RadioGroupItem value="true" id="answer-true" :disabled="showFeedback" class="hidden" />TRUE
                                </Label>
                                <Label
                                    for="answer-false"
                                    class="pixel-outline flex h-full w-full cursor-pointer items-center justify-center rounded-lg border-4 border-red-700 bg-red-500 px-6 py-3 text-lg font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                                    :class="{
                                        'border-red-800 bg-red-600': !showFeedback && userAnswers[currentIndex] === 'false',
                                        'border-green-800 bg-green-700': showFeedback && 'false' === currentQuiz.answers[0],
                                        'border-red-700 bg-red-500':
                                            showFeedback && userAnswers[currentIndex] === 'false' && 'false' !== currentQuiz.answers[0],
                                    }"
                                >
                                    <RadioGroupItem value="false" id="answer-false" :disabled="showFeedback" class="hidden" />FALSE
                                </Label>
                            </RadioGroup>
                        </div>

                        <!-- Enumeration Question -->
                        <div v-else-if="currentQuiz.type === 'enumeration'" class="w-full space-y-4">
                            <p
                                class="pixel-outline rounded-lg border-4 border-blue-700 bg-blue-500 px-4 py-2 text-sm font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                            >
                                Enter {{ currentQuiz.answers.length }} answers
                            </p>
                            <div v-for="(answer, index) in userAnswers[currentIndex]" :key="index" class="w-full">
                                <div
                                    class="flex w-full items-center rounded-lg border-4 px-4 py-2 text-lg font-bold text-yellow-300 shadow-[4px_4px_0px_rgba(0,0,0,0.4)]"
                                    :class="{
                                        'border-blue-700': !showFeedback,
                                        'border-green-800 bg-green-700 text-white':
                                            showFeedback && currentQuiz.answers.some((a: string) => a.toLowerCase() === answer.toLowerCase()),
                                        'border-red-800 bg-red-700 text-white':
                                            showFeedback &&
                                            answer &&
                                            !currentQuiz.answers.some((a: string) => a.toLowerCase() === answer.toLowerCase()),
                                    }"
                                >
                                    <Input
                                        v-model="userAnswers[currentIndex][index]"
                                        :placeholder="`Answer ${index + 1}`"
                                        :disabled="showFeedback"
                                        class="pixel-outline flex-1 border-none bg-transparent text-center text-white"
                                    />
                                </div>
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
                                        <CheckIcon class="pixel-outline-icon h-6 w-6 text-green-600 dark:text-green-300" />
                                    </div>
                                </div>
                                <div v-else class="flex-shrink-0">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900">
                                        <XIcon class="pixel-outline-icon h-6 w-6 text-red-600 dark:text-red-300" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-muted-foreground pixel-outline text-lg font-medium">
                                        {{ isCurrentAnswerCorrect ? 'Correct! You attacked the monster!' : 'Incorrect! The monster attacked you!' }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-between">
                        <Button
                            v-if="!showFeedback"
                            @click="checkAnswer"
                            variant="default"
                            :disabled="
                                (currentQuiz.type === 'multiple_choice' && !userAnswers[currentIndex]) ||
                                (currentQuiz.type === 'true_false' && !userAnswers[currentIndex]) ||
                                (currentQuiz.type === 'enumeration' && userAnswers[currentIndex].every((a: string) => !a))
                            "
                            class="w-full"
                        >
                            <SwordIcon class="mr-2 h-4 w-4" />
                            Attack with Answer
                        </Button>
                        <Button v-else @click="next" variant="default" class="w-full"> Next Question </Button>
                    </CardFooter>
                </Card>

                <!-- Battle Result -->
                <Card class="mx-4 mb-10 bg-black/50" v-if="battleFinished">
                    <CardHeader>
                        <CardTitle class="pixel-outline text-center">
                            {{ battleResult === 'victory' ? '🏆 Victory!' : '💀 Defeat!' }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="text-center">
                        <div v-if="battleResult === 'victory'" class="pixel-outline mb-4 text-2xl text-green-500">
                            🎉 You conquered the monster gauntlet! 🎉
                        </div>
                        <div v-else class="pixel-outline mb-4 text-2xl text-red-500">⚔️ The monsters proved too powerful! ⚔️</div>

                        <!-- Battle Summary -->
                        <div class="mt-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="rounded-lg bg-black/30 p-3">
                                    <div class="pixel-outline font-bold text-green-400">Questions Correct</div>
                                    <div class="pixel-outline text-2xl font-bold text-green-300">{{ correctAnswers }}</div>
                                    <div class="pixel-outline text-gray-400">out of {{ totalAnswered }}</div>
                                </div>
                                <div class="rounded-lg bg-black/30 p-3">
                                    <div class="pixel-outline font-bold text-blue-400">Accuracy</div>
                                    <div class="pixel-outline text-2xl font-bold text-blue-300">
                                        {{ Math.round((correctAnswers / totalAnswered) * 100) }}%
                                    </div>
                                    <div class="pixel-outline text-gray-400">success rate</div>
                                </div>
                            </div>

                            <div class="rounded-lg bg-black/30 p-3">
                                <div class="pixel-outline mb-2 font-bold text-red-400">Hearts Status</div>
                                <div class="flex items-center justify-center space-x-1">
                                    <span v-for="n in 3" :key="n" class="text-3xl">
                                        {{ n <= playerHp ? '❤️' : '🖤' }}
                                    </span>
                                </div>
                                <div class="pixel-outline mt-1 text-gray-400">{{ playerHp }}/3 remaining</div>
                            </div>

                            <div class="rounded-lg bg-black/30 p-3">
                                <div class="pixel-outline mb-2 font-bold text-yellow-400">🏟️ Challenge Complete</div>
                                <div class="pixel-outline text-gray-300">
                                    {{
                                        battleResult === 'victory'
                                            ? `You defeated ${correctAnswers} monsters and survived the arena!`
                                            : `You defeated ${correctAnswers} monsters before falling in battle.`
                                    }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-center space-x-4">
                        <Link :href="route('battles.show', props.battle.id)">
                            <Button class="pixel-outline">View Battle Details</Button>
                        </Link>
                        <Link :href="route('battles.index')">
                            <Button class="pixel-outline">Return to Battles</Button>
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

.animate-shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
    100% {
        transform: translateY(0px);
    }
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    10%,
    30%,
    50%,
    70%,
    90% {
        transform: translateX(-10px);
    }
    20%,
    40%,
    60%,
    80% {
        transform: translateX(10px);
    }
}
</style>
