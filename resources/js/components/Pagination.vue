<template>
    <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0" aria-label="Pagination">
        <div class="-mt-px flex w-0 flex-1">
            <Link
                v-if="links?.prev"
                :href="links.prev"
                class="inline-flex items-center border-t-2 border-transparent pt-4 pr-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
            >
                <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        fill-rule="evenodd"
                        d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1L4.66 9.25h12.59A.75.75 0 0118 10z"
                        clip-rule="evenodd"
                    />
                </svg>
                Previous
            </Link>
        </div>
        <div class="hidden md:-mt-px md:flex">
            <template v-for="(link, index) in paginationLinks" :key="index">
                <Link
                    v-if="link.url && !link.active"
                    :href="link.url"
                    class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    v-html="link.label"
                />
                <span
                    v-else-if="link.active"
                    class="inline-flex items-center border-t-2 border-indigo-500 px-4 pt-4 text-sm font-medium text-indigo-600"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500"
                    v-html="link.label"
                />
            </template>
        </div>
        <div class="-mt-px flex w-0 flex-1 justify-end">
            <Link
                v-if="links?.next"
                :href="links.next"
                class="inline-flex items-center border-t-2 border-transparent pt-4 pl-1 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
            >
                Next
                <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        fill-rule="evenodd"
                        d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z"
                        clip-rule="evenodd"
                    />
                </svg>
            </Link>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationLinks {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
    links: PaginationLink[];
}

interface Props {
    links?: PaginationLinks;
}

const props = defineProps<Props>();

const paginationLinks = computed(() => {
    // Filter out the first and last links as they're handled separately
    if (!props.links || !props.links.links || !Array.isArray(props.links.links)) {
        return [];
    }
    return props.links.links.filter((link, index) => index !== 0 && index !== props.links.links.length - 1);
});
</script>
