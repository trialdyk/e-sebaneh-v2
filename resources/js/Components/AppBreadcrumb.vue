<template>
    <UBreadcrumb :items="items" />
</template>

<script setup>
import { useBreadcrumb } from '@/composables/useBreadcrumb';

const props = defineProps({
    items: {
        type: Array,
        default: null,
    },
});

// Jika items tidak disediakan, generate otomatis dari URL
const autoItems = useBreadcrumb();
const items = props.items || autoItems.value;
</script>
