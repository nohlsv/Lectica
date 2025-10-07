<script setup lang="ts">
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { toast, Toaster } from 'vue-sonner';

const page = usePage();

onMounted(() => {
    if (page.props.toast) {
        toast(page.props.toast);
    }
    if (page.props.success) {
        toast.success(page.props.success);
    }
    if (page.props.error) {
        toast.error(page.props.error);
    }
    if (page.props.warning) {
        toast.warning(page.props.warning);
    }
    if (page.props.info) {
        toast.info(page.props.info);
    }
    if (page.props.message) {
        toast.info(page.props.message);
    }
});

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <Toaster richColors />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />

            <!--Footer-->
            <footer class="font-pixel mt-0 w-full border-4 border-black bg-yellow-800 p-2 text-center text-white shadow-[4px_4px_0px_rgba(0,0,0,1)]">
                <p class="text-lg">
                    © 2025 <span class="border-2 border-white bg-black px-2 py-1 text-yellow-300 shadow-[2px_2px_0px_rgba(0,0,0,1)]">Lectica</span>
                </p>
            </footer>
    </AppLayout>
</template>
