<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type File, type Flashcard } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, ChevronRight, RotateCcw, Shuffle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

const showAnswer = ref(false);

interface Props {
    file: File;
    flashcards: Flashcard[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Home', href: route('home') },
    { title: props.file.name, href: route('files.show', props.file.id) },
    { title: 'Flashcards', href: route('files.flashcards.index', props.file.id) },
    { title: 'Practice', href: route('files.flashcards.practice', props.file.id) },
];

const currentIndex = ref(0);
const shuffled = ref(false);
const cards = ref([...props.flashcards]);
const userAnswers = ref<Record<number, string>>({});

const currentFlashcard = computed(() => {
    if (cards.value.length === 0) return null;
    return cards.value[currentIndex.value];
});

const progress = computed(() => {
    if (cards.value.length === 0) return 0;
    return Math.round(((currentIndex.value + 1) / cards.value.length) * 100);
});

function toggleAnswer() {
    showAnswer.value = !showAnswer.value;
}

function next() {
    if (currentIndex.value < cards.value.length - 1) {
        currentIndex.value++;
        showAnswer.value = false;
    }
}

function previous() {
    if (currentIndex.value > 0) {
        currentIndex.value--;
        showAnswer.value = false;
    }
}

function reset() {
    currentIndex.value = 0;
    showAnswer.value = false;
}

function shuffleCards() {
    // Fisher-Yates shuffle algorithm
    const array = [...props.flashcards];
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    cards.value = array;
    currentIndex.value = 0;
    showAnswer.value = false;
    shuffled.value = true;
    toast.success('Flashcards shuffled successfully!');
}

function resetOrder() {
    cards.value = [...props.flashcards];
    currentIndex.value = 0;
    showAnswer.value = false;
    shuffled.value = false;
    toast.success('Flashcard order reset successfully!');
}

function recordAnswer(index: number, answer: string) {
    userAnswers.value[index] = answer;
}

function storePracticeRecord(correctAnswers: number, totalQuestions: number, mistakes: any[]) {
    axios
        .post(route('practice-records.store'), {
            file_id: props.file.id,
            type: 'flashcard',
            correct_answers: correctAnswers,
            total_questions: totalQuestions,
            mistakes,
        })
        .then(() => {
            toast.success('Practice record saved successfully!');
        })
        .catch((error) => {
            toast.error('Failed to save practice record.', {
                description: error.response?.data.message || 'An error occurred.',
            });
        });
}

function finishPractice() {
    const mistakes = cards.value
        .map((card, index) => {
            const userAnswer = userAnswers.value[index];
            if (userAnswer !== card.answer) {
                return {
                    question: card.question,
                    your_answer: userAnswer || 'No answer provided',
                    correct_answer: card.answer,
                };
            }
            return null;
        })
        .filter(Boolean);

    const correctAnswers = cards.value.length - mistakes.length;
    const totalQuestions = cards.value.length;

    storePracticeRecord(correctAnswers, totalQuestions, mistakes);
}
</script>

<!-- Flashcard Template -->
<template>
    <Head title="Practice Flashcards" />
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <AppLayout :breadcrumbs="breadcrumbs">
        <div 
          class="min-h-screen bg-no-repeat bg-cover bg-center bg-container"
       >

            <!--Buttons Row (Top Left // Center)-->
            <div 
              class="flex flex-wrap justify-center sm:justify-start gap-3 mt-6 sm:mt-8 ml-4 sm:ml-6">
              <!--Back Button (Escape)-->
              <Link :href="route('files.flashcards.index', file.id)">
                <Button 
                  variant="default" 
                  class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-sm sm:text-base md:text-lg
                        bg-red-500 border-4 border-red-700 text-white font-bold
                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                        hover:bg-red-600 hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                        active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]
                        transition-all duration-150 ease-in-out pixel-outline">
                  Escape
                </Button>
              </Link>
                <!--Shuffle Button-->
                <Button 
                  @click="shuffleCards" 
                  v-if="!shuffled"
                  class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-sm sm:text-base md:text-lg
                        bg-green-500 border-4 border-green-700 text-white font-bold
                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                        hover:bg-green-600 hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                        active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]
                        transition-all duration-150 ease-in-out pixel-outline">
                  <Shuffle class="h-4 w-4 sm:h-5 sm:w-5 mr-2" />
                    Shuffle
                </Button>
                <!--Reset Button (from shuffle)-->
                <Button 
                  @click="resetOrder" 
                  v-else
                  class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-sm sm:text-base md:text-lg
                        bg-blue-500 border-4 border-blue-700 text-white font-bold
                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                        hover:bg-blue-600 hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                        active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]
                        transition-all duration-150 ease-in-out pixel-outline">
                  <RotateCcw class="h-4 w-4 sm:h-5 sm:w-5 mr-2" />
                  Reset
                </Button>
                <!--Restart Button-->
                <Button
                  @click="reset"
                  variant="default"
                  class="px-3 sm:px-4 md:px-6 py-2 sm:py-3 text-sm sm:text-base md:text-lg
                        bg-yellow-500 border-4 border-yellow-700 text-white font-bold
                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                        hover:bg-yellow-600 hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                        active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]
                        transition-all duration-150 ease-in-out pixel-outline">
                  <RotateCcw class="h-4 w-4 sm:h-5 sm:w-5 mr-2" />
                  Restart
                </Button>
            </div>

            <!--Main Content-->
            <div class="mx-auto max-w-4xl space-y-6 p-6 sm:px-6 lg:px-8">
            <!--Title Header-->
            <div class="welcome-banner px-6 py-4 animate-soft-bounce text-center mb-4">
              <h2 class="text-2xl font-pixel text-white tracking-wider 
                        pixel-outline">
                Flashcard Practice
              </h2>
            </div>
            <!--No Flashcards Message-->
            <div v-if="cards.length === 0" class="text-center py-10">
              <p class="text-muted-foreground pixel-outline">No flashcards available.</p>
              <p class="text-muted-foreground mt-2 pixel-outline">Create flashcards to start practicing.</p>
              <Link :href="route('files.flashcards.create', file.id)" class="mt-4 inline-block">
                <Button>Create Flashcard</Button>
              </Link>
            </div>
            <div v-else class="space-y-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-muted-foreground">
                        Card {{ currentIndex + 1 }} of {{ cards.length }}
                    </div>
                    <!--No Flashcards Message-->
                    <div v-if="cards.length === 0" class="py-10 text-center">
                        <p class="text-muted-foreground">No flashcards available.</p>
                        <p class="text-muted-foreground mt-2">Create flashcards to start practicing.</p>
                        <Link :href="route('files.flashcards.create', file.id)" class="mt-4 inline-block">
                            <Button>Create Flashcard</Button>
                        </Link>
                    </div>
                    <div v-else class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="text-muted-foreground text-sm">Card {{ currentIndex + 1 }} of {{ cards.length }}</div>
                            <div class="h-2 w-1/2 overflow-hidden rounded-full bg-gray-200">
                                <div class="bg-primary h-full rounded-full" :style="{ width: `${progress}%` }"></div>
                            </div>
                        </div>
                        <!--Flashcard Layout-->
                        <Card
                            class="font-pixel relative mx-auto min-h-[320px] w-full max-w-sm overflow-hidden rounded-xl border-[6px] border-yellow-500 bg-gradient-to-br from-gray-900 via-black to-gray-800 shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-transform duration-500 hover:scale-105 hover:shadow-[0_0_25px_rgba(255,115,0,0.9)] sm:min-h-[360px] sm:max-w-md md:min-h-[420px] md:max-w-lg"
                        >
                            <CardContent class="perspective flex min-h-[320px] flex-col items-center justify-between p-6">
                                <!--Flip Flashcard-->
                                <div
                                    class="transform-style-preserve-3d relative flex w-full flex-1 items-center justify-center transition-transform duration-700"
                                    :class="{ 'rotate-y-180': showAnswer }"
                                >
                                    <!--Front (Question)-->
                                    <div class="absolute inset-0 flex items-center justify-center p-4 backface-hidden sm:p-6">
                                        <p class="font-pixel text-center text-sm leading-relaxed break-words text-white sm:text-base md:text-lg">
                                            {{ (currentFlashcard as Flashcard).question }}
                                        </p>
                                    </div>
                                    <!--Back (Answer)-->
                                    <div class="absolute inset-0 flex rotate-y-180 items-center justify-center p-4 backface-hidden sm:p-6">
                                        <p
                                            class="font-pixel animate-soft-bounce px-2 text-center text-sm text-yellow-300 sm:px-4 sm:text-base md:text-lg"
                                        >
                                            {{ (currentFlashcard as Flashcard).answer }}
                                        </p>
                                    </div>
                                </div>
                                <!--Middle Button (Flip Trigger)-->
                                <div
                                    @click="toggleAnswer"
                                    class="group relative mt-6 flex h-16 w-16 cursor-pointer items-center justify-center rounded-full border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] transition-transform duration-300 hover:scale-110 sm:mt-8 sm:h-20 sm:w-20"
                                >
                                    <!--Glow-->
                                    <div
                                        class="absolute h-20 w-20 rounded-full bg-white opacity-20 blur-xl group-hover:animate-ping sm:h-24 sm:w-24"
                                    ></div>
                                    <!--Show star when on question-->
                                    <span v-if="!showAnswer" class="relative z-10 animate-pulse text-xl sm:text-2xl md:text-3xl">⭐ </span>
                                    <!--Show moon when on answer-->
                                    <span v-else class="relative z-10 animate-pulse text-xl sm:text-2xl md:text-3xl">🌙 </span>
                                </div>
                            </CardContent>

                            <!--Footer with navigation buttons-->
                            <CardFooter class="flex justify-between border-t-4 border-black bg-yellow-300 px-4 py-3">
                                <!--Redirects to previous flashcard-->
                                <Button
                                    @click="previous"
                                    variant="default"
                                    class="font-pixel border-4 border-black bg-gray-200 transition hover:translate-x-1 hover:translate-y-1 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                >
                                    <ChevronLeft class="mr-2 h-4 w-4" />
                                    Previous
                                </Button>
                                <!--Skips to next flashcard-->
                                <Button
                                    @click="next"
                                    variant="default"
                                    class="font-pixel border-4 border-black bg-gray-200 transition hover:translate-x-1 hover:translate-y-1 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                                >
                                    Skip
                                    <ChevronRight class="ml-2 h-4 w-4" />
                                </Button>
                            </CardFooter>
                        </Card>

                        <!--Next Card Button (after flipping)-->
                        <div v-if="showAnswer" class="flex justify-center">
                            <Button
                                @click="next"
                                :disabled="currentIndex === cards.length - 1"
                                class="border-4 border-green-700 bg-green-500 font-bold text-white shadow-[4px_4px_0px_rgba(0,0,0,0.4)] transition-all duration-150 ease-in-out hover:-translate-y-1 hover:bg-green-600 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)] active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]"
                            >
                                Next Card
                                <ChevronRight class="ml-2 h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
                <!--Flashcard Layout-->
                <Card class="relative min-h-[320px] sm:min-h-[360px] md:min-h-[420px] w-full max-w-sm
                          sm:max-w-lg md:max-w-xl lg:max-w-3xl xl:max-w-4xl border-[6px] border-yellow-500 rounded-xl bg-gradient-to-br 
                          from-gray-900 via-black to-gray-800 shadow-[4px_4px_0px_rgba(0,0,0,0.4)] hover:scale-105 
                          hover:shadow-[0_0_25px_rgba(255,115,0,0.9)] transition-transform duration-500 
                          font-pixel overflow-hidden mx-auto">
                  <CardContent class="flex flex-col items-center justify-between min-h-[320px] perspective p-6">
                    <!--Flip Flashcard-->
                    <div
                      class="relative flex-1 w-full flex items-center justify-center transition-transform duration-700 transform-style-preserve-3d"
                      :class="{ 'rotate-y-180': showAnswer }">
                      <!--Front (Question)-->
                      <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6 backface-hidden">
                        <p class="text-sm sm:text-base md:text-lg lg:text-2xl font-pixel text-white text-center break-words leading-relaxed">
                          {{ (currentFlashcard as Flashcard).question }}
                        </p>
                      </div>
                      <!--Back (Answer)-->
                      <div class="absolute inset-0 flex items-center justify-center p-4 sm:p-6 backface-hidden rotate-y-180">
                        <p class="text-sm sm:text-base md:text-lg lg:text-xl font-pixel text-yellow-300 text-center px-2 sm:px-4 animate-soft-bounce">
                          {{ (currentFlashcard as Flashcard).answer }}
                        </p>
                      </div>
                    </div>
                    <!--Middle Button (Flip Trigger)-->
                    <div
                      @click="toggleAnswer"
                      class="relative mt-6 sm:mt-8 cursor-pointer group flex items-center justify-center 
                          w-16 h-16 sm:w-20 sm:h-20 rounded-full border-4 border-black 
                          bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] 
                          hover:scale-110 transition-transform duration-300">
                      <!--Glow-->
                      <div
                        class="absolute w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white blur-xl opacity-20
                            group-hover:animate-ping"> 
                      </div>
                      <!--Show star when on question-->
                      <span v-if="!showAnswer" 
                        class="relative z-10 text-xl sm:text-2xl md:text-3xl animate-pulse">⭐
                      </span>
                      <!--Show moon when on answer-->
                      <span v-else 
                        class="relative z-10 text-xl sm:text-2xl md:text-3xl animate-pulse">🌙
                      </span>
                    </div>
                  </CardContent>

                  <!--Footer with navigation buttons-->
                  <CardFooter class="flex justify-between px-4 py-3 bg-yellow-500 border-t-4 border-black">
                    <!--Redirects to previous flashcard-->
                    <Button
                      class="bg-blue-500 text-[#fdf6ee] hover:bg-blue-600 border-blue-700 border-2 pixel-outline">
                      <ChevronLeft class="h-4 w-4 mr-2" />
                      Previous
                    </Button>
                    <!--Reset Button (from shuffle)-->
                    <Button
                      @click="next"
                      variant="default"
                      class="bg-orange-500 text-[#fdf6ee] hover:bg-orange-600 border-orange-700 border-2 pixel-outline">
                          Skip
                      <ChevronRight class="h-4 w-4 ml-2" />
                    </Button>
                  </CardFooter>
                </Card>
                
                <!--Next Card Button (after flipping)-->
                <div v-if="showAnswer" class="flex justify-center">
                  <Button 
                    @click="next" 
                    :disabled="currentIndex === cards.length - 1"
                    class="bg-green-500 border-4 border-green-700 text-white font-bold
                        shadow-[4px_4px_0px_rgba(0,0,0,0.4)]
                        hover:bg-green-600 hover:-translate-y-1 hover:shadow-[6px_6px_0px_rgba(0,0,0,0.4)]
                        active:translate-y-0 active:shadow-[1px_1px_0px_rgba(0,0,0,0.4)]
                        transition-all duration-150 ease-in-out pixel-outline">
                    Next Card
                    <ChevronRight class="h-4 w-4 ml-2" />
                  </Button>
                </div>
            </div>
        </AppLayout>
    </div>
</template>
