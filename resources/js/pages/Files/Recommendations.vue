<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type File } from '@/types';
import FileCard from '@/components/FileCard.vue';
import { computed } from 'vue';

interface Props {
    recommendations: {
        program: File[];
        collaborative: File[];
        contentBased: File[];
        trending: File[];
    };
    userProgram: string | null;
}

const props = defineProps<Props>();

// Define breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Files',
        href: '/files',
    },
    {
        title: 'Recommendations',
        href: '/recommendations',
    },
];

// Check if we have any recommendations
const hasRecommendations = computed(() => {
    return props.recommendations.program.length > 0 ||
           props.recommendations.collaborative.length > 0 ||
           props.recommendations.contentBased.length > 0 ||
           props.recommendations.trending.length > 0;
});

// Format program name for display
const formattedProgram = computed(() => {
    return props.userProgram || 'Your Program';
});

</script>
