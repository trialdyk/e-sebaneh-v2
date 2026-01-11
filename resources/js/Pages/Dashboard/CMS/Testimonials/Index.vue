<template>
    <DashboardPage
        title="Testimonial Management"
        heading="Kelola Testimoni"
        description="Kelola testimoni dari alumni dan wali santri"
        page-id="cms-testimonials"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                label="Tambah Testimoni"
                to="/dashboard/cms/testimonials/create"
            />
        </template>

        <UCard>
            <UTable 
                :data="testimonials.data" 
                :columns="columns"
                :loading="false"
            >
                <template #photo-cell="{ row }">
                    <img 
                        :src="row.original.photo ? `/storage/${row.original.photo}` : row.original.photo" 
                        :alt="row.original.name"
                        class="w-10 h-10 rounded-full object-cover"
                    >
                </template>

                <template #name-cell="{ row }">
                    <div>
                        <p class="font-medium">{{ row.original.name }}</p>
                        <p class="text-sm text-gray-500">{{ row.original.role }}</p>
                    </div>
                </template>

                <template #quote-cell="{ row }">
                    <p class="max-w-md text-sm line-clamp-2">{{ row.original.quote }}</p>
                </template>

                <template #rating-cell="{ row }">
                    <div class="flex items-center gap-1" v-if="row.original.rating">
                        <UIcon 
                            v-for="i in 5" 
                            :key="i"
                            name="i-lucide-star" 
                            :class="i <= row.original.rating ? 'text-yellow-400' : 'text-gray-300'"
                            class="w-4 h-4"
                        />
                    </div>
                    <span v-else class="text-gray-400 text-sm">-</span>
                </template>

                <template #order-cell="{ row }">
                    <UBadge color="gray" variant="subtle">{{ row.original.order }}</UBadge>
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
                            :to="`/dashboard/cms/testimonials/${row.original.id}/edit`"
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
                    Showing {{ testimonials.from }} to {{ testimonials.to }} of {{ testimonials.total }} entries
                </span>
                <UPagination 
                    v-model="currentPage"
                    :total="testimonials.total"
                    :page-count="testimonials.per_page"
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
    testimonials: Object,
});

const currentPage = ref(props.testimonials.current_page);

const columns = [
    { accessorKey: 'photo', header: 'Foto' },
    { accessorKey: 'name', header: 'Nama & Role' },
    { accessorKey: 'quote', header: 'Testimoni' },
    { accessorKey: 'rating', header: 'Rating' },
    { accessorKey: 'order', header: 'Urutan' },
    { accessorKey: 'is_active', header: 'Status' },
    { accessorKey: 'actions', header: 'Aksi' },
];

watch(currentPage, (page) => {
    router.get('/dashboard/cms/testimonials', { page }, { preserveState: true, replace: true });
});

const confirmDelete = (testimonial) => {
    if (confirm(`Yakin ingin menghapus testimoni dari "${testimonial.name}"?`)) {
        router.delete(`/dashboard/cms/testimonials/${testimonial.id}`);
    }
};
</script>
