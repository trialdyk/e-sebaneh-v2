<template>
    <DashboardPage
        title="FAQ Management"
        heading="Frequently Asked Questions"
        description="Kelola pertanyaan yang sering diajukan"
        page-id="cms-faqs"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                label="Tambah FAQ"
                to="/dashboard/cms/faqs/create"
            />
        </template>

        <UCard>
            <UTable 
                :data="faqs.data" 
                :columns="columns"
                :loading="false"
            >
                <template #question-cell="{ row }">
                    <div class="max-w-md">
                        <p class="font-medium">{{ row.original.question }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ row.original.answer }}</p>
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
                            :to="`/dashboard/cms/faqs/${row.original.id}/edit`"
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

            <!-- Pagination -->
            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ faqs.from }} to {{ faqs.to }} of {{ faqs.total }} entries
                </span>
                <UPagination 
                    v-model="currentPage"
                    :total="faqs.total"
                    :page-count="faqs.per_page"
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
    faqs: Object,
});

const currentPage = ref(props.faqs.current_page);

const columns = [
    { accessorKey: 'question', header: 'Pertanyaan & Jawaban' },
    { accessorKey: 'order', header: 'Urutan' },
    { accessorKey: 'is_active', header: 'Status' },
    { accessorKey: 'actions', header: 'Aksi' },
];

watch(currentPage, (page) => {
    router.get('/dashboard/cms/faqs', { page }, { preserveState: true, replace: true });
});

const confirmDelete = (faq) => {
    if (confirm(`Yakin ingin menghapus FAQ: "${faq.question}"?`)) {
        router.delete(`/dashboard/cms/faqs/${faq.id}`);
    }
};
</script>
