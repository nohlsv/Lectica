<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="text-sidebar-primary-foreground pixel-outline-icon flex aspect-square size-8 items-center justify-center rounded-md bg-white">
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm hidden sm:block">
        <span class="pixel-outline truncate leading-none font-semibold">Lectica</span>
    </div>
</template>
