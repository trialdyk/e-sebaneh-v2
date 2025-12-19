<template>
    <DashboardLayout>
        <UDashboardPanel :id="pageId">
            <template #header>
                <UDashboardNavbar :title="title">
                    <template #leading>
                        <UDashboardSidebarCollapse />
                    </template>
                    <template v-if="$slots.actions" #trailing>
                        <slot name="actions" />
                    </template>
                </UDashboardNavbar>
            </template>

            <template #body>
                <!-- Breadcrumb -->
                <div v-if="showBreadcrumb" class="px-6 pt-6">
                    <UBreadcrumb :items="breadcrumbItems" />
                </div>

                <!-- Page Header -->
                <div v-if="heading || description || $slots.header" class="flex items-center justify-between px-6 py-4">
                    <div v-if="heading || description">
                        <h2 v-if="heading" class="text-2xl font-bold">{{ heading }}</h2>
                        <p v-if="description" class="text-sm text-gray-500 mt-1">{{ description }}</p>
                    </div>
                    <slot name="header" />
                </div>

                <!-- Content -->
                <div :class="contentClass">
                    <slot />
                </div>
            </template>
        </UDashboardPanel>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { useBreadcrumb } from '@/composables/useBreadcrumb';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    heading: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
    pageId: {
        type: String,
        default: null,
    },
    showBreadcrumb: {
        type: Boolean,
        default: true,
    },
    contentClass: {
        type: String,
        default: 'px-6 pb-6',
    },
});

// Auto breadcrumb
const breadcrumbItems = useBreadcrumb();
</script>
