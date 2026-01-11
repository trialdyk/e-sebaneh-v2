<template>
    <DashboardPage
        title="Tagihan Santri"
        heading="Tagihan Santri"
        description="Kelola tagihan santri - buat dan lacak pembayaran"
        page-id="student-invoices"
    >
        <template #header>
            <UButton
                color="primary"
                icon="i-lucide-plus"
                to="/dashboard/finance/student-invoices/create"
            >
                Buat Tagihan
            </UButton>
        </template>

        <!-- Filters -->
        <UCard class="mb-6">
            <div class="flex gap-4">
                <UInput
                    v-model="localFilters.name"
                    placeholder="Cari nama tagihan..."
                    icon="i-lucide-search"
                    class="flex-1"
                />

                <UButton
                    color="primary"
                    @click="applyFilters"
                >
                    Filter
                </UButton>
                <UButton
                    color="neutral"
                    variant="soft"
                    @click="resetFilters"
                >
                    Reset
                </UButton>
            </div>
        </UCard>

        <!-- Invoices Table -->
        <UCard>
            <UTable
                :data="invoices?.data || []"
                :columns="columns"
                :loading="false"
            >
                <template #name-cell="{ row }">
                    <div>
                        <p class="font-medium">{{ row.original.name }}</p>
                        <p class="text-xs text-gray-500">{{ getTypeLabel(row.original.type) }}</p>
                    </div>
                </template>

                <template #amount-cell="{ row }">
                    <span class="font-semibold text-blue-600">
                        Rp {{ formatCurrency(row.original.amount) }}
                    </span>
                </template>

                <template #payments_count-cell="{ row }">
                    <UBadge :color="row.original.payments_count > 0 ? 'success' : 'neutral'" variant="subtle">
                        {{ row.original.payments_count }} pembayaran
                    </UBadge>
                </template>

                <template #actions-cell="{ row }">
                    <UDropdownMenu
                        :items="[
                            [{
                                label: 'Detail',
                                icon: 'i-lucide-eye',
                                onSelect: () => router.visit(`/dashboard/finance/student-invoices/${row.original.id}`)
                            }],
                            [{
                                label: 'Edit',
                                icon: 'i-lucide-pencil',
                                onSelect: () => router.visit(`/dashboard/finance/student-invoices/${row.original.id}/edit`)
                            },
                            {
                                label: 'Hapus',
                                icon: 'i-lucide-trash-2',
                                onSelect: () => confirmDelete(row.original)
                            }]
                        ]"
                    >
                        <UButton
                            color="neutral"
                            variant="ghost"
                            icon="i-lucide-more-vertical"
                            size="sm"
                        />
                    </UDropdownMenu>
                </template>

                <template #empty>
                    <div class="text-center py-12">
                        <UIcon name="i-lucide-inbox" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                        <p class="text-gray-500">Belum ada tagihan dibuat</p>
                        <UButton
                            color="primary"
                            variant="soft"
                            icon="i-lucide-plus"
                            to="/dashboard/finance/student-invoices/create"
                            class="mt-4"
                        >
                            Buat Tagihan Pertama
                        </UButton>
                    </div>
                </template>
            </UTable>

            <!-- Pagination -->
            <div v-if="invoices?.data?.length > 0" class="flex justify-between items-center mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500">
                    Menampilkan {{ invoices.from }} - {{ invoices.to }} dari {{ invoices.total }} tagihan
                </p>
                <UPagination
                    v-model="currentPage"
                    :total="invoices.total"
                    :per-page="invoices.per_page"
                    @update:model-value="changePage"
                />
            </div>
        </UCard>
    </DashboardPage>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';

const props = defineProps({
    invoices: Object,
    filters: Object
});

const currentPage = ref(props.invoices?.current_page || 1);

const localFilters = reactive({
    name: props.filters?.name || ''
});



const columns = [
    { id: 'name', header: 'Nama Tagihan' },
    { id: 'amount', header: 'Nominal' },
    { id: 'payments_count', header: 'Pembayaran' },
    { id: 'actions', header: 'Aksi' }
];

function getTypeLabel(type) {
    const typeMap = {
        all_students: 'Semua Santri',
        by_classroom: 'Berdasarkan Kelas',
        by_gender: 'Berdasarkan Gender',
        specific_students: 'Santri Tertentu'
    };
    return typeMap[type] || type;
}

function getTypeBadgeColor(type) {
    const colorMap = {
        all_students: 'primary',
        by_classroom: 'info',
        by_gender: 'warning',
        specific_students: 'success'
    };
    return colorMap[type] || 'neutral';
}

function formatCurrency(value) {
    if (!value) return '0';
    return new Intl.NumberFormat('id-ID').format(value);
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function applyFilters() {
    router.get('/dashboard/finance/student-invoices', {
        name: localFilters.name || undefined
    }, {
        preserveState: true,
        preserveScroll: true
    });
}

function resetFilters() {
    localFilters.name = '';
    router.get('/dashboard/finance/student-invoices', {}, {
        preserveState: true,
        preserveScroll: true
    });
}

function changePage(page) {
    router.get('/dashboard/finance/student-invoices', {
        page,
        ...localFilters
    }, {
        preserveState: true,
        preserveScroll: true
    });
}

function confirmDelete(invoice) {
    if (confirm(`Yakin ingin menghapus tagihan "${invoice.name}"?`)) {
        router.delete(`/dashboard/finance/student-invoices/${invoice.id}`, {
            onSuccess: () => {
                // Success handled by flash message
            }
        });
    }
}
</script>
