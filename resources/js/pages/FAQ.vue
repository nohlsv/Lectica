<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { ChevronDown, BookOpen, Zap, Trophy, Users, FileText, Settings } from 'lucide-vue-next';
import { ref } from 'vue';

// Track which sections are open
const openSections = ref<string[]>(['getting-started']);

const toggleSection = (section: string) => {
    if (openSections.value.includes(section)) {
        openSections.value = openSections.value.filter(s => s !== section);
    } else {
        openSections.value.push(section);
    }
};

const faqSections = [
    {
        id: 'getting-started',
        title: 'Getting Started',
        icon: BookOpen,
        questions: [
            {
                question: 'What is Lectica?',
                answer: 'Lectica is a gamified learning platform that helps you study through interactive quizzes, flashcards, and competitive battles. Upload your study materials and transform them into engaging learning experiences.'
            },
            {
                question: 'How do I upload my first file?',
                answer: 'Go to "My Files" and click "Upload File". You can upload PDF documents, text files, or other study materials. The system will automatically process your content to create quizzes and flashcards.'
            },
            {
                question: 'What file formats are supported?',
                answer: 'Lectica supports PDF files, plain text files (.txt), and other common document formats. The content is processed to extract key information for quiz generation.'
            },
            {
                question: 'How do I start studying?',
                answer: 'After uploading a file, you can: 1) Start a quiz directly from the file page, 2) Create flashcards for memorization, 3) Join battles with other users, or 4) Add files to collections for organized study sessions.'
            }
        ]
    },
    {
        id: 'basic-features',
        title: 'Basic Features',
        icon: FileText,
        questions: [
            {
                question: 'What are Collections?',
                answer: 'Collections are groups of related files that you can organize together. This is useful for studying multiple topics at once, like combining all files from a specific course or subject area.'
            },
            {
                question: 'How do Flashcards work?',
                answer: 'Flashcards are generated from your uploaded content to help with memorization. You can review them in spaced repetition mode, which shows cards you struggle with more frequently.'
            },
            {
                question: 'What are the different quiz types?',
                answer: 'Lectica offers multiple quiz formats: Multiple Choice, True/False, Fill-in-the-blank, and Short Answer questions. The system automatically generates questions based on your content.'
            },
            {
                question: 'How do I organize my files?',
                answer: 'Use tags to categorize files, create collections to group related materials, and use the star system to mark your favorites. The search function helps you find content quickly.'
            }
        ]
    },
    {
        id: 'battles-multiplayer',
        title: 'Battles & Multiplayer',
        icon: Users,
        questions: [
            {
                question: 'What are Battles?',
                answer: 'Battles are competitive quiz sessions where you can challenge yourself or compete against other users. They make studying more engaging through gamification.'
            },
            {
                question: 'How do I start a Battle?',
                answer: 'Go to "Battles" → "Create Battle". Choose your files or collections, select difficulty settings, and decide if you want to play solo or invite others to compete.'
            },
            {
                question: 'What are Multiplayer Games?',
                answer: 'Multiplayer games are real-time competitive sessions where multiple players answer questions simultaneously. Join lobbies or create your own games to challenge friends and other users.'
            },
            {
                question: 'How does matchmaking work?',
                answer: 'The system matches players based on similar skill levels and the content being studied. You can also create private rooms to play with specific friends or study groups.'
            }
        ]
    },
    {
        id: 'gamification',
        title: 'Gamification & Rewards',
        icon: Trophy,
        questions: [
            {
                question: 'How does the scoring system work?',
                answer: 'You earn points based on: correct answers (more points for harder questions), speed of response, streak bonuses for consecutive correct answers, and completion of study sessions.'
            },
            {
                question: 'What are Leaderboards?',
                answer: 'Leaderboards show top performers across different categories: overall points, weekly performance, specific subjects, and battle victories. Compete with others to climb the rankings!'
            },
            {
                question: 'How do I earn achievements?',
                answer: 'Achievements are unlocked by: completing your first quiz, maintaining study streaks, winning battles, helping other users, and mastering difficult content areas.'
            },
            {
                question: 'What are streaks?',
                answer: 'Streaks track consecutive days of studying or consecutive correct answers. Maintaining streaks earns bonus points and helps build consistent study habits.'
            }
        ]
    },
    {
        id: 'advanced-tips',
        title: 'Advanced Features & Tips',
        icon: Zap,
        questions: [
            {
                question: 'How can I improve quiz quality?',
                answer: 'Upload well-structured content with clear headings and key points. The better your source material, the more accurate and relevant the generated questions will be.'
            },
            {
                question: 'How do I track my progress?',
                answer: 'Check your profile for detailed statistics: accuracy rates, subjects studied, time spent learning, and improvement trends over time. Use this data to identify areas for focus.'
            },
            {
                question: 'What are the best study strategies?',
                answer: 'Mix different study methods: use flashcards for memorization, take quizzes to test knowledge, join battles for motivation, and review mistakes to improve weak areas. Consistent daily practice works best!'
            }
        ]
    },
    {
        id: 'account-settings',
        title: 'Account & Settings',
        icon: Settings,
        questions: [
            {
                question: 'How do I delete my account?',
                answer: 'Go to Settings → Account → Delete Account. Note that this action is permanent and will remove all your files, progress, and achievements.'
            },
            {
                question: 'What if I forgot my password?',
                answer: 'Use the "Forgot Password" link on the login page. You\'ll receive an email with instructions to reset your password securely.'
            }
        ]
    }
];
</script>

<template>
    <Head title="FAQ - Frequently Asked Questions" />

    <AppLayout>
        <div class="bg-lectica min-h-screen">
            <div class="container mx-auto px-4 py-8">
                <!-- Header Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-white mb-4 pixel-outline">
                        Frequently Asked Questions
                    </h1>
                    <p class="text-xl text-white/90 max-w-3xl mx-auto">
                        Everything you need to know to get started with Lectica. 
                        From basic usage to advanced features and gamification mechanics.
                    </p>
                </div>

                <!-- Quick Navigation -->
                <div class="mb-8">
                    <div class="flex flex-wrap justify-center gap-4">
                        <button
                            v-for="section in faqSections"
                            :key="section.id"
                            @click="toggleSection(section.id)"
                            class="flex items-center gap-2 px-4 py-2 bg-container rounded-lg text-white hover:bg-white/20 transition-colors pixel-outline"
                        >
                            <component :is="section.icon" class="h-4 w-4" />
                            {{ section.title }}
                        </button>
                    </div>
                </div>

                <!-- FAQ Sections -->
                <div class="max-w-4xl mx-auto space-y-6">
                    <Card
                        v-for="section in faqSections"
                        :key="section.id"
                        class="bg-container border-2 border-gold/30 shadow-lg"
                    >
                        <Collapsible :open="openSections.includes(section.id)">
                            <CollapsibleTrigger 
                                @click="toggleSection(section.id)"
                                class="w-full"
                            >
                                <CardHeader class="hover:bg-white/10 transition-colors rounded-t-lg">
                                    <CardTitle class="flex items-center justify-between text-white pixel-outline">
                                        <div class="flex items-center gap-3">
                                            <component :is="section.icon" class="h-6 w-6 text-gold" />
                                            <span class="text-xl">{{ section.title }}</span>
                                        </div>
                                        <ChevronDown 
                                            class="h-5 w-5 text-gold transition-transform duration-200"
                                            :class="{ 'rotate-180': openSections.includes(section.id) }"
                                        />
                                    </CardTitle>
                                </CardHeader>
                            </CollapsibleTrigger>

                            <CollapsibleContent>
                                <CardContent class="pt-0 pb-6">
                                    <div class="space-y-6">
                                        <div
                                            v-for="(item, index) in section.questions"
                                            :key="index"
                                            class="border-l-4 border-gold/50 pl-4 py-2"
                                        >
                                            <h4 class="font-semibold text-white mb-2 pixel-outline">
                                                {{ item.question }}
                                            </h4>
                                            <p class="text-white/90 leading-relaxed">
                                                {{ item.answer }}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </CollapsibleContent>
                        </Collapsible>
                    </Card>
                </div>

            </div>
        </div>
    </AppLayout>
</template>