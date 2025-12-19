<template>
    <DashboardLayout>
        <UDashboardPanel id="dashboard">
            <template #header>
                <UDashboardNavbar title="Dashboard">
                    <template #leading>
                        <UDashboardSidebarCollapse />
                    </template>
                </UDashboardNavbar>
            </template>

            <template #body>
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
                    <UCard v-for="(stat, index) in stats" :key="index">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.title }}</p>
                                <p class="text-3xl font-bold mt-1">{{ stat.value }}</p>
                            </div>
                            <UIcon :name="stat.icon" class="w-8 h-8 text-primary" />
                        </div>
                        <UBadge :color="stat.variation > 0 ? 'success' : 'error'" variant="soft">
                            {{ stat.variation > 0 ? '+' : '' }}{{ stat.variation }}%
                        </UBadge>
                    </UCard>
                </div>

                <!-- Recent Posts -->
                <div class="px-6 pb-6">
                    <UCard>
                        <template #header>
                            <h3 class="text-lg font-semibold">Recent Posts</h3>
                        </template>

                        <div class="space-y-3">
                            <div v-for="post in posts" :key="post.id" class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                                <div>
                                    <p class="font-medium">{{ post.title }}</p>
                                    <p class="text-sm text-gray-500">{{ formatDate(post.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </UCard>
                </div>
            </template>
        </UDashboardPanel>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    posts: {
        type: Array,
        default: () => []
    }
});

const stats = [{
    title: 'Total Users',
    icon: 'i-lucide-users',
    value: '1,234',
    variation: 12
}, {
    title: 'Revenue',
    icon: 'i-lucide-circle-dollar-sign',
    value: '$45.2K',
    variation: 23
}, {
    title: 'Posts',
    icon: 'i-lucide-file-text',
    value: props.posts.length,
    variation: 8
}, {
    title: 'Conversion',
    icon: 'i-lucide-trending-up',
    value: '3.2%',
    variation: -2
}];

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>
