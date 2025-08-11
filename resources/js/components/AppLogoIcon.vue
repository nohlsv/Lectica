<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img src="/favicon-32x32.png" alt="Lectica Logo" :class="className" v-bind="$attrs" />
</template>
