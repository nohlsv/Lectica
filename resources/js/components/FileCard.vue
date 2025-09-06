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
    <div class="flex flex-col border-4 border-black bg-black/75 p-4 font-pixelshadow-[6px_6px_0px_rgba(0,0,0,1)] hover:translate-x-1 hover:translate-y-1 hover:shadow-[4px_4px_0px_rgba(0,0,0,1)]
            transition-transform duration-200">
        <!-- File header -->
        <div class="bg-red-900/75 p-4 -mx-4 flex items-center justify-between border-b border-border">
            <h3 class="font-medium text-foreground truncate max-w-[16rem] [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">{{ file.name }}</h3>
            <button
                @click.prevent="toggleStar"
                class="inline-flex items-center justify-center rounded-full p-1 hover:bg-accent transition-colors"
                :class="{'text-amber-500': isStarred, 'text-muted-foreground': !isStarred}"
                :disabled="isStarring"
            >
                <StarIcon class="h-5 w-5 pixel-outline-icon" :fill="isStarred ? 'currentColor' : 'none'" />
            </button>
        </div>

        <!-- File content -->
        <div class="p-4 flex-1">
            <p class="text-sm text-muted-foreground mb-3 break-words whitespace-normal text-justify [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                {{ file.description ? file.description : "No description provided." }}
            </p>

            <!-- Tags -->
            <div v-if="file.tags && file.tags.length > 0" class="flex flex-wrap gap-1.5 mt-2">
                <span
                    v-for="tag in file.tags"
                    :key="tag.id"
                    class="inline-flex px-2 py-0.5 text-xs rounded-full bg-primary/10 text-primary [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]"
                >
                    {{ tag.name }}
                </span>
            </div>
        </div>

        <!-- File footer -->
        <div class="border-t border-border p-4 flex justify-between items-center">
            <div class="flex items-center gap-1 text-xs text-muted-foreground [text-shadow:2px_0_black,-2px_0_black,0_2px_black,0_-2px_black]">
                <span>{{ formattedDate }}</span>
                <span class="px-1.5">•</span>
                <span class="flex items-center gap-0.5">
                    <StarIcon class="h-3 w-3" />
                    {{ starCount }}
                </span>
            </div>

            <div v-if="showActions" class="flex items-center gap-1 ml-3">
                <Link
                    :href="`/files/${file.id}`"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                    title="View file details"
                >
                    <EyeIcon class="h-3.5 w-3.5" />
                </Link>
                <Link
                    v-if="file.can_edit"
                    :href="`/files/${file.id}/edit`"
                    class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-border bg-background text-foreground hover:bg-accent"
                    title="Edit file"
                >
                    <PencilIcon class="h-3.5 w-3.5" />
                </Link>
                <div v-else class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-border bg-background text-muted-foreground opacity-40" title="Only the uploader can edit this file">
                    <PencilIcon class="h-3.5 w-3.5" />
                </div>
            </div>
        </div>
    </div>
</template>
