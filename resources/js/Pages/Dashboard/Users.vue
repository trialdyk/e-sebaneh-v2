<template>
    <DashboardLayout>
        <UDashboardPanel grow>
            <UDashboardNavbar title="Users Management" />

            <UDashboardPanelContent>
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">All Users</h2>
                        <p class="text-sm text-gray-500">Manage system users</p>
                    </div>
                    <UButton color="primary" leading-icon="i-lucide-user-plus">
                        Add User
                    </UButton>
                </div>

                <UCard>
                    <UTable :rows="users" :columns="columns">
                        <template #name-data="{ row }">
                            <div class="flex items-center gap-3">
                                <UAvatar 
                                    :src="`https://api.dicebear.com/7.x/avataaars/svg?seed=${row.email}`"
                                    size="sm"
                                />
                                <div>
                                    <p class="font-medium">{{ row.name }}</p>
                                    <p class="text-sm text-gray-500">{{ row.email }}</p>
                                </div>
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

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});

const columns = [
    { key: 'name', label: 'User' },
    { key: 'created_at', label: 'Joined' },
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
