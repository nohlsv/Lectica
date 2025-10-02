<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/vue3';
import { BookOpen, HelpCircle, Zap } from 'lucide-vue-next';

// This component will show helpful tips for new users
defineProps<{
    showGuide?: boolean;
}>();
</script>

<template>
    <div v-if="showGuide" class="mb-8">
        <Card class="bg-container border-gold/30 border-2 shadow-lg">
            <CardHeader>
                <CardTitle class="pixel-outline flex items-center gap-3 text-white">
                    <HelpCircle class="text-gold h-6 w-6" />
                    <span class="text-xl">New to Lectica?</span>
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="leading-relaxed text-white/90">
                    Welcome! Get started with these essential steps to make the most of your learning experience.
                </p>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-lg bg-white/10 p-4">
                        <BookOpen class="text-gold mt-0.5 h-5 w-5" />
                        <div>
                            <h4 class="pixel-outline mb-1 font-semibold text-white">1. Upload Your First File</h4>
                            <p class="text-sm text-white/80">Start by uploading PDF documents or text files to create your study materials.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-lg bg-white/10 p-4">
                        <Zap class="text-gold mt-0.5 h-5 w-5" />
                        <div>
                            <h4 class="pixel-outline mb-1 font-semibold text-white">2. Take a Quiz</h4>
                            <p class="text-sm text-white/80">Test your knowledge with auto-generated quizzes and start earning points!</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <Button as-child class="bg-gold hover:bg-gold/90 pixel-outline font-semibold text-black">
                        <Link href="/faq">
                            <HelpCircle class="mr-2 h-4 w-4" />
                            Read Full FAQ
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="border-gold text-gold hover:bg-gold/10 pixel-outline">
                        <Link href="/files">
                            <BookOpen class="mr-2 h-4 w-4" />
                            Upload Files
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
