<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red-300 pixel-outline treacking-wide">
            {{ message }}
        </p>
    </div>
</template>
