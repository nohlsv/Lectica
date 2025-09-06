<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-3">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center items-center flex flex-col">
                        <img src="/lectica-logo.gif" alt="Lectica Logo" class="md:h-40 md:w-40 h-30 w-30 mb-5 animate-soft-bounce"/>
                            <h1 class="text-xl md:text-2xl font-extrabold pixel-outline tracking-wide">{{ title }}</h1>
                            <p class="text-center text-sm md:text-base pixel-outline tracking-wide">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
