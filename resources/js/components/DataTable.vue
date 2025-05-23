<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { type PaginatedData } from '@/types';

export interface Column<T> {
    key: keyof T;
    label: string;
}

interface Props<T> {
    data: PaginatedData<T>;
    columns: Column<T>[];
    showActions?: boolean;
}

const props = defineProps<Props<any>>();

const hasData = computed(() => props.data.data.length > 0);

// Define the slots that can be used with this component
defineSlots<{
    actions(props: { item: any }): any;
    [key: string]: (props: { item: any }) => any; // Support for dynamic slot names including cell-*
}>();
</script>

<template>
    <div>
        <table class="table-auto w-full">
            <thead class="border-b">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key.toString()"
                        class="text-left p-4 pb-4 border-r border-border last:border-r-0"
                    >
                        {{ column.label }}
                    </th>
                    <th v-if="$slots.actions" class="text-left p-4 pb-4">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody v-if="hasData">
                <tr v-for="(item, index) in data.data" :key="index" class="border-b">
                    <td
                        v-for="column in columns"
                        :key="column.key.toString()"
                        class="p-4 border-r border-border last:border-r-0"
                    >
                        <!-- Check for a custom cell template, use it if available -->
                        <slot
                            v-if="$slots['cell-' + column.key.toString()]"
                            :name="'cell-' + column.key.toString()"
                            :item="item"
                        ></slot>
                        <!-- Default rendering if no custom template -->
                        <template v-else>{{ item[column.key] }}</template>
                    </td>
                    <td v-if="$slots.actions" class="p-4">
                        <slot name="actions" :item="item"></slot>
                    </td>
                </tr>
            </tbody>
            <tbody v-else>
                <tr>
                    <td
                        :colspan="$slots.actions ? columns.length + 1 : columns.length"
                        class="p-8 text-center text-muted-foreground"
                    >
                        No data available
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div
            v-if="hasData"
            class="mt-4 flex items-center justify-between border-t border-border px-4 py-3 sm:px-6"
        >
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-muted-foreground">
                        Showing
                        <span class="font-medium text-foreground">{{ data.from }}</span>
                        to
                        <span class="font-medium text-foreground">{{ data.to }}</span>
                        of
                        <span class="font-medium text-foreground">{{ data.total }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <Link
                            v-for="link in data.links"
                            :key="link.label"
                            :href="link.url ?? ''"
                            :class="[
                                link.active
                                    ? 'z-10 bg-primary text-primary-foreground focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring'
                                    : 'text-foreground hover:bg-accent focus:outline-offset-0',
                                'relative inline-flex items-center px-4 py-2 text-sm font-semibold border border-border'
                            ]"
                        ><span v-html="link.label"></span>
                        </Link>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>
