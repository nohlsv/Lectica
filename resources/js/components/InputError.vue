<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-400 pixel-outline">
            {{ message }}
        </p>
    </div>
</template>
