<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { HelpCircle, BookOpen, Zap } from 'lucide-vue-next';

// This component will show helpful tips for new users
defineProps<{
    showGuide?: boolean;
}>();
</script>

<template>
    <div v-if="showGuide" class="mb-8">
        <Card class="bg-container border-2 border-gold/30 shadow-lg">
            <CardHeader>
                <CardTitle class="flex items-center gap-3 text-white pixel-outline">
                    <HelpCircle class="h-6 w-6 text-gold" />
                    <span class="text-xl">New to Lectica?</span>
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-white/90 leading-relaxed">
                    Welcome! Get started with these essential steps to make the most of your learning experience.
                </p>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3 p-4 bg-white/10 rounded-lg">
                        <BookOpen class="h-5 w-5 text-gold mt-0.5" />
                        <div>
                            <h4 class="font-semibold text-white pixel-outline mb-1">1. Upload Your First File</h4>
                            <p class="text-sm text-white/80">Start by uploading PDF documents or text files to create your study materials.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3 p-4 bg-white/10 rounded-lg">
                        <Zap class="h-5 w-5 text-gold mt-0.5" />
                        <div>
                            <h4 class="font-semibold text-white pixel-outline mb-1">2. Take a Quiz</h4>
                            <p class="text-sm text-white/80">Test your knowledge with auto-generated quizzes and start earning points!</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <Button as-child class="bg-gold hover:bg-gold/90 text-black font-semibold pixel-outline">
                        <Link href="/faq">
                            <HelpCircle class="h-4 w-4 mr-2" />
                            Read Full FAQ
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="border-gold text-gold hover:bg-gold/10 pixel-outline">
                        <Link href="/files">
                            <BookOpen class="h-4 w-4 mr-2" />
                            Upload Files
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>