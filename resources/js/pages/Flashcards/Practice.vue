<script setup>
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ref, computed } from 'vue';
import { ChevronLeft, ChevronRight, RotateCcw, Shuffle } from 'lucide-vue-next';

const props = defineProps({
    file: Object,
    flashcards: Array,
});

const currentIndex = ref(0);
const showAnswer = ref(false);
const shuffled = ref(false);
const cards = ref([...props.flashcards]);

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
    let array = [...props.flashcards];
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    cards.value = array;
    currentIndex.value = 0;
    showAnswer.value = false;
    shuffled.value = true;
}

function resetOrder() {
    cards.value = [...props.flashcards];
    currentIndex.value = 0;
    showAnswer.value = false;
    shuffled.value = false;
}
</script>

<template>
    <Head title="Practice Flashcards" />

    <AuthLayout>
        <div class="mx-auto max-w-4xl space-y-6 p-6 sm:px-6 lg:px-8">
            <div class="flex justify-between">
                <h2 class="text-2xl font-bold">Practice Flashcards</h2>
                <div class="flex space-x-2">
                    <Link :href="route('files.flashcards.index', file.id)">
                        <Button variant="outline">Back to Flashcards</Button>
                    </Link>
                    <Button @click="shuffleCards" v-if="!shuffled">
                        <Shuffle class="h-4 w-4 mr-2" />
                        Shuffle
                    </Button>
                    <Button @click="resetOrder" v-else>
                        <RotateCcw class="h-4 w-4 mr-2" />
                        Reset Order
                    </Button>
                </div>
            </div>

            <div v-if="cards.length === 0" class="text-center py-10">
                <p class="text-gray-500">No flashcards available.</p>
                <p class="text-gray-500 mt-2">Create flashcards to start practicing.</p>
                <Link :href="route('files.flashcards.create', file.id)" class="mt-4 inline-block">
                    <Button>Create Flashcard</Button>
                </Link>
            </div>

            <div v-else class="space-y-4">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Card {{ currentIndex + 1 }} of {{ cards.length }}
                    </div>
                    <div class="w-1/2 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-primary rounded-full"
                            :style="{ width: `${progress}%` }"
                        ></div>
                    </div>
                </div>

                <Card class="min-h-[300px]">
                    <CardHeader>
                        <CardTitle class="text-center">{{ currentFlashcard.question }}</CardTitle>
                    </CardHeader>
                    <CardContent class="flex items-center justify-center">
                        <div v-if="showAnswer" class="text-center py-8">
                            <p class="text-lg">{{ currentFlashcard.answer }}</p>
                        </div>
                        <Button v-else @click="toggleAnswer" variant="outline" class="mx-auto">
                            Show Answer
                        </Button>
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
                        <Button @click="reset" variant="outline">
                            <RotateCcw class="h-4 w-4 mr-2" />
                            Restart
                        </Button>
                        <Button
                            @click="next"
                            :disabled="currentIndex === cards.length - 1"
                            variant="default"
                        >
                            Next
                            <ChevronRight class="h-4 w-4 ml-2" />
                        </Button>
                    </CardFooter>
                </Card>

                <div v-if="showAnswer" class="flex justify-center space-x-4">
                    <Button @click="next" :disabled="currentIndex === cards.length - 1" variant="default">
                        Next Card
                        <ChevronRight class="h-4 w-4 ml-2" />
                    </Button>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
