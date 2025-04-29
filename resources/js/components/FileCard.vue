<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { StarIcon, EyeIcon, PencilIcon } from 'lucide-vue-next';
import { type File } from '@/types';
import { computed, ref } from 'vue';
import { useDateFormat } from '@vueuse/core';

interface Props {
    file: File;
    showActions?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showActions: true
});

const isStarred = ref(props.file.is_starred || false);
const starCount = ref(props.file.star_count || 0);
const isStarring = ref(false);

const formattedDate = computed(() => {
    return useDateFormat(props.file.created_at, 'MMM D, YYYY');
});

const toggleStar = async () => {
    if (isStarring.value) return;

    isStarring.value = true;

    try {
        await router.post(route('files.star', { file: props.file.id }), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                isStarred.value = !isStarred.value;
                starCount.value = isStarred.value ? starCount.value + 1 : starCount.value - 1;
            },
            onFinish: () => {
                isStarring.value = false;
            }
        });
    } catch (error) {
        isStarring.value = false;
        console.error('Error toggling star', error);
    }
};
</script>

<template>
    <div class="flex flex-col rounded-lg border border-border overflow-hidden transition-shadow duration-200 hover:shadow-md">
        <!-- File header -->
        <div class="bg-accent/30 p-4 flex items-center justify-between border-b border-border">
            <h3 class="font-medium text-foreground line-clamp-1">{{ file.name }}</h3>
            <button
                @click.prevent="toggleStar"
                class="inline-flex items-center justify-center rounded-full p-1 hover:bg-accent transition-colors"
                :class="{'text-amber-500': isStarred, 'text-muted-foreground': !isStarred}"
                :disabled="isStarring"
            >
                <StarIcon class="h-5 w-5" :fill="isStarred ? 'currentColor' : 'none'" />
            </button>
        </div>

        <!-- File content -->
        <div class="p-4 flex-1">
            <p class="text-sm text-muted-foreground line-clamp-3 mb-3">
                {{ file.content }}
            </p>

            <!-- Tags -->
            <div v-if="file.tags && file.tags.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                <span
                    v-for="tag in file.tags"
                    :key="tag.id"
                    class="inline-flex px-2 py-0.5 text-xs rounded-full bg-primary/10 text-primary"
                >
                    {{ tag.name }}
                </span>
            </div>
        </div>

        <!-- File footer -->
        <div class="border-t border-border p-4 flex justify-between items-center">
            <div class="flex items-center gap-1 text-xs text-muted-foreground">
                <span>{{ formattedDate }}</span>
                <span class="px-1.5">•</span>
                <span class="flex items-center gap-0.5">
                    <StarIcon class="h-3 w-3" />
                    {{ starCount }}
                </span>
            </div>

            <div v-if="showActions" class="flex items-center gap-2">
                <Link
                    :href="`/files/${file.id}`"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                    title="View file details"
                >
                    <EyeIcon class="h-3.5 w-3.5" />
                </Link>
                <Link
                    :href="`/files/${file.id}/edit`"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                    title="Edit file"
                >
                    <PencilIcon class="h-3.5 w-3.5" />
                </Link>
            </div>
        </div>
    </div>
</template>
