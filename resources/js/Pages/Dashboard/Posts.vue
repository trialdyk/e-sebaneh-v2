<template>
    <DashboardLayout>
        <UDashboardPanel grow>
            <UDashboardNavbar title="Posts Management" />

            <UDashboardPanelContent>
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">All Posts</h2>
                        <p class="text-sm text-gray-500">Manage your blog posts</p>
                    </div>
                    <UButton color="primary" leading-icon="i-lucide-plus">
                        New Post
                    </UButton>
                </div>

                <UCard>
                    <UTable 
                        :rows="posts" 
                        :columns="columns"
                        :loading="loading"
                    >
                        <template #title-data="{ row }">
                            <div>
                                <p class="font-medium">{{ row.title }}</p>
                                <p class="text-sm text-gray-500">{{ row.excerpt }}</p>
                            </div>
                        </template>
                        <template #created_at-data="{ row }">
                            <span class="text-sm">{{ formatDate(row.created_at) }}</span>
                        </template>
                        <template #actions-data="{ row }">
                            <div class="flex gap-2">
                                <UButton 
                                    size="xs" 
                                    color="neutral" 
                                    variant="ghost"
                                    icon="i-lucide-pencil"
                                />
                                <UButton 
                                    size="xs" 
                                    color="error" 
                                    variant="ghost"
                                    icon="i-lucide-trash"
                                />
                            </div>
                        </template>
                    </UTable>
                </UCard>
            </UDashboardPanelContent>
        </UDashboardPanel>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    posts: {
        type: Array,
        default: () => []
    }
});

const loading = ref(false);

const columns = [
    { key: 'title', label: 'Post' },
    { key: 'created_at', label: 'Date' },
    { key: 'actions', label: 'Actions' }
];

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>
