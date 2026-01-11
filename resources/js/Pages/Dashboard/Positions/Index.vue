<template>
    <DashboardPage
        title="Master Jabatan"
        heading="Data Jabatan"
        description="Kelola data master jabatan guru"
        page-id="master-positions"
    >
        <template #header>
            <div class="flex gap-2">
                <UInput
                    v-model="search"
                    icon="i-lucide-search"
                    placeholder="Cari jabatan..."
                    class="w-64"
                    @keyup.enter="handleSearch"
                />
                <UButton
                    color="primary"
                    icon="i-lucide-plus"
                    label="Tambah Jabatan"
                    to="/dashboard/positions/create"
                />
            </div>
        </template>

        <UCard>
            <UTable
                :data="positions.data"
                :columns="columns"
                :loading="loading"
            >
                <template #name-cell="{ row }">
                    <span class="font-medium">{{ row.original.name }}</span>
                </template>

                <template #actions-cell="{ row }">
                    <div class="flex gap-1">
                        <UButton
                            color="neutral"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-pencil"
                            :to="`/dashboard/positions/${row.original.id}/edit`"
                        />
                        <UButton
                            color="error"
                            variant="ghost"
                            size="xs"
                            icon="i-lucide-trash-2"
                            @click="openDeleteModal(row.original)"
                        />
                    </div>
                </template>
            </UTable>
            
            <!-- Pagination -->
            <div class="flex justify-between items-center px-4 py-3 border-t border-default">
                <span class="text-sm text-muted">
                    Showing {{ positions.from }} to {{ positions.to }} of {{ positions.total }} entries
                </span>
                <UPagination
                    v-model="currentPage"
                    :total="positions.total"
                    :page-count="positions.per_page"
                />
            </div>
        </UCard>

        <!-- Delete Confirmation Modal -->
        <UModal v-model:open="isDeleteModalOpen">
            <template #header>
                <h3 class="text-lg font-semibold">Hapus Jabatan</h3>
            </template>

            <template #body>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-error/10 rounded-lg">
                        <UIcon name="i-lucide-alert-triangle" class="w-6 h-6 text-error" />
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold">Apakah Anda yakin?</h4>
                        <p class="text-sm text-muted mt-1">
                            Jabatan <span class="font-semibold">{{ selectedPosition?.name }}</span> akan dihapus secara permanen.
                        </p>
                    </div>
                </div>
            </template>

            <template #footer>
                <div class="flex justify-end gap-2 w-full">
                    <UButton
                        color="neutral"
                        variant="soft"
                        label="Batal"
                        @click="closeDeleteModal"
                        :disabled="deleteProcessing"
                    />
                    <UButton
                        color="error"
                        label="Hapus"
                        :loading="deleteProcessing"
                        @click="confirmDelete"
                    />
                </div>
            </template>
        </UModal>
    </DashboardPage>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardPage from '@/Components/DashboardPage.vue';
import { debounce } from 'lodash';

const props = defineProps({
    positions: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const currentPage = ref(props.positions?.current_page || 1);
const loading = ref(false);

const columns = [
    { accessorKey: 'name', header: 'Nama Jabatan' },
    { accessorKey: 'actions', header: 'Aksi' }
];

const handleSearch = () => {
    router.get('/dashboard/positions', { search: search.value }, { preserveState: true, replace: true });
};

watch(search, debounce(() => {
    handleSearch();
}, 500));

watch(currentPage, (page) => {
    router.get('/dashboard/positions', { page, search: search.value }, { preserveState: true, replace: true });
});

// Delete modal
const isDeleteModalOpen = ref(false);
const selectedPosition = ref(null);
const deleteProcessing = ref(false);

const openDeleteModal = (position) => {
    selectedPosition.value = position;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    selectedPosition.value = null;
};

const confirmDelete = () => {
    deleteProcessing.value = true;
    router.delete(`/dashboard/positions/${selectedPosition.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false;
            closeDeleteModal();
        },
    });
};
</script>
