<template>
    <DashboardPage
        title="Program"
        heading="Kelola Program Pondok"
        description="Kelola program unggulan pondok pesantren"
        page-id="cms-programs"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                label="Tambah Program"
                to="/dashboard/cms/programs/create"
            />
        </template>

        <UCard>
            <UTable 
                :data="programs.data" 
                :columns="columns"
                :loading="false"
            >
                <template #icon-cell="{ row }">
                    <UIcon :name="row.original.icon" class="w-6 h-6 text-primary" />
                </template>

                <template #title-cell="{ row }">
                    <div class="max-w-md">
                        <p class="font-medium">{{ row.original.title }}</p>
                        <p class="text-sm text-gray-500 line-clamp-1">{{ row.original.description }}</p>
                    </div>
                </template>

                <template #order-cell="{ row }">
                    <UBadge color="neutral" variant="subtle">{{ row.original.order }}</UBadge>
                </template>

                <template #is_active-cell="{ row }">
                    <UBadge :color="row.original.is_active ? 'success' : 'neutral'" variant="subtle">
                        {{ row.original.is_active ? 'Aktif' : 'Nonaktif' }}
                    </UBadge>
                </template>

                <template #actions-cell="{ row }">
                    <div class="flex gap-1">
                        <UButton 
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-pencil" 
                            :to="`/dashboard/cms/programs/${row.original.id}/edit`"
                        />
                        <UButton 
                            color="error"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-trash-2" 
                            @click="confirmDelete(row.original)"
                        />
                    </div>
                </template>
            </UTable>

            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ programs.from }} to {{ programs.to }} of {{ programs.total }} entries
                </span>
                <UPagination 
                    v-model="currentPage"
                    :total="programs.total"
                    :page-count="programs.per_page"
                />
            </div>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    programs: Object,
});

const currentPage = ref(props.programs.current_page);

const columns = [
    { accessorKey: 'icon', header: 'Icon' },
    { accessorKey: 'title', header: 'Program & Deskripsi' },
    { accessorKey: 'order', header: 'Urutan' },
    { accessorKey: 'is_active', header: 'Status' },
    { accessorKey: 'actions', header: 'Aksi' },
];

watch(currentPage, (page) => {
    router.get('/dashboard/cms/programs', { page }, { preserveState: true, replace: true });
});

const confirmDelete = (program) => {
    if (confirm(`Yakin ingin menghapus program "${program.title}"?`)) {
        router.delete(`/dashboard/cms/programs/${program.id}`);
    }
};
</script>
