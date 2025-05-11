<script setup lang="ts">
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import { toast } from 'vue-sonner';

const page = usePage();

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
console.log(page.props);

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
    </AppLayout>
</template>
