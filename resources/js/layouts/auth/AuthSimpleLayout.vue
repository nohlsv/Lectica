<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center p-6 md:p-10 min-h-screen">
        <div class="w-full max-w-sm flex-grow flex flex-col justify-center">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-3">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <img class="w-30 h-30 lg:w-40 lg:h-40 mx-auto" src="/logo-lectica.gif" alt="Lectica logo">
                        <h1 class="text-xl pixel-outline tracking-wide font-medium">{{ title }}</h1>
                        <p class="text-white/80 text-center text-sm pixel-outline tracking-wide -mb-5">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
